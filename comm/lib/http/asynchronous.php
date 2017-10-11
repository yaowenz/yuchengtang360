<?php
//利用 fsockopen 函数做异步访问 2012/9/25
class r_asynchronous
{
	public function request($a=array())
	{
		if(!$a['host'] || !$a['uri']) return false;
		$fp = fsockopen($a['host'], 80, $errno, $errstr, 30);
		if (!$fp) {
			echo "$errstr ($errno)<br />\n";
		} else {
			$out = "GET ".$a['uri']." HTTP/1.1\r\n";
			$out .= "Host: ".$a['host']."\r\n";
			$out .= "Connection: Close\r\n\r\n"; 
			fwrite($fp, $out);
			if($a['show']) while (!feof($fp)){echo fgets($fp, 128);}
			fclose($fp);
			return true;
		}
	}

	public function curl_r($a=array())
	{
		if(!$a['url']) return false;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $a['url']);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
		curl_exec($ch);
		curl_close($ch);
		return true;
	}
}