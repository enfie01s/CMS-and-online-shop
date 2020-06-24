<?php 
if($post_arr['identifier']=="lostpass"&&!isset($_SESSION['error']))
{
	/*$emailcheck_q=ysql_query("SELECT `email`, `apassword`, `username`, `date_created` FROM admin WHERE `email`='$post_arr[email]'",$con1)or die(sql_error("Error"));
	$emailcheck=mysql_num_rows($emailcheck_q);list($demail,$dpass,$dname,$dsign)=mysql_fetch_row($emailcheck_q);*/
	$emailcheck_q=$db1->prepare("SELECT `email`, `apassword`, `username`, `date_created` FROM admin WHERE `email`=?");
	$emailcheck_q->execute(array($post_arr['email']));
	$emailcheck=$emailcheck_q->rowCount();
	list($demail,$dpass,$dname,$dsign)=$emailcheck_q->fetch();
	if($emailcheck==0){$_SESSION['error']="Sorry, no admin account was found with that email address.";}
}
//error checks for password reset
if($post_arr['identifier']=="dopassreset")
{
	/*$emailcheck_q=ysql_query("SELECT `email`, `apassword`, `username`, `date_created`,`aid` FROM admin WHERE `retrcode`='$post_arr[code]'",$con1)or die(sql_error("Error"));
	$emailcheck=mysql_num_rows($emailcheck_q);list($demail,$dpass,$dname,$dsign,$admin_id)=mysql_fetch_row($emailcheck_q);*/
	$emailcheck_q=$db1->prepare("SELECT `email`, `apassword`, `username`, `date_created`,`aid` FROM admin WHERE `retrcode`=?");
	$emailcheck_q->execute(array($post_arr['code']));
	$emailcheck=$emailcheck_q->rowCount();list($demail,$dpass,$dname,$dsign,$admin_id)=$emailcheck_q->fetch();
	if($emailcheck==0){$_SESSION['error']="Invalid security code.";if(isset($get_arr['resetpassform'])){$get_arr['resetpassform']="";}}
	if($post_arr['password1']!=$post_arr['password2']&&!isset($_SESSION['error'])){$_SESSION['error']="Passwords not matching, please try again.";}
}

