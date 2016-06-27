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

$start = 0.01;// * rand()/getRandMax();
$volatility = 0.002;

for ($n = 0, $current = $start; $n < 50; $n++) {
  $current += $volatility * (rand()/getRandMax() - 0.5)**3;
  $chartData[$n] = $current;
}

print SVGChartBuilder::renderStockChart($chartData);
