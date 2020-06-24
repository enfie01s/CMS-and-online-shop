<?php $httppath = "http://www.gmk.co.uk/";if(!isset($page)){header("Location: ".$httppath."index.php");} 
if($submitok == 1)
{
	$send_togmk = "warranties@gmk.co.uk";
	$subjectgmk = "GMK Warranty Registration";
	$headersgmk = "From: $nametitle $firstname $lastname <$email>\r\n";
	$headersgmk .= "Reply-To: $email\r\n";
	$headersgmk .= "Return-Path: $email\r\n";
	$update = ($mailinglist == "Yes") ? "YES" : "NO";
	$ten = ($tenyr == "Yes") ? "YES" : "NO";
	
	$bodygmk = "Warranty Registration.\r\n" . 
	"_____________________________________________\r\n\r\n" .  
	"Product:\t\t" . $product . "\r\n" .  
	"Brand:\t\t\t" . $brand . "\r\n" .
	"Serial Number:\t\t" . $serial . "\r\n" .
	"Date Purchased:\t" . $date . "\r\n" .
	"Purchased From:\t" . $fromshop . "\r\n" .
	"Area of interest:\t" . $areaofinterest . "\r\n\r\n" .
	
	"Title:\t\t" . $nametitle . "\r\n" .	
	"First Name:\t" . $firstname . "\r\n" .
	"Last Name:\t" . $lastname . "\r\n" .
	"Address 1:\t" . $address1 . "\r\n" .
	"Address 2:\t" . $address2 . "\r\n" .
	"Town/City:\t" . $city . "\r\n" . 
	"County:\t" . $county . "\r\n" .
	"Postcode:\t" . $postcode . "\r\n" .
	"Telephone No:\t" . $telephone . "\r\n" .
	"Email Address:\t" . $email . "\r\n\r\n" .
	
	"OK to receive product offers and updates via email?  " . $update . "\r\n";
	
	//customer confirmation (\t = tab space so it displays aligned in outlook)
	$send_to = "$email";
	$subject = "GMK Warranty Registration Confirmation";
	$headers = "From: GMK <auto-confirm@gmk.co.uk>\r\n";
	$headers .= "Reply-To: $email\r\n";
	$headers .= "Return-Path: $email\r\n";

	$body = "Warranty Registration Confirmation.\r\n" . 
	"_____________________________________________\r\n\r\n" . 
	"Thank you for registering your Beretta Gun with GMK, you will receive a written confirmation within 7 days.\r\n" . 
	"Please keep a copy of this email for your records.\r\n\r\n" . 
	"Product:\t\t" . $product . "\r\n" .
	"Brand:\t\t\t" . $brand . "\r\n" .
	"Serial Number:\t\t" . $serial . "\r\n" .
	"Date Purchased:\t" . $date . "\r\n" .
	"Purchased From:\t" . $fromshop . "\r\n\r\n" .
	
	"Title:\t\t" . $nametitle . "\r\n" .	
	"First Name:\t" . $firstname . "\r\n" .
	"Last Name:\t" . $lastname . "\r\n" .
	"Address 1:\t" . $address1 . "\r\n" .
	"Address 2:\t" . $address2 . "\r\n" .
	"Town/City:\t" . $city . "\r\n" . 
	"County:\t" . $county . "\r\n" .
	"Postcode:\t" . $postcode . "\r\n" .
	"Telephone No:\t" . $telephone . "\r\n";
}
else
{
	header("Location: ".$httppath."index.php"); 
}
?>