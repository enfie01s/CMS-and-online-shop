<?php
$get_arr=isset($_GET)?mysql_real_extracted($_GET):array();

/*$q=ysql_query("SELECT p.`prod_title`,p.`description`,p.`type`,cv.`vskuvar`,cv.`vname`,cv.`field1`,cv.`field2`,cv.`field3`,cv.`field4`,cv.`kg`,c.`title`,b.`brand`,f.`itemId`,f.`ownerId`,f.`fusionId` FROM ((((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product') LEFT JOIN gmk_categories as c ON c.`cid`=f.`ownerId` AND f.`ownerType`='category') LEFT JOIN cart_variants as cv ON cv.`pid`=p.`pid`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) WHERE cv.`vskuvar` LIKE '%".$get_arr['simplesearch']."%' OR p.`prod_title` LIKE '%".$get_arr['simplesearch']."%' OR p.`description` LIKE '%".$get_arr['simplesearch']."%' OR p.`type` LIKE '%".$get_arr['simplesearch']."%' OR cv.`vname` LIKE '%".$get_arr['simplesearch']."%' OR cv.`field1` LIKE '%".$get_arr['simplesearch']."%' OR cv.`field2` LIKE '%".$get_arr['simplesearch']."%' OR cv.`field3` LIKE '%".$get_arr['simplesearch']."%' OR cv.`field4` LIKE '%".$get_arr['simplesearch']."%' OR cv.`kg` LIKE '%".$get_arr['simplesearch']."%' ORDER BY c.`displayorder` ASC,b.`sorting` ASC",$con1);*/
$q=$db1->prepare("SELECT p.`prod_title`,p.`description`,p.`type`,cv.`vskuvar`,cv.`vname`,cv.`field1`,cv.`field2`,cv.`field3`,cv.`field4`,cv.`kg`,c.`title`,b.`brand`,f.`itemId`,f.`ownerId`,f.`fusionId` FROM ((((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product') LEFT JOIN gmk_categories as c ON c.`cid`=f.`ownerId` AND f.`ownerType`='category') LEFT JOIN cart_variants as cv ON cv.`pid`=p.`pid`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) WHERE cv.`vskuvar` LIKE ? OR p.`prod_title` LIKE ? OR p.`description` LIKE ? OR p.`type` LIKE ? OR cv.`vname` LIKE ? OR cv.`field1` LIKE ? OR cv.`field2` LIKE ? OR cv.`field3` LIKE ? OR cv.`field4` LIKE ? OR cv.`kg` LIKE ? ORDER BY c.`displayorder` ASC,b.`sorting` ASC");
$q->execute(array("%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%","%".$get_arr['simplesearch']."%"));
$cat="";
$brand="";
if(!isset($get_arr['simplesearch'])||strlen($get_arr['simplesearch'])<1||$q->rowCount()<1)
{
	?>
	<div class='largenotify' style="text-align:center">Sorry, the product search yeilded no results.</div>
	<?php
}
else
{
	?>
	<table>
	<?php
	//while($r=mysql_fetch_assoc($q))//itemId & ownerId
	while($r=$q->fetch())//itemId & ownerId
	{
		$matched=array();
		foreach($r as $field => $val)
		{
			if($field!="prod_title"&&$field!="vskuvar"&&stristr($val,$get_arr['simplesearch']))
			{
				$matched[]=$field;
			}
		}
		if($cat!=$r['title']||$brand!=$r['brand'])
		{
			?>
			<tr class="head">
				<td colspan="4"><div class="titles"><?=$r['title']." - ".$r['brand']?></div></td>
			</tr>
			<tr class="subhead">
				<td>Links</td>
				<td style="width:10%">GU Code</td>
				<td style="width:20%">Product Title</td>
				<td style="width:70%">Matches</td>
			</tr>
			<?php 
			$cat=$r['title'];
			$brand=$r['brand'];
		}
		$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
		
		?>
		<tr class="<?=$row_class?>">
			<td style="white-space:nowrap"><a href="index.php?p=products&amp;showing=prodform&amp;pid=<?=$r['itemId']?>&amp;owner=<?=$r['ownerId']?>&amp;curpage=<?=urlencode($r['prod_title'])?>">ACP</a> | <a href="../products/item/<?=$r['fusionId']?>" target="_blank">WEB</a></td>
			<td style="white-space:nowrap"><?=highlightSearch($get_arr['simplesearch'],$r['vskuvar'])?></td>
			<td><?=highlightSearch($get_arr['simplesearch'],$r['prod_title'])?></td>
			<td>
			<?php $dep=0;foreach($matched as $field){?>
			<?=$dep==1?"<br />":""?>
			<?="<strong>".ucwords($field).":</strong> ".highlightSearch(" ".$get_arr['simplesearch']." ",$r[$field],80);?>
			<?php $dep=1;}?>
			</td>
		</tr><?php
	}
	?>
	</table>
	<?php 
}?>
