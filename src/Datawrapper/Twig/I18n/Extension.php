<?php

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
