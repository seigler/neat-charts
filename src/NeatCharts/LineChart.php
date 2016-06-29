<?php
namespace NeatCharts {
  class LineChart {
    private $options = [
      'width' => 800,
      'height' => 250,
      'lineColor' => '#000',
      'labelColor' => '#000',
      'smoothed' => false,
      'fontSize' => 15
    ];

    private $width;
    private $height;
    private $output;
    private $xMin;
    private $xMax;
    private $xRange;
    private $yMin;
    private $yMax;
    private $yRange;
    private $padding = ['top'=>10, 'right'=>10, 'bottom'=>10, 'left'=>10];

    private function arrayGet($array, $key, $default = NULL)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    private function labelFormat($float, $places, $minPlaces = 0) {
      $value = number_format($float, max($minPlaces, $places));
      // add a trailing space if there's no decimal
      return (strpos($value, '.') === false ? $value . '.' : $value);
    }

    /* Transform data coords to chart coords */
    /* Transform data coords to chart coords */
    private function transformX($x) {
      return round(
        ($x - $this->xMin) / $this->xRange * $this->width
      , 2);
    }
    private function transformY($y) {
      return round(
      // SVG has y axis reversed, 0 is at the top
        ($this->yMax - $y) / $this->yRange * $this->height
      , 2);
    }

    private function getPrecision($value) { // thanks http://stackoverflow.com/a/21788335/5402566
      if (!is_numeric($value)) { return false; }
      $decimal = $value - floor($value); //get the decimal portion of the number
      if ($decimal == 0) { return 0; } //if it's a whole number
      $precision = strlen(trim(number_format($decimal,10),'0')) - 1; //-2 to account for '0.'
      return $precision;
    }

    public function __construct($chartData, $options = []) {
      $this->setOptions($options);
      $this->setData($chartData);
    }

    public function setOptions($options) {
      $this->options = array_replace($this->options, $options);
      $this->padding['left'] = $this->options['fontSize'] * 5;
      $this->padding['top'] = $this->padding['bottom'] = $this->options['fontSize'];
    }

