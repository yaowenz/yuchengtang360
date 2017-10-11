<?php
/*
繁体 简体 互转
2010-8-3
*/

big5_base::$root=dirname(__FILE__) . '/big5';

class big5_base { 

	static $root;
	static function encode($str,$charset='hant2cn',$code_to = 'utf-8')
    {
            $codeing = mb_detect_encoding($str, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
			$str = mb_convert_encoding($str, "utf-8", $codeing);

			if ($charset=='cn2hant') 
            {
                    $fd = fopen(self::$root."/gb2big.map",'r'); 
                    $str1 = fread($fd,filesize(self::$root."/gb2big.map"));
            }
            else
            {
                    $fd = fopen(self::$root."/big2gb.map",'r');
                    $str1 = fread($fd,filesize(self::$root."/big2gb.map"));
            }
            fclose($fd);

            // convert to unicode and map code
            $chg_utf = array();
            for ($i=0;$i<strlen($str1);$i=$i+4)
            {
                    $ch1=ord(substr($str1,$i,1))*256;
                    $ch2=ord(substr($str1,$i+1,1));
                    $ch1=$ch1+$ch2;
                    $ch3=ord(substr($str1,$i+2,1))*256;
                    $ch4=ord(substr($str1,$i+3,1));
                    $ch3=$ch3+$ch4;
                    $chg_utf[$ch1]=$ch3;
            }

            $outstr='';
            for ($k=0;$k<strlen($str);$k++)
            {
                    $ch=ord(substr($str,$k,1));
                    if ($ch<0x80)
                    {
                            $outstr.=substr($str,$k,1);
                    }
                    else
                    {
                            if ($ch>0xBF && $ch<0xFE)
                            {
                                    if ($ch<0xE0) {
                                            $i=1;
                                            $uni_code=$ch-0xC0;
                                    } elseif ($ch<0xF0)	{
                                            $i=2;
                                            $uni_code=$ch-0xE0;
                                    } elseif ($ch<0xF8)	{
                                            $i=3;
                                            $uni_code=$ch-0xF0;
                                    } elseif ($ch<0xFC)	{
                                            $i=4;
                                            $uni_code=$ch-0xF8;
                                    } else {
                                            $i=5;
                                            $uni_code=$ch-0xFC;
                                    }
                            }

							$ch1=substr($str,$k,1);
                            for ($j=0;$j<$i;$j++)
                            {
                                    $ch1 .= substr($str,$k+$j+1,1);
                                    $ch=ord(substr($str,$k+$j+1,1))-0x80;
                                    $uni_code=$uni_code*64+$ch;
                            }
                            if (!$chg_utf[$uni_code])
                            {
                                    $outstr.=self::_U2_Utf8_Gb($ch1);
                            }
                            else
                            {
                                    $outstr.=self::_U2_Utf8_Gb($chg_utf[$uni_code]);
                            }
                            $k += $i;
                    } 
            }
			$outstr = mb_convert_encoding($outstr, $code_to, 'GBK');
            return $outstr;
    }

    // Return utf-8 character
    static function _U2_Utf8_Gb($_C)
    {
        $_String = '';
        if($_C < 0x80) $_String .= $_C;
        elseif($_C < 0x800)
        {
        $_String .= chr(0xC0 | $_C>>6);
        $_String .= chr(0x80 | $_C & 0x3F);
        }elseif($_C < 0x10000){
        $_String .= chr(0xE0 | $_C>>12);
        $_String .= chr(0x80 | $_C>>6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
        } elseif($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C>>18);
        $_String .= chr(0x80 | $_C>>12 & 0x3F);
        $_String .= chr(0x80 | $_C>>6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
        }
		return mb_convert_encoding($_String, 'GBK', 'UTF-8');
    }
  
}
?> 