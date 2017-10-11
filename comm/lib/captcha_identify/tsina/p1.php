<?php
//验证码识别插件 tsina_p1 by ziv 2011/7/20
require_once COMM_PATH . '/../duap/protected/lib/reach/program_plug/tsina_concern/access.php';
require_once COMM_PATH .'/lib/spider/my_spider/tsina.login.php';

class tsina_p1{
	public $parent; 
	public $img_file;
	public $img_dir; 
	public $undo_filename='undo'; 
	public $filter_filename='result'; 
	public $undo_path; 
	public $filter_path; 
 
	public function __construct(){ 
		my_spider::$time_out = 20; 
	}
	public function __destruct(){ 
	}
	public function get_identify(){  
		//获取图片
		$r=$this->get_img();		//print_R($this->img_file);   
		if(!$r) return false;

		//保存图片 
		$r=$this->save_img();		
		if(!$r) return false;

		//过滤 
		$r=$this->filter_img();		
		if(!$r) return false; 

		//识别 
		$r=$this->identify_img();		
		if(!$r) return false;  

		return true; 
	}
	 
	public function get_img(){
		$a=null; 
		$a['cookie_jar']=$this->get_cookie_jar();  
		$time1=time().rand(100,999);
		$a['url']=cm_url::get('tsina_weibo').'/pincode/pin1.php?lang=zh&r='.$time1.'&rule'; 
		$a['browser'] = $this->parent->get_proxy_browser();  
		if($this->parent->ref) $a['ref'] = $this->parent->ref;
		else $a['ref'] = cm_url::get('tsina_weibo')."/".$this->parent->uid;  
		$header[]="Accept: image/png,image/*;q=0.8,*/*;q=0.5";
		//$header[]="Accept-Language: zh-cn,zh;q=0.5"; 
		//$header[]="Accept-Encoding: gzip, deflate";
		//$header[]="Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7";
		//$header[]="Connection: keep-alive";
		$a['header'] = $header;
		$a['proxy'] = $this->parent->proxy;  
		//$cookie_key = 'tsina_p1get_img'.$this->parent->uid;
		$con_ary=my_spider::get_info_from_curl($a);   
		//$this->parent->cookie_jar = $con_ary['cookie_jar'];
		//print_R($con_ary['body']);exit; 
		$img_f=$con_ary['body']; 
		if(!$img_f) return false;  
		$this->img_file=$img_f;

		//echo '<br/>';echo 'a=';print_R($a);   echo '<br/>'; 

  

		return true;  
	}
	
	public function save_img()
	{
		if(!$this->img_file) return false; 
		 
		$t_b_path = comm_config::$tmp_path;
		$tag='/'.$this->parent->path_identify.'/'.$this->parent->plug_name; 
		$dir_path = my_spider::mkdir_by_date(time(),$t_b_path,$tag);  
		$f_rand=time().rand(1,999999);
		$dir=$dir_path.'/'.$f_rand;
		mkdir($dir,0755);
		$dir=realpath($dir);
		if(!$dir) 
		{
			$this->parent->error='error:dir';
			return false;
		}
		$fname=$dir.'/'.$this->undo_filename.'.jpg'; 

		$r=files::write_file($fname,$this->img_file);  
		if(!$r){
			$this->parent->error=files::$error;
			return false;
		}
		$this->undo_path=$fname;
		$this->img_dir=$dir; 
		return true;
	}

	public function filter_img() 
	{
		if(!$this->undo_path || !$this->img_dir) return false; 
		$fname2=$this->img_dir.'/'.$this->filter_filename.'.jpg'; 
		$image = new Imagick($this->undo_path); 
		$image->setImageColorSpace(2); 
		$image->gaussianBlurImage(1,100000);  
		$image->sigmoidalContrastImage(true,8,0); 
		$image->randomThresholdImage(64100,64100);  
		$image->writeImage($fname2); 
		$this->filter_path=$fname2; 
		return true;  
	}
	
