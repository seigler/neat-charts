<?php

class SVGChartBuilder {

  public static function renderStockChart($chartData, $options) {
    function arrayGet($array, $key, $default = NULL)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    $width = arrayGet($options, "width", 800) - 100;
    $height = arrayGet($options, "height");
    $lineColor = arrayGet($options, "lineColor", "#FF2F00");
    $labelColor = arrayGet($options, "labelColor", "#999");
    $smoothed = arrayGet($options, "smoothed", false);
    // we assume $chartData is sorted by key and keys and values are all numeric
    $previousY = $previousY = null;
    end($chartData);
    $xMax = key($chartData);
    reset($chartData);
    $xMin = key($chartData);
    $xRange = $xMax - $xMin;
    $count = count($chartData);
    $deltaX = $xRange / $count;
    $yMin = INF; // so the first comparison sets this to an actual value
    $yMax = -INF;
    $averageAbsSlope = 0; // we will add all of them then divide to get an average
    $secants = []; // slope between this point and the previous one
    $tangents = []; // slope across the point

    foreach ($chartData as $x => $y) {
      if ($y < $yMin) {
        $yMin = $y;
        $yMinX = $x;
      }
      if ($y > $yMax) {
        $yMax = $y;
        $yMaxX = $x;
      }
      if (!is_null($previousY)) {
        $averageAbsSlope += abs($y - $previousY); // just add up all the Y differences
        $secants[$previousX] = ($y - $previousY) / $deltaX;
      }
      if ($x == $xMax) {
        $secants[$x] = ($y - $previousY) / $deltaX;
      }
      $previousY = $y;
      $previousX = $x;
    }
    $yRange = $yMax - $yMin;
    $averageAbsSlope /= $yRange * $deltaX; // turn this absolute-deltas total into a slope

    if ($smoothed) {
      // take all these slopes and average them with their neighbors
      // unless they change direction, then make them zero
      // also restrict them a bit when they are very different
      $previousSecant = $previousX = null;
      foreach ($secants as $x => $secant) {
        if (!is_null($previousSecant)) {
          $tangents[$x] = ($secant + $previousSecant) / 2;
          if ($secant == 0 || $previousSecant == 0 || $secant * $previousSecant <= 0)
          {
            $tangents[$x] = 0;
          } else {
            if ($tangents[$x] / $previousSecant > 3) {
              $tangents[$x] = 3 * $previousSecant;
            } else if ($tangents[$x] / $secant > 3) {
              $tangents[$x] = 3 * $secant;
            }
          }
        }
        if ($x == $xMax) {
          $tangents[$x] = $secant;
        }
        if ($x == $xMin) {
          $tangents[$x] = $secant;
        }

        $previousX = $x;
        $previousSecant = $secant;
      }
    }

    /*
    We want the height of the median y-delta to be the same as
    the width of one x-delta, which puts the median slope at
    45 degrees. This improves comprehension.
    http://vis4.net/blog/posts/doing-the-line-charts-right/
    */
    $aspectRatio = max(0.25, min(0.75, 1 / $averageAbsSlope));
    $height = $height ?? floor($aspectRatio * $width);

    function labelFormat($float, $places, $minPlaces = 0) {
      $value = number_format($float, max($minPlaces, $places));
      // add a trailing space if there's no decimal
      return (strpos($value, ".") === false ? $value . "." : $value);
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
    $chartSplines = "M".
      transformX($xMin, $xMin, $xRange, $width).",".
      transformY($chartData[$xMin], $yMax, $yRange, $height);
    foreach ($chartData as $x => $y) {
      $chartPoints .=
        transformX($x, $xMin, $xRange, $width).",".
        transformY($y, $yMax, $yRange, $height) . "\n";

      if ($smoothed) {
        $controlX = $deltaX / 3 / sqrt(1 + $tangents[$x]**2);
        $controlY = $tangents[$x] * $controlX;
        if ($x != $xMin) {
          $chartSplines .= " S".
            transformX($x - $controlX, $xMin, $xRange, $width).",".
            transformY($y - $controlY, $yMax, $yRange, $height)." ".
            transformX($x, $xMin, $xRange, $width).",".
            transformY($y, $yMax, $yRange, $height);
        }
      }
    }

    $numLabels = 2 + ceil($height / 60);
    $labelInterval = $yRange / $numLabels;
    $labelModulation = 10 ** (1 + floor(-log($yRange / $numLabels, 10)));

    // 0.1 here is a fudge factor so we get multiples of 2.5 a little more often
    if (fmod($labelInterval * $labelModulation, 2.5) < fmod($labelInterval * $labelModulation, 2) + 0.1) {
      $labelModulation /= 2.5;
    } else {
      $labelModulation /= 2;
    }
    $labelInterval = ceil($labelInterval * $labelModulation) / $labelModulation;
    $labelPrecision = getPrecision($labelInterval);

    // Top and bottom grid lines
    $gridLines =
      "M10,0 ".$width.",0\n".
      "M10,".$height.",".$width.",".$height."\n";

    // Top and bottom grid labels
    $gridText =
      '<text x="6" y="4">'.labelFormat($yMax, $labelPrecision + 1).'</text>' .
      '<text x="6" y="'.($height + 4).'">'.labelFormat($yMin, $labelPrecision + 1).'</text>';

    // Main labels and grid lines
    for (
      $labelY = $yMin - fmod($yMin, $labelInterval) + $labelInterval; // Start at the first "nice" Y value > min
      $labelY < $yMax; // Keep going until max
      $labelY += $labelInterval // Add Interval each iteration
    ) {
      $labelHeight = transformY($labelY, $yMax, $yRange, $height);
      if ( // label is not too close to the min or max
        $labelHeight < $height - 25 &&
        $labelHeight > 25
      ) {
        $gridText .= '<text	x="-4" y="'.($labelHeight + 4).'">'.labelFormat($labelY, $labelPrecision).'</text>';
        $gridLines .= " M0,".$labelHeight." ".$width.",".$labelHeight;
      } else if ( // label is too close
          $labelHeight < $height - 4 &&
          $labelHeight > 4
        ) {
          $gridLines .= " M".( // move grid line over when it's very close to the min or max label
            $labelHeight < $height - 10 && $labelHeight > 10 ? 0 : 10
          ).",".$labelHeight." ".$width.",".$labelHeight;
        }
    }

    print '<?xml version="1.0" standalone="no"?>
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
      <stop offset="0.5%" stop-color="'.( $lineColor ).'" stop-opacity="0"></stop>
      <stop offset="5%" stop-color="'.( $lineColor ).'" stop-opacity="1"></stop>
      <stop offset="100%" stop-color="'.( $lineColor ).'" stop-opacity="1"></stop>
    </linearGradient>
      <style type="text/css"><![CDATA[
        .chart__gridLines {
          font-family: sans-serif;
          font-size: 10;
          fill: '.( $labelColor ).';
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
  <g class="chart__plotLine">
    <path d="'.( $smoothed ? $chartSplines : $chartPoints ).'" />
  </g>
</svg>';
  }
}
