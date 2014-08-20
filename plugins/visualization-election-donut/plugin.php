<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class DatawrapperPlugin_VisualizationElectionDonut extends DatawrapperPlugin_Visualization {
    public function getMeta() {
        return array(
            'id'         => 'election-donut-chart',
            'title'      => __('Election Donut', $this->getName()),
            'version'    => '1.3.0',
            'extends'    => 'donut-chart',
            'dimensions' => 1,
            'order'      => 60,
            'axes'       => array(
                'labels' => array(
                    'accepts' => array('text', 'date')
                ),
                'slices' => array(
                    'accepts'  => array('number'),
                    'multiple' => true
                )
            ),
            'options' => array(
                'base-color' => array(
                    'type'  => 'base-color',
                    'label' => __('Base color')
                ),
                'sort-values' => array(
                    'type'    => 'checkbox',
                    'label'   => __('Sort by size', $this->getName()),
                    'default' => true
                )
            )
        );
    }
}
