<?php $httppath = "http://www.gmk.co.uk";if(!isset($page)){header("Location: ".$httppath."index.php");}
$contactform_array = array("enquirytype"=>"Type of Enquiry","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email Address","comments"=>"Your Comments"); 
if(isset($_POST['contactsubmit']) && $submitagain == 1)
{
	//require "./cart/sagepay/includes.php";
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
	if($inhouse==0)
	{
	//https://developers.google.com/recaptcha/docs/verify
	$reply=curl_post("https://www.google.com/recaptcha/api/siteverify",array("secret"=>$enquiriesSecret,"response"=>$_POST['g-recaptcha-response']));
	if($reply['success']==false){$missingfield=true;$notifytext.="<li>Captcha response was incorrect.</li>";}
	}
	if($missingfield == true || strlen($notifytext)>0 || $_POST['shouldbeempty'] != null || strlen($_POST['shouldbeempty']) >0)
	{
		?><div class='failedsubmit'>The required fields (listed below) have errors.<br /><div class='missinglist' style='width:<?=$pwidth?>'><ul><?=$notifytext?></ul></div>Please click <a href='./about?missingfields<?=$missingstring?>'>HERE</a> to return to the contact form.</div>
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
		$sendto = $enquirytype=="Warranty"?"warranties@gmk.co.uk":"sales@gmk.co.uk";//sales@
		$subject = "GMK Website: Enquiry";
		$header = "From: ".$firstname." ".$lastname."<" . $email . ">\r\n";
		$header .= "Reply-To: " . $email . "\r\n";
		
		$message = "GMK ENQUIRY : " . strtoupper($enquirytype) . " ENQUIRY" . "\r\n";
		$message .= "Name: " . $firstname . " " . $lastname . "\r\n\r\n";
		$message .= "Enquiry: " . "\r\n";
		$message .= $comments . "\r\n";
		$q=$db1->prepare("INSERT INTO cart_contactus(`name`,`cust_id`,`email`,`comments`,`date_created`)VALUES(?,?,?,?,NOW())");
		$q->execute(array($firstname." ".$lastname,($_SESSION['loggedin']!=0?$ua['cust_id']:0),$email,$enquirytype.": ".$comments));
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
	<div class='contactpgtitle1'><img src='./content/images/main/title_contact.jpg' alt='Contact Us' /></div>
	<div class="contactpgtitle">Find Us</div>
	<div class='contactpgaddy'>
		<div><span>GMK Ltd</span>,<br />Bear House,<br />Concorde Way,<br />Fareham,<br />Hampshire,<br />PO15 5RL<br />
			<br />
			Tel: +44 (0)1489 579 999<br />
			Fax: +44 (0)1489 579 950
		</div>
	</div>
	<div class='contactpgimg'>
		<img src='./content/images/main/bearhouse.jpg' alt='' />
	</div>
	<div style='clear:both;'></div>
	<br /><br />
	<div class="contactpgtitle">Email Us</div>
	<div class="contactpgform">
	<fieldset><legend>Email Form</legend>
	<form action='./about' method='post' name='contact_form' id='contact_form' onsubmit="return formCheck(this);">
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
			<option value="Warranty" <?php if($gotvalue == "Warranty"){?>selected='selected'<?php }?>>Warranty</option>
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
	<?php if($inhouse==0){?>
	<tr><td></td><td><div class="g-recaptcha" data-sitekey="6LeAtRITAAAAAN1GbWjZ43pCgXo8p2dlBQgqjq63"></div></td></tr>
	<?php }?>
	<tr><td>&nbsp;</td><td><span style="font-size:10px;">* Fields marked with an asterisk are compulsory<br /><span style='color:red;'>!</span> = Fields were empty, <span style='color:red;'>&#8224;</span> = Email address invalid.</span></td></tr>
	<tr><td>&nbsp;</td><td><input type='submit' name='contactsubmit' value='Submit' /></td></tr>
	</table>
	
	</form>
	</fieldset>
	</div>
	<div style='clear:both;'></div>

<br /><br />
<a name="about"></a>
<div><img src="./content/images/main/title_about.jpg" alt="About Us" /></div><br />
<div id='aboutcontent'> 


<h1>Company</h1>
<p>GMK Ltd (previously Gunmark until January 1998) is a privately owned enterprise which commenced trading in 1971 and has been continuously owned and managed by the Waktare family. In 2006 Beretta, the major supplier to GMK bought 20% of the company. In March 2013, Beretta increased their share of the business to 60%. The remaining 40% continues to be owned by the Waktare family.<br /><br />
<!--<a href="http://berettaholding.com" target="_blank">--><img src="./content/images/main/berettaholding.png" alt="" /><!--</a>--><br />
GMK Ltd is part of <!--<a href="http://berettaholding.com" target="_blank">-->Beretta Holding<!--</a>-->.</p>
 
<h1>LLC Ltd </h1>

<p>LLC Ltd came into being on 1st April 2002. It has two functions as a business; firstly it is the UK distributor for Lafuma, Le Chameau, Lotus Grill and Resol Group products (check out <a href="http://www.llcliving.co.uk" target="_blank">www.llcliving.co.uk</a> for more info); secondly it provides accountancy, warehousing, personnel and marketing services to GMK Ltd. LLC Ltd is 100% owned by the Waktare family.</p>
 
<h1>The Beretta Gallery, London</h1>

<p>The London Gallery opened in December 2005 in the heart of the West End. It is a showcase store for the Beretta brand. Housed in an elegant building on St James's Street, two floors are dedicated to luxury Beretta clothing and accessories, while the third floor houses the finest gun room in London. The London Gallery is wholly owned by GMK Ltd.</p>

<h1>Finance </h1>
<p>From small beginnings in 1971 the company has enjoyed sustained and continuous growth. Thanks to the strong financial base GMK has prospered throughout domestic recessions and two new acts of prohibitive gun legislation.</p>

<h1>Marketing </h1>
<p>We have an in house marketing department which employs, amongst others, two graphic designers and two full time web designers. We produce our own press releases, brochures, advertisements and websites.</p>

<h1>Team</h1>
<h2>Directors</h2>
<p>GMK has seven directors in total; Franco Beretta, Nicola Perniola, Jorg Prediger, Johan Waktare, Anna Williams, Karl Waktare and Oskar Waktare. The latter two directors are involved in a directly operational capacity.</p>
 
<h2>Sales</h2>
<p>We operate with two separate sales teams covering the UK. This enables us to service the needs of both customers and suppliers to maximum effect. We also have a sales team servicing the needs of the UK Law Enforcement and Military forces. The sales teams are supported by one combined sales office team. </p>

<h2>Technical/After Sales</h2>
<p>We have a team of factory trained gunsmiths and one specialist stocker, again factory trained to the highest standards. We carry a large range of spare parts and are able to carry out both warranty and chargeable repairs in house to our own exacting standards.</p>
</div>
<!-- deined to prevent main.js interfering with captcha -->
<script type="text/javascript">
//<[CDATA[
emailereg = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$/;
formsubmit = 0;//set to 1 in form check function.
function trim(strText)
{ 
    // this will get rid of leading spaces 
    while (strText.substring(0,1) == ' ') 
        strText = strText.substring(1, strText.length);
    // this will get rid of trailing spaces 
    while (strText.substring(strText.length-1,strText.length) == ' ')
        strText = strText.substring(0, strText.length-1);
   return strText;
}
function in_array( what, where )
{
	var a=false;
	var theLength = where.length;
	for(var i=0;i<theLength;i++)
	{
		if(what == where[i])
		{
			a=true;
			break;
		}
	}
	return a;
} 
function formCheck(formObj)
{
	formsubmit = 1;//set var to say form was submitted.
	reqd = document.getElementById(formObj.name).required.value.split(',');
	reqlength = reqd.length;
	for(x=0;x < reqlength;x++)
	{
		if(reqd[x] != "purchasedate")
			validatefield(formObj.elements[reqd[x]],"",formObj.name);
	}
	formsubmit = 0;//reset var
	if(aformsubmit == 1)
		return true;
	else
		return false;
}
function validatefield(infield,serialcheck,formname)
{
	aformsubmit = 1;//to tell form to finish submitting (value = 0 to stop the submit)
	reqd = document.getElementById(formname).required.value.split(',');
	theField = infield.value;
	theInfo = (infield.name == "email" && !emailereg.test(theField) && theField != "" && theField != null) ? infield.name+"info1" : infield.name+"info";
	clause = (((theField == null || trim(theField) == "") && in_array(infield.name,reqd)) || (infield.name == "email" && !emailereg.test(theField)));
	if(clause == true || (infield.name == "serial" && serialcheck == theField)) 
	{
		infield.style.border = "1px solid red";
	  infield.style.background = "#996767";
	}
	else
	{
		infield.style.border="1px solid #aaaaaa";
		infield.style.backgroundColor="#FFFFFF";
	}
	document.getElementById(theInfo).style.display = (clause == true) ? "inline" : "";
	if(infield.name == "serial" && serialcheck != theField)
		document.getElementById("serialinfo1").style.display = "none";
	//for email validation, display or hide the other field depending if it's empty or invalid email.
	if(infield.name == "email" && (theField == "" || theField == null))
		document.getElementById("emailinfo1").style.display = "none";
	else if(infield.name == "email" && (theField != "" || theField != null))
		document.getElementById("emailinfo").style.display = "none";
	if(infield.name == "email" && emailereg.test(theField))//hide the invalid email text if we have the correct format
		document.getElementById("emailinfo1").style.display = "none";
	aformsubmit = (clause && formsubmit == 1) ? 0 : 1;
}
//]]>
</script>
<?php }?>