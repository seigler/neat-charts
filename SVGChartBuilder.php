<?php

class SVGChartBuilder {

  public static function renderStockChart($chartData, $width = 700) {
    $absoluteDeltas = [];
    $previousValue = null;
    $yMin = $xMin = INF;
    $yMax = $xMax = -INF;

    foreach ($chartData as $thisX => $thisY) {
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
    $yRange = $yMax - $yMin;
    end($chartData);
    $xMax = key($chartData);
    reset($chartData);
    $xMin = key($chartData);
    $xRange = $xMax - $xMin;
    $count = count($chartData);
    $averageDelta = abs(array_sum($absoluteDeltas)/$count);

    /*
    We want the height of the median y-delta to be the same as
    the width of one x-delta, which puts the median slope at
    45 degrees. This improves comprehension.
    http://vis4.net/blog/posts/doing-the-line-charts-right/
    */
    $aspectRatio = max(0.25, $yRange / $averageDelta / $count);
    $height = floor($aspectRatio * $width);

    function labelFormat($float, $sigFigs, $minPlaces = 0) {
      return number_format($float, max($minPlaces, $sigFigs));
    }

    /* Transform data coords to chart coords */
    function transformX($x, $xMin, $xRange, $width) {
      return round(
        ($x - $xMin) / $xRange * $width
      , 2);
    }
    function transformY($y, $yMax, $yRange, $height) {
      return round(
      // SVG has y axis reversed, 0 is at the top
        ($yMax - $y) / $yRange * $height
      , 2);
    }

    function getPrecision($value) { // thanks http://stackoverflow.com/a/21788335/5402566
      if (!is_numeric($value)) { return false; }
      $decimal = $value - floor($value); //get the decimal portion of the number
      if ($decimal == 0) { return 0; } //if it's a whole number
      $precision = strlen(trim(number_format($decimal,10),"0")) - 1; //-2 to account for "0."
      return $precision;
    }

    $chartPoints = "M";
    foreach ($chartData as $x => $y) {
      $chartPoints .= transformX($x, $xMin, $xRange, $width) . ',' . transformY($y, $yMax, $yRange, $height) . '
      ';
    }

    $numLabels = min(4, ceil($height / 40));
    $labelInterval = $yRange / $numLabels;
    $labelModulation = 10 ** (1 + floor(-log($yRange / $numLabels, 10)));
//    if (fmod($labelInterval, $labelModulation / 5) < $labelInterval * 0.5) {
      $labelModulation /= 2.5;
//    } else if (fmod($labelInterval, $labelModulation / 2) < $labelInterval * 0.25) {
//      $labelModulation /= 2;
//    }
//    var_dump($labelInterval, $labelModulation, $labelInterval * $labelModulation, ceil($labelInterval * $labelModulation) / $labelModulation);
    $labelInterval = ceil($labelInterval * $labelModulation) / $labelModulation;
    $labelPlaces = getPrecision($labelInterval);

    // Top and bottom grid lines
    $gridLines =
    "M10,0 ".$width.",0
    M10,".$height.",".$width.",".$height."
    ";

    // Top and bottom grid labels
    $gridText =
      '<text x="6" y="4">'.labelFormat($yMax, $labelPlaces + 1).'</text>' .
      '<text x="6" y="'.($height + 4).'">'.labelFormat($yMin, $labelPlaces + 1).'</text>';

    // Start at the first "nice" Y value > min + 50% of the interval
    // Keep going until max - 50% of the interval
    // Add Interval each iteration
    for (
      $labelY = $yMin - fmod($yMin, $labelInterval) + $labelInterval;
      $labelY < $yMax;
      $labelY += $labelInterval
    ) {
      $labelHeight = transformY($labelY, $yMax, $yRange, $height);
      if (
        $labelY < $yMax - 0.1 * $labelInterval &&
        $labelY > $yMin + 0.1 * $labelInterval
      ) {
        $gridLines .= " M0,".$labelHeight." ".$width.",".$labelHeight;
      }
      if (
        $labelY < $yMax - 0.3 * $labelInterval &&
        $labelY > $yMin + 0.3 * $labelInterval
      ) {
        $gridText .= '<text	x="-4" y="'.($labelHeight + 4).'">'.labelFormat($labelY, $labelPlaces).'</text>';
      }
    }

    return '<?xml version="1.0" standalone="no"?>
<svg
  viewBox="-90 -10 '.( $width + 100 ).' '.( $height + 20 ).'"
  width="'.( $width + 100 ).'"
  height="'.( $height + 20 ).'"
  version="1.1"
  xmlns="http://www.w3.org/2000/svg"
  xmlns:xlink="http://www.w3.org/1999/xlink">
  <defs>
    <marker id="markerCircle" markerWidth="2" markerHeight="2" refX="1" refY="1" markerUnits="strokeWidth">
      <circle cx="1" cy="1" r="1" style="stroke: none; fill:#000000;"/>
    </marker>
    <linearGradient id="fadeFromNothing" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0.5%" stop-color="#1C75BC" stop-opacity="0"></stop>
      <stop offset="5%" stop-color="#1C75BC" stop-opacity="1"></stop>
      <stop offset="100%" stop-color="#1C75BC" stop-opacity="1"></stop>
    </linearGradient>
      <style type="text/css"><![CDATA[
        .chart__gridLines {
          font-family: sans-serif;
          font-size: 10;
          fill: #7f7f7f;
          text-anchor: end;
          shape-rendering: crispEdges;
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
      <path class="chart__gridLinePaths" d="'.( $gridLines ).'" />
      '.( $gridText ).'
  </g>
  <path class="chart__plotLine" d="'.( $chartPoints ).'" />
</svg>';
  }
}
