function copyToClipBoard(txt)
{
 if(window.clipboardData)
 {
  // the IE-manier
  window.clipboardData.clearData();
  window.clipboardData.setData("Text", txt);
  alert("���Ѹ��ƣ�����ճ���� EXCEL ��");
 }
 else if(navigator.userAgent.indexOf("Opera") != -1)
 {
  window.location = txt;
  alert("����ʹ��IE�������¼���ǵ���վ����");
 }
 else if (window.netscape)
 {
  // dit is belangrijk maar staat nergens duidelijk vermeld:
  // you have to sign the code to enable this, or see notes below
  try {
  netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
  } catch (e) {
  alert("��������ܾ���\n���������������ַ������'about:config'���س�\nȻ��'signed.applets.codebase_principal_support'����Ϊ'true'");
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
  alert("���Ѹ��ƣ�����ճ���� EXCEL ��");
 }
}