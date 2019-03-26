var _mui;
try{
    _mui = mui;
}catch(e){
    _mui = {};
}

(function($) {
	if(!window['common']){window['common']={}};
	
	
	var baseUrl = 'http://www.kmjd.my';
	window['common']['baseUrl'] = baseUrl;
	
	/**
	 * 设置根元素的字体大小
	 */
	function setHtmlFontSize(){
		var htmlWidth = document.documentElement.clientWidth || document.body.clientWidth;
		if(htmlWidth > 750) {htmlWidth = 750;}
		var htmlDom = document.getElementsByTagName('html')[0];
		htmlDom.style.fontSize = htmlWidth / 20 + 'px';
	}
	window['common']['setHtmlFontSize'] = setHtmlFontSize;

    /**
     * 注销
     */
	function logoff(){
        localStorage.clear();
        location.reload()
    }
    window['common']['logoff'] = logoff;

    function getTokenInfo(){
        var  token  =  localStorage.getItem('XX-Token');
        var  device  = localStorage.getItem('XX-Device-Type');
        var  user = localStorage.getItem('user');
        if(token){
            return {
                token  : token,
                device : device,
                user   : user,
            }
        } else {
            return false;
        }

    }
    window['common']['getTokenInfo'] = getTokenInfo;

    //检查是否登录
	function isLogin(callback){
        token_http(baseUrl + '/api/pm/user/getUserId',{},function(response){
           if(response.code == 1){
               callback(true);
           } else {
               callback(false);
           }
        })
    }
	window['common']['isLogin'] =isLogin;

	function token_http(url,param,callback,error){
        http(url,param,callback,error,type='post',true);
    }
    window['common']['token_http'] = token_http;


    function token_get(url,param,callback,error){
        http(url,param,callback,error,type='get',true);
    }
    window['common']['token_get'] = token_get;

	/**
	 * 获取远程数据
	 */
	function http(url,param,callback,error,type='post',isToken = false) {
	    var head = {};
	    if(isToken){
            head['XX-Token'] = localStorage.getItem('XX-Token');
            head['XX-Device-Type'] = localStorage.getItem('XX-Device-Type');
        }

		//请求数据
		$.ajax(url, {
			data: param || '',
			dataType: 'json', //服务器返回json格式数据
			type: type, //HTTP请求类型
            headers:head,
            // timeout: 20000, //超时时间设置为10秒；
			success: function(data) {
				if(callback){
					callback(data);
				}
			},
			error: function(xhr, type, errorThrown) {	//异常处理；	
				console.log(arguments)
				if(error){
					error(xhr, type, errorThrown);
				}		
			}
		});
	}
	window['common']['http'] = http;
	
	/**
	 * 获取远程数据
	 */
	function get(url,param,callback,error) {
		http(url,param,callback,error,'get');
	}
	window['common']['get'] = get;
	
	
	//获取网页参数
	function getQueryString(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
		var r = window.location.search.substr(1).match(reg);
		if(r != null) return decodeURI(r[2]);
		return '';
	}
	window['common']['getQueryString'] = getQueryString;



    /**
     * 字符串截取
     * @param str
     * @param num
     */
    function splitStr(str,length){
        if(str == undefined) {
            return '';
        }
        if(str.length > length){
            return str.substring(0,length)+'..';
        } else {
            return str;
        }

    }
    window['common']['splitStr'] = splitStr;

    /**
     * 时间戳装换日期格式
     * @param timestamp         时间戳
     * @param format            显示的格式 ('Y/M/D' 或 'Y年M月D日')
     * @returns {string}
     */
    function timestampToTime(timestamp,format = 'Y-M-D h:m:s') {
        var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
        var datetime  = {};
        datetime.Y = date.getFullYear();
        datetime.M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1);
        datetime.D = date.getDate();
        datetime.h = date.getHours();
        datetime.m = date.getMinutes();
        datetime.s = date.getSeconds();
        return format.replace(/(\w{1})/g,function(){
            var args = arguments;
            return datetime[args[1]];
        });
    }
    window['common']['timestampToTime'] = timestampToTime;


    function http_builder_url(url, data) {
        if(typeof(url) == 'undefined' || url == null || url == '') {
            return '';
        }
        if(typeof(data) == 'undefined' || data == null || typeof(data) != 'object') {
            return '';
        }
        url += (url.indexOf("?") != -1) ? "" : "?";
        for(var k in data) {
            url += ((url.indexOf("=") != -1) ? "&" : "") + k + "=" + encodeURI(data[k]);
            console.log(url);
        }
        return url;
    }
    window['common']['http_builder_url'] = http_builder_url;
})(_mui);
