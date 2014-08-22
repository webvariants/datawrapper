<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\RestApp;

use Datawrapper\ORM\Action;
use Datawrapper\ORM\User;
use Datawrapper\ORM\UserQuery;
use Datawrapper\Hooks;
use Datawrapper\Mailer;
use Datawrapper\Session;

class UserController extends BaseController {
    /**
     * get list of all users
     * @needs admin
     */
    public function indexAction() {
        $user = Session::getUser();
        if ($user->isAdmin()) {
            $users = UserQuery::create()->filterByDeleted(false)->find();
            $res = array();
            foreach ($users as $user) {
                $res[] = $user->toArray();
            }
            ok($res);
        } else {
            error(403, 'Permission denied');
        }
    }

    public function getAction($id) {
        $user = Session::getUser();
        if ($user->isAdmin()) {
            ok(UserQuery::create()->findPK($id)->toArray());
        } else {
            error(403, 'Permission denied');
        }
    }

    /**
     * create a new user
     */
    public function createAction() {
        $data = json_decode($app->request()->getBody());
        $currUser = Session::getUser();
        $invitation = empty($data->invitation)? false : (bool) $data->invitation;
        // check values
        $checks = array(
            'email-missing' => function($d) { return trim($d->email) != ''; },
            'email-invalid' => function($d) { return check_email($d->email); },
            'email-already-exists' => function($d) { return !email_exists($d->email); },
        );
        // if invitation is false: classic way, we check passwords
        if (!$invitation) {
            $checks = array_merge($checks, array(
                'password-mismatch' => function($d) { return $d->pwd === $d->pwd2; },
                'password-missing' => function($d) { return trim($d->pwd) != ''; },
            ));
        }

        foreach ($checks as $code => $check) {
            if (call_user_func($check, $data) == false) {
                error($code, $code);
                return;
            }
        }

        // all checks passed
        $user = new User();
        $user->setCreatedAt(time());
        $user->setEmail($data->email);

        if (!$invitation) {
            $user->setPwd($data->pwd);
        }
        if ($currUser->isAdmin() && !empty($data->role)) {
            // Only sysadmin can set a sysadmin role
            if ($data->role == "sysadmin"){
                if (!$currUser->isSysAdmin()) {
                    error(403, 'Permission denied');
                    return;
                }
            }
            $user->SetRole($data->role);
        }
        $user->setLanguage(Session::getLanguage());
        $user->setActivateToken(hash_hmac('sha256', $data->email.'/'.time(), DW_TOKEN_SALT));
        $user->save();
        $result = $user->toArray();

        Hooks::execute(Hooks::USER_SIGNUP, $user);

        // send an email
        $mailer   = new Mailer($dw_config);
        $name     = $data->email;
        $domain   = $GLOBALS['dw_config']['domain'];
        $protocol = !empty($_SERVER['HTTPS']) ? "https" : "http";

        if ($invitation) {
            // send account invitation link
            $invitationLink = $protocol . '://' . $domain . '/account/invite/' . $user->getActivateToken();

            $mailBody = $mailer->renderBody($app, 'invitation.twig', array(
                'name'            => $user->guessName(),
                'invitation_link' => $invitationLink
            ));

            $mailer->sendSupportMail(
                $name,
                sprintf(__('You have been invited to Datawrapper on %s'), $domain),
                $mailBody
            );
        }
        else {
            // send account activation link
            $activationLink = $protocol . '://' . $domain . '/account/activate/' . $user->getActivateToken();

            $mailBody = $mailer->renderBody($app, 'activation.twig', array(
                'name'            => $user->guessName(),
                'activation_link' => $activationLink
            ));

            $mailer->sendSupportMail(
                $name,
                __('Datawrapper: Please activate your email address'),
                $mailBody
            );

            // we don't need to annoy the user with a login form now,
            // so just log in..
            Session::login($user);
        }

        ok($result);
    }

