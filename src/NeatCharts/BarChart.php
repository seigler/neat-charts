<?php
namespace NeatCharts {
  class BarChart extends NeatChart {
    public function setOptions($options) {
      $this->options = [ // BarChart defaults
        'width' => 600,
        'height' => 300,
        'barColor' => '#000',
        'markerColor' => '#000',
        'labelColor' => '#000',
        'fontSize' => 15,
        'yAxisEnabled'=>true,
        'xAxisEnabled'=>false,
        'yAxisZero'=>true
      ];
      parent::setOptions($options);
    }

    public function setData($chartData) {
      $this->setWindow($chartData, $this->options); // sets min, max, range, etc
      // we assume $chartData is sorted by key and keys and values are all numeric

      $count = count($chartData);
      $deltaX = $this->xRange / $count;
      $this->xMin -= $deltaX / 2;
      $this->xMax += $deltaX / 2;
      $this->xRange += $deltaX;

      $gridLabelXML = $this->buildGridLabelXML();
      // this also sets $this->width and $this->height

      $barRadius = min($this->width / $count / 2.5, $this->width / $count / 2 - 1);

      $chartPoints = '';
      foreach($chartData as $x => $y) {
        $barX = $this->transformX($x);
        $barY = $this->transformY($y);
        $barY0 = $this->transformY(0);
        $chartPoints .= ' M'.($barX - $barRadius).','.$barY0.' '.($barX + $barRadius).','.$barY0.' '.($barX + $barRadius).','.$barY.' '.($barX - $barRadius).','.$barY.' Z';
      }

      $this->output = '<svg viewBox="-'.( $this->padding['left'] ).' -'.( $this->padding['top'] ).' '.( $this->options['width'] ).' '.( $this->options['height'] ).'" width="'.( $this->options['width'] ).'" height="'.( $this->options['height'] ).'" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <g class="neatchart">'.
      $gridLabelXML.'
      <g
        class="chart__bars"
        fill="'.( $this->options['barColor'] ).'"
        fill-opacity="0.5"
        stroke="'.( $this->options['barColor'] ).'"
        stroke-width="2"
        stroke-linecap="round"
        >
        <path d="'.( $chartPoints ).'" />
      </g>
    </g>
  </svg>';
    }
  }
}
