<?php
session_name('gmk');
session_start();
header('Content-Type: text/html; charset=utf-8');
header('X-UA-Compatible: IE=edge');
$charset="utf-8";//"ISO-8859-1";
date_default_timezone_set("Europe/London");

/*
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
*/
//header('Content-Type: text/html; charset='.$charset);
include "../includes.php";
include "../ipcheck.php";
/*if(stristr($_SERVER['HTTP_HOST'],"www.")===false){	
	$gowhere="https://www.".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
	header("Location: ".$gowhere);
}*/
//if(!stristr($_SERVER['HTTP_HOST'],"www.")&&$inhouse==0){header("Location: ".str_replace("://","://www.",$_SERVER['SERVER_NAME']).$_SERVER['PHP_SELF']);}
include "admin/functions.php";
include "admin/vars.php";
include "content/brochures/config.php"; 
/* cart items */
$cart_path="./cart";//relative path to cart
$cart_adminpath=$cart_path."/admin/";

include $cart_path."/cart_functions.php";
include "./admin/asession.php";
$mods=array();
if(is_array($uaa)&&count($uaa)>0){
	/*$permsq=ysql_query("SELECT `permissions` FROM cart_admin_permissions WHERE `user_id`='".$cart_uaa['aid']."'",CARTDB);
	$perms=mysql_fetch_row($permsq);*/
	$permsq=$db1->prepare("SELECT `permissions` FROM cart_admin_permissions WHERE `user_id`=?");
	$permsq->execute(array($uaa['aid']));
	$perms=$permsq->fetch(PDO::FETCH_NUM);
	$mods=explode(",",$perms[0]);
}
if($allowcart)
{
	set_include_path($cart_path);  
	include "cart_usession.php";
}
$coopath=$_SERVER['HTTP_HOST']=="bhweb2"?'/gmk':'/';
if(isset($_GET['cookieinfo'])){
	setcookie('cookies','ok',time()+86400*90,$coopath);
	//header("Location: index.php");	
}
/* cart items */
//Set up some items
include "content/functions.php";
require_once "content/Mobile_Detect.php";
$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
//$deviceType = 'computer';
$ismsie = stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE')!==false ? 1 : 0;
//$ismsie=preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT'])==1?1:0;
$isopera = stripos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false ? 1 : 0;

$mouseevents=isset($_SESSION['cart'])&&count($_SESSION['cart'])>0&&is_array($_SESSION['cart'])?"onmouseover=\"divroller('basketsummary','mouseover');\" onmouseout=\"divroller('basketsummary','mouseout');\"":"";

//Page HTML

$submitagain = (!isset($_SESSION['submitagain']) || $_SESSION['submitagain'] <= date("U")) ? 1 : 0;
$formsubmitdelay = 10;
$required = array("serial","brand","product","purchasedate","fromshop","nametitle","firstname","lastname","address1","city","county","postcode","email","brochure","enquirytype","comments","skuvariant[358]","termsagree","areaofinterest","brand");
$warrantyarray = array("serial"=>"Serial No","brand"=>"Brand","product"=>"Product","purchasedate"=>"Date Purchased","fromshop"=>"Purchased From","nametitle"=>"Title","firstname"=>"First Name","lastname"=>"Last Name","address1"=>"Address Line 1","address2"=>"Address Line 2","city"=>"Town / City","county"=>"County","postcode"=>"Postcode","telephone"=>"Telephone","email"=>"Email Address","skuvariant[358]"=>"Warranty Length","areaofinterest"=>"Your area of interest","mailinglist"=>"Receive information via Email?**","termsagree"=>"I confirm I have read and agree to the <a href='./warranty_terms' target='_blank'>terms &amp; conditions</a>");
$maxlengths = array("serial"=>"22","product"=>"40","fromshop"=>"50","title"=>"4","firstname"=>"40","lastname"=>"40","address1"=>"30","address2"=>"30","address3"=>"30","city"=>"16","postcode"=>"8","telephone"=>"16","email"=>"50","skuvariant[358]"=>"10");
$newsarray = array();

$q="SELECT * FROM `gmknews` WHERE `display`='T' AND `date`<= ".date("U")." ORDER BY `date` DESC, `id` DESC";

$result = $db1->query($q);
$totalnews = $result->rowCount();
//$query = ysql_query($q,$con1) or die(mysql_error());
///$totalnews = mysql_num_rows($query);

$x=0;
//while($result = mysql_fetch_assoc($query))
while($resulta=$result->fetch(PDO::FETCH_ASSOC))
{
	$newsarray[$x] = $resulta;
	$x++;
}
$seo_title="";
$get_titles=array("viewing","title","prodname");$get_titles=array_flip($get_titles);
$get_title=array_intersect_key($get_titles,$_GET);
if(count($get_title)>0)
{
	$get_title=array_keys($get_title);
	$seo_title=$get_title[0];
}
$title=isset($_GET[$seo_title])&&strlen($_GET[$seo_title])>0?urldecode($_GET[$seo_title]):$title;
$title=$page=="cart_receipt"?"Receipt":$title;
$slider=1;//isset($_GET['slider'])?1:0;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><base href="/<?=$_SERVER['HTTP_HOST']!='gmk.co.uk'?"reference/public_html/":""?>"><!-- needed for relative paths to work -->
<!--[if IE]><script type="text/javascript">
    // Fix for IE ignoring relative base tags.
    (function() {
        var baseTag = document.getElementsByTagName('base')[0];
        baseTag.href = baseTag.href;
    })();
</script><![endif]-->
<?php if($page=="about")
{
	?><script src='https://www.google.com/recaptcha/api.js'></script><?php
}?>
<meta charset="<?=$charset?>" /> 
<title>GMK - <?=ucwords($title)?></title>
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="initial-scale=1.2, user-scalable=no, minimum-scale=1.0, maximum-scale=5.0" /> 

<link rel="stylesheet" href="./content/stylesheets/lightbox.css" type="text/css" media="screen" />

