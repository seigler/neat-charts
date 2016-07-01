<?php
require_once 'vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
  <title>Demo Page : seigler/neat-charts</title>
  <style>
    *, *:after, *:before { box-sizing: inherit; }
  </style>
</head>
<body>
  <header>
    <h1>neat-charts examples</h1>
  </header>
  <main>
    <section>
      <h2>SVG chart in <code>img</code> tag</h2>
      <figure>
        <img src="./demo-as-image.php">
        <figcaption>Random generated data, loaded as an image</figcaption>
      </figure>
    </section>
    <section>
      <h2>SVG chart in <code>svg</code> tag</h2>
      <figure>
        <?php
$chartData = [];
$offset = 100 * (rand()/getRandMax())**4;
$scale = 100 * (rand()/getRandMax())**2;
$volatility = 0.5 * (rand()/getRandMax())**3;
for ($n = 0, $current = $offset + 0.5 * $scale; $n < 96; $n++) {
  $current -= $offset;
  $current *= 1 + $volatility * (rand()/getRandMax() - 0.5);
  $current += $offset;
  $chartData[$n] = $current;
}

$chart = new NeatCharts\LineChart($chartData, [
  'width'=>800,
  'height'=>250,
  'lineColor'=>'#F00',
  'labelColor'=>'#222',
  'smoothed'=>false,
  'fontSize'=>14
]);
echo $chart->render();
?>
        <figcaption>Random generated data, loaded right in the page</figcaption>
      </figure>
    </section>
  </main>
</body>
</html>
