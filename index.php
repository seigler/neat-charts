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

$dateformat = 'gA';
$last24h = Util::getJson($poloniex_url);
$chartData = [];
$absoluteDeltas = [];
$previousValue = null;
$yMin = $xMin = INF;
$yMax = $xMax = -INF;

foreach ($last24h as $datum) {
  $thisX = $datum->date;
  $thisY = $datum->weightedAverage;
  $chartData[$thisX] = $thisY;
  if (!is_null($previousValue)) {
    $absoluteDeltas[] = abs($thisY - $previousValue);
  }
  if ($thisY < $yMin) {
    $yMin = $thisY;
    $yMinX = $thisX;
  }
  if ($thisY > $yMax) {
    $yMax = $thisY;
    $yMaxX = $thisX;
  }
  $previousValue = $thisY;
}
end($chartData); // already at end, not needed
$yRange = $yMax - $yMin;
$xMax = key($chartData);
reset($chartData);
$xMin = key($chartData);
$xRange = $xMax - $xMin;
$count = count($chartData);
$medianDelta = abs(Util::median($absoluteDeltas));
$width = 700;

/*
We want the height of the median y-delta to be the same as
the width of one x-delta, which puts the median slope at
45 degrees. This improves comprehension.
http://vis4.net/blog/posts/doing-the-line-charts-right/
*/
$aspectRatio = $yRange / $medianDelta / $count;
$height = floor($aspectRatio * $width);

function labelFormat($float, $sigFigs = 4, $minPlaces = 1) {
  return number_format($float, max(
    $minPlaces,
    $sigFigs + floor(-log($float, 10)))
    /* this floor(log) thing is the first significant place value */
  );
}

/* Transform data coords to chart coords */
function transformX($x) {
  global $xMin, $xRange, $width;
  return round(
    ($x - $xMin) / $xRange * $width
  , 2);
}
function transformY($y) {
  global $yMax, $yRange, $height;
  return round(
  // SVG has y axis reversed, 0 is at the top
    ($yMax - $y) / $yRange * $height
  , 2);
}

$chartPoints = "M";
foreach ($chartData as $x => $y) {
  $chartPoints .= transformX($x) . ',' . transformY($y) . '
  ';
}

$numLabels = floor($height / 50);
$labelModulation = 2 * 10 ** (1 + floor(-log($yRange / $numLabels, 10)));
$labelInterval = round($yRange / $numLabels * $labelModulation) / $labelModulation;

$gridLines =
"M0,0 ".$width.",0
M0,".$height.",".$width.",".$height."
";

$gridText =
  '<text x="-4" y="4">'.labelFormat($yMax).'</text>' .
  '<text x="-4" y="'.($height + 4).'">'.labelFormat($yMin).'</text>';

for (
  $labelY = $yMin + 0.5 * $labelInterval - fmod($yMin + 0.5 * $labelInterval , $labelInterval) + $labelInterval;
  $labelY < $yMax - 0.5 * $labelInterval;
  $labelY += $labelInterval
) {

  $labelHeight = transformY($labelY);
  $gridLines .= "M0,".$labelHeight." ".$width.",".$labelHeight."
  ";
  $gridText .= '<text	x="-4" y="'.($labelHeight + 4).'">'.labelFormat($labelY).'</text>';
}

// in case some dingbat has PHP short tags enabled
echo '<'.'?xml version="1.0" standalone="no"?'.'>';

?>
<svg
  viewBox="-90 -10 <?= $width + 100 ?> <?= $height + 20 ?>"
  width="<?= $width + 100 ?>"
  height="<?= $height + 20 ?>"
  version="1.1"
  xmlns="http://www.w3.org/2000/svg"
  xmlns:xlink="http://www.w3.org/1999/xlink">
  <defs>
    <marker id="markerCircle" markerWidth="2" markerHeight="2" refX="1" refY="1" markerUnits="strokeWidth">
      <circle cx="1" cy="1" r="1" style="stroke: none; fill:#000000;"/>
    </marker>
    <linearGradient id="fadeFromNothing" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#1C75BC" stop-opacity="0"></stop>
      <stop offset="2.5%" stop-color="#1C75BC" stop-opacity="1"></stop>
      <stop offset="100%" stop-color="#1C75BC" stop-opacity="1"></stop>
    </linearGradient>
      <style type="text/css"><![CDATA[
        .chart__gridLines {
          font-family: sans-serif;
          font-size: 10;
          fill: #7f7f7f;
          text-anchor:end;
        }
        .chart__gridLinePaths {
          fill: none;
          stroke: #000000;
          stroke-width: 1;
          stroke-dasharray: 2,2;
          stroke-opacity: 0.25;
        }
        .chart__plotLine {
          fill: none;
          stroke-width: 5;
          stroke-linejoin: round;
          stroke-linecap: round;
          stroke: url(#fadeFromNothing);
          marker-end: url(#markerCircle);
        }
      ]]></style>
  </defs>
  <g class="chart__gridLines">
      <path class="chart__gridLinePaths" d="<?= $gridLines ?>" />
      <?= $gridText ?>
  </g>
  <path class="chart__plotLine" d="<?= $chartPoints ?>" />
</svg>