    /**
     * update user profile
     * @needs admin or existing user
     */
    public function updateAction($user_id) {
        $payload = json_decode($app->request()->getBody());
        $curUser = Session::getUser();

        if ($curUser->isLoggedIn()) {
            if ($user_id == 'current' || $curUser->getId() === $user_id) {
                $user = $curUser;
            } else if ($curUser->isAdmin()) {
                $user = UserQuery::create()->findPK($user_id);
            }

            if (!empty($user)) {
                $messages = array();
                $errors = array();

                if (!empty($payload->pwd)) {
                    // update password
                    $chk = false;
                    if (!empty($payload->oldpwhash)) {
                        $chk = $user->getPwd() === secure_password($payload->oldpwhash);
                    }
                    if ($chk || $curUser->isSysAdmin()) {
                        $user->setPwd($payload->pwd);
                        Action::logAction($curUser, 'change-password', array('user' => $user->getId()));
                    } else {
                        Action::logAction($curUser, 'change-password-failed', array('user' => $user->getId(), 'reason' => 'old password is wrong'));
                        $errors[] = __('The password could not be changed because your old password was not entered correctly.');
                    }
                }

                if (!empty($payload->email) && $payload->email != $user->getEmail()) {
                    if (check_email($payload->email) || $curUser->isAdmin()) {
                        if (!email_exists($payload->email)) {
                            if ($curUser->isAdmin()) {
                                $user->setEmail($payload->email);
                            } else {
                                // non-admins need to confirm new emails addresses
                                $token = hash_hmac('sha256', $user->getEmail().'/'.$payload->email.'/'.time(), DW_TOKEN_SALT);
                                $token_link = 'http://' . $GLOBALS['dw_config']['domain'] . '/account/settings?token='.$token;

                                // send email with token
                                $mailer   = new Mailer($dw_config);
                                $mailBody = $mailer->renderBody($app, 'email-change.twig', array(
                                    'name'                    => $user->guessName(),
                                    'email_change_token_link' => $token_link,
                                    'old_email'               => $user->getEmail(),
                                    'new_email'               => $payload->email
                                ));

                                $mailer->sendSupportMail(
                                    $payload->email,
                                    __('Datawrapper: You requested a change of your email address'),
                                    $mailBody
                                );

                                // log action for later confirmation
                                Action::logAction($curUser, 'email-change-request', array(
                                    'old-email' => $user->getEmail(),
                                    'new-email' => $payload->email,
                                    'token' => $token
                                ));
                                $messages[] = __('To complete the change of your email address, you need to confirm that you have access to it. Therefor we sent an email with the confirmation link to your new address. Your new email will be set right after you clicked that link.');
                            }
                        } else {
                            $errors[] = sprintf(__('The email address <b>%s</b> already exists.'), $payload->email);
                        }
                    } else {
                        $errors[] = sprintf(__('The email address <b>%s</b> is invalid.'), $payload->email);
                    }
                }

                if (!empty($payload->name)) {
                    $user->setName($payload->name);

                }

                if ($curUser->isAdmin() && !empty($payload->role)) {
                    // Only sysadmin can set a sysadmin role
                    if ($payload->role == "sysadmin"){
                        if (!$curUser->isSysAdmin()) {
                            error(403, 'Permission denied');
                            return;
                        }
                    }
                    $user->setRole($payload->role);
                }

                if (!empty($payload->website)) {
                    $user->setWebsite($payload->website);
                }

                if (!empty($payload->profile)) {
                    $user->setSmProfile($payload->profile);
                }

                if ($user->isModified()) {
                    $user->save();
                    $messages[] = __('This just worked fine. Your profile has been updated.');
                }

                ok(array('messages' => $messages, 'errors' => $errors));
            } else {
                error('user-not-found', 'no user found with that id');
            }
        } else {
            error('need-login', 'you must be logged in to do that');
        }
    }

    /**
     * delete a user
     * @needs admin or existing user
     */
    public function deleteAction($user_id) {
        $curUser = Session::getUser();
        $payload = json_decode($app->request()->getBody());
        if (!isset($payload->pwd)) {
            $pwd = $app->request()->get('pwd');
            if (empty($pwd)) {
                error('no-password', 'no password was provided with the delete request');
                return;
            }
        } else {
            $pwd = $payload->pwd;
        }
        if ($curUser->isLoggedIn()) {
            if ($user_id == 'current' || $curUser->getId() == $user_id) {
                $user = $curUser;
            } else if ($curUser->isAdmin()) {
                $user = UserQuery::create()->findPK($user_id);
                $pwd = $user->getPwd();
            }
            if (!empty($user)) {
                if ($user->getPwd() === secure_password($pwd)) {

                    // Delete user
                    if (!$curUser->isAdmin()) {
                        Session::logout();
                    }
                    $user->erase();

                    ok();
                } else {
                    Action::logAction($user, 'delete-request-wrong-password', json_encode(get_user_ips()));
                    error('wrong-password', __('The password you entered is not correct.'));
                }
            } else {
                error('user-not-found', 'no user found with that id');
            }
        } else {
            error('need-login', 'you must be logged in to do that');
        }
    }

    public function addProductsAction($id) {
        if_is_admin(function() use ($app, $id) {
            $user = UserQuery::create()->findPk($id);
            if ($user) {
                $data = json_decode($app->request()->getBody(), true);
                foreach ($data as $p_id => $expires) {
                    $product = ProductQuery::create()->findPk($p_id);
                    if ($product) {
                        $up = new UserProduct();
                        $up->setProduct($product);

                        if ($expires) {
                            $up->setExpires($expires);
                        }

                        $user->addUserProduct($up);
                    }
                }
                $user->save();
                ok();
            } else {
                 error('user-not-found', 'no user found with that id');
            }
        });
    }

    protected function emailExists($email) {
        return !!UserQuery::create()->findOneByEmail($email);
    }
}
