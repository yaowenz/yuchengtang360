var isIE = !!window.ActiveXObject;  
var isIE6 = isIE && !window.XMLHttpRequest;
function qqzs(e)   
{
	if(isIE)
	{
		var tch=$(window).scrollTop();
		var rng = document.body.createTextRange(); 
		rng.moveToElementText(e); 
		rng.scrollIntoView();  
		rng.select(); 
		scroll(0,tch);
		rng.execCommand("Copy");  
		rng.collapse(false);
		malert("逛宝贝QQ助手已成功复制内容<br/>现在只要在QQ对话框中 <span>Ctrl + V</span> 粘帖内容就可以分享给好友或者分享到QQ群啦！");
	}
	else
	{
		var s = window.getSelection(); 
		var r = document.createRange(); 
		r.selectNodeContents(e); 
		s.removeAllRanges(); 
		s.addRange(r); 
		malert("逛宝贝QQ助手成功启动<br/>现在按 <span>Ctrl + C</span> 就可以轻松复制此条目信息<br/>在QQ对话框中 <span>Ctrl + V</span> 粘帖内容，分享给好友或者QQ群吧");
	} 
} 