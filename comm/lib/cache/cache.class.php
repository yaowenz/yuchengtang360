<?php

require_once dirname(__FILE__).'/../../../config/cache.config.php';

/**
 * 缓存类,放到临时缓存中，可以指定缓存时间，如果不指定，缓存有效期到内存不足或服务器重启时
 *
 * @author jay
 */


class temp_cache{ 

    private $memcache_client = array();
	public $mem_config_str;
	public $is_seria=false;
	
	public function __call($function_name, $arguments){
		return false; //暂时关闭
	}
/*
	public function key_base_ful($key)
	{
		if(CACHE_BASE_KEY) return $key.CACHE_BASE_KEY;
		else return $key;
	}

	//检查缓存情况
	public function check() {
		$key='temp_cache_check'.time();
		$this->saveOrUpdate($key,'1',5);
		$r=$this->get($key);
		$this->delete($key);
		return $r;
	}


    public function add($key, $value, $expire=15) {
		$key=$this->key_base_ful($key);
        $mc = $this->get_server($key);
        if($mc == false){
            return false;
        }
		$r=$mc->add($key, $value, 0, $expire);
		$mc=null;
        return $r;
    }


    public function update($key, $var, $expire=15) {
		$key=$this->key_base_ful($key);
        $mc = $this->get_server($key);
        if($mc == false){
            return false;
        }
		$r=$mc->replace($key, $var, 0, $expire);
		$mc=null;
        return $r;
    }

    public function saveOrUpdate($key, $var, $expire=15) {
		$key=$this->key_base_ful($key);
        $mc = $this->get_server($key);
        if($mc == false){
            return false;
        }
		$r=$mc->set($key, $var, 0, $expire);
		$mc=null;
        return $r;
    }


    public function delete($key) {
		$key=$this->key_base_ful($key);
        $mc = $this->get_server($key);
        if($mc == false){
            return false;
        }
		$r=$mc->delete($key);
		$mc=null;
        return $r;
    }

    public function get($key) {
		$key=$this->key_base_ful($key);
		$mc = $this->get_server($key);
		if(isset($_GET["__DEBUG"]) && $_GET["__DEBUG"] == 1) 
		{
			$mc->delete($key);
			return false;
		}
        if($mc == false){
            return false;
        }
		$r=$mc->get($key); 
		$mc=null;
        return $r;
    }


    public function increment($key, $amount=1) {
		$key=$this->key_base_ful($key);
        $mc = $this->get_server($key);
        if($mc == false){
            return false;
        }
        $result =  $mc->increment($key, $amount);
        if(False == $result) {
            $this->saveOrUpdate($key, $amount);
            return $amount;
        }
		$mc=null;
        return $result;
    }

    public function decrement($key, $amount=1) {
		$key=$this->key_base_ful($key);
        $mc = $this->get_server($key);
        if($mc == false){
            return false;
        }
		$mc->decrement($key, $amount);
		$mc=null;
        return $r;
    }

    public function flush() {
		$key=$this->key_base_ful($key);
        $mc = $this->get_server($key);
        if($mc != false){
             $mc-> flush();
        }
		$mc=null;
    }

    public function close() {
        foreach ($this->memcache_client as $key=>$value) {
            if(FALSE != $value) {
                $value->close();
            }
        }
    }

    public function get_server($key) {
		$key=$this->key_base_ful($key);
		if($this->mem_config_str) $temp_server = $this->mem_config_str;
        else if(!$this->is_seria) $temp_server = cm_cache_config::get('MEMCACHE_SERVER');
		else $temp_server = cm_cache_config::get('MEMCACHEDB_SERVER'); 
		if(!$temp_server){
			die('cache config error!');
		}

		$server_array = $temp_server;
		if(!is_array($server_array)) die('cache p error');
		$cache_obj=new Memcache;
		
		if(count($server_array)>1)
		{
			foreach($server_array as $k=>$i){
				$server = $i[0];
				$port  = $i[1];
				//$c_r = @$cache_obj->pconnect($i[0], $i[1]);
				$c_r = $cache_obj->pconnect($i[0], $i[1]);
				if(!$c_r) unset($server_array[$k]);
			}
			
			$count=0;
			foreach($server_array as $i){$count +=$i[2];}
			$hash = $this->_time33Hash($key);
			$rt = abs(fmod($hash, $count));
			$tmp=0;
			foreach($server_array as $k=>$i){
				
				if($rt<$i[2]+$tmp) {$r=$k;break;}	
				$tmp=$i[2];
			}
		}
		else $r=0;
		//print_R($server_array);echo '<br/><br/>';
        if(!empty($this->memcache_client[$r])) {
            return $this->memcache_client[$r];
        } else {
            $sever_config = $server_array[$r];
            $server = $sever_config[0];
            $port  = $sever_config[1];
            $this->memcache_client[$r] = $cache_obj;
            $result = $this->memcache_client[$r]->pconnect($server, $port);
            if(!$result){
                die("网络繁忙，请刷新再试");
            }
            return $this->memcache_client[$r];
        }
    }

     private function _time33Hash($str) {
        $hash = 0;
        $n = strlen($str);
        for ($i = 0; $i <$n; $i++) {
            $hash += ($hash <<5 ) + ord($str[$i]);
        }
        return $hash;
    }

    public function saveByTag($key,$obj,$tag,$exp=15) {
		$key=$this->key_base_ful($key);
		$this->saveOrUpdate($key, $obj, $exp);
		$keys = $this->get($tag);
	   	if (!empty($keys) && is_array($keys)) {
	    	$keys[] = $key;
	    	$keys = array_unique($keys);
	    } else {
	    	$keys = array($key);
	    }
	    return $this->saveOrUpdate($tag, $keys, $exp);
    }
    
    public function removeTag($tag) {
		$keys = $this->get($tag);
	    if (!empty($keys)) {
	    	foreach($keys as $key) {
	    		$this->delete($key);
	    	}
	    }
	    return $this->delete($tag);
    }

    public function getByTag($tag,$needKey=false) {		
		$ary = array();
	    $keys = $this->get($tag);
	    if (!empty($keys)) {
	    	foreach($keys as $key) {
				if(!$needKey){
					$ary[] = $this->get($key);
				}else{
					$ary[$key] = $this->get($key);
				}
	    	}
	    }
		return $ary;
    }
*/
}//end class 

class seria_cache extends temp_cache{
	public function __construct(){
		$this->is_seria=true;
	}
}