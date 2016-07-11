<?php
namespace NeatCharts {
  class CandlestickChart extends NeatChart {
    public function setOptions($options) {
      $this->options = [ // BarChart defaults
        'width' => 1200,
        'height' => 300,
        'barColor' => '#000',
        'risingColor' => '#0B0',
        'fallingColor' => '#D00',
        'markerColor' => '#000',
        'labelColor' => '#000',
        'fontSize' => 15,
        'yAxisEnabled'=>true,
        'xAxisEnabled'=>false,
        'yAxisZero'=>false
      ];
      parent::setOptions($options);
    }

    public function setData($chartData) { // this directly uses the poloniex historical data format for $chartData
      // $key = index, $value = ["date":1405699200,"high":0.0045388,"low":0.00403001,"open":0.00404545,"close":0.00435873,"volume":44.34555992,"quoteVolume":10311.88079097,"weightedAverage":0.00430043]
      // we assume $chartData is sorted by date and values are all numeric
      $this->xMax = end($chartData)->date;
      $this->xMin = reset($chartData)->date;
      $this->xRange = $this->xMax - $this->xMin;
      $this->yMin = ($this->options['yAxisZero'] ? 0 : INF);
      $this->yMax = ($this->options['yAxisZero'] ? 0 : -INF);

      foreach ($chartData as $v) {
        if ($v->low < $this->yMin) {
          $this->yMin = $v->low;
          $yMinX = $v->date;
        }
        if ($v->high > $this->yMax) {
          $this->yMax = $v->high;
          $yMaxX = $v->date;
        }
      }
      $this->yRange = $this->yMax - $this->yMin;

      $count = count($chartData);
      $deltaX = $this->xRange / $count;
      $this->xMin -= $deltaX / 2;
      $this->xMax += $deltaX / 2;
      $this->xRange += $deltaX;

      $gridLabelXML = $this->buildGridLabelXML();
      // this also sets $this->width and $this->height

      $barRadius = min($this->width / $count / 2.5, $this->width / $count / 2 - 1);

      $risingPoints = '';
      $fallingPoints = '';
      $whiskerPoints = '';
      foreach($chartData as $v) {
        $barX = $this->transformX($v->date);
        $barY0 = $this->transformY($v->open);
        $barY1 = $this->transformY($v->close);
        $whiskerY0 = $this->transformY($v->low);
        $whiskerY1 = $this->transformY($v->high);
        if ($v->close > $v->open) {
          $whiskerPoints .= ' M'.($barX).','.($whiskerY0).' '.($barX).','.($whiskerY1);
//          $risingPoints .= ' M'.($barX - $barRadius).','.$barY0.' '.($barX + $barRadius).','.$barY0.' '.($barX + $barRadius).','.$barY1.' '.($barX - $barRadius).','.$barY1.' Z';
          $risingPoints .= ' M'.$barX.','.$barY0.' '.$barX.','.$barY1;
        } else {
          $whiskerPoints .= ' M'.($barX).','.($whiskerY1).' '.($barX).','.($whiskerY0);
//          $fallingPoints .= ' M'.($barX - $barRadius).','.$barY0.' '.($barX + $barRadius).','.$barY0.' '.($barX + $barRadius).','.$barY1.' '.($barX - $barRadius).','.$barY1.' Z';
          $fallingPoints .= ' M'.$barX.','.$barY0.' '.$barX.','.$barY1;
        }
      }

      $this->output = '<svg viewBox="-'.( $this->padding['left'] ).' -'.( $this->padding['top'] ).' '.( $this->options['width'] ).' '.( $this->options['height'] ).'" width="'.( $this->options['width'] ).'" height="'.( $this->options['height'] ).'" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <g class="neatchart">'.
      $gridLabelXML.'
      <g
        class="chart__bars"
        stroke="'.( $this->options['barColor'] ).'"
        stroke-width="1"
        stroke-linecap="butt"
        >
        <g
          class="chart__whiskers"
          stroke-width="'.( $barRadius ).'"
          stroke-opacity="0.25"
        >
          <path d="'.( $whiskerPoints ).'" />
        </g>
        <g
          class="chart__rising-bars"
          stroke="'.( $this->options['risingColor'] ).'"
          stroke-width="'.( $barRadius * 2 ).'"
        >
          <path d="'.( $risingPoints ).'" />
        </g>
        <g
          class="chart__falling-bars"
          stroke="'.( $this->options['fallingColor'] ).'"
          stroke-width="'.( $barRadius * 2 ).'"
        >
          <path d="'.( $fallingPoints ).'" />
        </g>
      </g>
    </g>
  </svg>';
    }
  }
}
