<?php include "cart_head.php";?>
<div id="errorbox" style="<?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div>
<?php
$timer=$deadline-date("U");
$unixremain=$timer%3600;
$dhrs=($timer-$unixremain)/3600;
$dmins=floor($unixremain/60);

$ontext="Orders in the next<br />".($dhrs>0?$dhrs." hours, ":"").$dmins ." minutes<br />will ship today!*";
$offtext="Same day dispatch*<br />on all orders<br />placed before ".date("ga",$deadline)."!";
$datematch=date("j-n-Y");
$weekend=in_array(strtolower(date("D")),array("sat","sun"))?1:0;
$bankhol=array_key_exists($datematch,$bankhols)?1:0;
$special=date("U")>=strtotime("23 May 2016")&&date("U")<=strtotime("31 May 2016")?"MAY20":"";
?>
<div id="freeship"><img src="<?=$root_to_cart?>images/freeship.jpg" alt="SHIPPING ONLY £5 ALL ORDERS" /></div>
<div id="lifestyle"><a href="./shop/spares"><img src="<?=$root_to_cart?>images/lifestyle<?=$special?>.jpg" alt="" /></a></div>
<div id="sameday"><a href="./shop/gun%2Bstock%2Baccessories"><img src="<?=$root_to_cart?>images/sameday.jpg" alt="" /></a></div>
<!--
<div id="sameday">
	<img src="<?//=$root_to_cart?>/images/sameday.jpg" alt="" />
	<div id='jscount'>
		<?php 
		/*
		if($bankhol==0&&$weekend==0){?>
		<noscript><?=$dmins>0 || $dhrs>0?$ontext:$offtext?></noscript>
		<?php }else{echo "SAME DAY DISPATCH*<br />UNAVAILABLE DUE TO:<br /><span class='jsinfo'>".$bankhols[$datematch]."</span>";}
		*/
		?>
	</div>
</div>
-->
<div class="clear"></div>
<?php if(0/*$bankhol==0&&$weekend==0*/){?>
<script type="text/javascript">
var deadline=<?=date("H",$deadline)?>;
function startTime()
{
	var today=new Date();
	var h=today.getHours();
	var m=60-today.getMinutes();
	var s=60-today.getSeconds();
	h=deadline-h;
	if(m<60){h=h-1;}
	if(s<60){m=m-1;}
	hrs=(h>0?h+" hour"+(h>1?"s":""):"");
	mns=(m<60&&m>0?(hrs.length>0?", ":"")+m+" minute"+(m>1?"s":"")+" ":"");
	scs=(h<=0?(s==60?"0":s)+" second"+(s>1?"s":""):"");
	hms=hrs+mns+scs;
	if(h>-1)
	{
		document.getElementById('jscount').innerHTML="ORDERS IN THE NEXT<br /><span class='jsinfo'>"+hms+"</span><br />WILL SHIP TODAY!*";
		t=setTimeout('startTime()',500);
	}
	else
	{
		document.getElementById('jscount').innerHTML="ORDERS PLACED<br /><span class='jsinfo'>BEFORE <?=date("ga",$deadline)?></span><br />SHIP THE SAME DAY!*";
	}
}
startTime();
</script>		
<?php }?>
<!--<div style="text-align:left">Lorem ipsum dolor sit amet</div>-->
<div style="margin:6px;font-size:14px;"><strong>Welcome to GMK Spares Shop</strong><br />In order to speed up delivery of small, specific items not regularly stocked by your local gun shop, GMK now offer a service direct to the public. Please search above (and in the left side menu) for choke tubes, pads, bead sights and other small accessories.</div>
<div>
<?php
$tpq="SELECT p.`".PFIELDNAME."` as title,MIN(`price`) as price,`salediscount`,`saletype`,`rank`,`cust_rev_id`,`fusionId`,p.`".PFIELDID."` as prod_id FROM ((((".PTABLE." as p JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` AND cf.`allowpurchase`='1') JOIN cart_variants as cv ON p.`".PFIELDID."`=cv.`pid`) JOIN cart_reviews as c ON p.`".PFIELDID."`=c.`item_id` AND `rank` > '3') JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND `itemType`='product') WHERE p.`".PFIELDSHOW."`='1' GROUP BY p.`".PFIELDID."` ORDER BY `rank` DESC,RAND() LIMIT 0,3";
//echo $tpq;
/*$toppicksq=ysql_query($tpq,CARTDB);
$tpnum=mysql_num_rows($toppicksq);*/
$toppicksq=$db1->query($tpq);
$tpnum=$toppicksq->rowCount();
if($tpnum>0){?><h2 class="headbold">A selection of our top rated products</h2><?php }
//while($toppicks=mysql_fetch_array($toppicksq))
while($toppicks=$toppicksq->fetch())
{
	$img=is_file($images_arr['product']['path']."/".$toppicks['prod_id'].".png")?$images_arr['product']['path']."/".$toppicks['prod_id'].".png":$root_to_cart."images/spacer.gif";
	?>
	<div style=" <?=$deviceType=="phone"?"width:100%;":"width:33%;float:left;"?>text-align:center;line-height:120%;"><span style="display:inline-block;padding:4px 10px">
		<a href="<?=MAINBASE?>/products/item/<?=$toppicks['fusionId']?>"><img src="<?=$img?>" alt="" style="width:247px;height:102px;" /></a><br />
		<a href="<?=MAINBASE?>/products/item/<?=$toppicks['fusionId']?>"><?=$toppicks['title']?></a><br />From <?php $price=$toppicks['price']-cart_getdiscount($toppicks['price'],$toppicks['salediscount'],$toppicks['saletype']);?>
		&#163;<?=cart_addvat($price).($toppicks['salediscount']>0?" <span style='text-decoration:line-through'>RRP: &#163;".cart_addvat($toppicks['price'],1)."</span>":"")?>
	</span></div>
	<?php
}
?><div class="clear"></div>
</div>
<?php
cart_prodlist("SELECT `brand`,`bid`,`fusionId`,f.`ownerId`,p.`".PFIELDID."` as prod_id,p.`".PFIELDDESC."` as description,p.`".PFIELDNAME."` as title,min(cv.`price`) as price,cf.`salediscount`,cf.`saletype`,f.`sorting` FROM (((((".PTABLE." as p JOIN gmkbrands as gb ON p.`bid`=gb.`id`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product') LEFT JOIN cart_kits as ck ON ck.`ownerId`=p.`".PFIELDID."`) LEFT JOIN nav_stock as n ON cv.`vskuvar`=n.`nav_skuvar` AND `nav_qty`>'0' WHERE cf.`allowpurchase`='1' GROUP BY p.`".PFIELDID."` ORDER BY p.`views` DESC","3","Most Viewed Products","",3);

