<?php
date_default_timezone_set('Europe/London');
$cart_live=1;
$cart_debugmode=0;//all input/update/delete sql queries display on screen instead of being processed. and froogle.xml won't be uploaded
$allowcart=$cart_live==1||$inhouse==1||$islocal==1?1:0;
$freepostedsc="Free P&P";
$freepostid=7;
$admin_email="sales@gmk.co.uk";
$sales_email="sales@gmk.co.uk";
$sales_phone="01489 579 999";
$sales_fax="01489 579 950";
$sales_addy="GMK Ltd, Bear House, Concorde Way, Fareham, PO15 5RL";
$cart_order_email="sales@gmk.co.uk";//lechameauorders@llc-ltd.co.uk
$warranty_email="warranties@gmk.co.uk";
$sitename="GMK UK";
$postaladdy=$sitename."<br />
Bear House<br />
Concorde Way<br />
Fareham<br />
Hampshire<br />
United Kingdom<br />
PO15 5RL<br />
Email: ".$admin_email."<br />Tel: ".$sales_phone;
$webby="http://www.gmk.co.uk";
$admindir="/admin";
$root_to_cart="cart/";
$idle_minutes=30;//site user inactivity
$aidle_minutes=60;//admin user inactivity
$idletime=$idle_minutes*60;
$adminidletime=$aidle_minutes*60;
$passresetmins=$passreset_minutes*60;
$cart_paysystem="sagepay";//REMEMBER the settings for the payment system
$deadline=strtotime("12pm");//same day dispatch before Xpm
$stocklimit=5;//stock below this number considered low stock
$lastdays=7;//admin index stats show orders etc for X days
if(!isset($_SESSION['test'])){$_SESSION['test']=0;}
if(isset($_GET['test123'])){$_SESSION['test']=$_GET['test123'];}
/* IP TESTING */
$cart_ip1 = "86.188.176.166";
$cart_ip2 = "127.0.0.1";
$cart_iplocal = "192.168.7";
$basket_total=0;
$basket_qty=0;
$sub_total=0;
$discount=0;
$fusionOwn=0;
$totaldiscount=0;
$vattoadd=0;
//user's location check (viewing site from in the office or externally)
$cart_user_ip = $_SERVER['REMOTE_ADDR'];
$cart_ipbits=explode(".",$cart_user_ip);
$cart_user_ip2=$cart_ipbits[0].".".$cart_ipbits[1].".".$cart_ipbits[2];
$cart_userlocal=($cart_iplocal==$cart_user_ip2||$cart_user_ip==$cart_ip2||$cart_user_ip == $cart_ip1)?1:0;

// In house test (viewing inhouse website or live site)
$cart_server_ip=$_SERVER['SERVER_ADDR'];
$cart_sipbits=explode(".",$cart_server_ip);
$cart_server_ip2=$cart_sipbits[0].".".$cart_sipbits[1].".".$cart_sipbits[2];
$cart_inhouse=$cart_server_ip2==$cart_iplocal?1:0;

/* IP TESTING */
$mainbase=($cart_inhouse)?"http://bhweb1/gmk/public_html":"http://".$_SERVER['HTTP_HOST'];
$securebase=($cart_inhouse)?"http://bhweb1/gmk/public_html":"https://".$_SERVER['HTTP_HOST'];
$froogle_serv="";//uploads.google.com";
$froogle_user="";//"gmk";
$froogle_pass="";//"gmkHost1";
$vatreg="GB172993469";
$coreg="1026777";
							
$vat=20;//%
$uaa=!isset($uaa)?"":$uaa;
$cart_uaa=$uaa;

define("VAT",$vat);
define("SHOP_ONOFF","In Shop");

define('INHOUSE',$cart_inhouse);
define('ISLOCAL',$cart_userlocal);

define('ADMINPRODUCTS','products');
define('MAINBASE',$mainbase);
define('SECUREBASE',$securebase);

/* PRODUCT TABLE */
define('PTABLE','gmk_products');
define('PFIELDID','pid');
define('PFIELDNAME','prod_title');
define('PFIELDDESC','description');
define('PFIELDEXTRA','type');
define('PFIELDSHOW','displayed');

/* CATEGORY TABLE */
define('CTABLE','gmk_categories');
define('CFIELDID','cid');
define('CFIELDNAME','title');
define('CFIELDDESC','description');
define('CFIELDORDER','displayorder');

