<?php
namespace NeatCharts {
  abstract class NeatChart {
    protected $options = [
      'width' => 800,
      'height' => 250,
      'lineColor' => '#000',
      'labelColor' => '#000',
      'smoothed' => false,
      'fontSize' => 15
    ];

    protected $width;
    protected $height;
    protected $output;
    protected $xMin;
    protected $xMax;
    protected $xRange;
    protected $yMin;
    protected $yMax;
    protected $yRange;
    protected $padding = ['top'=>10, 'right'=>10, 'bottom'=>10, 'left'=>10];

    protected function arrayGet($array, $key, $default = NULL)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    protected function labelFormat($float, $places, $minPlaces = 0) {
      $value = number_format($float, max($minPlaces, $places));
      // add a trailing space if there's no decimal
      return (strpos($value, '.') === false ? $value . '.' : $value);
    }

    /* Transform data coords to chart coords */
    /* Transform data coords to chart coords */
    protected function transformX($x) {
      return round(
        ($x - $this->xMin) / $this->xRange * $this->width
      , 2);
    }
    protected function transformY($y) {
      return round(
      // SVG has y axis reversed, 0 is at the top
        ($this->yMax - $y) / $this->yRange * $this->height
      , 2);
    }

    protected function getPrecision($value) { // thanks http://stackoverflow.com/a/21788335/5402566
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

    public abstract function setData($chartData);
    public function render() {
      return $this->output;
    }
  }
}
