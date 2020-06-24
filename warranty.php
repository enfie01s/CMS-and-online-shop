<?php $httppath = "http://www.gmk.co.uk/";if(!isset($page)){header("Location: ".$httppath."index.php");}?>
<?=$_SESSION['test']==1?"TEST MODE: ".print_r($_SESSION['cart']):""?>
<?php //print_r($requireds['doregister']);?>
<script src="./content/js/calendar.js" type="text/javascript"></script>

<div><img src="./content/images/main/title_warranty.jpg" alt="Warranty Registration" /></div>
<?php if(isset($_REQUEST['brand'])&&$_REQUEST['brand']=="BERETTA"){?><div>Claim your free extended Beretta warranty</div><br /><?php } ?>
All extended warranty registrations must be made within 30 days of purchasing your <u>new</u> gun. <a href="./warranty_terms">Terms and conditions</a> apply. <br />
<br />
<?php if(isset($_REQUEST['brand'])&&$_REQUEST['brand']=="BERETTA"){?>
A standard one year warranty is provided with all new, GMK imported, Beretta shotguns. However, you can extend this warranty to three years free of charge by filling in the form below. In addition, GMK offers a ten year warranty (by using the form below) for a one-off payment of &pound;50. <br />
<br />
Please <a href="./contact">Contact Us</a> to request a registration card.<br />
<br />
<div class='heading' style='font-weight:bold;font-size:1.2em;'>Beretta 10 Year Warranty</div>
You can register your 10 year warranty using the form below (Beretta must be the selected brand), by phone or by post (for registration by post please include your name, address, date of purchase, product and serial number along with your cheque made payable to &quot;GMK Ltd.&quot;). Please refer to our <a href="?p=contact">Contact Us</a> page for our phone number and address. <br />
<br />
<div class='heading' style='font-weight:bold;font-size:1.2em;'>3 Year Warranty</div>
To claim for your three year extended warranty, simply complete the Warranty Registration form below. <br />
<?php }?>
<br />
<br />
<?php if(!isset($_REQUEST['brand'])){?>
<div class='heading' style='text-decoration:underline;font-size:1.8em;margin-bottom:10px;color:#FFF'>To register your warranty online, please click the brand of your gun</div>
<form action='./warranty' method='post' name='warranty_brand' id='warranty_brand'>
<?php
$brandsQ=$db1->query("SELECT Brand FROM gmkserialnums WHERE Brand != 'ARRIETA' GROUP BY Brand");
while(list($brand)=$brandsQ->fetch(PDO::FETCH_NUM))
{
	?>
	<button type="submit" name="brand" value="<?=$brand?>" style="border:0;background:transparent;padding:0;cursor:pointer"><img src="./content/images/warranty/<?=strtolower($brand)?>.jpg" alt="<?=$brand?>" /></button>
	<?php
}
?>
</form>
<?php }else{?>
<br />
<br />
<form action='./warrantysubmit' method='post' name='warranty_reg' id='warranty_reg' onsubmit="return formCheck(this);">
	<input type='hidden' class='hidden' name='shouldbeempty' />
	<input type='hidden' class='hidden' name='required' value='<?=implode(",",$required)?>' />
	<div style="width:<?=$deviceType=="phone"?"100":"90"?>%;margin:0px auto;">
		<fieldset>
			<legend>Warranty registration form</legend>
			<?			
			$gmonth = (isset($_GET['wr_month'])) ? $_GET['wr_month'] : date('n');
			$gday = (isset($_GET['wr_days'])) ? $_GET['wr_days'] : date('j');
			$gyear = (isset($_GET['wr_year'])) ? $_GET['wr_year'] : date('Y');
			?>			
			
<!-- NEW-->
			
<table class="emailform">
	<tbody>
		<tr>
			<td class="formlabel" style="width:<?=$deviceType=="phone"?"100":"50"?>%;<?=$deviceType=="phone"?"text-align:left !important;":""?>"><span id='serialinfo' class='fieldinfo'<?=isset($_GET['serial'])&&trim($_GET['serial'])==null?" style='display:inline;'":""?>>! </span><span id='serialinfo1' class='fieldinfo' <?=isset($_GET['invalidserial'])?" style='display:inline;'":""?>>&#8224; </span>Serial No *</td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;position:relative;top:0;left:0;" class="forminput"><input type="text" style=' <?=(isset($_GET['serial'])&&trim($_GET['serial'])==null)||isset($_GET['invalidserial'])?"border:1px solid red;background:#996767;":""?>' onkeyup="if(this.style.borderColor=='red'){ajax('warrantyserial',this)}" onblur="ajax('warrantyserial',this);" value="<?=isset($_GET['serial'])?$_GET['serial']:""?>" maxlength="22" size="28" name="serial" id="serial" />
			<ul id="suggestbox" style="display:none">
			</ul>
			</td>
		</tr>
		
		
		<tr>
			<td class="formlabel" style="width:<?=$deviceType=="phone"?"100":"50"?>%;<?=$deviceType=="phone"?"text-align:left !important;":""?>"><span id='brandinfo' class='fieldinfo'<?=isset($_GET['brand'])&&(trim($_GET['brand'])==null||!isset($_GET['invalidserial']))?" style='display:inline;'":""?>>! </span><span id='brandinfo1' class='fieldinfo'<?=isset($_GET['brand'])&&strlen(trim($_GET['brand']))>0&&isset($_GET['invalidserial'])?" style='display:inline;'":""?>>&#8224; </span>Brand *</td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput">
			
			
			<?php if(0){?>
			<select onchange="ajax('warrantybrand',this)" onblur="ajax('warrantybrand',this);" id="brand" name="brand" style=' <?=isset($_GET['brand'])&&(trim($_GET['brand'])==null||isset($_GET['invalidserial']))?"border:1px solid red;background:#996767;":($ismsie == 1?"background:white":"")?>'>
				<option value=""<?=!isset($_GET['brand'])||$_GET['brand']==""?" selected='selected'":""?>>Select brand...</option>
				<?php
				/*
				$brandsQ=ysql_query("SELECT Brand FROM gmkserialnums GROUP BY Brand",CON1);
				while(list($brand)=mysql_fetch_row($brandsQ))
				*/
				
				$brandsQ=$db1->query("SELECT Brand FROM gmkserialnums GROUP BY Brand");
				while(list($brand)=$brandsQ->fetch())
				{
					$b=ucwords(strtolower($brand));
					?><option value="<?=$brand?>"<?=isset($_GET['brand'])&&trim($_GET['brand'])==$brand?" selected='selected'":""?>><?=$b?></option><?php
				}
				?>
			</select>
			<?php }else{?><?=$_REQUEST['brand']?> [<a href="?p=warranty">Change</a>]<input type="hidden" name="brand" id="brand" value="<?=$_REQUEST['brand']?>" /><?php }?>
			
			
			</td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="productinfo"<?=isset($_GET['serial'])&&trim($_GET['serial'])==null?" style='display:inline;'":""?>>! </span><span class="fieldinfo" id="productinfo1">&#8224; </span>Product * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style="width:100%;<?=isset($_GET['product'])&&trim($_GET['product'])==null?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['product'])?stripslashes($_GET['product']):""?>" name="product" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="purchasedateinfo"<?=isset($_GET['purchasedate'])&&trim($_GET['purchasedate'])==null?" style='display:inline;'":""?>>! </span>Date Purchased * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><script type="text/javascript">DateInput('date', true, 'DD-MM-YYYY')</script>
				<noscript>
				<?=dateoptions($gmonth,$gday,$gyear);?>
				</noscript></td>
		</tr>
		<tr>
			<td style="width:50%;vertical-align:top;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="fromshopinfo"<?=isset($_GET['fromshop'])&&trim($_GET['fromshop'])==null?" style='display:inline;'":""?>>! </span>Purchased From * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><select onblur="javascript:validatefield(this,'','warranty_reg')" id="fromshop" name="fromshop" style=' <?=isset($_GET['fromshop'])&&trim($_GET['fromshop'])==null?"border:1px solid red;background:#996767;":($ismsie == 1?"background:white":"")?>'>
					<option value="">Select dealer...<?=str_repeat("&nbsp;",30)?></option>
					<?php $shops=array();
				/*$qc=ysql_query("SELECT `County` FROM dealerlistings WHERE `GMK`='Y' AND accountid!='{A5602769-2CCA-DF11-B8DA-00215E31A60A}' GROUP BY `County`",$con2);
				while($rc=mysql_fetch_row($qc))*/
				$qc=$db2->query("SELECT `County` FROM dealerlistings WHERE `GMK`='Y' AND accountid!='{A5602769-2CCA-DF11-B8DA-00215E31A60A}' GROUP BY `County` ORDER BY `County`");
				while($rc=$qc->fetch())
				{
					?>
					<optgroup label="<?=$rc[0]?>">
						<?php
						/*$q=ysql_query("SELECT distinct(`Account`) FROM dealerlistings WHERE `County`='".$rc[0]."' AND `GMK`='Y' AND accountid!='{A5602769-2CCA-DF11-B8DA-00215E31A60A}' ORDER BY `Account`",$con2);
						while($r=mysql_fetch_row($q))*/
						$q=$db2->query("SELECT distinct(`Account`) FROM dealerlistings WHERE `County`='".$rc[0]."' AND `GMK`='Y' AND accountid!='{A5602769-2CCA-DF11-B8DA-00215E31A60A}' ORDER BY `County`,`Account`");
						while($r=$q->fetch())
						{
							$shops[]=$r[0];
							?>
							<option value='<?=str_replace("'","",$r[0])?>' <?=isset($_GET['fromshop'])&&$_GET['fromshop']==str_replace("'","",$r[0])?"selected='selected'":""?>><?=$r[0]?></option>
							<?php 
						}
						?>
					</optgroup>
					<?php 
				}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="nametitleinfo"<?=isset($_GET['nametitle'])&&trim($_GET['nametitle'])==null?" style='display:inline;'":""?>>! </span>Title * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style=" <?=isset($_GET['nametitle'])&&trim($_GET['nametitle'])==null?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['nametitle'])?$_GET['nametitle']:""?>" maxlength="" size="28" name="nametitle" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="firstnameinfo"<?=isset($_GET['firstname'])&&trim($_GET['firstname'])==null?" style='display:inline;'":""?>>! </span>First Name * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style=" <?=isset($_GET['firstname'])&&trim($_GET['firstname'])==null?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['firstname'])?$_GET['firstname']:""?>" maxlength="40" size="28" name="firstname" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="lastnameinfo"<?=isset($_GET['lastname'])&&trim($_GET['lastname'])==null?" style='display:inline;'":""?>>! </span>Last Name * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style=" <?=isset($_GET['lastname'])&&trim($_GET['lastname'])==null?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['lastname'])?$_GET['lastname']:""?>" maxlength="40" size="28" name="lastname" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="address1info"<?=isset($_GET['address1'])&&trim($_GET['address1'])==null?" style='display:inline;'":""?>>! </span>Address Line 1 * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style=" <?=isset($_GET['address1'])&&trim($_GET['address1'])==null?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['address1'])?$_GET['address1']:""?>" maxlength="30" size="28" name="address1" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel">Address Line 2</td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['address2'])?$_GET['address2']:""?>" maxlength="30" size="28" name="address2" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="cityinfo"<?=isset($_GET['city'])&&trim($_GET['city'])==null?" style='display:inline;'":""?>>! </span>Town / City * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style=" <?=isset($_GET['city'])&&trim($_GET['city'])==null?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['city'])?$_GET['city']:""?>" maxlength="16" size="28" name="city" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="countyinfo"<?=isset($_GET['county'])&&trim($_GET['county'])==null?" style='display:inline;'":""?>>! </span>County * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><select onblur="javascript:validatefield(this,'','warranty_reg')" id="county" name="county" style=" <?=isset($_GET['county'])&&trim($_GET['county'])==null?"border:1px solid red;background:#996767;":""?>">
			<option value=''>Please select...<?=str_repeat("&nbsp;",20)?></option>
			<?php countiesoptions("SELECT * FROM counties WHERE `Country` IS NOT NULL AND `Country` !='' ORDER BY `Country` ASC, `County` ASC",$con2);?>
				</select></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="postcodeinfo"<?=isset($_GET['postcode'])&&trim($_GET['postcode'])==null?" style='display:inline;'":""?>>! </span>Postcode * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style=" <?=isset($_GET['postcode'])&&trim($_GET['postcode'])==null?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['postcode'])?$_GET['postcode']:""?>" maxlength="8" size="28" name="postcode" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel">Telephone</td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['telephone'])?$_GET['telephone']:""?>" maxlength="16" size="28" name="telephone" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="emailinfo"<?=isset($_GET['email'])&&trim($_GET['email'])==null?" style='display:inline;'":""?>>! </span><span class="fieldinfo" id="emailinfo1"<?=isset($_GET['email'])&&trim($_GET['email'])!=null&&!eregi($emailereg, $_GET['email'])?" style='display:inline;'":""?>>&#8224; </span>Email Address * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="text" style=" <?=(isset($_GET['email'])&&trim($_GET['email'])==null)||(isset($_GET['email'])&&trim($_GET['email'])!=null&&!eregi($emailereg, $_GET['email']))?"border:1px solid red;background:#996767;":""?>" onblur="javascript:validatefield(this,'','warranty_reg')" value="<?=isset($_GET['email'])?$_GET['email']:""?>" maxlength="50" size="28" name="email" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="skuvariant[358]info"<?=isset($_GET['skuvariant[358]'])&&trim($_GET['skuvariant[358]'])==null?" style='display:inline;'":""?>>! </span><span class="fieldinfo" id="skuvariant[358]info1">&#8224; </span>Warranty Length * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><select onblur="javascript:validatefield(this,'','warranty_reg')" id="skuvariant[358]" name="skuvariant[358]" style=" <?=isset($_GET['skuvariant[358]'])&&trim($_GET['skuvariant[358]'])==null?"border:1px solid red;background:#996767;":""?>">
					<option value="">Please select...</option>
					<?php /*
					$q=ysql_query("SELECT * FROM cart_variants WHERE `pid`='358' ORDER BY `order`");
					while($r=mysql_fetch_assoc($q))*/
					$modeldiffs=array("WARRANTY3-v-NONE"=>"ALCIONE","WARRANTY7-v-NONE"=>"AFFINITY");
					$q=$db1->query("SELECT * FROM cart_variants WHERE `pid`='358' ORDER BY `order`");
					while($r=$q->fetch())
					{
						$disabled=1;
						if($r['vid']=="1215"&&($_REQUEST['brand']=="BERETTA"||$_REQUEST['brand']=="FRANCHI")){$disabled=0;}//3 year option
						elseif($r['vid']=="1303"&&$_REQUEST['brand']=="FRANCHI"){$disabled=0;}//7 year option
						elseif($r['vid']=="1216"&&$_REQUEST['brand']=="BERETTA"){$disabled=0;}//10 year option
						elseif($r['vid']=="1290"&&$_REQUEST['brand']=="BENELLI"){$disabled=0;}
						elseif($r['vid']=="1288"&&($_REQUEST['brand']=="STOEGER"||$_REQUEST['brand']=="LANBER")){$disabled=0;}
						elseif($r['vid']=="1289"&&($_REQUEST['brand']=="SAKO"||$_REQUEST['brand']=="TIKKA")){$disabled=0;}
						/*
	if($_POST['brand']=="FRANCHI")
	{
		mod=prod.search("ALCIONE")!=-1?3:(prod.search("AFFINITY")!=-1?7:0);
	}
	*/
						if($disabled==0)
						{
						?>
						<option value='<?=$r['vid']?>' id='<?=$r['vskuvar']?>' <?=isset($_GET['skuvariant'])&&$_GET['skuvariant']==$r['vid']?"selected='selected'":""?><?=!isset($_GET['skuvariant'][358])||$_GET['skuvariant'][358]!=$r['vid']?" ".$disabled:""?>><?=$r['vname']?><?=$_REQUEST['brand']=="FRANCHI"?" (".$modeldiffs[$r['vskuvar']]." model only)":""?></option>
						<?php 
						}
					}?>
				</select></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="areaofinterestinfo"<?=isset($_GET['areaofinterest'])&&trim($_GET['areaofinterest'])==null?" style='display:inline;'":""?>>! </span><span class="fieldinfo" id="areaofinterestinfo1">&#8224; </span>Your area of interest * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><label for="Rifle Shooting">Rifle Shooting </label>
				<input type="checkbox" id="Rifle Shooting" value="Rifle Shooting" name="areaofinterest[]" <?=isset($_GET['areaofinterest'])&&stristr($_GET['areaofinterest'],"Rifle Shooting")!==false?"checked='checked'":""?> />
				<label for="Game Shooting">Game Shooting </label>
				<input type="checkbox" id="Game Shooting" value="Game Shooting" name="areaofinterest[]" <?=isset($_GET['areaofinterest'])&&stristr($_GET['areaofinterest'],"Game Shooting")!==false?"checked='checked'":""?> />
				<br />
				<label for="Competition/Clay Shooting">Competition/Clay Shooting </label>
				<input type="checkbox" id="Competition/Clay Shooting" value="Competition/Clay Shooting" name="areaofinterest[]" <?=isset($_GET['areaofinterest'])&&stristr($_GET['areaofinterest'],"Competition/Clay Shooting")!==false?"checked='checked'":""?> /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="mailinglistinfo"<?=isset($_GET['mailinglist'])&&trim($_GET['mailinglist'])==null?" style='display:inline;'":""?>>! </span><span class="fieldinfo" id="mailinglistinfo1">&#8224; </span>Receive information via Email?**</td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="checkbox" checked="checked" value="Yes" name="mailinglist" id="mailinglist" class="checkbox" /></td>
		</tr>
		<tr>
			<td style="width:50%;<?=$deviceType=="phone"?"text-align:left !important;":""?>" class="formlabel"><span class="fieldinfo" id="termsagreeinfo"<?=isset($_GET['termsagree'])&&trim($_GET['termsagree'])==null?" style='display:inline;'":""?>>! </span>I confirm the above details are correct and I have read and agree to the <a target="_blank" href="./warranty_terms">terms &amp; conditions</a> * </td>
			<?=$deviceType=="phone"?"</tr><tr>":""?>
			<td style="width:50%;" class="forminput"><input type="checkbox" value="Yes" name="termsagree" id="termsagree" class="checkbox" /></td>
		</tr>
		<tr>
			<td style="text-align:center" <?=$deviceType=="phone"?"":"colspan='2'"?>><span style="font-size:10px;">* Fields marked with an asterisk are compulsory, <span class="errormsg">!</span> = Fields were empty, <span class="errormsg">&#8224;</span> = Invalid entry.</span></td>
		</tr>
		<tr>
			<?=$deviceType=="phone"?"":"<td>&nbsp;</td>"?>
			<td><input type="submit" class="formbutton" value="Submit" name="warrantysubmit" /></td>
		</tr>
	</tbody>
</table>
			<!-- /NEW -->
		</fieldset>
	</div>
</form>
<?php }?>
<div class='wr_formdisclaimer'>**Data Protection Policy<br />
	By completing this warranty registration I understand that GMK Limited will receive my personal details and details of my purchases from GMK retailers and/or distributors. I also understand that GMK Limited may use the details I have provided to contact me in the future in connection with my purchase(s) and also to send details on competitions, promotions, newsletters and for consumer surveys.</div>
<script type="text/javascript">
//<[CDATA[
	$('#fromshop,#county').selectize({
		create: false
		//sortField: 'text'
	});
//]]>
</script>
