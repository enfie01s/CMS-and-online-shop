<?php
// Brochure Array "folder name" => "Brochure Name"
if(!isset($brochure)){$brochure="";}
$brochures = array( 
	
	"accessories" => "Accessories 2013: Brought together under the well respected gmk name, this catalogue contains some of the finest shooting accessories available in the uk. Each item has been carefully selected from around the world, with our market in mind.",
	
);

$postablebrochures=array();

function firstfile($dir)
{
	$h = glob($dir);
  return $h[0]; 
}

//dimensions
//h=small height,h_z=large height
//w=double small width,w_z=large width

if(strlen($brochure)>0)
{
	$sdims=getimagesize(firstfile('../../brochures/'.$brochure.'/pages/*_Page_*.jpg'));//*_Page to allow for different case file names
	$ldims=getimagesize(firstfile('../../brochures/'.$brochure.'/pages/large/*_Page_*.jpg'));
	$w = floor($sdims[0]*2);
	$w_z = floor($ldims[0]);
	$h =floor($sdims[1]);
	$h_z=floor($ldims[1]);
}
/*
switch ($brochure)
{
	
	case "accessories":
		$h = 679; $h_z = 1414;
		$w = 960; $w_z = 1000;
		break;
	
	}
*/
?>