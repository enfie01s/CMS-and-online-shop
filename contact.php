<?php $httppath = "http://www.gmk.co.uk";if(!isset($page)){header("Location: ".$httppath."index.php");}
$contactform_array = array("enquirytype"=>"Type of Enquiry","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email Address","comments"=>"Your Comments"); 
if(isset($_POST['contactsubmit']) && $submitagain == 1)
{
	$_SESSION['submitagain']=date("U")+$formsubmitdelay;
	$missingfield = false;
	$missingstring = "";
	$notifytext = "";
	$pwidth = "275px";
	foreach($_POST as $postfield => $postvalue)
	{
		if(($postfield != "shouldbeempty" && trim($postvalue) == null) || ($postfield == "email" && !eregi($emailereg, $postvalue)))//found some errors
		{
			$missingfield = true;
			$errordetail = ($postfield == "email" && $postvalue != null && !eregi($emailereg, $postvalue)) ? " is invalid.<br /><span style='font-style:italic;color:#cfb5a0'>Correct format: user@host.com</span>" : " is not filled in.";
			$notifytext .= "<li>&quot;".$contactform_array[$postfield]."&quot;".$errordetail."</li>";
		}
		$missingstring .= "&amp;".$postfield;
		$missingstring .= ($postvalue != null) ? "=".$postvalue : "";
	}
	if($missingfield == true || $_POST['shouldbeempty'] != null)
	{
		?><div class='failedsubmit'>The required fields (listed below) have errors.<br /><div class='missinglist' style='width:<?=$pwidth?>'><ul><?=$notifytext?></ul></div>Please click <a href='index.php?p=contact&amp;missingfields<?=$missingstring?>'>HERE</a> to return to the contact form.</div>
	<?php
	}
	else //passed tests
	{	
		//URL Variables
		debug("submitted from okurl? ".$found_url);
		foreach($_POST as $postfield => $postvalue)
		{
			$$postfield = trim($postvalue);//generate variables for the email text
		}
		$comments = trim(htmlspecialchars($_POST['comments']));	
		$sendto = "sales@gmk.co.uk";//sales@
		$subject = "GMK Website: Enquiry";
		$header = "From: ".$firstname." ".$lastname."<" . $email . ">\r\n";
		$header .= "Reply-To: " . $email . "\r\n";
		
		$message = "GMK ENQUIRY : " . strtoupper($enquirytype) . " ENQUIRY" . "\r\n";
		$message .= "Name: " . $firstname . " " . $lastname . "\r\n\r\n";
		$message .= "Enquiry: " . "\r\n";
		$message .= $comments . "\r\n";
		$q=$db1->prepare("INSERT INTO cart_contactus(`name`,`email`,`comments`,`date_created`)VALUES(?,?,?,NOW())");
		$q->execute(array($firstname." ".$lastname,$email,$enquirytype.": ".$comments));
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
else if(isset($_POST['contactsubmit']) && $submitagain == 0)
{
	?><div class='failedsubmit'>You have re submitted this form too soon, please wait another <?=($_SESSION['submitagain'] - date("U"))?> seconds then click your browser's refresh button to try again.</div><?php
}
else
{
	?>
	<div><img src='./content/images/main/title_contact.jpg' alt='Contact Us' /></div>
	<div style="float:left;width:150px;margin-top:20px;font-weight:bold;font-size:1.2em;">Find Us</div>
	<div class='contactpgaddy'>
		<div><span style='font-weight:bold;font-size:1.2em;'>GMK Ltd</span>,<br />Bear House,<br />Concorde Way,<br />Fareham,<br />Hampshire,<br />PO15 5RL<br />
			<br />
			Tel: +44 (0)1489 579 999<br />
			Fax: +44 (0)1489 579 950
		</div>
	</div>
	<div style='float:left;width:300px;margin-top:20px'>
		<img src='./content/images/main/bearhouse.jpg' alt='' />
	</div>
	<div style='clear:both;'></div>
	<br /><br />
	<div style="float:left;width:150px;font-weight:bold;font-size:1.2em;">Email Us</div>
	<div style='width:480px;float:left;'>
	<fieldset><legend>Email Form</legend>
	<form action='index.php?p=contact' method='post' name='contact_form' id='contact_form' onsubmit="return formCheck(this);">
	<input type='hidden' class='hidden' name='shouldbeempty' value='' />
	<input type='hidden' class='hidden' name='required' value='firstname,lastname,email,enquirytype,comments' />
	<table class="emailform">
	<?php 
	foreach($contactform_array as $contactform_field => $contactform_value)
	{
		$gotvalue = (isset($_GET[$contactform_field]) && trim($_GET[$contactform_field]) != null) ? trim($_GET[$contactform_field]) : "";
		$clause = (isset($_GET[$contactform_field]) && trim($_GET[$contactform_field]) == null);
		$emailclause = (isset($_GET['email']) && trim($_GET['email']) != null && $contactform_field == "email" && !eregi($emailereg, $_GET['email']));
		$infostyle = ($clause) ? "style='display:inline;'" : "";//empty field message
		$infostyle1 = ($emailclause) ? "style='display:inline;'" : "";//empty field message
		$style = ($clause || $emailclause) ? "style='border:1px solid red;background:#996767;'" : "";
		?><tr>
		<td style="width:30%" class="formlabel"><span id='<?=$contactform_field?>info' class='fieldinfo' <?=$infostyle?>>! </span><span id='<?=$contactform_field?>info1' class='fieldinfo' <?=$infostyle1?>>&#8224; </span><?=$contactform_value?> * </td>
		<td style="width:70%" class="forminput">
		<?php
		if($contactform_field == "enquirytype")
		{
			?>
			<select name='<?=$contactform_field?>' onblur='javascript:validatefield(this,"","contact_form")' <?=$style?>>
			<option value='' <?php if($gotvalue == ""){?>selected='selected'<?php }?>>Please Select</option>
			<option value="Sales Enquiry" <?php if($gotvalue == "Sales Enquiry"){?>selected='selected'<?php }?>>Sales Enquiry</option>
			<option value="Product Question" <?php if($gotvalue == "Product Question"){?>selected='selected'<?php }?>>Product Question</option>
			<option value="Support" <?php if($gotvalue == "Support"){?>selected='selected'<?php }?>>Support</option>
			<option value="Other Enquiry" <?php if($gotvalue == "Other Enquiry"){?>selected='selected'<?php }?>>Other</option>
			</select>
			<?php
		}
		else if($contactform_field == "comments")
		{
			?>
			<textarea name='<?=$contactform_field?>' id='<?=$contactform_field?>' cols="30" rows="7" style="width:100%;" onblur='javascript:validatefield(this,"","contact_form")' <?=$style?>><?=$gotvalue?></textarea>
			<?php
		}
		else
		{
			?>
			<input type='text' name='<?=$contactform_field?>' id='<?=$contactform_field?>' value='<?=$gotvalue?>' onblur='javascript:validatefield(this,"","contact_form")' size='30' maxlength="55" <?=$style?> />
			<?php
		}
		?>
	</td></tr><?php
	}
	?>
	<tr><td>&nbsp;</td><td><span style="font-size:10px;">* Fields marked with an asterisk are compulsory<br /><span style='color:red;'>!</span> = Fields were empty, <span style='color:red;'>&#8224;</span> = Email address invalid.</span></td></tr>
	<tr><td>&nbsp;</td><td><input type='submit' name='contactsubmit' value='Submit' /></td></tr>
	</table>
	</form>
	</fieldset>
	</div>
	<div style='clear:both;'></div>
	<?php 
}
?>