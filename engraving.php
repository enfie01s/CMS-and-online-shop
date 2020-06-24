<?php $httppath = "http://bhweb1/GMKmockup/";if(!isset($page)){header("Location: ".$httppath."index.php");}?>
<div><img src="./content/images/main/title_engraving.jpg" alt="Beretta Engraving" /></div><br />
<div style='float:left;width:220px;font-size:1.1em;line-height:1.3em;padding-right:30px;'>
	Click on the 'Engraving No' to see detailed images.<br /><br />
	<div class='heading'>The Engraver's Signature</div>
	<br />
	The Grand Master Engravers are proud to express their full skill on the prestigious Premium Grade Beretta Guns. Whether the technique is that of hammer and chisel; or that of gold wire inlay (the technique of the ancient Egyptian Goldsmiths), the result is unquestionably a masterpiece in miniature.<br />
	<br />
	Floral motifs, traditional game scenes, exotic subjects, English fine scroll, mythological scenes, gold inlaid animals... the most sophisticated and varied engraving styles can be executed on the Beretta Premium Grades. When this work of art is completed, the engraver rightfully inscribes on it his signature, authenticating his personal and unmistakable style, and guaranteeing the status of these guns as unique expressions of the gun-maker's art.<br /><br />
	 A tradition of excellence since 1526
</div>
<div class='engravingdiv' style='width:360px;padding:1px;float:left'>
	<div class='engravinghead' style='float:left;margin-right:1px;width:72px;'>Engraving No</div><div class='engravinghead' style='float:left;margin-right:1px;width:30px;'>Class</div><div class='engravinghead' style='float:left;margin-right:1px;width:51px;'>SO6 EELL</div><div class='engravinghead' style='float:left;margin-right:1px;width:30px;'>SO10</div><div class='engravinghead' style='float:left;margin-right:1px;width:58px;'>SO10 EELL</div><div class='engravinghead' style='float:right;width:54px;'>Imperiale</div>
<div class="clear"></div>
<?php
$a = 0;
$query = "SELECT * FROM gmkengraving ORDER BY id";
/*$result = ysql_query($query,$con1) or die(mysql_error());
while($engraving = mysql_fetch_array($result))*/
$result = $db1->query($query);
while($engraving = $result->fetch())
{
	$class = ($a%2 == 0) ? "engravingrow1" : "engravingrow0";
	?>
	<div class='<?=$class?>' style='float:left;margin-right:1px;width:72px;'>
		<a style="font-size:11px;" href="content/images/engravings/<?=$engraving['Engraving no']?>a.jpg" target="lightbox[<?=$engraving['Engraving no']?>]"><?=$engraving['Engraving no']?></a><a style="font-size:11px;text-decoration:none" href="content/images/engravings/<?=$engraving['Engraving no']?>b.jpg" target="lightbox[<?=$engraving['Engraving no']?>]"></a><?php if ($engraving['Engraving no'] != "6G3" && $engraving['Engraving no'] != "3S1"){?><a style="font-size:10px;text-decoration:none" href="content/images/engravings/<?=$engraving['Engraving no']?>c.jpg" target="lightbox[<?=$engraving['Engraving no']?>]"></a><?php }?>
	</div>
	<div class='<?=$class?>' style='float:left;margin-right:1px;width:30px;'><?=$engraving['Class']?></div>
	<div class='<?=$class?>' style='float:left;margin-right:1px;width:51px;'><img src='./content/images/engravings/eng<?=$engraving['SO6 EELL']?>.png' alt='<?=$engraving['SO6 EELL']?>' />&nbsp;</div>
	<div class='<?=$class?>' style='float:left;margin-right:1px;width:30px;'><img src='./content/images/engravings/eng<?=$engraving['SO10']?>.png' alt='<?=$engraving['SO10']?>' />&nbsp;</div>
	<div class='<?=$class?>' style='float:left;margin-right:1px;width:58px;'><img src='./content/images/engravings/eng<?=$engraving['SO10 EELL']?>.png' alt='<?=$engraving['SO10 EELL']?>' />&nbsp;</div>
	<div class='<?=$class?>' style='float:right;width:54px;'><img src='./content/images/engravings/eng<?=$engraving['Imperiale']?>.png' alt='<?=$engraving['Imperiale']?>' />&nbsp;</div>
	<div class="clear"></div>
	<?php
	$a++;
}
?>
<div class='engravinghead' style='float:left;padding:5px 10px 5px 5px;width:128px;margin-top:1px;font-size:0.9em;'><img src='./content/images/engravings/engs.png' alt='s' /> = Standard Engraving<br />(no additional charge)</div><div class='engravinghead' style='float:left;padding:5px 10px 5px 5px;width:202px;margin-top:1px;font-size:0.9em;'><img src='./content/images/engravings/engc.png' alt='c' /> = Custom Engraving<br />(additional charge applies)</div>
<div class="clear"></div>
</div><div class="clear"></div>