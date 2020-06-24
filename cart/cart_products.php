<?php 
if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security
/* ADD TO BASKET */
if(isset($post_arr['identifier'])&&$post_arr['identifier']=="add_to_cart"&&isset($_POST['prod_id']))
{
	$ids_in_cart=whats_in_cart(1);
	$skuvar_count=whats_in_cart(0);
	$allowed=array();
	if(strlen($post_arr['allowlist'])>0){$allowed=explode(",",$post_arr['allowlist']);}
	$allowedmatches=strlen($post_arr['allowlist'])<1?1:count(array_intersect($allowed,$ids_in_cart));
	
	if($allowedmatches>0){		
		add_to_cart($post_arr['skuvariant'],$post_arr['prod_id'],$post_arr['quantity'],$post_arr['price'],$post_arr['ispack'],$post_arr['exclude_discount'],$allowed,$post_arr['title'],$post_arr['returnpage']);
	}
	else
	{
		$_SESSION['error'][$post_arr['prod_id']]="Product Unavailable";
	}
}
/* END ADD TO BASKET */
include "cart_head.php";

$prodmod=array_search("cart_prods",$modules_pages);

$parents_num=0;
//$the_array set in index.php
/* PRODUCT LIST */

if(isset($the_array)&&is_array($the_array))
{
	?><h2 id="pagetitle"><?=ucwords($subdirs[$depth-1])?></h2><?php
	### Sub Categories ###
	
	
	### Products ###
	$ctype=$info['ctype'];
	$hasprods=0;
	foreach($the_array as $inx => $t)
	{
		if(!is_array($t)){$hasprods=1;}
	}
	if($hasprods)//products
	{
		//print_r($fstuff);
		if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?><div style="margin-bottom:15px;"><a class="acpbutton" href="./admin/index.php?p=products&owner=<?=urlencode($info['id'])?>" target="_blank">Sort Items</a> <a class="acpbutton" href="./admin/index.php?p=products&amp;showing=catform&amp;cid=<?=urlencode($info['id'])?>" target="_blank">Edit Cat</a> <a class="acpbutton" href="./admin/index.php?p=products&amp;showing=prodform&amp;owner=<?=urlencode($info['id'])?>" target="_blank">Add Item</a></div><?php }
		$filters="";
		if(isset($tofilter)&&count($tofilter)>0)
		{
			foreach($tofilter as $f_title => $thearray)
			{
				if(!is_array($tofilter[$f_title])){$tofilter[$f_title]=array();}
				$mmfound=0;
				//$prod_f_title=$fstuff[$f_title];
				//if(!in_array($prod_f_title,$tofilter[$f_title])&&strlen($prod_f_title)>0){$tofilter[$f_title][]=$prod_f_title;}
				$tofilter[$f_title]=$fstuff[$f_title];
				sort($tofilter[$f_title],SORT_NUMERIC);
			}
			//print_r($tofilter);
			$filtermatch=array_intersect_key($get_arr,$tofilter);
			$bindfilters=array();
		}
		if(count($tofilter)>0){?>
		<div id="quicksearchbar">QUICK SEARCH</div>
		<div style="float:left" id="quicksearchmain">
			<div id="sorting"> 
				<form action=".<?=$depth>0?$cururl.(isset($get_page)&&!isset($_GET['p'])?"/page".$get_page:""):"./products"?>" method="post" style="display:inline;vertical-align:middle">
				<input type="hidden" name="p" value="<?=$page?>" />
				<input type="hidden" name="cat" value="<?=$info['id']?>" />
				
				<?php if(0){?>Sort By: 
				<span id="sortbox">
				<select name="sort" class="formselect" style="width:auto" onchange="this.form.submit()">
					<option value="p.sorting"<?php if((isset($_POST['sort'])&&$_POST['sort']=="p.sorting")||!isset($_POST['sort'])){?> selected="selected"<?php }?>>Default</option>
					<option value="p.title"<?php if(isset($_POST['sort'])&&$_POST['sort']=="p.title"){?> selected="selected"<?php }?>>Name</option>
					<option value="avgrank"<?php if(isset($_POST['sort'])&&$_POST['sort']=="avgrank"){?> selected="selected"<?php }?>>Rating</option>
					<option value="p.price"<?php if(isset($_POST['sort'])&&$_POST['sort']=="p.price"){?> selected="selected"<?php }?>>Price</option>
				</select>
				<select name="ascdesc" class="formselect" style="width:auto">
					<option value="ASC"<?php if((isset($_POST['ascdesc'])&&$_POST['ascdesc']=="ASC")||!isset($_POST['ascdesc'])){?> selected="selected"<?php }?>>Low - High</option>
					<option value="DESC"<?php if(isset($_POST['ascdesc'])&&$_POST['ascdesc']=="DESC"){?> selected="selected"<?php }?>>High - Low</option>
				</select>
				</span>
				<?php }
				if(count($tofilter)>0){?>
					Filter By: 
					<span class="fieldgroup">
					<?php
					foreach($tofilter as $ct_field => $options)
					{
						$fname=array_search($ct_field,$fields[$info['ctype']]);
						?>
						<select name="<?=$ct_field?>" class="formselect" style="width:auto" onchange="this.form.submit()">
						<option value="">All <?=$fname?>s</option>
						<?php
						if($info['id']==22&&$ct_field=="field3")
						{
							?>
							<option value="flush" <?php if(urldecode($_POST[$ct_field])=="flush"){?>selected="selected"<?php }?>>Flush</option>
							<option value="extended" <?php if(urldecode($_POST[$ct_field])=="extended"){?>selected="selected"<?php }?>>Extended</option>
							<?php
						}
						else
						{
							foreach($options as $opt)
							{
								$opt=strlen($opt)<1?"Unspecified":$opt;
								?><option value="<?=urlencode($opt)?>" <?php if(urldecode($_POST[$ct_field])==$opt){?>selected="selected"<?php }?>><?=$opt?></option><?php
							}
						}
						?>
						</select>	
						<?php
					}
					?></span><?php
				}
				if(count($tofilter)>0){?><input type="submit" value="Go" class="button" /><?php }?>
				</form>
			</div>
		</div>
		<div class="clear"></div><?php }?>
		<table class="product">
		<tr>
		<?php
		$row=0;
		$cols=3;
		$prods_count=count($the_array);
		
		foreach($the_array as $inx => $t)
		{
			$thisinfo=$info[$t];
			$field1s=explode("##",$thisinfo['field1']);
			$field2s=explode("##",$thisinfo['field2']);
			$field3s=explode("##",$thisinfo['field3']);//array(0,20mm)
			$field4s=explode("##",$thisinfo['field4']);
			$fieldkgs=explode("##",$thisinfo['kg']);
			$flush=0;
			$extended=0;
			foreach($field3s as $ssss => $fext){if($fext==0||strlen(trim($fext))<1){$flush=1;}if($fext>0){$extended=1;}}
			if(
			!(isset($_POST['field1'])&&!in_array(urldecode($_POST['field1']),$field1s)&&strlen($_POST['field1'])>0)&&
			!(isset($_POST['field2'])&&!in_array(urldecode($_POST['field2']),$field2s)&&strlen($_POST['field2'])>0)&&
			!(isset($_POST['field3'])&&(($_POST['field3']=="extended"&&$extended==0)||($_POST['field3']=="flush"&&$flush==0)||($_POST['field3']!="extended"&&$_POST['field3']!="flush"&&!in_array(urldecode($_POST['field3']),$field3s)))&&strlen($_POST['field3'])>0)&&
			!(isset($_POST['field4'])&&!in_array(urldecode($_POST['field4']),$field4s)&&strlen($_POST['field4'])>0)&&
			!(isset($_POST['kg'])&&!in_array(urldecode($_POST['kg']),$fieldkgs)&&strlen($_POST['kg'])>0)
			)
			{
				$row++;
				$width=$deviceType=="phone"?"100":(100/$cols);
				$imgsrc="src='".$images_arr['product']['path'].$thisinfo['id'].".png"."'";
				$price=$thisinfo['cprice']-cart_getdiscount($thisinfo['cprice'],$thisinfo['salediscount'],$thisinfo['saletype']);
				?>
				<td class="pimg" style="border:0 !important;width:<?=number_format($width,1)?>%;">
				<div style="position:relative;top:0px;left:0px;text-align:center">
				<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
				<a class="acpbutton" style="position:absolute;top:0;right:0" href="./admin/index.php?p=products&amp;showing=prodform&amp;pid=<?=urlencode($thisinfo['id'])?>&amp;owner=<?=urlencode($info['id'])?>" target="_blank">Edit</a>
				<?php }?>
					<a href=".<?=$cururl."/".$t?>" title="<?=cart_trimtext(strip_tags($prod['description']),250)?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$thisinfo['id']?>)'<?php }?>><img <?=$imgsrc?> alt="" /></a><br />
					<a href=".<?=$cururl."/".$t?>" style="font-weight:bold;font-size:12px" title="<?=cart_trimtext(strip_tags($thisinfo['proddesc']),250)?>"<?php if($showit==1){?> onclick='cartUpdateViews(<?=$thisinfo['id']?>)'<?php }?>><?=ucwords($thisinfo['itemtitle'])?></a>
				<br />
					<span style="font-weight:bold;font-size:12px;color:#A9AEB7">
						From &#163;<?=cart_addvat($price)?><?php 
						if($thisinfo['salediscount']!=0){?> <span style="text-decoration:line-through">RRP: &#163;<?=cart_addvat($thisinfo['cprice'],1)?></span><?php }?>
					</span><br />
					<span style="white-space:nowrap;"><?php if(isset($thisinfo['avgrank'])&&$thisinfo['avgrank']>0){?>Customer Reviews:</span> <?=cart_stars($thisinfo['avgrank'],"small")?><?php }else{?>&nbsp;<?php }?>
					<?php if($thisinfo['salediscount']!=0){?><div class="salebadge"><img src="<?=$root_to_cart?>/images/sale_icon.png" alt="On Sale!" /></div><?php }?>
					</div>
				</td>
				<?=($row%$cols==0||$deviceType=="phone")&&$row<$prods_count?"</tr><tr>":"";
			}
		}
		?></tr></table><p>&nbsp;</p><?php
	}
	
	/*
	if(isset($get_arr['cat']))
	{
		$col=cleanCols(PTABLE,$get_arr['sort']);
		if(strlen($col)<1){$col=cleanCols("gmkbrands",$get_arr['sort']);}
		if(strlen($col)<1){$col=cleanCols("cart_fusion",$get_arr['sort']);}
		if(strlen($col)<1){$col=cleanCols("cart_variants",$get_arr['sort']);}
		if(strlen($col)<1){$col=cleanCols("fusion",$get_arr['sort']);}
		if(strlen($col)<1){$col=cleanCols("cart_kits",$get_arr['sort']);}
		if(strlen($col)<1){$col=cleanCols("nav_stock",$get_arr['sort']);}
		$order=isset($get_arr['sort'])?$col:"p.sorting";	
		$ascdesc=isset($get_arr['ascdesc'])&&$get_arr['ascdesc']=="DESC"?"DESC":"ASC";
		
		$filters="";
		$filtermatch=array_intersect_key($get_arr,$tofilter);
		$bindfilters=array();
		if(count($filtermatch)>0)
		{
			foreach($filtermatch as $key => $value)
			{
				if(strlen($value)>0){
					if(strlen($filters)<1){$filters.=" WHERE ";}
					else{$filters.=" AND ";}
					if($get_arr['cat']==22&&$key=="field3")
					{
						$v=$value=="extended"?"`>'1'":"`<'1'";
						$filters.="`".$key.$v;
					}
					else
					{
						$filters.="`".$key."`=?";
						$bindfilters[]=urldecode($value);
					}
				}
			}
		}
		?><h2 id="pagetitle"><?=ucwords($the_array['title'])?></h2><?php
		if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?><div style="margin-bottom:15px;"><a class="acpbutton" href="./admin/index.php?p=products&owner=<?=urlencode($get_arr['cat'])?>" target="_blank">Sort Items</a> <a class="acpbutton" href="./admin/index.php?p=products&amp;showing=catform&amp;cid=<?=urlencode($get_arr['cat'])?>" target="_blank">Edit Cat</a> <a class="acpbutton" href="./admin/index.php?p=products&amp;showing=prodform&amp;owner=<?=urlencode($get_arr['cat'])?>" target="_blank">Add Item</a></div><?php }
	
		$qq="CREATE OR REPLACE VIEW prods AS SELECT `brand`,`bid`,`fusionId`,f.`ownerId`,p.`".PFIELDID."` as prod_id,p.`".PFIELDDESC."` as description,p.`".PFIELDNAME."` as title,min(cv.`price`) as price,cf.`salediscount`,cf.`saletype`,f.`sorting` as sorting,c.`ctype`".$filterfields." FROM ((((((".PTABLE." as p JOIN gmkbrands as gb ON p.`bid`=gb.`id`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` AND cf.`allowpurchase`='1') JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product') LEFT JOIN cart_kits as ck ON ck.`ownerId`=p.`".PFIELDID."`) JOIN nav_stock as n ON cv.`vskuvar`=n.`nav_skuvar` AND `nav_qty`>'0') JOIN ".CTABLE." as c ON c.`cid`=".(int)$get_arr['cat']." WHERE f.`ownerId`=".(int)$get_arr['cat']." GROUP BY p.`".PFIELDID."` ORDER BY f.`sorting`";
	
		$qR=$db1->query($qq);
		if(strlen($the_array['intro_text'])>4){echo stripslashes(str_replace(array("\\r\\n","\\"),array("",""),$the_array['intro_text']));}
		
		cart_catlist("SELECT c.`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM (".CTABLE." as c JOIN cart_catopts as o ON c.`".CFIELDID."`=o.`cat_id` AND o.`showincart`='1') JOIN fusion as f ON c.`".CFIELDID."`=f.`itemId` AND `ownerType`='category' AND `itemType`='category' WHERE `ownerId`=?",array($get_arr['cat']));
		
			$forfilters=$db1->query("SELECT `brand`,`bid`,`fusionId`,`ownerId`,`prod_id`,p.`title`,p.`description`,`price`,`salediscount`,`saletype`,AVG(`rank`) as avgrank,count(`rank`) as totalrevs,`ctype`".$filterfields." FROM prods as p LEFT JOIN cart_reviews as cr ON p.`prod_id`=cr.`item_id` GROUP BY p.`prod_id` ORDER BY $order $ascdesc");
		$totalprods=$forfilters->rowCount();
		while($prod=$forfilters->fetch(PDO::FETCH_ASSOC))
		{
			foreach($tofilter as $f_title => $the_array)
			{
				if(!is_array($tofilter[$f_title])){$tofilter[$f_title]=array();}
				$mmfound=0;
				$prod_f_title=$prod[$f_title];
				if(!in_array($prod_f_title,$tofilter[$f_title])&&strlen($prod_f_title)>0){$tofilter[$f_title][]=$prod_f_title;}
				sort($tofilter[$f_title],SORT_NUMERIC);
			}
		}
		cart_prodlist("SELECT `brand`,`bid`,`fusionId`,`ownerId`,`prod_id`,p.`title`,p.`description`,`price`,`salediscount`,`saletype`,AVG(`rank`) as avgrank,count(`rank`) as totalrevs,`ctype`,`sorting`".$filterfields." FROM prods as p LEFT JOIN cart_reviews as cr ON p.`prod_id`=cr.`item_id`".$filters." GROUP BY p.`prod_id` ORDER BY $order $ascdesc","","Products&nbsp;","",3,$bindfilters);
	}
	*/
}
/* /PRODUCT LIST */

