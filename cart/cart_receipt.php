<?php include "cart_head.php";
if((!isset($_SESSION["VendorTxCode"])||strlen($_SESSION["VendorTxCode"])==0)&&(!isset($get_arr['invoice'])||(isset($get_arr['invoice'])&&$_SESSION['loggedin']==0)))
{
	?><br />Sorry, the online receipt for this transaction has expired. Please check your inbox for a copy of this receipt. You can also view your recent transactions <?=($_SESSION['loggedin']==0)?"<a href='".MAINBASE."/index.php?p=cart_login'>":"<a href='".MAINBASE."/index.php?p=cart_account'>"?>here</a>.<p>&#160;<br />&#160;</p><?php
}
else
{
//$txcode=isset($get_arr['invoice'])?"`invoice`='".$get_arr['invoice']."'":"`VendorTxCode`='".mysql_real_escape_string($_SESSION['VendorTxCode'])."'";
$txcode=isset($get_arr['invoice'])?"`invoice`=?":"`VendorTxCode`=?";
$bind=isset($get_arr['invoice'])?array($get_arr['invoice']):array($_SESSION['VendorTxCode']);
//$adbilq=ysql_query("SELECT * FROM cart_orders WHERE $txcode")or die(sql_error("Error","SELECT * FROM cart_orders WHERE $txcode<br />".mysql_error()));
$adbilq=$db1->prepare("SELECT * FROM cart_orders WHERE $txcode");
$adbilq->execute($bind);
$adbilnum=$adbilq->rowCount();
if($adbilnum>0){
$adbil=$adbilq->fetch(PDO::FETCH_ASSOC);
?>
<h2 id="pagetitle">Invoice Number: <?=$adbil['invoice']?></h2>
<div class="review">
	<div class="pg_content_left">
		<h3>Order Status</h3>
		<p class="note"><?=$adbil['order_status']?></p>
	</div>
	<div class="pg_content_right">
		<h3>Order Comments</h3>
		<p class="note">
			<?=((strlen($adbil['comments'])>0)?$adbil['comments']:"None")?>
		</p>
	</div>
</div>
<div class="review">
	<div class="pg_content_left">
		<h3>Payment Method</h3>
		<p class="note"><?=stristr($adbil['Status'],"free")===false?"Credit/Debit Card":"Free"?></p>
	</div>
	<div class="pg_content_right">
		<h3>Postage Method</h3>
		<p class="note">
			<?=$adbil['ship_description']?>
		</p>
	</div>
</div>
<div class="review">
	<div class="pg_content_left">
		<h3>Billing Address</h3>
		<address>
		<?=$adbil['firstname']?> <?=$adbil['lastname']?><br />
		<?=$adbil['address1']?><br />
		<?=strlen($adbil['address2'])>0?$adbil['address2']."<br />":""?>
		<?=$adbil['city']?><br />
		<?=cart_get_county($adbil['state'])?><br />
		<?=cart_get_country($adbil['country'])?><br />
		<?=$adbil['postcode']?><br />
		<?=$adbil['email']?><br />
		<?=$adbil['phone']?>
		</address>
	</div>
	<div class="pg_content_right">
		<h3>Delivery Address</h3>
		<address>
		<?php if($adbil['sameasbilling']==1){?>
		Same as billing address
		<?php }else{?>
		<?=$adbil['alt_name']?><br />
		<?=$adbil['alt_address1']?><br />
		<?=strlen($adbil['alt_address2'])>0?$adbil['alt_address2']."<br />":""?>
		<?=$adbil['alt_city']?><br />
		<?=cart_get_county($adbil['alt_state'])?><br />
		<?=cart_get_country($adbil['alt_country'])?><br />
		<?=$adbil['alt_postcode']?><br />
		<?=$adbil['alt_phone']?>
		<?php }?>
		</address>
	</div>
</div>
<p>&#160;</p>
<?php //cart_ordercontents("o.$txcode","100%");
cart_ordercontents("o.$txcode","100%",$bind);?>
<p id="printlink"><a onclick="window.print();return false" href="#">Print Invoice</a></p>
<p>
<?=$sitename?><br />
Bear House,<br />
Concorde Way<br />
Fareham<br />
PO15 5RL<br />
Email: <a href="mailto:<?=$admin_email?>"><?=$admin_email?></a><br />
Tel: <?=$sales_phone?><br />
Hampshire<br />
United Kingdom<br />
</p>
<p>
VAT. Registration No: <?=$vatreg?><br />
Company Registration No.: <?=$coreg?>
</p>
<p>Thank you for your order, we appreciate your custom.<br />
If you could spare a few moments, we would be very grateful if you could add review(s) of the product(s) you have purchased.
</p>
<?php }else{
?><br />Sorry, the online receipt for this transaction has expired. Please check your inbox for a copy of this receipt. You can also view your recent transactions <?=($_SESSION['loggedin']==0)?"<a href='".MAINBASE."/index.php?p=cart_login'>":"<a href='".MAINBASE."/index.php?p=cart_account'>"?>here</a>.<p>&#160;<br />&#160;</p><?php
}
}
include "cart_foot.php";?>
