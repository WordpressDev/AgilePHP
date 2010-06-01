
var AgilePHP={author:'Jeremy Hahn',copyright:'Make A Byte, inc.',version:'0.1a',licence:'GNU General Public License v3',package:'com.makeabyte.agilephp',requestBase:null,documentRoot:null,debugMode:false,setRequestBase:function(path){this.requestBase=path;},getRequestBase:function(){if(!AgilePHP.requestBase){var pos=location.pathname.indexOf('.php')+4;AgilePHP.requestBase=location.pathname.substring(0,pos);}
return AgilePHP.requestBase;},getDocumentRoot:function(){if(!AgilePHP.documentRoot){var pieces=AgilePHP.getRequestBase().split('/');AgilePHP.documentRoot=pieces.slice(0,(pieces.length-1)).join('/')+'/';}
return AgilePHP.documentRoot;},setDebug:function(boolean){this.debugMode=boolean;},isInDebugMode:function(){return this.debugMode==true;},go:function(url){location.href=url;},loadScript:function(file){var head=document.getElementsByTagName('head')[0];var script=document.createElement('script');script.type='text/javascript';script.src=file;head.appendChild(script);},debug:function(msg){try{if(this.isInDebugMode())
console.log(msg);}
catch(e){}},Persistence:{confirmDelete:function(requestBase,value,page,controller,action){var decision=confirm('Are you sure you want to delete this record?');if(decision===true)
location.href=requestBase+'/'+controller+'/'+action+'/'+value+'/'+page;},setStyle:function(el,style){el.setAttribute('class',style);},search:function(){var pos=location.pathname.indexOf('.php')+5;var mvcQuery=location.pathname.substring(pos);var mvcArgs=mvcQuery.split('/');var controller=mvcArgs[0];var keyword=document.getElementById('agilephpSearchText').value;var field=document.getElementById('agilephpSearchField').value;var url=location.protocol+'//'+location.host+AgilePHP.getRequestBase()+'/'+controller+'/search/'+field+'/'+keyword;location.href=url;}},XHR:function(){this.instance=null;this.isAsync=true;this.requestToken=null;this.MS_PROGIDS=new Array("Msxml2.XMLHTTP.7.0","Msxml2.XMLHTTP.6.0","Msxml2.XMLHTTP.5.0","Msxml2.XMLHTTP.4.0","MSXML2.XMLHTTP.3.0","MSXML2.XMLHTTP","Microsoft.XMLHTTP");this.getInstance=function(){if(this.instance==null){if(window.XMLHttpRequest!=null)
this.instance=new window.XMLHttpRequest();else if(window.ActiveXObject!=null){for(var i=0;i<this.MS_PROGIDS.length&&!obj;i++){try{this.instance=new ActiveXObject(this.MS_PROGIDS[i]);}
catch(ex){}}}
if(this.instance==null){var msg='Could not create XHR object.';AgilePHP.debug(msg);throw msg;}}
return this.instance;},this.setAsynchronous=function(){this.isAsync=true;},this.setSynchronous=function(){this.isAsync=false;},this.setRequestToken=function(token){this.requestToken=token;},this.getRequestToken=function(){return this.requestToken;},this.eval=function(xhr){return eval('('+xhr.responseText+')');},this.request=function(url,callback){var xhr=this.getInstance();xhr.open('GET',url,this.isAsync);xhr.setRequestHeader('X-Powered-By','AgilePHP Framework');xhr.send(null);if(this.isAsync){xhr.onreadystatechange=function(){if(xhr.readyState==4){var data=(xhr.responseText.length)?eval('('+xhr.responseText+')'):null;AgilePHP.debug(data);if(callback)callback(data);}}}
else{var data=(xhr.responseText.length)?eval('('+xhr.responseText+')'):null;AgilePHP.debug(data);return data;}},this.post=function(url,data,callback){if(this.getRequestToken())
data='AGILEPHP_REQUEST_TOKEN='+this.getRequestToken()+'&'+data;var xhr=this.getInstance();xhr.open('POST',url,this.isAsync);xhr.setRequestHeader('X-Powered-By','AgilePHP Framework');xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');xhr.setRequestHeader('Content-length',data.length);xhr.setRequestHeader('Connection','close');xhr.send(data);if(this.isAsync){xhr.onreadystatechange=function(){if(xhr.readyState==4){var data=(xhr.responseText.length)?eval('('+xhr.responseText+')'):null;AgilePHP.debug(data);if(callback)callback(data);}}}
else{var data=(xhr.responseText.length)?eval('('+xhr.responseText+')'):null;AgilePHP.debug(data);return data;}},this.updater=function(url,el){var xhr=this.getInstance();xhr.open('GET',url,this.isAsync);xhr.setRequestHeader('X-Powered-By','AgilePHP Framework');xhr.send(null);if(this.isAsync){xhr.onreadystatechange=function(){if(xhr.readyState==4){AgilePHP.debug(xhr);AgilePHP.debug(el);new AgilePHP.XHR().updaterHandler(xhr,el);}}}
else{AgilePHP.debug(xhr);AgilePHP.debug(el);new AgilePHP.XHR().updaterHandler(xhr,el);}},this.updaterHandler=function(o,el){try{document.getElementById(el).innerHTML=o.responseText;}
catch(e){AgilePHP.debug(e);throw e;}},this.formSubmit=function(url,form,callback){var data='';for(var i=0;i<form.getElementsByTagName('input').length;i++){if(form.getElementsByTagName('input')[i].type=='text')
data+=form.getElementsByTagName('input')[i].name+'='+
form.getElementsByTagName('input')[i].value+'&';if(form.getElementsByTagName('input')[i].type=='password')
data+=form.getElementsByTagName('input')[i].name+'='+
form.getElementsByTagName("input")[i].value+'&';if(form.getElementsByTagName('input')[i].type=='checkbox'){if(form.getElementsByTagName('input')[i].checked)
data+=form.getElementsByTagName('input')[i].name+'='+
form.getElementsByTagName('input')[i].value+'&';else
data+=form.getElementsByTagName('input')[i].name+'=&';}
if(form.getElementsByTagName('input')[i].type=='radio'){if(form.getElementsByTagName('input')[i].checked){data+=form.getElementsByTagName('input')[i].name+'='+
form.getElementsByTagName('input')[i].value+'&';}}
if(form.getElementsByTagName('input')[i].type=='hidden'){if(form.getElementsByTagName('input')[i].name=='AGILEPHP_REQUEST_TOKEN')
this.setRequestToken(form.getElementsByTagName('input')[i].value);}}
for(var i=0;i<form.getElementsByTagName('select').length;i++){var index=form.getElementsByTagName('select')[i].selectedIndex;data+=form.getElementsByTagName('select')[i].name+'='+
form.getElementsByTagName('select')[i].options[index].value+'&';}
for(var i=0;i<form.getElementsByTagName('textarea').length;i++)
data+=form.getElementsByTagName('textarea')[i].name+'='+
form.getElementsByTagName('textarea')[i].value+'&';data=data.substring(0,data.length-1);if(callback==undefined||callback==null){this.setSynchronous(true);return this.post(url,data);}
this.post(url,data,callback);}},MVC:{controller:'IndexController',action:'index',parameters:[],setController:function(controller){AgilePHP.MVC.controller=controller;},getController:function(){return AgilePHP.MVC.controller;},setAction:function(action){AgilePHP.MVC.action=action;},getAction:function(){return AgilePHP.MVC.action;},setParameters:function(params){if(typeof params=='Array'){AgilePHP.MVC.parameters=params.join('/');return;}
AgilePHP.MVC.parameters=params;},processRequest:function(callback){var url=AgilePHP.getRequestBase()+'/'+this.getController()+'/'+this.getAction();if(this.parameters.length)
url+='/'+this.parameters.join('/');if(callback!=undefined)
new AgilePHP.XHR().request(url,callback)
else{var xhr=new AgilePHP.XHR();xhr.setSynchronous(true);return xhr.request(url);}}},Remoting:{controller:null,setController:function(controller){AgilePHP.Remoting.controller=controller;},getController:function(){return AgilePHP.Remoting.controller;},invoke:function(stub,method,parameters){AgilePHP.debug('AgilePHP.Remoting.invoke');AgilePHP.debug(stub);AgilePHP.debug(method);AgilePHP.debug(parameters);var clazz=stub._class
var callback=stub._callback;delete stub._class;delete stub._callback;var url=AgilePHP.getRequestBase()+'/'+AgilePHP.Remoting.controller+'/invoke';var data='class='+clazz+'&method='+method+'&constructorArgs='+JSON.stringify(stub);if(parameters!=undefined){var o=new Object();for(var i=0;i<parameters.length;i++)
o['argument'+(i+1)]=parameters[i];data+='&parameters='+JSON.stringify(o);}
if(callback==undefined){var xhr=new AgilePHP.XHR();xhr.setSynchronous(true);return xhr.post(url,data);}
new AgilePHP.XHR().post(url,data,callback);}}}