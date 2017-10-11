<?php

	/////////////////////编码/////////////////////////////////////
    function encode($data) {
        $key = GenTeaKey('2e992010', '0acfd3da');
        $data =  mb_convert_encoding($data,"utf-8","gbk");
        $data = TeaEncrypt($data, $key);
        $data = base64_encode($data);
        $data = urlencode($data);
        return $data;
    }

    function decode($data) {
        $key = GenTeaKey('2e992010', '0acfd3da');
        $data = base64_decode($data);
        $data = TeaDecrypt($data, $key);
        //帐户中心为gb2312。编码前先进行转换
        $data = mb_convert_encoding($data, "gbk","utf-8");
        return $data;
    }

    /**
     * 远程验证数据
     */
    function remoteRequest($curlPost, $curlUrl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);
        curl_close($ch);
        $return_ary = explode("gb2312\r\n",$data);
        $curlStr = decode($return_ary[1]);
        return $curlStr;
    }

     /**
     * 远程验证数据
     */
    function remoteRequestBack($curlPost, $curlUrl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);
        curl_close($ch);
        $return_ary = explode("gb2312\r\n",$data);
        if(count($return_ary)<=0){
            return $data;
        }
        $curlStr = decode($return_ary[1]);
        return $curlStr;
    }

/////////////加密算法///////////////////
function GenTeaKey($Dmidl,$Dmidh) {
    $KeyDw1 = hexdec($Dmidl);
    $KeyDw2 = hexdec($Dmidh);
    $KeyDw3 = VerifyDword($KeyDw1+$KeyDw2);
    if($KeyDw1<$KeyDw2 && VerifyDword($KeyDw1-$KeyDw2) == 0x80000000)
        $KeyDw4 = VerifyDword((0xFFFFFFFF-($KeyDw2-$KeyDw1))+1);
    else
        $KeyDw4 = VerifyDword($KeyDw1-$KeyDw2);
        
    $TeaKey = sprintf("%08X%08X%08X%08X", $KeyDw1, $KeyDw2, $KeyDw3, $KeyDw4);

    return $TeaKey;
}

function TeaEncrypt($Text, $Key, $dwlen=4) {
    $DwArr = Text2DwArr($Text, true);
    $Len = count($DwArr);
    $KeyArr = DwText2Arr($Key);

    $n = $Len;
    if(strlen($Text)%$dwlen != 0)
        $n = $n - 1;

    $n = $n - 1;
    if($n >= 1) {
        $z = $DwArr[$n] ; $y = $DwArr[0] ; $delta = 0x9E3779B9 ; $sum = 0;
        $q = intval(6 + 52 / ( $n + 1 ));
        while( $q-- > 0 ) {
            $sum = VerifyDword( $sum + $delta ) ;
            $e = VerifyDword($sum >> 2 & 3) ;
            for($i = 0 ; $i < $n ; $i++ ) {
                $y = $DwArr[$i+1];

                $clause1 = VerifyDword(_BF_SHR32($z,4) ^ _BF_SHL32($y,2));
                $clause2 = VerifyDword(_BF_SHR32($y,3) ^ _BF_SHL32($z,4));
                $clause3 = VerifyDword($clause1 + $clause2);
                $clause4 = VerifyDword($sum ^ $y);
                $clause5 = VerifyDword(hexdec($KeyArr[$i & 3 ^ $e]) ^ $z);
                $clause6 = VerifyDword($clause4 + $clause5);
                $clause7 = VerifyDword($clause3 ^ $clause6);
                $DwArr[$i] = VerifyDword($DwArr[$i] + $clause7);

                $z = $DwArr[$i];
            }

            $y = $DwArr[0];

            $clause1 = VerifyDword(_BF_SHR32($z,4) ^ _BF_SHL32($y,2));
            $clause2 = VerifyDword(_BF_SHR32($y,3) ^ _BF_SHL32($z,4));
            $clause3 = VerifyDword($clause1 + $clause2);
            $clause4 = VerifyDword($sum ^ $y);
            $clause5 = VerifyDword(hexdec($KeyArr[$i & 3 ^ $e]) ^ $z);
            $clause6 = VerifyDword($clause4 + $clause5);
            $clause7 = VerifyDword($clause3 ^ $clause6);
            $DwArr[$n] = VerifyDword($DwArr[$n] + $clause7);

            $z = $DwArr[$n];
        }
    }

    $EncryptText = "";
    for($i=0; $i<$Len; $i++) {
    //printf("%s <br>", strtoupper(dechex($DwArr[$i])));

        $EncryptText .= hex2asc(dechex($DwArr[$i]), true);
    }

    $nLength = intval(strlen($Text)/4);
    if($nLength<=1 || strlen($Text)%4!=0) {
        $tailpos = $nLength>1 ? $nLength*4 : 0;

        for($i=$tailpos; $i<strlen($Text); $i++) {
            $EncryptText[$i] = chr(ord($EncryptText[$i]) ^ hexdec(substr($KeyArr[$i%4],-2)));
        }
    }

    $EncryptText = substr($EncryptText,0,strlen($Text));

    //printf("EncryptText=%s <br>", asc2hex($EncryptText));

    return $EncryptText;
}

