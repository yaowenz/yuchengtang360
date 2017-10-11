<?php
/**
 * 用于发送邮件
 * by zx 2010-7-5
 */
require_once dirname(__FILE__).'/../container.class.php';
require_once dirname(__FILE__).'/../db/db.class.php'; 
require_once(realpath(dirname(__FILE__)) . "/PHPMailer/class.phpmailer.php");
class smtp_mail extends PHPMailer
{ 
	public function __construct($exceptions = false){
		parent::__construct($exceptions);
	
		//$body             = file_get_contents('contents.html');
		//$body             = eregi_replace("[\]",'',$body);

		$this->IsSMTP(); // telling the class to use SMTP
		$this->CharSet ='utf-8';
		$this->Host       = "mail.gw.com.cn"; // SMTP server
		//$this->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
												   // 1 = errors and messages
												   // 2 = messages only
		$this->SMTPAuth   = true;                  // enable SMTP authentication
		$this->Port       = 25;                    // set the SMTP port for the GMAIL server
		$this->Username   = "emagazine"; // SMTP account username
		$this->Password   = "123456";        // SMTP account password 

		//$this->AddReplyTo('microblog@gw.com.cn', 'AddReplyTo');

		$this->AltBody    = "大智慧团队"; // optional, comment out and test
		
		//$this->MsgHTML($body);
		//$this->AddAttachment("images/phpmailer.gif");      // attachment
		//$this->AddAttachment("images/phpmailer_mini.gif"); // attachment

		$this->SetFrom($this->Username . '@gw.com.cn', '大智慧百科团队');
	}
	
	//send_hook
	public function doCallback($isSent,$to,$cc,$bcc,$subject,$body) {
		$db = cm_db::get_db(cm_db_config::get_ini('logs','master'));  
		
		$a['subject'] = $subject;
		$a['email_to'] = $to;
		$a['email_from'] = $this->From; 
		$a['email_from_name'] = $this->FromName; 
		$a['content'] = $body; 
		$a['createtime'] = time(); 
		$a['error_info'] = $this->ErrorInfo; 

		// 插入数据的数据表
		$table = 'cm_email_log';

		// i插入数据行并返回行数
		$rows_affected = $db->insert($table, $a);
	}

}

/* 用法案例
define('cm_COMM_PATH', '/opt/websites/cm_comm');//共用库目录

require_once(cm_COMM_PATH . "/lib/email/email.smtp.class.php");

$mail = new smtp_mail; 

$mail->Subject    = "您的词条已被采纳!";
$mail->AddAddress($w_info['email'], "zx");
$mail->MsgHTML('内容');
$mail->Send();
//if(!$mail->Send()) { echo "Mailer Error: " . $mail->ErrorInfo;} else { echo "Message sent!";} 
$mail=null;
*/
?>


