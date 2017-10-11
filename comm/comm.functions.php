<?php
//常用，通用 函数库

if (!function_exists('gzdecode')) { 
function gzdecode ($data) { 
$flags = ord(substr($data, 3, 1)); 
$headerlen = 10; 
$extralen = 0; 
$filenamelen = 0; 
if ($flags & 4) { 
$extralen = unpack('v' ,substr($data, 10, 2)); 
$extralen = $extralen[1]; 
$headerlen += 2 + $extralen; 
} 
if ($flags & 8) // Filename 
$headerlen = @strpos($data, chr(0), $headerlen) + 1; 
if ($flags & 16) // Comment 
$headerlen = @strpos($data, chr(0), $headerlen) + 1; 
if ($flags & 2) // CRC at end of file 
$headerlen += 2; 
$unpacked = @gzinflate(substr($data, $headerlen)); 
if ($unpacked === FALSE) 
$unpacked = $data; 
return $unpacked; 
} 
} 

if (!function_exists('escape')) { 
function escape($str){  
    $sublen=strlen($str);  
    $reString="";  
    for ($i=0;$i<$sublen;$i++){  
        if(ord($str[$i])>=127){  
            $tmpString=bin2hex(iconv("GBK","ucs-2",substr($str,$i,2)));    //此处GBK为目标代码的编码格式，请实际情况修改  
            if (!eregi("WIN",PHP_OS)){  
                $tmpString=substr($tmpString,2,2).substr($tmpString,0,2);  
            }  
            $reString.="%u".strtoupper($tmpString);  
            $i++;  
        } else {  
            $reString.="%".strtoupper(dechex(ord($str[$i])));  
        }  
    }  
    return $reString;  
}
}

//把 数组 递归，所有值格式转成 char 类型
if (!function_exists('array_deep_to_char')){
	function array_deep_to_char($value){
		$value = is_array($value)?array_map('array_deep_to_char',$value):strval($value);
		return $value; 
	}
}