<?php
$perrow = 1;
$maxrows = $deviceType=="phone"?50:6;
$perpage = $perrow*$maxrows;
$maxpagelinks = 5;
function srch($arr,$find)
{
	foreach($arr as $key => $val){
		if($val['id']==$find){return $key;}
	}
}
if(isset($_GET['nid'])||(isset($subdirs[2])&&sizeof($newsarray)>0))
{
	/*$nid = isset($_GET['id']) ? $newsarray[$_GET['id']]['id'] : mysql_real_escape_string(intval($_GET['nid']));
	$query = ysql_query("SELECT * FROM gmknews WHERE `id`='$nid' ORDER BY `date` DESC",$con1) or die(sql_error("Error"));
	$result = mysql_fetch_assoc($query);
	$id=isset($_GET['nid'])?srch($newsarray,$_GET['nid']):mysql_real_escape_string(intval($_GET['id']));*/
	$nid = isset($_GET['nid'])?$_GET['nid']:$subdirs[2];
	$query = $db1->prepare("SELECT * FROM gmknews WHERE `id`=? ORDER BY `date` DESC");
	$query->execute(array($nid));
	$result = $query->fetch();
	$id=srch($newsarray,$nid);
	
	$olderlink = ($id+1 < sizeof($newsarray)) ? "<a href='./news/id/".($newsarray[$id+1]['id'])."'>&lt;&nbsp;Older</a>" : "<span style='color:#36495c'>&lt;&nbsp;Older</span>";
	$newerlink = ($id > 0) ? "<a href='./news/id/".($newsarray[$id-1]['id'])."'>Newer&nbsp;&gt;</a>" : "<span style='color:#36495c'>Newer&nbsp;&gt;</span>";
	?>
	<div><img src='./content/images/main/title_news.jpg' alt='' /></div>
	<div id='newsarticle_header'>
	<div id='newsarticle_title'><a href="./news/id/<?=$result['id']?>"><?=htmlspecialchars($result['title'],ENT_QUOTES,"ISO-8859-1")?></a></div>
	<div id='newsarticle_date'>Added: <?=date("jS M Y",$result['date'])?>&nbsp;&nbsp;<?=$olderlink?> | <?=$newerlink?></div>
	<div class='clear'></div>
	</div>
	<div id='newsarticle'>
	<?php
	list($nw,$nh)=getimagesize("./content/images/news/".$result['id'].".jpg");
	?>
	<div style='text-align: center; <?php if($_GET['id']!=10){?>float: right; margin-left: 20px; margin-bottom: 20px;<?php }?>'>
		<?php 
		$nimgs=glob('./content/images/news/'.$result['id'].'*.jpg');		
		$steps=count($nimgs);
		if($steps>1)
		{
			$percents=100/$steps;
			$slidelength=6;
			$totalmovie=$slidelength*$steps;
			list($sw,$sh)=getimagesize($nimgs[0]);
			?>
			<style type="text/css">	
			<!--
			.slideshow,.slideshow figure,.slideshow-attr,.slideshow img{
				width:<?=$sw?>px;height:<?=$sh?>px;
			}
			<?php foreach($nimgs as $id => $img){?>
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
			<div class="slideshow" style='margin:auto !important; <?=$deviceType=="phone"?"width:".$sw."px;height:".$sh."px":""?>' id='slideshow'>
			<?php
			foreach($nimgs as $id => $img)
			{
				?>
				<figure>
					<img src="<?=$img?>" alt="" class="alignnone size-full" />
					<figcaption></figcaption> 
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
			
			/*
			list($w,$h)=getimagesize($nimgs[0]);
			$w=$deviceType=="phone"?"100%":$w."px";
			$h=$deviceType=="phone"?"":"height:".$h."px;";
			?>
			<div class="slider-wrapper theme-bar" id="slider1bg" style=" <?=$h?>width:<?=$w?>;margin:auto;">
				<div id="slider" class="nivoSlider">
					<?php foreach($nimgs as $nimg){?>
					<img src="<?=$nimg?>" data-thumb="<?=$nimg?>" alt="" />
					<?php }?>
				</div>           
			</div>
			<script type="text/javascript" src="content/slider/scripts/jquery-1.9.0.min.js"></script>
			<script type="text/javascript" src="content/slider/scripts/jquery.nivo.slider.js"></script>
			<script type="text/javascript">
			$(window).load(function() {
					$('#slider').nivoSlider({
							effect: 'boxRain', // Specify sets like: 'fold,fade,sliceDown'
							animSpeed: 900, // Slide transition speed
							pauseTime: 4000, // How long each slide will show
							directionNav: false, // Next & Prev navigation
							controlNav: true, // 1,2,3... navigation
							randomStart: true, // Start on a random slide
							controlNavThumbs: false
					});
			});		/*sliceDown,sliceDownLeft,sliceUp,sliceUpLeft,sliceUpDown,sliceUpDownLeft,fold,fade,random,slideInRight,slideInLeft,boxRandom,boxRain,boxRainReverse,boxRainGrow,boxRainGrowReverse
			</script>
			<script type="text/javascript">
				document.getElementById('slider1bg').style.height=(windowx*<?=$ratio?>)+"px";
			</script>
			<?php 
			*/
			
			
			
		}
		else{
			$bw=$deviceType=="phone"?" width:100%":($nw>500?"width:500px;":"");
			?>
			<img src='./content/images/news/<?=$result['id']?>.jpg' alt='' style=' <?=$bw?>' />
			<?php 
		}?>
	</div>
	<?=stripslashes($result['content'])?>
	<p><?=stripslashes($result['rawhtml'])?></p>
	</div>
	<?php
	$flashfile="content/images/news/flv/".$result['id'].".flv";
	if(file_exists($flashfile))
	{
		?>
		<div id="dvd">
			<object id="Object1" type="application/x-shockwave-flash" data="content/flash/player.swf" width="425" height="320">
				<param name="movie" value="content/flash/player.swf" />
				<param name="allowFullScreen" value="true" />
				<param name="wmode" value="opaque" />
				<param name="allowScriptAccess" value="sameDomain" />
				<param name="quality" value="high" />
				<param name="menu" value="false" />
				<param name="autoplay" value="false" />
				<param name="autoload" value="false" />
				<param name="FlashVars" value="configxml=content/xml/playerconfig.xml&flv=<?=$mainbase."/".$flashfile?>&width=425&height=320" />
			</object>
		</div>
		<?php 
	}
	?>
	<?php
}
else
{
	?>
	<div style='float:left'><a href="newsfeed.php"><img src='./content/images/main/title_news.jpg' alt='' /></a></div>
	<div style='float:right'>
	<?=pagenav($totalnews,$perpage,"./news",$maxpagelinks)?>
	</div>
	<div style='clear:both'></div>
	<?php
	for($i=$pgstart;$i < sizeof($newsarray) && $i<$pgend;$i++)
	{
		//javascript:newsarticle(<?=(($i*106)+210),210,"newsrow<?=$i","top")
		$bg=$deviceType=="phone"?"./content/images/news/".$newsarray[$i]['id'].".jpg":"./content/images/main/newsBG.jpg";
		?>
		<div class='newsrow' style='background-image: url("<?=$bg?>");<?=$deviceType=="phone"?"":"top:".((($i-$pgstart)*106)+210)."px"?>;' id='newsrow<?=($i-$pgstart)?>'>
		<a href='./news/id/<?=$newsarray[$i]['id']?>' style='' class='mainlink'>&nbsp;</a>
		<div style=' <?php if($deviceType!="phone"){?>float:left;<?php }?>z-index:1'>
		<div class='title'><a href='./news/id/<?=$newsarray[$i]['id']?>'><?=trimtext(htmlspecialchars($newsarray[$i]['title'],ENT_QUOTES,"ISO-8859-1"),"55","")?><br /><small><?=date("jS M Y",$newsarray[$i]['date'])?></small></a></div>
		<div class='intro'><a href='./news/id/<?=$newsarray[$i]['id']?>'><?=str_replace(array("Â","£","&pound;"),array("","&#163;","&#163;"),htmlspecialchars($newsarray[$i]['intro'],ENT_QUOTES,"ISO-8859-1"))?></a></div>
		</div>
		<?php if($deviceType!="phone"){?><div style=' float:right;z-index:1'><a href='./news/id/<?=$newsarray[$i]['id']?>'><img src='./content/images/news/<?=$deviceType=="phone"?"":"thumbs/"?><?=$newsarray[$i]['id']?>.jpg' alt='<?=findreplace($newsarray[$i]['title'],"display")?> thumbnail' <?php if($deviceType!="phone"){?>width='275' height='93'<?php }?> /></a></div><?php }?>
		<div style='clear:both'></div>
		</div>
		<?php
	}
}
?>
