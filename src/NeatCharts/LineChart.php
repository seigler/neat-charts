<?php
namespace NeatCharts {
  class LineChart extends NeatChart {
    public function setOptions($options) {
      $this->options = [ // LineChart defaults
        'width' => 800,
        'height' => 250,
        'lineColor' => '#000',
        'markerColor' => '#000',
        'labelColor' => '#000',
        'smoothed' => false,
        'fontSize' => 15,
        'yAxisEnabled'=>true,
        'xAxisEnabled'=>false,
        'yAxisZero'=>false
      ];
      parent::setOptions($options);
    }

    public function setData($chartData) {
      $this->setWindow($chartData, $this->options); // sets min, max, range, etc
      // we assume $chartData is sorted by key and keys and values are all numeric
      $previousX = $previousY = null;
      $count = count($chartData);
      $deltaX = $this->xRange / $count;
      $averageAbsSlope = 0; // we will add all of them then divide to get an average
      $secants = []; // slope between this point and the previous one
      $tangents = []; // slope across the point

      foreach ($chartData as $x => $y) {
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
      $this->padding['left'] = $this->padding['right'] = $this->options['fontSize'] / 2;
      if ($this->options['yAxisEnabled']) {
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

        $this->padding['left'] = $this->options['fontSize'] * 0.6 * (
          1 + max(1, ceil(log($this->yMax, 10))) + $this->getPrecision($labelInterval)
        ) + 10;
        $this->width = $this->options['width'] - $this->padding['left'] - $this->padding['right'];

        // Top and bottom grid lines
        $gridLines =
          'M10,0 '.$this->width.',0 '.
          ' M10,'.$this->height.','.$this->width.','.$this->height;

        // Top and bottom grid labels
        $gridText =
          '<text text-anchor="end" x="'.(0.4 * $this->options['fontSize']).'" y="'.($this->options['fontSize'] * 0.4).'">'.($this->labelFormat($this->yMax, $labelPrecision + 1)).'</text>' .
          '<text text-anchor="end" x="'.(0.4 * $this->options['fontSize']).'" y="'.($this->options['fontSize'] * 0.4 + $this->height).'">'.($this->labelFormat($this->yMin, $labelPrecision + 1)).'</text>';

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
            $gridText .= '<text text-anchor="end" x="-'.(0.25 * $this->options['fontSize']).'" y="'.($labelHeight + $this->options['fontSize'] * 0.4).'">'.$this->labelFormat($labelY, $labelPrecision).'</text>';
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
      } else {
        $this->width = $this->options['width'] - $this->padding['left'] - $this->padding['right'];
      }

      $chartPoints = 'M';
      if ($this->options['smoothed']) {
        $chartPoints .= $this->transformX($this->xMin).','.$this->transformY($chartData[$this->xMin]);
        foreach ($chartData as $x => $y) {
          $controlX = $deltaX / 3 / sqrt(1 + $tangents[$x]**2);
          $controlY = $tangents[$x] * $controlX;
          if ($x != $this->xMin) {
            $chartPoints .= ' S'.
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

      $chartID = rand();
      $this->output = '<svg viewBox="-'.( $this->padding['left'] ).' -'.( $this->padding['top'] ).' '.( $this->options['width'] ).' '.( $this->options['height'] ).'" width="'.( $this->options['width'] ).'" height="'.( $this->options['height'] ).'" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs>
      <marker id="neatchart-markerCircle-'.( $chartID ).'" markerWidth="2" markerHeight="2" refX="1" refY="1" markerUnits="strokeWidth">
        <circle class="neatchart-marker" cx="1" cy="1" r="1" stroke="none" fill="'.( $this->options['markerColor'] ).'" />
      </marker>
      <linearGradient id="neatchart-fadeFromNothing-'.( $chartID ).'" x1="0%" y1="0%" x2="100%" y2="0%" gradientUnits="userSpaceOnUse">
        <stop offset="0.5%" stop-color="'.( $this->options['lineColor'] ).'" stop-opacity="0"></stop>
        <stop offset="2%" stop-color="'.( $this->options['lineColor'] ).'" stop-opacity="1"></stop>
        <stop offset="100%" stop-color="'.( $this->options['lineColor'] ).'" stop-opacity="1"></stop>
      </linearGradient>
    </defs>
    <g class="neatchart">'.( $this->options['yAxisEnabled'] || $this->options['xAxisEnabled'] ? '
      <g class="chart__gridLines"
        fill="none"
        stroke="'.( $this->options['labelColor'] ).'"
        stroke-width="1"
        vector-effect="non-scaling-stroke"
        stroke-dasharray="2, 2"
        shape-rendering="crispEdges">
        <path class="chart__gridLinePaths" d="'.( $gridLines ).'" />
      </g>
      <g class="chart__gridLabels"
        fill="'.( $this->options['labelColor'] ).'"
        font-family="monospace"
        font-size="'.( $this->options['fontSize'] ).'px">
        '.( $gridText ).'
      </g>' : '').'
      <g class="chart__plotLine"
        fill="none"
        stroke-width="'.( $this->options['fontSize'] / 3 ).'"
        stroke-linejoin="round"
        stroke-linecap="round"
        stroke="url(#neatchart-fadeFromNothing-'.( $chartID ).')"
        marker-end="url(#neatchart-markerCircle-'.( $chartID ).')"
      >
        <path d="'.( $chartPoints ).'" />'.($this->options['yAxisZero'] ? '
        <path
          stroke="none"
          fill="url(#neatchart-fadeFromNothing-'.( $chartID ).')"
          fill-opacity="0.25"
          marker-end="none"
          d="'.$chartPoints.' L'.$this->width.','.$this->height.' 0,'.$this->height.' Z'.'" />' : '').'
      </g>
    </g>
  </svg>';
    }
  }
}
