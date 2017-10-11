<?
/**
 * 获取单例
   by zx 2010-5-14
 */
   
class container {

    //对像列表
    static $OBJECTS = array();
    
    /**
     * 设定参数
     * 
     * @param object $obj
     * @param array $params
     * 
     * @return void
     */
    static function processParams(& $obj, $params) {
    	
    	foreach($params as $_key => $_value) {

    		if (property_exists($obj, $_key)) {

    			$obj->$_key = $_value;
    		}
    	}
    }

   	/**
     * 返回指定对象的唯一实例，如果指定类无法载入或不存在，则抛出异常
     *
     * example:
     * <code>
     * $service = container::getSingleton('Service_Products?arg1,arg2');
     * </code>
     *
     * @param string $dsn 要获取的对象的名称
     *
     * @return object
     */
    static function getSingleton($dsn, $fix=null) {

        $_arg = self::parseDsn($dsn);
       	//print_r($_arg) ;
        $_preFix = md5(strtolower($dsn));
        if ($fix <> null) {	
        	$_preFix = md5($_preFix . serialize($fix));	
        }
        //如果对像已经存在，则直接返回
        if (isset(self::$OBJECTS[$_preFix])) {
            return self::$OBJECTS[$_preFix];
        }
	
        if (!class_exists($_arg['className'])) {

            die("No define class '{$_arg[className]}' !!!");
        } else {
        	$obj = new $_arg['className'];
        	
        	if (is_array($_arg['arg'])) {
        		
        		self::processParams($obj, $_arg['arg']);
        	}
        	
        	if (method_exists($obj, 'initMod')) {
        		
        		$obj->initMod();
        	}
        	
            return self::register($obj, $_preFix);
        }
    }
 	/**
     * 返回指定对象的唯一实例，如果指定类无法载入或不存在，则抛出异常
     * 
     * @param string $dsn
     * @param mixed	$ext
     * @return mixed
     */   
    static function getSingletonExt($dsn, $ext = null) {
    	
    	if ($ext !== null) {

    		if (is_array($ext)) {
    			
    			foreach ($ext as $file) {
    				
    				require_once($file);
    			} 
    		} else {
    			
    			require_once($ext);
    		}
    	}
    	
    	return self::getSingleton($dsn, $ext);
    }
    
	/**
     * 返回指定对象的实例，如果指定类无法载入或不存在，则抛出异常
     *
     * example:
     * <code>
     * $service = container::getInstance('Service_Products');
     * </code>
     *
     * @param string $dsn 要获取的对象的名称
     *
     * @return object
     */
    static function getInstance($dsn) {

        $_arg = self::parseDsn($dsn);
	
        if (!class_exists($_arg[className])) {
			
            die("No define class '{$_arg[className]}' !!!");
        } else {
        	
        	$obj = new $_arg[className];
        	
        	if (is_array($_arg['arg'])) {
        		
        		self::processParams($obj, $_arg['arg']);
        	}
        	
        	if (method_exists($obj, 'initMod')) {
        		
        		$obj->initMod();
        	}
            return $obj;
        }
    }
    
    /**
     * 解析对应Module参数
     * 
     * @param   array
     * @return  array
     */
    static function parseDsn($dsn = null) {

        preg_match('/([^?]*)\?{0,1}(.*)/i', $dsn, $_tmpUrl);
        $_ret['className'] = preg_replace('/(.*)\/([^\/]*)$/i', '\\2', $_tmpUrl[1]);
        parse_str($_tmpUrl[2], $_ret['arg']);
		//print_r($_ret);exit;
        return $_ret;
    }

    /**
     * 以特定名字注册一个对象，以便稍后用 registry() 方法取回该对象。
     * 如果指定名字已经被使用，则抛出异常
     *
     * example:
     * <code>
     * // 注册一个对象
     * container::register(new MyObject(), 'my_obejct');
     * .....
     * // 稍后取出对象
     * $obj = container::registry('my_object');
     * </code>
     *
     *
 
     * example:
     * <code>
     * if (!container::isRegister('ApplicationObject')) {
     * 		container::register(new Application(), 'ApplicationObject', true);
     * }
     * $app = container::registry('ApplicationObject');
     * </code>
     *
     * @param object $obj 要注册的对象
     * @param string $name 注册的名字
     * @param boolean $persistent 是否将对象放入持久化存储区
     */
    static function register($obj, $name = null, $persistent = false) {
	
        if (is_null($name)) {
            $name = get_class($obj);
        }
        if (!isset(self::$OBJECTS[$name])) {
            self::$OBJECTS[$name] = $obj;
            return self::$OBJECTS[$name];
        }
    }

    /**
     * 取得指定名字的对象实例，如果指定名字的对象不存在则抛出异常
     *
     * 使用示例参考 container::register()。
     *
     * @param string $name 注册名
     *
     * @return object
     */
    static function registry($name) {
        if (isset(self::$OBJECTS[$name])) {
            return self::$OBJECTS[$name];
        }
    }

    /**
     * 检查指定名字的对象是否已经注册
     *
     * 使用示例参考 container::register()。
     *
     * @param string $name 注册名
     *
     * @return boolean
     */
    static function isRegistered($name) {
        return isset(self::$OBJECTS[$name]);
    }
}
?>