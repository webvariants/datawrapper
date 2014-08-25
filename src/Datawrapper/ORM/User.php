<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\ORM;

use Datawrapper\ORM\om\BaseUser;
use BasePeer;

class User extends BaseUser {
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false) {
        $arr = parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);
        if (isset($arr['Pwd'])) unset($arr['Pwd']);  // never transmit passwords
        if (isset($arr['Token'])) unset($arr['Token']);  // never transmit passwords
        // unset($arr['Role']);  // never transmit passwords
        return $arr;
    }

    public function isLoggedIn() {
        return $this->getRole() != UserPeer::ROLE_GUEST;
    }

    public function isAdmin() {
        return in_array($this->getRole(), array(UserPeer::ROLE_ADMIN, UserPeer::ROLE_SYSADMIN));
    }

    public function isGraphicEditor() {
        return $this->getRole() == UserPeer::ROLE_GRAPHIC_EDITOR;
    }

    public function isSysAdmin() {
        return $this->getRole() == UserPeer::ROLE_SYSADMIN;
    }

    public function isAbleToPublish() {
        return DatawrapperHooks::hookRegistered(DatawrapperHooks::PUBLISH_FILES);
    }

    public function hasCharts() {
        return $this->chartCount() > 0;
    }

    public function chartCount() {
        return ChartQuery::create()
            ->filterByAuthorId($this->getId())
            ->filterByDeleted(false)
            ->filterByLastEditStep(array('min' => 2))
            ->count();
    }

    public function publicChartCount() {
        return ChartQuery::create()
            ->filterByAuthorId($this->getId())
            ->filterByDeleted(false)
            ->filterByLastEditStep(array('min' => 4))
            ->count();
    }

    public function setPwd($pwd) {
        return parent::setPwd(secure_password($pwd));
    }

    /*
     * this deletes all information stored by the user and
     * makes it impossible to login again
     */
    public function erase() {
        $u = $this;
        $u->setEmail('DELETED');
        $u->setName('');
        $u->setWebsite('');
        $u->setSmProfile('');
        $u->setActivateToken('');
        $u->setResetPasswordToken('');
        $u->setPwd('');
        $u->setDeleted(true);
        $u->save();
    }

    public function guessName() {
        $n = $this->getName();
        if (empty($n)) $n = $this->getEmail();
        if (empty($n)) $n = $this->getOAuthSignIn();
        if (!empty($n) && strpos($n, '::') > 0) $n = substr($n, strpos($n, '::')+2);
        if (empty($n)) $n = __('User').' '.$this->getId();
        return $n;
    }

    public function getRecentCharts($count=10) {
        return ChartQuery::create()
            ->filterByUser($this)
            ->filterByDeleted(false)
            ->filterByLastEditStep(array("min" => 3))
            ->orderByLastModifiedAt('desc')
            ->limit($count)
            ->find();
    }

    /*
     * returns the currently selected organization
     */
    public function getCurrentOrganization() {
        $organizations = $this->getOrganizations();
        if (count($organizations) < 1) return null;
        if (!empty($_SESSION['dw-user-organization'])) {
            foreach ($organizations as $org) {
                if ($org->getId() == $_SESSION['dw-user-organization']) {
                    return $org;
                }
            }
        }
        return $organizations[0];
    }

    /*
     * returns an Array serialization with less
     * sensitive information than $user->toArray()
     */
    public function serialize() {
        return array(
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'name' => $this->getName(),
            'website' => $this->getWebsite(),
            'socialmedia' => $this->getSmProfile()
        );
    }

	public function hasProduct(Product $product) {
		return UserProductsQuery::create()
			->filterByProduct($product)
			->filterByUser($this)
			->count() > 0;
    }
}

