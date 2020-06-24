<?php $httppath = "http://www.gmk.co.uk/";if(!isset($page)){header("Location: ".$httppath."index.php");}
$emailereg = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$";
$latlng_daterenew=2;//renew google lat & lng if older than 2 days
function trimtext($text,$chars,$link)
{
	if(strlen($text) > $chars)
	{
		$text=substr($text,0,$chars);
		$text.="...";
		$text .= ($link)?" <a href='$link'>Read More &gt;&gt;</a>":"";
	}
	return $text;
}
function curl_post($url,$fields)
{
	$fields_string="";
	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string,'&');
	
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
	
	//execute post
	$rawresponse = curl_exec($ch);
	// Check that a connection was made
	if (curl_error($ch)){
		// If it wasn't...
		$output['Status'] = "FAIL";
		$output['StatusDetail'] = curl_error($ch);
	}
	
	// Close the cURL session
	curl_close ($ch);
	
	$output=json_decode($rawresponse, true);
	
	return $output;
}
function whitebox($content,$boxid,$width,$height,$useheight)
{
	global $newheight;
	$top = ($height-$newheight)/2;
	$positioning = ($useheight == 1) ? "style='position:relative;top:".$top."px;'" : "";
	
	$corner = "";
	if($boxid == "newproduct" || $boxid == "randomproduct"){$corner = "<div class='corner'><img src='./content/images/$boxid.gif' alt='' /></div>";}
	?>
	<div class='whitebox' id='<?=$boxid?>' style='width:<?=$width+15?>px;height:<?=$height+15?>px'>
	<?=$corner?>
	<div style='float:left'><img src='./content/images/white-box/tl.gif' alt='' /></div>
	<div style='float:left;width:<?=$width?>px' class='whitebox-top'><img src='./content/images/white-box/t.gif' alt='' /></div>
	<div style='float:right'><img src='./content/images/white-box/tr.gif' alt='' /></div>
	<div style='clear:both'></div>
	<div style='float:left;height:<?=$height?>px' class='whitebox-left'><img src='./content/images/white-box/l.gif' alt='' /></div>
	<div style='float:left;width:<?=$width?>px;height:<?=$height?>px;' class='whitebox-content'><div <?=$positioning;?>><?=$content?></div></div>
	<div style='float:right;height:<?=$height?>px' class='whitebox-right'><img src='./content/images/white-box/r.gif' alt='' /></div>
	<div style='clear:both'></div>
	<div style='float:left'><img src='./content/images/white-box/bl.gif' alt='' /></div>
	<div style='float:left;width:<?=$width?>px' class='whitebox-bottom'><img src='./content/images/white-box/b.gif' alt='' /></div>
	<div style='float:right'><img src='./content/images/white-box/br.gif' alt='' /></div>
	
	</div>
	<?php
}
function findreplace($ttext,$ffor="")
{
	/*if($for == "sql")
	{
		$find = array("+","%22","%26","%C2%BC","%C2%BD","%C2%BE","%2F","%20");
		$replace = array(" ","\"","&","¼","½","¾","/"," ");
		$text = str_replace($find,$replace,$text);
	}
	else if($for == "display")
	{
		$find = array("‘","’","’","\"","”","+","%22","%26","%C2%BC","%C2%BD","%C2%BE","%2F","%20","fieldcompetition","category","kg","retail","â€“","â€¦","…","™","â€","°","COLOR: windowtext","»");
		$replace = array("&acute;","&acute;","&acute;","\"","\""," ","\"","&","¼","½","¾","/"," ","Category","type","weight","rrp","-","...","...","&trade;","&acute;","&deg;","","&raquo;");
		$text = str_replace($find,$replace,htmlentities($text));
	}
	else if($for == "displayraw")
	{
		$find = array("‘","’","’","\"","”","+","%22","%26","%C2%BC","%C2%BD","%C2%BE","%2F","%20","£","Â","fieldcompetition","category","kg","retail","â€“","â€¦","…","™","®","½","¼","â€","°","COLOR: windowtext","»","1?4","1?10","1?2","–","1?8");
		$replace = array("&acute;","&acute;","&acute;","\"","\""," ","\"","&","¼","½","¾","/"," ","&pound;","","Category","type","weight","rrp","-","...","...","&trade;","&reg;","&frac12;","&frac14;","'","&deg;","","&raquo;","&frac14;","<sup>1</sup><span class='fraction'>&frasl;</span><sub>10</sub>","&frac12;","-","<sup>1</sup><span class='fraction'>&frasl;</span><sub>8</sub>");
		$text = str_replace($find,$replace,$text);
	}*/
	$ttext=htmlspecialchars($ttext,ENT_QUOTES,"ISO-8859-1");
	return $ttext;
}
function cleanBrand($b)
{
	switch (strtolower($b)) 
	{
		case "beretta":
			return "(`beretta_sport`='Y' OR `beretta_guns`='Y' OR `beretta_premium`='Y' OR `beretta_clothing`='Y')";
			break;
		case "beretta guns":
			return "(`beretta_guns`='Y' OR `beretta_premium`='Y')";
			break;
		case "beretta accessories":
			return "`beretta_sport`='Y'";
			break;
		case "beretta premium":
			return "`beretta_premium`='Y'";
			break;
		case "beretta clothing":
			return "`beretta_clothing`='Y'";
			break;
		case "benelli premium":
			return "`benelli_premium`='Y'";
			break;
		case "rcbs":
		case "speer":
		case "federal":
		case "cci":
		case "atk":
			return "`atk`='Y'";
			break;
		case "sako":
			return "(`sako`='Y' OR `sako_ammo`='Y')";
			break;
		case "tikka":
			return "`tikka`='Y'";
			break;
		case "leupold":
			return "`leupold`='Y'";
			break;
		case "franchi":
			return "`franchi`='Y'";
			break;
		case "benelli":
			return "(`benelli`='Y' OR `benelli_premium`='Y')";
			break;
		case "lanber":
			return "`lanber`='Y'";
			break;
		case "arrieta":
			return "`arrieta`='Y'";
			break;
		case "burris":
			return "`burris`='Y'";
			break;
		case "steiner":
			return "`steiner`='Y'";
			break;
		case "stoeger":
			return "`stoeger`='Y'";
			break;
		default:
			return "all dealers";
			break;
	}
}
function dealer($brand,$county,$pgnum,$perpage)
{
	global $selected_county, $selected_brand, $selected, $db1, $db2, $urlregex, $emailereg;
	$thiscounty = urldecode($county);
	$thisbrand = urldecode($brand);
	$thispgnum = intval($pgnum);
	$thisperpage = intval($perpage);
	$thisbrand=cleanBrand($thisbrand);
	if($thiscounty == "London"){
		$docity=" OR ((COALESCE(`County`,0)='0' OR `County`='') AND `City`='London'))";
	}
	else if(strlen($thisbrand) > 2 && strlen($thiscounty) > 3 && !($thisbrand == "all dealers" and $thiscounty == "all counties")){$docity=")";}
	
	if($thisbrand == "all dealers" and $thiscounty == "all counties"){$where = "";}
	else if(strlen($thisbrand) > 2 and $thiscounty == "all counties"){$where = "AND ((".$thisbrand . ")";}
	else if($thisbrand == "all dealers" and strlen($thiscounty) > 3){$where = "AND (`beretta_guns`='Y' OR `beretta_sport`='Y' OR `beretta_premium`='Y' OR `beretta_clothing`='Y' OR `franchi`='Y' OR `sako`='Y' OR `sako_ammo`='Y' OR `leupold`='Y' OR `tikka`='Y' OR `benelli`='Y' OR `benelli_premium`='Y' OR `lanber`='Y' OR `arrieta`='Y' OR `atk`='Y' OR `burris`='Y' OR `accessories`='Y' OR `stoeger`='Y' OR `steiner`='Y') AND (`County`=:county";}
	else if(strlen($thisbrand) > 2 and strlen($thiscounty) > 3){$where = "AND (" . $thisbrand . ") AND (`County`=:county";}
	
	$q="SELECT * FROM dealerlistings WHERE `accountid` != '' AND `accountid`!='{A5602769-2CCA-DF11-B8DA-00215E31A60A}' $where $docity AND `GMK`='Y' ORDER BY `Account`";
	//return $q;
	//echo $q;
	$numquery = $db2->prepare($q);
	//if(strlen($thisbrand)>0&&$thisbrand != "all dealers"){$numquery->bindValue(':brand',$thisbrand);}
	if(strlen($thiscounty)>0&&$thiscounty != "all counties"){$numquery->bindValue(':county',$thiscounty);}
	$numquery->execute();
	$thisnumdealers = $numquery->rowCount();
	//print_r($numquery->errorInfo());
	
	$start = ($thispgnum > 0 && (($thispgnum-1)*$thisperpage) <= $thisnumdealers) ? (($thispgnum-1)*$thisperpage) : 0;
	
	$result=$db2->prepare($q." LIMIT ".intval($start).",".$thisperpage);
	//if(strlen($thisbrand)>0&&$thisbrand != "all dealers"){$result->bindValue(':brand',$thisbrand);}
	if(strlen($thiscounty)>0&&$thiscounty != "all counties"){$result->bindValue(':county',$thiscounty);}
	$result->execute();
	$row = 0;
	$res="";
	while($thedealer =$result->fetch())
	{
		foreach($thedealer as $key => $val){$thedealer[$key]=htmlspecialchars($val,ENT_QUOTES);}
		$row = ($row == 1) ? 0 : 1;
		ob_start();
		?>
		<div class='dealerrow<?=$row?>'>
		<div class='dealeraccount'><?=str_replace(array("&amp;Amp;","&amp;amp;","P C Quad Ltd T/a Hill Top Shooting Ranges"),array("&amp;","&amp;","Hilltop"),$thedealer['Account'])?></div>
		<div class='dealeraddress'>
		<?php
		$webfind = array("http://","www.","www"," ","&");
		$webrepl = array("","","","","&amp;");			
		for($de=2;$de < sizeof($thedealer) && $de < 7;$de++)
		{
			if(trim($thedealer[$de]) != "" && trim($thedealer[$de]) != null){
				$words=$thedealer[$de];
				print str_replace(array("&amp;Amp;","&amp;amp;","&amp;#039;"),array("&amp;","&amp;","&#039;"),trim(htmlspecialchars($words)));
				if($de < 6){ print", ";}
			}
		}
		?>
		</div>
		<?php if(trim($thedealer['Mainphone']) != ""){?><div class='dealercontact'><strong>Tel: </strong><?=trim($thedealer['Mainphone'])?></div><?php }?>
		<?php if(preg_match('/'.$emailereg.'/', $thedealer['Email'])){?><div class='dealercontact'><strong>Email: </strong><a href='mailto:<?=strtolower(trim($thedealer['Email']))?>'><?=strtolower(trim($thedealer['Email']))?></a></div><?php }?>
		<?php if($thedealer['Webaddress'] != "" && $thedealer['Webaddress'] != "0" && strtolower($thedealer['Webaddress']) != "n/a"){?><div class='dealercontact'><strong>Web: </strong><a href='http://www.<?=str_replace($webfind, $webrepl, trim($thedealer['Webaddress']))?>' target='_blank'>www.<?=str_replace($webfind, $webrepl, trim($thedealer['Webaddress']))?></a></div><?php }?>
		<div class="clear"></div>
		</div>
		<?php
		$res.=ob_get_clean();
	}

	return array($thisnumdealers,$res);
	//print_r($return);
}
/*class dealer
{
	var $numdealers;
	var $result;
	public function initsearch()
	{
		global $db2;
		switch ($this->brand) 
		{
			case "Beretta":
				$this->brand = "`beretta_sport`='Y' OR `beretta_guns`='Y' OR `beretta_premium`='Y' OR `beretta_clothing`";
				break;
			case "Beretta Guns":
				$this->brand = "`beretta_guns`='Y' OR `beretta_premium`";
				break;
			case "Beretta Accessories":
				$this->brand = "`beretta_sport`";
				break;
			case "Beretta Premium":
				$this->brand = "`beretta_premium`";
				break;
			case "Beretta Clothing":
				$this->brand = "`beretta_clothing`";
				break;
			case "Benelli Premium":
				$this->brand = "`benelli_premium`";
				break;
			case "RCBS":
			case "Speer":
			case "Federal":
			case "CCI":
				$this->brand = "`atk`";
				break;
			case "Sako":
				$this->brand = "`sako`='Y' OR `sako_ammo`";
				break;
		}
		if($this->county == "London"){
			$docity=" OR ((COALESCE(`County`,0)='0' OR `County`='') AND `City`='London'))";
		}
		else{$docity=")";}
		if($this->brand == "all dealers" and $this->county == "all counties"){$where = "";}
		else if(strlen($this->brand) > 2 and $this->county == "all counties"){$where = "AND ((".$this->brand . "='Y')";}
		else if($this->brand == "all dealers" and strlen($this->county) > 3){$where = "AND (`beretta_guns`='Y' OR `beretta_sport`='Y' OR `beretta_premium`='Y' OR `beretta_clothing`='Y' OR `franchi`='Y' OR `sako`='Y' OR `sako_ammo`='Y' OR `leupold`='Y' OR `tikka`='Y' OR `benelli`='Y' OR `benelli_premium`='Y' OR `lanber`='Y' OR `arrieta`='Y' OR `atk`='Y' OR `burris`='Y' OR `accessories`='Y' OR `stoeger`='Y' OR `steiner`='Y') AND (`County`='" . $this->county . "'";}
		else if(strlen($this->brand) > 2 and strlen($this->county) > 3){$where = "AND (" . $this->brand . "='Y') AND (`County`='" . $this->county . "'";}
		
		$numquery=ysql_query("SELECT * FROM dealerlistings WHERE `accountid` != '' $where $docity AND `GMK`='Y' ORDER BY `Account`",$con2) or die(sql_error("Error"));
		$this->numdealers = mysql_num_rows($numquery);
		$start = ($this->pgnum > 0 && (($this->pgnum-1)*$this->perpage) <= $this->numdealers) ? (($this->pgnum-1)*$this->perpage) : 0;
		
		$this->dealersearch = ysql_query("SELECT * FROM dealerlistings WHERE `accountid` != '' $where $docity AND GMK='Y' ORDER BY `Account` LIMIT ".intval($start).",".intval($this->perpage)."",$con2) or die(sql_error("Error"));
		
		
		debug("SELECT * FROM dealerlistings WHERE `accountid` != '' $where $docity AND GMK='Y' ORDER BY Account LIMIT ".intval($start).",".intval($this->perpage)."");
	}
	public function getbrands($column,$table)
	{
		global $selected_county, $selected_brand, $selected, $db1, $db2;
		$toselect = ($column == "County") ? $selected_county : $selected_brand;
		$dealerquery = ysql_query("SELECT DISTINCT(`$column`) FROM $table ORDER BY `$column` ASC",$con1) or die(sql_error("Error"));
		while($dealeropt = mysql_fetch_row($dealerquery))
		{ 
			if(trim($dealeropt[0]) != null && trim($dealeropt[0]) != "") 
			{?>
				<option value="<?=trim($dealeropt[0])?>" <?php if($toselect == trim($dealeropt[0])){print"$selected";}?>><?=trim($dealeropt[0])?></option>
				<?php 
			}
		}
	}
	public function searchvars($brand,$county,$pgnum,$perpage)
	{
		$this->county = mysql_real_escape_string(urldecode($county));
		$this->brand = mysql_real_escape_string(urldecode($brand));
		$this->pgnum = mysql_real_escape_string(intval($pgnum));
		$this->perpage = mysql_real_escape_string(intval($perpage));
	}
	
	public function searchresults()
	{
		global $urlregex, $emailereg,$db2,$where,$docity,$result;
		$row = 0;
		while($thedealer = mysql_fetch_array($this->dealersearch))
		{
			foreach($thedealer as $key => $val){$thedealer[$key]=htmlspecialchars($val,ENT_QUOTES);}
			$row = ($row == 1) ? 0 : 1;
			?>
			<div class='dealerrow<?=$row?>'>
			<div class='dealeraccount'><?=str_replace(array("&amp;Amp;","&amp;amp;","P C Quad Ltd T/a Hill Top Shooting Ranges"),array("&amp;","&amp;","Hilltop"),$thedealer['Account'])?></div>
			<div class='dealeraddress'>
			<?php
			$webfind = array("http://","www.","www"," ","&");
			$webrepl = array("","","","","&amp;");			
			for($de=2;$de < sizeof($thedealer) && $de < 7;$de++)
			{
				if(trim($thedealer[$de]) != "" && trim($thedealer[$de]) != null){
					$words=$thedealer[$de];
					print str_replace(array("&amp;Amp;","&amp;amp;","&amp;#039;"),array("&amp;","&amp;","&#039;"),trim(htmlspecialchars($words)));
					if($de < 6){ print", ";}
				}
			}
			?>
			</div>
			<?php if(trim($thedealer['Mainphone']) != ""){?><div class='dealercontact'><strong>Tel: </strong><?=trim($thedealer['Mainphone'])?></div><?php }?>
			<?php if(eregi($emailereg, $thedealer['Email'])){?><div class='dealercontact'><strong>Email: </strong><a href='mailto:<?=strtolower(trim($thedealer['Email']))?>'><?=strtolower(trim($thedealer['Email']))?></a></div><?php }?>
			<?php if($thedealer['Webaddress'] != "" && $thedealer['Webaddress'] != "0" && strtolower($thedealer['Webaddress']) != "n/a"){?><div class='dealercontact'><strong>Web: </strong><a href='http://www.<?=str_replace($webfind, $webrepl, trim($thedealer['Webaddress']))?>' target='_blank'>www.<?=str_replace($webfind, $webrepl, trim($thedealer['Webaddress']))?></a></div><?php }?>
			<div class="clear"></div>
			</div>
			<?php
		}
	}
	function searchresultsnum()
	{
		return $this->numdealers;
	}
}*/
function printlist($listarr,$cwidth,$left)
{
	?>
	
	<ul><?php
	for($x=1;$x<sizeof($listarr);$x++)
	{
		$ddbottomborder = ($x == sizeof($listarr)-1) ? "border-bottom:1px solid #2d3253;margin:0px;" : "";
		$dropdownbl = ($x == sizeof($listarr)-1) ? "<span class='dropdownbl'><img src='./content/images/dropdownbl.png' alt='' /></span>" : "";
		$dropdownbr = ($x == sizeof($listarr)-1) ? "<span class='dropdownbr'><img src='./content/images/dropdownbr.png' alt='' /></span>" : "";
		$litop = ($x == sizeof($listarr)-1) ? ($x-1)*20 : (($x-1)*20)+1; 
		?>
		<li style='width:<?=$cwidth?>px;position:absolute;top:<?=$litop?>px;left:<?=$left?>px;border-left:1px solid #2d3253;border-right:1px solid #2d3253;border-top:0px;<?=$ddbottomborder?>'>
		<?=$dropdownbl?>
		<?php if(is_array($listarr[$x][0])){?>
		<a href='<?=$listarr[$x][0][2]?>'><?=str_replace(" and "," &amp; ",$listarr[$x][0][0])?></a>
		<?php }else{?>
		<a href='<?=$listarr[$x][2]?>'><?=str_replace(" and "," &amp; ",$listarr[$x][0])?></a>
		<?php }?>
		<?php if(is_array($listarr[$x][0])){printlist($listarr[$x],($listarr[$x][0][1]-3),$cwidth);}?>
		<?=$dropdownbr?>
		</li>
		<?php
	}
	?></ul><?php
}