/* ADMINISTRATORS TABLE */
define('ATABLE','admin');
define('AFIELDID','aid');
define('AFIELDNAME','username');
define('AFIELDPASS','apassword');
define('AFIELDEMAIL','email');
define('AFIELDCREATE','date_created');
define('AFIELDLASTIN','date_lastin');
define('AFIELDSUPER','super');

define('PERPAGE','30');
define('PERROW','5');
define('MAXPGLINKS','5');

define("ISLOCALHN",(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0?1:0));
$modules=array(
"Home",
"Products",
"Products - &#34;Cart Specific&#34; section",
"Product Packages",
"Invoices",
"Customers",
"Enquiries",
"Promotions",
"Reports",
"Postage &amp; Packing",
"Admins &amp; Permissions",
"Delete Voided Orders",
"Warranties",
"Vacancies",
"News",
"Competitions",
"Brands",
"Reviews"
);
$modules_pages=array(
"home",
"products",
"cart_prods",
"cart_packages",
"cart_invoices",
"cart_customers",
"cart_enquiries",
"cart_promotions",
"cart_reports",
"cart_postage",
"cart_admins",
"cart_voids",
"cart_warranties",
"vacancies",
"news",
"comps",
"brands",
"reviews"
);
$menusection=array("main menu"=>array(14,13,15,17),"builder"=>array(1,3,7,16),"sales"=>array(4,6,5,12),"admin"=>array(8,11,9,10));

$cprefixpath=($cart_inhouse)?"W:/Website/gmk/public_html/":"/home/gmk/public_html/";
$cprefixurl=($cart_inhouse)?"../../../../gmk/public_html/":"http://www.gmk.co.uk/";
$breadsep=" &#187; ";
$cart_images_arr=array();
$cart_images_arr['variants']=array(
	"path"=>$root_to_cart."images/options/",
	"images"=>array(
		"main"=>"45x45",
		"small"=>"15x15",
		"prod"=>"421x461"
	)
);
$cart_images_arr['product']=array(
	"path"=>"content/images/products/",
	"images"=>array(
		"large"=>"421x461",
		"small"=>"219x240",
		"thumbnail"=>"70x77"
	)
);
$cart_images_arr['department']=array(
	"path"=>"content/images/products/intro/",
	"images"=>array(
		"mens"=>"274x274",
		"womens"=>"274x274",
		"unisex"=>"274x274",
		"thumb"=>"70x77"
	)
);
$fields=$fields;//array("rifle"=>array("code"=>"vskuvar","variant name"=>"vname","caliber"=>"field1","barrel"=>"field2","weight (kg)"=>"kg","rrp inc. vat"=>"price"))

$cart_imgfiletypes=array('jpg');
$postal=array("Royal Mail","Parcelforce","DHL","Yorkshire Parcels","UPS","FedEx");
$postaltracking=array(
"Royal Mail"=>"http://www.royalmail.com/portal/rm/track?trackNumber=",
"Parcelforce"=>"http://www.parcelforce.com/portal/pw/track?trackNumber=",
"DHL"=>"http://www.dhl.co.uk/content/gb/en/express/tracking.shtml?brand=DHL&amp;AWB=",
"Yorkshire Parcels"=>"",
"UPS"=>"http://wwwapps.ups.com/WebTracking/processInputRequest?tracknum=",
"FedEx"=>"http://fedex.com/Tracking?action=track&amp;cntry_code=uk&amp;tracknumber_list="
);
/* CUSTOMER FORMS */
$requireds=array();
$requireds['admindoupdate']=array("nametitle","firstname","lastname","email","address1","city","state","postcode","country");
$requireds['doupdate']=array("nametitle","firstname","lastname","email","address1","city","state","postcode","country","phone");
$requireds['dopassupdate']=array("email","password");
$requireds['doregister']=array("password1","password2","nametitle","firstname","lastname","email","address1","city","state","postcode","country","phone");
$requireds['lostpass']=array("email");
$requireds['dopassreset']=array("email","password1","password2");
$requireds['checkout_newcust']=array("email");
$requireds['checkout_registered']=array("email","pass");
$requireds['checkout_customer']=array("nametitle","firstname","lastname","address1","city","state","postcode","country","email","phone");

