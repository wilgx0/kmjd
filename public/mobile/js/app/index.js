common.setHtmlFontSize();
var app = new Vue({
	el: '#app',
	data: {
		slideData: [],
		activeIndex: 0,
		categoriesAndList: [],
		showOffcanvas: false,
		currentAriticle: {},
	},
	methods: {
		selectTabHead: function(index) {
			this.activeIndex = index;
		},
		showArticle: function(v) {
			this.currentAriticle = v;
			this.showOffcanvas = true;
		},
		hideOffcanvas: function() {
			this.showOffcanvas = false;
			this.currentAriticle = {};
		},
		handleClickSlide:function(index){
			this.showArticle(this.slideData[index]);
		}
		
	},
	watch: {
		slideData: function() {
			var _this  = this;
			this.$nextTick(function() {
				initSwiper(_this.slideData.length > 1);
			})
		},
		categoriesAndList: function() {
			var _this = this;
			this.$nextTick(function() {
				for(var i = 0; i < _this.categoriesAndList.length; i++) {
					listInit(i, _this.categoriesAndList[i].id);
				}
			})
		}
	}
});

(function($) {
	$.init();
	//幻灯片id
	//var slideCid = 3;
	var slideCid = common.getQueryString('sid');
	//列表文章分类的id
	//var categoryIds = '1,2';
    var categoryIds = common.getQueryString('aid');
 
    if(!slideCid || !categoryIds){
    	$.alert('参数sid,aid不能为空！','提示!');
    	return false;
    }
    
	//获取幻灯片数据
    var url = common.http_builder_url(common.baseUrl + '/api/portal/lists/getCategoryPostLists',{
        category_id: slideCid,
        page: '1,3',
        order: '-id'
    });

	common.get(url, {}, function(response) {
		if(response.code == 1) {
			app.slideData = response.data.list
		}
	})

	//幻灯片初始化
	function initSwiper(loop) {
		var mySwiper = new Swiper('.swiper-container', {
			loop: loop, // 循环模式选项
			autoplay: true, //自动切换		
			on:{
				click:function(){
					var  realIndex = this.realIndex;
					app.handleClickSlide(realIndex);
				}
			},
			pagination: { // 如果需要分页器
				el: '.swiper-pagination',
			},
		});
	}
	window['initSwiper'] = initSwiper;


    url = common.http_builder_url(common.baseUrl + '/api/portal/categories',{
        ids: categoryIds,
        order: '+id'
    });
	//获取分类
	common.get(url, {}, function(data) {
		if(data.code == 1) {
			var categoriesAndList = data.data;
			var l = categoriesAndList.length - 1;
			for(var i = 0; i <= l; i++) {
				console.log(categoriesAndList);
				categoriesAndList[i]['articleList'] = [];
				var id = categoriesAndList[i]['id'];
				(function(id, i) { //获取文章
					common.http(common.baseUrl + '/api/portal/lists/getCategoryPostLists', {
						category_id: id,
						page: '1,7',
						order: '-id'
					}, function(data) {
						if(data.code == 1) {
							addArticleList(id);

							function addArticleList(id) {
								var list = categoriesAndList;
								for(var i = 0; i < list.length; i++) {
									if(list[i].id == id) {
										list[i].articleList = data.data.list;
										break;
									}
								}
							}
						}
						if(l == i) {
							app.categoriesAndList = categoriesAndList
						}
					});
				})(id, i)
			}
		}
	});

	//数据列表初始化
	function listInit(index, cid) {
		var Pullrefresh = $('#pullrefresh' + index);
		Pullrefresh.on('tap', 'li', function(event) {
			this.click();
		});
		var page = 2; //页码
		var pageSize = 7; //分页大小
		Pullrefresh.pullRefresh({
			down: { //下拉刷新
				style: 'circle',
				callback: pulldownRefresh
			},
			up: { //上拉加载
				auto: true,
				contentrefresh: '正在加载...',
				callback: pullupRefresh
			}
		});

		/**
		 * 上拉加载
		 */
		function pullupRefresh(callback) {
			common.http(common.baseUrl + '/api/portal/lists/getCategoryPostLists', {
				category_id: cid,
				page: (page++) + ',' + pageSize,
				order: '-id'
			}, function(data) {
				if(callback) {
					callback();
				}
				if(data.code == 1 && data.data.list.length > 0) {
					Pullrefresh.pullRefresh().endPullupToRefresh(false);
					var obj = app.categoriesAndList[index];
					var list = obj.articleList.concat(data.data.list);
					Vue.set(obj, 'articleList', list);
				} else {
					Pullrefresh.pullRefresh().endPullupToRefresh(true);
				}
			});
		}

		/**
		 * 下拉刷新
		 */
		function pulldownRefresh() {
			page = 1;
			Pullrefresh.pullRefresh().refresh(true); //重置上拉加载	   
			pullupRefresh(function() {
				var obj = app.categoriesAndList[index];
				obj.articleList = [];
				Pullrefresh.pullRefresh().endPulldownToRefresh();
			});
		}

	}

	window['listInit'] = listInit;
})(mui);