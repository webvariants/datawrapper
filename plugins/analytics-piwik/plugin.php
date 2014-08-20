<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Plugin;
use Datawrapper\Hooks;

class DatawrapperPlugin_AnalyticsPiwik extends Plugin {
    public function init() {
        Hooks::register(Hooks::CHART_AFTER_BODY, array($this, 'getTrackingCode'));
        Hooks::register(Hooks::CORE_AFTER_BODY,  array($this, 'getTrackingCode'));
    }

    public function getTrackingCode($chart = null) {
        $config = $this->getConfig();
        if (empty($config)) return false;

        $url = $config['url'];
        $idSite = $config['idSite'];

        $user = $chart->getUser();

        print '<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
  _paq.push(["setCookieDomain", "*.www.datawrapper.de"]);
  '.(is_a($chart, 'Chart') && $user ?
 '_paq.push(["setCustomVariable", 1, "Layout", "'.$chart->getTheme().'", "page"]);
  _paq.push(["setCustomVariable", 2, "Author", "'.$user->getId().'", "page"]);
  _paq.push(["setCustomVariable", 3, "Visualization", "'.$chart->getType().'", "page"]);
  ' : '').'
  _paq.push(["trackPageView"]);
  _paq.push(["enableLinkTracking"]);

  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://' . $url . '/";
    _paq.push(["setTrackerUrl", u+"piwik.php"]);
    _paq.push(["setSiteId", "1"]);
    var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
    g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Piwik Code -->';
    }
}
