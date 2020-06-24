<?php
if(isset($_GET['county'])||isset($_GET['brand']))
{

//$db=ysql_connect("109.123.78.12", "gmk", "cwuio745bjd") or die(sql_error("Error")); 
//mysql_select_db("gmk_global",$db) or die(sql_error("Error"));
$thisbrand=cleanBrand($_GET['brand']);
$thiscounty = urldecode($_GET['county']);
if($thiscounty == "London"){
	$docity=" OR ((COALESCE(`County`,0)='0' OR `County`='') AND `City`='London'))";
}
else if(strlen($thisbrand) > 2 and strlen($thiscounty) > 3){$docity=")";}

if($thisbrand == "all dealers" and $thiscounty == "all counties"){$where = "";}
else if(strlen($thisbrand) > 2 and $thiscounty == "all counties"){$where = "AND ((".$thisbrand . ")";}
else if($thisbrand == "all dealers" and strlen($thiscounty) > 3){$where = "AND (`beretta_guns`='Y' OR `beretta_sport`='Y' OR `beretta_premium`='Y' OR `beretta_clothing`='Y' OR `franchi`='Y' OR `sako`='Y' OR `sako_ammo`='Y' OR `leupold`='Y' OR `tikka`='Y' OR `benelli`='Y' OR `benelli_premium`='Y' OR `lanber`='Y' OR `arrieta`='Y' OR `atk`='Y' OR `burris`='Y' OR `accessories`='Y' OR `stoeger`='Y' OR `steiner`='Y') AND (`County`=:county";}
else if(strlen($thisbrand) > 2 and strlen($thiscounty) > 3){$where = "AND (" . $thisbrand . ") AND (`County`=:county";}

$query = "SELECT * FROM dealerlistings as dl LEFT JOIN dealerlistings_latlng as dll ON dl.`accountid`=dll.`acid` WHERE `GMK`='Y' AND `accountid`!='{A5602769-2CCA-DF11-B8DA-00215E31A60A}' $where $docity ORDER BY `Account` ASC";	
//$result = ysql_query($query,$con2) or die(sql_error("Error",$query));
//echo $query;
$result=$db2->prepare($query);
if(strlen($thiscounty)>0&&$thiscounty != "all counties"){$result->bindValue(':county',$_GET['county']);}
$result->execute();
?>
<h2 id="pagetitle">UK Stockists of <?=ucwords($_GET['brand'])?> in  <?=strlen($_GET['county'])>0?ucwords($_GET['county']):"All Counties"?></h2>
<?php
/* GEOCODES */
$i=1;
while($info = $result->fetch())  
{ //http://maps.googleapis.com/maps/api/geocode/xml?address=Aberdeenshire,UK&sensor=false
	mapmarkers($info,$i);
	$i++;
}
/* GEOCODES */
if($brand=="Beretta"){
?>
<div class="mapkey">
<div style="float:left"><img src="content/images/mapmarkers/beretta_clothing.png" alt="" /> = Beretta Clothing Only</div>
<div style="float:left;margin-left:5px;"><img src="content/images/mapmarkers/beretta_no_clothing.png" alt="" /> = Beretta Guns Only</div>
<div style="float:left;margin-left:5px;"><img src="content/images/mapmarkers/default.png" alt="" /> = Default/Mixed Product Ranges</div>
<div class="clear"></div>
</div>
<?php }?>
<a href="./dealer&amp;dealercounty=<?=strlen($thecounty)>0?$thecounty:"all+counties"?>&amp;dealerbrand=<?=urlencode($brand)?>&amp;dealersubmit=Submit">Back to dealers</a>
<div id="map_canvas" style="width: 100%; height: 570px;border:2px solid #ccc;color:black;"></div>
<?php }?>