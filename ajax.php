<?php
session_name('gmk');
session_start();
include "../includes.php";
include "../ipcheck.php";
include "./admin/asession.php";
include "./cart/cart_vars.php";
$prodmod=array_search("products",$modules_pages);
$mods=array();
if(is_array($uaa)&&count($uaa)>0){
	/*$permsq=ysql_query("SELECT `permissions` FROM cart_admin_permissions WHERE `user_id`='".$cart_uaa['aid']."'",CARTDB);
	$perms=mysql_fetch_row($permsq);*/
	$permsq=$db1->prepare("SELECT `permissions` FROM cart_admin_permissions WHERE `user_id`=?");
	$permsq->execute(array($uaa['aid']));
	$perms=$permsq->fetch(PDO::FETCH_NUM);
	$mods=explode(",",$perms[0]);
}
if(isset($_GET['warrantyserial'])||isset($_GET['warrantybrand']))
{
	if(isset($_GET['warrantyserial']))
	{
		//$serialsQ=ysql_query("SELECT serial,Brand,Description FROM gmkserialnums WHERE serial='".mysql_real_escape_string($_GET['warrantyserial'])."' AND CHAR_LENGTH(serial)>0",CON1);
		$serialsQ=$db1->prepare("SELECT serial,Brand,Description FROM gmkserialnums WHERE serial=:wserial AND CHAR_LENGTH(serial)>0");
		$serialsQ->bindValue(':wserial',$_GET['warrantyserial']);
		$serialsQ->execute();
	}
	else
	{
		$serbra=explode("][",$_GET['warrantybrand']);
		//$ss=strlen($serbra[1])>0?" AND serial='".mysql_real_escape_string($serbra[1])."'":"";
		//$serialsQ=ysql_query("SELECT serial,Brand,Description FROM gmkserialnums WHERE brand='".mysql_real_escape_string($serbra[0])."'{$ss} AND CHAR_LENGTH(serial)>0",CON1);
		$ss=strlen($serbra[1])>0?" AND serial=:wserial":"";
		$serialsQ=$db1->prepare("SELECT serial,Brand,Description FROM gmkserialnums WHERE brand=:brand{$ss} AND CHAR_LENGTH(serial)>0");
		if(strlen($serbra[1])>0){$serialsQ->bindValue(':wserial',$serbra[1]);}
		$serialsQ->bindValue(':brand',$serbra[0]);
		$serialsQ->execute();
	}
	$n=0;
	$s="";
	//while($serial=mysql_fetch_row($serialsQ))
	while($serial=$serialsQ->fetch())
	{
		if($n==0){$b=$serial[1];$p=$serial[2];}
		if(strlen($s)>0){$s.="|";}
		$s.=$serial[0];
		$n++;
	}
	if($n==0){echo "error";}
	else{echo $b."][".$p."][".$s;}
	
}
else if(isset($_GET['catorder'])&&isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0&&in_array($prodmod,$mods))
{
	$o=explode("][",$_GET['catorder']);//pos#owner#dir
	$sel=$db1->prepare("SELECT * FROM gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.itemId AND t2.itemType='category' AND t2.ownerType='category' WHERE t2.ownerId=? AND t1.visible=1 ORDER BY displayorder");
	$sel->execute(array($o[1]));
	$swapwith=$o[2]=="down"?$o[0]-1:$o[0]+1;
	$x=1;
	while($c=$sel->fetch())
	{
		$leave=array();
		$q=$db1->prepare("UPDATE gmk_categories SET displayorder=? WHERE cid=?");
		if($x==$o[0])
		{$q->execute(array($swapwith,$c['cid']));$leave[]=$c['cid'];}
		if($x==$swapwith)
		{$q->execute(array($o[0],$c['cid']));$leave[]=$c['cid'];}
		if(!in_array($c['cid'],$leave))
		{$q->execute(array($x,$c['cid']));}
		$x++;
	}
}
else if(isset($_GET['prodorder'])&&isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0&&in_array($prodmod,$mods))
{
	$o=explode("][",$_GET['prodorder']);//pos#owner#dir
	$sel=$db1->prepare("SELECT * FROM gmk_products as t1 JOIN fusion as t2 ON t1.pid=t2.itemId AND t2.itemType='product' AND t2.ownerType='category' WHERE t2.ownerId=? AND t1.displayed=1 ORDER BY sorting");
	$sel->execute(array($o[1]));
	$swapwith=$o[2]=="down"?$o[0]-1:$o[0]+1;
	$x=1;
	while($c=$sel->fetch())
	{
		$leave=array();
		$q=$db1->prepare("UPDATE fusion SET sorting=? WHERE fusionId=?");
		if($x==$o[0])//eg 2
		{$q->execute(array($swapwith,$c['fusionId']));$leave[]=$c['fusionId'];}
		if($x==$swapwith)//eg 3
		{$q->execute(array($o[0],$c['fusionId']));$leave[]=$c['fusionId'];}
		if(!in_array($c['fusionId'],$leave))//eg 1,4,5,6
		{$q->execute(array($x,$c['fusionId']));}
		$x++;
	}
	echo $swapwith;
}
?>