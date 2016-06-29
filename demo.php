<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
  <title>SVGChartBuilder demo</title>
  <style>
    main {
      -webkit-columns: 800px 2;
      -moz-columns: 800px 2;
      columns: 800px 2;
    }
    section {
      display: inline-block;
      width: auto;
      max-width: 800px;
      padding: 10px;
      vertical-align: top;
    }
  </style>
</head>

<body>
  <main>
    <section>
      <h2>Poloniex Dash/BTC Price</h2>
      <img src="poloniex-dash-btc.php" alt="Poloniex Dash/BTC price">
    </section>

    <section>
      <h2>Fake Stock Market Data</h2>
<?php
ini_set('display_errors', 1);

spl_autoload_register(function ($class_name) {
    include $class_name . ".php";
});

// fake up some stock market data

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

$stockChart = new SVGChartBuilder($chartData, [
  "width"=>500,
  "height"=>150,
  "fontSize"=>10
]);
print $stockChart->render();
?>
    </section>

    <section>
      <h2>Monotonically Smoothed Chart</h2>
<?php
$chartData = [];

$start = 100 * (rand()/getRandMax())**3;
$volatility = rand()/getRandMax() + 0.01;
$velocity = (rand()/getRandMax() - 0.5);
$acceleration = 0.1 * (rand()/getRandMax())**2;

for ($n = 0, $current = $start; $n < 12; $n++) {
  $velocity *= 0.5;
  $velocity += $acceleration * 2 * (rand()/getRandMax() - 0.5);
  $current += $velocity;
  $chartData[$n] = $current;
}

$tempChart = new SVGChartBuilder($chartData,  [
  "width"=>700,
  "height"=>400,
  "lineColor"=>"#D00",
  "labelColor"=>"#777",
  "smoothed"=>true
]);
print $tempChart->render();
?>
    </section>
  </main>

</body>
</html>
