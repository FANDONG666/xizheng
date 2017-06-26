$(document).ready(function(){

	// Lowering the opacity of all slide in divs
	$('.new div').css('opacity',0.4);

	// Using the hover method 
	$('.new').hover(function(){

		// Executed on mouseenter
		
		var el = $(this);
		
		// Find all the divs inside the banner div,
		// and animate them with the new size
		
		el.find('div').stop().animate({width:600,height:600},'slow',function(){
			// Show the "Visit Company" text:
			el.find('p').fadeIn('fast');
		});

	},function(){

		// Executed on moseleave

		var el = $(this);
		
		// Hiding the text
		el.find('p').stop(true,true).hide();
		
		// Animating the divs
		el.find('div').stop().animate({width:180,height:180},'fast');

	}).click(function(){
		
		// When clicked, open a tab with the address of the hyperlink
		
		// window.open($(this).find('a').attr('href'));
		
	});
	/**/
	$(".content li").hover(function() {
		var index = $(this).index();
		if(index == 0) {
			$(".content li div img")[0].src = "/themes/wangge/img/11.png";
		}
	}, function() {
		$(".content li div img")[0].src = "/themes/wangge/img/1.png";
	})
	$(".content li").hover(function() {
		var index = $(this).index();
		if(index == 1) {
			$(".content li div img")[1].src = "/themes/wangge/img/22.png";
		}
	}, function() {
		$(".content li div img")[1].src = "/themes/wangge/img/2.png";
	})
	$(".content li").hover(function() {
		var index = $(this).index();
		if(index == 2) {
			$(".content li div img")[2].src = "/themes/wangge/img/33.png";
		}
	}, function() {
		$(".content li div img")[2].src = "/themes/wangge/img/3.png";
	})
	$(".content li").hover(function() {
		var index = $(this).index();
		if(index == 3) {
			$(".content li div img")[3].src = "/themes/wangge/img/44.png";
		}
	}, function() {
		$(".content li div img")[3].src = "/themes/wangge/img/4.png";
	})
	$(".content li").hover(function() {
		var index = $(this).index();
		if(index == 4) {
			$(".content li div img")[4].src = "/themes/wangge/img/55.png";
		}
	}, function() {
		$(".content li div img")[4].src = "/themes/wangge/img/5.png";
	})
	$(".content li").hover(function() {
		var index = $(this).index();
		if(index == 5) {
			$(".content li div img")[5].src = "/themes/wangge/img/66.png";
		}
	}, function() {
		$(".content li div img")[5].src = "/themes/wangge/img/6.png";
	})
	/*$(".fac_cont p span")[0].addClass("cread")*/
	$(".fac_cont p span").on("click",function(){
		var index =$(this).index();
		
		if(index==0){
			$(".fac_cont .fac_re .fac_re1").stop(true).animate({left:"0"});
		}
		if(index==1){
			$(".fac_cont .fac_re .fac_re1").stop(true).animate({left:"-1085px"});
		}
		
	})

	$(".laws_list li").on('click', function () {
		location.href = $(this).attr('data-href');
    })
	
});

/**
 * 提交留言
 */
function guestBook() {
    var username = $(".feedback input").eq(0).val();
    var title = $(".feedback input").eq(1).val();
    var content = $(".feedback textarea").val();
    var flag = true;
    if (username == '') {
        $(".feedback input").eq(0).next().show();
        flag = false;
    } else {
        $(".feedback input").eq(0).next().hide();
    }
    if (title == '') {
        $(".feedback input").eq(1).next().show();
        flag = false;
    } else {
        $(".feedback input").eq(1).next().hide();
    }
    if (content == '') {
        $(".feedback textarea").next().show();
        flag = false;
    } else {
        $(".feedback textarea").next().hide();
    }
    if (!flag) {
		return false;
    }
    $.ajax({
        url: guestBookUrl,
        type: 'post',
        dataType: 'json',
        data: {
            username: username,
            title: title,
            content: content
        },
        success: function (res) {
            if (res.code === 0) {
                $(".feedback input").eq(0).val('');
                $(".feedback input").eq(1).val('');
                $(".feedback textarea").val('');
            }
            alert(res.msg);
        },
        error: function (res) {
            alert('提交失败');
        }
    })
}