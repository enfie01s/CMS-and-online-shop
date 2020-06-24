<?php
if(!in_array(basename($_SERVER['PHP_SELF']),array("index.php","transactionRegister.php"))){die("Access Denied");}//direct access security

if(isset($_SESSION['email'])&&isset($_SESSION['pass']))
{
	if(isset($_SESSION['loggedin'])&&$_SESSION['loggedin']!=0&&time()-$_SESSION['loggedin']>$idletime){unset($_SESSION['pass']);unset($_SESSION['email']);}
	
	$pass=cart_hashandsalt($_SESSION['email'],$_SESSION['pass']);
	/*$uq=ysql_query("SELECT * FROM (cart_customers as cu LEFT JOIN cart_counties as cn ON cu.`state`=cn.`county_id`) LEFT JOIN cart_countries as co ON cu.`country`=co.`country_id` WHERE `email`='$_SESSION[email]' AND `gpassword`='$pass'",CARTDB);
	$un=mysql_num_rows($uq);
	if($un>0){$ua=mysql_fetch_assoc($uq);$_SESSION['loggedin']=time();}*/
	$uq=$db1->prepare("SELECT * FROM (cart_customers as cu LEFT JOIN cart_counties as cn ON cu.`state`=cn.`county_id`) LEFT JOIN cart_countries as co ON cu.`country`=co.`country_id` WHERE `email`=? AND `gpassword`=?");
	$uq->execute(array($_SESSION['email'],$pass));
	$un=$uq->rowCount();
	if($un>0){$ua=$uq->fetch(PDO::FETCH_ASSOC);$_SESSION['loggedin']=time();}
	else{$_SESSION['loggedin']=0;}
}//else{echo "no";}
?>