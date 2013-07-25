<?php
	include_once('inc/TmsPageManager.class.php');
	$pm = new TmsPageManager();
	if( $pm->signed() ) {
		header('location: admin.php');
	}
	$access_url = 'https://graph.renren.com/oauth/authorize?client_id='.$config->APIKey.'&redirect_uri='.urlencode($config->RedirectURI).'&response_type=code&scope=read_user_album+read_user_feed+admin_page';
	if( $_REQUEST['s']=='changeaccount' ) $access_url .= '&x_renew=true';
?><!DOCTYPE html>
<!-- tinymins 2013  http://ZhaiYiMing.CoM -->
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
  <title>加入我们 - 人人树洞</title>
  <link rel="stylesheet" href="css/index-default.css">
  <link rel="icon"  type="image/jpeg" href="image/favoicon.jpg">
</head>
<body>
<div class="header">
      <div class="widthLimiter">
		<center>
			<a href="http://shuhole.sinaapp.com" class="title">加入我们 - 人人树洞</a>
		</center>
	  </div>
</div>
<div class="widthLimiter">
	<center><h4 class="subtitle">放在树洞里的秘密</h4></center>
	<hr />
	<div class = "row">
		<br/>
		<center><a class="radius button" id="btn_send" href="<?php echo $access_url ?>" style="font-size:60px">加入树洞</a></center>
		<br/>
		<center><h4 class="subtitle" style="text-shadow: 0 1px 0 #D6D6D6;">状态会实时匿名发布到</h4></center>
		<center><h4><a class="pagelink" href="http://page.renren.com/" target="_blank"><strong>您的人人公共主页</strong></a></h4></center>
	</div>
	<hr/>
	<center><a href="http://weibo.com/zymah" target="_blank" style="color:#444;">About</a></center>
</div>
<script type="text/javascript" src="js/jquery.1.8.2.min.js"></script>
  
<script type="text/javascript">
	$(window).load(function() {
		var theWindow = $(window),	    			    		
		resizeBg = function() {
			if ( theWindow.width() < 700 ) {
				$('.widthLimiter').css({'align':'center','width':'auto'});
				$('#btn_send').css({width:theWindow.width()-50+'px'});
			} else {
				$('.widthLimiter').css({'align':'left','width':'640px'});
				$('#btn_send').add('#status').css({width:''});
			}
		}
		theWindow.resize(resizeBg).trigger("resize");
	});
</script>

</body>
</html>
