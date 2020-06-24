<div><img src='./content/images/main/title_vbrochure.jpg' alt='Viewing Brochures' /></div>
<div class='heading'>To view a brochure, please click on it's image (below).<?php if($deviceType!="phone"){?> Hover over a brochure to view more information.<?php }?></div>

<?php 
$rowlen=0;
$newrows=0;
$brobits="";
list($shelfwid,$shelfhei)=getimagesize("content/images/brochures/shelf.jpg");
$broheight=135;
$padd=25;
$containwidth=$shelfwid-($padd*2);
foreach($brochures as $brochure => $name)
{
	list($mainwid, $mainhei, $maintype, $mainattr) = getimagesize("content/brochures/".$brochure."/thumbnail.jpg");
	$mainwid+=7;
	/*$rowlen+=$mainwida;
	$adjust=$broheight-$mainhei;
	if($rowlen>$containwidth)
	{
		$rowlen=$mainwida;
		$newrows++;
	}
	$top=$shelfhei*$newrows;
	$top+=$adjust>0?$adjust:0;*/
	//$brobits.="<div style='position:absolute;left:".($rowlen-$mainwid+$padd)."px;top:".($top+13)."px;' class='shadow'><a href=\"javascript: brochure('".$brochure."');\" title='".$name."'><img src='./content/images/brochures/".$brochure.".jpg' alt='".$name."' /></a></div>";
	$row=!isset($row)||$row=="right_dark"?"left_light":"right_dark";
	$infobits=stristr($name,":")?explode(":",$name):array();
	$title=$deviceType=="phone"&&strlen($infobits[0])>0?$infobits[0]:$name;
	$title1=$deviceType=="phone"&&strlen($title)>0?"<div class='titlesbold'>".$title."</div>":"";
	$detail=$deviceType=="phone"&&strlen($infobits[1])>0?$infobits[1]:"";
	$detail1=strlen($detail)>0?"<div style=''>".$detail."</div>":"";
	$link=$deviceType!="computer"||!is_dir("./content/brochures/".$brochure."/pages/")?"./content/brochures/".$brochure."/".$brochure.".pdf":"javascript: brochure('".$brochure."');";
	$brobits.="<div ".($deviceType=="phone"?"":"style='width:".$mainwid."px;'")." class='shadow'><a href=\"".$link."\" title='".$name."' ".($deviceType=="phone"?"class='".$row."'":"")."><img src='./content/brochures/".$brochure."/thumbnail.jpg' alt='".$title."' />$title1 $detail1 <div class='clear'></div></a></div>";
}
?>
<div class="clear"></div>
<div class="brochures">
<?=$brobits?>
</div>
<div id="brochureextrainfo"> Click <a href="./brochureform">here</a> to request a brochure by post.</div>
