<?php
$debugmode=0;//all input/update/delete sql queries display on screen instead of being processed.
$idle_minutes=30;//site user inactivity
$aidle_minutes=60*100;//admin user inactivity
$passreset_minutes=30;
$imgfiletypes=array('jpg','png','gif','flv');

/* NON configurable vars below */
$idletime=$idle_minutes*60;
$adminidletime=$aidle_minutes*60;
$passresetmins=$passreset_minutes*60;

$prefixpath=($inhouse)?"W:/Website/gmk/public_html/":"/home/gmk/public_html/";
$mainbase=($inhouse)?"http://bhweb2/gmk/public_html":"http://www.gmk.co.uk";

$images_arr=array();
$images_arr['product']=array(
	"path"=>"content/images/products/",
	"images"=>array(
		"big"=>"1000x500",
		"thumbnail"=>"247x102",
		"feature icons"=>""
	)
);
$images_arr['news']=array(
	"path"=>"content/images/news/",
	"images"=>array(
		"large"=>"350x350",
		"thumbnail"=>"275x93",
		"tiny"=>"164x41"
	)
);
$images=array(
array(
"home_690comp.jpg",
"<a href='./products/item/393'>Click to see more information on the Beretta 690 Competition</a>",
"Click to see more information on the Beretta 690 Competition",
"./products/item/393",
"8","111"),
array(
"home_690.jpg",
"<a href='./news/id/51112'>Click to see more information on the Beretta 690 Field III</a>",
"Click to see more information on the Beretta 690 Field III",
"./news/id/51112",
"8","111"),
array(
"home_b828u.jpg",
"<a href='./news/id/51757'>Click to see more information on the new Benelli 828U</a>",
"Click to see more information on the new Benelli 828U",
"./news/id/51757",
"8","111"),
array(
"home_carbonlight.jpg",
"<a href='./news/id/51760'>Click to see more information on the new Sako 85 Carbonlight</a>",
"Click to see more information on the new Sako 85 Carbonlight",
"./news/id/51760",
"8","111")
/*,
array(
"home_gamefair.jpg",
"Come see us at Harewood House, venue for the 2015 CLA Game Fair. Friday 31st July to Sunday 2nd August. Stand T1499",
"Come see us at Harewood House, venue for the 2015 CLA Game Fair. Friday 31st July to Sunday 2nd August. Stand T1499",
"",
"8","111"),
array(
"home_spauto.jpg",
"<a href='http://www.benellispauto.co.uk/index.php?p=results'>Click to view the full results</a>",
"Click to view the full results",
"http://www.benellispauto.co.uk/index.php?p=results",
"8","111")*/

);
define("MAIN_ONOFF","On Website");
define("SEP","&#187;");
/*get vars*/
if(basename($_SERVER['PHP_SELF'])!="auth.php")
{
$get_arr=isset($_GET)?mysql_real_extracted($_GET):array();
$post_arr=isset($_POST)?mysql_real_extracted($_POST):array();
}
$act=isset($get_arr['act'])?$get_arr['act']:"";
$id=isset($get_arr['id'])?$get_arr['id']:"";
$cid=isset($get_arr['cid'])?$get_arr['cid']:0;
$pid=isset($get_arr['pid'])?$get_arr['pid']:"";
$owner=isset($get_arr['owner'])?$get_arr['owner']:0;
$curpage=isset($get_arr['curpage'])?$get_arr['curpage']:"";
$table=isset($get_arr['table'])?$get_arr['table']:"cats";
/*/get vars*/
$formaction=basename($_SERVER['PHP_SELF'])."?".str_replace("&new=1","",$_SERVER['QUERY_STRING']);
$higherr=array();
$date=date("U");
$subtypes=array(
"shotgun"=>array("field","competition"),
"rifle"=>array(),
"optic"=>array("scope","scope kit","sight"),
"Miscellaneous"=>array("torches"),
"choke"=>array("choke","key"),
"sight"=>array(),
"spare"=>array("swivel","mount","trigger"),
"barrel"=>array("over & under","side by side"),
"pad"=>array()
);
$fields=array(//name for Fields 1-4 in order
	"rifle"=>array("variant name"=>"vname","code"=>"vskuvar","caliber"=>"field1","Action"=>"field4","barrel"=>"field2","weight (kg)"=>"kg","rrp inc. vat"=>"price"),
	"shotgun"=>array("variant name"=>"vname","code"=>"vskuvar","gauge"=>"field1","barrel"=>"field2","chamber"=>"field3","chokes"=>"field4","weight (kg)"=>"kg","rrp inc. vat"=>"price"),
	"choke"=>array("variant name"=>"vname","code"=>"vskuvar","Extension"=>"field3","Gauge"=>"field2","Constriction"=>"field1","rrp inc. vat"=>"price"),
	"barrel"=>array("variant name"=>"vname","code"=>"vskuvar","gauge"=>"field1","length"=>"field2","choke req'd"=>"field4","weight (kg)"=>"kg","rrp inc. vat"=>"price"),
	"pad"=>array("variant name"=>"vname","code"=>"vskuvar","Depth"=>"field1","rrp inc. vat"=>"price"),
	"optic"=>array("variant name"=>"vname","code"=>"vskuvar","tube"=>"field1","rrp inc. vat"=>"price"),
	"sight"=>array("variant name"=>"vname","code"=>"vskuvar","rrp inc. vat"=>"price"),
	"spare"=>array("variant name"=>"vname","code"=>"vskuvar","rrp inc. vat"=>"price"),
	
	
	/*"Miscellaneous"=>array("variant name"=>"vname","code"=>"vskuvar","rrp inc. vat"=>"price"),*/
	"default"=>array("variant name"=>"vname","code"=>"vskuvar","gauge/caliber"=>"field1","barrel/choke gauge"=>"field2","chamber/extension"=>"field3","chokes/action"=>"field4","weight (kg)"=>"kg","rrp inc. vat"=>"price")
);
$ignored=array("vname","vskuvar","price");
?>