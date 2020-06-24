<?php
### PAGE NO LONGER USED ###
$depth=count($subdirs);
$getid=isset($_GET['id']);
if(isset($subdirs[1])&&$subdirs[1]=="item"&&isset($subdirs[2])&&strlen(isset($subdirs[2]))>0)
{$getid=$subdirs[2];}
elseif($depth>0)
{$getid=$subdirs[$depth-1];}
if($getid>0)
{	
	$stat=isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0?"":" AND displayed=1";
	/*$id = trim(mysql_real_escape_string(intval($_GET['id'])));
	$query_string = "SELECT *,p.`description` as description FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `pid`='$id'".$stat." ORDER BY `order` ASC";
	$query = ysql_query($query_string,$con1) or die(sql_error("Error",$query_string));
	if(mysql_num_rows($query)>0)*/
	$id = trim(intval($getid));
	$query_string = "SELECT *,p.`description` as description FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `pid`=?".$stat." ORDER BY `order` ASC";
	$query = $db1->prepare($query_string);
	$query->execute(array($id));
	if($query->rowCount()>0)
	{
		?><noscript><?php
		if(!isset($_SESSION['view'])&&$islocal!=1&&!$inhouse!=1){
		//ysql_query("UPDATE gmk_products SET `views`=`views`+1 WHERE `pid`='".$id."'",$con1);
		$q=$db1->prepare("UPDATE gmk_products SET `views`=`views`+1 WHERE `pid`=?");
		$q->execute(array($id));
		$_SESSION['view']=1;
		}
		?></noscript><?php
		//$result = mysql_fetch_assoc($query);
		$result = $query->fetchAll();
		if(strlen($stat)<1&&$result[0]['displayed']==0){?><div class="notice">Warning: This product status is set to off, switch on to make it viewable by the public!</div><?php }
		$find = array("vname","price","kg");
		$replace = array("Description","RRP Inc. VAT","weight");
		
		$scopecols1 = array("lens"=>"Lens Coating","windage"=>"Windage &amp; Elevation adjustment dials","reticule"=>"Reticule Options|Listed reticule styles are available on select models","eyepiece_focus"=>"eyepiece focus design","parallax"=>"user parallax adjustment control","magnification"=>"magnification system","maintube_dia"=>"Maintube Diameter and Material","power_selector"=>"power selector style","waterproof"=>"waterproof/fog proof technology","ballistics"=>"Ballistics Aiming System","shop_serviceable"=>"Custom Shop Serviceable","guarantee"=>"Leupold Full Lifetime Guarantee");
		//$rangecols1
		
		$prodpath="./".$images_arr['product']['path'];
		$exists=file_exists($prodpath.$result[0]['pid'].".png")?1:0;
		$timage=$exists==1?$prodpath.$result[0]['pid'].".png":$prodpath."unavail.png";
		$big=is_file($prodpath."big/".$result[0]['pid'].".jpg")?$result[0]['pid'].".jpg":0;
		if($big==0)
		{
			$stageW=400;
			$stageH=400;
		}
		else
		{
			list($w,$h)=getimagesize($prodpath."big/".$result[0][PFIELDID].".jpg");
			$stageW=$w/2;
			$stageH=$h/2;
		}
		$stageW=$deviceType=="phone"?"96%":$stageW."px";
		$stageH=$deviceType=="phone"?"":"height:".$stageH."px;";
		if($id!=332){
		?>
		<div id="prod_detail_brand"><?=$result[0]['brand']?></div>
		<div id="prod_detail_model"><?=str_replace(array("&lt;","&gt;","&amp;nbsp;"),array("<",">","&nbsp;"),htmlentities($result[0]['prod_title'],ENT_QUOTES,"UTF-8"))?></div>
		<?php 
		}else{
			?><img src="./content/images/banners/486Parallelo-banner.jpg" alt="<?=$result[0]['brand']." - ".str_replace(array("&lt;","&gt;","&amp;nbsp;"),array("<",">","&nbsp;"),htmlentities($result[0]['prod_title'],ENT_QUOTES,"UTF-8"))?>" /><br /><br /><?php
		}
		if($big==0){		
			$linkpre=$deviceType=="computer"?"<a href='?p=image&amp;id=".$result[0]['pid']."'>":"";
			$linksuff=$deviceType=="computer"?"</a>":"";
			?>	
		<div id="prod_detail_model_img">
		<div style="position:absolute;top:8px;left:8px;z-index:3;"><?=$linkpre?><img src='./content/images/logos/<?=strtolower($result[0]['brand'])?><?php if($result[0]['premium'] == "y"){?>_premium<?php }?>.gif' alt='<?=ucwords($result[0]['brand'])?>' /><?=$linksuff?></div>
		<?=$linkpre?><img src='<?=$timage?>' alt='' border="0" class="gunimg" /><?=$linksuff?><?php if($deviveType=="computer"){?><br /><a href='?p=image&amp;id=<?=$result[0]['pid']?>' class="enlarge"><img src='./content/images/main/enlarge.png' alt='+ enlarge' /></a><?php }?>
		</div>
		<?php }?>
	
		<?php if($big!=0){?>
		<div style="width:<?=$stageW?>;<?=$stageH?>padding:7px 7px 15px;background:#8b8b8b;"> 
			<img src="<?=$prodpath?>big/<?=$big?>" style="width:100%;position:relative;top:0;left:0;" alt="" id="img_02" />
			<div><?=$deviceType=="phone"?"Click around":"Move your mouse over"?> the image to zoom</div>
		</div>
		<?php }?>
		<div id="prod_detail_description"><?=str_replace("\r\n","",$result[0]['description'])?></div>
		<?php
		$flashfile=$prodpath."flv/".$id.".flv";
		if(is_file($flashfile)&&$deviceType=="computer")
		{
			?>
			<div id="dvd">
				<?php if($id=='188'){?><div style="float:left"><h1>Vinci Assembly Video</h1><?php }?>
				<object id="Object1" type="application/x-shockwave-flash" data="content/flash/player.swf" width="320" height="240">
					<param name="movie" value="content/flash/player.swf" />
					<param name="allowFullScreen" value="true" />
					<param name="wmode" value="opaque" />
					<param name="allowScriptAccess" value="sameDomain" />
					<param name="quality" value="high" />
					<param name="menu" value="true" />
					<param name="autoplay" value="false" />
					<param name="autoload" value="false" />
					<param name="FlashVars" value="configxml=content/xml/playerconfig.xml&flv=<?=$mainbase."/".$flashfile?>&width=320&height=240" />
				</object>
				<?php if($id=='188'){?>
				</div>
				<div style="float:left;margin-left:10px;">
				<h1>Vinci Challenge Video</h1>
				<object id="Object2" type="application/x-shockwave-flash" data="content/flash/player.swf" width="320" height="240">
					<param name="movie" value="content/flash/player.swf" />
					<param name="allowFullScreen" value="true" />
					<param name="wmode" value="opaque" />
					<param name="allowScriptAccess" value="sameDomain" />
					<param name="quality" value="high" />
					<param name="menu" value="true" />
					<param name="autoplay" value="false" />
					<param name="autoload" value="false" />
					<param name="FlashVars" value="configxml=content/xml/playerconfig.xml&flv=<?=$mainbase?>/content/flash/movies/Vinci_tricks.flv&width=320&height=240" />
				</object>
				</div>
				<div style="clear:both;"></div>
				<?php }?>
			</div>
			<?php 
		}
		$extrapics=glob("./content/images/products/".$id."/*.jpg");
		$expicscount=count($extrapics);
		if($expicscount>0)
		{
			$ex=0;
			?><h2>Extra Images</h2><dfn>(Click to enlarge)</dfn><table><tr><?php
			foreach($extrapics as $extrapic){
			$ex++;
			$rowdivide=$ex%5;
			$totalover=$expicscount%5;
			$cellstoadd=5-$totalover;
			?><td style="text-align:center;background:#FFF"><span class="lbox"><a href="<?=$extrapic?>" style="display:inline-block" title="Engraving: <?=str_replace(".jpg","",basename($extrapic))?>"><img src="<?=$extrapic?>" alt="Engraving: <?=str_replace(".jpg","",basename($extrapic))?>" style="height:100px;vertical-align:bottom;" /></a></span></td><?php
				if($cellstoadd<5&&$ex>=$expicscount){for($x=1;$x<=$cellstoadd;$x++){?><td></td><?php }}
				if($rowdivide==0||($ex>=$expicscount&&$x-1==$cellstoadd)){?></tr><?php if($ex<$expicscount){?><tr><?php }}			
			}
			?></table><?php
		}
		$fieldcols=$deviceType=="phone"?array():$fields[$result[0]['ctype']];
		$phonecols=$deviceType=="phone"?array_diff($fields[$result[0]['ctype']],$fieldcols):array();
		if($deviceType=="phone"){$fieldcols['variations']="";}
		
		?>
		<table id="product_options">
			<tr>
				<?php 
				
				$width = floor(100/count($fieldcols));
				foreach($fieldcols as $name => $col){?>
				<td class="table_header" id="<?=str_replace(" ","_",$name)?>_header" style="width:<?=$width?>%;<?php if($col == "price"){?>text-align:right<?php }?>"><?=ucwords($name)?></td>
				<?php }?>
			</tr>
		<?php
		foreach($result as $row => $optsresult)
		{
			$class = (!isset($class) || $class == 1) ? 0 : 1;
			?>
			<tr>
				<?php foreach($fieldcols as $name => $col){?>
				<td class="table_row<?=$class?>" style="width:<?=$width?>%;<?php if($col == "price"){?>text-align:right;<?php }?><?php if($col == "vname" || $col == "field1"){?>white-space:nowrap;<?php }?>">
				<?php
				if($name=="variations")
				{
					$f=0;
					$f1=count($phonecols);
					foreach($phonecols as $name1 => $col1)
					{
						if($f>1&&$f<$f1){?> | <?php }
						$f++;
						$pre1 = ($col1 == "price") ? "&pound;" : "";
						$suff1 = ($col1 == "kg") ? " kg" : "";	
						$resss=$col1 == "price" && $optsresult[$col1] <= 0?"TBA":$optsresult[$col1];			
						?>
						<?=($col1=="vname"?"<strong style='text-decoration:underline'>":"").ucwords($name1).": ".(strlen($optsresult[$col1])>0?$pre1.htmlspecialchars(str_replace("-v-NONE","",$resss),ENT_QUOTES,"ISO-8859-1").$suff1:"").($col1=="vname"?"</strong><br />":"")?>
						<?php 
					}
				}
				else
				{				
					$pre = ($col == "price") ? "&pound;" : "";
					$suff = ($col == "kg") ? " kg" : "";		
					$resss=$col == "price" && $optsresult[$col] <= 0?"TBA":$optsresult[$col];		
					?>
					<?=strlen($optsresult[$col])>0?$pre.htmlspecialchars(str_replace("-v-NONE","",$resss),ENT_QUOTES,"ISO-8859-1").$suff:"--"?>
					<?php 
				}?>
				</td>
				<?php }?>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
		include "abbreviations.php";abbreviationsKey(strtolower($result[0]['brand']));	
	}
	else
	{
		?><div id="errorbox"><p>Error</p>Sorry, this product is currently unavailable.</div><?php
	}
}
?>