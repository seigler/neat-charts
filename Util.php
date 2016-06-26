<?php

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
#        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
#        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
#        curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "/VeriSignClass3PublicPrimaryCertificationAuthority-G5.pem");

		$result = curl_exec($ch);
		$result = json_decode($result) or trigger_error('Couldn\'t parse JSON');
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
