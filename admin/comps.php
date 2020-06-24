<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Competitions</a></div>
<?php
$comps=array("cpsa premier league 2015"=>"10 September 2015");
//$result = ysql_query("SELECT * FROM vacancies ORDER BY `start` DESC",$con1) or die(sql_error("Error"));
?>
<table class="linkslist"> 
	<tr class="head">
		<td colspan="6">Listing Competitions</td>
	</tr> 
	<tr class="subhead">
		<td style="width:50%"><div class="titles">Title</div></td>
		<td style="width:20%;text-align:center">Closing Date</td>
		<td style="width:10%;text-align:right">Entries</td>	
		<td style="width:20%;">Random Entry</td>	
	</tr> 
	<?php 
	foreach($comps as $compname => $ending)
	{ 
		$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
		/*$countQ= ysql_query("SELECT count(*) FROM comps WHERE compname='{$compname}'",$con1) or die(sql_error("Error"));
		list($numentered)=mysql_fetch_row($countQ);
		$winQ=ysql_query("SELECT CONCAT(`firstname`,', ',`lastname`) FROM comps WHERE compname='{$compname}' ORDER BY RAND() LIMIT 1",$con1) or die(sql_error("Error"));
		list($win)=mysql_fetch_row($winQ);*/
		$countQ= $db1->prepare("SELECT count(*) FROM comps WHERE compname=?");
		$countQ->execute(array($compname));
		list($numentered)=$countQ->fetch();
		$winQ=$db1->prepare("SELECT CONCAT(`firstname`,', ',`lastname`) FROM comps WHERE compname=? ORDER BY RAND() LIMIT 1");
		$winQ->execute(array($compname));
		list($win)=$winQ->fetch();
		?>
		<tr class="<?=$row_class?>">
			<td><?=ucwords($compname)?></td>
			<td style="text-align:center"><?=date("jS M y",strtotime($ending))?></td>
			<td style="text-align:right"><?=$numentered?></td>
			<td><?=$win?></td>			
		</tr>
		<?php 
	}
	?>
</table>