function scaleimg($image,$maxwidth,$maxheight)
{
	global $newheight;
	if(file_exists($image))
	{
		list($w,$h) = getimagesize($image);
		//$maxheight = ($maxheight == "") ? $h : $maxheight;
		//$maxwidth = ($maxwidth == "") ? $w : $maxwidth;
		if(!($w < $maxwidth && $h < $maxheight))//where image is smaller in BOTH width and height, do nothing.
		{
			if(!($w > $maxwidth && $h > $maxheight))//where image is not bigger in BOTH width and height, than maxwidth and maxheight
			{
				if($h < $maxheight && $w > $maxwidth)//where maxheight is larger than image, but maxwidth is smaller, scale the height based on maxwidth and set width to maxwidth
				{$newwidth = $maxwidth;$newheight = round($newwidth * ($h/$w));}
				else if($h > $maxheight && $w < $maxwidth)//where maxwidth is larger than image, but maxheight is smaller, scale the width based on maxheight and set height to maxheight
				{$newwidth = round($maxheight * ($w/$h));$newheight = $maxheight;}
			}
			else//where image is bigger in both width and height than maxwidth and maxheight
			{
				if($w > $h){$newwidth = $maxwidth;$newheight = round($newwidth * ($h/$w));}
				else if($w < $h){$newheight = $maxheight;$newwidth = round($newheight * ($w/$h));}
			}
		}
			if(!$newwidth){$newwidth = $w;}
			if(!$newheight){$newheight = $h;}
			return "width='".$newwidth."px' height='".$newheight."px'";
	}
}
function debug($text)
{
	global $dev;
	if($dev == 1){
	?>
	<div id='debug' style='display:none;'><?=$text?></div>
	<?php
	}
}
function countiesoptions($query,$connex)
{
	global $selected_county, $db2;
	//$countries_query = ysql_query($query,$connex) or die(sql_error("Error"));
	$countries_query = $db2->query($query);
	$currentcountry = "";
	$group =0;
	//while($country = mysql_fetch_array($countries_query))
	while($country=$countries_query->fetch())
	{
		if($group == 0){$group = 1;$currentcountry = $country['Country'];?> <optgroup label="<?=$currentcountry?>"> <?php }
		$tcounty = trim($country['County']);
		if($currentcountry != $country['Country']){$currentcountry = $country['Country'];?> </optgroup><optgroup label="<?=$currentcountry?>"> <?php }
		
		$tcounty1=str_replace("&amp;","&",strtolower($tcounty));
		$tcounty1=htmlspecialchars($tcounty1,ENT_QUOTES,"ISO-8859-1");
		//if stristr &amp; 
		?>
		<option value='<?=$tcounty?>' <?php if($selected_county == $tcounty){ ?>selected='selected'<?php }?>><?=ucwords($tcounty1)?></option>
		<?php
	}
	?></optgroup><?php
}
function dateoptions($inmonth,$inday,$inyear)
{
	global $months, $ismsie;
	//MONTHS -----------------------
	?><select name='wr_month' id='wr_month' onchange='javascript:getDaysInMonth("wr_month","wr_year","wr_days")' <?php if($ismsie == 1){?>style='background:white'<?php }?>><?php 
	for($x=1;$x<=12;$x++)
	{
		?><option value='<?=$x?>' <?php if($inmonth == $x){?>selected='selected'<?php }?>><?=date('M',mktime(0,0,0,$x,1,0))?></option><?php
	}
	?>
	</select>
	<script type="text/javascript"><?php $wr_days = date('t',mktime(0,0,0,$inmonth,1,$inyear));?></script>
	<?php getdays($inday,$inmonth,$inyear);?>
	<select name='wr_year' id='wr_year' onchange='javascript:getDaysInMonth("wr_month","wr_year","wr_days")' <?php if($ismsie == 1){?>style='background:white'<?php }?>>
	<?php
			for($x=date('Y');$x >= 1976;$x--)
			{
				?>
				<option value='<?=$x?>' <?php if($inyear == $x){?>selected='selected'<?php }?>><?=$x?></option>
				<?php  
			}
	?>
	</select>
	
	<!--<input type='year' name='wr_year' id='wr_year' size='5' maxlength='4' value='' onblur='javascript:fixYearInput(this)' onkeypress='return yearDigitsOnly(event,this)' onkeyup='javascript:checkValidYear(this)' />-->
	<?php
}
function getdays($thisday,$thismonth,$thisyear)
{
	global $ismsie;
	?>
	<select name='wr_days' id='wr_days' <?php if(isset($_GET['daysover'])){?>style='border:1px solid red;background:#996767'<?php }else if($ismsie == 1){?>style='background:white'<?php }?>>
	<?php 
	if(!isset($wr_days)){$wr_days = 31;}//if JS not availavle, $days will not have been set
	for ($x=1;$x <= $wr_days;$x++)
	{
		?><option value='<?=$x?>' <?php if($thisday == $x){?>selected='selected'<?php }?>><?=$x?></option><?php
	}
	?>
	</select>
	<?php
}
function pagenav($total,$perpage,$inurl,$maxpagelinks)
{
	global $pgstart, $pgend;
	$pgnum = (isset($_GET['pgnum'])) ? $_GET['pgnum'] : 1;
	$pgstart = ($pgnum > 0 && (($pgnum-1)*$perpage) <= $total) ? (($pgnum-1)*$perpage) : 0;
	$pgend = ($pgstart+$perpage >= $total) ? $total : $pgstart+$perpage;
	if($total > $perpage)
	{
		$totalpages = ceil($total/$perpage);//raw pages
		$backlink = ($pgnum > 1) ? "<a href='$inurl&amp;pgnum=".($pgnum-1)."'><img src='./content/images/main/on_back.jpg' alt='&laquo; BACK' /></a>" : "<img src='./content/images/main/off_back.jpg' alt='' />";
		$nextlink = ($pgnum < $totalpages) ? "<a href='$inurl&amp;pgnum=".($pgnum+1)."'><img src='./content/images/main/on_next.jpg' alt='&nbsp;NEXT &raquo;' /></a>" : "<img src='./content/images/main/off_next.jpg' alt='' />";
		$paginationstart = ($pgnum > ceil($maxpagelinks/2) && !($totalpages < $maxpagelinks && $pgnum == $totalpages)) ? ((($pgnum < $totalpages-floor($maxpagelinks/2)) ? $pgnum-($maxpagelinks-3) : ($totalpages-$maxpagelinks+1))) : 1;		
		$pgnumbers = "";
		for($p=$paginationstart;$p<=$totalpages && $p < $paginationstart+5;$p++)
		{
			if($p == $pgnum){
				$pgnumbers.="<span class='pagelinkon'>$p</span>";
			}else{
				$pgnumbers.="<a href='$inurl&amp;pgnum=$p' class='pagelink'>$p</a>";
			}
		}
		?>
		<div id='pagination' class='pagination'><span class='desc'><?=$totalpages?> PAGES:</span><?=$backlink?> <?=$pgnumbers?> <?=$nextlink?></div>
		<?php
	}
}
function gmkmysql_error($error,$query)
{
	echo "<div class='notify'><span style='text-decoration:underline'>There was an error with the MYSQL query</span><br /><br /><span>Error:</span> &quot;$error&quot;<br /><br /><span>Query:</span><br />$query</div>";
}
function geocode($address)
{
	$xml="";
	$f = fopen( 'http://maps.googleapis.com/maps/api/geocode/xml?address='.urlencode($address).'&sensor=false', 'r' );
	if(!$f){echo "error";}
	while( $data = fread( $f, 4096 ) ) { $xml .= $data; }
	fclose( $f );
	preg_match_all( "/\<location\>(.*?)\<\/location\>/s", $xml, $locations );
	foreach($locations[1] as $location)
	{
		preg_match_all( "/\<lng\>(.*?)\<\/lng\>/", $location, $lng );
		preg_match_all( "/\<lat\>(.*?)\<\/lat\>/", $location, $lat );
	}
	return array($lat[1][0],$lng[1][0]);
}
function mapmarkers($info,$inc)
{
	global $latlng_daterenew,$brand,$db2;
	$dblatlng=1;
	$cleaninfo=array();
	foreach($info as $key => $val){$cleaninfo[$key]=htmlspecialchars($val,ENT_QUOTES);}
	$address=ucwords(strtolower($cleaninfo['Address1'])).",";
	if(strlen($info['Address2'])>0){$address.=ucwords(strtolower($cleaninfo['Address2'])).",";}
	if(strlen($info['City'])>0){$address.=ucwords(strtolower($cleaninfo['City'])).",";}
	if(strlen($info['County'])>0){$address.=ucwords(strtolower($cleaninfo['County'])).",";}
	if(strlen($info['Postcode'])>0){$address.=$cleaninfo['Postcode'].",";}
	$address.="UK";
	//echo $address;
	if($info['acid']==null||$info['dateadded']<date("U")-(86400*$latlng_daterenew)||$info['lat']==null||$info['lng']==null)//empty lng/lat or if newly entered
	{
		$latlng=geocode($address);
		if(strlen($latlng[0])<1){$latlng=geocode($cleaninfo['Address1'].",".$cleaninfo['Postcode'].",UK");}
		if(strlen($latlng[0])>0)
		{
			//ysql_query("INSERT INTO global.dealerlistings_latlng(`acid`,`lat`,`lng`,`dateadded`)VALUES('".$info['accountid']."','".$latlng[0]."','".$latlng[1]."','".date("U")."')");$dblatlng=0;
			if($info['acid']==null)
			{$ins=$db2->prepare("INSERT INTO dealerlistings_latlng(`acid`,`lat`,`lng`,`dateadded`)VALUES(:aid,:lat,:lng,:date)");}
			else if($info['dateadded']<date("U")-(86400*$latlng_daterenew)||$info['lat']==null||$info['lng']==null)
			{$ins=$db2->prepare("UPDATE dealerlistings_latlng SET `lat`=:lat,`lng`=:lng,`dateadded`=:date WHERE `acid`=:aid");}
			$ins->bindValue(':aid',$info['accountid']);
			$ins->bindValue(':lat',$latlng[0]);
			$ins->bindValue(':lng',$latlng[1]);
			$ins->bindValue(':date',date("U"));
			$ins->execute();
			$dblatlng=0;
		}
	}
	if($dblatlng==1)
	{
		$latlng=array($info['lat'],$info['lng']);
	}
	if($info['beretta_clothing']=='Y'&&$info['beretta_guns']=='N'&&$info['beretta_sport']=='N'&&$info['beretta_premium']=='N'&&$brand=="Beretta")
	{ $img="beretta_clothing";}
	else if($info['beretta_clothing']=='N'&&$brand=="Beretta")
	{ $img="beretta_no_clothing";}
	else
	{$img="default";}
	$infocontent="<div><strong>".$cleaninfo['Account']."<\/strong>";
	if ($info['beretta_premium'] == "True") {
		$infocontent.="<span style='color:#0091B6; font-weight:bold;'>P<\/span>";
	}
	$infocontent.="<br \/>".str_replace(",",", ",$address);
	if(strlen($info['Mainphone'])>0){
		$infocontent.="<br \/>Tel: ".$cleaninfo['Mainphone'];
	}
	if(strlen($info['Email'])>0){
		$infocontent.="<br \/>Email: <a href=\"mailto:".$cleaninfo['Email']."\">".$cleaninfo['Email']."<\/a>";
	}
	if(strlen($info['Webaddress'])>0&&$info['Webaddress']!='0'){
		$infocontent.="<br \/>Web: <a href=\"http://".str_replace("http://","",$cleaninfo['Webaddress'])."\" target=\"_blank\">".$cleaninfo['Webaddress']."<\/a>";
	}
	$infocontent.="<\/div>";
	$preg_account=preg_replace("/[^a-zA-Z0-9]/", "", $info['Account']);
	?>
	<script type="text/javascript">
	//<![CDATA[
	var <?=$preg_account?> = new google.maps.LatLng(<?=$latlng[0]?>, <?=$latlng[1]?>);
	addMarker(<?=$preg_account?>,'content/images/mapmarkers/<?=$img?>.png','<?=$cleaninfo['Account']?>','<?=$infocontent?>');
	//]]>
	</script>
	<?php
}
function stripAccents($stripAccents){
  return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}