$fieldtitles=array("password1"=>"Password","password2"=>"Confirm Password","nametitle"=>"Title","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company","mailing"=>"Email List","deliver_nametitle"=>"Delivery address - Title","deliver_firstname"=>"Delivery address - First Name","deliver_lastname"=>"Delivery address - Last Name","deliver_address1"=>"Delivery address - Address1","deliver_city"=>"Delivery address - City","deliver_state"=>"Delivery address - County/State","deliver_postcode"=>"Delivery address - Postcode/Zip","deliver_country"=>"Delivery address - Country","deliver_phone"=>"Delivery address - Phone");
$alpha="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
define('EMAILREG','^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$');
$cart_orderstatuses=array("New"=>"New","Pending"=>"Pending","Received"=>"Received","Backorder"=>"Backorder","Shipped"=>"Dispatched","Void"=>"Void",""=>"Unprocessed");
$ranks=array("Unrated","Horrible","Poor","Fair","Good","Excellent");

//PDO - no need for connection here
//$cartdb=mysql_connect("localhost", "gmk", "cwuio745bjd", true) or die(sql_error("Error")); 
//$sel4=mysql_select_db("gmk_main",$cartdb) or die(sql_error("Error"));

//define('CARTDB',$cartdb);


/* NON EDITABLE */
$indexpage=basename($_SERVER['PHP_SELF']);
if($indexpage!="ajax.php"){
$get_arr=isset($_GET)?cart_mysql_real_extracted($_GET):array();
$post_arr=isset($_POST)?cart_mysql_real_extracted($_POST):array();
}
$getoptid=isset($get_arr['optid'])?$get_arr['optid']:"";
$submittedfrom=isset($post_arr['submittedfrom'])?$post_arr['submittedfrom']:"";
$self=$indexpage."?p=".$page;
$clean_query_string=str_replace(array("&new=1","&"),array("","&amp;"),$_SERVER['QUERY_STRING']);
$formaction=$indexpage."?".$clean_query_string;
$date=date("U");
$br=array("\r\n","<br />","\r\n");//opt out,html,plain line breaks
$contenttype=array("text/plain","text/html","text/plain");
$mailtype=array("None","HTML","Plain Text");
$errorboxdisplay="display:none";
$errorlist=array();
$daysofweek=array("monday","tuesday","wednesday","thursday","friday","saturday","sunday");

/*$freepostq=ysql_query("SELECT `status`,pmd.`description` FROM cart_postage as pm JOIN cart_postage_details as pmd ON pm.`post_id`=pmd.`post_id` WHERE pm.`post_id`='7'",CARTDB);
list($freepost,$freepostdesc)=mysql_fetch_row($freepostq);*/
$freepostq=$db1->query("SELECT `status`,pmd.`description` FROM cart_postage as pm JOIN cart_postage_details as pmd ON pm.`post_id`=pmd.`post_id` WHERE pm.`post_id`='7'");
list($freepost,$freepostdesc)=$freepostq->fetch();
$mods=array();
if(is_array($cart_uaa)&&count($cart_uaa)>0){
	/*$permsq=ysql_query("SELECT `permissions` FROM cart_admin_permissions WHERE `user_id`='".$cart_uaa['aid']."'",CARTDB);
	$perms=mysql_fetch_row($permsq);*/
	$permsq=$db1->prepare("SELECT `permissions` FROM cart_admin_permissions WHERE `user_id`=?");
	$permsq->execute(array($cart_uaa['aid']));
	$perms=$permsq->fetch(PDO::FETCH_NUM);
	$mods=explode(",",$perms[0]);
}
$noaccessmsg="Sorry you are not authorized to view this module.";
$higherr=array();
$bankhols=array(
"2-5-2016"=>"Early May Bank Holiday",
"30-5-2016"=>"Spring Bank Holiday",
"29-8-2016"=>"Summer Bank Holiday",
"24-12-2016"=>"Christmas Shutdown",
"25-12-2016"=>"Christmas Day",
"26-12-2016"=>"Boxing Day",
"27-12-2016"=>"Christmas Day substitute",
"28-12-2016"=>"Christmas Shutdown",
"29-12-2016"=>"Christmas Shutdown",
"30-12-2016"=>"Christmas Shutdown",
"14-4-2017"=>"Good Friday",
"17-4-2017"=>"Easter Monday",
"1-5-2017"=>"Early May Bank Holiday",
"29-5-2017"=>"Spring Bank Holiday",
"28-8-2017"=>"Summer Bank Holiday"
);
?>