<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\Publishing;

use Datawrapper\ORM\Chart;

class FilesystemStatus {
    public function set(Chart $chart, $status) {
        file_put_contents($this->getFilename($chart), $status);
    }

    public function get(Chart $chart) {
        $filename = $this->getFilename($chart);

        return file_exists($filename) ? file_get_contents($filename) : false;
    }

    public function clear(Chart $chart) {
        @unlink($this->getFilename($chart));
    }

    protected function getFilename(Chart $chart) {
        return ROOT_PATH.'charts/tmp/publish-status-'.$chart->getID();
    }
}
