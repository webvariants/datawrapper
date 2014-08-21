<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Datawrapper\Application;
use Datawrapper\Visualization;

class DatawrapperPlugin_VisualizationColumnCharts extends DatawrapperPlugin_Visualization {
    public function init(Application $app) {
        Visualization::register($this, $this->getMeta_Simple());
        Visualization::register($this, $this->getMeta_Grouped());
        Visualization::register($this, $this->getMeta_Stacked());
    }

    public function getMeta_Simple() {
        $id = $this->getName();

        return array(
            'id'         => 'column-chart',
            'title'      => __('Column Chart', $id),
            'version'    => $this->getVersion(),
            'dimensions' => 1,
            'extends'    => 'raphael-chart',
            'order'      => 9,
            'axes'       => array(
                'labels' => array(
                    'accepts' => array('text', 'date')
                ),
                'columns' => array(
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
                    'label' => __('Automatically sort bars', $id)
                ),
                'reverse-order' => array(
                    'type'  => 'checkbox',
                    'label' => __('Reverse order', $id)
                ),
                'negative-color' => array(
                    'type'  => 'checkbox',
                    'label' => __('Use different color for negative values', $id),
                    'depends-on' => array(
                        'chart.min_value[columns]' => '<0'
                    )
                ),
                'ignore-missing-values' => array(
                    'type'    => 'checkbox',
                    'label'   => __('Ignore missing values', $id),
                    'default' => false
                ),
                'absolute-scale' => array(
                    'type'       => 'checkbox',
                    'label'      => __('Use the same scale for all columns', $id),
                    'depends-on' => array(
                        'chart.min_columns[columns]' => 2
                    )
                ),
                'grid-lines' => array(
                    'type'    => 'radio-left',
                    'label'   => __('Grid lines', $id),
                    'options' => array(
                        array('value' => 'auto', 'label' => __('Automatic', $id)),
                        array('value' => 'show', 'label' => __('Show', $id)),
                        array('value' => 'hide', 'label' => __('Hide', $id))
                    ),
                    'default' => false
                )
            )
        );
    }

    public function getMeta_Grouped() {
        $id = $this->getName();

        return array(
            'id'         => 'grouped-column-chart',
            'title'      => __('Grouped Column Chart', $id),
            'version'    => $this->getVersion(),
            'dimensions' => 2,
            'extends'    => 'raphael-chart',
            'color-by'   => 'row',
            'order'      => 10,
            'axes'       => array(
                'labels' => array(
                    'accepts' => array('text', 'date')
                ),
                'columns' => array(
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
                    'label' => __('Automatically sort bars', $id)
                ),
                'reverse-order' => array(
                    'type'  => 'checkbox',
                    'label' => __('Reverse order', $id)
                ),
                'negative-color' => array(
                    'type'       => 'checkbox',
                    'label'      => __('Use different color for negative values', $id),
                    'depends-on' => array(
                        'chart.min_value[columns]' => '<0'
                    )
                )
            ),
            'libraries' => array()
        );
    }

    public function getMeta_Stacked() {
        $id = $this->getName();

        return array(
            'id'         => 'stacked-column-chart',
            'title'      => __('Stacked Column Chart', $id),
            'version'    => $this->getVersion(),
            'dimensions' => 2,
            'extends'    => 'grouped-column-chart',
            'color-by'   => 'row',
            'order'      => 11,
            'axes'       => array(
                'labels' => array(
                    'accepts' => array('text', 'date')
                ),
                'columns' => array(
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
                    'label' => __('Automatically sort bars', $id)
                ),
                'reverse-order' => array(
                    'type'  => 'checkbox',
                    'label' => __('Reverse order', $id)
                ),
                'negative-color' => array(
                    'type'  => 'checkbox',
                    'label' => __('Use different color for negative values', $id)
                ),
                'normalize' => array(
                    'type'    => 'checkbox',
                    'label'   => __('Stack percentages', $id),
                    'default' => false
                ),
                'normalize-user' => array(
                    'type'       => 'checkbox',
                    'label'      => __('Let user switch mode'),
                    'depends-on' => array(
                        'normalize' => true
                    )
                )
            ),
            'locale' => array(
                'stack percentages'        => __('stack percentages', $id),
                'cannotShowNegativeValues' => __('Negative values, as contained in your dataset, cannot be stacked on top of each other in a stacked column chart. Please consider using a different chart type instead (eg. a grouped column chart).', $id)
            )
        );
    }
}