function genWarrantyXML($info)
{
	//$where=strlen($vendorcode)>0?"i.VendorTxCode='$vendorcode'":"b.id=$id";
	//$query_string="SELECT *,b.id,b.firstname,b.lastname,b.address1,b.address2,b.city,b.county,b.postcode,b.email FROM ".BOOKTABLE." as b JOIN ".INVTABLE." as i ON b.invoice=i.id WHERE $where AND b.ordertype='ticket'";
	//$query = mysql_query($query_string)or die("query '$query_string' failed<br />".mysql_error());
	//$groupcount=mysql_num_rows($query);
	//$fin=array("21s","18s");
	//$rep=array("21's","18's");
	//while($info=mysql_fetch_assoc($query))
	//{	
	//	if(!isset($bookdate)){$bookdate=date("d/m/Y G:i",$info['date_ordered']);}//date("d/m/Y G:i")
						
		$el=array(
		//"web_order_number"=>$info['id'],
		//"shoot_name"=>CRMSHOOTNAME,
		//"session_date"=>date("l jS F Y",$info['firstshoot']),
		//"session_time"=>date("H:i",$info['firstshoot']),
		//"main_category"=>str_replace($fin,$rep,$info['categoryadd']),
		//"optional_category"=>"",/*str_replace($fin,$rep,$info['category']),*/
		//"cpsa_class"=>strtoupper($info['class']),
		//"cpsa_number"=>$info['orgref'],
		//"club_beretta_number"=>"",
		//"cost"=>$info['amount'],
		"title"=>$info[0],
		"first_name"=>stripAccents(trim($info[1])),
		"last_name"=>stripAccents(trim($info[2])),
		"address_1"=>$info[3],
		"address_2"=>$info[4],
		"address_3"=>'',
		"city"=>$info[5],
		"county"=>$info[6],
		"postcode"=>$info[7],
		"telephone"=>$info[8],
		//"booking_date"=>$bookdate,
		//"group_booking"=>(strlen($info['groupref'])>0&&$info['groupref']!=0)||$groupcount>1?$info['groupref']:"",
		"email"=>$info[9],
		//"payment_method"=>$payment_method
		);
		$doc = new DOMDocument();		
		$doc->formatOutput = true; 		 
		$a = $doc->createElement( "beretta_world" ); //keep same as bw for the import to work
		$doc->appendChild( $a ); 	
		$b = $doc->createElement( "bw_head" ); // bw_head start
		$xmlmessage="";
		foreach($el as $name => $value)
		{
			$element = $doc->createElement( $name ); 
			$element->appendChild($doc->createTextNode( $value )); 
			$b->appendChild( $element );
			$xmlmessage.=$name." = ".$value.PHP_EOL;
		}		
		$a->appendChild( $b ); // bw_head end	 
		//echo $doc->saveXML(); 
		//$pre=$payment_method=="Web"&&SAGEPAYSERVERTYPE=="SERVER"?"./":"./";
		if(@$doc->save('./warrantyorders/GMKWO' . $info[10] . '.xml')){}
		else{/*@mail("senfield@gmk.co.uk","xml function","xml errored: ".$vendorcode."\r\n".$pre.'bookings/'.SHORTNAME . $info['id'] . '.xml',"From: sales@gmk.co.uk");*/}
	//}
}
?>