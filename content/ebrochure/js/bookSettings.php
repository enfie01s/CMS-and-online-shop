<?php
$brochure = $_GET['b'];

// Default sizes
include("../../brochures/config.php"); 




print 'flippingBook.pages = [';  					  

if ($handle = opendir("../../brochures/" . $brochure . "/pages")) 
	{
	$row = array();
	while (false !== ($file = readdir($handle))) 
		{ 
		if ($file !== "." and $file !== ".." and $file !== "large" and $file !== "Thumbs.db" and $file !== "_notes")
			{	
			$row[] = '"../brochures/'.$brochure.'/pages/' . $file . '", '; 	
			}
		}
	sort($row);	

	$rows = '';
	foreach ($row as $sorted_rows)
		{
		$rows .= $sorted_rows;
		}	
	print substr($rows, 0, -2);	

	closedir($handle);
	}

print ']; flippingBook.contents = [';

include('../../brochures/'.$brochure.'/contents.php');

print '];

flippingBook.settings.bookWidth = '.$w.';
flippingBook.settings.bookHeight = '.$h.';
flippingBook.settings.zoomImageWidth = '.$w_z.';
flippingBook.settings.zoomImageHeight = '.$h_z.';
flippingBook.settings.pageBackgroundColor = 0x25435F;
flippingBook.settings.backgroundColor = 0x25435F;
flippingBook.settings.zoomUIColor = 0x5580AB;
flippingBook.settings.smoothPages = false;	
flippingBook.settings.useCustomCursors = false;
flippingBook.settings.dropShadowEnabled = false,
flippingBook.settings.zoomHintEnabled = true;
flippingBook.settings.downloadURL = "../brochures/'.$brochure.'/'.$brochure.'.pdf";
flippingBook.settings.flipSound = "";
flippingBook.settings.flipCornerStyle = "first page only";

flippingBook.create();
'; ?>