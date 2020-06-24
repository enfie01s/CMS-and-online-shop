<?php 
$basket_total=0;
$basket_qty=0;
$sub_total=0;
$discount=0;
$fusionOwn=0;
$totaldiscount=0;
$vattoadd=0;
$_SESSION['pageloads']=!isset($_SESSION['pageloads'])||$_SESSION['pageloads']>=2?1:$_SESSION['pageloads']+1;
//if session == 11 and freepost == 0 change session
if(isset($_SESSION['shipping'])&&$_SESSION['shipping']==$freepostid&&$freepost==0&&cart_postapplicable()==1){$_SESSION['shipping']=5;$_SESSION['postdesc']="P&P";}
$_SESSION['postdesc']=isset($_SESSION['postdesc'])&&strlen($_SESSION['postdesc'])>0?(cart_postapplicable()?$_SESSION['postdesc']:$freepostedsc):($freepost==1||cart_postapplicable()==0?$freepostedsc:"P&P");
$_SESSION['shipping']=isset($_SESSION['shipping'])&&strlen($_SESSION['shipping'])>0?(cart_postapplicable()?$_SESSION['shipping']:$freepostid):($freepost==1||cart_postapplicable()==0?$freepostid:5);
$_SESSION['postdesc']=!isset($_SESSION['postdesc'])?(cart_postapplicable()?$_SESSION['postdesc']:$freepostedsc):$_SESSION['postdesc'];
$_SESSION['shipping']=!isset($_SESSION['shipping'])?(cart_postapplicable()?$_SESSION['shipping']:$freepostid):$_SESSION['shipping'];

/*
$_SESSION['cart']=array(
	array("prod_id" => 25,"skuvariant"=>array("25"=>"50500-qty-1"),"qty"=>1,"price"=>2935,"ispack"=>0,"exclude_discount"=>0,"allowlist"=>array(),"title"=>"Bodiam")
);
*/
if(!isset($_SESSION['cart'])){$_SESSION['cart']=array();}

$cart_ids=array();
$numincart=!isset($_SESSION['cart'])?0:count($_SESSION['cart']);
if($numincart>0)
{
	foreach($_SESSION['cart'] as $id => $item)
	{
		$itemprice=$item['price']*$item['qty'];
		$itemdiscount=(isset($_SESSION['discount_amount']) && isset($_SESSION['discount_list'])&&count($_SESSION['discount_list'])>0 && in_array($item['prod_id'],$_SESSION['discount_list'])) || (isset($_SESSION['discount_amount']) && (!isset($_SESSION['discount_list'])||count($_SESSION['discount_list'])<1)) && $_SESSION['discount_amount']>0 && $item['exclude_discount']!=1?$itemprice:0;
		
		$basket_qty+=$item['qty'];
		$sub_total+=$itemprice;
		$totaldiscount+=$itemdiscount;//add up all items which are excluded from discount
		if(!in_array($item['prod_id'],$cart_ids)){array_push($cart_ids,$item['prod_id']);}
	}
	$defaultpostage=$basket_qty>0?cart_postagecalc($sub_total,$_SESSION['shipping']):0;

	$discount=isset($_SESSION['discount_amount'])&&$_SESSION['discount_amount']>0?(($totaldiscount)/100)*$_SESSION['discount_amount']:0;
	$total=$sub_total-$discount;
	//$vattoadd=$vat*($total/100);//total vat for all items (including non discountable)
	$basket_total=cart_addvat($total,1)+$defaultpostage;//total, vat, postage cost and discount
}


