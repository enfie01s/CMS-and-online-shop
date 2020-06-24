<div style="background:url('content/home-slides/<?=$_GET['bg']?>.jpg');height:492px;width:783px;position:relative;top:0;left:0;">
<iframe width="555" height="305" src="//www.youtube.com/embed/Lfo1QUGfWZE?autoplay=1" frameborder="0" allowfullscreen style="position:absolute;top:95px;left:97px"></iframe>
</div>
<?php if(date("U")<strtotime("31 October 2014")){?>
<a style="letter-spacing:1px;font-size:17px;display:block;color:#FFF;padding:5px;" href="./dt11-test-drive/" target="_blank">Test Drive A <img src="./dt11-test-drive/dt11text.png" alt="DT11" /> Click here for further details.</a>
<br /><br />
<?php }?>
<?php if(date("U")<strtotime("21 December 2014")){?>
<a style="letter-spacing:1px;font-size:17px;display:block;color:#FFF;padding:5px;" href="./vxr/" target="_blank"> <img src="./vxr/images/leupold.png" alt="Leupold" /> &#163;50 Cashback offer on Leupold VXR. Click here for further details.</a>
<br /><br />
<?php }?>
<div id="latestnews">
	<h1>NEWS &amp; EVENTS</h1>
	<ul>
	<?php
	$st=$deviceType=="phone"?20:49;/*
	$query = ysql_query("SELECT * FROM `gmknews` WHERE `display`='T' ORDER BY `date` DESC LIMIT 3",$con1) or die(sql_error("Error"));
	while($latest=mysql_fetch_assoc($query))*/
	$query = $db1->query("SELECT * FROM `gmknews` WHERE `display`='T' ORDER BY `date` DESC LIMIT 3");
	while($latest=$query->fetch())
	{/*$color=!isset($color)||$color=="bfc0c4"?"f3eeea":"bfc0c4";*/
	?>
		<li><a href="?p=news&amp;nid=<?=$latest['id']?>"><img src="./content/images/news/tiny/<?=$latest['id']?>.jpg" alt="" style="width:164px;height:41px;" /><?=trimtext($latest['title'],$st,'')?> <dfn>read more...</dfn></a></li>
	<?php }?>
	</ul>
	<div class="clear"></div>
</div>
	<br />
	<!--<a href="./content/pdf/SteinerCash_sales_presenter.pdf"><img src="content/images/banners/steiner.jpg" alt="Steiner Cash Sales Form" /></a><br /><br />-->
	<?php if(date("U")<strtotime("17 February 2014")&&date("U")>strtotime("1 January 2014")){?><a href="http://www.shootingshow.co.uk"><img src="content/images/banners/BritishShootingShow.jpg" alt="See us at... British Shooting Show 2014" /></a><br /><br /><?php }?>
	
