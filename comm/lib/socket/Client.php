<?php
include_once 'socket.php';
include_once 'CommFunction.php';
include_once 'ConstantComCode.php';

class Client
{
    private $socket;
    public function __construct()
    {
		require_once 'AskSerAdd.php';
        $this->socket = new Socket();
    }
        
    /**
     * 共通函数,发送包,并解析接收的数据包
     * @param <type> $send_pack     发送数据包
     * @param <type> $unpack_str    解析包格式字符串
     * @param <type> $isRecord      服务端返回包中是否含有记录条数
     * @return <type> 数据包解析后的数组 
     */
    function CommSendRecvPack($send_pack, $unpack_str, $isRecord, $isSelfStk=false, $isFS=false)
    {
        $arry_pack;     // 返回数组
        if(!$this->socket instanceof Socket){
            $this->socket = new Socket();
        }
        $this->socket->connect();
        $len = strlen($send_pack);
        //发送请求信息
        $send_size = $this->socket->write($send_pack, $len);

		if($send_size != $len){
            $arry_pack = Array("nodata");
            return $arry_pack;
        }
        //获取服务端返回数据
        $rev_size = $this->socket->read($rev_pack);

        // 自选股时需要特殊处理
        if($isSelfStk){
            $code = unpack("Cv1", substr($rev_pack, 7, 1));
            if($code[v1] == 0 && $rev_size > 9){
                $len = unpack("sv1", substr($rev_pack, 8, 2));
                $unpack_str .= "/sv5/a".$len[v1]."v6";
            }
        }
        //调用函数重新生成unpack的字符串,详见函数说明
        if($isRecord)
        {
            $unpack_str_new = unpack_rc_str($unpack_str, $rev_pack);
        }
        else
        {
            $unpack_str_new = unpack_str($unpack_str, $rev_pack);
        }

		// 由于分时数据量大,unpack函数不能一次解析,需要特殊处理
		if($isFS)
		{
			// 数据量大分为三段分别进行解析
			// array1包括信息头部数据
			$array1 = unpack("av0/sv1/C2v2/sv3/iv4/iv5/iv6/Cv7/Cv8/iv9/srec", substr($rev_pack,0,27));
			if($array1[rec]>120){
				$tmp_str = "";
				for($i = 0; $i < 480; $i++){
					$tmp_str .= "im".$i."/";
				}
				$tmp_str .= "im480";
				// array2包括上午的分时数据
				$array2 = unpack($tmp_str, substr($rev_pack,27,1947));
				$count = $array1[rec]*4-1;
				$tmp_str = "";
				for($k = 481; $k < $count; $k++){
					$tmp_str .= "im".$k."/";
				}
				$tmp_str .= "im".$count;
				$size = 1947+($count-480)*4;

				// array3包括下午的分时数据
				$array3 = unpack($tmp_str, substr($rev_pack,1951,$size));
				// 将三部分数据合并
				$arry_pack = $array1 + $array2 + $array3;
			}else{
				$arry_pack = unpack($unpack_str_new, $rev_pack);
			}
		}
		else
		{
			$arry_pack = unpack($unpack_str_new, $rev_pack);
		}
        return $arry_pack;
    }
    
    /**
     * 请求股票基本信息
     * @param <type> $StkCode   股票代码
     * @return <type> 数据包解析后的数组
     */
    public function AskStkInfo($StkCode)
    {
        $pack_type = constant("STOCK_INFO");
        $head_flag = constant("HEAD_FLAG");
        $foot_flag = constant("FOOT_FLAG");
        $pack_str = "asC2ssa8a";    // pack格式字符串
        $send_pack = pack($pack_str,
                           $head_flag,
                           $pack_type,    // 包类型
                           0,             // 属性
                           0,             // 属性
                           10,            // 正文长度
                           8,             // 正文数据(short)
                           $StkCode,      // 正文数据(data)
                           $foot_flag);
        $unpack_str = "av0/sv1/C2v2/sv3/Z/Z/Cv6/Cv7/iv9/iv10/iv11";
        //调用共通函数处理发包和收包信息
        $result_array = $this->CommSendRecvPack($send_pack, $unpack_str, false);
        return $result_array;
    }

	/**
     * 
     * @param 
     * @return 
     */
    public function AskRequestGuZhuDu($reqcode,$page,$num)
    {
        $pack_type = constant("REQUEST_GUZHUDU");
        $head_flag = constant("HEAD_FLAG");
        $foot_flag = constant("FOOT_FLAG");
		$offset = ($page-1)*$num;
        $pack_str = "asC2sssCa";    // pack格式字符串
        $send_pack = pack($pack_str,
                           $head_flag,
                           $pack_type,    // 包类型
                           0,             // 属性
                           0,             // 属性
                           5,			  // 正文长度
                           $offset,       // 正文数据(short)
						   $num,          // 正文数据(short)
                           $reqcode,	  // 正文数据(data)
                           $foot_flag);
        $unpack_str = "av0/sv1/C2v2/sv3RZ/Z/i/i";
        //调用共通函数处理发包和收包信息
        $result_array = $this->CommSendRecvPack($send_pack, $unpack_str, true);
        return $result_array;
    }

	/**
     * 请求股票K线数据
     * @param <type> $StkCode   股票代码
     * @return <type> 
     */
	public function AskStockKLineData($StkCode,$type,$date,$record)
	{
		$pack_type = constant("KLine_DATA");
        $head_flag = constant("HEAD_FLAG");
        $foot_flag = constant("FOOT_FLAG");
        $pack_str = "asC2ssa8Cisa";    	  // pack格式字符串
        $send_pack = pack($pack_str,
                           $head_flag,
                           $pack_type,    // 包类型
                           0,             // 属性
                           0,             // 属性
                           17,            // 正文长度
                           8,             // 正文数据(short)
                           $StkCode,      // 正文数据(data)
						   $type,		  // K线类型
						   $date,		  // 日期
						   $record,		  // 请求条数
                           $foot_flag);
        $unpack_str = "av0/sv1/C2v2/sv3Ri/i/i/i/i/i/i";
        //调用共通函数处理发包和收包信息
        $result_array = $this->CommSendRecvPack($send_pack,$unpack_str,true,false,false);
		return $result_array;
	}
}
?>
