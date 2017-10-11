<?php
   session_start();
/**
 * socket class
 * @author yuegb
 */
class Socket
{
    public $host;       	//通信地址
    public $port;       	//通信端口
    public $limitTime = 0;  //超时时间
    public $backlog=3;  	//请求队列中允许的最大请求数
    public $socket = null;
    public $result = null;

    //构造函数
    public function __construct()
    {
        //设置超时时间
        set_time_limit($this->limitTime);
        if(isset($_SESSION["host"])&&isset($_SESSION["port"])){
            $this->host = $_SESSION["host"];        //行情服务器地址
            $this->port = $_SESSION["port"];        //行情服务器端口
        } else {
            //$this->host = "222.73.103.42";          //调度服务器地址
			$this->host = "222.73.34.9";
            $this->port = 12346;                    //调度服务器端口
        }
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0)
            or die("could not create socket\n");
    }
    //连接服务端
    public function connect()
    {
        $result = socket_connect($this->socket, $this->host, $this->port);
        if ($result < 0) {
            echo "连接超时，请重试, 谢谢!<br/>";
        }
    }
    //向服务端发送信息
    public function write($send_pack, $pack_len)
    {
        $size;
        $num = 0;
        socket_set_nonblock($this->socket);     // 设置非阻塞模式
        $timeout = time() + 1;                  // 监听1秒
        while(time() <= $timeout){
            if(($size = socket_send($this->socket, $send_pack, $pack_len, 0)) < 0 ){
                $num = -1;
                break;
            }
            $num += $size;
            $pack_len -= $size;
            if($pack_len <= 0){
                break;
            }
        }
        // 如果发送数据不完整
        if($num == 0){
            unset($_SESSION["host"]);
            unset($_SESSION["port"]);
        }
        return $num;
    }
    //读取服务端的信息
    public function read(&$rev_pack)
    {
        $rev_len = 0;
        socket_set_nonblock($this->socket);     // 设置非阻塞模式
        $timeout = time() + 4;                  // 监听1秒
        while(time() <= $timeout)
        {
            $size = socket_recv($this->socket, $rev_buf, 4048, 0);
            //or die("不能读取数据，请重试, 谢谢!<br/>");
            //读取包中数据长度
            if($size <= 0)
			{
				continue;
			}
            if($size > 0)
			{
			 	$rev_len += $size;
				$rev_pack .= $rev_buf;
				if ($rev_len > 5)
			 		$pack_len = unpack("s", substr($rev_pack, 5, 2));
           }
            //收取数据小于数据总长度的情况
            if($rev_len < $pack_len[1] + 8){
				usleep(10000);
                continue;
            } else {
                break;
            }
        }
        return $rev_len;
    }
    //关闭该socket
    public function close()
    {
        socket_close($this->socket);
    }
    //析构函数
    public function __destruct()
    {
        socket_close($this->socket);
    }
}
?>
