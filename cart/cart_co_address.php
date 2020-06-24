<?php if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
$tomatch=$_SESSION['loggedin']!=0?$ua:(isset($post_arr['identifier'])?$post_arr:(isset($_SESSION['address_details']['delivery'])&&count($_SESSION['address_details']['delivery'])>0?$_SESSION['address_details']['delivery']:""));
$strCart=$_SESSION["cart"];

if (!is_array($_SESSION["cart"])||count($_SESSION["cart"])==0) 
{
	cart_redirection("./cart_basket");
	exit();
}
$breadstring=$breadsep."<a href='./cart_basket'>Shopping Basket</a>".$breadsep."Billing &amp; delivery address";
include "cart_head.php";
//print_r($_POST);
//print_r($requireds['doregister']);
//print_r($_SESSION);
?>
<h2 id="pagetitle">Billing &amp; delivery address</h2>
<div id="errorbox" style=" <?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div>
<form action="./cart_co_address" method="post">
<input type="hidden" name="identifier" value="checkout_customer" />
<?php if($_SESSION['loggedin']==0){?>
<p>If you wish to register on our site then please enter an account password. Registration enables you to access the My Account section of our site to update your details, view your invoice history and status, setup a wish list or signup to our mailing list.</p>
<table class="details">
<tr class="head"><td colspan="2"><div class="titles">Registration - optional</div></td></tr>
<?php cart_formrows(array("password1"=>"Password","password2"=>"Confirm Password"),array(),array(),array(),array(),"","registerpass");?>
<tr class="row_light"><td colspan="2">
<input type="hidden" name="opt_in" value="0" /><input type="checkbox" name="opt_in" id="opt_in" value="1" checked="checked" /><label for="opt_in"> I would like to receive updates and special offers from GMK</label></td></tr>
</table>
<br />
<?php }else{?>
<p>If you are not <?=$ua['firstname']." ".$ua['lastname']?>, <a href="./cart_login&amp;logout=1">please sign in or register.</a></p>
<?php }?>
All fields marked (*) are required
<table class="details">
<tr class="head"><td colspan="2"><div class="titles">Billing address</div></td></tr>
<?php cart_formrows(array("nametitle"=>"Title","firstname"=>"First Name","lastname"=>"Last Name","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","email"=>"Email Address","phone"=>"Telephone","homepage"=>"Website","company"=>"Company"),$requireds['doregister'],array("state"=>"SELECT `county_id`,`countyname` FROM cart_counties ORDER BY `countyname` ASC","country"=>"SELECT `cshortname`,`countryname`,`country_id` FROM cart_countries ORDER BY `countryname` ASC"),array(),array(),$tomatch,"billingaddy");?>
</table>
<br />
<table class="details">
<tr class="head"><td colspan="2"><div class="titles">Delivery address</div></td></tr>
<?php $prefix="deliver_";cart_formrows(array("matchbilling"=>"Same as billing",$prefix."nametitle"=>"Title",$prefix."firstname"=>"First Name",$prefix."lastname"=>"Last Name",$prefix."address1"=>"Address 1",$prefix."address2"=>"Address 2",$prefix."city"=>"City",$prefix."state"=>"County/State",$prefix."postcode"=>"Postcode/Zip",$prefix."country"=>"Country",$prefix."phone"=>"Telephone"),$requireds['doregister'],array($prefix."state"=>"SELECT `county_id`,`countyname` FROM cart_counties ORDER BY `countyname` ASC",$prefix."country"=>"SELECT `cshortname`,`countryname`,`country_id` FROM cart_countries ORDER BY `countryname` ASC"),array(),array("matchbilling"=>"1: "),$tomatch,$prefix);?>
</table>
<input type="checkbox" name="terms_agree" id="terms_agree" value="1" /><label for="terms_agree"> I agree to the <a href="./cart_terms" target="_blank">Terms &amp; Conditions</a></label>
<br />
<br />
<input type="submit" name="checkout_customer" value="Continue" class="formbutton" />
</form>
<script src="<?=SECUREBASE?>/content/js/countrycodes.js" type="text/javascript"></script>
<?php include "cart_foot.php";?>