$depth=count($subdirs);
$arr=$prodscats;
$info=$pcdetail;
$fstuff=$fieldstuff;
for($dp=0;$dp<$depth;$dp++)
{
	$arr=$arr[$subdirs[$dp]];
	$info=$info[$subdirs[$dp]];
	$fstuff=$fstuff[$subdirs[$dp]];
}
if(isset($subdirs[1])&&$subdirs[1]=="item"&&isset($subdirs[2])&&strlen(isset($subdirs[2]))>0)
{
	$query_string="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title,p.`".PFIELDDESC."` as content,cv.`vskuvar`,cf.`salediscount`,cf.`saletype`,MIN(cv.`price`) as price,cv.`kg`,cf.`excludediscount`,`fusionId`,f.`ownerId` as ownerId,f.`itemId` as itemId, `itemType`,`sorting`,`ownerType`,MAX(`rank`) as maxrank,MIN(`rank`) as minrank,AVG(`rank`) as avgrank,count(`cust_rev_id`) as countrevs,cf.`allowoffer`,cf.`allowpurchase`,ck.`in_kit_list` as kit,p.bigimage,p.lhimg,cv.field1,cv.field2,cv.field3,cv.field4,cv.kg,p.description FROM ((((".PTABLE." as p JOIN cart_variants as cv ON cv.`pid`=p.`".PFIELDID."`) JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product') JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` AND `allowpurchase`='1') LEFT JOIN cart_reviews as cr ON cr.`item_id`=p.`".PFIELDID."`) LEFT JOIN cart_kits as ck ON ck.`ownerId`=p.`".PFIELDID."` AND ck.`in_kit_list`='1' WHERE `fusionId`=? GROUP BY p.`".PFIELDID."`";
	$bind=$subdirs[2];
	$the_query=$db1->prepare($query_string);
	$the_query->execute(array($bind));
	$tarr=$the_query->fetch(PDO::FETCH_ASSOC);//array for product pages
	$info=array("id"=>$tarr['prod_id'],"bigimage"=>$tarr['bigimage'],"proddesc"=>$tarr['description'],"cprice"=>$tarr['price'],"lhimg"=>$tarr['lhimg'],"field1"=>$tarr['field1'],"field2"=>$tarr['field2'],"field3"=>$tarr['field3'],"field4"=>$tarr['field4'],"kg"=>$tarr['kg'],"fusionId"=>$tarr['fusionId'],"description"=>$tarr['description'],"allowoffer"=>$tarr['allowoffer'],"allowpurchase"=>$tarr['allowpurchase'],"ownerId"=>$tarr['ownerId'],"sku"=>$tarr['sku'],"itemtitle"=>$tarr['title']);
}
//if(isset($get_arr['pid'])||isset($get_arr['cat'])&&$page!="sitemap")
if($depth>0&&$page!="sitemap")
{
	$the_array=$arr;
	
	$allowlist=array();
	if(isset($info['allowoffer'])&&$info['allowoffer']==1)
	{
		//suggestion list
		
		$get_arrallowedq=$db1->prepare("SELECT * FROM fusion WHERE `itemId`=? AND `ownerType`='product'");
		$get_arrallowedq->execute(array($info['id']));
		while($get_arrallowed=$get_arrallowedq->fetch(PDO::FETCH_ASSOC))
		{
			if(!in_array($get_arrallowed['ownerId'],$allowlist)){array_push($allowlist,$get_arrallowed['ownerId']);}
			//if($get_arrallowed['displayed']==1){$allowlist=array();break;}
		}
	}
	if(count($the_array)>0||$the_array['allowoffer']==1)
	{
		$title=$subdirs[$depth-1];
	}
	else
	{
		$title="Product Unavailable";
	}
	$crumbtitle=$breadsep.$title;
	$pagetitle=$title;
	if(isset($info['ctype'])&&isset($fields[$info['ctype']])){
		$tofilter=array();
		$filterfields="";
		foreach($fields[$info['ctype']] as $ct_title =>$ct_field)
		{
			if(!in_array($ct_field,$ignored))
			{
				$tofilter[$ct_field]=array();
				$filterfields.=",".$ct_field;
			}
		}
	}
}
else if($page!="search")
{
	$title="The Official Online Store";
	$thepage=ucwords(str_replace("_"," ",$page));
	$crumbtitle=(!isset($crumbarray[$page])||$page=="home")?"":$breadsep.$crumbarray[$page];
	//if(!isset($pgheaderarray)){$pgheaderarray=array();}
	//$pagetitle=(!isset($pgheaderarray[$page]))?$pgheaderarray["home"]:$pgheaderarray[$page];
}
else
{
	$title="The Official Online Store";
	$thepage=ucwords(str_replace("_"," ",$page));
	$crumbtitle=" $breadsep Search Results";
	
	$searchq=$db1->prepare("
	(SELECT p.`title` as title,p.`shortdesc` as tdesc,f.`vtype` as ftype,f.`fusionId` as fid,f.`iOwner_FK` as iown  
	FROM products as p JOIN fusion as f ON p.`prod_id`=f.`itemId` AND f.`vtype`='product' 
	WHERE p.`title` LIKE ? OR p.`shortdesc` LIKE ? OR p.`content` LIKE ? OR p.`sku`=? 
	GROUP BY p.`prod_id`) 
	UNION all 
	(SELECT c.`title` as title,`vSeoTitle` as tdesc,f.`vtype` as ftype,c.`cat_id` as fid,f.`iOwner_FK` as iown  
	FROM categories as c JOIN fusion as f ON c.`cat_id`=f.`itemId` AND f.`vtype`='category' 
	WHERE c.`title` LIKE ? OR c.`content` LIKE ? 
	GROUP BY c.`cat_id`) 
	ORDER BY `ftype`,`iown`
	");
	$searchq->execute(array("%".$post_arr['searchall']."%","%".$post_arr['searchall']."%","%".$post_arr['searchall']."%","%".$post_arr['searchall']."%","%".$post_arr['searchall']."%","%".$post_arr['searchall']."%"));
	$searchrows=$searchq->rowCount();
	$pagetitle=$searchrows>0?$searchrows." Records Found":"Sorry, your search returned no results, please try again";
}
$submit = isset($get_arr['submit'])?trim(htmlspecialchars($get_arr['submit'])):"";
?>
<script src="<?=$cart_path?>/cart_functions.js" type="text/javascript"></script>
<script src="./content/js/thumbnailviewer.js" type="text/javascript"></script>
<?php if($page=="payment"){?>
<script src="<?=$cart_path?>/validate.js" type="text/javascript"></script>
<?php }
if(!isset($menu)){$menu="";}
if($menu!=""){?>
<style type="text/css">
	#cart_menu ul li#menu<?=$menu?> a{background:#FFFFFF;}
	#brandsmenu,#catsmenu{display:none;}
</style>
<?php }?>

<?php if($page!="cart_products"){?><script type="text/javascript" src="<?=$cart_path?>/jquery.tools.min.js"></script><?php }?>

<div id="searchandbread">
<?php if(isset($_GET['stuff'])){?><pre><?php print_r($_SESSION);?></pre><?php }?>
<div id="bread">You are here: <a href="./shop">Shop Home</a> <?=isset($subdirs)?cart_bread():(isset($breadstring)?$breadstring:"")?></div>
</div>
<!-- MAIN MENU -->
<?php
if(isset($get_arr['cat'])){$menu=$get_arr['cat'];}
else {$menu=$page;}
?>
<div id="sidemenu">
		<div id="info">
			<div style="float:left"><img src="./content/images/main/basket<?=isset($_SESSION['cart'])&&count($_SESSION['cart'])>0?"_full":""?>.png" alt="" /></div>
			<div style="float:left;padding-left:5px;"><a href="./shop/basket" style="font-weight:bold;font-size:1.1em;line-height:1em;color:#e4e4e4">VIEW<br />BASKET</a><br />Total: &#163;<?=number_format($basket_total,2)?><?=$numincart>0?"<br />(".$numincart." item".($numincart==1?"":"s").")":""?></div>
			<div class="clear"></div>
		</div>
		
	<div style="margin-bottom:10px;padding:10px 10px 0 0;" id="brandsmenu">
	<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0){?>
	<ul id="admin">
		<li class="heading">Admin Menu</li>
		<li id="menuadmin"><a href="admin/index.php" target="_blank"><span>Load Admin</span></a></li>
		<li id="menuadmin1"><a href="admin/index.php?logout=1" target="_blank"><span>Admin Logout</span></a></li>
		<li id="menuadmin2"><a href="javascript:history.go(-1)"><span>Previous Page</span></a></li>
		<?php
					 
					if(isset($get_arr['cat'])){$editurl="catform&amp;cid=".$get_arr['cat']."";}
					else if(isset($the_array['itemId'])){$editurl="prodform&amp;pid=".$the_array['itemId']."&amp;owner=".$the_array['ownerId'];}
					
					if(isset($get_arr['cat'])||isset($get_arr['pid'])){?>
		<li id="menuadmin4"><a href="admin/index.php?p=products&amp;showing=<?=$editurl?>" target="_blank"><span>Edit This Page</span></a></li>
		<?php }?>
	</ul>
	<?php }?>
	<ul id="products_menu">
		<?php 
		$ownercat="";
		//select cats with avail prods
		//in while loop get owner cat title
		/*$cats_query=ysql_query("SELECT c.`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM (".CTABLE." as c JOIN cart_catopts as o ON c.`".CFIELDID."`=o.`cat_id` AND o.`showincart`='1') JOIN fusion as f ON c.`".CFIELDID."`=f.`itemId` AND `ownerType`='category' AND `itemType`='category' WHERE `ownerId`='0' ORDER BY f.`sorting`",CARTDB) or die(sql_error("Error"));
		while($cat=mysql_fetch_assoc($cats_query))*/
		/*$cats_query=$db1->query("SELECT c.`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM (".CTABLE." as c JOIN cart_catopts as o ON c.`".CFIELDID."`=o.`cat_id` AND o.`showincart`='1') JOIN fusion as f ON c.`".CFIELDID."`=f.`itemId` AND `ownerType`='category' AND `itemType`='category' WHERE `ownerId`='0' ORDER BY f.`sorting`");
		while($cat=$cats_query->fetch(PDO::FETCH_ASSOC))
		{
			//$topcat=cart_gettopcat($cat['ownerId']);
		//	if($ownercat!=$topcat)
			//{
				?>
				<li id="menu_<?=str_replace(" ","_",htmlspecialchars($cat['title'],ENT_QUOTES,"ISO-8859-1"))?>"><a href="index.php?p=cart_products&amp;cat=<?=$cat['cat_id']?>&amp;curpage=<?=urlencode($cat['title'])?>"><span><?=ucwords($cat['title'])?></span></a></li>
				<?php
				//$ownercat=$topcat;
			//}
		}*/
		foreach($prodscats as $main => $subs)			
		{			
			$collapsed=is_array($subs)&&count($subs)>0?0:1;
			if($h!=$main)
			{
				if(strlen($h)>0){?></ul><ul<?=$collapsed!=1?" style='padding-bottom:3px;margin-top:4px;margin-bottom:6px;'":""?>><?php }$h=$main;
				if($collapsed==1&&strlen($subs)>0){$thetitle="<a href=\"".$subs."\">".$h."</a>";}
				else if($r['subprods']>0){$thetitle="<a href='./".urlencode(urlencode(str_replace("&","-and-",$main)))."'>".$h."</a>";}
				else{$thetitle=$h;}
				?>
				<li class='heading'<?=$collapsed!=1?" style='border-bottom: 1px solid #163756;'":""?>><?=$thetitle?></li>
				<?php 
			}
			/* str_replace("&","-and-",$main) combats the issue with ampersand messing up mod rewrite urls */
			if(is_array($subs)&&count($subs)>0)
			{
				foreach($subs as $sub => $arr)
				{
					?>
					<li id="menu_<?=str_replace(" ","_",htmlspecialchars($cat['title'],ENT_QUOTES,"ISO-8859-1"))?>"><a href='./<?=urlencode(urlencode(str_replace("&","-and-",$main)))?>/<?=urlencode(urlencode(strtolower(str_replace("&","-and-",$sub))))?>'><?=ucwords($sub)?></a></li>
					<?php
				}
			}
		}
		?>
	</ul>
	
	<ul id="tools">
		<li class="heading">My Tools</li>
		<li id="menumy_account">
			<?=(isset($_SESSION['loggedin'])&&$_SESSION['loggedin']==0)?"<a href='./cart_account&amp;registerform'><span>Register an Account":"<a href='./cart_account'><span>My Account"?>
			</span></a></li>
		<li id="menushopping_basket"><a href="./shop/basket"><span>View Basket/Checkout</span></a></li>
		<?php if(!isset($_SESSION['loggedin'])||(isset($_SESSION['loggedin'])&&$_SESSION['loggedin']==0)){?>
		<li id="menucustomer_login"><a href="./cart_login&amp;to_=/cart_index"><span>Sign in</span></a></li>
		<?php }else{?>
		<li><a href="./shop&amp;logout=1"><span>Logout</span></a></li>
		<?php }?>
		<li><a href="./cart_chokeguide"><span>Choke Guide</span></a></li>
	</ul>
