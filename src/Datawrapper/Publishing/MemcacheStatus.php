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

class MemcacheStatus {
    protected $memcache;

    public function __construct(Memcache $memcache) {
        $this->memcache = $memcache;
    }

    public function set(Chart $chart, $status) {
        $this->memcache->set($this->getKey($chart), round($status*100));
    }

    public function get(Chart $chart) {
        return $this->memcache->get($this->getKey($chart));
    }

    public function clear(Chart $chart) {
        $this->memcache->delete($this->getKey($chart));
    }

    protected function getKey(Chart $chart) {
        return 'publish-status-'.$chart->getID();
    }
}
