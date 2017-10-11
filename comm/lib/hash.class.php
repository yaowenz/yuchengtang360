<?php
//2012/2/10 by jay hash取值

class _hash
{
	private $is_hash_num = false;
	private $_max;
	private $_min;
	private $ary_key_list;
	
	//按照key获取一个哈希值
	public function get_hash($str){
		if(!$str || !is_string($str)){
			return false;
		}
		if(!$this->_max){
			return false;
		}

		$hash = $this->_time33Hash($str);
		//echo $hash;
		$count = $this->_max;
		if($this->is_hash_num){
			$count = $count - $this->_min;
		}
		$rt = abs(fmod($hash, $count));
		if($this->is_hash_num){
			return $rt + $this->_min;
		} else if($this->ary_key_list){
			return $this->ary_key_list[$rt];
		} else {
			return $rt;
		}
	}
	
	//time33核心算法
	public function _time33Hash($str) {
		$hash = 0;
		$n = strlen($str);
		for ($i = 0; $i < $n; $i++) {
			$hash += ($hash <<5 ) + ord($str[$i]);
		}
		return $hash;
	}
	
	//设置需要哈希的数组
	public function set_hash_ary($ary){
		$this->is_hash_num = false;
		$this->_max = count($ary);
		$this->ary_key_list = array_keys($ary);
	}

	//设置数字的最小与最大范围
	public function set_hash_num($min=0,$max=1000){
		$this->_min = $min;
		$this->_max = $max + 1;
		$this->is_hash_num = true;
	}
}//end class