function TeaDecrypt($Text, $Key, $dwlen=4) {
    $DwArr = Text2DwArr($Text,true);
    $Len = count($DwArr);
    $KeyArr = DwText2Arr($Key);

    $n = $Len;
    if(strlen($Text)%$dwlen != 0)
        $n = $n - 1;

    $n = $n - 1;
    if($n >= 1) {
        $z = $DwArr[$n] ; $y = $DwArr[0] ; $delta = 0x9E3779B9 ; $sum = 0;
        $q = intval(6 + 52 / ( $n + 1 ));
        $sum = $q * $delta ;
        while( $sum != 0 ) {
            $e = VerifyDword($sum >> 2 & 3) ;
            for ( $i = $n ; $i > 0 ; $i-- ) {
                $z = $DwArr[$i - 1];

                $clause1 = VerifyDword(_BF_SHR32($z,4) ^ _BF_SHL32($y,2));
                $clause2 = VerifyDword(_BF_SHR32($y,3) ^ _BF_SHL32($z,4));
                $clause3 = VerifyDword($clause1 + $clause2);
                $clause4 = VerifyDword($sum ^ $y);
                $clause5 = VerifyDword(hexdec($KeyArr[$i & 3 ^ $e]) ^ $z);
                $clause6 = VerifyDword($clause4 + $clause5);
                $clause7 = VerifyDword($clause3 ^ $clause6);
                if($DwArr[$i]<$clause7 && VerifyDword($DwArr[$i]-$clause7) == 0x80000000)
                    $DwArr[$i] = VerifyDword((0xFFFFFFFF-($clause7-$DwArr[$i]))+1);
                else
                    $DwArr[$i] = VerifyDword($DwArr[$i]-$clause7);

                $y = $DwArr[$i];
            }

            $z = $DwArr[$n];

            $clause1 = VerifyDword(_BF_SHR32($z,4) ^ _BF_SHL32($y,2));
            $clause2 = VerifyDword(_BF_SHR32($y,3) ^ _BF_SHL32($z,4));
            $clause3 = VerifyDword($clause1 + $clause2);
            $clause4 = VerifyDword($sum ^ $y);
            $clause5 = VerifyDword(hexdec($KeyArr[$i & 3 ^ $e]) ^ $z);
            $clause6 = VerifyDword($clause4 + $clause5);
            $clause7 = VerifyDword($clause3 ^ $clause6);
            if($DwArr[0]<$clause7 && VerifyDword($DwArr[0]-$clause7) == 0x80000000)
                $DwArr[0] = VerifyDword((0xFFFFFFFF-($clause7-$DwArr[0]))+1);
            else
                $DwArr[0] = VerifyDword($DwArr[0]-$clause7);

            $y = $DwArr[0];
            $sum = $sum - $delta ;
        }
    }

    $DecryptText = "";
    for($i=0; $i<$Len; $i++) {
    //printf("%s <br>", strtoupper(dechex($DwArr[$i])));

        $DecryptText .= hex2asc(dechex($DwArr[$i]),true);
    }

    $nLength = intval(strlen($Text)/4);
    if($nLength<=1 || strlen($Text)%4!=0) {
        $tailpos = $nLength>1 ? $nLength*4 : 0;

        for($i=$tailpos; $i<strlen($Text); $i++) {
            $DecryptText[$i] = chr(ord($DecryptText[$i]) ^ hexdec(substr($KeyArr[$i%4],-2)));
        }
    }

    $DecryptText = trim($DecryptText, "\0");

    //printf("DecryptText=%s <br>", asc2hex($DecryptText));

    return $DecryptText;
}

