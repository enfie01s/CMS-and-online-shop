<?php 
session_name("gmk");
session_start();
ini_set('display_errors',1); 
error_reporting(E_ALL);
if(strtolower(substr($_SERVER['REQUEST_METHOD'],-4,4))!="post"){die("Access Denied");}//Direct access security
if(isset($_POST['identifier'])&&$_POST['identifier']=="login")
{
	include "../../config.inc.php";
	include "../../ipcheck.php";
	include "vars.php";
	if(strlen($_POST['adminpass'])<1||strlen($_POST['adminuser'])<1)//a missing field
	{
		$_SESSION['error']="You did not fill in all information.";
		header("Location: index.php".(isset($_SESSION['query'])&&!stristr($_SESSION['query'],"login")?"?".$_SESSION['query']."&loginerr=1":"&loginerr=1"));
	}
	else//fields not empty
	{
		/*$pass1=hashandsalt(mysql_real_escape_string($_POST['adminuser']),mysql_real_escape_string($_POST['adminpass']));
		$pass2=hashandsalt(mysql_real_escape_string($_POST['adminuser']),$pass1);
		$uaq=ysql_query("SELECT `username`,`apassword` FROM admin WHERE `username`='$_POST[adminuser]' AND `apassword`='*$pass2'",CON1) or die(mysql_error());
		$uan=mysql_num_rows($uaq);*/
		$pass1=hashandsalt($_POST['adminuser'],$_POST['adminpass']);
		$pass2=hashandsalt($_POST['adminuser'],$pass1);
		$uaq=$db1->prepare("SELECT `username`,`apassword` FROM admin WHERE `username`=? AND `apassword`=?");
		$uaq->execute(array($_POST['adminuser'],'*'.$pass2));
		$uan=$uaq->rowCount();
		if($uan>0)//user found
		{
			//$uaa=mysql_fetch_assoc($uaq);
			$uaa=$uaq->fetch();
			//$_SESSION['adminpass']=$pass1;
			//$_SESSION['adminuser']=$uaa['username'];			
			$coopath=$_SERVER['HTTP_HOST']=="bhweb1"?'/gmk':'/';
			setcookie('adminpass',$pass1,time()+$adminidletime,$coopath);
			setcookie('adminuser',$uaa['username'],time()+$adminidletime,$coopath);
			$_SESSION['error']="";
			header("Location: index.php".(isset($_SESSION['query'])&&!stristr($_SESSION['query'],"login")?"?".$_SESSION['query']:""));
		}
		else//user not found
		{
			$_SESSION['error']="Username or password incorrect, please try again.";
			header("Location: index.php".(isset($_SESSION['query'])&&!stristr($_SESSION['query'],"login")?"?".$_SESSION['query']."&loginerr=2":"?p=login&loginerr=2"));
			//header("Location: login.php");
		}
	}
}
?>