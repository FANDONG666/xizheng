/*头部导航*/
$('.nav li').mouseenter(function(){
	var index = $('.nav li').index(this);
	$(".nav li em").removeClass('addBar');
	$(".nav li em").eq(index).addClass('addBar').siblings('.nav li em');
	if(index==1){
		$('.select').slideDown();
	}else{
		$('.select').slideUp();
	}
})
$('.fix-scan').hover(function(){
	$('.codeJx').show();
},function(){
	$('.codeJx').hide();
})
function aa(){
	$(".news-text").each(function(i){
	    var divH = $(this).height();
	    var $p = $("p", $(this)).eq(0);
	    while ($p.outerHeight() > divH) {
	        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
	    };
	});
}
function bb(){
	$(".part-text").each(function(i){
	    var divH = $(this).height();
	    var $p = $("p", $(this)).eq(0);
	    while ($p.outerHeight() > divH) {
	        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
	    };
	});
}
function cc(){
	$(".part-js").each(function(i){
	    var divH = $(this).height();
	    var $p = $("p", $(this)).eq(0);
	    while ($p.outerHeight() > divH) {
	        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
	    };
	});
}
$(".part-text").each(function(i){
    var divH = $(this).height();
    var $p = $("p", $(this)).eq(0);
    while ($p.outerHeight() > divH) {
        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
    };
});

$('.select').on("mousemove","i",function(){
	$(this).css("color","#45ff33").siblings().css("color","#fff");
})

$(".ind-f").hover(function(){
	$(".ind-f").attr("src","img/ind-ff.png");
},function(){
	$(".ind-f").attr("src","img/ind-f.png");
})
$(".ind-t").hover(function(){
	$(".ind-t").attr("src","img/ind-tt.png");
},function(){
	$(".ind-t").attr("src","img/ind-t.png");
})
$(".ind-i").hover(function(){
	$(".ind-i").attr("src","img/ind-ii.png");
},function(){
	$(".ind-i").attr("src","img/ind-i.png");
})
$(".ind-w").hover(function(){
	$(".ind-w").attr("src","img/ind-ww.png");
},function(){
	$(".ind-w").attr("src","img/ind-w.png");
})
$(".ind-q").hover(function(){
	$(".ind-q").attr("src","img/ind-qq.png");
},function(){
	$(".ind-q").attr("src","img/ind-q.png");
})
	
	var wh=$(window).height();
	$(window).scroll(function(){
		var s=wh-$(window).scrollTop();
		if(s<230){
			$('.sh').css({
				"-webkit-transform":"scale(1.3,1.3)",
				"-moz-transform":"scale(1.3,1.3)",
				"-o-transform":"scale(1.3,1.3)",
			})
			
			$('.ind-about').addClass('animated rollIn');
			
		}if(s<-360){
			$('.sh').css({
				"-webkit-transform":"scale(1)",
				"-moz-transform":"scale(1)",
				"-o-transform":"scale(1)",
			})
		}
		
	});
/*分页*/
$('.paging span').click(function(){
	$(this).addClass('addBGg').siblings().removeClass('addBGg')
})
/*产品目录*/
function tabList(that){
	var index = $(that).index();
	var id1= that.dataset.i;
	$('.pro-center li img').hide();
	$('.pro-center li').css("background","#f7f7f7");
	$('.pro-center li').eq(index).css("background","#34aa11");
	
	$('.pro-center li a').css("color","#666");
	$('.pro-center li a').eq(index).css("color","#fff");
	$('.pro-center li img').attr("src","img/pro-sj1.png");
	$('.pro-center li img').eq(index).show();
	proList(1,id1);
}

$('.pro-paging span').click(function(){
	var index = $('.pro-paging span').index(this);
	console.log(index);
	$('.pro-ul').eq(index).show().siblings('.pro-ul').hide();
	$(this).addClass('addBGg').siblings().removeClass('addBGg')
});
$('.pro-pre').click(function(){
	if($('.pro-paging span.addBGg')){
		var a = parseInt($('.pro-paging span').html());
	}
});
/*表单*/
$('#username').keyup(function(){
	testData()
}).blur(function(){
	testData()
});

$('#email').keyup(function(){
	testData()
}).blur(function(){
	testData()
});

$('#mobile').keyup(function(){
	testData()
}).blur(function(){
	testData()
});

function testData(){
	var data={
		username:$('#username').val(),
		email:$('#email').val(),
		tel:$('#mobile').val(),
	}
	for(var i in data)
		if(!data[i])return $('.con-sub').css("background","#c9c9c9");
		$('.con-sub').css("background","#009344");
}
/*首页下拉框*/
$('.triangle1').click(function(){
	$('.pro-slt').slideToggle()
})
$('.triangle2').click(function(){
	$('.cit-slt').slideToggle()
})
$('.cit-slt li').click(function(){
	var index = $('.cit-slt li').index(this);
	var li_val= $('.cit-slt li').eq(index).html();
	$('.city input').val("城市：      "+li_val);
	$('.cit-slt').hide()
})
$('.pro-slt li').click(function(){
	var index = $('.pro-slt li').index(this);
	var li_val= $('.pro-slt li').eq(index).html();
	$('.provinces input').val("省份：      "+li_val);
	$('.pro-slt').hide()
})
$('.pro-slt li').hover(function(){
	var index =$('.pro-slt li').index(this);
	$('.pro-slt li').css({"background":"#fff","color":"#666"});
	$('.pro-slt li').eq(index).css({"background":"#0000CC","color":"#fff"});
})
$('.cit-slt li').hover(function(){
	var index =$('.cit-slt li').index(this);
	$('.cit-slt li').css({"background":"#fff","color":"#666"});
	$('.cit-slt li').eq(index).css({"background":"#0000CC","color":"#fff"});
})
/*产品详情导航鼠标移入效果*/
$('.part1-nav span').mousemove(function(){
	$(this).addClass('greenBg').siblings().removeClass('greenBg');
})
/*产品详情tab切换 */
$('.part1-nav span').click(function(){
	var index = $('.part1-nav span').index(this);
	$('.part2-list').eq(index).show().siblings().hide();
})

$('#ind-submit').click(function(){
	$(this).css("background","transparent");
})
$('.con-sub').click(function(){
	$(this).css("background","#c9c9c9");
})





