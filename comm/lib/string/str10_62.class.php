<?php
//10进制数字格式字符串到62进制，这套也是新浪微博用的那套。
//C:\PHP\php.exe C:\job\guangbaobei\codes\comm\lib\string\str10_62.class.php
//$o = new str10_62;
//echo $o->midToStr('3564134182784976');echo '<br>';echo $o->StrTomid('zqUqebGuY');
//echo $o->intTo62('1111087');echo '<br>';echo $o->s62Toint('L2F4');

//$str=3564134182784976;  
//$object = new Base62();  
//echo $object->base62_encode($str) . "\n";  
//echo $object->base62_decode($object->base62_encode($str)) . "\n";

class Base62{  
    private $string = "vPh7zZaSotcVfIMxJi6XljYTKu31Brdge9CNp0OWwA2LyU4bGq5HQ8REnmDkFs";  
    public function base62_encode($str) {  
        $out = '';   
        for($t=floor(log10($str)/log10(62)); $t>=0; $t--) {  
            $a = floor($str / pow(62, $t));  
            $out = $out.substr($this->string, $a, 1);   
            $str = $str - ($a * pow(62, $t));  
        }     
        return $out;  
    }     
    public function base62_decode($str) {  
        $out = 0;  
        $len = strlen($str) - 1;  
        for($t=0; $t<=$len; $t++) {  
            $out = $out + strpos($this->string, substr($str, $t, 1)) * pow(62, $len - $t);  
        }     
        return substr(sprintf("%f", $out), 0, -7);  
    }     
}  

class str10_62{

	public $str62keys = array (
	"0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q",
	"r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q",
	"R","S","T","U","V","W","X","Y","Z"
	);

	public function midToStr($int_mid) {
		settype($int_mid, 'string');
		$mid_length = strlen($int_mid);
		$url = '';
		$str = strrev($int_mid);
		$str = str_split($str, 7);
		foreach ($str as $v) {
			$char = $this->intTo62(strrev($v));
			$char = str_pad($char, 4, "0");
			$url .= $char;
		}
		$url_str = strrev($url);
		return ltrim($url_str, '0');
	}

	public function StrTomid($str_mid) {
		$surl[2] = $this->s62Toint(substr($str_mid, strlen($str_mid) - 4, 4));
		$surl[1] = $this->s62Toint(substr($str_mid, strlen($str_mid) - 8, 4));
		$surl[0] = $this->s62Toint(substr($str_mid, 0, strlen($str_mid) - 8));
		$int10 = $surl[0] . $surl[1] . $surl[2];
		return ltrim($int10, '0');
	}

	public function intTo62($int10) {
		$s62 = '';
		$r = 0;
		while($int10 != 0){
			$r = $int10 % 62;
			$s62 .= $this->item10_to_62($r);
			$int10 = floor($int10 / 62);
		}
		return $s62;
	}

	public function s62Toint($int62) {
		$strarry = str_split($int62);
		$str = 0;
		for ($i = 0; $i < strlen($int62); $i++) {
			$vi = Pow(62, (strlen($int62) - $i -1));
			$str += $vi * $this->itme62_to_10($strarry[$i]);
		}
		$str = str_pad($str, 7, "0", STR_PAD_LEFT);
		return $str;
	}

	public function item10_to_62($key){ 
		return $this->str62keys[$key];
	}

	public function itme62_to_10($v){
		return array_search($v, $this->str62keys);
	}
}