    public function setData($chartData) {
      // we assume $chartData is sorted by key and keys and values are all numeric
      $previousX = $previousY = null;
      end($chartData);
      $this->xMax = key($chartData);
      reset($chartData);
      $this->xMin = key($chartData);
      $this->xRange = $this->xMax - $this->xMin;
      $count = count($chartData);
      $deltaX = $this->xRange / $count;
      $this->yMin = INF; // so the first comparison sets this to an actual value
      $this->yMax = -INF;
      $averageAbsSlope = 0; // we will add all of them then divide to get an average
      $secants = []; // slope between this point and the previous one
      $tangents = []; // slope across the point

      foreach ($chartData as $x => $y) {
        if ($y < $this->yMin) {
          $this->yMin = $y;
          $yMinX = $x;
        }
        if ($y > $this->yMax) {
          $this->yMax = $y;
          $yMaxX = $x;
        }
        if (!is_null($previousY)) {
          $averageAbsSlope += abs($y - $previousY); // just add up all the Y differences
          $secants[$previousX] = ($y - $previousY) / $deltaX;
        }
        if ($x == $this->xMax) {
          $secants[$x] = ($y - $previousY) / $deltaX;
        }
        $previousY = $y;
        $previousX = $x;
      }
      $this->yRange = $this->yMax - $this->yMin;
      $averageAbsSlope /= $this->yRange * $deltaX; // turn this absolute-deltas total into a slope

      if ($this->options['smoothed']) {
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
          if ($x == $this->xMax) {
            $tangents[$x] = $secant;
          }
          if ($x == $this->xMin) {
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
      $this->width = $this->options['width'] - $this->padding['left'] - $this->padding['right'];
      if (isset($this->options['height'])) {
        $this->height = $this->options['height'] - $this->padding['top'] - $this->padding['bottom'];
      } else {
        $this->height = floor($aspectRatio * $this->width);
        $this->options['height'] = $this->height + $this->padding['top'] + $this->padding['bottom'];
      }

      $chartPoints = 'M';
      $chartSplines = 'M'.
        $this->transformX($this->xMin).','.
        $this->transformY($chartData[$this->xMin]);
      if ($this->options['smoothed']) {
        foreach ($chartData as $x => $y) {
          $controlX = $deltaX / 3 / sqrt(1 + $tangents[$x]**2);
          $controlY = $tangents[$x] * $controlX;
          if ($x != $this->xMin) {
            $chartSplines .= ' S'.
              $this->transformX($x - $controlX).','.
              $this->transformY($y - $controlY).' '.
              $this->transformX($x).','.
              $this->transformY($y);
          }
        }
      } else {
        foreach ($chartData as $x => $y) {
          $chartPoints .=
            $this->transformX($x).','.
            $this->transformY($y) . ' ';
        }
      }

      $numLabels = 2 + ceil($this->height / $this->options['fontSize'] / 6);
      $labelInterval = $this->yRange / $numLabels;
      $labelModulation = 10 ** (1 + floor(-log($this->yRange / $numLabels, 10)));

      // 0.1 here is a fudge factor so we get multiples of 2.5 a little more often
      if (fmod($labelInterval * $labelModulation, 2.5) < fmod($labelInterval * $labelModulation, 2) + 0.1) {
        $labelModulation /= 2.5;
      } else {
        $labelModulation /= 2;
      }
      $labelInterval = ceil($labelInterval * $labelModulation) / $labelModulation;
      $labelPrecision = $this->getPrecision($labelInterval);

      // Top and bottom grid lines
      $gridLines =
        'M10,0 '.$this->width.',0 '.
        ' M10,'.$this->height.','.$this->width.','.$this->height;

      // Top and bottom grid labels
      $gridText =
        '<text x="'.(0.4 * $this->options['fontSize']).'" y="'.($this->options['fontSize'] * 0.4).'">'.($this->labelFormat($this->yMax, $labelPrecision + 1)).'</text>' .
        '<text x="'.(0.4 * $this->options['fontSize']).'" y="'.($this->options['fontSize'] * 0.4 + $this->height).'">'.($this->labelFormat($this->yMin, $labelPrecision + 1)).'</text>';

      // Main labels and grid lines
      for (
        $labelY = $this->yMin - fmod($this->yMin, $labelInterval) + $labelInterval; // Start at the first "nice" Y value > min
        $labelY < $this->yMax; // Keep going until max
        $labelY += $labelInterval // Add Interval each iteration
      ) {
        $labelHeight = $this->transformY($labelY);
        if ( // label is not too close to the min or max
          $labelHeight < $this->height - 1.5 * $this->options['fontSize'] &&
          $labelHeight > $this->options['fontSize'] * 1.5
        ) {
          $gridText .= '<text	x="-'.(0.25 * $this->options['fontSize']).'" y="'.($labelHeight + $this->options['fontSize'] * 0.4).'">'.$this->labelFormat($labelY, $labelPrecision).'</text>';
          $gridLines .= ' M0,'.$labelHeight.' '.$this->width.','.$labelHeight;
        } else if ( // label is too close
            $labelHeight < $this->height - $this->options['fontSize'] * 0.75 &&
            $labelHeight > $this->options['fontSize'] * 0.75
          ) {
            $gridLines .= ' M'.( // move grid line over when it's very close to the min or max label
              $labelHeight < $this->height - $this->options['fontSize'] / 2 && $labelHeight > $this->options['fontSize'] / 2 ? 0 : $this->options['fontSize'] / 2
            ).','.$labelHeight.' '.$this->width.','.$labelHeight;
          }
      }

      $chartID = rand();
      $this->output = '<?xml version="1.0" standalone="no"?>
  <svg viewBox="-'.( $this->padding['left'] ).' -'.( $this->padding['top'] ).' '.( $this->options['width'] ).' '.( $this->options['height'] ).'" width="'.( $this->options['width'] ).'" height="'.( $this->options['height'] ).'" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs>
      <marker id="SVGChart-markerCircle-'.( $chartID ).'" markerWidth="2" markerHeight="2" refX="1" refY="1" markerUnits="strokeWidth">
        <circle cx="1" cy="1" r="1" style="stroke: none; fill:'.( $this->options['lineColor'] ).';" />
      </marker>
      <linearGradient id="SVGChart-fadeFromNothing-'.( $chartID ).'" x1="0%" y1="0%" x2="100%" y2="0%">
        <stop offset="0.5%" stop-color="'.( $this->options['lineColor'] ).'" stop-opacity="0"></stop>
        <stop offset="5%" stop-color="'.( $this->options['lineColor'] ).'" stop-opacity="1"></stop>
        <stop offset="100%" stop-color="'.( $this->options['lineColor'] ).'" stop-opacity="1"></stop>
      </linearGradient>
      <style type="text/css">
        <![CDATA[ .SVGChart-'.( $chartID ).' .chart__gridLines {
          font-family: sans-serif;
          font-size: '.( $this->options['fontSize'] ).'px;
          fill: '.( $this->options['labelColor'] ).';
          text-anchor: end;
          shape-rendering: crispEdges;
        }

        .SVGChart-'.( $chartID ).' .chart__gridLinePaths {
          fill: none;
          stroke: '.( $this->options['labelColor'] ).';
          stroke-opacity: 0.75;
          stroke-width: 1;
          stroke-dasharray: 2, 2;
        }

        .SVGChart-'.( $chartID ).' .chart__plotLine {
          fill: none;
          stroke-width: '.( $this->options['fontSize'] / 3 ).';
          stroke-linejoin: round;
          stroke-linecap: round;
          stroke: url(#SVGChart-fadeFromNothing-'.( $chartID ).');
          marker-end: url(#SVGChart-markerCircle-'.( $chartID ).');
        }

        ]]>
      </style>
    </defs>
    <g class="SVGChart SVGChart-'.( $chartID ).'">
      <g class="chart__gridLines">
        <path class="chart__gridLinePaths" d="'.( $gridLines ).'" /> '.( $gridText ).'
      </g>
      <g class="chart__plotLine">
        <path d="'.( $this->options['smoothed'] ? $chartSplines : $chartPoints ).'" />
      </g>
    </g>
  </svg>';
    }
    public function render() {
      return $this->output;
    }
  }
}
