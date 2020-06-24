<?php if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
include "cart_head.php";
/*$qstring="
	(SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDDESC."` as tdesc,f.`itemType` as ftype,f.`fusionId` as fid,f.`ownerId` as iown  
	FROM (".PTABLE." as p JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND f.`itemType`='product' AND p.`displayed`='1') JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` AND cf.`allowpurchase`='1'  
	WHERE p.`".PFIELDNAME."` LIKE '%$post_arr[searchall]%' OR p.`".PFIELDDESC."` LIKE '%$post_arr[searchall]%' OR cf.`sku`='$post_arr[searchall]' 
	GROUP BY p.`".PFIELDID."`) 
	UNION all 
	(SELECT c.`".CFIELDNAME."` as title,c.`".CFIELDDESC."` as tdesc, f.`itemType` as ftype,c.`".CFIELDID."` as fid,f.`ownerId` as iown  
	FROM (".CTABLE." as c JOIN fusion as f ON c.`".CFIELDID."`=f.`itemId` AND f.`itemType`='category') JOIN cart_catopts as co ON co.`cat_id`=c.`".CFIELDID."` AND co.`showincart`='1' 
	WHERE c.`".CFIELDNAME."` LIKE '%$post_arr[searchall]%' OR c.`".CFIELDDESC."` LIKE '%$post_arr[searchall]%' 
	GROUP BY c.`".CFIELDID."`) 
	ORDER BY `ftype`,`iown`
	";
$searchq=ysql_query($qstring,CARTDB) or die(sql_error("Error"));
$searchrows=mysql_num_rows($searchq);*/
$qstring="
	(SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDDESC."` as tdesc,f.`itemType` as ftype,f.`fusionId` as fid,f.`ownerId` as iown  
	FROM (".PTABLE." as p JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND f.`itemType`='product' AND p.`displayed`='1') JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` AND cf.`allowpurchase`='1'  
	WHERE p.`".PFIELDNAME."` LIKE ? OR p.`".PFIELDDESC."` LIKE ? OR cf.`sku`=? 
	GROUP BY p.`".PFIELDID."`) 
	UNION all 
	(SELECT c.`".CFIELDNAME."` as title,c.`".CFIELDDESC."` as tdesc, f.`itemType` as ftype,c.`".CFIELDID."` as fid,f.`ownerId` as iown  
	FROM (".CTABLE." as c JOIN fusion as f ON c.`".CFIELDID."`=f.`itemId` AND f.`itemType`='category') JOIN cart_catopts as co ON co.`cat_id`=c.`".CFIELDID."` AND co.`showincart`='1' 
	WHERE c.`".CFIELDNAME."` LIKE ? OR c.`".CFIELDDESC."` LIKE ? 
	GROUP BY c.`".CFIELDID."`) 
	ORDER BY `ftype`,`iown`
	";
$searchq=$db1->prepare($qstring);
$searchq->execute(array("%".$post_arr['searchall']."%","%".$post_arr['searchall']."%","%".$post_arr['searchall']."%","%".$post_arr['searchall']."%","%".$post_arr['searchall']."%"));
$searchrows=$searchq->rowCount();
$pagetitle=$searchrows>0?$searchrows." Record".($searchrows>1||$searchrows<1?"s":"")." Found for &#168;".$post_arr['searchall']."&#168;":"Sorry, your search returned no results, please try again";
?><h2 id="pagetitle">Search Results</h2><p class="headbold"><?=$pagetitle?></p><?php
if($searchrows>0)
{
	?><ol><?php
	while($searchres=mysql_fetch_assoc($searchq))
	{
		?><li>
		<h2><a href='<?=MAINBASE?>/index.php?p=cart_products&amp;<?=($searchres['ftype']=='product'?"pid=":"cat=").$searchres['fid']?>&amp;<?=($searchres['ftype']=='product'?"prodname=":"catname=").urlencode($searchres['title'])?>'><?=ucwords($searchres['title'])?></a> | <?=$searchres['ftype']?></h2>
		<?=$searchres['ftype']=='product'&&strlen($searchres['tdesc'])>0?str_replace(array("&gt;","&lt;","&amp;nbsp;"),array(">","<"," "),htmlentities(cart_trimtext($searchres['tdesc'],200),ENT_QUOTES,"ISO-8859-1")):""?>
		</li><?php
	}
	?></ol><?php
}
include "cart_foot.php";
?>