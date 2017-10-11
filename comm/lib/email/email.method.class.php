<?php
/**
 * 常用eamil 相关
 * by zx 2010-8-12
 */
class email_method{

	//email 正则-验证email地址格式合法性
	static function check($email)
	{
		return (strlen($email)>6) && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}

	//根据email地址，返回webmail url
	static function open_url($email)
	{
		$ary = explode("@",$email);
                if($ary[1]=="gmail.com"){
                    $emailUrl = " http://www.".$ary[1];
                } else if($ary[1]=="hotmail.com"){
                    $emailUrl = " http://mail.live.com";
                } else {
                    $emailUrl = " http://mail.".$ary[1];
                }
		return $emailUrl;
	}

	static function replace($email){
		if(self::check($email)){
			$ary = explode('@',$email);
			$mail_name = $ary[0];
			$length = strlen($mail_name);
			$new_mail_name = '';
			switch($length){
				case 1;
					$new_mail_name = '***';
					break;
				case 2;
					$start = substr($mail_name,0,1);
					$new_mail_name = $start.'***';
					break;
				case 3;
					$start = substr($mail_name,0,2);
					$new_mail_name = $start.'***';
					break;
				default:
					$start = substr($mail_name,0,2);
					$end = substr($mail_name,-1,1);
					$new_mail_name = $start.'***'.$end;
					break;
			}
			return $new_mail_name.'@'.$ary[1];
		}
	}
}
?>