<?php 
if($page=="map")
{
	$thisbrand=cleanBrand($_GET['brand']);
$thiscounty = urldecode($_GET['county']);
if($thiscounty == "London"){
	$docity=" OR ((COALESCE(`County`,0)='0' OR `County`='') AND `City`='London'))";
}
else if(strlen($thisbrand) > 2 and strlen($thiscounty) > 3){$docity=")";}

if($thisbrand == "all dealers" and $thiscounty == "all counties"){$where = "";}
else if(strlen($thisbrand) > 2 and $thiscounty == "all counties"){$where = "AND ((".$thisbrand . "='Y')";}
else if($thisbrand == "all dealers" and strlen($thiscounty) > 3){$where = "AND (`beretta_guns`='Y' OR `beretta_sport`='Y' OR `beretta_premium`='Y' OR `beretta_clothing`='Y' OR `franchi`='Y' OR `sako`='Y' OR `sako_ammo`='Y' OR `leupold`='Y' OR `tikka`='Y' OR `benelli`='Y' OR `benelli_premium`='Y' OR `lanber`='Y' OR `arrieta`='Y' OR `atk`='Y' OR `burris`='Y' OR `accessories`='Y' OR `stoeger`='Y' OR `steiner`='Y') AND (`County`=:county";}
else if(strlen($thisbrand) > 2 and strlen($thiscounty) > 3){$where = "AND (" . $thisbrand . "='Y') AND (`County`=:county";}

$query = "SELECT * FROM dealerlistings as dl LEFT JOIN dealerlistings_latlng as dll ON dl.`accountid`=dll.`acid` WHERE `GMK`='Y' $where $docity ORDER BY `Account` ASC";
	$mapresult = $db2->prepare($query);
	if(strlen($thiscounty)>0&&$thiscounty != "all counties"){$mapresult->bindValue(':county', $thiscounty);}
	$mapresult->execute();
	//$result = ysql_query($query,$con2) or die(sql_error("Error",$query));
	//list($lat,$lng) = mysql_fetch_array( $result );
/* GEOCODES */

$curcountylatlng=geocode($thiscounty.",UK");
/* GEOCODES */
	?>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&amp;region=GB"></script>
<script type="text/javascript">
/* <![CDATA[ */
var map;
var markersArray = [];
var dealers=[];
var curCounty = new google.maps.LatLng(<?=$curcountylatlng[0]?>, <?=$curcountylatlng[1]?>);
function initialize() {
  var mapOptions = {
    zoom: 10,
    center: curCounty,
		streetViewControl: false,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  map =  new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
	showOverlays();
}

function addMarker(location,image,titlename,infocontent) {
  location = new google.maps.Marker({
    position: location,
    map: map,
		icon: image,
		title: titlename
  });
	google.maps.event.addListener(location, 'click', function(event) {
		infowindow.setContent(infocontent);
		//infowindow.setPosition();
		infowindow.open(map,location);
	});
	markersArray.push(location);
}

var infowindow = new google.maps.InfoWindow();

// Shows any overlays currently in the array
function showOverlays() {
  if (markersArray) {
    for (i in markersArray) {
			if(typeof(markersArray[i]['title'])!=="undefined")
				markersArray[i].setMap(map);
    }
  }
}

/* ]]> */
</script>
<?php 
$i=1;
while($info = $mapresult->fetch())  
{ //http://maps.googleapis.com/maps/api/geocode/xml?address=Aberdeenshire,UK&sensor=false
	mapmarkers($info,$i);
	$i++;
}
}
?>
<script type="text/javascript">
	
	<!--
	var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],windowx=w.innerWidth||e.clientWidth||g.clientWidth,windowy=w.innerHeight||e.clientHeight||g.clientHeight;
	
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-1536461-1', 'auto');
  ga('send', 'pageview');

	//-->
</script>
<?php 
$_SESSION['dt']=isset($_GET['dt'])?$_GET['dt']:(isset($_SESSION['dt'])?$_SESSION['dt']:$deviceType);
$deviceType=$_SESSION['dt'];
?>
<link rel="stylesheet" href="./content/stylesheets/style_print.css" type="text/css" media="print" />
<link rel="stylesheet" href="./content/stylesheets/style<?=$deviceType=="phone"?"_phone":""?>.css" type="text/css" />
<?php if(stristr($page,"cart_")){?>
<link rel="stylesheet" href="<?=$cart_path?>/cart_style<?=$deviceType=="phone"?"_phone":""?>.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?=$cart_path?>/cart_print.css" type="text/css" media="print" />
<?php }?>
<link rel="alternate" type="application/rss+xml" title="GMK News Feed" href="feed://www.gmk.co.uk/newsfeed.php" />
<meta name="keywords" content="gmk, beretta, benelli, lanber, arrieta, sako, tikka T3, tikka, atk, rcbs, cci, speer, federal, burris, leupold, beretta premium, franchi, shotgun, rifle, ammunition, redfield, pigeon, pidgeon, ammo, silver pigeon, gun spares, pads, spacers, choke, chokes, sights, geltek, mobilchoke,riflescope, rangefinder" />
<meta name="description" content="GMK are the UK's leading shooting sports distributor." />
<meta name="robots" content="all" />
<meta name="rating" content="general" />
<!-- jQuery lightBox plugin -->
<?php if(in_array($page,array("products","productdetail","cart_products"))){?>
<script type="text/javascript" src="./content/js/jquery.js"></script>
<script src="./content/js/jquery.imageLens.js" type="text/javascript"></script>
<script type="text/javascript" src="./content/js/jquery.lightbox-0.5.js"></script>
<link rel="stylesheet" type="text/css" href="./content/stylesheets/jquery.lightbox-0.5.css" media="screen" />
<script type="text/javascript">
	isIE=<?=$ismsie?>;
	var lensLeftSize=isIE?5:65;//was 115:165 - maybe online vs local issue?
	$(function() {
			$('.lbox a').lightBox({gmkPath: '<?=$mainbase?>'});
	});
	$(function () {
		$("#img_02").imageLens({lensSize: 200, borderSize: 0, lensCss: 'lens', lensTop:190, lensLeft: lensLeftSize });/* left is width of side menu */
	});	
</script>
<?php }?>
<!-- / jQuery lightBox plugin -->

<?php 
if($page=="warranty")
{
	?>
	<script src="./content/js/jquery-selectize.js"></script>
	<script type="text/javascript" src="./content/js/selectize.js"></script>
	<link href="./content/js/selectize.bootstrap3.css" rel="stylesheet">
	<?php
}
if(0/*in_array($page,array("warranty","cart_account","cart_co_address"))*/)
{
	if($page=="warranty"){$pafkey='AW69-ZU12-KA27-UA39';}
	if($page=="cart_account"){$pafkey='PJ41-TE48-DP39-HW68';}
	if($page=="cart_co_address"){$pafkey='NU84-PW79-DA93-ZT11';}
	//test PC: WR2 6NJ
	?>
	<link rel="stylesheet" type="text/css" href="http://services.postcodeanywhere.co.uk/css/captureplus-2.00.min.css?key=<?=$pafkey?>" /><script type="text/javascript" src="http://services.postcodeanywhere.co.uk/js/captureplus-2.00.min.js?key=<?=$pafkey?>"></script>
	<?php 
}?>
<!--<meta http-equiv="X-UA-Compatible" content="IE=edge" />-->
</head>

