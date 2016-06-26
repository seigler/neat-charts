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
}