if((isset($get_arr['resetpassform'])&&$get_arr['resetpassform']!="")||$post_arr['identifier']=="dopassreset")
{
	$thecode=$post_arr['identifier']=="dopassreset"?$post_arr['code']:$get_arr['resetpassform'];
	$codebits=explode("_",$thecode);
	if(($codebits[1]<=$date||strlen($codebits[1])<1)&&!isset($_SESSION['error']))
	{
		$_SESSION['error']="Sorry, this code has now expired.";
		sql_query("UPDATE admin SET `retrcode`='' WHERE `retrcode`=?",$db1,array($thecode));
		if(isset($get_arr['resetpassform'])){$get_arr['resetpassform']="";}
	}
}
//no errors
if(!isset($_SESSION['error']) && isset($post_arr['identifier']))
{
	$eheaders = "From: GMK UK <sales@gmk.co.uk>\r\n";
	$eheaders .= "Reply-To: sales@gmk.co.uk\r\n";
	$eheaders .= "Return-Path: sales@gmk.co.uk\r\n";
	$eheaders .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
	switch($post_arr['identifier'])
	{
		case "dopassreset":
			//$_SESSION['adminpass']=hashandsalt($dname,$post_arr['password1']);
			$coopath=$_SERVER['HTTP_HOST']=="bhweb1"?'/gmk':'/';
			setcookie('adminpass',hashandsalt($dname,$post_arr['password1']),time()+$adminidletime,$coopath);
			$pass2=hashandsalt($dname,$_COOKIE['adminpass']);
			sql_query("UPDATE admin SET `apassword`=?, `retrcode`='' WHERE `aid`=?",$db1,array('*'.$pass2,$admin_id));
			
			//set confirm email details
			$msg="========================================<br />
			You password change at GMK UK<br />
			========================================<br />
			Hi ".$dname.",<br />
			<br />
			You have changed your admin account password on the $sitename website, please see your new login details below.<br />
			<br />
			<strong>Username:</strong>&#160;".$dname."<br />
			<strong>Password:</strong>&#160;".$post_arr['password1']."<br />";
			$to=$post_arr['email'];
			$subject="Password changed at GMK UK";
			$get_arr['resetpassform']="";
			break;
		case "lostpass":
			$thehash=md5($dname.$demail.$dpass.$dsign);
			$timedelay=$date+$passresetmins;
			/* 
			could do 
			$thehash=".$thehash.$timedelay.(strlen($timedelay)<10?0:strlen($timedelay))" - add 0 on stamp length
			$timelen=substr($hash,-2,2); - last 2 digits are stamp length
			$timestamp=substr($hash,((0-$timelen)-2),$timelen)
			*/
			sql_query("UPDATE admin SET `retrcode`=? WHERE `email`=?",$db1,array($thehash."_".$timedelay,$demail));
			$msg="===========================================<br />
			Request to reset your password on the GMK UK website<br />
			===========================================<br />
			Hi ".$dname.",<br />
			<br />
			A request was made to reset your password. If you did not make this request or have since remembered your password, please ignore this email.<br /><br />
			Click <a href='$mainbase/admin/index.php?p=login&amp;resetpassform=".$thehash."_".$timedelay."'>HERE</a> to reset your password.<br />This link will expire in $passreset_minutes minutes.<br />";
			$to=$demail;
			$subject="Resquest to reset your password on $sitename";
			break;
	}
	$msg .= "<br />
	Kind Regards<br />
	<a href='$webby'>$sitename</a><br />
	01489 557 600<br />
	<a href='mailto:sales@gmk.co.uk'>sales@gmk.co.uk</a>";
	if($inhouse){$_SESSION["allgood"]=$msg;}
	else if(mail($to,$subject,$msg,$eheaders)){$_SESSION['allgood']="Please check your inbox for further instructions";}else{$_SESSION['error']="There was an error sending the password retrieval details";}
	//if($post_arr['identifier']=="dopassreset"){$_SESSION["allgood"]="Password reset successful, you may now log in with your new details";header("Location: $mainbase/admin/login.php?unset=1");}
}
?>
<div id="right" style="float:none !important;width:600px;margin:0 auto;padding:20px;">
	<?php if(isset($_SESSION['timedout'])&&$_SESSION['timedout']==1){$_SESSION['error']="Your session timed out due to $aidle_minutes minutes of inactivity";}
		if(isset($_SESSION['error'])){echo "<div class='notice'>".$_SESSION['error']."</div>";unset($_SESSION['error']);}else if(isset($_SESSION["allgood"])){echo "<div class='notice'>".$_SESSION["allgood"]."</div>";}if(isset($get_arr['unset'])||$_SESSION["allgood"]==$msg){unset($_SESSION["allgood"]);}?>
			<?php 
			/* ----- PASSWORD RESET FORM ----- */
if(isset($get_arr['resetpassform'])&&$get_arr['resetpassform']!=""&&!isset($_SESSION['allgood'])){

?>
		<form action="<?=$mainbase?>/admin/index.php?p=login&amp;resetpassform=<?=$get_arr['resetpassform']?>" method="post">
			<input type="hidden" name="identifier" value="dopassreset" />
			<input type="hidden" name="code" value="<?=$get_arr['resetpassform']?>" />
			<dfn>All fields marked (*) are required</dfn>
			<table style="width:100% !important">
				<tr class="head">
						<td colspan="2"><div class="titles">Password Reset</div></td>
					</tr>
				<tr>
					<td class="left_light"><label for="passlogin">New Password <span>*</span></label></td>
					<td class="right_light"><input type="password" name="password1" id="passlogin" class="input_text" />
					</td>
				</tr>
				<tr>
					<td class="left_dark"><label for="passlogin">Confirm Password <span>*</span></label></td>
					<td class="right_dark"><input type="password" name="password2" id="passlogin" class="input_text" />
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" value="Modify Password" /></p>
		</form>
		<?php }else if(!isset($_POST['email'])&&!isset($get_arr['resetpassform'])){?>			
			<form action="index.php?p=login" method="post">
				<input type="hidden" name="identifier" value="lostpass" />				
				<table width="100%">
					<tr class="head">
						<td colspan="2"><div class="titles">Lost your password?</div></td>
					</tr>
					<tr>
						<td class="left_light"><label for="emaillost">Email Address</label></td>
						<td class="right_light"><input type="text" name="email" id="emaillost" onfocus="this.select()" class="input_text" /></td>
					</tr>
				</table>
				<p class="submit"><input type="submit" value="Request Password Reset" /></p>
				
			</form>
		<?php }?>
</div>
