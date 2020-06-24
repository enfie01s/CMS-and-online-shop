<?php
/*if(basename($_SERVER['PHP_SELF'])=="cart_genfroogle.php"){die("Access Denied");}//direct access security 

$doc = new DOMDocument();
$date_generated = $doc->createComment( "Generated: ".date("l jS F Y H:i") );
$doc->appendChild($date_generated);
$doc->formatOutput = true; 
$rss = $doc->createElement("rss");
$rss->setAttribute("version","2.0");
$rss->setAttribute("xmlns:g","http://base.google.com/ns/1.0");
$rss->setAttribute("xmlns:c","http://base.google.com/cns/1.0");

$r = $doc->createElement( "channel" ); 
	$rss->appendChild( $r ); 
	
	$title = $doc->createElement( "title" ); 
	$title->appendChild($doc->createTextNode( $sitename." Products" )); 
	$r->appendChild( $title ); 
	
	$link = $doc->createElement( "link" ); 
	$link->appendChild($doc->createTextNode( $webby )); 
	$r->appendChild( $link ); 
	
	$desc = $doc->createElement( "description" ); 
	$desc->appendChild($doc->createTextNode( "Official GMK Website. Shop online for GMK Spares." )); 
	$r->appendChild( $desc ); 

	$cats=mysql_query("SELECT c.`".CFIELDNAME."` as title,c.`".CFIELDID."` as cat_id FROM ".CTABLE." as c JOIN cart_catopts as o ON o.`cat_id`=c.`cid` WHERE `showincart`='1'",CARTDB);
	while($cat=mysql_fetch_assoc($cats))
	{	
		$froogle_prods=mysql_query("SELECT p.`".PFIELDNAME."` as title,cf.`".PFIELDSKU."` as sku,p.`".PFIELDID."` as prod_id,p.`".PFIELDDESC."` as content,`fusionId`,cf.`price`,cf.`saleprice`,`weight` FROM (".PTABLE." as p JOIN cart_fusion as cf ON cf.`pid`=p.`".PFIELDID."`) JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND f.`itemType`='product' AND p.`".PFIELDSHOW."`='1' WHERE `ownerId`='".$cat['cat_id']."' AND `ownerType`='category' AND `allowpurchase`='1'",CARTDB);
		while($froogle_prod=mysql_fetch_assoc($froogle_prods))
		{
			$price=$froogle_prod['saleprice']!=0?$froogle_prod['saleprice']:$froogle_prod['price'];
			$topcat=cart_gettopcat($cat['cat_id']);
			$stocks=mysql_query("SELECT SUM(`nav_qty`) FROM nav_stock WHERE `nav_sku`='$froogle_prod[sku]'",CARTDB);
			list($stock)=mysql_fetch_row($stocks);
			$stock=($stock==""||!isset($stock))?"out of stock":($stock<$stocklimit?"limited availability":"in stock");
			$totalrevs=0;
			$reviewsq=@mysql_query("SELECT AVG(`rank`) as avgrank,count(`rank`) as totalrevs FROM ".PTABLE." as p LEFT JOIN cart_reviews as cr ON p.`".PFIELDID."`=cr.`item_id` WHERE p.`".PFIELDID."`='$froogle_prod[prod_id]' GROUP BY p.`".PFIELDID."`",CARTDB);
			@list($avgrank,$totalrevs)=@mysql_fetch_row($reviewsq);			
			
			$h = $doc->createElement( "item" );// item start
			
				$ititle = $doc->createElement( "title" ); 
				$ititle->appendChild($doc->createTextNode( $froogle_prod['title'] )); 
				$h->appendChild( $ititle ); 
			
				$ilink = $doc->createElement( "link" ); 
				$ilink->appendChild($doc->createTextNode( str_replace(" ","%20",$webby."/index.php?p=cart_products&pid=".$froogle_prod['fusionId']."&prodname=".urlencode($froogle_prod['title'])) )); 
				$h->appendChild( $ilink ); 
			
				$idesc = $doc->createElement( "description" ); 
				$idesc->appendChild($doc->createTextNode( strip_tags(str_replace(array("<br />","<br>","\'","&acute;","&#160;"),array("\r\n","\r\n","'","'"," "),$froogle_prod['content'])) )); 
				$h->appendChild( $idesc ); 
			
				$iimg = $doc->createElement( "g:image_link" ); 
				$iimg->appendChild($doc->createTextNode( str_replace(" ","%20",$webby."/content/images/products/".$froogle_prod['prod_id']."/small.jpg") )); 
				$h->appendChild( $iimg ); 
			
				$ibrand = $doc->createElement( "g:brand" ); 
				$ibrand->appendChild($doc->createTextNode( $sitename )); 
				$h->appendChild( $ibrand ); 
				
				if($froogle_prod['weight']>0)
				{
					$ishipping_weight = $doc->createElement( "g:shipping_weight" ); 
					$ishipping_weight->appendChild($doc->createTextNode( $froogle_prod['weight']." kilograms" )); 
					$h->appendChild( $ishipping_weight ); 
				}
				
				$iprice = $doc->createElement( "g:price" ); 
				$iprice->appendChild($doc->createTextNode( number_format($price+($vat*($price/100)),2) )); 
				$h->appendChild( $iprice ); 
			
				$icondition = $doc->createElement( "g:condition" ); 
				$icondition->appendChild($doc->createTextNode( "New" )); 
				$h->appendChild( $icondition ); 
			
				$iid = $doc->createElement( "g:id" ); 
				$iid->appendChild($doc->createTextNode( $froogle_prod['fusionId'] )); 
				$h->appendChild( $iid ); 
				
				$itype = $doc->createElement( "g:product_type" ); 
				$itype->appendChild($doc->createTextNode( ucwords($topcat[1]) )); 
				$h->appendChild( $itype ); 
				
				$istock = $doc->createElement( "g:availability" ); 
				$istock->appendChild($doc->createTextNode( $stock )); 
				$h->appendChild( $istock ); 
				
				if($totalrevs>0){
					$avg = $doc->createElement( "g:product_review_average" ); 
					$avg->appendChild($doc->createTextNode( $avgrank )); 
					$h->appendChild( $avg ); 
					
					$rev = $doc->createElement( "g:product_review_count" ); 
					$rev->appendChild($doc->createTextNode( $totalrevs )); 
					$h->appendChild( $rev ); 
				}
				
			$r->appendChild( $h ); //item end
		}
	}
$doc->appendChild($rss);
$doc->save($_SERVER['DOCUMENT_ROOT'].'/froogle.xml');

$put=0;
$auth="";
if(file_exists($_SERVER['DOCUMENT_ROOT']."/froogle.xml")&&$cart_live==1)
{
	//$conn = ftp_connect($froogle_serv);
	//$auth = ftp_login($conn,$froogle_user,$froogle_pass);
	//ftp_pasv($conn, true);
	//ftp_set_option($conn,FTP_TIMEOUT_SEC,120);
	//$put = ftp_put($conn,"froogle.xml",$_SERVER['DOCUMENT_ROOT']."/froogle.xml",FTP_ASCII);
	//ftp_close($conn);
}
else if(!file_exists("froogle.xml"))
{
	$put="File hasn't been generated";
}

if($uaa['super']==1)
{
	if($put==1&&$inhouse==0)
	{
			echo "<div style='text-align:center'>Successfully updated products on Google Products ".date("l jS F Y H:i")."</div>";
		
	}
	else if($put != 1)
	{
		echo "<div style='text-align:center'>There was an error while attempting to update products on Google Products".($auth!=1?"<br />".$auth:"")."</div>";		
	}
}
else if($put!=1){mail("senfield@gmk.co.uk","Error updating Google Products","There was an error while ".ucwords($uaa['username'])." was updating Google Products","From: ".$sitename." <$admin_email>\r\nReply-To: $admin_email\r\n");}*/
?>