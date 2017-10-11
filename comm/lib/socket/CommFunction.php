<?php
/**
* 用于一般性处理解析包结构
 * @param <type> $f_parse 参数格式
 * @param <type> $d_pack  二进制包数据
**/
function unpack_str($f_parse, $d_pack)
{
    $argc       = 1;		//定义数据长度
    $size		= 0;		//每个变量所指的长度
    $argvlen	= 0;		//指针偏移量
    $unpack_str;            //重新解析生成的字符串
    $ary = explode("/", $f_parse);
    $arylen = count($ary);
    for($i = 0; $i < $arylen; $i++)
    {
    	$dostr = false;		//用于判断是否为字符串
    	$parse = $ary[$i];
    	$type = substr($parse, 0, 1);
    	switch($type){
    			case 'a':
    					$size  = 1;
    					$argv .= 'a';
    					break;
    			case 's':
    					$size  = 2;
    					$argv .= 's';
    					break;
                case 'i':
                        $size = 4;
                        $argv .= 'i';
                        break;
    			case 'C':
    					$size  = 1;
    					$argv .= 'C';
    					break;
    			case 'c':
    					$size  = 1;
    					$argv .= 'c';
    					break;
    			case 'Z':
    					$dostr = true;
    					$aryv = unpack("s", substr($d_pack, $argvlen, 2));
    					$argvlen += 2;
    					$argvlen += $aryv[1];
    					$ary[$i] = "slen".$i."/a".$aryv[1]."con".$i;
    					break;
	        default:
	            die('parse error');
    	}
    	if($dostr)
          continue;
    	$argc = intval(substr($parse, 1));
    	if($argc==0){
          $argc = 1;
        }else{
             /*unpack代码限制了只能200*/
             if($argc>2000)
              $argc = 2000;
        }
      $argvlen += $argc*$size;
    }
    for($j = 0; $j < $arylen-1; $j++)
    {
    	$unpack_str .= $ary[$j]."/";
    }
    $unpack_str .= $ary[$arylen-1];
    return $unpack_str;
}

/**
 * 用于解析带有记录数的特殊包结构
 * @param <type> $f_parse 参数格式
 * @param <type> $d_pack  二进制包数据
 */
function unpack_rc_str($f_parse, $d_pack)
{
    $unpack_rc_str; // 返回解析带记录的格式字符串
    $unpack_str1;   // 用于存储前半部分字符串返回值
    $unpack_str2;   // 用于存储后半部分字符串返回值
    $argvlen = 0;
    //R为一个约定的值,指这处为记录条数
    $rc_ary = explode("R", $f_parse);
    if($rc_ary[0] != ""){
        $argvlen = unpack_str_rc($rc_ary[0], $d_pack, $unpack_str1, 1);
    }
    $tmp_str;
    $recount = unpack("s", substr($d_pack, $argvlen, 2));
    for($i = 0; $i < $recount[1] - 1; $i++)
    {
        $tmp_str .= $rc_ary[1]."/";
    }

    $tmp_str .= $rc_ary[1];

    $tmp_ary = explode("/", $tmp_str);
    $tmp_count = count($tmp_ary);
    $tmp_str = "";
	
    for($j = 0; $j < $tmp_count - 1; $j++)
    {
        $tmp_str .= $tmp_ary[$j]."m".$j."/";
    }
    $tmp_str .= $tmp_ary[$tmp_count-1]."m".($tmp_count-1);
	//echo $tmp_str;
    unpack_str_rc($tmp_str, substr($d_pack, $argvlen+2), $unpack_str2, 2);
    $unpack_rc_str = $unpack_str1."/srec/".$unpack_str2;
    return $unpack_rc_str;
}

/**
 * 用于处理带有记录条数的包结构
 * @param <type> $f_parse
 * @param <type> $d_pack
 * @return <type>
 */
function unpack_str_rc($f_parse, $d_pack, &$unpack_str, $s)
{
    $argc       = 1;		//定义数据长度
    $size		= 0;		//每个变量所指的长度
    $argvlen	= 0;		//指针偏移量
    $unpack_str;            //重新解析生成的字符串
    $ary = explode("/", $f_parse);
    $arylen = count($ary);
    for($i = 0; $i < $arylen; $i++)
    {
    	$dostr = false;		//用于判断是否为字符串
    	$parse = $ary[$i];
    	$type = substr($parse, 0, 1);

    	switch($type){
    			case 'a':
    					$size  = 1;
    					$argv .= 'a';
    					break;
    			case 's':
    					$size  = 2;
    					$argv .= 's';
    					break;
                case 'i':
                        $size = 4;
                        $argv .= 'i';
                        break;
    			case 'C':
    					$size  = 1;
    					$argv .= 'C';
    					break;
    			case 'c':
    					$size  = 1;
    					$argv .= 'c';
    					break;
    			case 'Z':
    					$dostr = true;
    					$aryv = unpack("s", substr($d_pack, $argvlen, 2));
    					$argvlen += 2;
    					$argvlen += $aryv[1];
    					$ary[$i] = "slen".$i.$s."/a".$aryv[1]."con".$i.$s;
    					break;
	        default:
	            die('parse error');
    	}
    	if($dostr)
          continue;
    	$argc = intval(substr($parse, 1));
    	if($argc==0){
          $argc = 1;
        }else{
             /*unpack代码限制了只能200*/
             if($argc>5000)
              $argc = 5000;
        }
      $argvlen += $argc*$size;
    }
    for($j = 0; $j < $arylen-1; $j++)
    {
    	$unpack_str .= $ary[$j]."/";
    }
    $unpack_str .= $ary[$arylen-1];
    return $argvlen;
}
?>
