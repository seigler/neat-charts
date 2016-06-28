<?php
/*

This file is intended to be a testing ground in case
you want to adjust the parameters of these charts.
That way you can make lots of changes without
hammering Poloniex half to death.

*/

ini_set('display_errors', 1);
//include 'buffer.php';

Header('Content-type: image/svg+xml; charset=utf-8');

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$chartData = [];

$start = 100 * (rand()/getRandMax())**3;
$volatility = rand()/getRandMax() + 0.01;
$velocity = (rand()/getRandMax() - 0.5);
$acceleration = 0.1 * (rand()/getRandMax())**2;

for ($n = 0, $current = $start; $n < 24; $n++) {
  $velocity *= 0.5;
  $velocity += $acceleration * 2 * (rand()/getRandMax() - 0.5);
  $current += $velocity;
  $chartData[$n] = $current;
}

print SVGChartBuilder::renderStockChart($chartData,  [
  'width'=>1000,
  'lineColor'=>"#708",
  'labelColor'=>"#777",
  'smoothed'=>true
]);
