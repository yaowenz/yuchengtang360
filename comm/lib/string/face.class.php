<?php
/**
	edit by zx 2010-11-19 表情相关
 */
require_once dirname(__FILE__).'/../../config/url.config.php';

class cm_face{ 

	//从cache取过滤规则列表  
	static function translate($string){ 
		$pattern = '/\{img:(\d+)}/ius';
		$replacement = "<img src='" . cm_url::get('microblog') . "/global/images/face/\$1.gif'>";
		$content = preg_replace($pattern, $replacement, $string);
 
		return $content;
	}
 
}//end class