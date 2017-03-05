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
        'yAxisEnabled' => true,
        'xAxisEnabled' => false,
        'yAxisZero' => false,
        'filled' => false,
        'background' => 'none'
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
      if (!isset($this->options['height'])) {
        $this->options['height'] = floor($aspectRatio * $this->options['width']);
      }

      $gridLabelsXML = parent::buildGridLabelXML();

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
    <g class="neatchart">'.( $this->options['yAxisEnabled'] || $this->options['xAxisEnabled'] ? $gridLabelsXML : '').'
      <g class="chart__plotLine"
        fill="none"
        stroke-width="'.( $this->options['fontSize'] / 3 ).'"
        stroke-linejoin="round"
        stroke-linecap="round"
        stroke="url(#neatchart-fadeFromNothing-'.( $chartID ).')"
        marker-end="url(#neatchart-markerCircle-'.( $chartID ).')"
      >
        <path d="'.( $chartPoints ).'" />'.($this->options['filled'] ? '
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
