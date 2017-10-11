<?php
/**
 * 定义常量文件
 */

define("HEAD_FLAG", "{");               // 包头
define("FOOT_FLAG", "}");               // 包尾

//请求包类型常量
define("ASK_SERVER", 1000);             // 请求服务器地址
define("STOCK_LIST", 2000);             // 股票列表
define("MARKET_LIST", 2100);            // 股票行情列表
define("EX_MARKET_LIST", 2103);
define("EX_L2_MARKET_LIST", 2105);      // 扩展行情列表
define("BK_STOCK", 2101);               // 板块成分股列表
define("FS_DATA",2201);					// 请求分时数据
define("FB_DATA",2205);					// 请求分笔数据
define("ZD_DATA",2206);					// 涨跌家数数据
define("KLine_DATA",2202);				// 请求K线数据
define("MM_DATA", 2204);                // 买卖盘数据
define("L2MM_DATA", 2915);              // level2扩展买卖盘数据
define("PHONE_STOCK", 2911);            // 手机自选股
define("STOCK_INFO", 2200);             // 股票基本信息
define("PRICE_DATA", 2203);             // 价量数据
define("RMB_DATA", 2500);             	// 人民币汇率
define("INDEX_CONTRI", 2102);           // 指数贡献
define("USER_REIGSTER", 2903);          // 用户注册
define("VALI_REGISTER", 2904);          // 验证手机注册
define("MOBLIE_LOGIN", 2905);           // 手机登录
define("USER_LOGIN", 2906);             // 用户登录
define("COMM_FRIEND", 2908);            // 推荐好友
define("UPDATE_MOBLIE", 2909);          // 更新手机号
define("REQUEST_GUZHUDU", 2959);         // 请求关注度
?>
