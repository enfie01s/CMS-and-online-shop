<?php
if(isset($_GET['mp'])&&count($subdirs)>0)
{
	$prodmod=array_search("products",$modules_pages);
	$catsdeployed=0;
	### cats ###
	$depth=count($subdirs);
	$arr=$prodscats;
	$info=$pcdetail;
	for($dp=0;$dp<$depth;$dp++)
	{
		$arr=$arr[urldecode($subdirs[$dp])];
		$info=$info[urldecode($subdirs[$dp])];
	}
	if($depth>2){?><div style="width:100%;background:#fff;color:#000;padding:6px;font-size:1.2em"><?php }
	if(isset($arr)&&is_array($arr))
	{
		$catsdeployed=1;
		?>
		<div style="position:relative;top:0;left:0;margin-bottom:20px;<?=$depth<3?"font-size:1.2em":""?>">
		<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
		<div style="position:absolute;top:10px;right:5px;"><a class="acpbutton" href="./admin/index.php?p=products&amp;showing=catform&amp;cid=<?=urlencode($info['id'])?>" target="_blank">Edit Cat</a> <a class="acpbutton" href="./admin/index.php?p=products&amp;showing=catform&amp;owner=<?=urlencode($info['id'])?>" target="_blank">Add Subcat</a></div>
		<?php }
		$tyhead="content/images/categories/".$info['header'];
		if(is_file($tyhead))
		{
			?>
			<img src="<?=$tyhead?>" alt="" style="width:100%" />
			<?php 
		}
		if(strlen(trim(str_replace(array("<p>","</p>"),array("",""),$info['catscrip'])))>0){?>
		<div id="brandintro" <?=is_file($tyhead)?"":"style='position:relative !important;'"?>><?=$info['catscrip']?></div>
		<?php }?>
		</div>
		<?php
		?><table id="type2links" style="border-collapse:separate"><?php
		$xx=1;
		$totalxx=count($arr);
		foreach($arr as $inx => $t)
		{
			if(is_array($t))
			{
				$idetail=$info[$inx];
				$tylogo="content/images/categories/".$idetail['logo'];
				if(file_exists($tylogo)){list($w,$h)=getimagesize($tylogo);}
				if($xx%2>0){?><tr><?php }?>
				<td style="border-<?=$xx%2>0?"right":"left"?>: 15px solid #010C39;position:relative;top:0;left:0;z-index:1;width:50%" id="cat<?=$xx?>">
				<a href="./<?=$subdirs[0]?>/<?=$subdirs[1]?>/<?=urlencode(urlencode($inx))?>">
				<?=file_exists($tylogo)?"<img src='".$tylogo."' alt=''".($w<100?" style='width:auto !important'":"")." />":""?><?=ucwords($inx)?>
				</a><?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
				<div style="position:absolute;top:2px;right:2px;z-index:2;color:#fff"><?php if($xx>1){?><a onclick="ajax('catorder','<?=$xx?>][<?=$info['id']?>][down')" style="padding:2px 4px;display:inline-block;color:#fff;cursor:pointer" class="acpbutton" title="Move up the order">&lt;</a> <?php } if($xx<$totalxx){?><a onclick="ajax('catorder','<?=$xx?>][<?=$info['id']?>][up')" style="padding:2px 4px;display:inline-block;color:#fff;cursor:pointer" class="acpbutton" title="Move down the order">&gt;</a><?php }?></div>
				<?php }?></td>		
				<?php 
				if($xx==$totalxx&&$xx%2>0){?><td style="background: transparent !important;"></td><?php }
				if($xx%2==0){
					?></tr><?php 
				}
				$xx++;
			}
		}
		?></table><?php
	}
	
	### products ###
	
	//$q=$db1->prepare("SELECT *,MIN(`price`) as cprice,t5.description as catdesc,t1.description proddesc FROM (((gmk_products as t1 JOIN fusion as t2 ON t1.pid=t2.itemID AND t2.itemType='product') JOIN gmk_categories as t5 ON t5.cid=t2.ownerId) LEFT JOIN gmkbrands as t3 ON t1.bid=t3.id) LEFT JOIN cart_variants as t4 ON t1.pid=t4.pid WHERE t2.ownerId=? AND displayed=1 GROUP BY t1.`pid` ORDER BY t2.sorting ASC");
	//$q->execute(array($_GET['br']));
	//$t=$q->fetch(PDO::FETCH_ASSOC);
	$ctype=$info['ctype'];
	$hasprods=0;
	foreach($arr as $inx => $t)
	{
		if(!is_array($t)){$hasprods=1;}
	}
			
	if($hasprods)
	{
		if(/*$catsdeployed==*/0)
		{
			$head="content/images/categories/".$info['header'];
			?>
			<div style="position:relative;top:0;left:0;margin-bottom:20px">
			<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
		<div style="position:absolute;top:10px;right:5px;"><a class="acpbutton" href="./admin/index.php?p=products&amp;showing=catform&amp;cid=<?=urlencode($info['id'])?>" target="_blank">Edit Cat</a> <a class="acpbutton" href="./admin/index.php?p=products&amp;showing=prodform&amp;owner=<?=urlencode($info['id'])?>" target="_blank">Add Item</a></div>
		<?php }?>
				<?php if(is_file($head)){?><img src="<?=$head?>" alt="" style="width:100%" /><?php }?>
				<?php if(strlen($info['catscrip'])>0){?><div id="brandintro" <?php if(!is_file($head)){?>style="position:relative !important;top:0 !important;"<?php }?>><?=$info['catscrip']?></div><?php }?>
			</div>
			<?php
		}
		$tylogo="content/images/categories/".$info['logo'];
		//$tydesc="content/images/categories/".$info[4].".txt";
		$table="content/images/categories/".$info['id']."_table.jpg";
		$tyicons="content/images/categories/".$info['id']."_icons.jpg";
		?><div><?php
		if(is_file($tylogo)&&strlen($info['scrip'])>0)
		{
			?><img src="<?=$tylogo?>" alt="" style="float:left;margin-right:10px" /><?php
		}
		if(strlen($info['scrip'])>0)
		{
			echo $info['scrip'];
		}
		?></div><div class="clear">&nbsp;</div><br /><?php
		if(in_array($ctype,array("rifle","shotgun")))//Rifles Layout
		{
			if($ctype=="rifle"){include "abbreviations.php";}
			$vari=array();
			$varibarrels=array();
			$varigauges=array();
			$xx=1;
			$totalrows=count($arr);
			foreach($arr as $inx => $t)
			{
				$thisinfo=$info[$t];
				//list($rw,$rh)=getimagesize($images_arr['product']['path']."big/".$t['bigimage']);				
				//image width/content width = %
				//$pc=$rw/777;$arpos=($rh/$pc)-85;//for up/down arrows pos
				$vq=$db1->prepare("SELECT vname,GROUP_CONCAT(DISTINCT(`field1`) SEPARATOR '#') as f2,GROUP_CONCAT(DISTINCT(`field2`) SEPARATOR '#') as fbarrel,`field4`,`field1`,`kg`,`price` FROM cart_variants WHERE `pid`=? GROUP BY `field4` ORDER BY `price`,`field1`,`field2`,`field4`");
				$vq->execute(array($thisinfo['id']));
				$v=$vq->fetch();
				?>
				<div style="position:relative;top:0;left:0" id="prod<?=$xx?>">
					<div>
						<a name="item<?=$thisinfo['id']?>"></a>
						<img src="<?=$images_arr['product']['path']?>big/<?=$thisinfo['bigimage']?>" alt="" style="width:100%" />
						<div class="rifleprice_sakotikka" style=" <?=$thisinfo['lhimg']==1?"left:".($deviceType=="phone"?15:30)."px;text-align:left;":"right:30px;text-align:right;"?>">
							<div><span><?=$thisinfo['itemtitle']?></span>
							<br />RRP &#163;<?=$v['price']?></div>
						</div>
						<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
							<div style="position:absolute;top:0px;right:5px;text-align:right"><a class="acpbutton" href="./admin/index.php?p=products&amp;showing=prodform&amp;pid=<?=urlencode($thisinfo['id'])?>&amp;owner=<?=urlencode($info['id'])?>" target="_blank">Edit</a></div>
						<?php }?>
						<div style="width:90%;margin:auto;position:relative;top:-40px;left:0"><?=$thisinfo['proddesc']?></div>
					</div>
					<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods))
					{?>
					<div style="position:absolute;top:-3px;right:51px"><?php if($xx>1){?><a onclick="ajax('prodorder','<?=$xx?>][<?=$info['id']?>][down')" style="padding:2px 4px;display:inline-block;color:#fff;cursor:pointer" class="acpbutton" title="Move up">&#x25B2;</a> <?php } if($xx<$totalrows){?><a onclick="ajax('prodorder','<?=$xx?>][<?=$info['id']?>][up')" style="padding:2px 4px;display:inline-block;color:#fff;cursor:pointer" class="acpbutton" title="Move down">&#x25BC;</a><?php }?>
					</div>
					<?php }?>
				</div>
				<?php
				if($ctype=="shotgun")
				{
					$vq->execute(array($thisinfo['id']));
					while($v=$vq->fetch())
					{
						//echo $t['prod_title'].", ".$v['field1'].", ".$v['f2'].", ".$v['field4'].", ".$v['field4']."<br />";
						$cleanbarrels=strlen(trim($v['fbarrel']))>0&&$v['fbarrel']!="#"?str_replace(array(",","/"),array("#","#"),$v['fbarrel']):"";
						$cleangauges=strlen(trim($v['f2']))>0&&$v['f2']!="#"?str_replace(array(",","/"),array("#","#"),$v['f2']):"";
						$vari[]=array($v['price'],ucwords($thisinfo['itemtitle']),$v['field1'],$cleangauges,$v['field4'],$v['kg'],$thisinfo['LH'],$v['vname'],$cleanbarrels);
						$barrels=strlen(trim($cleanbarrels))>0?explode("#",str_replace(array("\""," "),array("",""),$cleanbarrels)):array();
						$gauges=strlen(trim($cleangauges))>0?explode("#",str_replace(array("\""," "),array("",""),$cleangauges)):array();
						foreach($barrels as $b => $bar)
						{
							if(!in_array($bar,$varibarrels)){$varibarrels[]=$bar;}
						}
						foreach($gauges as $g => $gau)
						{
							if(!in_array($gau,$varigauges)){$varigauges[]=$gau;}
						}
					}
				}
				elseif($ctype=="rifle")
				{
					$vq->execute(array($thisinfo['id']));
					while($v=$vq->fetch())
					{
						//echo $t['prod_title'].", ".$v['field1'].", ".$v['f2'].", ".$v['field4'].", ".$v['field4']."<br />";
						$cleangauges=strlen(trim($v['f2']))>0&&$v['f2']!="#"?str_replace(array(",","/"," ","*"),array("#","#","",""),$v['f2']):"";
						$vari[]=array($v['price'],ucwords($thisinfo['itemtitle']),$v['field1'],$cleangauges,$v['field4'],$v['kg'],$thisinfo['LH'],$v['vname']);						
						$gauges=strlen(trim($cleangauges))>0?explode("#",str_replace(array("\""," ","*"),array("","",""),$cleangauges)):array();
						foreach($gauges as $g => $gau)
						{
							if(!in_array($gau,$varigauges)&&strlen(trim($gau))>0&&array_key_exists($gau,$abbrev['sako'])){$varigauges[]=$gau;}
						}
					}
				}
				$xx++;
			}
			if(count($vari)>0)
			{
				if($ctype=="shotgun")
				{
					sort($varigauges);
					$varnum=count($varigauges);
					sort($varibarrels);
					$barnum=count($varibarrels);
					?>
					<table style="width:100%;" cellspacing="1" cellpadding="6" class="lightrows">
					<tr class="head">
						<td colspan="<?=$barnum+$varnum+5?>"><strong>Information</strong></td>
					</tr>
					<tr class="subhead">
						<td rowspan="2">Model/Description</td>
						<td colspan="<?=$varnum?>" style="text-align:center">Gauge</td>
						<td colspan="<?=$barnum?>" style="text-align:center">Barrel</td>
						<td rowspan="2" style="text-align:center">Chokes<br />(No. Chokes)</td>
						<td rowspan="2" style="text-align:center">Approx Weight<br />Kg</td>
						<td rowspan="2" style="text-align:center">Left Hand</td>
						<td rowspan="2" style="text-align:center">SRP<br />(Inc. VAT)</td>
					</tr>
					<tr class="extrahead">
					<?php foreach($varigauges as $g => $gau){?>
						<td style="text-align:center"><?=$gau?></td>
					<?php }?>
					<?php foreach($varibarrels as $b => $bar){?>
						<td style="text-align:center"><?=$bar?>&#34;</td>
					<?php }?>
					</tr>
					<?php
					asort($vari);
					foreach($vari as $n => $ar)
					{
						$gauges=explode("#",str_replace("\"","",$ar[3]));
						$barrels=explode("#",str_replace("\"","",$ar[8]));
						$row=!isset($row)||$row=="_dark"?"_light":"_dark";
						?>
						<tr class="row<?=$row?>">
							<td><?=$ar[1].": ".$ar[7]?></td>
							<?php foreach($varigauges as $g => $gau){?>
							<td style="text-align:center"><?=in_array($gau,$gauges)?"&#10004;":""?></td>
							<?php }?>
							
							<?php foreach($varibarrels as $b => $bar){?>
							<td style="text-align:center"><?=in_array($bar,$barrels)?"&#10004;":""?></td>
							<?php }?>
							<td style="text-align:center"><?=$ar[4]?></td>
							<td style="text-align:center"><?=$ar[5]?></td>
							<td style="text-align:center"><?=$ar[6]==1?"&#10004;":""?></td>
							<td style="text-align:center">&#163;<?=number_format($ar[0],2)?></td>
						</tr>
						<?php
					}
					?>
					</table>
					<?php
				}
				elseif($ctype=="rifle")
				{ 
					sort($varigauges);
					$barnum=count($varigauges);
					?>
					<table style="width:100%;" cellspacing="1" cellpadding="6" class="lightrows">
					<tr class="head">
						<td colspan="<?=$barnum+2?>"><strong>Information</strong></td>
					</tr>
					<tr class="subhead">
						<th>Model/Description</th>
						<th>Action</th>
						<?php foreach($varigauges as $g => $gau){?>
							<th class="vertical-text" style="height:<?=(strlen($abbrev['sako'][$gau])+2)*6?>px">
							<div><span><?=$abbrev['sako'][$gau]?></span></div>
							</th>
						<?php }?>
					</tr>
					<?php
					asort($vari);
					foreach($vari as $n => $ar)
					{
						$gauges=explode("#",str_replace("\"","",$ar[3]));
						$row=!isset($row)||$row=="_dark"?"_light":"_dark";
						?>
						<tr class="row<?=$row?>">
							<td><?=$ar[1].(strlen($ar[7])>0?": ".$ar[7]:"")?></td>
							<td><?=$ar[4]?></td>
							<?php foreach($varigauges as $g => $gau){?>
							<td style="text-align:center"><?=in_array($gau,$gauges)?"&bull;":""?></td>
							<?php }?>
						</tr>
						<?php
					}
					?>
					</table>
					<?php
				}
			}
			elseif(is_file($table))
			{
				list($wx, $hx) = getimagesize($table);?>
				<div style="width:90%;margin:auto;"><img src="<?=$table?>" alt="" style="width:<?=$wx>650?"100%":"auto"?>" /><br />&nbsp;</div>
				<?php
			}
			?>
			</div><?php
		}
		else// if(in_array($_GET['br'],array(7,8,16,15)))//Steiner, burris & Leupold
		{
				$move=55;
				$xx=1;
				$totalrows=count($arr);
				foreach($arr as $inx => $t)
				{
					$thisinfo=$info[$t];
					$ld="_dark";
					$bfname=$images_arr['product']['path']."big/".$thisinfo['bigimage'];
					$ticons=$images_arr['product']['path']."icons/".$thisinfo['id'].".png";
					$itype=pathinfo($bfname, PATHINFO_EXTENSION);
					$bigstyle=$itype=="png"&&$thisinfo['imgshift']==1?"position:absolute;top:-".$move."px;right:0;width:45%;":"width:40%;";				
					if($info['id']==7&&$itype=="png")
					{
						//$bigstyle.="-ms-transform: rotate(7deg);-webkit-transform: rotate(7deg);transform: rotate(7deg);";
					}
					list($spacerw,$spacerh)=is_file($bfname)?getimagesize($bfname):array(0,0);				
					//$tblstyle=pathinfo($images_arr['product']['path']."big/".$t['bigimage'], PATHINFO_EXTENSION)=="png"?"position:relative;top:-45px;left:0;":"";
					?>
					<div style="position:relative;top:0;left:0" id="prod<?=$xx?>">
					<div>
						<div class="prodprice" style=" background:#<?=$thisinfo['col1']?>;">
							<div><span <?=$thisinfo['id']==15?"style='color:#000 !important;'":""?>><?=htmlspecialchars($thisinfo['itemtitle'])?></span>
							<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
				<a class="acpbutton" href="./admin/index.php?p=products&amp;showing=prodform&amp;pid=<?=urlencode($thisinfo['id'])?>&amp;owner=<?=urlencode($info['id'])?>" target="_blank">Edit</a>
				<?php }?>
							</div>
						</div>
						<div class="prodinfobox">
							<?php if(strlen(trim(str_replace(array("&nbsp;","<p>","</p>"),array("","",""),$thisinfo['proddesc'])))>0||is_file($bfname)){?>
							<div style="padding:10px;float:left;width:50%;">
							<?=str_replace(array("</p><p>","<p>","</p>"),array("<br /><br />","",""),$thisinfo['proddesc'])?>
							<?php if(is_file($ticons)){?>
							<img src="<?=$ticons?>" alt="" style="height:40px;visibility:hidden" />
							
							<?php }?>
							</div>
							<?php }?>
							<?php if(is_file($bfname)){?><img src="<?=$bfname?>" alt="" style="float:right;<?=$bigstyle?>" /><?php }?>
							<?php if($itype=="png"&&$thisinfo['imgshift']==1){?><img src="<?=$bfname?>" alt="" style="width:37%;visibility:hidden" /><?php }?>
							<div class="clear"></div>
						<table style="position:relative;top:0;left:0;<?=$thisinfo['id']==15?"border-top:4px solid #".$thisinfo['col2'].";":""?>" class="infotable"><?php 
						$vR=$db1->prepare("SELECT * FROM cart_variants WHERE `pid`=? ORDER BY `price` ASC");
						$vR->execute(array($thisinfo['id']));$vn=0;
						while($v=$vR->fetch())
						{
							$ld=!isset($ld)||$ld=="_dark"?"_light":"_dark";
							?>
								<tr class="row<?=$ld?>">
									<td style="white-space:nowrap;<?=$ld=="_light"?"background:#".$thisinfo['col2']." !important":""?>"><?php if($vn==0&&is_file($ticons)){?><img src="<?=$ticons?>" alt="" style="height:30px;position:absolute;top:-40px;left:10px;" /><?php $vn=1;}?><?=str_replace("-v-NONE","",$v['vskuvar'])?></td>
									<td style="width:100%"><?=$v['vname']?></td>
									<td style="text-align:right;white-space:nowrap;font-weight:bold;"><?php if($v['price']>0){?>&#163;<?=number_format($v['price'],2)?><?php }else{?>TBA<?php }?></td>
								</tr>
							<?php
						}
						?></table></div>
					</div>
					<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods))
					{?>
					<div style="position:absolute;top:0px;right:5px"><?php if($xx>1){?><a onclick="ajax('prodorder','<?=$xx?>][<?=$info['id']?>][down')" style="padding:2px 4px;display:inline-block;color:#fff;cursor:pointer" class="acpbutton" title="Move up the order">&#x25B2;</a> <?php } if($xx<$totalrows){?><a onclick="ajax('prodorder','<?=$xx?>][<?=$info['id']?>][up')" style="padding:2px 4px;display:inline-block;color:#fff;cursor:pointer" class="acpbutton" title="Move down the order">&#x25BC;</a><?php }?>
					</div>
					<?php }?>
					</div>
					<?php
					$xx++;
				}
				if(is_file($tyicons)){?><img src="<?=$tyicons?>" alt="" style="margin-left:38px;" /><?php }?>			
			</div><?php
		}
	}
}
?>