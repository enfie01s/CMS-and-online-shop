<?php $httppath = "http://www.gmk.co.uk";if(!isset($page)){header("Location: ".$httppath."index.php");}
debug($_SESSION['views']);
$brochureform_array = array("brochure"=>"Brochure Required","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email Address","address1"=>"Your Address","address2"=>"","city"=>"City/Town","county"=>"County","postcode"=>"Postcode","interest"=>"Area of Interest");
$brochurewidth_array = array("Accessories"=>"62","Beretta"=>"62","Benelli"=>"85","Accuracy Insight"=>"62","Sako"=>"80","Tikka"=>"65","UGB25 Xcel"=>"80","Burris"=>"80");
include("content/brochures/config.php"); 

if(isset($_POST['brochuresubmit']) && $submitagain == 1)//shouldbeempty is for form bots
{
	$_SESSION['submitagain']=date("U")+$formsubmitdelay;
	$missingfield = false;
	$missingstring = "";
	$notifytext = "";
	$pwidth = "245px";
	foreach($_POST as $postfield => $postvalue)
	{
		if(is_array($postvalue))
		{
			$postvalue = trim(implode(", ",$postvalue));
		}
		if(($postfield != "address2" && $postfield != "shouldbeempty" && ($postvalue == null || trim($postvalue) == null)) || ($postfield == "email" && !eregi($emailereg, $postvalue)))//found some errors
		{
			$missingfield = true;
			$errordetail = ($postfield == "email" && $postvalue != null && !eregi($emailereg, $postvalue)) ? " is invalid.<br /><span style='font-style:italic;color:#687694'>Correct format: user@host.com</span>" : " is not filled in.";
			$notifytext .= "<li>&quot;".$brochureform_array[$postfield]."&quot;".$errordetail."</li>";
		}
		$missingstring .= "&amp;".$postfield;
		$missingstring .= ($postvalue != null) ? "=".$postvalue : "";
	}
	if($missingfield == true || $_POST['shouldbeempty'] != null)
	{
		?><div class='failedsubmit'>The required fields (listed below) have errors.<br /><div class='missinglist' style='width:<?=$pwidth?>'><ul><?=$notifytext?></ul></div>Please click <a href='./brochureform&amp;missingfields<?=$missingstring?>'>HERE</a> to return to the brochure request form.</div>
	<?php
	}
	else //passed tests
	{	
		//URL Variables
		$setokurl = "1";
		$okurls = "109.123.78.12";
		if ($setokurl == "1") // Security Check for URL
		{
			$referer = $_SERVER["SERVER_ADDR"];
			$found_url = ($referer == $okurls) ? "y" : "n";
		}	
		if($found_url == "y" || $dev == 1)
		{
			foreach($_POST as $postfield => $postvalue)
			{
				if(is_array($postvalue))
				{
					$$postfield = trim(implode(", ",$postvalue));
				}
				else{
					$$postfield = trim($postvalue);//generate variables for the email text
				}
			}
			
			$sendto = "marketing@gmk.co.uk";/*"brochures@gmk.co.uk";*/
			$subject = "GMK Website: Brochure Request\r\n";
			$header = "From: ".$firstname." ".$lastname."<" . $email . ">\r\n";
			$header .= "Reply-To: " . $email . "\r\n";
			
			$message = "GMK BROCHURE REQUEST" . "\r\n_____________________________\r\n\r\n";
			$message .= "Brochure Required: " . $brochure . "\r\n\r\n";
			
			$message .= "   Name: " . $firstname . " " . $lastname . "\r\n";
			$message .= "Address: " .$address1 . "\r\n         ";
			if (strlen($address2) > 0) { $message .= $address2 . "\r\n         "; }
			$message .= $city . "\r\n         ";
			$message .= $county . "\r\n         ";
			$message .= $postcode . "\r\n";
			$message .= "Interest: " . $interest . "\r\n";
			
			if(mail($sendto,$subject,$message,$header))
			{
				?><div class='success'>Your Request has been received, thank you.</div><?php
			}
			else
			{
				?><div class='failedsubmit'>Sorry, there was an error while trying to send your request. Please call 01489 579 999 for assistance.</div><?php
			}
		}
	}
}
else if(isset($_POST['brochuresubmit']) && $submitagain == 0)
{
	?><div class='failedsubmit'>You have re submitted this form too soon, please wait another <?=($_SESSION['submitagain'] - date("U"))?> seconds then click your browser's refresh button to try again.</div><?php
}
else //nothing submitted
{
	?>
	
	<div><img src='./content/images/main/title_brochures.jpg' alt='Request Brochures' /></div>
	<div>Please fill in this form to request a brochure by post.</div><br />
	<div style='width:420px;margin:20px auto 0px;'>
	<fieldset><legend>Brochure Request Form</legend>
	<form action='./brochureform' method='post' name='brochure_form' id='brochure_form' onsubmit="return formCheck(this);">
	<input type='hidden' class='hidden' name='shouldbeempty' />
	<input type='hidden' class='hidden' name='required' value='brochure,firstname,lastname,address1,city,county,postcode,email' />
	<table class="emailform" cellspacing="0" cellpadding="2">
	<?php
	foreach($brochureform_array as $br_formfield => $br_formtitle)
	{
		$gotvalue = (isset($_GET[$br_formfield]) && trim($_GET[$br_formfield]) != null) ? trim($_GET[$br_formfield]) : "";
		$clause = (isset($_GET[$br_formfield]) && $br_formfield != "address2" && trim($_GET[$br_formfield]) == null);
		$emailclause = (isset($_GET['email']) && trim($_GET['email']) != null && $br_formfield == "email" && !eregi($emailereg, $_GET['email']));
		$infostyle = ($clause) ? "style='display:inline;'" : "";//empty field message
		$infostyle1 = ($emailclause) ? "style='display:inline'" : "";//invalid email addy message
		$style = ($clause || $emailclause) ? "style='border:1px solid red;background:#996767;'" : "";
		$class = (!isset($class) || $class == "forminput1") ? "forminput0" : "forminput1";
		?>
		<tr>
		<td width="40%" class="formlabel"><span id='<?=$br_formfield?>info' class='fieldinfo' <?=$infostyle?>>! </span><span id='<?=$br_formfield?>info1' class='fieldinfo' <?=$infostyle1?>>&#8224; </span><?=$br_formtitle?><?php if($br_formfield != "address2"){?> * <?php }?></td>
		<td width="60%" class="forminput">
			<?php 
			if($br_formfield == "brochure" || $br_formfield == "county")
			{
				?>
				<select name='<?=$br_formfield?>' onblur='javascript:validatefield(this,"","brochure_form")' <?=$style?>><option value="" selected="selected">Please Select</option>
				<?php if($br_formfield == "brochure")
				{
					$thebrochures=$brochures;
					asort($thebrochures);
					foreach($postablebrochures as $rawname => $brochure)
					{ 
						?>
						<option value="<?=$brochure?>" <?php if($gotvalue == $brochure){ ?>selected="selected"<?php }?>><?=$brochure?></option>
						<?php
					} 
				}
				else
				{
					countiesoptions("SELECT * FROM counties ORDER BY `Country` DESC, `County` ASC",$con1,$gotvalue);
				}
				?>
				</select>
				<?php
			}
			else if($br_formfield == "interest")
			{
				?><div <?=$style?>><input type="hidden" name="<?=$br_formfield?>" value="" />
				<input type="checkbox" name="<?=$br_formfield?>[]" value="Rifle Shooting" id="rifle" /><label for="rifle"> Rifle Shooting</label>
				<input type="checkbox" name="<?=$br_formfield?>[]" value="Game Shooting" id="game" /><label for="game"> Game Shooting</label>
				<input type="checkbox" name="<?=$br_formfield?>[]" value="Competition/Clay Shooting" id="clay" /><label for="clay"> Competition/Clay Shooting</label>
				</div><?php
			}
			else
			{
				?>
				<input type='text' name='<?=$br_formfield?>' value='<?=$gotvalue?>' size='30' maxlength='<?php if($br_formfield == "postcode"){?>8<?php }else{?>25<?php } ?>' onblur='javascript:validatefield(this,"","brochure_form")' <?=$style?> />
				<?php
			}
			?>
		</td>
	</tr>
		<?php
	}
	?><tr><td>&nbsp;</td><td><span style="font-size:10px;">* Fields marked with an asterisk are compulsory<br /><span style='color:red;'>!</span> = Fields were empty, <span style='color:red;'>&#8224;</span> = Email address invalid.</span></td></tr>
	<tr><td>&nbsp;</td><td><input type='submit' name='brochuresubmit' value='Submit' class='formbutton' /></td></tr>
	</table>
	</form>
	</fieldset></div>
	<div style="margin-top:50px;font-size:1.2em;">Click <a href="./viewbrochures">here</a> to view the brochures which are available to view online.</div>
<?php 
}
?>