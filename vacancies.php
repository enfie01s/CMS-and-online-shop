<div><img src='./content/images/main/title_vacancies.jpg' alt='' /></div>
<div id="vacancies">
<?php
if(!isset($_GET['id']))
{
	$loop=0;
	/*$vacQ=ysql_query("SELECT * FROM vacancies WHERE ((start<=NOW() AND expire>NOW()) OR `force`=1) AND display=1 ORDER BY featured DESC,start DESC",CON1);
	if(mysql_num_rows($vacQ))
	{
		while($vac=mysql_fetch_assoc($vacQ))*/
		$vacQ=$db1->query("SELECT * FROM vacancies WHERE ((start<=NOW() AND expire>NOW()) OR `force`=1) AND display=1 ORDER BY featured DESC,start DESC");
	if($vacQ->rowCount())
	{
		while($vac=$vacQ->fetch())
		{
			if($loop==0)
			{
				largeVac($vac);
				$loop=1;
			}
			else if(isset($vac['id']))
			{
				if($loop==1){?>
				<h1 style="margin-top:20px">Other Vacancies</h1>
				<ul><?php }
				?><li><a href="?p=vacancies&amp;id=<?=$vac['id']?>"><?=$vac['title']?></a> <?php if($vac['force']==0){?><dfn>Expire<?=strtotime($vac['expire'])>date('U')?"s":"d"?>:  <?=date("jS F Y",strtotime($vac['expire']))?></dfn><?php }?></li><?php
				$loop=2;
			}
		}
		if($loop>0){?></ul><?php }
	}
	else
	{
		?><div class="errorbox">Sorry, there are currently no vacancies</div><?php
	}
}
else
{
	/*$vacQ=ysql_query("SELECT * FROM vacancies WHERE id='{$get_arr['id']}' AND display=1 ORDER BY start DESC",CON1);
	$vac=mysql_fetch_assoc($vacQ);*/
	$vacQ=$db1->prepare("SELECT * FROM vacancies WHERE id=? AND display=1 ORDER BY start DESC");
	$vacQ->execute(array($get_arr['id']));
	$vac=$vacQ->fetch();
	largeVac($vac);
}

function largeVac($ar)
{
	global $prefixpath,$mainbase;
	?>
	<h1><?=$ar['title']?></h1>
	<?php if($ar['force']==0){?><dfn>Vacancy Expire<?=strtotime($ar['expire'])>date('U')?"s":"d"?>: <?=date("jS F Y",strtotime($ar['expire']))?></dfn><?php }?>
	<div><?=$ar['description']?></div>
	<?php 
	$folder="/content/vacancies/".$ar['id'];
	$files=array("mainpdf"=>"View full job specification","addpdf1"=>$ar['addpdf1'],"addpdf2"=>$ar['addpdf2']);
	foreach($files as $t => $n)
	{
		if(is_file($prefixpath.$folder."/".$t.".pdf")){?><a href="<?=$mainbase.$folder?>/<?=$t?>.pdf" class="button" target="_blank"><?=$n?></a><?php }
	}

	
}
?></div>