</div>
</div>
<!-- /MAIN MENU -->
<?php 
if(isset($_SESSION['error'])&&((is_array($_SESSION['error'])&&array_key_exists($the_array['prod_id'],$_SESSION['error']))||!is_array($_SESSION['error'])))
{ 
	if(is_array($_SESSION['error'])&&array_key_exists($the_array['prod_id'],$_SESSION['error'])&&count($_SESSION['error'])>0)//for adding prods to cart
	{
		if(!isset($errormsg)){$errormsg="";}
		$errormsg.=$_SESSION['error'][$the_array['prod_id']];
		$errorboxdisplay="display:block;";unset($_SESSION['error']);
	}
	else if(!is_array($_SESSION['error'])&&strlen($_SESSION['error'])>0)
	{
		$errormsg.=$_SESSION['error'];
		$errorboxdisplay="display:block;";unset($_SESSION['error']);
	}
}?>
<div id="cart_content">
<?php if(date("U")>strtotime("December 24 ".(date("Y")))&&date("U")<strtotime("January 2 ".(date("Y")+1))){?><img src="<?=$root_to_cart?>images/xmasbanner.jpg" alt="Merry Christmas to all of our customers, last order for dispatch on Monday 23rd December at 12pm. Normal service resumes on Thursday 2nd January 2014." /><br /><br /><?php }?>