$col=cleanCols(PTABLE,$get_arr['sort']);
if(strlen($col)<1){$col=cleanCols("gmkbrands",$get_arr['sort']);}
if(strlen($col)<1){$col=cleanCols("cart_fusion",$get_arr['sort']);}
if(strlen($col)<1){$col=cleanCols("cart_variants",$get_arr['sort']);}
if(strlen($col)<1){$col=cleanCols("fusion",$get_arr['sort']);}
if(strlen($col)<1){$col=cleanCols("cart_kits",$get_arr['sort']);}
if(strlen($col)<1){$col=cleanCols("nav_stock",$get_arr['sort']);}
$order=isset($get_arr['sort'])?$col:"p.`sorting`";
$ascdesc=isset($get_arr['ascdesc'])&&$get_arr['ascdesc']=="DESC"?"DESC":"ASC";
$qq="CREATE OR REPLACE VIEW prods AS SELECT `brand`,`bid`,`fusionId`,f.`ownerId`,p.`".PFIELDID."` as prod_id,p.`".PFIELDDESC."` as description,p.`".PFIELDNAME."` as title,min(cv.`price`) as price,cf.`salediscount`,cf.`saletype`,f.`sorting` FROM (((((".PTABLE." as p JOIN gmkbrands as gb ON p.`bid`=gb.`id`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` AND cf.`allowpurchase`='1') JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND p.`displayed`='1') LEFT JOIN cart_kits as ck ON ck.`ownerId`=p.`".PFIELDID."`) LEFT JOIN nav_stock as n ON cv.`vskuvar`=n.`nav_skuvar` AND `nav_qty`>'0' WHERE f.`ownerId`='0' GROUP BY p.`".PFIELDID."` ORDER BY f.`sorting`";
//ysql_query($qq,CARTDB);
$db1->query($qq);

cart_prodlist("SELECT `brand`,`bid`,`fusionId`,`ownerId`,`prod_id`,p.`title`,p.`description`,`price`,`salediscount`,`saletype`,AVG(`rank`) as avgrank,count(`rank`) as totalrevs FROM prods as p LEFT JOIN cart_reviews as cr ON p.`prod_id`=cr.`item_id` GROUP BY p.`prod_id` ORDER BY $order $ascdesc","","Suggested Products","lrgthumbs",2);?>


<?php include "cart_foot.php";?>