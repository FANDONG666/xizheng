function createXMLHTTPRequest() {
    var xmlHttpRequest;
    if (window.XMLHttpRequest) {
        xmlHttpRequest = new XMLHttpRequest();
        if (xmlHttpRequest.overrideMimeType) {
            xmlHttpRequest.overrideMimeType("text/xml");
        }
    } else if (window.ActiveXObject) {
        var activexName = [ "MSXML2.XMLHTTP", "Microsoft.XMLHTTP" ];
        for ( var i = 0; i < activexName.length; i++) {
            try {

                xmlHttpRequest = new ActiveXObject(activexName[i]);
                if(xmlHttpRequest){
                    break;
                }
            } catch (e) {
            }
        }
    }
    return xmlHttpRequest;
}
window.onload=function(){
    var xhr=createXMLHTTPRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState===4){
            if(xhr.status===200){
                doResponse(xhr);
            }else {
                alert('响应完成但有问题');
            }
        }
    }
    xhr.open('GET','http://wangge.tao3w.com/index.php?g=Asset&m=Ueditor&a=ueditor_template_list',true);
    xhr.send();
    function doResponse(xhr){
        var templates=JSON.parse(xhr.responseText);
        (function () {
            var me = editor,
                preview = $G( "preview" ),
                preitem = $G( "preitem" ),
                tmps = templates,
                currentTmp;
            var initPre = function () {
                var str = "";
                for ( var i = 0, tmp; tmp = tmps[i++]; ) {
                    str += '<div class="preitem" data-i="'+tmp.id+'" onclick="pre(' + i + ')"><p>' + (tmp.title ? tmp.title + "" : "") + '</p></div>';
                }
                preitem.innerHTML = str;
            };
            var pre = function ( n ) {
                var tmp = tmps[n - 1];
                currentTmp = tmp;
                clearItem();
                domUtils.setStyles( preitem.childNodes[n - 1], {
                    "background-color":"lemonChiffon",
                    "border":"#ccc 1px solid"
                } );
                preview.innerHTML = tmp.preHtml ? tmp.preHtml : "";
            };
            var clearItem = function () {
                var items = preitem.children;
                for ( var i = 0, item; item = items[i++]; ) {
                    domUtils.setStyles( item, {
                        "background-color":"",
                        "border":"white 1px solid"
                    } );
                }
            };
            dialog.onok = function () {
                if ( !$G( "issave" ).checked ){
                    me.execCommand( "cleardoc" );
                }
                var obj = {
                    html:currentTmp && currentTmp.html
                };
                me.execCommand( "template", obj );
            };
            initPre();
            window.pre = pre;
            pre(2)
        })();
    }
};
//删除模板
function createXMLHTTPRequest() {
    var xmlHttpRequest;
    if (window.XMLHttpRequest) {
        xmlHttpRequest = new XMLHttpRequest();
        if (xmlHttpRequest.overrideMimeType) {
            xmlHttpRequest.overrideMimeType("text/xml");
        }
    } else if (window.ActiveXObject) {
        var activexName = [ "MSXML2.XMLHTTP", "Microsoft.XMLHTTP" ];
        for ( var i = 0; i < activexName.length; i++) {
            try {

                xmlHttpRequest = new ActiveXObject(activexName[i]);
                if(xmlHttpRequest){
                    break;
                }
            } catch (e) {
            }
        }
    }
    return xmlHttpRequest;
}
function deleteMuban(){
    var delHtml=document.getElementById('preview').innerHTML;
    var dl=document.getElementsByClassName('preitem');
    for(var i=0;i<dl.length;i++){
        if(dl[i].getAttribute('style')!='border: 1px solid white;'){
            var num=i+1;
        };
    }
    var child=document.querySelectorAll('.preitem:nth-child('+num+')')[0];
    var id=document.querySelectorAll('.preitem:nth-child('+num+')')[0].dataset.i;
    var xhr = createXMLHTTPRequest() ;
    xhr.onreadystatechange = function(){
        if(xhr.readyState===4){
            if(xhr.status===200){
                doResponse(xhr);
            }else {
                alert('响应完成但有问题');
            }
        }
    }
    xhr.open('POST','http://wangge.tao3w.com/index.php?g=Asset&m=Ueditor&a=ueditor_template_del&id='+id,true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.send();
    function doResponse(xhr){
        alert('模板删除成功！');
        child.style.display="none";
    }
}