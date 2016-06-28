# Dash-SVG-chart
PHP project to generate cached SVG price charts

![Dash 24h price in BTC from Poloniex](http://cryptohistory.org/dash/24h/)

## Requirements

* PHP

## Installation
Extract the files from https://github.com/seigler/Dash-SVG-chart/archive/master.zip where you want to use the chart, or from the command line run `git clone "https://github.com/seigler/Dash-SVG-chart" .` in the folder where you want the charts served from.

## Usage
In your PHP file:

```php
Header('Content-type: image/svg+xml; charset=utf-8');
Header('Content-Disposition: inline; filename="Dash-24h-chart-' . date('Y-m-d\THisT') . '.svg"');
include 'buffer.php';
include 'SVGChartBuilder.php';

/* your code here to generate $chartData */

print SVGChartBuilder::renderStockChart($chartData, [
  'width'=>800,
  'height'=>250,
  'lineColor'=>"#1C75BC",
  'labelColor'=>"#777",
  'smoothed'=>false
]);
```

In your HTML:
`<img src="path to the PHP file">`

## Credits

* PHP output buffering based on http://www.the-art-of-web.com/php/buffer/
* Chart appearance based on advice found at http://vis4.net/blog/posts/doing-the-line-charts-right/
