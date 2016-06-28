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

$offset = 100 * (rand()/getRandMax())**4;
$scale = 100 * (rand()/getRandMax())**4;
$volatility = 0.5 * (rand()/getRandMax())**3;

for ($n = 0, $current = $offset + 0.5 * $scale; $n < 96; $n++) {
  $current -= $offset;
  $current *= 1 + $volatility * (rand()/getRandMax() - 0.5);
  $current += $offset;
  $chartData[$n] = $current;
}

print SVGChartBuilder::renderStockChart($chartData, 1000, "#708", "#777", false);
