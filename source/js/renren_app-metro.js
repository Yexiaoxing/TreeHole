$(document).ready(function(){
	/* 以下为业务流程 */
	$(".class_button").hover(function(){
		if( !sendState ) $(this).stop().animate({backgroundColor:"#FFFFFF",color:"#7eafd4"},300);
	},function(){
		if( !sendState ) $(this).stop().animate({backgroundColor:"#7eafd4",color:"#FFFFFF"},300);
	});
	$(".class_page_lnk").hover(function(){
		$(this).stop().animate({color:"#ff7e7e"},300);
	},function(){
		$(this).stop().animate({color:"#555555"},300);
	});
	$(".class_about").hover(function(){
		$(this).stop().animate({color:"#ff7e7e"},300);
	},function(){
		$(this).stop().animate({color:"#555555"},300);
	});
	$(".class_status_list_box").hover(function(){
		$('.class_textarea_status').add('.class_button').add('.class_div_alert').stop(true,false).fadeTo(800,0.01).parent().stop(true,false).slideUp(800);
		$(this).stop(true,false).animate({height:'580px'});
	},function(){
		$('.class_textarea_status').add('.class_button').add('.class_div_alert').parent().stop(true,false).slideDown(800).children().stop(true,false).fadeTo(800,1);
		$(this).stop(true,false).animate({height:'270px'});
	});
	$("#btn_send").click(function(){
		if(sendState||!lengthCheck) {return;} else {$("#btn_send").css("cursor","wait");sendState=1;}
		if($('.class_textarea_status').val()=="") {showAlert("先写点什么吧..",0); sendState = 0; return;} else { showAlert("树洞发送中..",1,0); sendState=1; $("#btn_send").css("cursor","wait"); }
		$('#btn_send').stop(true,true).animate({color:'#eee',backgroundColor:'#999'},1000).add('.class_textarea_status').css('borderColor','#999');
		$('.class_textarea_status').attr('disabled','true');
		$.ajax({
			url: "sendMsg.php",
			type: "post",
			data: {'s':encodeURI($('.class_textarea_status').val()),'sid':$('.class_textarea_status').attr('data-sid')},
			success: function(resultData){
				sendState = 0;
				$('#btn_send').stop(true,true).animate({color:'#FFFFFF',backgroundColor:'#7eafd4'},1000).add('.class_textarea_status').css('borderColor','#7eafd4');
				$('.class_textarea_status').removeAttr('disabled');
				if(resultData=='1'){
					showAlert("发送成功！");
					$('.class_textarea_status').val('');
				} else {
					if(resultData=="") resultData ="网络错误 请检查网络或刷新页面"; //Sorry，API数量限制，请5分钟以后试试吧。
					showAlert("发送失败: "+resultData,0);
				}
			},
			error:function( jqXHR, textStatus, errorThrown ){
				sendState = 0;
				$('#btn_send').stop(true,true).animate({color:'#FFFFFF',backgroundColor:'#7eafd4'},1000).add('.class_textarea_status').css('borderColor','#7eafd4');
				$('.class_textarea_status').removeAttr('disabled');
				showAlert("发送失败："+textStatus+'['+errorThrown+']',0);
			}
		}); 
		
	});
	$('.class_textarea_status').live('click keyup',function(){
		if(this.value.length>240) {if( !sendState ) showAlert("超过"+(this.value.length-240)+'字',0,0); lengthCheck = 0;}
		else {if( !sendState ) showAlert("剩余"+(240-this.value.length)+'字',1,2000); lengthCheck = 1;}
	});
	/* 以下为流式设计 */
	var theWindow = $(window),	    			    		
	resizeBg = function() {
		if ( theWindow.width() < 760 ) {
			$('.class_div_header').stop().animate({'height':'20px'},500);
			$('.class_textarea_status').stop().animate({width:theWindow.width()-40+'px',height:'88px'},500);
			$('.class_button').stop().animate({width:theWindow.width()-20+'px'},500);
			$('.class_div_alert').stop().animate({'min-height':'30px','width':theWindow.width()-20+'px'},500);
			$('.class_title').parents().filter('.class_div_header_resize').html('<center>'+$('.class_title').parent().removeClass('width_760').html()+'</center>');
			$('.class_tip_spliter').html('<br/>');
		} else {
			$('.class_div_header').stop().animate({'height':'40px'},500);
			$('.class_textarea_status').stop().animate({width:'734px',height:'120px'},500);
			$('.class_button').stop().animate({width:'754px'},500);
			$('.class_div_alert').stop().animate({'min-height':'30px','width':theWindow.width()-20+'px'},500);
			$('.class_title').parents().filter('.class_div_header_resize').html($('.class_title').parent().addClass('width_760').html());
			$('.class_tip_spliter').html('&nbsp;');
		}
					
	}
	theWindow.resize(resizeBg).trigger("resize");
});
/* 以下为公共函数流程 */
closeTimer = 0;
sendState  = 0;
lengthCheck= 1;
function showAlert(sMsg,iStyle,iTime){
	if( typeof(iTime) == 'undefined' ) iTime = 8000;
	switch(iStyle){
	case 0:
		$("#id_div_alert").removeClass('class_div_alert_success').addClass('class_div_alert_error');
	break;
	case 1:
	default:
		$("#id_div_alert").removeClass('class_div_alert_error').addClass('class_div_alert_success');
	break;
	}
	$('#id_a_tip').stop(true,false).css('visibility','visible').hide().text(sMsg).fadeIn(400);
	if( iTime&&!closeTimer ) closeTimer = setTimeout(closeAlert,iTime);
	$("#btn_send").css("cursor","pointer");
}
function closeAlert(){
	closeTimer = 0;
	$('#id_a_tip').stop(true,false).fadeOut(1000);
}