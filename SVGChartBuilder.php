<?php

class SVGChartBuilder {

  public static function renderStockChart($chartData, $width = 700) {
    $absoluteDeltas = [];
    $previousValue = null;
    $yMin = $xMin = INF;
    $yMax = $xMax = -INF;

    function median($arr) {
      sort($arr);
      $count = count($arr); //total numbers in array
      $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
      if($count % 2) { // odd number, middle is the median
          $median = $arr[$middleval];
      } else { // even number, calculate avg of 2 medians
          $low = $arr[$middleval];
          $high = $arr[$middleval+1];
          $median = (($low+$high)/2);
      }
      return $median;
    }

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
    $medianDelta = abs(median($absoluteDeltas));

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

    $chartPoints = "M";
    foreach ($chartData as $x => $y) {
      $chartPoints .= transformX($x, $xMin, $xRange, $width) . ',' . transformY($y, $yMax, $yRange, $height) . '
      ';
    }

    $numLabels = floor($height / 25);
    $labelModulation = 10 ** (1 + floor(-log($yRange / $numLabels, 10)));// / 5;
    $labelInterval = ceil($yRange / $numLabels * $labelModulation) / $labelModulation;

    // Top and bottom grid lines
    $gridLines =
    "M0,0 ".$width.",0
    M0,".$height.",".$width.",".$height."
    ";

    // Top and bottom grid labels
    $gridText =
      '<text x="-4" y="4">'.labelFormat($yMax).'</text>' .
      '<text x="-4" y="'.($height + 4).'">'.labelFormat($yMin).'</text>';

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
        $labelY < $yMax - 0.4 * $labelInterval &&
        $labelY > $yMin + 0.4 * $labelInterval
      ) {
        $gridLines .= "M0,".$labelHeight." ".$width.",".$labelHeight."
        ";
        $gridText .= '<text	x="-4" y="'.($labelHeight + 4).'">'.labelFormat($labelY).'</text>';
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
      <path class="chart__gridLinePaths" d="'.( $gridLines ).'" />
      '.( $gridText ).'
  </g>
  <path class="chart__plotLine" d="'.( $chartPoints ).'" />
</svg>';
  }
}
