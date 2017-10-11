<?php

require_once dirname(__FILE__).'/../../../config/cache.config.php';

/**
 * 缓存类,放到临时缓存中，可以指定缓存时间，如果不指定，缓存有效期到内存不足或服务器重启时
 *
 * @author jay
 */


class open_temp_cache {

    private $memcache_client = array();
	public $mem_config_str;


    /**
     *add to cache， in the time defined
     * @param <type> $key
     * @param <type> $value
     * @param <type> $expire  a Unix timestamp or a number of seconds starting from current time, default 0 means the item will never expire
     * @return <boolean> true or false
     */
    public function add($key, $value, $expire=0) {

        $mc = $this->_get_cacheClient($key);
        if($mc == false){
            return false;
        }
        return $mc->add($key, $value, 0, $expire);
    }

    /**
     * update date the cache within the time defined
     * @param <type> $key
     * @param <type> $var
     * @param <type> $expire a Unix timestamp or a number of seconds starting from current time, default 0 means the item will never expire
     * @return <boolean> true if success, false if not exist or update failtrue
     */
    public function update($key, $var, $expire=0) {
        $mc = $this->_get_cacheClient($key);
        if($mc == false){
            return false;
        }
        return  $mc-> replace($key, $var, 0, $expire);
    }

    /**
     *save or update cache, within the time defined
     * @param <type> $key
     * @param <type> $var
     * @param <type> $expire a Unix timestamp or a number of seconds starting from current time, default 0 means the item will never expire
     * @return <boolean> true if success, false if failtrue
     */
    public function saveOrUpdate($key, $var, $expire=0) {
        $mc = $this->_get_cacheClient($key);
        if($mc == false){
            return false;
        }
        return  $mc-> set($key, $var, 0, $expire);
    }

    /**
     * delete from cache
     * @param <type> $key
     * @return <boolean> 是否删除成功
     */
    public function  delete($key) {
        $mc = $this->_get_cacheClient($key);
        if($mc == false){
            return false;
        }
        return  $mc->delete($key);
    }

    /**
     * search value from cache, you can pass array key to get multi value.
     * the result will contain only found key-value pair
     *
     * @param <type> $key
     * @return <booean>  result
     */
    public  function get($key) {
		if(isset($_GET["__DEBUG"]) && $_GET["__DEBUG"] == 1) return false;
        $mc = $this->_get_cacheClient($key);
        if($mc == false){
            return false;
        }
        return $mc->get($key);
    }

    /**
     * incr key, return $amount if the value does not exist
     * @param <type> $key
     * @param <type> $amount optional, the amount to increment
     * @return <type>
     */
    public function increment($key, $amount=1) {
        $mc = $this->_get_cacheClient($key);
        if($mc == false){
            return false;
        }
        $result =  $mc->increment($key, $amount);
        if(False == $result) {
            $this->saveOrUpdate($key, $amount);
            return $amount;
        }
        return $result;
    }

    /**
     * decrement $key
     * @param <type> $key
     * @param <type> $amount optional, the amount to decrement
     * @return <type>
     */
    public function decrement($key, $amount=1) {
        $mc = $this->_get_cacheClient($key);
        if($mc == false){
            return false;
        }
        return   $mc->decrement($key, $amount);
    }

    /**
     * remove all cache
     */
    public function flush() {
        $mc = $this->_get_cacheClient($key);
        if($mc != false){
             $mc-> flush();
        }
    }

    /**
     *  release the memcache connection, it will not work on pconnect
     */
    public function close() {
        foreach ($this->memcache_client as $key=>$value) {
            if(FALSE != $value) {
                $value->close();
            }
        }
    }

