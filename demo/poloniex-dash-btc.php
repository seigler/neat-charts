<?php
//ini_set('display_errors', 1);

Header('Content-type: image/svg+xml; charset=utf-8');
Header('Content-Disposition: inline; filename="Dash-24h-chart-' . date('Y-m-d\THisT') . '.svg"');
require 'buffer.php';
require '../src/NeatCharts/LineChart.php'; // really just use composer instead

function getJson($url) {
  if (empty($url)) {
    trigger_error('Missing or empty JSON url');
  }
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_TIMEOUT, 3);
//  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
//  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
//  curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "/VeriSignClass3PublicPrimaryCertificationAuthority-G5.pem");

  $result = curl_exec($ch);
  $result = json_decode($result) or trigger_error('Couldn\'t parse JSON');
  return $result;
}

$yesterday = time() - (24 * 60 * 60);
$poloniex_url = 'https://poloniex.com/public?command=returnChartData&currencyPair=BTC_DASH&start=' . $yesterday . '&end=9999999999&period=' .
//300 //5 min
900 //15 min
//1800 //30 min
//7200 //3h
//14400 //6h
//86400 //1d
;
$last24h = getJson($poloniex_url);
$chartData = [];

foreach ($last24h as $item) {
  $chartData[$item->date] = $item->weightedAverage;
}

$poloniexChart = new NeatCharts\LineChart($chartData, [
  'width'=>800,
  'height'=>250,
  'lineColor'=>"#1C75BC", // Dash blue
  'labelColor'=>"#777",
  'smoothed'=>false
]);
print $poloniexChart->render();
