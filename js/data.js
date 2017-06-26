var host='http://xizhengjingshui.tao3w.com/';
   /*ajax封装*/
function ajax(options){
    var opts = $.extend({}, options);
    return $.ajax({
            type:opts.type || 'get',
            dataType:'json',
            url:host+opts.url,
            data:opts.data||{},
            success:opts.success,
            error:opts.error
    })
}
/*产品导航*/
function proNav(){
	ajax({
		url:'api/api/productCategory',
		success:function(res){
	  		var html='';
			var id1="";
			id1 = res.data[0].term_id;
			$.each(res.data,function(i,d){
				html+='<i data-b="'+d.term_id+'">'+d.name+'</i>';
			})
			$(".select").html(html);
		}
	})
	$(".js_select").on("click","i",function(){
		var id = this.dataset.b;
		location.href="products.html?id="+id;
	});
}
/*产品目录*/
function catalog(){
	ajax({
		url:'api/api/productCategory',
		success:function(res){
	  		var html='';
			var id1= !!location.search.split('?id=')[1] ? parseInt( location.search.split('?id=')[1] ):  res.data[0].term_id ;

			$.each(res.data,function(i,d){
				if(id1 == d.term_id){
					html+='<li onclick="tabList(this)" class="cur" data-i="'+d.term_id+'"><img src="img/pro-sj1.png" alt="" /><a href="JavaScript:;">'+d.name+'</a></li>';
				}else{
					html+='<li onclick="tabList(this)" data-i="'+d.term_id+'"><img src="img/pro-sj1.png" alt="" /><a href="JavaScript:;">'+d.name+'</a></li>';
				}
			});
			$('.pro-center').html(html);
			$('.pro-center>:first img').show();
			
			proList(1,id1);
		}
	})
}
/*产品列表*/
function proList(pagenum,id){
	ajax({
		url:'api/api/posts',
		type:"get",
		data:{
			term_id: Number(id),
			page_size: 6,
			p: pagenum
		},
		success:function(res){
			var html = '';
			$.each(res.data.posts,function(i,d){
				html+='<li data-a="'+d.object_id+'" data-c="'+d.term_id+'"><img src='+ res.data.upload_path + JSON.parse(d.smeta).thumb +' /><div class="proTit" style="display:none">'+d.post_title+'</div></li>';
			})
			$('.pro-ul').html(html);
			$('.js_page').html('<div class="tcdPageCode" ></div>');
			var how=Math.ceil(res.data.total/6);
			$(".tcdPageCode").attr({"how":how,"ids":id,"page":pagenum});

			$(".tcdPageCode").createPage({
				pageCount: how,
				current: pagenum,
				backFn:function(p){
					proList(p,id);
					$(".tcdPageCode"+id).attr("btn",true);
				}
			})
			$('.pro-ul li').mouseenter(function(){
				var index = $('.pro-ul li').index(this);
				$('.proTit').hide();
				$('.proTit').eq(index).slideDown();		
			})
			$('.pro-ul li').mouseleave(function(){
				var index = $('.pro-ul li').index(this);
				$('.proTit').hide();
				$('.proTit').eq(index).slideUp();					
			})
			$('.pro-ul').on("click","li",function(){
				var id = this.dataset.c;
				var td = this.dataset.a;
				location.href="productsList.html?id="+id+"&termId="+td;
			})
			proDetails();
		}
	})
}
/*产品详情*/
function proDetails(){
	var proId= parseInt(location.search.split('?id=')[1]);
	var term_id= parseInt(location.search.split('&termId=')[1]);
	ajax({
		url:'api/api/detail',
		type:'get',
		data:{
			id:term_id,
			term_id:proId,
		},
		success:function(res){

			var post_title =!!res.data.current ? res.data.current.post_title : "";
			var post_content =!!res.data.current ? res.data.current.post_content : "";
			var smeta = !!res.data.current ? res.data.current.smeta : "";
			var industry="",parameter="",intro="",video_url="";
			
			if(!!res.data.current && res.data.current.detail){
				industry = res.data.current.detail.industry;
				parameter =res.data.current.detail.parameter;
				intro =res.data.current.detail.intro;
				video_url =res.data.current.detail.video_url;
			}
			$('.part-tit').html(post_title);
			$('.part-text p').html(post_content);
			$('.part-js p').html(industry);
			bb();
			cc();
			
			if(!!smeta){
				$('.part-Img img').attr("src",res.data.upload_path + smeta);
			}else{
				$('.part-Img img').css("display","none");
			}
			
			$('.part2-list1').html(post_content);
			$('.part2-list2').html(parameter);
			$('.part2-list3').html(intro);
			$('.part2-list4').html(industry);
			$('#video').attr("src",video_url);
		}
	})
	
}
/*关于我们*/
function about(){
	ajax({
		url:'api/api/page',
		data:{
			id:63
		},
		success:function(res){
			$('.ind-about').html(res.data.content);
			$('.ind-imgDm').attr("src",res.data.upload_path+res.data.pic);
		}
	})
}
/*热销产品*/
function hotProducts(){
	ajax({
		url:'api/api/hotProduct',
		success:function(res){
			var html ='';
			$.each(res.data.posts,function(i,d){
				html+='<li><div class="pic ind-sclImg"><div class="proImgs"><img data-q="'+d.object_id+'" data-j="'+d.term_id+'" src='+res.data.upload_path+JSON.parse(d.smeta).thumb+' /><span class="indimg-tit">'+d.post_title+'</span></div></div></li>'
			});
			$('#hotPro').html(html);
			jQuery(".slideGroup .slideBox").slide({ mainCell:"ul",vis:3,prevCell:".sPrev",nextCell:".sNext",effect:"leftLoop"});
			$('.proImgs').on("click","img",function(){
				var id = this.dataset.j;
				var td = this.dataset.q;
				location.href="productsList.html?id="+id+"&termId="+td;
			})
			proDetails();
		}
	})
}
/*销售网络*/
function introduction(){
	ajax({
		url:'api/api/page',
		type:"get",
		data:{
			id:8
		},
		success:function(res){
			$('.js-mes-con').html(res.data.content);
			$("#dh").css("display", "block");
		}
	})
}
/*售后服务*/
function questions(){
	ajax({
		url:'/api/api/page',
		type:'get',
		data:{
			id:56
		},
		success:function(res){
			var html = '';
			$.each(res.data.faq, function(i,d) {
				html+='<li data-i="'+d.id+'"><div class="question">'
				+'<img class="wt" src="img/w.png"/><span>'+d.question+'</span></div><div class="answer  clearfix">'
				+'<img class="dan fl" src="img/d.png"/><div class="answer-text fl"><p>'+d.answer+'</p></div></div></li>';
			});
			$('.questionUl').html(html);
			$('.ser-top').after(res.data.content);
		}
	})
}
/*联系我们 */
function contact(){
	ajax({
		url:'api/api/contactInformation',
		success:function(res){
			$('.tel').html("电话："+res.data.tel);
			$('.email').html("邮箱："+res.data.email);
			$('.fax').html("传真："+res.data.fax);
			$('.address').html("地址："+res.data.address);
			$('.con-map').attr("src",res.data.upload_path+res.data.map_pic);
		}
	})
}
/*新闻列表*/
function newsList(num){
	ajax({
		url:'api/api/posts',
		data:{
			term_id:76,
			p:num,
			page_size:6
		},
		type:'get',
		success:function(res){
			var html = '';
			$.each(res.data.posts,function(i,d){
				html+='<li><div class="newsImg"><img src='+res.data.upload_path + JSON.parse(d.smeta).thumb+'  /></div><div class="newsDiv">'
						+'<h1 class="newsTitle">'+d.post_title+'</h1><div class="news-time">'+d.post_date+'</div><div class="news-text">'
						+'<p>'+d.post_content+'</p></div></div><a class="newsListA" href="newsList.html?id='+d.object_id+'&termId='+d.term_id+'"><img src="img/newsJr.png" alt="" class="more"/></a></li>'
			});
			$('.news-list').html(html);
			$(".more").hover(function(){
				$(this).attr("src","img/newsJr1.png");
			},function(){
				$(this).attr("src","img/newsJr.png");
			})
			aa();
			var how=Math.ceil(res.data.total/6);
			$(".tcdPageCode").attr({"how":how,"page":num});
			$(".tcdPageCode").createPage({
				pageCount: how,
				current:num,
				backFn:function(p){
					newsList(p);
					$(".tcdPageCode").attr("btn",true);
				}
			})
		}
	})
}
/*新闻详情*/
function newsDetail(){
	var pageId= parseInt(location.search.split('?id=')[1]);
	var term_id= parseInt(location.search.split('&termId=')[1]);
	ajax({
		url:'api/api/detail',
		data:{
			id:pageId,
			term_id:term_id
		},
		success:function(res){
			$('.newsL-titlt').html(res.data.current.post_title);
			$('.readNums').html("阅读次数："+res.data.current.post_hits);
			$('.timimg').html("日期："+res.data.current.post_date);
			$('.newsL-text').html(res.data.current.post_content);
			if(!!res.data.prev){
				var prev = res.data.prev;
				$('.newsL-next').html("下一篇："+prev.post_title).attr({"term_id":prev.term_id ,"id":prev.id});
			}
			if(!!res.data.next){
				var next = res.data.next;
				$('.newsL-pre').html("上一篇："+next.post_title).attr({"term_id":next.term_id ,"id":next.id});;
			}
		}
	})
}
$(".newsL-pre").on("click",function(){
	var pageId = $(this).attr("id");
	var term_id = $(this).attr("term_id");
	location.href="newsList.html?id="+pageId+"&termId="+term_id;
	newsDetail();
});
$(".newsL-next").on("click",function(){
	var pageId = $(this).attr("id");
	var term_id = $(this).attr("term_id");
	location.href="newsList.html?id="+pageId+"&termId="+term_id;
	newsDetail();
});
/*留言*/
function message(){
	var name=$('#username').val();
	var email=$('#email').val();
	var mobile=$('#mobile').val();
	var content=$('#content').val();
	ajax({
		url:'api/api/guestbook',
		type:'post',
		data:{
			name:name,
			email:email,
			phone:mobile,
			msg:content
		},
		success:function(res){
			if(res.error_code==0){
				console.log("成功");
				$('#username').val('');
				$('#email').val('');
				$('#mobile').val('');
				$('#content').val('');
			}
		}
	})
}
/*首页表单*/
function indMessage(){
	var name=$('#ind-name').val();
	var mobile=$('#ind-tel').val();
	var ly=$('#ind-ly').val();
	ajax({
		url:'api/api/guestbook',
		type:'post',
		data:{
			name:name,
			phone:mobile,
			msg:ly
		},
		success:function(res){
			if(res.error_code==0){
				console.log("成功");
				$('#ind-name').val('');
				$('#ind-tel').val('');
				$('#ind-ly').val('');
			}
		}
	})
}
/*首页联系方式*/
function indInfo(){
	ajax({
		url:'api/api/contactInformation',
		type:'post',
		success:function(res){
			$('.indTel').html("电话："+res.data.tel);
			$('.indeAdr').html("地址："+res.data.address);
			$('.indEm').html("邮箱："+res.data.email);
			$('.fwdh').html("服务网点定期更新，详情请拨打官方免费热线  "+res.data.tel+" 查询实时情况。")
		}
	})
}