function VerifyDword($dword, $dwlen=8) {
    $hexval = dechex($dword);

    if(strlen($hexval)>$dwlen)
        return hexdec(substr($hexval,-$dwlen));
    else
        return hexdec($hexval);
}

function Text2DwArr($text, $Reverse=false, $dwlen=4) {
    $textlen = strlen($text);
    if($textlen%$dwlen != 0)
        $dwarrlen = intval($textlen/$dwlen)+1;
    else
        $dwarrlen = intval($textlen/$dwlen);

    $dwarr = array();
    for($dwnum=0; $dwnum<$dwarrlen; $dwnum++) {
        if($dwnum != $dwarrlen) {
            if($Reverse)
                $dwarr[] = hexdec(asc2hex(strrev(substr($text, $dwnum*$dwlen, $dwlen))));
            else
                $dwarr[] = hexdec(asc2hex(substr($text, $dwnum*$dwlen, $dwlen)));
        }
        else {
            if($Reverse)
                $dwarr[] = hexdec(asc2hex(strrev(substr($text, $dwnum*$dwlen))));
            else
                $dwarr[] = hexdec(asc2hex(substr($text, $dwnum*$dwlen)));
        }
    }

    return $dwarr;
}

function DwText2Arr($dwtext, $dwlen=8) {
    $dwtextlen = strlen($dwtext);
    if($dwtextlen%$dwlen != 0)
        $dwarrlen = intval($dwtextlen/$dwlen)+1;
    else
        $dwarrlen = intval($dwtextlen/$dwlen);

    $dwarr = array();
    for($dwnum=0; $dwnum<$dwarrlen; $dwnum++) {
        if($dwnum != $dwarrlen)
            $dwarr[] = substr($dwtext, $dwnum*$dwlen, $dwlen);
        else
            $dwarr[] = substr($dwtext, $dwnum*$dwlen);
    }

    return $dwarr;
}

function asc2hex($text, $fmtzero=false) {
    $hexstr = "";

    $len = strlen($text);
    for($i=0; $i<$len; $i++) {
        $hexstr .= sprintf("%02X", ord($text[$i]));
    }

    return $hexstr;
}

function hex2asc($hexstr,$Reverse=false,$hexLen=8,$fillzeronum=0) {
    $dwarr = DwText2Arr(sprintf("%0".$hexLen."s", $hexstr),2);

    $text = "";
    $len = count($dwarr);

    if(!$Reverse) {
        for($i=0; $i<$len; $i++) {
            $text .= chr(hexdec($dwarr[$i]));
        }
    }
    else {
        for($i=$len-1; $i>=0; $i--) {
            $text .= chr(hexdec($dwarr[$i]));
        }
    }

    for($i=0; $i<$fillzeronum; $i++) {
        $text .= chr(0);
    }

    return $text;
}

function hex2bin($data) {
    $len = strlen($data);
    for($i=0;$i<$len;$i+=2) {
        $newdata .= pack("C",hexdec(substr($string,$i,2)));
    }
    return $newdata;
}

function _BF_SHR32 ($x, $bits) {
    if ($bits==0) return $x;
    if ($bits==32) return 0;
    $y = ($x & 0x7FFFFFFF) >> $bits;
    if (0x80000000 & $x) {
        $y |= (1<<(31-$bits));
    }
    return $y;
}

function _BF_SHL32 ($x, $bits) {
    if ($bits==0) return $x;
    if ($bits==32) return 0;
    $mask = (1<<(32-$bits)) - 1;
    return (($x & $mask) << $bits) & 0xFFFFFFFF;
}

function _BF_GETBYTE ($x, $y) {
    return _BF_SHR32 ($x, 8 * $y) & 0xFF;
}

function _BF_OR32 ($x, $y) {
    return ($x | $y) & 0xFFFFFFFF;
}

function _BF_ADD32 ($x, $y) {

    $x = $x & 0xFFFFFFFF;
    $y = $y & 0xFFFFFFFF;

    $total = 0;
    $carry = 0;
    for ($i=0; $i<4; $i++) {
        $byte_x = _BF_GETBYTE($x, $i);
        $byte_y = _BF_GETBYTE($y, $i);
        $sum = $byte_x + $byte_y;

        $result = $sum & 0xFF;
        $carryforward = _BF_SHR32($sum, 8);

        $sum = $result + $carry;
        $result = $sum & 0xFF;
        $carry = $carryforward + _BF_SHR32($sum, 8);

        $total = _BF_OR32(_BF_SHL32($result, $i*8), $total);
    }

    return $total;
}
?>