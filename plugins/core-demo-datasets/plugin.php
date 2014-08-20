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

class DatawrapperPlugin_CoreDemoDatasets extends Plugin {
    public function init() {
        $plugin = $this;
        foreach ($this->getDemoDatasets() as $key => $dataset) {
            Hooks::register(Hooks::GET_DEMO_DATASETS, function() use ($dataset) {
                return $dataset;
            });
        }
    }

    function getDemoDatasets() {
        $datasets = array();

        $datasets[] = array(
            'id' => 'youth-unemployment',
            'title' => __('Youth Unemployment in Europe'),
            'type' => __('Line chart'),
            'presets' => array(
                'type' => 'line-chart',
                'metadata.describe.source-name' => 'Eurostat',
                'metadata.describe.source-url' => 'http://appsso.eurostat.ec.europa.eu/nui/show.do?query=BOOKMARK_DS-055624_QID_91D6DBE_UID_-3F171EB0&layout=TIME,C,X,0;GEO,L,Y,0;S_ADJ,L,Z,0;AGE,L,Z,1;SEX,L,Z,2;INDICATORS,C,Z,3;&zSelection=DS-055624AGE,Y_LT25;DS-055624SEX,T;DS-055624S_ADJ,SA;DS-055624INDICATORS,OBS_FLAG;&rankName1=SEX_1_2_-1_2&rankName2=AGE_1_2_-1_2&rankName3=TIME_1_0_0_0&rankName4=S-ADJ_1_2_-1_2&rankName5=INDICATORS_1_2_-1_2&rankName6=GEO_1_2_0_1&sortR=ASC_361_FIRST&pprRK=FIRST&pprSO=PROTOCOL&ppcRK=FIRST&ppcSO=ASC&sortC=ASC_-1_FIRST&rStp=&cStp=&rDCh=&cDCh=&rDM=true&cDM=true&footnes=false&empty=false&wai=false&time_mode=NONE&lang=EN&cfo=%23%23%23%2C%23%23%23.%23%23%23',
                'metadata.data.vertical-header' => true,
                'metadata.data.transpose' => true
            ),
            'data' => rtrim(file_get_contents(__DIR__.'/data/youth-unemployment.csv'))
        );

        $datasets[] = array(
            'id' => 'us-trade',
            'title' => __('US Trade with United Kingdom'),
            'type' => __('Line chart'),
            'presets' => array(
                'type' => 'line-chart',
                'metadata.describe.source-name' => 'US Census Bureau',
                'metadata.describe.source-url' => 'http://www.census.gov/foreign-trade/balance/c4120.html',
                'metadata.data.vertical-header' => true,
                'metadata.describe.number-format' => 'n1',
                'metadata.describe.number-divisor' => '3',
                'metadata.describe.number-append' => ' Billion USD',
                'metadata.visualize.sort-values' => false,
                'metadata.data.transpose' => false
            ),
            'data' => rtrim(file_get_contents(__DIR__.'/data/us-trade.csv'))
        );

        $datasets[] = array(
            'id' => 'marriages',
            'title' => __('Marriages in Germany'),
            'chartid' => '',
            'type' => __('Line chart'),
            'presets' => array(
                'type' => 'line-chart',
                'metadata.describe.source-name' => 'Statistisches Bundesamt',
                'metadata.describe.source-url' => 'http://destatis.de',
                'metadata.data.vertical-header' => true,
                'metadata.describe.number-format' => 'n1',
                'metadata.data.number-append' => '',
                'metadata.data.number-divisor' => '1',
                'metadata.data.transpose' => false
            ),
            'data' => __('Year')."\t".__('Marriages')."\n1946\t8.1\n1947\t9.8\n1948\t10.5\n1949\t10.2\n1950\t11.0\n1951\t10.4\n1952\t9.5\n1953\t8.9\n1954\t8.7\n1955\t8.8\n1956\t8.9\n1957\t8.9\n1958\t9.1\n1959\t9.2\n1960\t9.5\n1961\t9.5\n1962\t9.4\n1963\t8.8\n1964\t8.5\n1965\t8.2\n1966\t8.0\n1967\t7.9\n1968\t7.3\n1969\t7.4\n1970\t7.4\n1971\t7.2\n1972\t7.0\n1973\t6.7\n1974\t6.5\n1975\t6.7\n1976\t6.5\n1977\t6.5\n1978\t6.0\n1979\t6.2\n1980\t6.3\n1981\t6.2\n1982\t6.2\n1983\t6.3\n1984\t6.4\n1985\t6.4\n1986\t6.6\n1987\t6.7\n1988\t6.8\n1989\t6.7\n1990\t6.5\n1991\t5.7\n1992\t5.6\n1993\t5.5\n1994\t5.4\n1995\t5.3\n1996\t5.2\n1997\t5.2\n1998\t5.1\n1999\t5.2\n2000\t5.1\n2001\t4.7\n2002\t4.8\n2003\t4.6\n2004\t4.8\n2005\t4.7\n2006\t4.5\n2007\t4.5\n2008\t4.6\n2009\t4.6\n2010\t4.7\n2011\t4.6
    "
        );

        $datasets[] = array(
            'id' => 'felix',
            'title' => __('Fearless Felix: How far did he fall'),
            'type' => __('Bar chart'),
            'presets' => array(
                'type' => 'column-chart',
                'metadata.describe.source-name' => 'DataRemixed',
                'metadata.describe.source-url' => 'http://dataremixed.com/2012/10/a-tribute-to-fearless-felix/',
                'metadata.data.vertical-header' => true,
                'metadata.describe.number-format' => 'n1',
                'metadata.describe.number-append' => ' km',
                'metadata.describe.number-divisor' => '3',
                'metadata.data.transpose' => false
            ),
            'data' => rtrim(file_get_contents(__DIR__.'/data/felix.csv'))
        );

        $datasets[] = array(
            'id' => 'new-borrowing',
            'type' => __('Bar chart'),
            'presets' => array(
                'type' => 'column-chart',
                'metadata.describe.source-name' => 'BMF, Haushaltsausschuss',
                'metadata.describe.source-url' => 'http://www.bundesfinanzministerium.de/bundeshaushalt2012/pdf/finanzplan.pdf',
                'metadata.data.transpose' => true
            ),
            'title' => __('Net borrowing of Germany'),
            'data' => '"'.__('Year').'","2007","2008","2009","2010","2011","2012","2013","2014","2015","2016"'."\n".
            '"'.__('New debt in Bio.').'","14.3","11.5","34.1","44","17.3","34.8","19.6","14.6","10.3","1.1"'
        );

        $datasets[] = array(
            'id' => 'german-parliament',
            'title' => __('Women in German Parliament'),
            'type' => __('Bar chart (grouped)'),
            'presets' => array(
                'type' => 'column-chart',
                'metadata.describe.source-name' => 'Bundestag',
                'metadata.describe.source-url' => 'http://www.bundestag.de/bundestag/abgeordnete17/mdb_zahlen/frauen_maenner.html',
                'metadata.data.vertical-header' => true,
                'metadata.visualize.sort-values' => true
            ),
            'data' => __('Party')."\t".__('Women')."\t".__('Men')."\t".__('Total')."
CDU/CSU\t45\t192\t237
SPD\t57\t89\t146
FDP\t24\t69\t93
LINKE\t42\t34\t76
GRÜNE\t36\t32\t68
    "
        );

        return $datasets;
    }
}
