<?php
//ini_set('display_errors', 1);

Header('Content-type: image/svg+xml; charset=utf-8');
Header('Content-Disposition: inline; filename="Dash-24h-chart-' . date('Y-m-d\THisT') . '.svg"');
include 'buffer.php';

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$yesterday = time() - (24 * 60 * 60);
$poloniex_url = 'https://poloniex.com/public?command=returnChartData&currencyPair=BTC_DASH&start=' . $yesterday . '&end=9999999999&period=' .
//300 //5 min
900 //15 min
//1800 //30 min
//7200 //3h
//14400 //6h
//86400 //1d
;
$last24h = Util::getJson($poloniex_url);
$chartData = [];

foreach ($last24h as $item) {
  $chartData[$item->date] = $item->weightedAverage;
}

print SVGChartBuilder::renderStockChart($chartData, [
  'width'=>800,
  'height'=>250,
  'lineColor'=>"#1C75BC",
  'labelColor'=>"#777",
  'smoothed'=>false
]);
