<?php $httppath = "http://bhweb1/GMKmockup/";if(!isset($page)){header("Location: ".$httppath."index.php");}?>
<div><img src="./content/images/main/title_info.jpg" alt="Useful Info" /></div>
<br />
<div style="width:200px;display:inline"><img src="./content/images/main/pdf-icon.png" alt="PDF - " /> <a href="./content/pdf/loyalty-scheme2014.pdf" style="font-size:14px;" title="View/Download the 2014 Beretta Domestic Loyalty Scheme">2014 Beretta Domestic Loyalty Scheme</a></div>
<div style="width:200px;display:inline"><img src="./content/images/main/pdf-icon.png" alt="PDF - " /> <a href="./content/pdf/FEDELTA%202014.pdf" style="font-size:14px;" title="View/Download the 2014 FEDELTA Loyalty Scheme">2014 FEDELTA Loyalty Scheme</a></div>
<br /><br />
<div><img src="./content/images/main/title_youngshots.jpg" alt="Young Shots Scheme" /></div>
<div><a href="./content/pdf/young-shots.pdf" target="_blank">Click here for more information.</a></div>
<div><a href="./content/pdf/young-shots_application.pdf" target="_blank">Click here for the application form.</a></div>
<br />
<div><img src="./content/images/main/title_choke.jpg" alt="Chokes Guide" /></div>
<div><a href="./cart_chokeguide">Click here for our chokes guide.</a></div>
<br />
<div><img src="./content/images/main/title_guncare.jpg" alt="Gun Care" /></div>
<div><img src='./content/images/main/malcolm.png' alt='' style='float:right' />
<span class='heading'>4 ways to avoid Malcom</span><br /><br />Malcolm Grendon is the manager of GMK's workshop and during the year he handles hundreds of rifles and shotguns sent for repair or service. According to Malcolm, although some of the work arises from accidents and mishaps, most of it would be avoidable if owners took a few precautions in the form of simple maintenance and care. So if you want to keep clear of a potentially expensive introduction to Malcolm and his team take heed:</div><br /><br />
<ol>
<li style="margin-bottom:20px"><strong><u>Keep your knuckles in fighting order</u></strong> The knuckle is the name of the hinge where the barrels open. A simple, light, application of oil each time the gun is used will prevent the scoring that will otherwise occur, eventually leading to real difficulty getting the gun to open or close.</li>

<li style="margin-bottom:20px"><strong><u>Guns are not waterproof...</u></strong> Time and again owners will, after a day's shooting or at the end of the season, put their guns away wet and time again they will experience problems with rust. Drying the gun and from time to time applying a little gun oil will stop any possibility of rust damage.</li>

<li style="margin-bottom:20px"><strong><u>...and neither is wood</u></strong> Constant exposure to wet conditions will cause the stock to expand or heave causing splitting. All that's needed is to dry off the weapon fully and every so often apply a little conditioning oil (available from all retailers) to the stock</li>

<li><strong><u>Don't get choked up</u></strong> Keeping your multi-chokes clean is essential. Frequently these are left on the gun dirty. Eventually they become corroded in place with expensive results. They should be cleaned and slightly loosened when the gun is put away - but don't forget to tighten them up before use. The rules apply if your rifle is fitted with a sound moderator. </li>
</ol>
<h1>Beretta Engraving</h1><br />
<div id="engravingtext">
	Click on the 'Engraving No' to see detailed images.<br /><br />
	<div class='heading'>The Engraver's Signature</div>
	<br />
	The Grand Master Engravers are proud to express their full skill on the prestigious Premium Grade Beretta Guns. Whether the technique is that of hammer and chisel; or that of gold wire inlay (the technique of the ancient Egyptian Goldsmiths), the result is unquestionably a masterpiece in miniature.<br />
	<br />
	Floral motifs, traditional game scenes, exotic subjects, English fine scroll, mythological scenes, gold inlaid animals... the most sophisticated and varied engraving styles can be executed on the Beretta Premium Grades. When this work of art is completed, the engraver rightfully inscribes on it his signature, authenticating his personal and unmistakable style, and guaranteeing the status of these guns as unique expressions of the gun-maker's art.<br /><br />
	 A tradition of excellence since 1526
</div>

<table class='engravingdiv' id='engravingtbl' cellpadding="0" cellspacing="1">
	<tr>
		<td class='engravinghead'>Engraving No</td>
		<td class='engravinghead'>Class</td>
		<td class='engravinghead'>SO6 EELL</td>
		<td class='engravinghead'>SO10</td>
		<td class='engravinghead'>SO10 EELL</td>
		<td class='engravinghead'>Imperiale</td>
</tr>
<?php
$query = "SELECT * FROM gmkengraving ORDER BY `id`";
$a = 0;
/*$result = ysql_query($query,$con1) or die(sql_error("Error"));
while($engraving = mysql_fetch_array($result))*/
$result = $db1->query($query);
while($engraving = $result->fetch())
{
	$class = ($a%2 == 0) ? "engravingrow1" : "engravingrow0";
	?>
	<tr>
		<td class='<?=$class?>'>
			<a style="font-size:11px;" href="content/images/engravings/<?=$engraving['Engraving no']?>a.jpg" target="lightbox[<?=$engraving['Engraving no']?>]"><?=$engraving['Engraving no']?></a><a style="font-size:11px;text-decoration:none" href="content/images/engravings/<?=$engraving['Engraving no']?>b.jpg" target="lightbox[<?=$engraving['Engraving no']?>]"></a><?php if ($engraving['Engraving no'] != "6G3" && $engraving['Engraving no'] != "3S1"){?><a style="font-size:10px;text-decoration:none" href="content/images/engravings/<?=$engraving['Engraving no']?>c.jpg" target="lightbox[<?=$engraving['Engraving no']?>]"></a><?php }?>
		</td>
		<td class='<?=$class?>'><?=$engraving['Class']?></td>
		<td class='<?=$class?>'><img src='./content/images/engravings/eng<?=$engraving['SO6 EELL']?>.png' alt='<?=$engraving['SO6 EELL']?>' />&nbsp;</td>
		<td class='<?=$class?>'><img src='./content/images/engravings/eng<?=$engraving['SO10']?>.png' alt='<?=$engraving['SO10']?>' />&nbsp;</td>
		<td class='<?=$class?>'><img src='./content/images/engravings/eng<?=$engraving['SO10 EELL']?>.png' alt='<?=$engraving['SO10 EELL']?>' />&nbsp;</td>
		<td class='<?=$class?>'><img src='./content/images/engravings/eng<?=$engraving['Imperiale']?>.png' alt='<?=$engraving['Imperiale']?>' />&nbsp;</td>
	</tr>
	<?php
	$a++;
}
?>
<tr>
	<td class='engravingfoot' colspan="6"><div><img src='./content/images/engravings/engs.png' alt='s' /> = Standard Engraving<br />(no additional charge)</div><div><img src='./content/images/engravings/engc.png' alt='c' /> = Custom Engraving<br />(additional charge applies)</div></td>
</tr>
</table>

</div><div class="clear"></div>