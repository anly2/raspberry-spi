   xmlhttp = new Array();
function call(url, callback, sync){
   var async = (typeof callback == "boolean")? !callback : (sync!=null)? !sync : true;

   if(window.XMLHttpRequest)
      var xh = new XMLHttpRequest();
   else
   if(window.ActiveXObject)
      var xh = new ActiveXObject("Microsoft.XMLHTTP");
   else
      return false;


   if(typeof callback != "boolean" && callback!=null)
      xh.onreadystatechange = function(){
         if (this.readyState==4 && this.status==200)
            callback(this.responseText);
         xmlhttp.splice(xmlhttp.indexOf(this), 1);
      }

   xh.open("GET", url, async);
   xh.send(null);

   xmlhttp.push(xh);
   return async? xh : xh.responseText;
}