<?php

/**
 * adding translate function to global scope
 */
function __($text, $domain = false, $fallback = '') {
    global $__l10n;
    return $__l10n->translate($text, $domain, $fallback);
}

/**
 * parses the config and populates some defaults
 */
function parse_config(array $cfg) {
    // check that email adresses are set
    if (!isset($cfg['email']))
        $cfg['email'] = array();

    if (!isset($cfg['email']['support']))
        $cfg['email']['support'] = 'support@' . $cfg['domain'];

    if (!isset($cfg['email']['log']))
        $cfg['email']['log'] = 'admin@' . $cfg['domain'];

    if (!isset($cfg['email']['error']))
        $cfg['email']['error'] = 'error@' . $cfg['domain'];

    if (!isset($cfg['asset_domain']))
        $cfg['asset_domain'] = false;

    return $cfg;
}
