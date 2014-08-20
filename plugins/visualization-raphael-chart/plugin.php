<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class DatawrapperPlugin_VisualizationRaphaelChart extends DatawrapperPlugin_Visualization {
    public function getMeta() {
        $asset_domain = $GLOBALS['dw_config']['asset_domain'];
        $asset_url    = '//'.$asset_domain.'/';

        return array(
            'id'        => 'raphael-chart',
            'libraries' => array(
                array(
                    'local' => 'vendor/d3-light.min.js',
                    'cdn'   => !empty($asset_domain)
                        ? $asset_url.'vendor/d3-light/3.1.7/d3-light.min.js'
                        : null
                ),
                array(
                    'local' => 'vendor/chroma.min.js',
                    'cdn'   => !empty($asset_domain)
                        ? $asset_url.'vendor/chroma-js/0.5.4/chroma.min.js'
                        : null
                ),
                array(
                    'local' => 'vendor/raphael-2.1.2.min.js',
                    'cdn'   => !empty($asset_domain)
                        ? $asset_url.'vendor/raphael-js/2.1.2/raphael-min.js'
                        : null
                )
            )
        );
    }
}
