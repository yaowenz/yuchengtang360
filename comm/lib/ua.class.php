<?php
//2011/12/5 by ziv 浏览器 ua

class comm_ua
{
	//随机获取浏览器 ua
	static function get_rand_ua(){
		$browser = array();
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; CIBA; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SE 2.X MetaSr 1.0)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Tablet PC 2.0)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.98 Safari/534.13';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; GTB6.6; SV1; TencentTraveler 4.0)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; InfoPath.1; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; QQDownload 677; CIBA; .NET CLR 2.0.50727)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ) AppleWebKit/533.9 (KHTML, like Gecko) Maxthon/3.0 Safari/533.9';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; BaiduGame)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; Apache; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; QQDownload 675; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.30729; .NET CLR 3.5.30729)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; CIBA)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB6.6; .NET CLR 2.0.50727; .NET CLR 1.1.4322; Alexa Toolbar)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; CIBA)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; IEShow Toolbar; IEShow herowyToolBar)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; @?xV{U3lV`eMjEZxR|cwmRD1s?0Rj.i*Fk9W+U; .NET4.0C; .NET4.0E; Alexa Toolbar)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; SE 2.X MetaSr 1.0)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; Tablet PC 2.0; .NET4.0C; .NET4.0E)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; 4399Box.560)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.11 (KHTML, like Gecko) Chrome/9.0.570.0 Safari/534.11';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; TencentTraveler ; .NET CLR 2.0.50727; .NET4.0C)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET4.0C; .NET4.0E)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C); QQBrowser/5.0.6657.400 (trident)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ) AppleWebKit/533.9 (KHTML, like Gecko) Maxthon/3.0 Safari/533.9';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; CIBA)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.205 Safari/534.16';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; GTB6.6; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 2.0.50727)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; SE 2.X MetaSr 1.0)';
		$browser[] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; GTB6.6; InfoPath.2)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.2)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; Sicent; WoShiHoney.B; .NET CLR 2.0.50727)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; GTB6.6)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; InfoPath.2)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; Tablet PC 2.0; .NET4.0C; SE 2.X MetaSr 1.0)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; Media Center PC 6.0)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 6.1; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; InfoPath.2; LEN2)';
		$browser[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322;TencentTraveler)';
		$index = array_rand($browser,1);
		return $browser[$index];
	}
}//end class