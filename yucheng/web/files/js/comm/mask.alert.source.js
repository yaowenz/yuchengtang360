function malert(s)
{
	var tch=$(window).scrollTop();
	var b=$("#maskb");
	var a=$("#maska");
	a.html(s); 
	b.fadeIn("fast").height($(document).height()); 
	var t1=tch+200;
	a.css("top",t1+"px"); 
	a.fadeIn("fast"); 
	b.unbind("click").click(function(){$("embed").css("visibility","visible");$("#dm #c div").show();$(this).fadeOut("fast");a.fadeOut("fast");scroll(0,tch);}); 
}