# NeatCharts
PHP project to generate clean-looking SVG price charts

![Dash 24h price in BTC from Poloniex](http://cryptohistory.org/dash/24h/)  
24h of Dash price in Bitcoin from Poloniex.com

## Requirements

* PHP

## Installation
Best:  
add a composer dependency on `seigler/neat-charts`.

Next best:  
Copy the how it's done in `/demo/index.php`.

## Usage
With Composer:  
`composer require seigler/neat-charts`

In your PHP file:  
```php
<?php
Header('Content-type: image/svg+xml; charset=utf-8');
Header('Content-Disposition: inline; filename="chart-' . date('Y-m-d\THisT') . '.svg"');
require 'NeatCharts/LineChart.php'; // better to use composer, require "seigler/neat-charts".

/*
your code here to populate $chartData
*/

$chart = new NeatCharts/LineChart($chartData, [ // all parameters optional
  'width'=>800,
  'height'=>250,
  'lineColor'=>"#1C75BC",
  'labelColor'=>"#777",
  'smoothed'=>false
]);
print $chart->render();
```

In your HTML:
`<img src="path to the PHP file">`

## Credits

* Demo's output caching based on http://www.the-art-of-web.com/php/buffer/
* Chart appearance based on advice found at http://vis4.net/blog/posts/doing-the-line-charts-right/
