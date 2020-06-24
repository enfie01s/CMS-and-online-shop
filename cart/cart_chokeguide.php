<?php if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
include "cart_head.php";?>
<h2 id="pagetitle">Choke Guide</h2>
<img src="<?=$root_to_cart?>/images/ChokeGuide.jpg" alt="" />
<?php 
/*$imgs=glob("./".$root_to_cart."/images/chokeguides/*.jpg");
foreach($imgs as $i => $im)
{
	?><a title="<?=basename($im,".jpg")?>" href="<?=$im?>" target="_blank" style="margin:2px;display:inline-block"><img src="<?=$im?>" alt="" style="width:250px" /></a><?php
}*/
?>
<?php include "cart_foot.php";?>