	public function identify_img() 
	{
		//通过asp 执行ocr exe webservice 得到,粗略的字符串
		if(!$this->img_dir) return false;  
		$url=cm_url::get('crontab_webservice').'/tesseract.exe.asp?n='.$this->filter_filename.'&d='.$this->img_dir.'&t=jpg&r='.time();
 		$r2=file_get_contents($url);
		if(!$r2) {
			$this->parent->error='tesseract error';
			return false;
		} 
 		$fname3=$this->img_dir.'/'.$this->filter_filename.'.txt';   
		for($i=0;$i<50;$i++)
		{
			$r3=@file_get_contents($fname3);
			if($r3) break;
			usleep(100000);
		} 
		if(!$r3) return false; 
		///
   //$r3="J-6'11=?";        
		//深加工1: 替换 疑似 数字的英文为数字 
		require_once COMM_PATH.'/lib/captcha_identify/general.php'; 
		$r=captcha_identify_general::replace_abc2n($r3);			//print_R($r);echo '<br/>';//exit;   
		///

		//深加工2: 匹配出 nn +-* nn = ? 的格式 
		$preg1="/^(.*?\d{1,2})(.*?)(\d{1,1}[^\d=:\?]{0,2}\d{0,1})(.*)$/";     
		preg_match($preg1,$r,$matches);
		/// 

		//深加工3: 根据格式不同位置的特点细处理  
			$matches[1] = preg_replace('/[^\d]/is','',$matches[1]);

			$matches[2] = str_replace("--","-",$matches[2]); 
			$matches[2] = str_replace("`","*",$matches[2]);  
			$matches[2] = preg_replace('/[^\+\-\*]/is','',$matches[2]);
			$matches[2] = preg_replace('/.*\*{1,1}.*/','*',$matches[2]); 
			$matches[2] = preg_replace('/.*\+{1,1}.*/','+',$matches[2]);   
			if(!$matches[2])
			{
				if($matches[1]<10 && $matches[3]<10) $matches[2]='*';
				else $matches[2]='-';  
			} 
			$matches[2]=substr($matches[2],0,1);  
			if($matches[2]=='-')
			{ 
				if(intval($matches[1])<=intval($matches[3]))
				{
					if($matches[1]<10) $matches[2]='*';   
					else 
					{
						$matches[2]='-';
						$matches[3]=substr($matches[3],0,1); 
					}
				}
				if($matches[1]<10 && $matches[3]<10) $matches[2]='*'; 
			}
			if($matches[1]<10 && $matches[2]!='+' && $matches[3]>=10) $matches[3]=substr($matches[3],0,1);  
			
			$matches[3] = preg_replace('/[^\d]/is','',$matches[3]); 

			$matches[4] = str_replace(":7","=?",$matches[4]);
			$matches[4] = str_replace("7","?",$matches[4]);
			$matches[4] = str_replace("=Q","=?",$matches[4]);
			$matches[4] = preg_replace("/^:.{0,1}/","=",$matches[4]); 
			$matches[4] = preg_replace("/=.?/","=?",$matches[4]);   
			$matches[4] = preg_replace("/[^\d]{1,1}\?/","=?",$matches[4]);  
			$matches[4] = preg_replace("/.*?=.*?\??.{0,}/","=?",$matches[4]);     

			if($matches[2]=='*')
			{
				if($matches[1]>10) $matches[1]=substr($matches[1],0,1); 
				if($matches[3]>10) $matches[3]=substr($matches[3],0,1); 
				$matches[4]='=?'; 
			}
			//print_R($matches);exit;   
		///
		
		if($matches[4]=='=?') @eval('$this->parent->result='.$matches[1].$matches[2].$matches[3].';');   
 		//print_R($this->parent->result);exit;  
		return true;
	}
	
