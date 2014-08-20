<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\Twig\I18n;

/**
 * Custom i18n extension for Twig
 *
 * This uses our own __() function instead of the gettext function.
 */
class Extension extends \Twig_Extensions_Extension_I18n {
    public function getTokenParsers() {
        return array(new Parser());
    }

    public function getFilters() {
        return array(
            'trans' => new \Twig_Filter_Function('__'),
        );
    }
}