/* INDIVIDUAL PRODUCT */
else if(isset($info)&&is_array($info))
{ 
	?><noscript><?php
	if(!isset($_SESSION['view'])&&$islocal!=1&&!$inhouse!=1){
	//ysql_query("UPDATE gmk_products SET `views`=`views`+1 WHERE `pid`='".$the_array['prod_id']."'",CARTDB);
	$q=$db1->prepare("UPDATE gmk_products SET `views`=`views`+1 WHERE `pid`=?");
	$q->execute(array($info['id']));
	$_SESSION['view']=1;
	}
	?></noscript><?php
	$imagesdir=$cart_images_arr['variants']['path'].$info['id']."/";
	$prodimgs=glob($imagesdir."*-t-prod.jpg");
	?><h2 id="pagetitle"><?=ucwords($info['itemtitle'])?></h2>
	<div id="errorbox" style=" <?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div><?php
	//debug($allowlist);
	//print_r($allowlist);
	if($info['allowpurchase']==1||(count(array_intersect($allowlist,$cart_ids))>0&&$info['allowoffer']==1))
	{
		$prodpath="./".$images_arr['product']['path'];
	$exists=file_exists($prodpath.$info['id'].".png")?1:0;
	$timage=$exists==1?$prodpath.$info['id'].".png":$prodpath."unavail.png";
	$big=file_exists($prodpath."big/".$info['bigimage'])?$info['bigimage']:0;
	$stageW=400;
	$stageH=200;
	?>
		
	<div id="mainimg">
	<?php if($exists&&$big==0){?>	
	<div id="prod_detail_model_img">
	<img src='<?=$timage?>' alt='' border="0" class="gunimg" />
	</div>
	<?php }?>

	<?php if($exists&&$big!=0){?>
	<div style=" <?=$deviceType=="phone"?"width:100%;":"width:".$stageW."px;height:".$stageH."px;"?>padding:7px 7px 15px;background:#8b8b8b;">
		<img src="<?=$prodpath?>big/<?=$big?>" style=" <?=$deviceType=="phone"?"width:100%;":"width:".$stageW."px;height:".$stageH."px;"?>position:relative;top:0;left:0;" alt="" id="img_02" />
		<?=$deviceType=="phone"?"":"<div>Click the image to zoom</div>"?>
	</div>
	<?php }?>
		
		
		<?php 
		if(count($prodimgs)>0)
		{
			?>
			<p>(
			<?php if($lrgsrc!="notfound"){?>Click above to enlarge image, <?php }?>hover below for colour variations
			)</p>
			<p class="hidefromprint"><?php 
			foreach($prodimgs as $prodimg)
			{
				$img=basename($prodimg);
				$expl=explode("-t-",$img);
				$swatch=$imagesdir.$expl[0]."-t-main.jpg";
				if(file_exists($swatch))
				{
					?><a href="" onmouseover="javascript:swapimage('thumbnail','<?=$prodimg?>')" onmouseout="javascript:returnimage('thumbnail')" title="<?=ucwords($expl[0])?>" style="vertical-align:text-bottom;"><img src="<?=$cart_images_arr['variants']['path']?>swatch-mask.png" style="vertical-align:text-bottom;background: url('<?=$swatch?>')" alt="<?=ucwords($expl[0])?>" /></a><?php
				}
			}
			?>
			</p>
			<?php 
		}
		?>		
		</div>		
		<?php 
			/*$kitq=ysql_query("SELECT `qty`,p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id,`itemId` FROM cart_kits as k JOIN ".PTABLE." as p ON k.`itemId`=p.`".PFIELDID."` WHERE `ownerId`='".$the_array['prod_id']."'",CARTDB);
			$kitsfound=mysql_num_rows($kitq);*/
			$kitq=$db1->prepare("SELECT `qty`,p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id,`itemId` FROM cart_kits as k JOIN ".PTABLE." as p ON k.`itemId`=p.`".PFIELDID."` WHERE `ownerId`=?");
			$kitq->execute(array($info['id']));
			$kitsfound=$kitq->rowCount();
			$price=$info['cprice']-cart_getdiscount($info['cprice'],$info['salediscount'],$info['saletype']);
		?>
		<div id="choices">
			<form id="productoptions" name="productoptions" method="post" action=".<?=$cururl?>#basketbutton">
			<input type="hidden" name="returnpage" value=".<?=$cururl?>" /><!-- used for link in basket and for form/login return -->
			<input type="hidden" name="prod_id" value="<?=$info['id']?>" />
			<input type="hidden" name="sku" value="<?=$info['sku']?>" />
			<input type="hidden" name="ispack" value="<?=$kitsfound>0?1:0?>" />
			<input type="hidden" name="identifier" value="add_to_cart" />
			<input type="hidden" name="price" value="<?=$price?>" />
			<input type="hidden" name="allowlist" value="<?=implode(",",$allowlist)?>" />
			<input type="hidden" name="title" value="<?=$thisinfo['itemtitle']?>" />
			<input type="hidden" name="exclude_discount" value="<?=$info['excludediscount']?>" />
			<?php if($info['salediscount']!=0){?><p class="highlight">On Sale!</p><?php }
			?>
			<p class="price">Price from: &#163;<?=cart_addvat($price)?></p>
			<?php if($info['salediscount']!=0){?><p><span style="text-decoration:line-through">RRP from: &#163;<?=cart_addvat($info['cprice'],1)?></span></p><?php }
			
			if($info['kit']==1)//show each item in package 
			{
				if($kitsfound>0)
				{
					?>
					<h3>Items in Package</h3>
					<?php
					/*while($kit=mysql_fetch_assoc($kitq))
					{
						$fusionidq=ysql_query("SELECT `fusionId` FROM fusion as f JOIN ".PTABLE." as p ON p.`".PFIELDID."`=f.`itemId` WHERE `itemId`='$kit[itemId]' AND `itemType`='product'",CARTDB);
						list($fusionId)=mysql_fetch_row($fusionidq);*/
					while($kit=$kitq->fetch())
					{
						$fusionidq=$db1->prepare("SELECT `fusionId` FROM fusion as f JOIN ".PTABLE." as p ON p.`".PFIELDID."`=f.`itemId` WHERE `itemId`=? AND `itemType`='product'");
						$fusionidq->execute(array($kit['itemId']));
						list($fusionId)=$fusionidq->fetch();
						?>
						<input type="hidden" name="item_qty" value="<?=$kit['qty']?>" />
						<a href='<?=MAINBASE?>/shop/item/<?=$fusionId?>'><?=$kit['title']?></a> (<?=$kit['qty']?>)<br />
						<?php
						cart_colourchooser($kit['prod_id'],$kit['qty'],$kitsfound,0);
						?><br /><?php
					}
				}
			}
			else//don't show each item
			{
				$kitskus=array();
				if($kitsfound>1)//more than 1, auto pick colours
				{
					//while($hkits=mysql_fetch_assoc($kitq))
					while($hkits=$kitq->fetch())
					{	
						if($hkits['prod_id']!=$info['id'])
						{	
							/*$sq=ysql_query("SELECT `nav_skuvar` FROM (".PTABLE." as p JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) JOIN nav_stock as n ON cf.`sku`=n.`nav_sku` WHERE p.`".PFIELDID."`='$hkits[prod_id]' AND `nav_qty`>'0' ORDER BY `nav_skuvar` ASC",CARTDB);
							list($prodsku)=mysql_fetch_row($sq);*/
							$sq=$db1->prepare("SELECT `nav_skuvar` FROM (".PTABLE." as p JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) JOIN nav_stock as n ON cf.`sku`=n.`nav_sku` WHERE p.`".PFIELDID."`=? AND `nav_qty`>'0' ORDER BY `nav_skuvar` ASC");
							$sq->execute(array($hkits['prod_id']));
							list($prodsku)=$sq->fetch();
							?><input type="hidden" name="skuvariant[<?=$hkits['prod_id']?>]" value="<?=$prodsku?>-qty-<?=$hkits['qty']?>" /><?php 
						}
						array_push($kitskus,$hkits['prod_id']);
					}
				}
				if(in_array($info['id'],$kitskus)||$kitsfound<2)//show chooser if only 1 in pack or current product is in pack?
				{
					if($kitsfound>0){
						//$kit=mysql_fetch_assoc($kitq);
						$kit=$kitq->fetch();
						$kit_qty=$kit['qty'];
						$kit_item=$kit['itemId'];
					}else{$kit_qty=1;$kit_item=$info['id'];}
					
					cart_colourchooser($kit_item,$kit_qty,1,0);
				}
			}
			?>
			<br /><br />
			<div id="addbasket"><a name="basketbutton"></a>QTY <input type="text" name="quantity" style="width:20px;" class="formfield" value="1" /> <input type="submit" name="submit" value="Add to Basket" class="formbutton" /><br />
			<div class="clear"></div><?php
			if($info['excludediscount']==1){?><p style="margin-top:5px"><dfn style="font-size:90%;font-style:italic;color:#CD071E;line-height:100%">Discount exempt (this product is already on special offer, therefore further discounts will not apply to this product).</dfn></p><?php }?></div>
			</form>			
			<?php if(isset($_SESSION['cartupdate']))
			{
				?>
				<div style="background:#FFF;border:1px solid red;float:left;margin-left:5px;color:red"><?=!isset($_SESSION['error'][$the_array['prod_id']])?$_SESSION['cartupdate']:$_SESSION['error'][$info['id']];?></div>
				<div class="clear"></div>
				<?php 
				if(isset($_SESSION['offerprod'])&&$_SESSION['offerprod']==$info['id'])
				{
					list($cart_ids,$binds)=bindIns(implode(",",$cart_ids));
					
					cart_prodlist("SELECT * FROM (fusion as f JOIN ".PTABLE." as p ON f.`itemId`=p.`".PFIELDID."`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` WHERE `ownerId`='".$info['id']."' AND p.`".PFIELDID."` NOT IN(".$cart_ids.") AND `itemType`='product' AND `ownerType`='product' AND `allowoffer`='1' ORDER BY f.`sorting`","","We have found these additional suggestions for you.","offerprods",1,$binds);
					
					
				}
				unset($_SESSION['cartupdate']);
				unset($_SESSION['offerprod']);
			}
			cart_prodlist("SELECT `fusionId`,f.`ownerId`,p.`".PFIELDID."` as prod_id,p.`".PFIELDDESC."` as description,p.`".PFIELDNAME."` as title,min(cv.`price`) as price,cf.`salediscount`,cf.`saletype`,f.`sorting` FROM ((((".PTABLE." as p JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` AND cf.`allowpurchase`='1') JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='product') JOIN nav_stock as n ON cv.`vskuvar`=n.`nav_skuvar` AND `nav_qty`>'0') LEFT JOIN cart_kits as ck ON ck.`ownerId`=p.`".PFIELDID."` WHERE f.`ownerId`=? GROUP BY p.`".PFIELDID."` ORDER BY f.`sorting`","","Suggested Products","prodthumbs",5,array($info['id']));
			?>
		</div>
		
		<div id="details">
				
		<h2>Product Details</h2>
		<?php if(isset($info['avgrank'])&&$info['avgrank']>0){?>
		Average rating: <?=cart_stars($info['avgrank'])?> 
		<a href="#reviews">(<?=$info['countrevs']?> review<?=$info['countrevs']>1||$info['countrevs']<1?"s":""?>)</a>
		<br /><?php }?><?=$info['description']?>
		<?php if($info['ownerId']==22){?><dfn>Please note when ordering a choke it is your responsibility to ensure you select the correct choke system for the barrels of your gun.  GMK accept no responsibility for any damage caused to barrels by incorrect choke fit.</dfn><?php }?>
		</div>		
		<?php
		$extrapics=glob($cart_images_arr['product']['path'].$info['id']."/*.jpg");
		$expicscount=count($extrapics);
		$cols=5;
		if($expicscount>0)
		{
			$ex=0;
			?><h2>Additional Images</h2><dfn>(Click to enlarge)</dfn><table style="width:100%"><tr><?php
			foreach($extrapics as $extrapic){
			$ex++;
			$rowdivide=$ex%$cols;
			$totalover=$expicscount%$cols;
			$cellstoadd=$cols-$totalover;
			?><td style="text-align:center;width:<?=(100/$cols)?>%;"><span <?=$deviceType=="phone"?"":"class='lbox'"?>><a href="<?=$extrapic?>" style="display:inline-block;background:#FFF;" title="<?=str_replace(".jpg","",basename($extrapic))?>"><img src="<?=$extrapic?>" alt="<?=str_replace(".jpg","",basename($extrapic))?>" style="height:100px;vertical-align:bottom;" /></a></span></td><?php
				if($cellstoadd<$cols&&$ex>=$expicscount){for($x=1;$x<=$cellstoadd;$x++){?><td style="border:1px solid #010C39"></td><?php }}
				if($rowdivide==0||($ex>=$expicscount&&$x-1==$cellstoadd)){?></tr><?php if($ex<$expicscount){?><tr><?php }}			
			}
			?></table><?php
		}
		?>
		<h2>Customer Reviews</h2>
		<div id="reviews">
				<?php
				if(isset($post_arr['comment'])&&strlen($post_arr['shouldbeempty'])<1&&isset($get_arr['reviewform'])&&$get_arr['reviewform']==1&&$_SESSION['loggedin']!=0)
				{
					$higherr=array();
					if($post_arr['comment']=="Your review"||strlen($post_arr['comment'])<1 || $post_arr['title']=="review title"||strlen($post_arr['title'])<1)
					{
						if($post_arr['title']=="review title"||strlen($post_arr['title'])<1)
						{
							array_push($higherr,"title");
						}
						if($post_arr['comment']=="Your review"||strlen($post_arr['comment'])<1)
						{
							array_push($higherr,"comment");
						}
					}
					else
					{
						cart_query("INSERT INTO cart_reviews (item_id,cust_id,title,comment,rank,display,date_created,state)VALUES(?,?,?,?,?,?,NOW(),'1')",array($post_arr['item_id'],$ua['cust_id'],$post_arr['title'],$post_arr['comment'],$post_arr['rank'],$post_arr['display']));
						$get_arr['reviewform']=0;
					}
				}
				/*$rev_q=ysql_query("SELECT * FROM cart_reviews as cr LEFT JOIN cart_customers as c ON cr.`cust_id`=c.`cust_id` WHERE cr.`item_id`='$the_array[prod_id]' AND cr.`state`='1'");
				$rev_num=mysql_num_rows($rev_q);*/
				$rev_q=$db1->prepare("SELECT * FROM cart_reviews as cr LEFT JOIN cart_customers as c ON cr.`cust_id`=c.`cust_id` WHERE cr.`item_id`=? AND cr.`state`='1'");
				$rev_q->execute(array($info['id']));
				$rev_num=$rev_q->rowCount();
				$link=isset($_SESSION['loggedin'])&&$_SESSION['loggedin']!=0?$cururl."&amp;reviewform=1#review":"cart_login&amp;to_".$cururl."&amp;to_reviewform=1&amp;hash=review";
				if(!isset($get_arr['reviewform'])||$get_arr['reviewform']==0||$_SESSION['loggedin']==0){
				?>
			<ul>
				<li>
				<a href="./<?=$link?>"><?=$rev_num==0?"Be the first to review this product":"Write a review"?></a>
				</li>
			</ul>
			<?php } 
			?><a name="review"></a><?php
			if(isset($get_arr['reviewform'])&&$get_arr['reviewform']==1&&$_SESSION['loggedin']!=0)
			{
				
			?>
			<div style="width:440px;">
			
			<form class="global-form" action=".<?=$cururl?>&amp;reviewform=1#review" method="post">
				<input type="hidden" name="shouldbeempty" value="" />
				<input type="hidden" name="item_id" value="<?=$info['id']?>" />
				<table>
					<tr class="head"><td colspan="2"><div class="titles">Your Review</div></td></tr>
					<tr>
						<td class="left_light"><label for="title">Review Title</label></td>
						<td class="right_light"><input class="input_text" type="text" name="title" id="title" onfocus="this.select()" value="review title" maxlength="40" <?=cart_highlighterrors($higherr,"title")?> /></td>
					</tr>
					<tr>
						<td class="left_dark"><label for="rank">Ranking</label></td>
						<td class="right_dark">
							<select id="rank" name="rank">
								<option value="5" selected="selected">Excellent</option>
								<option value="4">Good</option>
								<option value="3">Fair</option>
								<option value="2">Poor</option>
								<option value="1">Horrible</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top" class="left_light"><label for="comment">Your Review</label></td>
						<td class="right_light"><textarea class="input_text" id="comment" name="comment" rows="5" cols="50" onfocus="this.select()" <?=cart_highlighterrors($higherr,"comment")?>>Your review</textarea></td>
					</tr>
					<tr>
						<td class="left_dark"><label for="display">Show my Name</label></td>
						<td class="right_dark"><input type="hidden" name="display" value="0" /><input type="checkbox" class="formradio" id="display" name="display" value="1" /></td>
					</tr>
				</table>
				<?php if(count($higherr)>0){?><dfn style="color:red;display:block">Please fill in all fields</dfn><?php }?>
				<p class="submit"><input type="submit" class="formbutton" value="Add Review" /></p>
			</form>
			</div>
			<?php } 
						
			$revcount=1;
			//while($rev=mysql_fetch_assoc($rev_q))
			while($rev=$rev_q->fetch(PDO::FETCH_ASSOC))
			{
				?>
				<p>
				<span class="titlebold" style="font-size:14px;color:#b8bed2;padding-bottom:2px;border-bottom:1px dotted #4a526e"><?=ucfirst($rev['title'])?>&nbsp;&nbsp;</span><br />
				<strong>Rating:</strong> <?=cart_stars($rev['rank'])?><br />
				<?php if($rev['display']==1){?><strong>Submitted By:</strong> <?=$rev['firstname']." ".$rev['lastname']?><br /><?php }?>
				&#34;<?=ucfirst($rev['comment'])?>&#34;<br />
				<strong>Submitted On:</strong> <?=date("d/m/Y",strtotime($rev['date_created']))?><br />				
				<strong>Review:</strong> <?=$revcount++?> of <?=$rev_num?>
				</p>
				<?php 
			}
			?>
		</div>
		<script type="text/javascript">
		/* <![CDATA[ */
		var oldimage;
		function swapimage(id,thenewimg)
		{
			if(thenewimg.length>3)
			{
				imageobj=document.getElementById(id+"_image");
				oldimage=imageobj.src;
				if(file_exists(thenewimg))
					imageobj.src=thenewimg;
			}
		}
		function returnimage(id)
		{
			document.getElementById(id+"_image").src=oldimage;
		}
		function file_exists (url) {
			req = this.window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
			if (!req) {throw new Error('XMLHttpRequest not supported');}      
			// HEAD Results are usually shorter (faster) than GET
			req.open('HEAD', url, false);
			req.send(null);
			if (req.status != 404)
				return true;
			else
				return false;
		}
		/* ]]> */
		</script>
		<?php 
	}
	else
	{
		echo "Sorry, this product is currently unavailable.";
	}
}

/* /INDIVIDUAL PRODUCT */
include "cart_foot.php";
?>