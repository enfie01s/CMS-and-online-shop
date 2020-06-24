<?php
$slider=1;//preg_match('/(?i)msie [2-9]/',$_SERVER['HTTP_USER_AGENT'])?0:$slider;

$num_to_th=array("first","second","third","fourth","fifth");
$steps=count($images);
$percents=100/$steps;
$slidelength=6;
$totalmovie=$slidelength*$steps;
//$deviceType="phone";
$mn=$deviceType=="phone"?"0":"164";
$sw=$deviceType=="phone"?"100%":"783px";
$sh=$deviceType=="phone"?"100%":"height:492px;";
$st=$deviceType=="phone"?20:49;
$sizes=getimagesize('content/home-slides/'.$images[0][0]);
$ratio=$sizes[0]<$sizes[1]?$sizes[0]/$sizes[1]:$sizes[1]/$sizes[0];
$regex="/<a href='([\d\w.?=&;%+]*)'>([\s\d\w]*)<\/a>/i";
if(date("U")<strtotime("30 July 2014"))//show until Monday
{

}
else
{
if($slider==1)
{
	?>
	<style type="text/css">	
	<!--
	.slideshow,.slideshow figure,.slideshow-attr,.slideshow img{
		width:<?=$sw?>;<?=$sh?>;
	}
	<?php foreach($images as $id => $arr){?>
	figure:nth-child(<?=$id+1?>) {
		-webkit-animation: xfade <?=$totalmovie?>s <?=$totalmovie-(($id+1)*$slidelength)?>s infinite;
		animation: xfade <?=$totalmovie?>s <?=$totalmovie-(($id+1)*$slidelength)?>s infinite;
	}
	<?php }?>
	@-webkit-keyframes xfade{
		0%{
			opacity: 1;
			z-index:100;
		}
		<?=number_format(($percents-$slidelength),2)?>% {
			opacity:1;
			z-index:100;
		}
		<?=number_format($percents,2)?>%{
			opacity: 0;
			z-index:0;
		}
		<?=(100-$slidelength)-$steps?>% {
			opacity:0;
			z-index:0;
		}
		100% {
			opacity:1;
			z-index:0;
		}
	}
	@keyframes xfade{
		0%{
			opacity: 1;
			z-index:100;
		}
		<?=number_format(($percents-$slidelength),2)?>% {
			opacity:1;
			z-index:100;
		}
		<?=number_format($percents,2)?>%{
			opacity: 0;
			z-index:0;
		}
		<?=(100-$slidelength)-$steps?>% {
			opacity:0;
			z-index:0;
		}
		100% {
			opacity:1;
			z-index:0;
		}
	}
	-->
	</style>
	<div class="slideshow" <?=$deviceType=="phone"?"style='width:".$sw.":".$sh."' id='slideshow'":""?>>
	<?php
	foreach($images as $id => $arr)
	{
		?>
		<figure>
			<a href="<?=$arr[3]?>"><img src="content/home-slides/<?=$arr[0]?>" alt="<?=$arr[2]?>" class="alignnone size-full" /></a>
			<figcaption><?=$arr[1]?></figcaption> 
		</figure>
		<?php 
	}
	
	?></div>
	<?php if($deviceType!="computer"){?>
	<script type="text/javascript">
		if(windowx < 783)
			document.getElementById('slideshow').style.height=(windowx*<?=$ratio?>)+"px";
	</script>
	<?php
	}
}
else
{
	$rand=!isset($_GET['rand'])?rand(0,count($images)-1):$_GET['rand'];
	$home=$images[$rand];
	?>
	<div style="position:relative;top:0;left:0;z-index:1">
	<img src="./content/home-slides/<?=$home[0]?>" alt="" />
	<div style="position:absolute;top:<?=$home[5]?>px;left:<?=$home[4]?>px;z-index:2"><a href="<?=$home[3]?>" title="<?=$home[2]?>"><img src="./content/images/main/button_learnmore.jpg" alt="<?=$home[2]?>" /></a></div>
	</div>
	<?php 
}
}
?>
<?php if(date("U")<strtotime("28 May 2015")){?>
<a href="http://www.benellispauto.co.uk" target="_blank">
<img src="./content/images/main/spacer.gif" style="width:247px;height:93px;background:url(./content/images/news/thumbs/51742.jpg) no-repeat -14px 0" alt="Benelli SP'Auto" />
</a>&nbsp;
<?php }?>
<?php if(date("U")<strtotime("7 July 2015")){?>
<a href="http://www.berettaworld.co.uk" target="_blank">
<img src="./content/images/banners/BerettaWorld2015.jpg" alt="Beretta World" />
</a>
<?php }?>
<?php if(date("U")<strtotime("30 May 2015")||date("U")<strtotime("15 July 2015")){?><br />&nbsp;<?php }?>

<div id="latestnews">
	<h1>NEWS &amp; EVENTS</h1>
	<ul>
	<?php
	//$query = ysql_query("SELECT * FROM `gmknews` WHERE `display`='T' ORDER BY `date` DESC LIMIT 3",$con1) or die(sql_error("Error"));
	//while($latest=mysql_fetch_assoc($query))
	$query = $db1->query("SELECT * FROM `gmknews` WHERE `display`='T' AND `date`<= ".date("U")." ORDER BY `date` DESC,id DESC LIMIT 3");
	while($latest= $query->fetch())
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
	
<?php if(0/*$slider==1*/){
	$bw=$deviceType=="phone"?"100%":"775px";
	$bh=$deviceType=="phone"?"":"height:176px;";
	$sizes=getimagesize('content/images/banners/sparesbanner.jpg');
	$ratio=$sizes[0]<$sizes[1]?$sizes[0]/$sizes[1]:$sizes[1]/$sizes[0];
	?><br /><br />
	<div class="slider-wrapper theme-bar" id="slider1bg" style="width:<?=$bw?>;<?=$bh?>">
		<div id="slider1" class="nivoSlider">		
			<a href="./shop"><img src="content/images/banners/sparesbanner.jpg" alt="GMK Spares Shop Now Open!" /></a>
		</div>           
	</div>
	<script type="text/javascript">
		document.getElementById('slider1bg').style.height=(windowx*<?=$ratio?>)+"px";
	</script>
<?php }?>