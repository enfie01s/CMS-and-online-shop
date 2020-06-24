<?php if(basename($_SERVER['PHP_SELF'])!="index.php"&&$_SERVER['REQUEST_METHOD']!="POST"){die("Access Denied");}//direct access security 
$strCart=$_SESSION['cart'];
$co=isset($get_arr['co'])?$get_arr['co']:"";
if((!is_array($strCart)||count($strCart)==0) && $co=="payment")
{
	cart_redirection(MAINBASE."/cart_basket");
	exit();
}
if($co=="payment"){
$breadstring=$breadsep."<a href='./cart_basket'>Shopping Basket</a>".$breadsep."<a href='./cart_co_address'>Billing &amp; delivery address</a>".$breadsep."<a href='./cart_co_postage'>Postage information</a>".$breadsep."<a href='./cart_co_review'>Review Order</a>".$breadsep."Payment Details";
}
include "cart_head.php";
?>
<h2 id="pagetitle">Payment Details</h2>
<div id="errorbox" style=" <?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div>
<?php
if(strlen($co)>0){
include $cart_paysystem."/".$co.".php";
}
include "cart_foot.php";
?>