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
use Datawrapper\ORM\ActionQuery;
use Datawrapper\ORM\UserQuery;
use Datawrapper\Session;
use Datawrapper\Mailer;

class AccountController extends BaseController {
    /* get session info */
    public function indexAction() {
        try {
            $r = Session::toArray();
            ok($r);
        } catch (Exception $e) {
            error('exception', $e->getMessage());
        }
    }

    /* get current language */
    public function getLanguageAction() {
        ok(Session::getLanguage());
    }

    /* set a new language */
    public function updateLanguageAction() {
        $data = json_decode($app->request()->getBody());
        Session::setLanguage( $data->lang );
        ok();
    }

    /**
     * endpoint for sending a new password to a user
     *
     * expects payload { "email": "validemail@domain.tld" }
     */
    public function sendPasswordResetMailAction() {
        $payload = json_decode($app->request()->getBody());
        $user = UserQuery::create()->findOneByEmail($payload->email);
        if (!empty($user)) {

            $curToken = $user->getResetPasswordToken();
            if (!empty($curToken)) {
                error('password-already-reset', __('The password reset email has already been sent. Please contact an <a href="mailto:hello@datawrapper.de">administrator</a>.'));
                return;
            }

            if ($user->getRole() == 'pending') {
                error('account-not-activated', __('You haven\'t activated this email address yet, so we cannot safely send emails to it. Please contact an <a href="mailto:hello@datawrapper.de">administrator</a>.'));
                return;
            }

            $token = hash_hmac('sha256', $user->getEmail().'/'.$user->getPwd().'/'.microtime(), DW_TOKEN_SALT);
            Action::logAction($user, 'reset-password', $token);

            $user->setResetPasswordToken($token);
            $user->save();

            $mailer = new Mailer($dw_config);
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $passwordResetLink = $protocol . '://' . $dw_config['domain'] . '/account/reset-password/' . $token;

            $mailBody = $mailer->renderBody($app, 'password-reset.twig', array(
                'name'                => $user->guessName(),
                'password_reset_link' => $passwordResetLink
            ));

            $mailer->sendSupportMail(
                $user->getEmail(),
                __('Datawrapper: You requested a reset of your password'),
                $mailBody
            );

            ok(__('You should soon receive an email with further instructions.'));

        } else {
            error('login-email-unknown', __('The email is not registered yet.'));
        }
    }

    /**
     * reet the user password
     */
    public function resetPasswordAction() {
        $payload = json_decode($app->request()->getBody());
        if (!empty($payload->token)) {
            $user = UserQuery::create()->getUserByPwdResetToken($payload->token);
            if (!empty($user)) {
                if (!empty($payload->pwd)) {
                    // update password
                    $user->setPwd($payload->pwd);
                    $user->setResetPasswordToken('');
                    $user->setActivateToken('');
                    $user->save();
                    ok();
                } else {
                    error('empty-password', __('The password must not be empty.'));
                }
            } else {
                error('invalid-token', __('The supplied token for password resetting is invalid.'));
            }
        }
    }

    /**
     * endpoint for re-sending the activation link to a user
     */
    public function resendActivationAction() {
        $user = Session::getUser();
        $token = $user->getActivateToken();
        if (!empty($token)) {
            // check how often the activation email has been send
            // we don't want to send it too often in order to prevent
            // mail spam coming from our server
            $r = ActionQuery::create()->filterByUser($user)
                ->filterByKey('resend-activation')
                ->find();
            if (count($r) > 2) {
                error('avoid-spam', str_replace('%support_email%', $dw_config['email']['support'], __('You already resent the activation mail three times, now. Please <a href="mailto:%support_email%">contact an administrator</a> to proceed with your account activation.')));
                return false;
            }

            // remember that we send the email
            Action::logAction($user, 'resend-activation', $token);

            // send email with activation key
            $domain   = $dw_config['domain'];
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $activationLink = $protocol . '://' . $domain . '/account/activate/' . $token;

            $mailer   = new Mailer($dw_config);
            $mailBody = $mailer->renderBody($app, 'activation.twig', array(
                'name'            => $user->guessName(),
                'activation_link' => $activationLink
            ));

            $mailer->sendSupportMail(
                $user->getEmail(),
                __('Datawrapper: Please activate your email address'),
                $mailBody
            );

            ok(__('The activation email has been send to your email address, again.'));

        } else {
            error('token-empty', __('You\'re account is probably already activated.'));
        }
    }

    /**
     * endpoint for sending a new invitation to a user
     *
     * expects payload { "email": "validemail@domain.tld" }
     */
    public function resendInvitationAction() {
        $payload = json_decode($app->request()->getBody());
        $user    = UserQuery::create()->findOneByEmail($payload->email);
        $token   = $user->getActivateToken();
        if (!empty($user)) {
            if (empty($token)) {
                return error("token-invalid", _("This activation token is invalid. Your email address is probably already activated."));
            }

            $domain         = $dw_config['domain'];
            $protocol       = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $invitationLink = $protocol . '://' . $domain . '/account/invite/' . $token;

            $mailer   = new Mailer($dw_config);
            $mailBody = $mailer->renderBody($app, 'invitation.twig', array(
                'name'            => $user->guessName(),
                'invitation_link' => $invitationLink
            ));

            $mailer->sendSupportMail(
                $user->getEmail(),
                __('You have been invited to Datawrapper!'),
                $mailBody
            );

            ok(__('You should soon receive an email with further instructions.'));
        }
        else {
            error('login-email-unknown', __('The email is not registered yet.'));
        }
    }

    /**
     * endpoint for validating an invitation. The user sends his new password
     */
    public function validateInvitationAction() {
        $data = json_decode($app->request()->getBody());
        if (!empty($token)) {
            $users = UserQuery::create()
              ->filterByActivateToken($token)
              ->find();
            if (count($users) != 1) {
                error("token-invalid", _("This activation token is invalid. Your email address is probably already activated."));
            } elseif (empty($data->pwd1)) {
                error("password-missing", _("You must enter a password."));
            } elseif ($data->pwd1 != $data->pwd2) {
                error("password-mismatch", _("Both passwords must be the same."));
            } else {
                $user = $users[0];
                $user->setActivateToken('');
                $user->setPwd($data->pwd1);
                $user->save();
                // NOTE: we don't need a confirmation.
                # send confirmation email
                // $name   = $user->getEmail();
                // $domain = $GLOBALS['dw_config']['domain'];
                // $from   = $GLOBALS['dw_config']['email'];
                // $link = 'http://' . $domain;
                // include('../../lib/templates/confirmation-email.php');
                // mail($name, _('Confirmation of account creation') . ' ' . $domain, $confirmation_email, 'From: ' . $from);
                Session::login($user);
                ok();
            }
        }
    }
}
