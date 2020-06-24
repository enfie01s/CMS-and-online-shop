<?php $httppath = "http://www.gmk.co.uk";if(!isset($page)){header("Location: ".$httppath."index.php");}
if(isset($_POST['joinmailing']) && $submitagain == 1)
{
	$_SESSION['submitagain']=date("U")+$formsubmitdelay;
	$missingfield = false;
	$missingstring = "";
	$notifytext = "";
	$pwidth = "256px";
	$postvalue = $_POST['mailingemail'];
	$postfield = "Email";
	if($postvalue == null || !eregi($emailereg, $postvalue))//found some errors
	{
		$missingfield = true;
		$errordetail = ($postvalue != null && !eregi($emailereg, $postvalue)) ? " is invalid.<br /><span style='font-style:italic;color:#cfb5a0'>Correct format: user@host.com</span>" : " is not filled in.";
		$notifytext .= "<li>&quot;Email&quot;".$errordetail."</li>";
		$missingstring .= "&amp;".$postfield;
		$missingstring .= ($postvalue != null) ? "=".$postvalue : "";
	}
	
	if($missingfield == true || $_POST['shouldbeempty'] != null)
	{
		?><div class='failedsubmit'>There was an error submitting the mailing form.<br /><div class='missinglist' style='width:<?=$pwidth?>'><ul><?=$notifytext?></ul></div>Please use the form below to try again.</div>
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
			debug("submitted from okurl? ".$found_url);
			foreach($_POST as $postfield => $postvalue)
			{
				$$postfield = trim($postvalue);//generate variables for the email text
			}
			$comments = trim(htmlspecialchars($_POST['comments']));	
			$sendto = "eparkin@gmk.co.uk";//emmacc@
			$subject = "GMK Website: Request to join mailing list";
			$header = "From: " . $mailingemail. "\r\n";
			$header .= "Reply-To: " . $mailingemail . "\r\n";
			
			$message = "GMK MAILING LIST REQUEST.\r\n" . 
			"_____________________________________________\r\n\r\n" .  
			"Email address: " . $mailingemail . "\r\n\r\n" .
			"Please add the above email address to the News & Events mailing list.\r\n";
			
			if(mail($sendto,$subject,$message,$header))
			{
				?><div class='success'>Your Request has been received, thank you.</div><?php
			}
			else
			{
				?><div class='failedsubmit'>Sorry, there was an error while trying to send your request. Please call 01489 579 999 for assistance.</div><?php
			}
		}
		else
		{
			?>Sorry, this form is for internal use only.<?php //not submitted from gmk!
		}
	}
}
else if(isset($_POST['joinmailing']) && $submitagain == 0)
{
	?><div class='failedsubmit'>You have re submitted this form too soon, please wait another <?=($_SESSION['submitagain'] - date("U"))?> seconds then click your browser's refresh button to try again.</div><?php
}
else
{
	?>Sorry, this form is for internal use only.<?php //this page is only to handle posted data for joining mailing list.
}
?>