<!---gmk, beretta, benelli, lanber, arrieta, sako, tikka T3, atk, rcbs, cci, speer, federal, burris, leupold, beretta premium, franchi, club accuracy, shotgun, rifle, ammunition-->
<!--initialize() is for the google map -->
<body<?php if($page=="map"){?> onLoad="initialize()"<?php }?>>

<!--<div class="notice">Website currently down for maintenance, please come back in 5 minutes</div>-->
<?php
## make array of all categories & products up to 4 levels deep ##
$prodscats=array();
if(stristr($page,"cart_"))
{
$q=$db1->query("SELECT t1.prod_title as title1,t1.pid as id1,t4.title as title2,t4.cid as id2,t7.title as title3,t7.cid as id3,'' as title4,0 as id4,t9.sorting as topord,t6.sorting as midord,t3.sorting as midord1,0 as midord2,'' as url,t7.layout as layout,t4.header as header,t4.logo as logo,t4.subdesc as subscrip,t4.description as catscrip,t4.ctype as ctype,t1.bigimage as bigimage,t1.description as proddesc,t1.lhimg as lhimg,t4.col1 as col1,t4.col2 as col2,(SELECT MIN(`price`) FROM cart_variants as t10 WHERE t1.pid=t10.pid) as price,t2.salediscount,t2.saletype,(SELECT GROUP_CONCAT(DISTINCT(t11.field1) SEPARATOR '##') FROM cart_variants as t11 WHERE t1.pid=t11.pid AND CHAR_LENGTH(t11.`field1`)>0 ORDER BY t11.field1) as field1,(SELECT GROUP_CONCAT(DISTINCT(t12.field2) SEPARATOR '##') FROM cart_variants as t12 WHERE t1.pid=t12.pid AND CHAR_LENGTH(t12.`field2`)>0 ORDER BY t12.field2) as field2,(SELECT GROUP_CONCAT(DISTINCT(t13.field3) SEPARATOR '##') FROM cart_variants as t13 WHERE t1.pid=t13.pid AND CHAR_LENGTH(t13.`field3`)>0 GROUP BY t13.field3) as field3,(SELECT GROUP_CONCAT(DISTINCT(t14.field4) SEPARATOR '##') FROM cart_variants as t14 WHERE t1.pid=t14.pid AND CHAR_LENGTH(t14.`field4`)>0 ORDER BY t14.field4) as field4,(SELECT GROUP_CONCAT(DISTINCT(t15.kg) SEPARATOR '##') FROM cart_variants as t15 WHERE t1.pid=t15.pid AND CHAR_LENGTH(t15.`kg`)>0 ORDER BY t15.kg) as kg,t3.fusionId,t2.allowoffer,t2.allowpurchase,(SELECT vskuvar FROM cart_variants as t18 JOIN nav_stock as t19 ON t18.vskuvar=t19.nav_skuvar WHERE t1.pid=t18.pid AND t19.nav_qty>0 GROUP BY t18.pid ORDER BY t18.vid) as sku
FROM (gmk_products as t1 JOIN cart_fusion as t2 ON t1.pid=t2.pid AND t2.allowpurchase=1) JOIN fusion as t3 ON t1.pid=t3.itemId AND t3.itemType='product' AND t3.ownerType='category'
INNER JOIN ((gmk_categories as t4 JOIN cart_catopts as t5 ON t4.cid=t5.cat_id AND t5.showincart=1) JOIN fusion as t6 ON t4.cid=t6.itemId AND t6.itemType='category' AND t6.ownerType='category') ON t6.itemId=t3.ownerId
INNER JOIN ((gmk_categories as t7 JOIN cart_catopts as t8 ON t7.cid=t8.cat_id AND t8.showincart=1) JOIN fusion as t9 ON t7.cid=t9.itemId AND t9.itemType='category' AND t9.ownerType='category') ON t9.itemId=t6.ownerId
WHERE (SELECT SUM(`nav_qty`) FROM cart_variants as t16 JOIN nav_stock as t17 ON t16.vskuvar=t17.nav_skuvar WHERE t1.pid=t16.pid)>0
ORDER BY t9.sorting,t6.sorting,t3.sorting");
}
else
{
$q=$db1->query("SELECT t1.prod_title as title1,t1.pid as id1,t3.title as title2,t3.cid as id2,t5.title as title3,t5.cid as id3,t7.title as title4,t7.cid as id4,t8.sorting as topord,t6.sorting as midord,t4.sorting as midord1,t2.sorting as midord2,'' as url,t5.layout as layout,t3.header as header,t3.logo as logo,t3.subdesc as subscrip,t3.description as catscrip,t3.ctype as ctype,t1.bigimage as bigimage,t1.description as proddesc,t1.lhimg as lhimg,t3.col1 as col1,t3.col2 as col2,(SELECT MIN(`price`) FROM cart_variants as t10 WHERE t1.pid=t10.pid) as price,t3.imgshift as imgshift FROM (gmk_products as t1 JOIN fusion as t2 ON t1.pid=t2.itemId AND t2.itemType='product' AND t2.ownerType='category')
INNER JOIN (gmk_categories as t3 JOIN fusion as t4 ON t3.cid=t4.itemId AND t4.itemType='category' AND t4.ownerType='category' AND t3.visible=1) ON t4.itemId=t2.ownerId
INNER JOIN (gmk_categories as t5 JOIN fusion as t6 ON t5.cid=t6.itemId AND t6.itemType='category' AND t6.ownerType='category' AND t5.visible=1) ON t6.itemId=t4.ownerId
INNER JOIN (gmk_categories as t7 JOIN fusion as t8 ON t7.cid=t8.itemId AND t8.itemType='category' AND t8.ownerType='category' AND t7.visible=1 AND t8.ownerId=0) ON t8.itemId=t6.ownerId
WHERE t1.displayed=1
UNION ALL
SELECT t1.prod_title as title1,t1.pid as id1,t3.title as title2,t3.cid as id2,t5.title as title3,t5.cid as id3,'' as title4,'' as id4,t6.sorting as topord,t4.sorting as midord,1 as midord1,t2.sorting as midord2,'' as url,t3.layout as layout,t3.header as header,t3.logo as logo,t3.subdesc as subscrip,t3.description as catscrip,t3.ctype as ctype,t1.bigimage as bigimage,t1.description as proddesc,t1.lhimg as lhimg,t3.col1 as col1,t3.col2 as col2,(SELECT MIN(`price`) FROM cart_variants as t10 WHERE t1.pid=t10.pid) as price,t3.imgshift as imgshift FROM (gmk_products as t1 JOIN fusion as t2 ON t1.pid=t2.itemId AND t2.itemType='product' AND t2.ownerType='category')
INNER JOIN (gmk_categories as t3 JOIN fusion as t4 ON t3.cid=t4.itemId AND t4.itemType='category' AND t4.ownerType='category' AND t3.visible=1) ON t4.itemId=t2.ownerId
INNER JOIN (gmk_categories as t5 JOIN fusion as t6 ON t5.cid=t6.itemId AND t6.itemType='category' AND t6.ownerType='category' AND t5.visible=1 AND t6.ownerId=0) ON t6.itemId=t4.ownerId
WHERE t1.displayed=1
UNION ALL
SELECT t1.title as title1,t1.cid as id1,'' as title2,'' as id2,'' as title3,'' as id3,'' as title4,'' as id4,t2.sorting as topord,t2.sorting as midord,1 as midord1,1 as midord2,t1.url as url,t1.layout as layout,t1.header as header,t1.logo as logo,t1.subdesc as subscrip,t1.description as catscrip,t1.ctype as ctype,t1.header as bigimage,t1.description as proddesc,'' as lhimg,t1.col1 as col1,t1.col2 as col2,0 as price,t1.imgshift as imgshift FROM (gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.itemId AND t2.itemType='category' AND t2.ownerType='category' AND t1.visible=1)
WHERE t1.collapsed=1
ORDER BY topord,midord,midord1,midord2");
}
$levelnames=array("","","");
$thiscat="";
$thisarr=array();
$pcdetail=array();
$fieldstuff=array();

while($cp=$q->fetch())
{
	$word4=strtolower($cp['title4']);
	$word3=strtolower($cp['title3']);
	$word2=strtolower($cp['title2']);
	$word1=strtolower($cp['id1']);
	if(strlen($word4)>0)
	{
		if(!isset($prodscats[$word4]))
		{
			$prodscats[$word4]=array();
		}
		if(!isset($prodscats[$word4][$word3]))
		{
			$prodscats[$word4][$word3]=array();
			$pcdetail[$word4][$word3]=array("layout"=>$cp['layout'],"ctype"=>$cp['ctype'],"header"=>$cp['header'],"logo"=>$cp['logo'],"catscrip"=>$cp['catscrip'],"scrip"=>$cp['subscrip'],"id"=>$cp['id3']);
		}
		if(!isset($prodscats[$word4][$word3][$word2]))
		{
			$prodscats[$word4][$word3][$word2]=array();
			$pcdetail[$word4][$word3][$word2]=array("layout"=>$cp['layout'],"ctype"=>$cp['ctype'],"header"=>$cp['header'],"logo"=>$cp['logo'],"catscrip"=>$cp['catscrip'],"scrip"=>$cp['subscrip'],"id"=>$cp['id2']);
		}
		$prodscats[$word4][$word3][$word2][]=$word1;
		$pcdetail[$word4][$word3][$word2][$word1]=array("id"=>$cp['id1'],"bigimage"=>$cp['bigimage'],"proddesc"=>$cp['proddesc'],"cprice"=>$cp['price'],"lhimg"=>$cp['lhimg'],"col1"=>$cp['col1'],"col2"=>$cp['col2'],"field1"=>$cp['field1'],"field2"=>$cp['field2'],"field3"=>$cp['field3'],"field4"=>$cp['field4'],"kg"=>$cp['kg'],"fusionId"=>$cp['fusionId'],"description"=>$cp['proddesc'],"allowoffer"=>$cp['allowoffer'],"allowpurchase"=>$cp['allowpurchase'],"ownerId"=>$cp['id2'],"sku"=>$cp['sku'],"itemtitle"=>$cp['title1'],"imgshift"=>$cp['imgshift']);
		if(!isset($fieldstuff[$word4][$word3][$word2]['field1'])){$fieldstuff[$word4][$word3][$word2]['field1']=array();}
		$fieldstuff[$word4][$word3][$word2]['field1']=getflstuff($fieldstuff[$word4][$word3][$word2]['field1'],$cp['field1']);
		
		if(!isset($fieldstuff[$word4][$word3][$word2]['field2'])){$fieldstuff[$word4][$word3][$word2]['field2']=array();}
		$fieldstuff[$word4][$word3][$word2]['field2']=getflstuff($fieldstuff[$word4][$word3][$word2]['field2'],$cp['field2']);
		
		if(!isset($fieldstuff[$word4][$word3][$word2]['field3'])){$fieldstuff[$word4][$word3][$word2]['field3']=array();}
		$fieldstuff[$word4][$word3][$word2]['field3']=getflstuff($fieldstuff[$word4][$word3][$word2]['field3'],$cp['field3']);
		
		if(!isset($fieldstuff[$word4][$word3][$word2]['field4'])){$fieldstuff[$word4][$word3][$word2]['field4']=array();}
		$fieldstuff[$word4][$word3][$word2]['field4']=getflstuff($fieldstuff[$word4][$word3][$word2]['field4'],$cp['field4']);
		
		if(!isset($fieldstuff[$word4][$word3][$word2]['kg'])){$fieldstuff[$word4][$word3][$word2]['kg']=array();}
		$fieldstuff[$word4][$word3][$word2]['kg']=getflstuff($fieldstuff[$word4][$word3][$word2]['kg'],$cp['kg']);
	}
	else if(strlen($word3)>0)//Old format & spares shop
	{
		if(!isset($prodscats[$word3]))
		{
			$prodscats[$word3]=array();
		}
		if(!isset($prodscats[$word3][$word2]))
		{
			$prodscats[$word3][$word2]=array();
			$pcdetail[$word3][$word2]=array("layout"=>$cp['layout'],"ctype"=>$cp['ctype'],"header"=>$cp['header'],"logo"=>$cp['logo'],"catscrip"=>$cp['catscrip'],"scrip"=>$cp['subscrip'],"id"=>$cp['id2']);
		}
		$prodscats[$word3][$word2][]=$word1;
		$pcdetail[$word3][$word2][$word1]=array("id"=>$cp['id1'],"bigimage"=>$cp['bigimage'],"proddesc"=>$cp['proddesc'],"cprice"=>$cp['price'],"lhimg"=>$cp['lhimg'],"col1"=>$cp['col1'],"col2"=>$cp['col2'],"field1"=>$cp['field1'],"field2"=>$cp['field2'],"field3"=>$cp['field3'],"field4"=>$cp['field4'],"kg"=>$cp['kg'],"fusionId"=>$cp['fusionId'],"description"=>$cp['proddesc'],"allowoffer"=>$cp['allowoffer'],"allowpurchase"=>$cp['allowpurchase'],"ownerId"=>$cp['id2'],"sku"=>$cp['sku'],"itemtitle"=>$cp['title1'],"imgshift"=>$cp['imgshift']);
		
		if(!isset($fieldstuff[$word3][$word2]['field1'])){$fieldstuff[$word3][$word2]['field1']=array();}
		$fieldstuff[$word3][$word2]['field1']=getflstuff($fieldstuff[$word3][$word2]['field1'],$cp['field1']);
		
		if(!isset($fieldstuff[$word3][$word2]['field2'])){$fieldstuff[$word3][$word2]['field2']=array();}
		$fieldstuff[$word3][$word2]['field2']=getflstuff($fieldstuff[$word3][$word2]['field2'],$cp['field2']);
		
		if(!isset($fieldstuff[$word3][$word2]['field3'])){$fieldstuff[$word3][$word2]['field3']=array();}
		$fieldstuff[$word3][$word2]['field3']=getflstuff($fieldstuff[$word3][$word2]['field3'],$cp['field3']);
		
		if(!isset($fieldstuff[$word3][$word2]['field4'])){$fieldstuff[$word3][$word2]['field4']=array();}
		$fieldstuff[$word3][$word2]['field4']=getflstuff($fieldstuff[$word3][$word2]['field4'],$cp['field4']);
		
		if(!isset($fieldstuff[$word3][$word2]['kg'])){$fieldstuff[$word3][$word2]['kg']=array();}
		$fieldstuff[$word3][$word2]['kg']=getflstuff($fieldstuff[$word3][$word2]['kg'],$cp['kg']);
	}
	else if(strlen($word2)>0)//menu link only
	{
		if(!isset($prodscats[$word2]))
		{
			$prodscats[$word2]=array();
		}
		$prodscats[$word2][]=$word1;
		$pcdetail[$word2]=array("layout"=>$cp['layout'],"header"=>$cp['header'],"logo"=>$cp['logo'],"catscrip"=>$cp['catscrip'],"scrip"=>$cp['scrip'],"id"=>$cp['id1'],"imgshift"=>$cp['imgshift']);
	}
	else
	{
		$prodscats[$word1]=$cp['url'];
		$pcdetail[$word1]=array("itemtitle"=>$cp['title1'],"imgshift"=>$cp['imgshift']);
	}
}
function getflstuff($ar,$fbits)
{
	$f1stuff=explode("##",$fbits);
	return array_unique(array_merge($ar,$f1stuff));
}
#################
//if(isset($_SESSION['test'])){print_r($subdirs);}
//exit();
//print_r($_SESSION['cart']);
//print_r($_POST);
?>
<script type="text/javascript">
		function screenRot(){
			document.location = '<?=$_SERVER['PHP_SELF']?>?<?=$_SERVER['QUERY_STRING']?>';
		}
		document.body.addEventListener('orientationchange', screenRot );
		var menuState=0;
		function phonemenu(){
			var sideMenu=document.getElementById('sidemenu');
			var menButt=document.getElementById('menubutt');
			if(menButt.innerHTML=="SHOW MENU")
			{
				sideMenu.style.display="block";
				menButt.innerHTML="HIDE MENU";	
			}
			else
			{
				sideMenu.style.display="none";
				menButt.innerHTML="SHOW MENU";	
			}
		}
</script>
<!--<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>-->
<?php $promoend=mktime(0,0,0,1,15,2012);?>
<div id="pageheader"><img src='./content/images/main/pageheader_home_default.jpg' alt='' id="pgheadimg" /><?php if($deviceType=="phone"){?><div id="menubutt" onclick="phonemenu()">SHOW MENU</div><?php }?></div>
<div id='menustrip'>
	<div id='inner'>
	<?php if($deviceType!="phone"){?>
		<div id='simplesearch'>
			<form action="./products" method="get" id="simplesearchform">
				<input type="text" name="simplesearch" <?php if(!isset($get_arr['simplesearch'])){?>value="Product Search" onfocus='if (value== "Product Search") {value=""}' onblur='if (value== "") {value="Product Search"}'<?php }else{?>value="<?=$get_arr['simplesearch']?>"<?php }?> style="width:122px;" size="22" maxlength="50" /><input type="submit" value="go" class="button" />
			</form>
		</div>
		<?php }?>
		<div id='mainlinks'>
			<div class="left"><a href='./' title='Home'>home</a><a href='./dealer'>find a dealer</a><a href='./warranty'>warranty</a><a href='http://www.gmk-online.co.uk'>trade login</a><a href="./vacancies">Vacancies</a><?php if($allowcart){?><a href='./shop' title='Spares Shop' style='position:relative;top:0;left:0'>Spares Shop<!--<span style="position:absolute;bottom:-15px;right:-20px;z-index:10"><img src='./content/images/main/new.png' alt='' /></span>--></a><?php }?><?php if($deviceType!="phone"){?></div>
			<div class="right"><?php }?><a href='./viewbrochures' <?=$deviceType=="phone"?"class='right'":""?>>brochures</a><a href='./news' <?=$deviceType=="phone"?"class='right'":""?>>news &amp; events</a><a href='./about' style="margin-right:0 !important; <?=$deviceType=="phone"?"":"width:98px !important"?>" <?=$deviceType=="phone"?"class='right'":""?>>contact &amp; about</a></div>
			<div class="clear"></div>
		</div>
	</div>
</div>
<div id='midsection' <?php if($deviceType=="phone"&&$page=="dealer"){?>style="padding:10px 0 !important"<?php }?>>
<?php if(!stristr($page,"cart_"))
{
	?>
	<div id='sidemenu'>		
		<?php if(isset($_SESSION['cart'])&&count($_SESSION['cart'])>0&&$allowcart)
		{		
			?>
			<div id="info">
				<div style="float:left"><a href="./shop/basket"><img src="./content/images/main/basket<?=isset($_SESSION['cart'])&&count($_SESSION['cart'])>0?"_full":""?>.png" alt="" /></a></div>
				<div style="float:left;padding-left:5px;"><a href="./shop/basket" style="font-weight:bold;font-size:1.1em;line-height:1em;color:#e4e4e4">VIEW<br />BASKET</a><br />Total: &#163;<?=number_format($basket_total,2)?></div>
				<div class="clear"></div>
			</div>
			<?php 
		}?>
		<div id='brandsmenu'>
			<ul style="padding-bottom:3px;margin-bottom:6px;">
				<?php 
				$h="";
				$pp="";
				//SELECT c.`title`,c.`cid`,b.`brand`,b.`id`,p.`premium`,c.`url`,c.`collapsed`,p.`pid` FROM ((gmk_categories as c LEFT JOIN fusion as f ON c.`cid`=f.`ownerId` AND f.`ownerType`='category') LEFT JOIN gmk_products as p ON f.`itemId`=p.`pid` AND p.`displayed`='1') LEFT JOIN gmkbrands as b ON p.`bid`=b.`id` WHERE c.`visible`='1' GROUP BY c.`cid`,b.`id` ORDER BY c.`displayorder`,b.`sorting`
				//SELECT t1.`title`,t1.`cid`,t1.`url`,t1.`collapsed`,(SELECT GROUP_CONCAT(t3.title,'##',t3.cid) FROM gmk_categories as t3 JOIN fusion as t4 ON t3.cid=t4.itemId WHERE t4.ownerId=t1.cid AND t4.ownerType='category' AND t3.visible=1 AND ((SELECT count(*) FROM gmk_products as t5 JOIN fusion as t6 ON t5.pid=t6.itemId AND t5.displayed=1 WHERE t6.ownerId=t4.itemId)>0 OR (SELECT count(*) FROM gmk_products as t7 JOIN fusion as t8 ON t7.pid=t8.itemId AND t7.displayed=1 WHERE t8.ownerId=t2.itemId)>0) ORDER BY t3.displayorder) as subs FROM gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.itemId AND t2.ownerId=0 AND t2.itemType='category' AND t2.ownerType='category' AND t1.visible=1 ORDER BY t1.displayorder
				
				//$q = $db1->query("SELECT t1.`title`,t1.`cid`,t1.`url`,t1.`collapsed`,(SELECT count(*) FROM gmk_products as t13 JOIN fusion as t14 ON t13.pid=t14.itemId AND t13.displayed=1 WHERE t14.ownerId=t1.cid AND t14.itemType='product' AND t14.ownerType='category') as subprods,(SELECT GROUP_CONCAT(t3.title,'##',t3.cid,'##',t3.`layout` ORDER BY t3.displayorder) FROM gmk_categories as t3 JOIN fusion as t4 ON t3.cid=t4.itemId WHERE t4.ownerId=t1.cid AND t4.ownerType='category' AND t4.itemType='category' AND t3.visible=1 AND ((SELECT count(*) FROM gmk_categories as t5 JOIN fusion as t6 ON t5.cid=t6.itemId AND t5.visible=1 WHERE t6.ownerId=t3.cid AND t6.itemType='category' AND t6.ownerType='category')>0 OR (SELECT count(*) FROM gmk_products as t7 JOIN fusion as t8 ON t7.pid=t8.itemId AND t7.displayed=1 WHERE t8.ownerId=t3.cid AND t8.itemType='product' AND t8.ownerType='category')>0)) as subs FROM gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.itemId AND t2.ownerId=0 AND t2.itemType='category' AND t2.ownerType='category' AND t1.visible=1 AND ((SELECT count(*) FROM gmk_categories as t9 JOIN fusion as t10 ON t9.cid=t10.itemId AND t9.visible=1 WHERE t10.ownerId=t1.cid AND t10.itemType='category' AND t10.ownerType='category')>0 OR (SELECT count(*) FROM gmk_products as t11 JOIN fusion as t12 ON t11.pid=t12.itemId AND t11.displayed=1 WHERE t12.ownerId=t1.cid AND t12.itemType='product' AND t12.ownerType='category')>0 OR CHAR_LENGTH(t1.url)>0) ORDER BY t1.displayorder");
				/*
				while($r=$q->fetch())				
				{					
					//if($r['collapsed']==1)
					//{
						if($h!=$r['title'])
						{
							if(strlen($h)>0){?></ul><ul<?=$r['collapsed']!=1?" style='padding-bottom:3px;margin-top:4px;margin-bottom:6px;'":""?>><?php }$h=$r['title'];
							if($r['collapsed']==1&&strlen($r['url'])>0){$thetitle="<a href=\"".$r['url']."\">".$h."</a>";}
							else if($r['subprods']>0){$thetitle="<a href='?p=products&amp;code=".$r['cid']."&amp;viewing=".urlencode($r['title'])."'>".$h."</a>";}
							else{$thetitle=$h;}
							?>
							<li class='heading'<?=$r['collapsed']!=1?" style='border-bottom: 1px solid #163756;'":""?>><?=$thetitle?></li>
							<?php 
						}
						
						if($r['collapsed']!=1)
						{
							$subs=explode(",",$r['subs']);
							foreach($subs as $sub)
							{
								$subbits=explode("##",$sub);$code=$subbits[1];
								if($subbits[2]==1)
								{
									?>
									<li><a href='./<?=urlencode(strtolower($r['title']))?>/<?=urlencode(strtolower($subbits[0]))?>'><?=$subbits[0]?></a></li>
									<?php
								}
								else
								{
									?>
									<li><a href='?p=products&amp;code=<?=$code?>&amp;viewing=<?=urlencode($subbits[0].":%20".$r['title'])?>'><?=$subbits[0]?></a></li>
									<?php
								}
							}
						}
						if(0)
						{
							$subs=explode(",",$r['subs']);
							foreach($subs as $sub)
							{
								$subbits=explode("##",$sub);$code=$subbits[1];
								if($subbits[2]==1)
								{
									?>
									<li><a href='?p=product&amp;br=<?=urlencode($subbits[1])?>&amp;viewing=<?=urlencode($subbits[0].":%20".$r['title'])?>'><?=$subbits[0]?></a></li>
									<?php
								}
								else
								{
									?>
									<li><a href='?p=products&amp;code=<?=$code?>&amp;viewing=<?=urlencode($subbits[0].":%20".$r['title'])?>'><?=$subbits[0]?></a></li>
									<?php
								}
							}
						}
					//}
				}
				*/
				/* str_replace("&","-and-",$main) combats the issue with ampersand messing up mod rewrite urls */
				foreach($prodscats as $main => $subs)			
				{			
					$collapsed=is_array($subs)&&count($subs)>0?0:1;
					if($h!=$main)
					{
						if(strlen($h)>0){?></ul><ul<?=$collapsed!=1?" style='padding-bottom:3px;margin-top:4px;margin-bottom:6px;'":""?>><?php }$h=$main;
						if($collapsed==1&&strlen($subs)>0){$thetitle="<a href=\"".$subs."\">".$pcdetail[$main]['itemtitle']."</a>";}
						else if($r['subprods']>0){$thetitle="<a href='./".urlencode(urlencode(str_replace("&","-and-",$main)))."'>".$h."</a>";}
						else{$thetitle=$h;}
						?>
						<li class='heading'<?=$collapsed!=1?" style='border-bottom: 1px solid #163756;'":""?>><?=$thetitle?></li>
						<?php 
					}
					
					if(is_array($subs)&&count($subs)>0)
					{
						foreach($subs as $sub => $arr)
						{
							?>
							<li><a href='./<?=urlencode(str_replace("&","-and-",$main))?>/<?=urlencode(urlencode(strtolower(str_replace("&","-and-",$sub))))?>'><?=ucwords($sub)?></a></li>
							<?php
						}
					}
				}
				?>
			</ul>
		</div>
		<div id="infodd">
		<ul style="padding-bottom:3px;margin-bottom:6px;">
			<li style="background:none;padding-left:0" class='heading'><a class="hide" href="#">INFORMATION</a>
				<ul>
					<li><a href="./guncare">Gun Care</a></li>
					<li><a href="./content/pdf/loyalty-scheme2015.pdf" target="_blank">Beretta Domestic Loyalty Scheme (2015)</a></li>
					<li><a href="./content/pdf/FEDELTA%202015.pdf" target="_blank">International Loyalty Scheme (2015)</a></li>
					<li><a href="./cart_chokeguide">Choke Guide</a></li>
					<li><a href="./content/images/main/ItalianDateStamps.jpg" target="_blank">Italian Date Stamps</a></li>
				</ul>
			</li>
		</ul>
		</div>
	</div><?php }?>
	<div id='content' <?php if($deviceType=="phone"&&$page=="dealer"){?>style="width:100% !important;padding:0 !important;"<?php }?>>
	<?php if(0){?><div style="border:1px solid red;padding:10px;background:#DFE1E5;color:#010821;font-size:16px"><strong>Please Note:</strong> Our phone lines are currently out of service. For any urgent enquiries, please send an email to <a href="mailto:sales@gmk.co.uk" style="color:#010821;text-decoration:underline">sales@gmk.co.uk</a>. Sorry for any inconvenience this may cause.</div><?php }?>

	<?php include $page . ".php";?>
	
		<?php if($deviceType!="phone"){?><div><img src='./content/images/main/spacer.gif' width='1' height='128' alt='' /></div><?php }?>
	</div>
	
	<div class="clear"></div>
	
	<?php 
	/* CART */
	$countcart=count($_SESSION['cart']);
	if(isset($_SESSION['cart'])&&$countcart>0&&$allowcart){?>
	<style type="text/css">
	#basketsummary_contents{		
		-webkit-transition-duration:<?=$countcart*0.5?>s;
		transition-duration:<?=$countcart*0.5?>s;
	}
	#basketsummary:hover #basketsummary_contents{height:<?=($countcart*38)+23?>px;}
	</style>
	<div id="basketsummary">
	<a style="height:64px;width:163px;z-index:100;display:block;" href="./cart_basket"><img src="content/images/main/spacer.gif" alt="" style="width:163px;height:64px;" /></a>
	<div id="basketsummary_contents">
		<div id="basketsummary_head">
			<p style="width:30px;">QTY</p>
			<p>Product</p>
		</div>
		<?php 
		foreach($_SESSION['cart'] as $id => $cart)
		{
			$skuvars="";
			foreach($cart['skuvariant'] as $ident => $newsku)
			{
				$expsku=explode("-qty-",$newsku);
				$skuvars.=($skuvars!=""?",":"").$expsku[0];
			}
			/*$query="SELECT p.`".PFIELDNAME."` as title,f.`fusionId` as fusionId FROM (".PTABLE." as p JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' WHERE p.`".PFIELDID."`='".$cart['prod_id']."'";
			$prodinfoq=ysql_query($query,$con1);
			$prodinfo=mysql_fetch_assoc($prodinfoq);
			*/
			$query="SELECT p.`".PFIELDNAME."` as title,f.`fusionId` as fusionId FROM (".PTABLE." as p JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' WHERE p.`".PFIELDID."`=?";
			$prodinfoq=$db1->prepare($query);
			$prodinfoq->execute(array($cart['prod_id']));
			$prodinfo=$prodinfoq->fetch();
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			$plink=$cart['prod_id']=='358'?"./warranty":$cart['rlink'];
			?>
			<div class="basketsummary_<?=$row_class?>">
				<div style="width:30px;font-size:22px;"><?=$cart['qty']?></div>
				<div>
					<a href="<?=$plink?>"><?=$prodinfo['title']?></a>
					<?php $choice=cart_variants($skuvars);?>
					<br /><?=is_array($choice)?ucwords("Variant: ".$choice['vname'])." (".str_replace("-v-NONE","",$choice['vskuvar']).")":""?>
				</div>
			</div>
			<?php
		}
		?>
		</div>
</div>
<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
<?php }
/* /CART */
?>
</div>
<script type="text/javascript">
var dType="<?=$deviceType?>";
//alert(dType);
if(windowx<480||dType=="phone")
{
	document.getElementById('midsection').style.width=windowx+"px";
	document.getElementById('menustrip').style.width=windowx+"px";
	document.getElementById('footer').style.width=windowx+"px";
	if(windowx>335)
		document.getElementById('pgheadimg').style.width=windowx+"px";
	document.getElementById('pgheadimg').src='./content/images/phone/logo.jpg';
}
else if(windowx<1024)
{
	document.getElementById('pgheadimg').style.width=windowx+"px";
}
</script>
<?php /*$brokeys=array_keys($brochures);$broimg="./content/images/brochures/".$brokeys[0].".jpg";list($browid)=getimagesize($broimg);*/?>
<div id='footer'>
	<div id='links'>
	<!-- AddThis Follow BEGIN -->
<div class="addthis_toolbox addthis_default_style addthis_default_style" style="float:left;margin-right:5px;">
<a class="addthis_button_facebook_follow" addthis:userid="gmk.uk"></a>
<a class="addthis_button_twitter_follow" addthis:userid="gmkltd"></a>
<a class="addthis_button_google_follow" addthis:userid="111799340464719001752"></a>
<a class="addthis_button_pinterest_follow" addthis:userid="gmkltd"></a>
</div>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52ea79d355e1ace9"></script>
<!-- AddThis Follow END -->
	<!--<div id="twitterlink"><a href="https://twitter.com/GMKLtd" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @GMKLtd</a></div><div id="fbooklink"><?// if(!stristr($page,"cart_")){?><div class="fb-like" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false"></div><?// }?></div>-->
	<!--<div id="youtubelink"><a href="http://www.youtube.com/user/GMKLtd">YouTube</div>-->
	<?php if($allowcart){?><a href='./cart_terms'>Terms &amp; Conditions</a> | <?php }?><a href='./privacy'>Privacy Policy</a> | <a href='content/pdf/Quality-Assurance.pdf' target='_blank'>Quality Assurance</a> | <a href='content/pdf/Mission-Statement.pdf' target='_blank'>Mission Statement</a><?=$deviceType=="phone"?"<br />":" | "?>Copyright &copy; <?=date("Y")?> GMK Limited. All Rights Reserved.</div>
</div>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<?php if(!in_array($page,array("products","productdetail","cart_products","home","news","about"))){?>
<script src="./content/js/main.js" type="text/javascript"></script>
<script type="text/javascript" src="./content/js/scriptaculous.js?load=effects"></script>
<?php 
}?>

<script src="./content/js/AC_RunActiveContent.js" type="text/javascript"></script>
<!--<script src="https://www.google-analytics.com/urchin.js" type="text/javascript"></script>-->
<!-- this is at the bottom to catch new total without needing JS-->
<script type="text/javascript">
// <![CDATA[
var brandWarranty=['BERETTA'];
function ajax(subj,id)
{
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
  {
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			responseHtml=xmlhttp.responseText;	
			if((subj=='warrantyserial'||subj=='warrantybrand'))
			{
				theForm=id.form;
				if(responseHtml=="error"||id.value.length<1||(subj=='warrantybrand'&&theForm['brand'].selectedIndex==0))
				{
					id.style.border="1px solid red";
					id.style.backgroundColor="#996767";
					if(subj=='warrantybrand'&&(theForm['serial'].value.length>0||theForm['brand'].selectedIndex==0))
					{
						document.getElementById('brandinfo').style.display=theForm['brand'].selectedIndex==0?'inline':'none';
						document.getElementById('brandinfo1').style.display=theForm['serial'].value.length>0?'inline':'none';
					}
					if(subj=='warrantyserial'&&id.value.length<1)
					{
						document.getElementById('serialinfo').style.display='inline';
						document.getElementById('serialinfo1').style.display='none';
					}
					//theForm['brand'].value='';
					//theForm['product'].value='';
					warrantyLength(theForm['brand'].value,theForm['product'].value);
				}
				else
				{
					id.style.border="1px solid #aaaaaa";
					id.style.backgroundColor="#FFFFFF";
					id.form['brand'].style.border="1px solid #aaaaaa";
					id.form['brand'].style.backgroundColor="#FFFFFF";
					document.getElementById('serialinfo').style.display='none';
					document.getElementById('serialinfo1').style.display='none';
					document.getElementById('brandinfo').style.display='none';
					document.getElementById('brandinfo1').style.display='none';
					bits=responseHtml.split('][');			
					theSug=document.getElementById('suggestbox');
					sers=bits[2].split('|');
					sersL=sers.length;
					if(bits[0].length>0&&/*id.form['brand'].value.length>0&&*/id.form['serial'].value.length>0)
					{
						//theForm['brand'].value=bits[0];//pre selecting in new design
						//alcione: TA17246
						//affinity: BL28845S14
						//Hrrier: 9506931
						theForm['product'].value=bits[1];
					}
					warrantyLength(bits[0],theForm['product'].value);
					/* don't offer suggestions 
					sugInner="";
					if(sersL<15)
					{
						for(x=0;x<sersL;x++)
							sugInner+="<li onclick='document.getElementById(\"serial\").value=\""+sers[x]+"\";ajax(\""+subj+"\",document.getElementById(\"serial\"));showhideSug(0);'>"+sers[x]+"</li>";
						theSug.innerHTML=sugInner;
					}
					else {showhideSug(0);}
					if(sersL>1 && sersL<15)
						showhideSug(1);
					*/
				}
			}
			else if(subj=='catorder'||subj=='prodorder')
			{
				o=id.split('][');
				swapwith=o[2]=="down"?parseInt(o[0])-1:parseInt(o[0])+1;
				idName=subj=='catorder'?'cat':'prod';
				swapEl(document.getElementById(idName+o[0]),document.getElementById(idName+swapwith));	
			}
		}
  }
	iid=id;
	if(typeof(id)==="object")
	{
		iid=id.value;
	}
	if(subj=='warrantybrand')
	{
		iid+="]["+id.form['serial'].value;
	}
	//xmlhttp.open("GET","ajax.php?q="+escape(str)+"&k="+kid,true);
	xmlhttp.open("GET","ajax.php?"+subj+"="+iid,true);
	xmlhttp.send();
}
function swapEl(el1,el2)
{
	var cloned1 = el1.children[0].cloneNode(true);
  var cloned2 = el2.children[0].cloneNode(true);
	el2.replaceChild(cloned1, el2.children[0]);
	el1.replaceChild(cloned2, el1.children[0]);
}
//]]>
</script>
<?php if(!isset($_COOKIE['cookies'])&&!isset($_GET['cookieinfo'])){?>
<div id="cookienote">This website uses cookies to ensure you get the best experience on our website. <a href="./privacy">More Info</a> | <a href="index.php?cookieinfo=1">Dismiss</a></div>
<?php }?>
</body>
</html>
