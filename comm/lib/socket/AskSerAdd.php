<?php session_start();
    include_once 'socket.php';
    if(!isset($_SESSION["host"])&&!isset($_SESSION["port"])){
        $pack_str = "asC2ssa3sa3sa3a";          // pack格式字符串
        $send_pack = pack($pack_str,
                           "{",
                           1000,                // 包类型
                           0,                   // 属性
                           0,                   // 属性
                           15,                  // 正文长度
                           3,                   // 字符长度
                           "wap",               // 内容
                           3,                   // 字符长度
                           "wap",               // 内容
                           3,                   // 字符长度
                           "wap",               // 内容
                           "}");
        $socket = new Socket();       
        $socket->connect();
        $len = strlen($send_pack);
        //发送请求信息
        $send_size = $socket->write($send_pack, $len);
        if($send_size < $len){
            return;
        }
        //获取服务端返回数据
        $rev_size = $socket->read($rev_pack);
        $ser_len = unpack("s", substr($rev_pack, 9, 11));
        $server_add = unpack("a".$ser_len[1], substr($rev_pack, 11, 33));
        $ser_ary = explode(":", $server_add[1]);
        $_SESSION["host"] = $ser_ary[0];        //行情服务器地址
        $_SESSION["port"] = $ser_ary[1];        //行情服务器端口
    }
?>
