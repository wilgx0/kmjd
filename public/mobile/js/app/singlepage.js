common.setHtmlFontSize();
var app = new Vue({
	el: '#app',
	data: {
        article:{},
	},
	methods: {


	},
	watch: {

	},
    created:function(){

    }
});

(function($) {
	$.init();

	//列表文章分类的id
    var id = common.getQueryString('id');

    if(!id){
    	$.alert('文章的参数id不能为空！','提示!');
    	return false;
    }

    common.get(common.baseUrl + '/api/portal/articles/'+ id, {}, function(data) {
       //console.log(data);
       if(data.code == 1){
           app.article = data.data;
           console.log(app.article)
       }
    });





})(mui);
