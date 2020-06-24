<?php if(!isset($page)){header("Location: index.php");}
$selected = "selected='selected'";
//$pgnum = (isset($_GET['pgnum'])) ? mysql_real_escape_string($_GET['pgnum']) : 1;
$pgnum = isset($_GET['pgnum']) ? $_GET['pgnum'] : 1;
$maxpagelinks = 5;
$perpage = 500;//high number to show all
if(isset($_POST['dealercounty'])||isset($_POST['dealerbrand']))
{
	//$_SESSION['dealerbrand'] = trim(mysql_real_escape_string($_GET['dealerbrand']));
	//$_SESSION['dealercounty'] = trim(mysql_real_escape_string($_GET['dealercounty']));
	$_SESSION['dealerbrand'] = trim($_POST['dealerbrand']);
	$_SESSION['dealercounty'] = trim($_POST['dealercounty']);
}
$srchbrand = isset($_SESSION['dealerbrand']) ? $_SESSION['dealerbrand'] : "";
$srchcounty = isset($_SESSION['dealercounty']) ? $_SESSION['dealercounty'] : "";

if($srchbrand != ""&&$srchcounty!="")
{
	$searchres=dealer($_SESSION['dealerbrand'],$_SESSION['dealercounty'],$pgnum,$perpage);
	if(!($srchbrand == "all dealers" && $srchcounty == "all counties"))
	{
		$resultnum = $searchres[0];
	}
}
$selected_county = $srchcounty;
$selected_brand = $srchbrand;
?>

<div style='float:left;<?php if($deviceType=="phone"){?>padding-left:5px;<?php }?>' class='pagetitle'><img src='./content/images/main/title_dealers.jpg' alt='Find A Dealer' /></div>
<div style='float:right'>
<?=pagenav($resultnum,$perpage,"?p=dealer",$maxpagelinks)?>
</div>
<div style='clear:both'></div>
<div id='quicksearchbar'<?php if($deviceType=="phone"){?> style="padding: 3px 7px 3px 5px;"<?php }?>>Search</div>
<div id='dealerform'>
<form action="./dealer" method="post" id="dealer">
<input type="hidden" name="p" value="dealer" class='hidden' />
<label for="dealercounty" style="white-space:nowrap">Select County: <select id="dealercounty" name="dealercounty" onchange="this.form.submit()">
<option value="all counties">- Please select -</option>
<?php 
countiesoptions("SELECT * FROM dealerlistings WHERE (`County` != '' AND `County` IS NOT NULL) AND `GMK`='Y' AND (`beretta_guns`='Y' OR `beretta_sport`='Y' OR `beretta_premium`='Y' OR `beretta_clothing`='Y' OR `franchi`='Y' OR `sako`='Y' OR `sako_ammo`='Y' OR `leupold`='Y' OR `tikka`='Y' OR `benelli`='Y' OR `benelli_premium`='Y' OR `lanber`='Y' OR `arrieta`='Y' OR `atk`='Y' OR `burris`='Y' OR `accessories`='Y' OR `stoeger`='Y' OR `steiner`='Y') GROUP BY `County` ORDER BY `County` ASC",$con2);
/*$cols=ysql_query("SHOW COLUMNS FROM dealerlistings WHERE Field NOT IN('accountid','Account','Address1','Address2','City','County','Postcode','Mainphone','Email','Webaddress','GMK','LLC','beretta_guns','beretta_sport','beretta_premium','beretta_clothing')",$con2);
$brandsok="";
while($col=mysql_fetch_row($cols)){if(strlen($brandsok)>0){$brandsok.=",";}$brandsok.="'".$col[0]."'";}*/
?>
</select></label>
<label for="dealerbrand" style="white-space:nowrap">&nbsp;&nbsp;Select Brand: <select id="dealerbrand" name="dealerbrand" onchange="this.form.submit()">
<option value="all dealers" <?php if($selected_brand == ""){echo $selected;}?>>- Please select -</option>
<option value="Beretta" <?php if($selected_brand == "Beretta"){echo $selected;}?>>All Beretta Products</option>
<option value="Beretta Guns" <?php if($selected_brand == "Beretta Guns"){echo $selected;}?>>Beretta Guns</option>
<option value="Beretta Accessories" <?php if($selected_brand == "Beretta Accessories"){echo $selected;}?>>Beretta Accessories</option>
<option value="Beretta Premium" <?php if($selected_brand == "Beretta Premium"){echo $selected;}?>>Beretta Premium</option>
<option value="Beretta Clothing" <?php if($selected_brand == "Beretta Clothing"){echo $selected;}?>>Beretta Clothing</option>
<option value="Benelli Premium" <?php if($selected_brand == "Benelli Premium"){echo $selected;}?>>Benelli Premium</option>
<?php 
//$brand_query = ysql_query("SELECT	c.column_name FROM INFORMATION_SCHEMA.COLUMNS c WHERE	c.table_name = 'dealerlistings' AND c.column_name NOT IN ('accountid','Account','Address1','Address2','City','County','Postcode','Mainphone','Email','Webaddress','GMK','LLC') AND COLUMN_COMMENT = 'gmk' ORDER BY c.column_name",$con2)	or die(sql_error("Error"));
//while($listbrand = mysql_fetch_array($brand_query))
$brand_query = $db2->query("SELECT	c.column_name FROM INFORMATION_SCHEMA.COLUMNS c WHERE	c.table_name = 'dealerlistings' AND c.column_name NOT IN ('accountid','Account','Address1','Address2','City','County','Postcode','Mainphone','Email','Webaddress','GMK','LLC') AND COLUMN_COMMENT = 'gmk' ORDER BY c.column_name");
while($listbrand = $brand_query->fetch())
{
	?>
	<option value="<?=$listbrand['column_name']?>" <?php if($selected_brand == $listbrand['column_name']){echo $selected;}?>><?=ucwords(str_replace("_"," ",$listbrand['column_name']))?></option>
<?php
}
?>
</select></label>
<input id="dealersubmit" name="dealersubmit" type="submit" value="Submit" class='formbutton' />
</form>
</div>

<?php 
if($deviceType=="phone"){?></div><?php }
if(($srchbrand != ""&&$srchcounty!="") && !($srchbrand == "all dealers" && $srchcounty == "all counties"))
{
	?><div id='infobar'<?php if($deviceType=="phone"){?> style="padding: 3px 7px 3px 5px !important;"<?php }?>>
	Found <?=$resultnum?> results for <?=$srchbrand=='all dealers'?ucwords($srchbrand):"&quot;".ucwords($srchbrand)."&quot"?> in &quot;<?=ucwords(str_replace("&Amp;","&",$srchcounty))?>&quot;. <a href="./map&amp;brand=<?=urlencode($srchbrand)?><?php if(strlen($srchcounty)>0&&$srchcounty!="all counties"){?>&amp;county=<?=$srchcounty?><?php }?>" style="color:white;">View Map</a>
	</div>
	<?php 
} 

?>
<div id='dealerlist' class='opaque'>
<?php if(($srchbrand != ""&&$srchcounty!="") && !($srchbrand == "all dealers" && $srchcounty == "all counties")){
echo $searchres[1];
}
else if($srchbrand == "all dealers" && $srchcounty == "all counties") 
{ 
	?>
	<div id='infobar'>Please select at least one search option from the dropdown fields above.</div>
	<?php 
}
else
{ 
	?>
	<div id='infobar'>Please select a search option from the dropdown fields above.</div>
	<?php 
} 
?>
</div>