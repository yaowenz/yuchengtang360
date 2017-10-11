function copyToClipBoard(txt)
{
 if(window.clipboardData)
 {
  // the IE-manier
  window.clipboardData.clearData();
  window.clipboardData.setData("Text", txt);
  alert("你已复制，可以粘贴到 EXCEL 。");
 }
 else if(navigator.userAgent.indexOf("Opera") != -1)
 {
  window.location = txt;
  alert("建议使用IE浏览器登录我们的网站：）");
 }
 else if (window.netscape)
 {
  // dit is belangrijk maar staat nergens duidelijk vermeld:
  // you have to sign the code to enable this, or see notes below
  try {
  netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
  } catch (e) {
  alert("被浏览器拒绝！\n您可以在浏览器地址栏输入'about:config'并回车\n然后将'signed.applets.codebase_principal_support'设置为'true'");
  }
  // maak een interface naar het clipboard
  var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
  if (!clip){return;}
  // alert(clip);
  // maak een transferable
  var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
  if (!trans){return;}
  // specificeer wat voor soort data we op willen halen; text in dit geval
  trans.addDataFlavor('text/unicode');
  // om de data uit de transferable te halen hebben we 2 nieuwe objecten 
  // nodig om het in op te slaan
  var str = new Object();
  var len = new Object();
  str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
  var copytext = txt;
  str.data = copytext;
  trans.setTransferData("text/unicode",str,copytext.length*2);
  var clipid = Components.interfaces.nsIClipboard;
  if (!clip){return false;}
  clip.setData(trans,null,clipid.kGlobalClipboard);
  alert("你已复制，可以粘贴到 EXCEL 。");
 }
}