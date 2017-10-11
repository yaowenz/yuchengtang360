function  _parse_Int(x){ 
	return parseInt(x*1);
}
function  isDate(s){ 
    var   a   =   s.split(" "); 
    b   =   a[1].split( ":"); 
    a   =   a[0].split( "-"); 
    dt   =   new   Date(_parse_Int(a[0]),_parse_Int(a[1])-1,_parse_Int(a[2]),_parse_Int(b[0]),_parse_Int(b[1]),_parse_Int(b[2])); 
	if((dt.getMonth()+1)   !=   _parse_Int(a[1])   || 
            dt.getDate()   !=   _parse_Int(a[2])   || 
            dt.getHours()   !=   _parse_Int(b[0])   || 
            dt.getMinutes()   !=   _parse_Int(b[1])   || 
            dt.getSeconds()   !=   _parse_Int(b[2])   )   return   false; 
    return dt; 
} 
//if(isDate( "2009-09-15 12:59:22"))   alert(dt);else   alert( "err ");