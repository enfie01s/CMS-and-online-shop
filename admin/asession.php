<?php
if(basename($_SERVER['PHP_SELF'])=="asession.php"){die("Access Denied");}//direct access security
if(isset($_COOKIE['adminuser'])&&isset($_COOKIE['adminpass']))
{
	//if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&time()-$_SESSION['aloggedin']>$adminidletime){unset($_SESSION['adminpass']);unset($_SESSION['adminuser']);$_SESSION['timedout']=1;}
	$pass=hashandsalt($_COOKIE['adminuser'],$_COOKIE['adminpass']);
	/*$pass=hashandsalt($_SESSION['adminuser'],mysql_real_escape_string($_SESSION['adminpass']));
	$uqa=ysql_query("SELECT * FROM admin WHERE `username`='".mysql_real_escape_string($_SESSION['adminuser'])."' AND `apassword`='*$pass'",$con1);
	$una=mysql_num_rows($uqa);*/
	//$pass=hashandsalt($_SESSION['adminuser'],$_SESSION['adminpass']);
	$uqa=$db1->prepare("SELECT * FROM admin WHERE `username`=? AND `apassword`=?");
	$uqa->execute(array($_COOKIE['adminuser'],'*'.$pass));
	$una=$uqa->rowCount();
	if($una>0){
		/*$uaa=mysql_fetch_assoc($uqa);
		ysql_query("UPDATE admin SET date_lastin='".date("U")."' WHERE aid='".$uaa['aid']."'",$con1);*/
		$uaa=$uqa->fetch();
		$q=$db1->prepare("UPDATE admin SET date_lastin='".date("U")."' WHERE aid=?");
		$q->execute(array($uaa['aid']));
		$_SESSION['aloggedin']=time();
		$_SESSION['timedout']=0;
		unset($_SESSION['query']);
	}
	else{$_SESSION['aloggedin']=0;}
	$showloginform=0;
}
if((!isset($_SESSION['aloggedin'])||$_SESSION['aloggedin']==0)&&(basename(dirname($_SERVER['PHP_SELF']))=="admin"||basename(dirname($_SERVER['PHP_SELF']))=="admin1")){
	unset($_SESSION['error']);
	$_SESSION['query'] = !isset($_GET['logout'])?$_SERVER['QUERY_STRING']:"";//hold requested url to redirct after login
	$showloginform=1;
}
?>