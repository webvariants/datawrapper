<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class DatawrapperPlugin_VisualizationBarChart extends DatawrapperPlugin_Visualization {
    public function getMeta() {
        $id = $this->getName();

        return array(
            'id'         => 'bar-chart',
            'title'      => __('Bar Chart', $id),
            'version'    => '1.3.2',
            'extends'    => 'raphael-chart',
            'order'      => 5,
            'dimensions' => 1,
            'axes' => array(
                'labels' => array(
                    'accepts' => array('text', 'date')
                ),
                'bars' => array(
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
                    'type'  => 'checkbox',
                    'label' => __('Autmatically sort bars', $id)
                ),
                'reverse-order' => array(
                    'type'  => 'checkbox',
                    'label' => __('Reverse order', $id),
                ),
                'negative-color' => array(
                    'type'  => 'checkbox',
                    'label' => __('Use different color for negative values', $id),
                    'depends-on' => array(
                        'chart.min_value[columns]' => '<0'
                    )
                ),
                'absolute-scale' => array(
                    'type'  => 'checkbox',
                    'label' => __('Use the same scale for all columns', $id)
                ),
                'filter-missing-values' => array(
                    'type'    => 'checkbox',
                    'default' => true,
                    'label'   => __('Filter missing values', $id)
                )
            ),
            'libraries' => array()
        );
    }
}
