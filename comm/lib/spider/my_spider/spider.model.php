<?php
//spider模型类
class spider_model{
	private $user;
	private $proxy;
	private $process_id;
	private $user_browser;
	private $domain;
	private $plug_id;
	private $pt;

	function set_user($user = null){
		$this->user = $user;
	}
	function get_user(){
		return $this->user;
	}

	function set_proxy($proxy = null){
		$this->proxy = $proxy;
	}
	function get_proxy(){
		return $this->proxy;
	}

	function set_process_id($process_id = ''){
		$this->process_id = $process_id;
	}
	function get_process_id(){
		return $this->process_id;
	}

	function set_user_browser($user_browser = null){
		$this->user_browser = $user_browser;
	}
	function get_user_browser(){
		return $this->user_browser;
	}

	function set_domain($domain = ''){
		$this->domain = $domain;
	}
	function get_domain(){
		return $this->domain;
	}

	function set_plug_id($plug_id){
		$this->plug_id = $plug_id;
	}
	function get_plug_id(){
		return $this->plug_id;
	}

	function set_pt($pt){
		$this->pt = $pt;
	}
	function get_pt(){
		return $this->pt;
	}
}