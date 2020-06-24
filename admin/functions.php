<?php
function getExtension($str) 
{         
	return pathinfo($str, PATHINFO_EXTENSION);
}
function highlightSearch($needle,$hay,$maxlen=80)
{
	$hay=str_replace(array("<br />","<br>","<p>","</p>"),array(" "," "," "," "),$hay);
	$partfound=strripos($hay,$needle);
	$needlelen=strlen($needle);
	$maxback=$maxlen-$needlelen;
	if($partfound!==false)
	{
		$part=substr($hay,$partfound,$needlelen); 
		if(strlen($hay)>$maxlen)
		{
			$from=$partfound-$maxback>0?$partfound-$maxback:0;
			$hay=($from>0?"...":"").substr($hay,$from,$maxlen)."...";
		}
		$hay=str_replace($part,"<span style='color:red'>".$part."</span>",$hay);	
	}
	return $hay;
}
function uploadfile($target_path,$file_name,$tmp_name,$newname,$maxsize,$allowed)
{
	$upload_ok = 1;
	$size1 = number_format(filesize($tmp_name) / 1024,2);
	$filename1 = basename($file_name);
  if ($newname) { $extension1 = strtolower(getExtension($filename1)); }
	$error="";
	if(!in_array($extension1, $allowed)){$error="Error, file extension: $extension1 of file: $file_name not allowed, please try again.";}
	else if($size1 > $maxsize){$error="Error, file too big, please try again.";}
	else if(strlen($filename1) < 1){$error="Error, please choose a file to upload";}
	
	$upload_ok=strlen($error)>0?0:1;
	if ($upload_ok == 1)
	{
		$target_path1 = $target_path . $newname . "." . $extension1;
		if ($newname)
		{
			if(move_uploaded_file($tmp_name, $target_path1)) 
			{ 
				chmod($target_path1, 0755); 
				return $target_path1;
				//header('Location: ../index.php?p=main&sub=lechameau&con=news');
			}
			else 
			{ 
				return "There was an error moving the file, please try again!"; 
			}	
		}	
		else 
		{ 
			return "There was an error uploading the file, please try again!"; 
		}	
	}
	else
	{
		return $error;
	}
}
function bindINs($in)
{
	$vals=explode(",",$in);
	$inPar=str_repeat('?,',count($vals)-1).'?';
	return array($inPar,$vals);
}
function cleanCols($tbl,$srch)
{
	global $db1;
	$database="gmk_main";
	$cols=$db1->query("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$database."' AND `TABLE_NAME`='".$tbl."'");
	$srchCol=stristr($srch,".")===false?$srch:stristr($srch,".");
	$srchCol=str_replace(".","",$srchCol);
	
	while($col=$cols->fetch())
	{
		if($srchCol==$col[0]){return $col[0];}
	}
	return "";
}
function sql_query($query,$dbcon,$binds=array())
{
	global $debugmode,$db1;
	if($debugmode){echo($query."<br />");}
	else{
		//if(is_resource($dbcon)){ysql_query($query,$dbcon)or die(sql_error("Error"));}
		//else{ysql_query($query)or die(sql_error("Error"));}
		if(count($binds)>0)
		{
			$q=$db1->prepare($query);
			$q->execute($binds);
		}
		else
		{
			$db1->query($query);
		}
	}
}
$breadarray=array();
$previewpath=".";
function buildbread($iid,$type)
{	
	global $db1,$breadarray,$get_arr,$previewpath;
	$tablebits=$type=="category"?"gmk_categories as c ON c.`cid`":"gmk_products as p ON p.`pid`";
	//$query="SELECT * FROM fusion as f JOIN $tablebits =f.`itemId` AND `itemType`='$type' WHERE `itemId`='".intval($iid)."'";
	$query="SELECT * FROM fusion as f JOIN $tablebits =f.`itemId` AND `itemType`=? WHERE `itemId`=?";
	/*$breadq=ysql_query($query,$con1) or die(sql_error("Error"));
	$bread=mysql_fetch_assoc($breadq);*/
	$breadq=$db1->prepare($query);
	$breadq->execute(array($type,intval($iid)));
	$bread=$breadq->fetch();
	if($bread['ownerId']!=0){buildbread($bread['ownerId'],$bread['ownerType']);}
	
	if($bread['itemType']=="category"){
		$breadarray["category_".$bread['itemId']]=array($bread['title'],"index.php?p=products&amp;showing=list&amp;owner=$bread[itemId]");
		$previewpath.="/".urlencode(urlencode(strtolower($bread['title'])));
	}
	if($bread['itemType']=="product"){
		$breadarray["product_".$bread['itemId']]=array($bread['prod_title'],"index.php?p=products&amp;showing=prodform&amp;pid=$bread[itemId]&amp;owner=$bread[ownerId]");
	}
}
function bread($iid,$type="",$action="")
{
	global $breadarray,$act,$curpage;
	$type=strlen($type)>0?$type:"category";
	$current=urldecode($curpage);
	$breadarray["Products_0"]=array("Products","index.php?p=products");
	buildbread($iid,$type);
	$breadarray_count=count($breadarray);
	$x=0;
	foreach($breadarray as $id => $textlink)
	{
		$theid=explode("_",$id);
		if(!($theid[1]==$iid&&$theid[0]==$type)||$id=="Products"||strlen($action)>0){?><a href="<?=$textlink[1]?>"><?=htmlspecialchars(ucwords($textlink[0]),ENT_QUOTES,"ISO-8859-1")?></a><?php }
		else{echo htmlspecialchars(ucwords($textlink[0]),ENT_QUOTES,"ISO-8859-1");}
		if($x<$breadarray_count-1){echo " ".SEP." ";}
		$x++;
	}
	if(strlen($action)>0){echo " ".SEP." ".$action;}
}
function mysql_real_extracted($x)
{
	$e=array();
	foreach($x as $f => $v)
	{
		if(!is_array($v)&&is_numeric($v)&&stristr($f,'price')!==false){$v=intval($v);}
		//$e[$f]=is_array($v)?mysql_real_extracted($v):mysql_real_escape_string(stripslashes(str_replace("\\r\\n","",$v)));
		$e[$f]=is_array($v)?mysql_real_extracted($v):stripslashes(str_replace("\\r\\n","",$v));
	}
	return $e;
}
function editparentcats($c,$displ,$pid)
{
	global $db1;
	if(is_array($c))
	{
		$allparents=array();
		$pid=intval($pid);
		/*$parents=ysql_query("SELECT `ownerId` FROM fusion WHERE `itemId`='$pid' AND `itemType`='product' AND `ownerType`='category'",$con1);
		while($parent=mysql_fetch_row($parents))*/
		$parents=$db1->prepare("SELECT `ownerId` FROM fusion WHERE `itemId`=? AND `itemType`='product' AND `ownerType`='category'");
		$parents->execute(array($pid));
		while($parent=$parents->fetch())
		{
			if(in_array($parent[0],$c))
			{
				
			}
			else if(!in_array($parent[0],$c))
			{
				//sql_query("DELETE FROM fusion WHERE `itemId`='$pid' AND `ownerId`='$parent[0]' AND `itemType`='product' AND `ownerType`='category'",$con1);
				sql_query("DELETE FROM fusion WHERE `itemId`=? AND `ownerId`=? AND `itemType`='product' AND `ownerType`='category'",$db1,array($pid,$parent[0]));
			}
			if(!in_array($parent[0],$allparents)){$allparents[]=$parent[0];}
		}
		foreach($c as $cid)
		{
			if(!in_array($cid,$allparents))//in posted parents but not in current parents
			{
				//sql_query("INSERT INTO fusion(`ownerId`,`itemId`,`sorting`,`itemType`,`ownerType`)VALUES('$cid','$pid','1','product','category')",$con1);
				sql_query("INSERT INTO fusion(`ownerId`,`itemId`,`sorting`,`itemType`,`ownerType`)VALUES(?,?,'1','product','category')",$db1,array($cid,$pid));
			}
		}
		return $c[0];
	}	
}
function pagenums($query,$inurl,$prods_per_page,$maxpagelinks,$forceseltype='',$binds)
{
	global $db1,$prods_num,$types,$get_page;
/*	$prods_query1=ysql_query($query,$con1) or die(sql_error("Error"));
	while($type=mysql_fetch_assoc($prods_query1)){if(!in_array($type['type'],$types)&&strlen($type['type'])>0){$types[]=$type['type'];}}
	$prods_num=mysql_num_rows($prods_query1);
	$pgnum=(isset($_GET['page'])&&$_GET['page']>0)?mysql_real_escape_string(intval($_GET['page'])):1;*/
	if(count($binds)>0)
	{
		$prods_query1=$db1->prepare($query);
		$prods_query1->execute($binds);
	}
	else
	{
		$prods_query1=$db1->query($query);
	}
	while($type=$prods_query1->fetch()){if(!in_array($type['type'],$types)&&strlen($type['type'])>0){$types[]=$type['type'];}}
	$prods_num=$prods_query1->rowCount();
	$pgnum=isset($get_page)&&!isset($_GET['p'])?$get_page:(isset($_GET['page'])&&$_GET['page']>0?intval($_GET['page']):1);
	$pgurlbit=isset($get_page)&&!isset($_GET['p'])?"/page":"&amp;page=";
	$pgstart = ($pgnum > 0 && (($pgnum-1)*$prods_per_page) <= $prods_num) ? (($pgnum-1)*$prods_per_page) : 0;
	$pgend = ($pgstart+$prods_per_page >= $prods_num) ? $prods_num : $pgstart+$prods_per_page;
	if($prods_num > $prods_per_page)
	{
		$totalpages = ceil($prods_num/$prods_per_page);//raw pages
		$seltype = strlen($forceseltype)>0?$forceseltype:($totalpages > ($maxpagelinks*2) ? 1 : 0);
		/* LINKS */
		$backlink = $pgnum > 1&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "<a href='$inurl".$pgurlbit.($pgnum-1)."'><img src='./content/images/main/on_back.jpg' alt='| &laquo; BACK' /></a>" : "";
		
		$nextlink = $pgnum < $totalpages&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "<a href='$inurl".$pgurlbit.($pgnum+1)."'><img src='./content/images/main/on_next.jpg' alt='&nbsp;NEXT &raquo; |' /></a>&#160;" : "";
		
		$firstlink =$pgnum > 1&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "<a href='$inurl".$pgurlbit."1'><img src='./content/images/main/on_first.jpg' alt='&#171; FIRST' /></a>" : ($pgnum <= 1&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages))?"<span style='color:#999999'><img src='./content/images/main/off_first.jpg' alt='&#171; FIRST' /></span>":"");
		
		$lastlink = $pgnum < $totalpages&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "<a href='$inurl".$pgurlbit.($totalpages)."'><img src='./content/images/main/on_last.jpg' alt='&#160;LAST &#187;' /></a>" : ($pgnum >= $totalpages&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages))?"<span style='color:#999999'><img src='./content/images/main/off_last.jpg' alt='&#160;LAST &#187;' /></span>":"");
		/* /LINKS */
		$paginationstart = $pgnum > ceil($maxpagelinks/2) && !($totalpages < $maxpagelinks && $pgnum == $totalpages) ? (($pgnum < $totalpages-floor($maxpagelinks/2)) ? $pgnum-($maxpagelinks-3) : ($totalpages-$maxpagelinks+1)) : 1;		
		$pgnumbers = "";
		if($seltype==0)
		{ 
			for($p=$paginationstart;$p<=$totalpages && $p < $paginationstart+$maxpagelinks;$p++)
			{
				if($p == $pgnum){
					$pgnumbers.="<span class='pagelinkon'>$p</span>";
				}else{
					$pgnumbers.="<a href='$inurl".$pgurlbit.$p."' class='pagelink'>$p</a>";
				}
			}
		}
		else
		{
			$pgnumbers="(Page <form action='$inurl' method='get' style='display:inline-block' name='pageform' class='pageform'><select name='page' onchange='location.href=this.options[selectedIndex].value'>";
			for($p=1;$p<=$totalpages;$p++)
			{
				$ss=($p == $pgnum)?"selected='selected'":"";
				$pgnumbers.="<option value='".$inurl.$pgurlbit.$p."' $ss>$p</option>";
			}
			$pgnumbers.="</select></form> of ".$totalpages.")";
		}
		$pagesdisplay="<div class='pagination'>";
		if($seltype==0){$pagesdisplay.="<span class='desc'>$totalpages PAGES:</span> "; }
		$pagesdisplay.="$firstlink $backlink $pgnumbers $nextlink $lastlink</div>";
		if(basename($_SERVER['PHP_SELF'])=="admin.php"){
			$pagesdisplay.="<div class='paginationshowing'>Showing: ".($pgstart+1)." to $pgend of $prods_num</div>";
		}
		$pagesdisplay.="<div class='clear'></div>";
	}else{$pagesdisplay = "";}
	$toreturn=array($query." LIMIT ".(($pgnum-1)*$prods_per_page).",$prods_per_page",$pagesdisplay);
	return $toreturn;
}
function highlighterrors($errorarray,$field)
{
	$dohighlight="";
	if(in_array($field,$errorarray)){$dohighlight="style='border:1px solid red;'"; }
	return $dohighlight;
}
function idhighlighterrors($errorarray,$field,$elementid)
{
	if(in_array($field,$errorarray)){foreach($elementid as $cssid){?><style type="text/css">#<?=$cssid?> {border:1px solid red;}</style><?php }}
}
$cur=0;
$curlev=0;
function parentsoptions($cownid,$selects,$nonselect)
{
	global $db1, $cur, $curlev;
/*	$q=ysql_query("SELECT * FROM gmk_categories as c,fusion as f WHERE `ownerId`='$cownid' AND f.`itemId`=c.`cid` AND `itemType`='category' AND `ownerType`='category' ORDER BY `sorting`",$con1);
	$n=mysql_num_rows($q);*/
	$q=$db1->prepare("SELECT * FROM gmk_categories as c,fusion as f WHERE `ownerId`=? AND f.`itemId`=c.`cid` AND `itemType`='category' AND `ownerType`='category' ORDER BY `sorting`");
	$q->execute(array($cownid));
	$n=$q->rowCount();
	$row=0;
	if($n>0){$curlev++;}
	//while($r=mysql_fetch_assoc($q))
	while($r=$q->fetch())
	{	
		?><option value="<?=$r['cid']?>" <?=in_array($r['cid'],$selects)?"selected='selected'":""?> <?=in_array($r['cid'],$nonselect)?"disabled='disabled'":""?>><?=$curlev>0?str_repeat("&#160;&#160;&#160;&#166;",$curlev)."&#183;&#183;":""?> <?=htmlspecialchars(ucwords($r['title']),ENT_QUOTES,"ISO-8859-1")?></option><?php
		$row++;
		parentsoptions($r['itemId'],$selects,$nonselect);//recursively get children
		if($cur!=$cownid&&$row==$n){$curlev--;$cur=$cownid;}
	}
}
function getparents($cid,$seperator="",$link="")
{
	global $db1;
	$parent="";
	if($cid!=""&&$cid!=0)
	{
/*		$pquery="SELECT * FROM ".CTABLE." as c,fusion as f WHERE `itemId`='$cid' AND f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' AND `ownerType`='category' LIMIT 1";
		$parents_query=ysql_query($pquery,CARTDB);
		$parents=mysql_fetch_assoc($parents_query);
		$parents_num=mysql_num_rows($parents_query);*/
		$pquery="SELECT * FROM ".CTABLE." as c,fusion as f WHERE `itemId`=? AND f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' AND `ownerType`='category' LIMIT 1";
		$parents_query=$db1->prepare($pquery);
		$parents_query->execute(array($cid));
		$parents=$parents_query->fetch();
		$parents_num=$parents_query->rowCount();
		if($parents_num>0)
		{		
			if($parents['ownerId']!=0){$parent.=cart_getparents($parents['ownerId'],$seperator,$link);}//recursively get parents
				$parent.=$seperator;
				if(strlen($link)>0){$parent.="<a href='$link";$parent.=$parents[CFIELDID];$parent.="'>";}
				$parent.=ucwords($parents[CFIELDNAME]);
				if(strlen($link)>0){$parent.="</a>";}
		}
	}
	return $parent;
}
function getparentlayout($cid)
{
	global $db1;
	$parent=array();
	if($cid!=""&&$cid!=0)
	{
/*		$pquery="SELECT * FROM ".CTABLE." as c,fusion as f WHERE `itemId`='$cid' AND f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' AND `ownerType`='category' LIMIT 1";
		$parents_query=ysql_query($pquery,CARTDB);
		$parents=mysql_fetch_assoc($parents_query);
		$parents_num=mysql_num_rows($parents_query);*/
		$pquery="SELECT * FROM ".CTABLE." as c,fusion as f WHERE `itemId`=? AND f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' AND `ownerType`='category' LIMIT 1";
		$parents_query=$db1->prepare($pquery);
		$parents_query->execute(array($cid));
		$parents=$parents_query->fetch();
		$parents_num=$parents_query->rowCount();
		if($parents_num>0)
		{		
			if($parents['ownerId']!=0){$parent=getparentlayout($parents['ownerId']);}//recursively get parents
				$parent[]=array($parents['itemId'],$parents['layout']);
		}
	}
	return $parent;
}
function finderrors($requiredarr,$postdata)
{
	$feerrors="";
	$requireds=explode(",",$requiredarr);
	$reg="^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$";
	foreach($requireds as $require)
	{
		$requirebits=explode(":",$require);
		if(strlen($postdata[$requirebits[0]])<1)
		{
			$feerrors.=$requirebits[1]." is empty.<br />";
		}
		else if(stristr($requirebits[0],"email")&&!eregi($reg, $postdata[$requirebits[0]]))
		{
			$feerrors.=$requirebits[1]." is invalid format (user@host.com).<br />";
		}
	}
	return $feerrors;
}
function productbox($query,$binds=array())
{
	global $perrow,$get_arr,$images_arr,$db1,$mods,$prodmod,$cururl;
	$gunfinder=!isset($get_arr['gunfinder_submit'])&&!isset($get_arr['riflefinder_submit'])?0:1;
	$i=0;
	//$pq=ysql_query($query,CON1);
	//$pn=mysql_num_rows($pq);
	
	if(count($binds)>0)
	{
		$pq=$db1->prepare($query);
		$pq->execute($binds);
	}
	else
	{
		$pq=$db1->query($query);
	}
	$pn=$pq->rowcount();
	if($pn<1)
	{
		?>
		<div class='largenotify'>Sorry, the <?=$gunfinder==1?"gun finder":"product"?> search yeilded no results.<br />
		<?php if($gunfinder == 1){?>
		Please call 01489 579 999 for assistance.
		<?php }else{?>
		Please try our <a href='./gunfinder'>Gun Finder</a> to perform a more thorough search.
		<?php }?>
		</div>
		<?php	
	}
	else
	{
		$introdeployed=0;
		$products=array("withpic"=>array(),"nopic"=>array());
		//while($prod=mysql_fetch_row($pq))
		while($prod=$pq->fetch())
		{
			list($brand,$minprice,$title,$pid,$premium,$type,$intro)=$prod;
			$image="./content/images/products/".$pid.".png";
			if(file_exists($image))
			{
				$products["withpic"][]=$prod;
			}
			else
			{
				$products["nopic"][]=$prod;
			}		
		}
			
		$withcount=count($products["withpic"]);
		$nocount=count($products["nopic"]);
		
		foreach($products['withpic'] as $aid => $trow)
		{
			list($brand,$minprice,$title,$pid,$premium,$type,$intro)=$trow;
			$path=".";
			if($trow['layout']==1)
			{
				if(strlen($trow['path3'])>0){$path.="/".urlencode(urlencode(strtolower($trow['path3'])));}
				if(strlen($trow['path2'])>0){$path.="/".urlencode(urlencode(strtolower($trow['path2'])));}
				if(strlen($trow['path1'])>0){$path.="/".urlencode(urlencode(strtolower($trow['path1'])));}
			}
			$inshop=isset($trow[8])&&$trow[8]==1?1:0;			
			if($introdeployed==0){if(strlen(trim($intro))>3&&isset($get_arr['code'])){?><div style="margin-top:20px;margin-bottom:5px;"><?=$intro?></div><?php }$introdeployed=1;}			
			$i++;
			$image="./content/images/products/".$pid.".png";
			$timage=file_exists($image)?$image:"./content/images/products/unavail.png";
			?>
			<div class='productbox' <?php if($i%$perrow == 2){?>style='margin:18px 18px 0px;'<?php }?>>
			<?php if($inshop){?><div class="inshop"><a href="./shop/item/<?=$trow[7]?>" style="color:#000"><span style="font-size:20px;line-height:20px;"><img src="./content/images/main/view_in_shop.png" alt="&#9672;" /></span></a></div><?php }?>
			<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
			<div style="position:absolute;top:10px;right:5px;z-index:1"><a class="acpbutton" href="./admin/index.php?p=products&amp;showing=prodform&amp;pid=<?=urlencode($pid)?>&amp;owner=<?=urlencode($trow[9])?>" target="_blank">Edit</a></div>
			<?php }?>
				<div class='brand'><img src='./content/images/logos/<?=strtolower(urlencode($brand))?><?php if($premium=="y"){?>_premium<?php }?>.gif' alt='<?=ucwords($brand)?>' /></div>
				<div class='gun'><a href='<?=isset($_GET['simplesearch'])?($trow['layout']==1?$path:"./products/item/".$pid):".".$cururl."/".$pid?><?=isset($_GET['simplesearch'])?"?simplesearch=".urlencode($_GET['simplesearch']):""?><?=isset($_GET['simplesearch'])&&$trow['layout']==0?"&amp;":($trow['layout']==0?"?":"")?><?=$trow['layout']==0?"l=0":""?><?=$trow['layout']==1?"#item".$pid:""?>'<?php if($islocal!=1&&!$inhouse!=1){?> onclick='updateViews(<?=$pid?>)'<?php }?> title="<?=urlencode($brand.": ".$title)?>"><img src='<?=$timage?>' alt='' border='0' /></a></div>
				<div class='prodinfo'><span class='rrp'>RRP<?php if($minprice>0){?> from: &pound;<?=$minprice?><?php }else{?>: TBA<?php }?></span><br /><?=htmlspecialchars($title,ENT_QUOTES,"ISO-8859-1")?></div>
			</div>
			<?php //./shotguns/beretta/over%2B%2526%2Bunder%2Bfield#item193
			if($i%$perrow == 0 || ($i%$perrow != 0 && $i == $withcount)){?><div style='clear:both'></div><?php }
		}
		?><br /><?php
		foreach($products['nopic'] as $aid => $trow)
		{
			list($brand,$minprice,$title,$pid,$premium,$type,$intro)=$trow;
			if($trow['layout']==1)
			{
				if(strlen($trow['path3'])>0){$path.="/".urlencode(urlencode(strtolower($trow['path3'])));}
				if(strlen($trow['path2'])>0){$path.="/".urlencode(urlencode(strtolower($trow['path2'])));}
				if(strlen($trow['path1'])>0){$path.="/".urlencode(urlencode(strtolower($trow['path1'])));}
			}
			$inshop=isset($trow[8])&&$trow[8]==1?1:0;	
			?>
			<div class="pinfo" style="width:100%">
			<?php if($inshop){?><a href="./shop/item/<?=$trow[7]?>" style="color:#000"><span style="font-size:20px;line-height:20px;"><img src="./content/images/main/view_in_shop.png" alt="&#9672;" style="height:20px" /></span></a> <?php }?>
			<a href='<?=isset($_GET['simplesearch'])?($trow['layout']==1?$path:"./products/item/".$pid):".".$cururl."/".$pid?><?=isset($_GET['simplesearch'])?"?simplesearch=".urlencode($_GET['simplesearch']):""?><?=isset($_GET['simplesearch'])&&$trow['layout']==0?"&amp;":($trow['layout']==0?"?":"")?><?=$trow['layout']==0?"l=0":""?><?=$trow['layout']==1?"#item".$pid:""?>' class="titlesbold" title="<?=urlencode($brand.": ".$title)?>"<?php if($islocal!=1&&!$inhouse!=1){?> onclick='updateViews(<?=$pid?>)'<?php }?>><?=htmlspecialchars($title,ENT_QUOTES,"ISO-8859-1")?></a>
				<span class="pprice" style="font-size:11px">From &#163;<?=cart_addvat($minprice)?><?php if($prod['salediscount']!=0){?> <span style="text-decoration:line-through">RRP: &#163;<?=cart_addvat($minprice,1)?></span><?php }?></span>
				<?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?>
			<a class="acpbutton" href="./admin/index.php?p=products&amp;showing=prodform&amp;pid=<?=urlencode($pid)?>&amp;owner=<?=urlencode($trow[9])?>" target="_blank">Edit</a>
			<?php }?>
			</div>
			<?php
		}
	}
}
function sql_error($string,$query="")
{
	echo $string;
}
?>