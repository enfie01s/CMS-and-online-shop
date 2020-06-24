<?php 
session_name("gmk");
session_start();
include "../../config.inc.php";
if(strtolower($_SERVER['REQUEST_METHOD'])!="post"){die("Access Denied");}//Direct access security
include "cart_functions.php";
if(isset($post_arr['identifier'])&&$post_arr['identifier']=="login")
{
	if($post_arr['pass']==""||$post_arr['email']=="")//a missing field
	{
		$_SESSION['error']="You did not fill in all information. Please make sure all fields are completed.";
		$to_p=isset($get_arr['to_'])?"&to_=".$get_arr['to_']:"";
		header("Location: ".MAINBASE."/cart_login".$to_p);
	}
	else//fields not empty
	{
		$pass1=cart_hashandsalt($post_arr['email'],$post_arr['pass']);
		$pass2=cart_hashandsalt($post_arr['email'],$pass1);
		/*$uq=ysql_query("SELECT `email`,`gpassword` FROM cart_customers WHERE `email`='$post_arr[email]' AND `gpassword`='$pass2'",CARTDB);
		$un=mysql_num_rows($uq);*/
		$uq=$db1->prepare("SELECT `email`,`gpassword` FROM cart_customers WHERE `email`=? AND `gpassword`=?");
		$uq->execute(array($post_arr['email'],$pass2));
		$un=$uq->rowCount();
		if($un>0)//user found
		{
			//$ua=mysql_fetch_assoc($uq);
			$ua=$uq->fetch(PDO::FETCH_ASSOC);
			$_SESSION['pass']=$pass1;
			$_SESSION['email']=$ua['email'];
			/*if(isset($post_arr['cookie'])){
			setcookie("pass",$_SESSION['pass'],time()+3600*24*30,"/",MAINBASE);
			setcookie("email",$_SESSION['email'],time()+3600*24*30,"/",MAINBASE);
			}*/
			$goto=$post_arr['redirectstring'];
			$whichbase=$goto=="p=cart_co_address"||$goto=="/cart_co_address"?SECUREBASE:MAINBASE;
			if(substr($goto,0,1)=="p"){$goto="/index.php?".$goto;}
			header("Location: ".$whichbase.$goto);
		}
		else//user not found
		{
			$_SESSION['error']="There was a problem with your email or password, please try again (and check caps lock).";
			$goto=(isset($post_arr['redirectstring'])&&$post_arr['redirectstring']=="/cart_co_address")?"cart_login&to_=/cart_co_address":"cart_login";
			$_SESSION['failpass']=$post_arr['pass'];
			$_SESSION['failemail']=$post_arr['email'];
			//echo $pass2;
			header("Location: ".MAINBASE."/".$goto);
		}
	}
}
?>