<?php
// https://poloniex.com/public?command=returnChartData&currencyPair=BTC_XMR&start=1466740210&end=9999999999&period=300

class Util {
	public static function getJson($url) {
		if (empty($url)) {
		  trigger_error('Missing or empty JSON url');
		}
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
#								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
#								curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
#								curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "/VeriSignClass3PublicPrimaryCertificationAuthority-G5.pem");

		$result = curl_exec($ch);
//		$result = file_get_contents('https://api.flickr.com/services/feeds/photos_public.gne?id=50219999@N00&tags=Sketches&lang=en-us&format=json&nojsoncallback=1');
		$result = json_decode($result) or trigger_error('Couldn\'t parse JSON');
#		foreach ($result->items as $item) { }
		return $result;
	}

  public static function median($arr) {
      sort($arr);
      $count = count($arr); //total numbers in array
      $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
      if($count % 2) { // odd number, middle is the median
          $median = $arr[$middleval];
      } else { // even number, calculate avg of 2 medians
          $low = $arr[$middleval];
          $high = $arr[$middleval+1];
          $median = (($low+$high)/2);
      }
      return $median;
  }
}
