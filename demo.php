<?php
require_once 'vendor/autoload.php';

function randomData($count = 96, $offsetMax = 100) {
  $randomData = [];
  $offset = $offsetMax * (rand()/getRandMax())**2;
  $scale = max(0.25 * $offset, 100 * rand() / getRandMax());
  $volatility = 0.5 * (rand()/getRandMax())**3 + 0.25;
  for ($n = 0, $current = $offset + 0.5 * $scale; $n < $count; $n++) {
    $current -= $offset;
    $current *= 1 + $volatility * (rand()/getRandMax() - 0.5);
    $current += $offset;
    $randomData[$n] = $current;
  }
  return $randomData;
}

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
      <h2>Chart in <code>img</code> tag</h2>
      <figure>
        <img src="./demo-as-image.php">
        <figcaption>Random generated data, loaded as an image</figcaption>
      </figure>
    </section>
    <section>
      <h2>Chart in <code>svg</code> tag, zero axis shown, filled</h2>
      <figure>
<?php
$chart = new NeatCharts\LineChart(randomData(), [
  'width'=>800,
  'height'=>250,
  'lineColor'=>'#F00',
  'labelColor'=>'#222',
  'smoothed'=>false,
  'fontSize'=>14,
  'yAxisZero'=>true,
  'filled'=>true
]);
echo $chart->render();
?>
        <figcaption>Random generated data, loaded right in the page</figcaption>
      </figure>
    </section>
    <section>
      <h2>Smoothed chart in <code>svg</code> tag</h2>
      <figure>
<?php
$chart = new NeatCharts\LineChart(randomData(12), [
  'width'=>400,
  'height'=>300, // null works as a height too, it picks a height that makes the plot look good
  'lineColor'=>'#080',
  'labelColor'=>'#222',
  'smoothed'=>true,
  'fontSize'=>14
]);
echo $chart->render();
?>
        <figcaption>Random generated data, loaded right in the page</figcaption>
      </figure>
    </section>
    <section>
      <h2>Sparkline in <code>svg</code> tag</h2>
      <figure>
        <?php
$chart = new NeatCharts\LineChart(randomData(48), [
  'width'=>100,
  'height'=>20,
  'lineColor'=>'#000',
  'markerColor'=>'#F00',
  'smoothed'=>false,
  'fontSize'=>4,
  'yAxisEnabled'=>false,
  'xAxisEnabled'=>false
]);
echo $chart->render();
?>
        <figcaption>Random generated data, loaded right in the page</figcaption>
      </figure>
    </section>
    <section>
      <h2>Bar chart in <code>svg</code> tag</h2>
      <figure>
<?php
        $chart = new NeatCharts\BarChart(randomData(10, 0), [
          'barColor'=>'#409'
        ]);
        echo $chart->render();
?>
        <figcaption>Random generated data, loaded right in the page</figcaption>
      </figure>
    </section>
  </main>
</body>
</html>
