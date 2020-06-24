<?php 
session_name("gmk");
session_start();

header('Content-Type: text/html; charset=utf-8');
if(isset($_GET['logout'])){$coopath=$_SERVER['HTTP_HOST']=="bhweb1"?'/gmk':'/';
	setcookie('adminpass','',time()-200,$coopath);setcookie('adminuser','',time()-200,$coopath);$_SESSION['aloggedin']=0;}
include "../../ipcheck.php";
include "../../includes.php";
include "functions.php";
include "vars.php";
require "asession.php";
/* cart items */
$cart_path="../cart";//relative path to cart
$cart_adminpath=$cart_path."/admin";
set_include_path($cart_adminpath);  
include $cart_path."/cart_functions.php";
/* cart items */
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta charset="utf-8" /> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" /> 
<meta name="apple-mobile-web-app-capable" content="yes" />
<link href="acp.css" rel="stylesheet" type="text/css" />
<title>GMK ACP<?php if(strlen($page)>0){echo ": ".ucwords($page); }?></title>
<script type="text/javascript" src="../content/js/jquery.js"></script>
<script type="text/javascript" src="../content/js/jscolor/jscolor.js"></script>
</head>
<body><a name="top"></a>

<div id="header">
	<div style="float:left;"><img src="img/logo.jpg" alt="GMK Admin" style="margin:10px;" /></div>
	<div id="headerbox">
		
			<?php if($showloginform==0){?>
			<form action="index.php" method="get" style="margin:3px auto 10px !important;">
			<input type="hidden" class="hidden" name="p" value="search" />
			<input type="text" name="simplesearch" value="<?=isset($_GET['simplesearch'])?$_GET['simplesearch']:""?>" style="width:200px;" />&#160;&#160;<input type="submit" value="Search Products" class="formsubmit" />
			</form>
			<?php }else{?>
			<form action="auth.php" method="post" style="margin:3px auto 10px !important;">
			<input type="hidden" name="identifier" value="login" />
			Username: <input type="text" name="adminuser" id="adminuser" />
			Password: <input type="password" name="adminpass" id="adminpass" />
			<input type="submit" value="Log In" class="formsubmit" />
			</form>
			<?php }?>
			<span>
			<?php if($showloginform==1){?>
				<?php if(isset($_GET['loginerr'])&&$_GET['loginerr']==1){?>
				You did not fill in all information.
				<?php }else if(isset($_GET['loginerr'])&&$_GET['loginerr']==2){?>
				Username or password incorrect, please try again or <a href="?p=login">reset your password</a>
				<?php }else{?>
				Please log in using the form above.
				<?php }?>
			<?php }else{?>You are logged in as <?=ucwords($uaa['username'])?> (<a href="index.php?logout=1">Log Out</a>).<?php }?></span>
		
	</div>
	<div class="clear"></div>
</div>

<div id="sitecontent">
<?php
if($showloginform==1&&$page=="login"){
	include $page.".php";
}
else if($showloginform==0){?>
	<div id="left">
		<ul>
			<li class="heading"><a href="index.php">Home</a></li>			
		</ul>
		<?php cart_adminmenu(); 
		if($page!="home"&&$page!="search")
		{
			?>
			<script type="text/javascript">
			page="menu<?=$page?>";
			document.getElementById(page).className="activemenu";
			</script>
			<?php
		}
		?>
	</div>
	<div id="right">
	<?php				
		if(strlen($page)>0&&$page!="home"&&$page!="login"){
			$key = array_search($page, $modules_pages); 
			if(!in_array($key,$mods)&&in_array($page,$modules_pages)&&$uaa['super']!=1){
				?><div class="notice">&nbsp;&nbsp;Sorry you are not authorized to view this module</div><?php 
			}else{include $page.".php";}
		}
		else
		{
			cart_adminindex();
			/*$topten=ysql_query("SELECT `prod_title`,`views`,`pid` FROM `gmk_products` WHERE `views` > '0' ORDER BY `views` DESC LIMIT 0,10",$con1);
			$toptencount=mysql_num_rows($topten);*/
			$topten=$db1->query("SELECT `prod_title`,`views`,`pid` FROM `gmk_products` WHERE `views` > '0' ORDER BY `views` DESC LIMIT 0,10");
			$toptencount=$topten->rowCount();
			?>		
			<table style="float:left;width:48%;">
				<tr class="head"><td colspan="2"><div class="titles">Top <?=$toptencount?> Most Viewed Products</div></td></tr>
				<tr class="subhead"><td>Views</td><td>Product</td></tr>
				<?php
				//while($toptenprod=mysql_fetch_assoc($topten))
				while($toptenprod=$topten->fetch())
				{
					$row=!isset($row)||$row=="_dark"?"_light":"_dark";
					?><tr class="row<?=$row?>"><td style="width:5%;text-align:center"><?=$toptenprod['views']?></td><td style="width:95%"><?=$toptenprod['prod_title']?></td></tr><?php
				}
				?>
			</table>
			<div class="clear"></div>
			<?php
		}
		?>
		</div>
		<div class="clear"></div>
<?php }?>
</div>
<div id="copy">Admin CP &#169; <?=date("Y")>2014?"2014-":""?><?=date("Y")?> GMK & LLC</div>
</body>
</html>