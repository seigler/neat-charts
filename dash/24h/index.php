<?php
//ini_set('display_errors', 1);
include '../../buffer.php';
include '../../cms_chart.php';

spl_autoload_register(function ($class_name) {
    include '../../' . $class_name . '.php';
});

$yesterday = time() - (24 * 60 * 60);
$poloniex_url = 'https://poloniex.com/public?command=returnChartData&currencyPair=BTC_DASH&start=' . $yesterday . '&end=9999999999&period=' .
//300
//900
1800
//7200
//14400
//86400
;
//print $poloniex_url;

$dateformat = 'gA';
$last24h = Util::getJson($poloniex_url);
$averages = [];

foreach ($last24h as $datum) {
  $averages[date($dateformat, $datum->date)] = $datum->weightedAverage;
}

$chartConfig = array(
  'chart' => 'line',
  'css' => '1',
  'xKey' => 'Hour',
  'xFormat' => 'date|' . $dateformat,
  'xSkip' => '1',
  'yFormat' => 'format|6|.',
  'yKey' => 'BTC',
);

Header('Content-type: image/svg+xml; charset=utf-8');
print '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
cms_chart($averages, $chartConfig);

