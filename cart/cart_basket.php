<?php
if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security

/* CART MANIPULATION */
if(isset($post_arr['identifier'])&&$post_arr['identifier']=="update_cart")
{
	//$ids_in_cart=whats_in_cart(1);
	$skuvar_count=whats_in_cart(0);
	if($post_arr['mode']=="Update Basket"/*!in_array($post_arr['mode'],array("Empty Basket","Apply","Start Checkout"))*/)
	{
		$errormsg="Sorry, not all of your items could be updated due to stock availability, see below:<br />";
		foreach($post_arr['qty'] as $id => $qty)//each cart item
		{
			if($_SESSION['cart'][$id]['qty']!=$qty)
			{
				if($qty>0)
				{
					$skuvars="";
					$newqty=0;
					$ok_to_add=1;//set the var
					foreach($_SESSION['cart'][$id]['skuvariant'] as $itemid => $itemsku)
					{
						$cleansku=explode("-qty-",$itemsku);
						if($ok_to_add!=0)//stop as soon as we find stock unavailable
						{
							$newqty=$skuvar_count[$cleansku[0]]-($_SESSION['cart'][$id]['qty']*$cleansku[1])+($qty*$cleansku[1]);//total minus this cart item qty plus new posted qty
							$ok_to_add=($newqty<=cart_checkstock($cleansku[0]))?1:0;//enough stock?, can we continue?
						}
						$skuvars.=(($skuvars!="")?",":"")."'".$cleansku[0]."'";
					}
					/*$variantlist=(count($_SESSION['cart'][$id]['skuvariant'])>1)?"IN($skuvars)":"=$skuvars";
					$uquery=$_SESSION['cart'][$id]['ispack']!=1?"SELECT p.`".PFIELDNAME."` as title,cv.`vname` as item_desc FROM (".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid`) JOIN cart_variants as cv ON cv.`pid`=p.`pid` WHERE cv.`vskuvar` $variantlist":"SELECT p.`".PFIELDNAME."` as title,cv.`vname` as item_desc FROM ((".PTABLE." as p JOIN cart_kits as ck ON ck.`ownerId`=p.`".PFIELDID."`)JOIN cart_fusion as cf ON ck.`itemId`=cf.`pid`) JOIN cart_variants as cv ON cv.`pid`=p.`pid` WHERE p.`".PFIELDID."`='".$_SESSION['cart'][$id]['prod_id']."' AND cv.`vskuvar` $variantlist";
					$qty_chkq=ysql_query($uquery,CARTDB);
					$prod=mysql_fetch_assoc($qty_chkq);*/
					$binds=array();
					if($_SESSION['cart'][$id]['ispack']!=1)
					{
						$binds[]=$_SESSION['cart'][$id]['prod_id'];
					}
					if(count($_SESSION['cart'][$id]['skuvariant'])>1)
					{
						$ins=bindIns($skuvars);
						$variantlist="IN(".$ins[0].")";
						$binds=array_merge($binds,$ins[1]);
					}
					else
					{
						$binds[]=$skuvars;
						$variantlist="=?";
					}
					
					if($_SESSION['cart'][$id]['ispack']!=1)
					{
						$uquery="SELECT p.`".PFIELDNAME."` as title,cv.`vname` as item_desc FROM (".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid`) JOIN cart_variants as cv ON cv.`pid`=p.`pid` WHERE cv.`vskuvar` $variantlist";
					}
					else
					{
						$uquery="SELECT p.`".PFIELDNAME."` as title,cv.`vname` as item_desc FROM ((".PTABLE." as p JOIN cart_kits as ck ON ck.`ownerId`=p.`".PFIELDID."`)JOIN cart_fusion as cf ON ck.`itemId`=cf.`pid`) JOIN cart_variants as cv ON cv.`pid`=p.`pid` WHERE p.`".PFIELDID."`=? AND cv.`vskuvar` $variantlist";
					}
					$qty_chkq=$db1->prepare($uquery);
					$prod=$qty_chkq->fetch(PDO::FETCH_ASSOC);
					if($ok_to_add==0)
					{
						if($_SESSION['cart'][$id]['qty']!=$qty)
						{
							$errormsg .= "&bull; ".ucwords($prod['title']).($_SESSION['cart'][$id]['ispack']!=1?" ($prod[item_desc])":": not enough stock or one or more of the pack contents.")."<br />";
						}
						$errorboxdisplay="display:block;";
					}
					else{$_SESSION['cart'][$id]["qty"]=$qty;}
				}
				else{unset($_SESSION['cart'][$id]);}//qty = 0 (remove item)
			}
		}
		if($errormsg=="Sorry, not all of your items could be updated due to stock availability, see below:<br />"){unset($errormsg);$errorboxdisplay="display:none;";}
	}
	else if($post_arr['mode']=="Apply Discount")
	{
		$discountinfo=cart_discount_check($post_arr['discount'],$post_arr['basket_total']);
	}
	else if($post_arr['mode']=="Empty Basket")
	{
		if(isset($_SESSION['cart'])){unset($_SESSION['cart']);unset($_SESSION['added']);unset($_SESSION['discount_code']);unset($_SESSION['discount_amount']);unset($_SESSION['discount_list']);}
	}
}
if(isset($_GET['remove_item'])&&$_GET['remove_item']!="")
{	
	$rcart_ids=array();
	if(isset($_SESSION['cart'][$_GET['remove_item']])){
		unset($_SESSION['cart'][$_GET['remove_item']]);
		foreach($_SESSION['cart'] as $rid => $rcartitems){if(!in_array($rcartitems['prod_id'],$rcart_ids)){array_push($rcart_ids,$rcartitems['prod_id']);}}
		foreach($_SESSION['cart'] as $id => $cartitems)
		{
			$allowedmatches=!is_array($cartitems['allowlist'])||count($cartitems['allowlist'])<1?1:count(array_intersect($cartitems['allowlist'],$rcart_ids));
			if($allowedmatches<1)
			{unset($_SESSION['cart'][$id]);}
		}
	}
}
/* CART MANIPULATION */
$breadstring=$breadsep."Shopping Basket";
include "cart_head.php";

if(!isset($_SESSION['backto'])){$_SESSION['backto']=str_replace("&","&amp;",$_SERVER['HTTP_REFERER']);}//for continue shopping
?><h2 id="pagetitle">Shopping Basket</h2>
<div id="errorbox" style=" <?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div><?php
if(isset($_SESSION['cart'])&&count($_SESSION['cart'])>0&&is_array($_SESSION['cart']))
{
	//print_r($_SESSION['cart']);
	?>
	<form action="./cart_basket" method="post">
	<input type="hidden" name="identifier" value="update_cart" />
	<input type="hidden" name="backto" value="<?=$_SESSION['backto']?>" />
	<input type="hidden" name="checkout" value="<?=(($_SESSION['loggedin']!=0)?SECUREBASE."/cart_co_address":SECUREBASE."/cart_login&amp;to_=/cart_co_address")?>" />
	<?php 
	cart_contents(1);
	/*$added_sugsq=ysql_query("SELECT * FROM (fusion as f JOIN ".PTABLE." as p ON f.`itemId`=p.`".PFIELDID."`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` WHERE f.`ownerId` IN('".implode("','",$cart_ids)."') AND p.`".PFIELDID."` NOT IN('".implode("','",$cart_ids)."') AND `itemType`='product' AND f.`ownerType`='product' AND cf.`allowoffer`='1' ORDER BY f.`sorting`",CARTDB);
	$added_sugs=mysql_num_rows($added_sugsq);*/
	$ins=bindIns(implode(",",$cart_ids));
	$added_sugsq=$db1->prepare("SELECT * FROM (fusion as f JOIN ".PTABLE." as p ON f.`itemId`=p.`".PFIELDID."`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` WHERE f.`ownerId` IN('".$ins[0]."') AND p.`".PFIELDID."` NOT IN('".$ins[0]."') AND `itemType`='product' AND f.`ownerType`='product' AND cf.`allowoffer`='1' ORDER BY f.`sorting`");
	$added_sugsq->execute($ins[1]);
	$added_sugs=$added_sugsq->rowCount();
	if($added_sugs>0){?>
	<div><a href="./shop/basket&amp;offerprod=1">View additional product suggestions</a> (exclusively in conjunction with products you have in your basket).</div>
	<?php }
	if(isset($_GET['offerprod']))
	{
		cart_prodlist("SELECT * FROM (fusion as f JOIN ".PTABLE." as p ON f.`itemId`=p.`".PFIELDID."`) JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."` WHERE f.`ownerId` IN('".$ins[0]."') AND `".PFIELDID."` NOT IN('".$ins[0]."') AND `itemType`='product' AND `ownerType`='product' AND `allowoffer`='1' ORDER BY f.`iSort`","","We have found these additional suggestions for you.","offerprods",1,$ins[1]);}
	?>
	<p style="float:left"><img src="<?=$cart_path?>/images/mastercard.gif" alt="Mastercard" /> <img src="<?=$cart_path?>/images/visa.gif" alt="Visa" /> <img src="<?=$cart_path?>/images/maestro.gif" alt="Maestro" /> <img src="<?=$cart_path?>/images/solo.gif" alt="Solo" /></p>
	<input type="hidden" name="basket_total" value="<?=number_format($sub_total+$vattoadd,2)?>" />
	<p style="float:right"><strong>Discount Code:</strong> <input type="text" name="discount" value="" /> <input type="submit" name="mode" class="formbutton" value="Apply Discount" /><?=$discountinfo?></p>
	<p class="actions" style="clear:both">
	<input name="mode" value="Update Basket" type="submit" class="formbutton" /> <input name="mode" value="Empty Basket" type="submit" class="formbutton" id="emptybasket" /> <input name="mode" value="Start Checkout" type="submit" class="formbutton" />
	</p>
	</form>
	<script>
	//<[CDATA[
	$('#emptybasket').click(function() {
		if(confirm('Are you sure you want to empty the basket?')) return true;else return false;
	}
	)
	//]]>
	</script>
	<?php
}
else
{
	?>Your shopping basket is empty, <a href="./shop">please return to the shop front.</a><p>&#160;<br />&#160;</p><?php
}
unset($_SESSION['backto']);
include "cart_foot.php";
?>