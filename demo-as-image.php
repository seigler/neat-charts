<?php
require_once 'vendor/autoload.php';

function randomData($count = 20, $offsetMax = 100) {
  $randomData = [];
  $duration = 60 * 5 + rand() * 60 * 60 * 24;
  $begin = time() - $duration;
  $offset = $offsetMax * (rand()/getRandMax())**2;
  $scale = max(0.25 * $offset, 100 * rand() / getRandMax());
  $volatility = 0.25 * (rand()/getRandMax())**3 + 0.25;
  for ($n = 0, $current = $offset + 0.5 * $scale; $n < $count; $n++) {
    $current -= $offset;
    $current *= 1 + $volatility * (rand()/getRandMax() - 0.5);
    $current += $offset;
    $randomData[$begin + $duration / $count * $n] = $current;
  }
  return $randomData;
}

$chart = new NeatCharts\LineChart(randomData(), [
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
