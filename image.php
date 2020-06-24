<?php
if(isset($_GET['id']))
{	
	$id = trim(intval($_GET['id']));
	/*
	$id = trim(mysql_real_escape_string(intval($_GET['id'])));
	$query_string = "SELECT * FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `pid`='$id'";
	$query = ysql_query($query_string,$con1) or die(sql_error("Error"));
	$result = mysql_fetch_assoc($query);*/
	$query_string = "SELECT * FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `pid`=?";
	$query = $db1->prepare($query_string);
	$query->execute(array($id));
	$result = $query->fetch();
	?>
<script src="./content/js/AC_RunActiveContent.js" type="text/javascript"></script>

	<div id="magnify_brand"><?=$result['brand']?></div>
	<div id="magnify_model"><?=findreplace($result['prod_title'],"displayraw")?></div><br />
	<span class="b1"></span><span class="b2"></span><span class="b3"></span><span class="b4"></span>
  <div class="contentb">
	<div id="close"><a href="?p=productdetail&amp;id=<?=$id?>" title="Close" id="closelink"><img src="./content/images/main/close.png" alt="Close" /></a></div>
  <div id="magnify">
		<script type="text/javascript">
AC_FL_RunContent( 'type','application/x-shockwave-flash','data','content/flash/zoomifyViewer.swf','width','765','height','400','id','theMovie','flashvars','zoomifyImagePath=content/images/products/large/<?=$result['pid']?>/&zoomifyToolbar=1&zoomifyNavWindow=0&zoomifySlider=0','src','./content/flash/zoomifyViewer','menu','false','wmode','transparent','pluginsage','http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'); //end AC code
</script><noscript><object type="application/x-shockwave-flash" data="./content/flash/zoomifyViewer.swf" width="765" height="400" id="theMovie">
		<param name="FlashVars" value="zoomifyImagePath=content/images/products/large/<?=$result['pid']?>/&amp;zoomifyToolbar=1&amp;zoomifyNavWindow=0&amp;zoomifySlider=0" />
		<param name="menu" value="false" />
		<param name="wmode" value="transparent" />
		<param name="src" value="./content/flash/zoomifyViewer.swf" />
		</object></noscript>
	<div id="blanking"><img src="./content/images/products/blanking.jpg" alt="" /></div>
  <div id="magnify_legend">
		<div style="float:left;width:166px;margin-left:259px;">Zoom In/Out</div>
		<div style="float:left;width:74px;margin-left:18px;">Move</div>
		<div style="float:left;width:36px;">Reset</div>
		<div class="clear"></div>
	</div>
	</div>
  </div>
	<span class="b4"></span><span class="b3"></span><span class="b2"></span><span class="b1"></span>
	
	<div>After zooming, click and drag the image in any direction to view more detail</div>
	<script type="text/javascript"><!--//
	document.getElementById("closelink").setAttribute('href','javascript:history.back();');
	//--></script>
	<?php 
}?>


	
