$(function(){
	$('#container').show().BlocksIt({
		numOfCol:4,
		offsetX: 8,
		offsetY: 8,
		blockElement: '.grid'
	});
	$("img.lazy").lazyload();	
	var loading = false;
	$(window).scroll(function(){			
		if ($(document).height() - $(this).scrollTop() - $(this).height()<150){
			loadMore();
			if($('#container').attr('data-page')!=0){
				$('#nole').fadeIn();
				$("#nole").text("加载中……");				
			}
        }
		function loadMore() {
		if (loading === false) {
			loading = true;
			var pubpage=$('#container').attr('data-page');
			var puburl=G_BASE_URL + '/rewen/ajax/get_article/__page';			
		    var foxurl= puburl + '-' +  (Number(pubpage)+1);
			$.get(foxurl , function (result){
				if ($.trim(result) != ''){
					$('#container').attr('data-page', parseInt($('#container').attr('data-page')) + 1);
					$('#container').append(result);	
					$('#container').show().BlocksIt({
						numOfCol:4,
						offsetX: 8,
						offsetY: 8,
						blockElement: '.grid'
					});
					$("img.lazy").lazyload();	
					loading = false;  
					$('#nole').fadeOut();
				}else{
					$('#container').attr('data-page', 0);
					$("#nole").text("已经没有可显示的啦");
					$('#nole').fadeIn();
				}
			});
			}else{
				return;
			}
		}
	});
	var currentWidth = 1000;
	$(window).resize(function() {
		var winWidth = $(window).width();
		var conWidth;
		if(winWidth < 300) {
			conWidth = 250;
			col = 1
		} else if(winWidth < 600) {
			conWidth = 500;
			col = 2
		} else if(winWidth < 800) {
			conWidth = 750;
			col = 3
		} else {
			conWidth = 1000;
			col = 3;
		}
		
		if(conWidth != currentWidth) {
			currentWidth = conWidth;
			$('#container').width(conWidth);
			$('#container').BlocksIt({
				numOfCol: col,
				offsetX: 8,
				offsetY: 8
			});
		}
	});
});



