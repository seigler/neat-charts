<?php
require_once 'vendor/autoload.php';

$chartData = [];
$offset = 100 * (rand()/getRandMax())**4;
$scale = 100 * (rand()/getRandMax())**2;
$volatility = 0.5 * (rand()/getRandMax())**3;
for ($n = 0, $current = $offset + 0.5 * $scale; $n < 24; $n++) {
  $current -= $offset;
  $current *= 1 + $volatility * (rand()/getRandMax() - 0.5);
  $current += $offset;
  $chartData[$n] = $current;
}

$chart = new NeatCharts\LineChart($chartData, [
  'width'=>500,
  'height'=>400,
  'lineColor'=>'#00F',
  'labelColor'=>'#222',
  'smoothed'=>false,
  'fontSize'=>14
]);

header('Content-type: image/svg+xml; charset=utf-8');
echo '<?xml version="1.0" standalone="no"?>' . PHP_EOL;
echo $chart->render();
?>