    /**
     * get memcached client
     * @return <Memcache> client
     */
    private function _get_cacheClient($key) {


//        if(FALSE == $this->memcache_client) {
//            $temp_server = cm_cache_config::get('MEMCACHE_SERVER');
//            $server_config = explode(":", $temp_server);
//            $server = $server_config[0];
//            $port = $server_config[1];
//            $this->memcache_client = new Memcache;
//            $result =  $this->memcache_client->pconnect($server, $port);
////            if(!$result){
////                throw new WbException("网络繁忙，请稍后再试cd");
////            }
//        }
//        return $this->memcache_client;
		
		if($this->mem_config_str) $temp_server = $this->mem_config_str;
        else $temp_server = cm_cache_config::get('MEMCACHE_SERVER');

        $server_array = explode(";", $temp_server);
        $count = count($server_array);
        $hash = $this->_time33Hash($key);
        $r = abs(fmod($hash, $count));
        if(!empty($this->memcache_client[$r])) {
            return $this->memcache_client[$r];
        } else {
            $sever_config = explode(":", $server_array[$r]);
            $server = $sever_config[0];
            $port  = $sever_config[1];
            $this->memcache_client[$r] = new Memcache;
            $result = $this->memcache_client[$r]->pconnect($server, $port);
            if(!$result){
                throw new WbException("网络繁忙，请稍后再试cd");
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

	/**
     * 根据Tag进行缓存
     *
     * @param string $key
     * @param object $obj
     * @param string $tag
     * @param int $exp
     * @param int $errorLevel
     * @return boolean
     */
    public function saveByTag( $key, $obj, $tag, $exp=0) {
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
    
    /**
     * 删除整个Tag标记的缓存
     * 
     * @param string $tag
     * @return boolean
     */
    public function removeTag($tag) {		
		$keys = $this->get($tag);
	    if (!empty($keys)) {
	    	foreach($keys as $key) {
	    		$this->delete($key);
	    	}
	    }
	    return $this->delete($tag);
    }

	/**
     * 按Tag标记获取数据
     * 
     * @param string $tag
     * @return array
     */
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
}



/**
 * no cache support
 *
 * 
 */
class Closetemp_cache {


    public  function add($key, $value, $expire=0) {
        return false;
    }

    public function update($key, $var, $expire=0) {
        return false;
    }

    public function saveOrUpdate($key, $var, $expire=0) {
        return false;
    }

    public function  delete($key) {
        return false;
    }

    public function get($key) {
        return null;
    }

    public function increment($key, $amount) {
        return null;
    }

    public function decrement($key, $amount) {
        return null;
    }

    public function flush() {

    }

    public  function close() {

    }
}



/**
 * 持久缓存, 操作对象将永久保留在缓存中，并被持久化
 *
 * 
 */
class seria_cache {

    private $memcache_client = array();
	public $mem_config_str;

    /**
     *add to cache， in the time defined
     * @param <type> $key
     * @param <type> $value
     * @return <boolean> true or false
     */
    public function add($key, $value) {

        $mc = $this->_get_cacheClient($key);
        return $mc->add($key, $value, 0);
    }

    /**
     * update date the cache within the time defined
     * @param <type> $key
     * @param <type> $var
     * @return <boolean> true if success, false if not exist or update failtrue
     */
    public function update($key, $var) {
        $mc = $this->_get_cacheClient($key);
        return  $mc-> replace($key, $var, 0);
    }

    /**
     *save or update cache, within the time defined
     * @param <type> $key
     * @param <type> $var
     * @return <boolean> true if success, false if failtrue
     */
    public function saveOrUpdate($key, $var) {
        $mc = $this->_get_cacheClient($key);
        return  $mc-> set($key, $var);
    }

    /**
     * delete from cache
     * @param <type> $key
     * @return <boolean> 是否删除成功
     */
    public function  delete($key) {
        $mc = $this->_get_cacheClient($key);
        return  $mc->delete($key);
    }

    /**
     * search value from cache, you can pass array key to get multi value.
     * the result will contain only found key-value pair
     *
     * @param <type> $key
     * @return <booean>  result
     */
    public function get($key) {
		if(isset($_GET["__DEBUG"]) && $_GET["__DEBUG"] == 1) return false; 
        $mc = $this->_get_cacheClient($key);
        return $mc->get($key);
    }

    /**
     * incr key, return $amount if the value does not exist
     * @param <type> $key
     * @param <type> $amount optional, the amount to increment
     * @return <type>
     */
    public function increment($key, $amount=1) {
        $mc = $this->_get_cacheClient($key);
        $result =  $mc->increment($key, $amount);
        if(False == $result) {
            $this->saveOrUpdate($key, $amount);
            return $amount;
        }
        return $result;
    }

    /**
     * decrement $key
     * @param <type> $key
     * @param <type> $amount optional, the amount to decrement
     * @return <type>
     */
    public function decrement($key, $amount=1) {
        $mc = $this->_get_cacheClient($key);
        return   $mc->decrement($key, $amount);
    }
    /**
     *  release the memcache connection, it will not work on pconnect
     */
    public function close() {
        foreach ($this->memcache_client as $key=>$value) {
            if(FALSE != $value) {
                $value->close();
            }
        }
    }
    /**
     * get memcached client
     * @return <Memcache> client
     */
    private  function _get_cacheClient($key) {
        //使用php扩展的方式
		if($this->mem_config_str) $temp_server = $this->mem_config_str;
        else $temp_server = cm_cache_config::get('MEMCACHEDB_SERVER');
        $server_array = explode(";", $temp_server);
        $count = count($server_array);
        $hash = $this->_time33Hash($key);
        $r = abs(fmod($hash, $count));
        if(!empty($this->memcache_client[$r])) {
            return $this->memcache_client[$r];
        } else {
            $sever_config = explode(":", $server_array[$r]);
            $server = $sever_config[0];
            $port  = $sever_config[1];
            $this->memcache_client[$r] = new Memcache;
            $result = $this->memcache_client[$r]->pconnect($server, $port);
            if(!$result){
                die("网络繁忙，请稍后再试cdb");
            }
            return $this->memcache_client[$r];
        }
    //            Doo::loadClass("cache/memcached-client");
    //           $temp_server = cm_cache_config::get('MEMCACHEDB_SERVER');
    //           $server_array = explode(";", $temp_server);
    ////  选项设置
    //$options = array(
    //    'servers' => $server_array, //memcached 服务的地址、端口，可用多个数组元素表示多个 memcached 服务
    //    'debug' => true,  //是否打开 debug
    //    'compress_threshold' => 10240,  //超过多少字节的数据时进行压缩
    //    'persistant' => false  //是否使用持久连接
    //    );
    ////  创建 memcached 对象实例
    //$mc = new memcached($options);
    }

    private function _time33Hash($str) {
        $hash = 0;
        $n = strlen($str);
        for ($i = 0; $i <$n; $i++) {
            $hash += ($hash <<5 ) + ord($str[$i]);
        }
        return $hash;
    }

	/**
     * 根据Tag进行缓存
     *
     * @param object $obj
     * @param string $key
     * @param string $tag
     * @param int $exp
     * @param int $errorLevel
     * @return boolean
     */
    public function saveByTag($key, $obj, $tag) {
	    $this->saveOrUpdate($key, $obj);	
		$keys = $this->get($tag);
	   	if (!empty($keys) && is_array($keys)) {
	    	$keys[] = $key;
	    	$keys = array_unique($keys);
	    } else {
	    	$keys = array($key);
	    }
	    return $this->saveOrUpdate($tag, $keys);
    }
    
    /**
     * 删除整个Tag标记的缓存
     * 
     * @param string $tag
     * @return boolean
     */
    public function removeTag($tag) {
	    $keys = $this->get($tag);
	    if (!empty($keys)) {
	    	foreach($keys as $key) {
	    		$this->delete($key);
	    	}
	    }
	    return $this->delete($tag);    	
    }

	/**
     * 按Tag标记获取数据
     * 
     * @param string $tag
     * @return array
     */
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
}



 
class temp_cache extends open_temp_cache{
  
    
}



?>
