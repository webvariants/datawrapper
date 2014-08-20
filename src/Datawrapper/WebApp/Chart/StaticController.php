<?php

namespace Datawrapper\WebApp\Chart;

use Datawrapper\WebApp\BaseController;

class StaticController extends BaseController {
    /**
     * VISUALIZE STEP
     */
    public function staticAction($chartID) {
        $app   = $this->disableCache()->getApp();
        $query = $app->request();

        check_chart_public($chartID, function($user, $chart) use ($query) {
            $w      = $query->params('w') ?: 300;
            $h      = $query->params('h') ?: 220;
            $format = $query->params('f') ?: 'png';

            $out = $chart->getId().'-'.$w.'-'.$h.'.'.$format;

            $img_dir = ROOT_PATH.'charts/images/';
            $script  = ROOT_PATH.'scripts/render.js';
            $cmd     = PHANTOMJS.' '.$script.' '.$chart->getId().' '.$img_dir.$out.' '.$w.' '.$h;

            if ($format == 'png') {
                header('Content-Type: image/png');
            }
            else {
                $title = trim(strtolower($chart->getTitle()));
                $name  = $chart->getId().'-'.preg_replace('/[äöüa-z0-9ß]+/', '-', $title).'.pdf';

                header('Content-Disposition: attachment;filename="' . $name . '"');
                header('Content-Type: application/pdf');
            }

            if (!file_exists($img_dir.$out)) {
                exec($cmd);
            }

            $fp = fopen($img_dir.$out, 'rb');
            fpassthru($fp);
            exit;
        });
    }
}
