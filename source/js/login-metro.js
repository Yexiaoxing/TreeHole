$(document).ready(function(){
	$(window).resize(function(){
		$('.class_login_box').css({position:'relative',top:179,left:($('.class_login_bg').width()-$('.class_login_box').width())/2});
	});
	$(window).resize();
});