<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="keywords" content="gmk, beretta, benelli, lanber, arrieta, sako, tikka T3, atk, rcbs, cci, speer, federal, burris, leupold, beretta premium, franchi, club accuracy, shotgun, rifle, ammunition" />
<meta name="description" content="GMK are the UK's leading shooting sports distributor." />
<meta name="copyright" content="GMK UK" />
<meta name="revisit-after" content="14 days" />
<meta name="distribution" content="global" /> 
<meta name="robots" content="all" />
<meta name="rating" content="general" />
<!---gmk, beretta, benelli, lanber, arrieta, sako, tikka T3, atk, rcbs, cci, speer, federal, burris, leupold, beretta premium, franchi, club accuracy, shotgun, rifle, ammunition-->
<link href="content/stylesheets/global.css" rel="stylesheet" type="text/css" />
<link href="content/stylesheets/style1.css" rel="stylesheet" type="text/css" />
<link href="content/stylesheets/menu.css" rel="stylesheet" type="text/css" />
<title>GMK : Brochure Request</title>
</head>
<body style="font-family:Arial, Helvetica, sans-serif;">
<?php 
$database = 1;
include('../../global/includes.php');

$br = $_GET['br'];

$query = "SELECT * FROM gmkbrochures ORDER BY `Brochure` ASC";
$result = mysql_query($query,$con1) or die(sql_error("Error"));
?>
<script language="JavaScript">
<!--
function formCheck(formobj)
	{
	var fieldRequired = Array("brochure", "firstname", "lastname", "email", "address1", "city", "County", "postcode");
	var fieldDescription = Array("Required Brochure", "First Name", "Last Name", "Email Address", "Address", "Town / City", "County", "Postcode");
	// dialog message
	var alertMsg = "Please complete the following fields:\n";
	
	var l_Msg = alertMsg.length;
	
	for (var i = 0; i < fieldRequired.length; i++)
		{
		var obj = formobj.elements[fieldRequired[i]];
		if (obj)
			{
			switch(obj.type)
				{
				case "select-one":
				if (obj.selectedIndex == -1 || obj.options[obj.selectedIndex].text == "")
					{
					alertMsg += " - " + fieldDescription[i] + "\n";
					}
				break;
				case "select-multiple":
				if (obj.selectedIndex == -1)
					{
					alertMsg += " - " + fieldDescription[i] + "\n";
					}
				break;
				case "text":
				case "textarea":
				case "password":
				if (obj.value == "" || obj.value == null)
					{
					alertMsg += " - " + fieldDescription[i] + "\n";
					}
				break;
				default:
				}
			if (obj.type == undefined)
				{
				var blnchecked = false;
				for (var j = 0; j < obj.length; j++)
					{
					if (obj[j].checked)
						{
						blnchecked = true;
						}
					}
				if (!blnchecked)
					{
					alertMsg += " - " + fieldDescription[i] + "\n";
					}
				}
			}
		}
	var opt=document.formcheck.brochure;
	if(opt.value=='')
		{
		alertMsg +=' - ' + 'Required Brochure' +'\n';
		}
	var optc=document.formcheck.County;
	if(optc.value=='')
		{
		alertMsg +=' - ' + 'County' +'\n';
		}
	if (alertMsg.length == l_Msg)
		{
		return true;
		}
	else
		{
		alert(alertMsg);
		return false;
		}
	}
// -->
var emailfilter=/^\w+[\+\.\w-]*@([\w-]+\.)*\w+[\w-]*\.([a-z]{2,4}|\d+)$/i

function checkmail(e){
var returnval=emailfilter.test(e.value)
if (returnval==false){
alert("Please enter a valid email address.")
e.select()
}
return returnval
}
</script>



<table width="100%">
	<tr>
    	<td valign="top">
        <strong>Request a brochure by post...</strong><br /><br />
       
        <br /><br /><br />
<form action="../submit.php" method="post" name="formcheck" onsubmit="return formCheck(this);">                                                             
	<input type="hidden" name="type" value="brochure" />
    <input type="hidden" name="alt" value="yes" />
    <input type="hidden" name="brochure" value="<?php print $br; ?>" />
	<table align="center" width="400">
		<tr>
			<td align="right"><span class="greytext">Brochure Required:</span> </td>
			<td><strong><?php print ucwords($br); ?></strong></td>
		</tr>
		<tr>
			<td align="right">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td align="right"><span class="greytext">First Name:</span></td>
			<td><input type="text" size="21"  name="firstname" maxlength="55" /><span class="required"> *</span></td>
		</tr>
		<tr>
			<td align="right"><span class="greytext">Last Name:</span></td>
			<td><input type="text" size="21"  name="lastname" maxlength="55" /><span class="required"> *</span></td>
		</tr>
		<tr>
			<td align="right"><span class="greytext">Email Address:</span></td>
			<td><input type="text" name="email" size="30" /><span class="required"> *</span></td>
		</tr>
		<tr>
			<td align="right"><span class="greytext">Your Address:</span></td>
			<td><input type="text" name="address1" size="35" /><span class="required"> *</span></td>
		</tr>
		<tr>
			<td align="right"></td>
			<td><input type="text" name="address2" size="35" /></td>
		</tr>
		<tr>
			<td align="right"><span class="greytext">City / Town:</span></td>
			<td><input type="text" name="city" size="30" /><span class="required"> *</span></td>
		</tr>
		<tr>
			<td align="right"><span class="greytext">County:</span></td>
			<td>
			<?php include("../content/scripts/counties.php"); ?>	
			<span class="required"> *</span>
			</td>
		</tr>
		<tr>
			<td align="right"><span class="greytext">Postcode:</span></td>
			<td><input type="text" name="postcode" size="7" /><span class="required"> *</span></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><span style="font-size:10px;">* Fields marked with an asterisk are compulsory</span></td>
		</tr>
		<tr>
			<td>&nbsp;<br /><br /><br /><br /><br /></td>
			<td><input type="submit" value=" &nbsp; &nbsp; Submit &nbsp; &nbsp; " onClick="return checkmail(this.form.email)" /></td>
		</tr>
</table>
</form>
		</td>
        <td align="right"><img src="../content/images/brochures.jpg" /></td>
    </tr>
</table>
</body>
</html>