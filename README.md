# seigler/neat-charts [![GitHub stars](https://img.shields.io/github/stars/seigler/neat-charts.svg)](https://github.com/seigler/neat-charts/stargazers) [![Packagist](https://img.shields.io/packagist/dt/seigler/neat-charts.svg)](https://packagist.org/packages/seigler/neat-charts) [![Github All Releases](https://img.shields.io/github/downloads/seigler/neat-charts/total.svg?maxAge=2592000)](https://github.com/seigler/neat-charts) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/seigler/neat-charts/blob/master/LICENSE.txt)

PHP project to generate clean-looking SVG price charts

![Dash 24h price in BTC from Poloniex](http://cryptohistory.org/dash/24h/)  
24h of Dash price in Bitcoin from Poloniex.com

## Requirements

* PHP >=5.3.0

## Installation
### Using Composer

To install using Composer, you will have to install Composer first.

`curl -s https://getcomposer.org/installer | php`

Create a composer.json file in your project root.

```json
{
  "require": {
    "seigler/neat-charts": "@dev"
  }
}
```

Tell Composer to install the required dependencies.

`php composer.phar install`

If you want to use the autoloading provided by Composer, add the following line to your application file.

`require 'vendor/autoload.php';`

You are now ready to use NeatCharts.

### Install NeatCharts manually

Just download the NeatCharts class and require it in your application file.

`require 'NeatCharts.php';`

## Usage
```php
Header('Content-type: image/svg+xml; charset=utf-8');

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

* Demo output caching based on http://www.the-art-of-web.com/php/buffer/
* Chart appearance based on advice found at http://vis4.net/blog/posts/doing-the-line-charts-right/
