<?php
/*
	comm用户类
*/
class comm_user 
{
	static $key_comm_user_get='cous_tqdianyiyc_ffgv1'; //key for cache
 
	static function get_login_user_cache()
	{
	}

	static function get_login_user($u) 
	{
		$ava=get_usermeta($u->ID,'simple_local_avatar');
		if(!$ava)
		{
			$burl='http://img.guangbaobei.net/site_img/tx/';
			$ava['full'] = $burl.'full.png';
			$ava[64] = $burl.'64.png';
			$ava[16] = $burl.'16.png';
			$ava[96] = $burl.'96.png';
			$ava[32] = $burl.'32.png';
			$ava[35] = $burl.'64.png';
		} 
		$u->tx=$ava;
		return $u;
	}

}//end class