	public function get_cookie_jar() 
	{
		if($this->parent->cookie_jar) return $this->parent->cookie_jar; 
	
		$user['user']=$this->parent->user_name;
		$user['pwd']=$this->parent->pwd;

		//echo '<br/>';echo 'user=';print_R($user); echo '<br/>';
 
		tsina_login::$user = $user;
		tsina_login::$process_id = $this->parent->process_id; 

		//获取浏览器信息 
		$browser = $this->parent->get_proxy_browser(); 

		if($this->parent->proxy) tsina_login::$proxy = $this->parent->proxy;  

		//模拟登录
	
		$cookie_jar = tsina_login::simulate_login();
		if(!$cookie_jar){
			$this->parent->error='simulate_login_error';
			return false;
		}
		$this->parent->cookie_jar=$cookie_jar;
		return $this->parent->cookie_jar; 
	}
	//出现验证码后再次发文 过程如下:
	/*	
		http://weibo.com/mblog/publish.php?rnd=0.18961859273127757&content=%E6%88%91%E6%88%90%E4%B8%BA%E6%8B%89%E7%BD%91%E4%BB%A3%E8%A8%80%E4%BA%BA%E5%95%A6%EF%BC%81%E5%B0%BC%E7%8E%9B%E8%A6%81%E4%B8%8D%E8%A6%81%E4%B8%80%E8%B5%B7%E7%8E%A9%EF%BC%9F%E6%9C%89%E6%9C%A8%E6%9C%89~~%E6%9C%89%E6%9C%A8%E6%9C%896&pic=&retcode=&styleid=1
		http://weibo.com/pincode/pin1.php?lang=zh&r=1311668500663&rule
		http://weibo.com/mblog/publish.php?rnd=0.24226979063204435&door=30&token=05932e14f786a0a0118a7227cf9bca09
		{"code":"A00006","data":"bda022b1bf7ade428bd36956f61e38e9"} 成功
		http://weibo.com/mblog/publish.php?rnd=0.18838596466215995&door=5&token=05932e14f786a0a0118a7227cf9bca09
		{"code":"R01409"} 失败 
		http://weibo.com/mblog/publish.php?rnd=0.5184178198867331&content=%E6%88%91%E6%88%90%E4%B8%BA%E6%8B%89%E7%BD%91%E4%BB%A3%E8%A8%80%E4%BA%BA%E5%95%A6%EF%BC%81%E5%B0%BC%E7%8E%9B%E8%A6%81%E4%B8%8D%E8%A6%81%E4%B8%80%E8%B5%B7%E7%8E%A9%EF%BC%9F%E6%9C%89%E6%9C%A8%E6%9C%89~~%E6%9C%89%E6%9C%A8%E6%9C%896&pic=&retcode=bda022b1bf7ade428bd36956f61e38e9&styleid=1
		{"code":"A00006","data":{"html":"<li c ... 
	*/
	public function post_for_article($a) 
	{
		if(!is_array($a) || !$a['captcha']) return false;  
		$browser=$this->parent->get_proxy_browser(); 
		$cookie_jar=$this->get_cookie_jar(); 
		
		//过程2
		require_once COMM_PATH . '/../config/url.config.php';
		$y = array();
		$y['url']=cm_url::get('tsina_weibo').'/mblog/publish.php?rnd='.substr(str_replace(" ", "",microtime()),0,18); 
		$y['post_request']['door']=$a['captcha'];
		$y['post_request']['token']=$this->parent->token; 
		$y['browser']=$browser;
		$y['cookie_jar']=$cookie_jar;  
		if($this->parent->ref) $y['ref'] = $this->parent->ref;
		else $y['ref'] = cm_url::get('tsina_weibo')."/".$this->parent->uid;
		$y['proxy'] = $this->parent->proxy; 

		//$header[]="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		//$header[]="Accept-Language: zh-cn,zh;q=0.5";
		//$header[]="Accept-Encoding: gzip, deflate";
		//$header[]="Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7"; 
		//$header[]="Connection: keep-alive"; 
		//$header[]="Content-Type: application/x-www-form-urlencoded; charset=UTF-8";
		//$y['header'] = $header;
		$rt=my_spider::get_info_from_curl($y);  
		//echo '<br/>';echo 'y=';print_R($y); echo '<br/>';

 		if(!$rt['body'])
		{ 
			$this->parent->error='post_captcha_2_error'; 
			return false;
		}
		$rt = json_decode($rt['body'],true);

		//echo '<br/>';echo 'rt=';print_R($rt);echo '<br/>';
 
		if($rt['code'] != 'A00006'){//正确
			if($rt['code'] == 'R01409'){//验证码输入错误 
				$this->parent->error='R01409'; 
			}
			return false;
		}
		if(!$rt['data'])
		{
			$this->parent->error='post_captcha_2.1_error'; 
			return false;
		}
		///
 
		return $rt['data'];
	}



	public function post_for_concern($a)  
	{
		if(!is_array($a) || !$a['captcha']) return false;  
		$browser=$this->parent->get_proxy_browser(); 
		$cookie_jar=$this->get_cookie_jar(); 
		
		//过程2
		require_once COMM_PATH . '/../config/url.config.php';
		$y = array();
		$y['url']=cm_url::get('tsina_weibo').'/attention/aj_addfollow.php?rnd='.substr(str_replace(" ", "",microtime()),0,18); 
		$y['post_request']['door']=$a['captcha'];
		$y['post_request']['token']=$this->parent->token; 
		$y['browser']=$browser;
		$y['cookie_jar']=$cookie_jar;  
		if($this->parent->ref) $y['ref'] = $this->parent->ref;
		else $y['ref'] = cm_url::get('tsina_weibo')."/".$this->parent->uid;
		$y['proxy'] = $this->parent->proxy; 

		$rt=my_spider::get_info_from_curl($y);  
		//echo '<br/>';echo 'y=';print_R($y); echo '<br/>';

 		if(!$rt['body'])
		{ 
			$this->parent->error='post_captcha_2_error'; 
			return false;
		}
		$rt = json_decode($rt['body'],true);

		//echo '<br/>';echo 'rt=';print_R($rt);echo '<br/>';
 
		if($rt['code'] != 'A00006'){//正确
			if($rt['code'] == 'R01409'){//验证码输入错误 
				$this->parent->error='R01409'; 
			}
			return false;
		}
		if(!$rt['data'])
		{
			$this->parent->error='post_captcha_2.1_error'; 
			return false;
		}
		///
 
		return $rt['data'];
	}


}//end class