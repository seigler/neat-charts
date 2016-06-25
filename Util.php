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
}

