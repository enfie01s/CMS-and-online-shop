<?php
$get_arr=isset($_GET)?mysql_real_extracted($_GET):array();
$post_arr=isset($_POST)?mysql_real_extracted($_POST):array();
$action=isset($post_arr['action'])?$post_arr['action']:(isset($get_arr['action'])?$get_arr['action']:"");
$showing=isset($get_arr['showing'])?$get_arr['showing']:"list";

switch($action)
{
	case "updatecat":
		$error="";
		$error.=finderrors($post_arr['required'],$post_arr);
		$description=str_replace("<br>","<br />",addslashes($post_arr['description']));
		$subdesc=str_replace("<br>","<br />",$post_arr['subdesc']);
		if(strlen($error)<1){
			### images ###
			foreach($_FILES['files']['name'] as $size => $fname)
			{
				if(strlen($fname)>0)
				{
					$path="../content/images/categories/";
					$newname=$cid."_".$size;
					$ext=strtolower(getExtension(basename($fname)));
					$findcur=glob($path.$newname."*.".$ext);//see if there are files the same/numbered
					$findcurnum=count($findcur);
					if($findcurnum>0){$newname.="_".($findcurnum+1);}//if there are same images add a number
					$uploadedfile=uploadfile($path,$fname,$_FILES['files']['tmp_name'][$size],$newname,'800',$imgfiletypes);
					if(stripos($uploadedfile,"Error")!==false)
					{$error.=$uploadedfile."<br />";}else{$post_arr[$size]=$newname.".".$ext;}
				}				
			}
			##
			sql_query("UPDATE gmk_categories SET `title`=?,`description`=?,`subdesc`=?,`ctype`=?,`visible`=?,`collapsed`=?,`layout`=?,`url`=?,`header`=?,`logo`=?,`col1`=?,`col2`=?,`imgshift`=? WHERE `cid`='".intval($cid)."'",$db1,array($post_arr['title'],$description,$subdesc,$post_arr['ctype'],$post_arr['visible'],$post_arr['collapsed'],$post_arr['layout'],$post_arr['url'],$post_arr['header'],$post_arr['logo'],$post_arr['col1'],$post_arr['col2'],$post_arr['imgshift']));
			sql_query("UPDATE fusion SET `ownerId`='".$post_arr['parent']."' WHERE `itemId`='".intval($cid)."' AND `itemType`='category'",$con1);
			?><!--<div class="notice">Successfully updated</div>--><?php 
		}else{?><div class="notice">ERROR<br /><?=$error?></div><?php }
		cart_sql_cat("update",$post_arr['showincart'],$cid);
		break;
	case "addcat":
		$error="";
		$error.=finderrors($post_arr['required'],$post_arr);
		$description=str_replace("<br>","<br />",addslashes($post_arr['description']));
		if(strlen($error)<1){
			sql_query("INSERT INTO gmk_categories (`title`,`description`,`ctype`,`visible`,`url`,`collapsed`,`layout`,`subdesc`,`header`,`logo`,`col1`,`col2`,`imgshift`)VALUES('".$post_arr['title']."','".$description."','".$post_arr['ctype']."','".$post_arr['visible']."','".$post_arr['url']."','".$post_arr['collapsed']."','".$post_arr['layout']."','".$post_arr['subdesc']."','".$post_arr['header']."','".$post_arr['logo']."','".$post_arr['col1']."','".$post_arr['col2']."','".$post_arr['imgshift']."')",$con1);
			if($debugmode==0){$cid=$db1->lastInsertId();}
			### images ###
			$doimgs=0;
			foreach($_FILES['files']['name'] as $size => $fname)
			{
				if(strlen($fname)>0)
				{
					$path="../content/images/categories/";
					$newname=$cid."_".$size;
					$ext=strtolower(getExtension(basename($fname)));
					$findcur=glob($path.$newname."*.".$ext);//see if there are files the same/numbered
					$findcurnum=count($findcur);
					if($findcurnum>0){$newname.="_".($findcurnum+1);}//if there are same images add a number
					$uploadedfile=uploadfile($path,$fname,$_FILES['files']['tmp_name'][$size],$newname,'800',$imgfiletypes);
					if(stripos($uploadedfile,"Error")!==false)
					{$error.=$uploadedfile."<br />";}else{$post_arr[$size]=$newname.".".$ext;$doimgs=1;}
				}				
			}
			if($doimgs){sql_query("UPDATE gmk_categories SET `header`='".$post_arr['header']."',`logo`='".$post_arr['logo']."' WHERE `cid`='".intval($cid)."'",$con1);}
			##
			sql_query("INSERT INTO fusion (`ownerId`,`itemId`,`sorting`,`itemType`,`ownerType`)VALUES('".intval($owner)."','".intval($cid)."','1','category','category')",$con1);
			?><!--<div class="notice">Successfully added the new category</div>--><?php 
			cart_sql_cat("add",$post_arr,$cid);
		}else{?><div class="notice">ERROR<br /><?=$error?></div><?php }
		
		break;
	case "sortingcat":
		if(isset($post_arr['sorting']))
		{
			foreach($post_arr['sorting'] as $itemid => $sort)
			{
				sql_query("UPDATE fusion SET `sorting`='".intval($sort)."' WHERE `itemId`='$itemid' AND `itemType`='category' AND `ownerType`='category'",$con1);
				sql_query("UPDATE gmk_categories SET `displayorder`='$sort',`visible`='".$post_arr['visible'][$itemid]."' WHERE `cid`='".intval($itemid)."'",$con1);
				cart_sql_cat("update",$post_arr['showincart'][$itemid],$itemid);
			}
		}
		break;
	case "deletecat":
		$q=$db1->prepare("SELECT (SELECT count(*) FROM gmk_categories as t2 WHERE (t2.`header`=t1.`header` OR t2.`logo`=t1.`header`) AND t2.`cid`!=t1.`cid`),(SELECT count(*) FROM gmk_categories as t3 WHERE (t3.`header`=t1.`logo` OR t3.`logo`=t1.`logo`) AND t3.`cid`!=t1.`cid`),`header`,`logo` FROM gmk_categories as t1 WHERE `cid`=?");
		$q->execute(array($cid));
		list($headused,$logoused,$headname,$logoname)=$q->fetch();
		sql_query("DELETE FROM gmk_categories WHERE `cid`='".intval($cid)."'",$con1);
		sql_query("DELETE FROM fusion WHERE (`itemId`='".intval($cid)."' AND `itemType`='category') OR (`ownerId`='".intval($cid)."' AND `ownerType`='category')",$con1);
		if($debugmode==0){
			@unlink($prefixpath.$introimgdir.$cid."_mens.jpg");
			@unlink($prefixpath.$introimgdir.$cid."_womens.jpg");
			@unlink($prefixpath.$introimgdir.$cid.".jpg");
			if($headused<1){@unlink("../content/images/categories/".$headname);}//delete header image if not used by other categories
			if($logoused<1){@unlink("../content/images/categories/".$logoname);}//delete logo image if not used by other categories
		}
		cart_sql_cat("delete",$post_arr,$cid);
		break;
	case "deletecatimg":
		if(isset($_GET['deleteimg'])){
			if($debugmode==0){@unlink($prefixpath.$introimgdir.$_GET['deleteimg']);}
			else{echo "Delete ".$prefixpath.$introimgdir.$_GET['deleteimg'];}
		}
		break;
	case "updateprod":
		$error="";
		$error.=finderrors($post_arr['required'],$post_arr);
		$description=str_replace(array("<br>","\r\n"),array("<br />",""),$post_arr['description']);
		
		if($debugmode==0){
			foreach($_FILES['files']['name'] as $size => $fname)
			{
				if(strlen($fname)>0)
				{
					$path=$size=="thumbnail"?$prefixpath.$images_arr['product']['path']:$prefixpath.$images_arr['product']['path'].$size."/";
					$uploadedfile=uploadfile($path,$fname,$_FILES['files']['tmp_name'][$size],$pid,'800',$imgfiletypes);
					if($uploadedfile!=$path.$pid.".jpg"&&$uploadedfile!=$path.$pid.".png"&&$uploadedfile!=$path.$pid.".gif")
					{$error.=$uploadedfile."<br />";}
				}				
			}
			if(strlen($_FILES['flash']['name'])>0)
			{ 
				$path=$prefixpath.$images_arr['product']['path']."flv/";
				$uploadedfile=uploadfile($path,$_FILES['flash']['name'],$_FILES['flash']['tmp_name'],$pid,'8000',$imgfiletypes);
				if($uploadedfile!=$path.$pid.".flv")
					{$error.=$uploadedfile."<br />";}
			}
			if(is_array($post_arr['parent'])&&strlen($owner)<1){$owner=$post_arr['parent'][0];}
			if(strlen($error)>0)
			{?><div class="notice">ERROR<br /><?=$error?></div><?php }
			else{
				$imagename=$_FILES['files']['error']['big']==0?$pid.".".pathinfo($_FILES['files']['name']['big'], PATHINFO_EXTENSION):"";
				$binds=array();
				$binds[]=$post_arr['prod_title'];
				$binds[]=$post_arr['brand'];
				$binds[]=$description;
				if(strlen($imagename)>0){$binds[]=$imagename;}
				$binds[]=$post_arr['type'];
				$binds[]=$post_arr['type2'];
				$binds[]=$post_arr['lhimg'];
				$binds[]=$post_arr['showasnew'];
				$binds[]=$post_arr['displayed'];
				$binds[]=$post_arr['premium'];
				$binds[]=$post_arr['LH'];
			sql_query("UPDATE gmk_products SET `prod_title`=?,`bid`=?,`description`=?".(strlen($imagename)>0?",`bigimage`=?":"").",`type`=?,`type2`=?,`lhimg`=?,`showasnew`=?,`displayed`=?,`premium`=?,`LH`=? WHERE `pid`='".intval($pid)."'",$con1,$binds);
			$newown=editparentcats($post_arr['parent'],$post_arr['displayed'],$pid);
			$owner=is_array($post_arr['parent'])?$post_arr['parent'][0]:$owner;
			
			?><!--<div class="notice">Successfully updated</div>--><?php }
		}
		cart_sql_prod("update",$post_arr,$pid);
		break;
	case "addprod":
		$error="";
		$error.=finderrors($post_arr['required'],$post_arr);
		
		if($debugmode==0){
			if(strlen($error)<1)
			{
				$imagename=$_FILES['files']['error']['big']==0&&strlen($_FILES['files']['name']['big'])>0?$pid.".".pathinfo($_FILES['files']['name']['big'], PATHINFO_EXTENSION):"";
				$binds=array();
				$binds[]=$post_arr['prod_title'];
				$binds[]=$post_arr['description'];
				if(strlen($imagename)>0){$binds[]=$imagename;}
				$binds[]=$post_arr['type'];
				$binds[]=$post_arr['type2'];
				$binds[]=$post_arr['lhimg'];
				$binds[]=$post_arr['brand'];
				$binds[]=$post_arr['showasnew'];
				$binds[]=$post_arr['displayed'];
				$binds[]=$post_arr['premium'];
				$binds[]=$post_arr['LH'];
				sql_query("INSERT INTO gmk_products (`prod_title`,`description`".(strlen($imagename)>0?",`bigimage`":"").",`type`,`type2`,`lhimg`,`bid`,`showasnew`,`displayed`,`premium`,`LH`)VALUES(?,?".(strlen($imagename)>0?",?":"").",?,?,?,?,?,?,?,?)",$con1,$binds);
				$pid=$db1->lastInsertID();
				$formaction.="&amp;pid=$pid";
			}
			foreach($_FILES['files']['name'] as $size => $fname)
			{
				if(strlen($fname)>0)
				{
					$path=$size=="thumbnail"?$prefixpath.$images_arr['product']['path']:$prefixpath.$images_arr['product']['path'].$size."/";
					$uploadedfile=uploadfile($path,$fname,$_FILES['files']['tmp_name'][$size],$pid,'800',$imgfiletypes);
					if($uploadedfile!=$path.$pid.".jpg"&&$uploadedfile!=$path.$pid.".png"&&$uploadedfile!=$path.$pid.".gif")
					{$error.=$uploadedfile."<br />";}
				}				
			}
			if(strlen($_FILES['flash']['name'])>0)
			{ 
				$path=$prefixpath.$images_arr['product']['path']."flv/";
				$uploadedfile=uploadfile($path,$_FILES['flash']['name'],$_FILES['flash']['tmp_name'],$pid,'8000',$imgfiletypes);
				if($uploadedfile!=$path.$pid.".flv")
					{$error.=$uploadedfile."<br />";}
			}
			if(strlen($error)>0)
			{?><div class="notice">ERROR<br /><?=$error?></div><?php }
			else{
					foreach($post_arr['parent'] as $parent){
					sql_query("INSERT INTO fusion (`ownerId`,`itemId`,`sorting`,`itemType`,`ownerType`)VALUES('".$parent."','".intval($pid)."','1','product','category')",$con1);
				}
				?><div class="notice">Successfully added the new product</div><?php 
				cart_sql_prod("add",$post_arr,$pid);
			}
		}
		break;
	case "displayedprod":
		foreach($post_arr['displayed'] as $ppid => $val)
		{
			sql_query("UPDATE gmk_products SET `showasnew`='".$post_arr['showasnew'][$ppid]."', `displayed`='$val' WHERE `pid`='".intval($ppid)."'",$con1);
			cart_sql_allowpurch($post_arr['allowpurchase'][$ppid],$ppid);
		}
		foreach($post_arr['sorting'] as $fid => $val)
		{
			cart_query("UPDATE fusion SET `sorting`=? WHERE `fusionId`=?",array($val,$fid));
		}
		break;
	case "deleteprod":
		if($pid>0)
		{
			if(strlen($owner)<1)
			{
				sql_query("DELETE FROM fusion WHERE `itemId`='".intval($pid)."' AND `itemType`='product'",$con1);//delete fusion
				//delete images
				if($debugmode==0){
					foreach($images_arr['product']['images'] as $prodimg => $size)
					{
						if($prodimg=="thumbnail"){@unlink($prefixpath.$images_arr['product']['path'].$pid.".png");}
						else{
							$pimgs=glob($prefixpath.$images_arr['product']['path'].$prodimg."/".$pid.".*");
							foreach($pimgs as $pimg){@unlink($pimg);}
						}
						
					}
					@unlink($prefixpath.$images_arr['product']['path']."flv/".$pid.".flv");
				}
				sql_query("DELETE FROM gmk_products WHERE `pid`='".intval($pid)."'",$con1);//delete prod
			}
			else
			{
				sql_query("DELETE FROM fusion WHERE `itemId`='".intval($pid)."' AND `ownerId`='$owner' AND `itemType`='product'",$con1);//remove product from category
			}
			cart_sql_prod("delete",$post_arr,$pid);
		} 
		break;
	case "deleteprodimg":
		if(isset($_GET['deleteimg'])){
			$todelete=$get_arr['deleteimg']=="thumbnail"?$prefixpath.$images_arr['product']['path'].$pid.".png":$prefixpath.$images_arr['product']['path'].$get_arr['deleteimg']."/".$_GET['bigname'];
			if($debugmode==0){@unlink($todelete);}
			else{echo "Delete ".$todelete;}
		}
		break;
	case "deleteflash":
			if($debugmode==0){@unlink($prefixpath.$images_arr['product']['path']."flv/".$pid.".flv");}
			else{echo "Delete ".$prefixpath.$images_arr['product']['path']."flv/".$pid.".flv";}
		break;
	case "addassoc":
		if(isset($post_arr['item']))
		{
			foreach($post_arr['item'] as $item)
			{
				sql_query("INSERT INTO fusion(`ownerId`,`itemId`,`sorting`,`itemType`,`ownerType`)VALUES('$owner','$item','1','product','product')",$con1);
			}
		}
		break;
	case "deleteassoc":
		sql_query("DELETE FROM fusion WHERE `fusionId`='$get_arr[rem]'",$con1);
		break;
}
if($showing=="catform"||$showing=="prodform")
{
	?>
	<!-- Load TinyMCE -->
	<script type="text/javascript" src="tinymce/jquery.tinymce.js"></script>
	<script type="text/javascript">
		$().ready(function() {
			$('textarea.tinymce').tinymce({
				// Location of TinyMCE script
				script_url : 'tinymce/tiny_mce.js',
	
				// General options
				theme : "advanced",
				skin : "o2k7",
				skin_variant : "silver",
				plugins : "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,advlist",
	
				// Theme options
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,forecolor,|,bullist,numlist,|,outdent,indent",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,replace,|,link,unlink,charmap,iespell,advhr,|,tablecontrols,|,visualaid,cleanup,|,code,preview,fullscreen",
				
				theme_advanced_buttons3:"",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "none",
				theme_advanced_resizing : true,
				
				//document_base_url : "http://www.lechameau.co.uk/",
	
				// Example content CSS (should be your site CSS)
				content_css : "../content/stylesheets/style.css",
	
				// Drop lists for link/image/media/template dialogs
				template_external_list_url : "lists/template_list.js",
				external_link_list_url : "lists/link_list.js",
				external_image_list_url : "",
				media_external_list_url : "lists/media_list.js",
	
				// Replace values for the template plugin
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				}
			});
		});
	</script>
	<!-- /TinyMCE -->
	<?php
}
switch($showing)
{
	case "catform":
		if(strlen($cid)>0&&$cid>0)
		{
			if(!isset($post_arr['action']))
			{
				/*$q=ysql_query("SELECT * FROM fusion as f JOIN ".CTABLE." as c ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' WHERE c.`".CFIELDID."`='".intval($cid)."'",$con1);
				$r=mysql_fetch_assoc($q);*/
				$q=$db1->prepare("SELECT * FROM fusion as f JOIN ".CTABLE." as c ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' WHERE c.`".CFIELDID."`=?");
				$q->execute(array(intval($cid)));
				$r=$q->fetch();
				extract($r);$parentcat=$ownerId;
			}
			else{$r=$post_arr;extract($r);$parentcat=$post_arr['parent'];}
			
			?><div id="bread"><a href="index.php">Home</a> <?=SEP?> <?php bread($parentcat,'category','Editing category: '.ucwords($title));?></div><?php
			$nonselectable=array($cid);
		}
		else
		{
			if(!isset($post_arr['action']))
			{
				$title="";
				$description="";
				$visible=0;
			}
			else
			{
				extract($post_arr);
			}
			?><div id="bread"><a href="index.php">Home</a> <?=SEP?> <?php bread($owner,'category','Adding new category');?></div><?php
			$parentcat=$owner;
			$nonselectable=array();
		}		
		$catimg=array();
		$catimg['header']="../content/images/categories/".$header;
		$catimg['logo']="../content/images/categories/".$logo;
		?>
		<script type="text/javascript" src="functions.js"></script>
		<p class="submittop"><a class="button" href="http://www.photoshoponlinefree.com/" target="_blank">Photo Editor</a></p>
		<form action="<?=$formaction?>" method="post" id="editform" name="editform" onsubmit="return checkForm('editform');" enctype="multipart/form-data">
		<input type="hidden" name="required" value="title:Title" />
		<input type="hidden" name="action" value="<?=strlen($cid)>0&&$cid>0?"updatecat":"addcat"?>" />
		<input type="hidden" name="showing" value="catform" />
		<table>
			<tr class="head">
				<td colspan="2"><div class="titles">Category Form</div></td>
			</tr>
			<tr>
				<td class="left_light">Title</td>
				<td class="right_light"><input type="text" name="title" id="title" value="<?=htmlspecialchars($title,ENT_QUOTES)?>" class="input_text" /></td>
			</tr>
			<tr>
				<td class="left_dark">Parent Category</td>
				<td class="right_dark">
				<select name="parent">
					<option value="0" <?php if(0===$parentcat){?>selected="selected"<?php }?>>Shop Front</option>
					<?php parentsoptions(0,array($parentcat),$nonselectable);?>	
				</select>
				</td>
			</tr>
			<tr>
				<td class="left_light">Category Type</td>
				<td class="right_light">
				<select name="ctype">
				<option value="" <?=isset($ctype)&&strlen($ctype)<1?"selected='selected'":""?>>Select Type...</option>
				<?php foreach($subtypes as $cattype => $prodtypes){if($cattype!="default"){?>
				<option value="<?=$cattype?>" <?=isset($ctype)&&$ctype==$cattype?"selected='selected'":""?>><?=ucwords($cattype)?></option>
				<?php }}?>
				</select>
				</td>
			</tr>
			<tr>
				<td class="left_dark" style="vertical-align:top">Text</td>
				<td class="right_dark"><textarea id="description" name="description" class="tinymce" style="height: 150px; width: 100%;color:#A9AEB7;background:#010C39"><?=stripslashes(str_replace(array("\\r\\n","\\"),array("",""),$description))?></textarea></td>
			</tr>
			<tr>
				<td class="left_light" style="vertical-align:top">Sub Text</td>
				<td class="right_light"><textarea id="subdesc" name="subdesc" class="tinymce" style="height: 150px; width: 100%;color:#A9AEB7;background:#010C39"><?=stripslashes(str_replace(array("\\r\\n","\\"),array("",""),$subdesc))?></textarea></td>
			</tr>
			<?php 
			$catimglist=glob("../content/images/categories/*.*");
			foreach($catimg as $thisitype => $thisipath){$row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>"><?=ucwords($thisitype)?> Image</td>
				<td class="right<?=$row?>">
				<div style="float:left">Choose an existing image:  
				<select name="<?=$thisitype?>"><option value="" style="height:50px;margin:2px;border:1px solid #000;">No Image</option>
				<?php foreach($catimglist as $path){?><option style="background:url(<?=str_replace(" ","%20",$path)?>) left 17px/contain no-repeat;height:50px;margin:2px;border:1px solid #000;" value="<?=basename($path)?>" <?=$r[$thisitype]==basename($path)?"selected='selected'":""?>><?=basename($path)?></option><?php }?>
				</select>
				</div>
				<div style="float:right">
				Or upload new: <input type="file" name="files[<?=$thisitype?>]" class="input_file" />			
				</div><div class="clear"></div>
				</td>
			</tr>
			<?php }$row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>">URL (to replace category link)</td>
				<td class="right<?=$row?>"><input type="text" name="url" id="url" value="<?=isset($url)?stripslashes($url):""?>" class="input_text" style="width:300px" /></td>
			</tr>
			<?php $row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>">Colours</td>
				<td class="right<?=$row?>"><label for="col1">Colour 1 (title bars) hex#</label><input type="text" name="col1" id="col1" class="color" maxlength="6" value="<?=isset($col1)?stripslashes($col1):"#BCBDC0"?>" class="input_text" style="width:50px;text-transform:uppercase;border-width:1px;" /> <label for="col2">Colour 2 (variants code) hex#</label><input type="text" name="col2" id="col2" class="color" maxlength="6" value="<?=isset($col2)?stripslashes($col2):"#BCBDC0"?>" class="input_text" style="width:50px;text-transform:uppercase;border-width:1px;" /></td>
			</tr>
			<?php $row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>" style="vertical-align:top">Collapsed</td>
				<td class="right<?=$row?>"><label for="collapsed1" class="yes"><input type="radio" name="collapsed" id="collapsed1" value="1" <?=isset($collapsed)&&$collapsed==1?"checked='checked'":""?> /> On</label><label for="collapsed0" class="no"><input type="radio" name="collapsed" id="collapsed0" value="0" <?=isset($collapsed)&&$collapsed==1?"":"checked='checked'"?> /> Off</label></td>
			</tr>
			<?php $row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>" style="vertical-align:top"><?=MAIN_ONOFF?></td>
				<td class="right<?=$row?>"><label for="visible1" class="yes"><input type="radio" name="visible" id="visible1" value="1" <?=isset($visible)&&$visible==1?"checked='checked'":""?> /> On</label><label for="visible0" class="no"><input type="radio" name="visible" id="visible0" value="0" <?=isset($visible)&&$visible==0?"checked='checked'":""?> /> Off</label></td>
			</tr>
			<?php $row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>"><?=SHOP_ONOFF?></td>
				<td class="right<?=$row?>"><?php cart_catform_opt($cid)?></td>
			</tr>
			<?php $row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>" style="vertical-align:top">New Layout</td>
				<td class="right<?=$row?>"><label for="layout1" class="yes"><input type="radio" name="layout" id="layout1" value="1" <?=isset($layout)&&$layout==1?"checked='checked'":""?> /> On</label><label for="layout0" class="no"><input type="radio" name="layout" id="layout0" value="0" <?=!isset($layout)||$layout==0?"checked='checked'":""?> /> Off</label></td>
			</tr>
			<?php $row=!isset($row)||$row=="_light"?"_dark":"_light";?>
			<tr>
				<td class="left<?=$row?>" style="vertical-align:top">Shift UP Product Images</td>
				<td class="right<?=$row?>"><label for="imgshift1" class="yes"><input type="radio" name="imgshift" id="imgshift1" value="1" <?=isset($imgshift)&&$imgshift==1?"checked='checked'":""?> /> Yes</label><label for="imgshift0" class="no"><input type="radio" name="imgshift" id="imgshift0" value="0" <?=!isset($imgshift)||$imgshift==0?"checked='checked'":""?> /> No</label></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" value="Submit" style="border:0" /></p>
		</form>
		<?php
		break;
	case "prodform":
		//strlen($pid) - add or update
		//strlen($owner) - orphan or not (never do an add to orphans)
	
		if(strlen($owner)<1&&strlen($pid)>0)
		{
			/*$q=ysql_query("SELECT * FROM gmk_products WHERE `pid`='".intval($pid)."'",$con1);
			$r=mysql_fetch_assoc($q);*/
			$q=$db1->prepare("SELECT * FROM gmk_products WHERE `pid`=?");
			$q->execute(array(intval($pid)));
			$r=$q->fetch();
			extract($r);
			$curpid=$pid;
			?><div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="index.php?p=products">Products</a> <?=SEP?> <a href="index.php?p=products&amp;owner=">Orphan Products</a> <?=SEP?> Editing product: <?=$prod_title?></div><?php
		}
		else if(strlen($pid)>0)
		{
			/*$q=ysql_query("SELECT *,p.`description` as description FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') LEFT JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) WHERE `pid`='".intval($pid)."' AND `ownerId`='".intval($owner)."'",$con1);
			$r=mysql_fetch_assoc($q);*/
			$q=$db1->prepare("SELECT *,p.`description` as description FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') LEFT JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) WHERE `pid`=? AND `ownerId`=?");
			$q->execute(array($pid,$owner));
			$r=$q->fetch();
			extract($r);
			$curpid=$pid;
			?><div id="bread"><a href="index.php">Home</a> <?=SEP?> <?php bread($owner,"category","Editing product: ".str_replace(array("™","®"),array("&trade;","&reg;"),$prod_title));?></div><?php 
		}
		else
		{
			/*$q=ysql_query("SELECT * FROM gmk_categories WHERE `cid`='".intval($owner)."'",$con1);
			$r=mysql_fetch_assoc($q);*/
			$q=$db1->prepare("SELECT * FROM gmk_categories WHERE `cid`=?");
			$q->execute(array(intval($owner)));
			$r=$q->fetch();
			if(is_array($r)){extract($r);}
			?><div id="bread"><?php bread($owner,"category","Adding new product");?></div><?php
		}
		$p=getparentlayout($owner);
		$sitelink=$p[1][1]==1?$previewpath."#item".$pid:"../products/item/".$pid;
		?>
		<script type="text/javascript" src="functions.js"></script>
		<p class="submittop"><a class="button" href="http://www.photoshoponlinefree.com/" target="_blank">Photo Editor</a></p>
		<form action="<?=$formaction?>" method="post" id="editform" name="editform" onsubmit="return checkForm('editform');" enctype="multipart/form-data">
		<input type="hidden" name="required" value="prod_title:Title,description:Description,brand:Brand" />
		<input type="hidden" name="showing" value="prodform" />
		<input type="hidden" name="action" value="<?=strlen($pid)>0?"updateprod":"addprod"?>" />
		<table>
			<tr class="head">
				<td colspan="2">Product Form<?=strlen($pid)>0?" [<a href='../$sitelink' target='_blank'>View on site</a>]":""?></td>
			</tr>
			<tr>
				<td class="left_light" id="prod_title_title">Title</td>
				<td class="right_light"><input type="text" name="prod_title" id="prod_title" value="<?=htmlspecialchars($prod_title,ENT_QUOTES)?>" class="input_text" /></td>
			</tr>
			<tr>
				<td class="left_dark">Type</td>
				<td class="right_dark"><?php if(isset($ctype)&&isset($subtypes[$ctype])){foreach($subtypes[$ctype] as $typeid => $typename){?><input type="radio" name="type" value="<?=$typename?>" id="type<?=$typename?>" <?=isset($type)&&$type==$typename?"checked='checked'":""?> style="vertical-align:middle" /><label for="type<?=$typename?>"> <?=ucwords($typename)?></label> <?php }}?><input type="radio" name="type" value="" id="type0" <?=!isset($type)||strlen($type)<1||(is_array($subtypes[$ctype])&&!in_array($type,$subtypes[$ctype]))?"checked='checked'":""?> style="vertical-align:middle" /><label for="type0"> Universal</label></td>
			</tr>
			<tr>
				<td class="left_light">Sub Type</td>
				<td class="right_light"><input type="text" name="type2" id="type2" value="<?=isset($type2)?htmlspecialchars($type2,ENT_QUOTES,"ISO-8859-1"):""?>" /></td>
			</tr>
			<tr>
				<td class="left_dark" id="prod_title_brand">Brand</td>
				<td class="right_dark">
				<select name="brand">
				<option value="">Select Brand</option>
				<?php
				/*$brands=ysql_query("SELECT * FROM gmkbrands ORDER BY `brand`",$con1);
				while($brand=mysql_fetch_assoc($brands))*/
				$brands=$db1->query("SELECT * FROM gmkbrands ORDER BY `brand`");
				while($brand=$brands->fetch())
				{
					?><option value="<?=$brand['id']?>" <?php if(isset($bid)&&$bid==$brand['id']){?>selected="selected"<?php }?>><?=ucwords($brand['brand'])?></option><?php
				}
				?>
				</select>
				</td>
			</tr>
			<tr>
				<td class="left_light" style="vertical-align:top;padding-top:3px;">Parent Categories<br /><dfn>(CTRL + Click)</dfn></td>
				<td class="right_light">
				<?php
				$selected=array();
				if(strlen($pid)>0)
				{
					$curcats=array();
					$on=0;
					$off=0;
					/*$curs=ysql_query("SELECT ownerId FROM fusion WHERE `itemId`='".intval($pid)."' AND `itemType`='product' GROUP BY `ownerId`",$con1);
					while($cur=mysql_fetch_row($curs))*/
					$curs=$db1->prepare("SELECT ownerId FROM fusion WHERE `itemId`=? AND `itemType`='product' GROUP BY `ownerId`");
					$curs->execute(array(intval($pid)));
					while($cur=$curs->fetch())
					{$curcats[]=$cur[0];}
					$selecteds=$curcats;
				}
				else if(isset($post_arr['action'])){$selecteds[]=$post_arr['parent'];}
				else if(!isset($post_arr['action'])){$selecteds[]=$owner;}
				?>
				<select name="parent[]" multiple="multiple" size="5">
				<option value="0" <?php if($owner==0||in_array(0,$selecteds)){?>selected="selected"<?php }?>>Shop Front</option>
				<?php parentsoptions(0,$selecteds,array());?>
				</select>
				</td>
			</tr>
			<tr>
				<td class="left_dark" style="vertical-align:top" id="description_title">Description</td>
				<td class="right_dark"><textarea id="description" name="description" class="tinymce" style="height: 150px; width: 100%;color:#A9AEB7;background:#010C39"><?=isset($description)?$description:""?></textarea></td>
			</tr>
			<?php foreach($images_arr['product']['images'] as $prodimg => $prodsizes){
				$pwh=explode("x",$prodsizes);
				$imagefile=$prodimg=="thumbnail"?"../".$images_arr['product']['path'].$pid.".png":"../".$images_arr['product']['path'].$prodimg."/".$bigimage;
				$rowcss=!isset($rowcss)||$rowcss=="_light"?"_light":"_dark";
				$inote=ucwords($prodimg)=="Thumbnail"?"For old layout with thumbnails":(ucwords($prodimg)=="Feature Icons"?"Icons representing specifications":"");	
				?>
			<tr>
				<td class="left<?=$rowcss?>"><div><?=ucwords($prodimg)?> Image (<?=$prodimg!="big"?"PNG":"JPG/PNG/GIF"?>)</div><?php if(strlen($prodsizes)>0){?><dfn>(W:<?=$pwh[0]?>px, H:<?=$pwh[1]?>px)</dfn><?php }?><?php if(strlen($inote)>0){?><div style="font-size:10px;color:#99332D"><?=$inote?></div><?php }?></td>
				<td class="right<?=$rowcss?>"><div style="float:left"><input type="file" name="files[<?=$prodimg?>]" class="input_file" /></div>
				<div style="float:right">
				
				<?php if(is_file($imagefile)){?>
				<ul class="enlarge">
						<li>
							<img src="img/spacer.gif" style="background:url(<?=$imagefile?>) no-repeat center center" class="thumb" alt="" />
							<span><img src="<?=$imagefile?>" alt="" /><br /><?=ucwords($prodimg)?> Image</span>
							</li>						
					</ul>
				<a href="javascript:decision('Really delete <?=$prodimg?> image?','index.php?p=products&amp;action=deleteprodimg&amp;showing=prodform&amp;pid=<?=$pid?>&amp;owner=<?=$owner?>&amp;curpage=<?=urlencode($prod_title)?>&amp;deleteimg=<?=$prodimg?>&amp;bigname=<?=$bigimage?>')"><img src="img/delete.png" alt="X" /></a></span>
				<?php }else{?>
				No image found.
				<?php }?>
				
				</div><div class="clear"></div></td>
			</tr>
			<?php }
				$rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	?>
			<tr>
				<td class="left<?=$rowcss?>">Video File (.flv)</td>
				<td class="right<?=$rowcss?>">
				<?php $flashfile="../".$images_arr['product']['path']."flv/".$pid.".flv";?>
				<div style="float:left"><input type="file" name="flash" class="input_file" /></div>
				<div style="float:right"><?php if(file_exists($flashfile)){?>[<a href="javascript:decision('Really delete flash video?','index.php?p=products&amp;action=deleteflash&amp;showing=prodform&amp;pid=<?=$pid?>&amp;owner=<?=$owner?>&amp;curpage=<?=urlencode($prod_title)?>')">Delete current file</a>]<?php }else{?>No video found.<?php }?></div><div class="clear"></div>
				</td>
			</tr>
			<?php $rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	?>
			<tr>
				<td class="left<?=$rowcss?>" style="vertical-align:top"> Status</td>
				<td class="right<?=$rowcss?>"><label for="displayed1" class="yes"><input type="radio" name="displayed" id="displayed1" value="1" <?=isset($displayed)&&$displayed==1?"checked='checked'":""?> /> On</label><label for="displayed0" class="no"><input type="radio" name="displayed" id="displayed0" value="0" <?=!isset($displayed)||$displayed==0?"checked='checked'":""?> /> Off</label></td>
			</tr>
			<?php $rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	?>
			<tr>
				<td class="left<?=$rowcss?>" style="vertical-align:top"> Image is Left Hand</td>
				<td class="right<?=$rowcss?>"><label for="lhimg1" class="yes"><input type="radio" name="lhimg" id="lhimg1" value="1" <?=isset($lhimg)&&$lhimg==1?"checked='checked'":""?> /> Yes</label><label for="lhimg0" class="no"><input type="radio" name="lhimg" id="lhimg0" value="0" <?=!isset($lhimg)||$lhimg==0?"checked='checked'":""?> /> No</label></td>
			</tr>
			<?php $rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	?>
			<tr>
				<td class="left<?=$rowcss?>" style="vertical-align:top">Show &#34;NEW&#34; label</td>
				<td class="right<?=$rowcss?>"><label for="showasnew1" class="yes"><input type="radio" name="showasnew" id="showasnew1" value="1" <?=isset($showasnew)&&$showasnew==1?"checked='checked'":""?> /> Yes</label><label for="showasnew0" class="no"><input type="radio" name="showasnew" id="showasnew0" value="0" <?=!isset($showasnew)||$showasnew==0?"checked='checked'":""?> /> No</label></td>
			</tr>
			<?php $rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	?>
			<tr>
				<td class="left<?=$rowcss?>" style="vertical-align:top">Premium</td>
				<td class="right<?=$rowcss?>"><label for="premium1" class="yes"><input type="radio" name="premium" id="premium1" value="y" <?=isset($premium)&&$premium=='y'?"checked='checked'":""?> /> Yes</label><label for="premium0" class="no"><input type="radio" name="premium" id="premium0" value="0" <?=isset($premium)&&$premium=='y'?"":"checked='checked'"?> /> No</label></td>
			</tr>
			<?php $rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	?>
			<tr>
				<td class="left<?=$rowcss?>" style="vertical-align:top">Left Hand Available</td>
				<td class="right<?=$rowcss?>"><label for="LH1" class="yes"><input type="radio" name="LH" id="LH1" value="1" <?=isset($LH)&&$LH==1?"checked='checked'":""?> /> Yes</label><label for="LH0" class="no"><input type="radio" name="LH" id="LH0" value="0" <?=isset($LH)&&$LH==1?"":"checked='checked'"?> /> No</label>
				</td>
			</tr>
			
		</table>
		<?php cart_prodedit($pid);?>
		<p class="submit"><input type="submit" value="Save Changes" style="border:0" /></p>
		</form>
		
		<?php /* ASSOCIATED PRODUCTS */ ?>
		
		<?php if(strlen($pid)>0){?>
			<form action="<?=$formaction?>" method="post">
			<input type="hidden" name="showing" value="prodform" />
			<input type="hidden" name="action" value="updateassoc" />
			<p class="submittop" style="margin-top:30px;"><a href="index.php?p=products&amp;showing=assocform&amp;owner=<?=$curpid?>">Add Suggestion</a></p>
			<table class="linkslist" style="margin-bottom:30px;">
			<tr class="head">
				<td colspan="2"><div class="titles">Suggested Products</div></td>
			</tr>
				<tr class="subhead">
					<td style="width:85%">Name</td>
					<td style="width:15%;text-align:center"></td>
				</tr>
			<?php
			
			/*$q=ysql_query("SELECT * FROM gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` WHERE `ownerId`='".intval($pid)."' AND `itemType`='product' AND `ownerType`='product' ORDER BY `prod_title`",$con1) or die(sql_error("Error"));
			$n=mysql_num_rows($q);*/
			$q=$db1->prepare("SELECT * FROM gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` WHERE `ownerId`=? AND `itemType`='product' AND `ownerType`='product' ORDER BY `prod_title`");
			$q->execute(array(intval($pid)));
			$n=$q->rowCount();
			if($n<1){?>
				<tr class="row_dark"><td colspan="2" style="text-align:center">No Products Found</td></tr>
			<?php }
			//while($r=mysql_fetch_assoc($q))
			while($r=$q->fetch())
			{
				extract($r);
				$delmsg=strlen($owner)>0?"Really remove $prod_title association from this product? (if owned by no other categories, this product will be moved to the orphaned category)":"Really permanently delete $prod_title?";
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink"><a href="index.php?p=products&amp;showing=prodform&amp;pid=<?=$pid?>&amp;owner=<?=$ownerId?>&amp;curpage=<?=urlencode($prod_title)?>"><img src="images/<?=$type?>.png" alt="<?=$type=="womens"?"&#9792;":($type=="mens"?"&#9794;":"")?>" /> <?=$prod_title?></a></td>
					<td style="text-align:right" class="blocklink"><a href="javascript:decision('<?=$delmsg?>','index.php?p=products&amp;action=deleteassoc&amp;showing=prodform&amp;pid=<?=$curpid?>&amp;owner=<?=$owner?>&amp;rem=<?=$fusionId?>')"><img src="img/delete.png" alt="Delete" /></a></td>
				</tr>
				<?php
			}
			?>
			</table>
			</form>
		<?php }
		
		/* LISTED AS SUGGESTION FOR */
		if(isset($curpid))
		{
			/*$suggs=ysql_query("SELECT p.`".PFIELDNAME."` as prod_title,p.`".PFIELDEXTRA."` as type,p.`".PFIELDID."` as pid FROM fusion as f JOIN ".PTABLE." as p ON f.`ownerId`=p.`".PFIELDID."` WHERE f.`itemType`='product' AND f.`ownerType`='product' AND f.`itemId`='".intval($curpid)."'",$con1);
			$suggn=mysql_num_rows($suggs);*/
			$suggs=$db1->prepare("SELECT p.`".PFIELDNAME."` as prod_title,p.`".PFIELDEXTRA."` as type,p.`".PFIELDID."` as pid FROM fusion as f JOIN ".PTABLE." as p ON f.`ownerId`=p.`".PFIELDID."` WHERE f.`itemType`='product' AND f.`ownerType`='product' AND f.`itemId`=?");
			$suggs->execute(array(intval($curpid)));
			$suggn=$suggs->rowCount();
			?>
			<table class="linkslist">
			<tr class="head">
				<td><div class="titles">Parent Products</div></td>
			</tr>
			<?php 
			if($suggn<1)
			{
				?>
				<tr class="row_dark">
					<td style="text-align:center">No Parent Products</td>
				</tr>
				<?php 
			}
			//while($sugg=mysql_fetch_assoc($suggs))
			while($sugg=$suggs->fetch())
			{
				/*$suggowns=ysql_query("SELECT f.`ownerId` FROM fusion as f WHERE `itemId`='$sugg[pid]' AND `itemType`='product' AND `ownerType`='category'",$con1);
				$suggown=mysql_fetch_row($suggowns);*/
				$suggowns=$db1->prepare("SELECT f.`ownerId` FROM fusion as f WHERE `itemId`=? AND `itemType`='product' AND `ownerType`='category'");
				$suggowns->execute(array($sugg['pid']));
				$suggown=$suggowns->fetch();
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink"><a href="index.php?p=products&amp;showing=prodform&amp;pid=<?=$sugg['pid']?>&amp;owner=<?=$suggown[0]?>&amp;curpage=<?=urlencode($sugg['prod_title'])?>"><img src="images/<?=$sugg['type']?>.png" alt="<?=$sugg['type']=="womens"?"&#9792;":($sugg['type']=="mens"?"&#9794;":"")?>" /> <?=$sugg['prod_title']?></a></td>
				</tr>
				<?php
			}
			?></table><?php
		}
		break;
	case "assocform":
		?>
		<script type="text/javascript" src="functions.js"></script>
		<div id="bread"><a href="index.php">Home</a> <?=SEP?> <?php bread($owner,"product","Suggested Products");?></div>
		<table class="linkslist">
			<tr class="head">
				<td colspan="2"><div class="titles">Suggested Products</div></td>
			</tr>
			<?php
			/*$q=ysql_query("SELECT * FROM gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` WHERE `ownerId`='".intval($owner)."' AND `itemType`='product' AND `ownerType`='product' ORDER BY `prod_title`",$con1) or die(sql_error("Error"));
			$n=mysql_num_rows($q);*/
			$q=$db1->prepare("SELECT * FROM gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` WHERE `ownerId`=? AND `itemType`='product' AND `ownerType`='product' ORDER BY `prod_title`");
			$q->execute(array(intval($owner)));
			$n=$q->rowCount();
			if($n<1){?>
				<tr class="row_dark"><td colspan="2" style="text-align:center">No Products Found</td></tr>
			<?php }else{?>
				<tr class="subhead">
					<td style="width:85%">Name</td>
					<td style="width:15%;text-align:center">Remove</td>
				</tr>
			<?php }
			//while($r=mysql_fetch_assoc($q))
			while($r=$q->fetch())
			{
				extract($r);
				$delmsg="Really remove $prod_title association from this product? (if owned by no other categories, this product will be moved to the orphaned category)";
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td><img src="images/<?=$type?>.png" alt="<?=$type=="womens"?"&#9792;":($type=="mens"?"&#9794;":"")?>" /> <?=$prod_title?></td>
					<td style="text-align:center" class="blocklink"><a href="javascript:decision('<?=$delmsg?>','index.php?p=products&amp;action=deleteassoc&amp;showing=assocform&amp;owner=<?=$owner?>&amp;rem=<?=$fusionId?>')">Remove</a></td>
				</tr>
				<?php
			}
			?>
			<tr class="subhead">
				<td colspan="2"><div class="titles">Add to list</div></td>
			</tr>
			<?php if(isset($get_arr['submititem'])){?>
			<tr class="infohead">
				<td colspan="2" style="text-align:center;font-style:normal;font-weight:bold;font-size:12px">You must add at least one item.</td>
			</tr>
			<?php }?>
			<tr class="row_light">
				<td style="width:50%;text-align:center">
				<form action="<?=$formaction?>" method="post">
				<select name="dept" size="10" style="width:300px">
				<option value="0" <?php if($post_arr['dept']==0){?>selected="selected"<?php }?>>Home Page</option>
				<option value="orphaned" <?php if($post_arr['dept']=="orphaned"){?>selected="selected"<?php }?>>All orphan products</option>
				<option value="onlyinprods" <?php if($post_arr['dept']=="onlyinprods"){?>selected="selected"<?php }?>>Products with only product(s) as parent</option>
				<?php
				$par="";
				$curloop=1;
				/*$deptsQ=ysql_query("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cid,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' AND `ownerId` !='0' ORDER BY `ownerId`,c.`".CFIELDID."`",CARTDB);
				$deptsnum=mysql_num_rows($deptsQ);
				while($depts=mysql_fetch_assoc($deptsQ))*/
				$deptsQ=$db1->query("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cid,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' AND `ownerId` !='0' ORDER BY `ownerId`,c.`".CFIELDID."`");
				$deptsnum=$deptsQ->rowCount();
				while($depts=$deptsQ->fetch())
				{
					if($depts['ownerId']!=0){
						$parentsgot=getparents($depts['ownerId'],"/");
						if($par != $parentsgot){if(strlen($par)>0){?></optgroup><?php }?>
						<optgroup label="<?=$parentsgot?>"><?php $par = $parentsgot;} 
					}
					?>
					<option value="<?=$depts['cid']?>" <?php if($post_arr['dept']==$depts['cid']){?>selected="selected"<?php }?>>
					<?=ucwords($depts['title'])?></option><?php
					if($depts['ownerId']!=0&&strlen($par)>0&&$deptsnum==$curloop){?></optgroup><?php }
					$curloop++;
				}
				?>
				</select>
				<div style="text-align:center"><input type="submit" name="submitdept" style="border:0;" value="View Items" /></div>
				</form>
				</td>
				<td style="width:50%;text-align:center">
				<form action="<?=$formaction?>" method="post">
				<input type="hidden" name="action" value="addassoc" />
				<select name="item[]" size="10" style="width:300px" multiple="multiple">
				<?php
				$binds=array();
				if(isset($post_arr['dept'])&&$post_arr['dept']=="orphaned")
				{
					$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND f.`itemType`='product' WHERE fusionId IS NULL AND p.`".PFIELDID."`!=?";
					$binds[]=intval($owner);
				}
				else if(isset($post_arr['dept'])&&$post_arr['dept']=="onlyinprods")
				{
					$ids="";
					/*$notinacatQ=ysql_query("SELECT `itemId` FROM fusion WHERE `itemType`='product' AND `ownerType`='category' GROUP BY `itemId`",CARTDB);
					while($notinacat=mysql_fetch_assoc($notinacatQ))*/
					$notinacatQ=$db1->query("SELECT `itemId` FROM fusion WHERE `itemType`='product' AND `ownerType`='category' GROUP BY `itemId`");
					while($notinacat=$notinacatQ->fetch())
					{
						if($ids!=""){$ids.=",";}$ids.=$notinacat['itemId'];
					}
					$in=bindIns($ids);
					$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(".PFIELDEXTRA.")>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM fusion as f,".PTABLE." as p WHERE f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='product' AND `itemId` NOT IN(".$in[0].") AND `itemId`!=? GROUP BY `itemId`";
					$binds=array_merge($binds,$in[1]);
					$binds[]=intval($owner);
				}
				else
				{
					$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(".PFIELDEXTRA.")>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='category' WHERE `ownerId`=? AND p.`".PFIELDID."`!=? ORDER BY `sorting`";
					$binds[]=$post_arr['dept'];
					$binds[]=intval($owner);
				}
				if(count($binds)>0)
				{
					$itemsQ=$db1->prepare($sqlQ);
					$itemsQ->execute($binds);
				}
				else
				{
					$itemsQ=$db1->query($sqlQ);
				}
				while($items=$itemsQ->fetch())
				{
					?>
					<option value="<?=$items['prod_id']?>"><?=$items['title']?><?php if(strlen(".PFIELDEXTRA.")>0){?> (<?=$items['extra']?>)<?php }?></option>
					<?php
				}
				?>
				</select>
				<div style="text-align:center"><input type="submit" style="border:0;" value="Add to list" /></div>
				</form>
				</td>
			</tr>			
			</table>
		<?php
		break;
	case "list":
		?>
		<div id="bread"><a href="index.php">Home</a> <?=SEP?> 
			<?php if(strlen($owner)<1){?>
			<a href="index.php?p=products">Products</a> <?=SEP?> Orphan Products
			<?php }else{
			 bread($owner);
			}?>
		</div>
		
		<?php /* CATEGORIES */ ?>
		
			<script type="text/javascript" src="functions.js"></script>
			<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
			<script type="text/javascript">boxarr['showincart']=[];boxarr['visible']=[];</script>
			<form action="<?=$formaction?>" method="post" id='cats'>
			<input type="hidden" name="action" value="sortingcat" />
			<?php if(strlen($owner)>0||!isset($_GET['owner'])){ if($owner>0||!isset($_GET['owner'])){?><p class="submittop"><a href="index.php?p=products&amp;showing=catform&amp;owner=<?=$owner?>">Add Category</a></p><?php }?>
			<table class="linkslist">
			<tr class="head">
				<td colspan="<?=strlen($owner)>0?"5":"4"?>">Categories</td>
			</tr>
			<tr class="subhead">
				<td style="width:80%"><div class="titles">Category Name</div></td>
				<td style="white-space:nowrap;width:5%;"><?=MAIN_ONOFF?>&nbsp;<input type="checkbox" onclick="cart_multiCheck(this.form,'visible',this)" /></td>
				<td style="white-space:nowrap;width:5%;"><?=SHOP_ONOFF?> <input type="checkbox" onclick="cart_multiCheck(this.form,'showincart',this)" /></td>
				<?php if(strlen($owner)>0){?><td style="width:5%;text-align:center">Sorting</td><?php }?>
				<td style="width:5%;text-align:center"></td>
			</tr>
			<?php 		
			/*
			$joining=strlen($owner)>0?"JOIN fusion as f ON c.`cid`=f.`itemId` WHERE `ownerId`='$owner' AND `itemType`='category'":"LEFT JOIN fusion as f ON c.`cid`=f.`itemId` AND `itemType`='category' WHERE `fusionId` is NULL";$q=ysql_query("SELECT * FROM gmk_categories as c $joining ORDER BY `sorting`",$con1) or die(sql_error("Error"));
			$n=mysql_num_rows($q);*/
			$binds=array();
			if(strlen($owner)>0)
			{$joining="JOIN fusion as f ON c.`cid`=f.`itemId` WHERE `ownerId`=? AND `itemType`='category'";$binds[]=$owner;}
			else{$joining="LEFT JOIN fusion as f ON c.`cid`=f.`itemId` AND `itemType`='category' WHERE `fusionId` is NULL";}
			if(count($binds)>0)
			{
				$q=$db1->prepare("SELECT * FROM gmk_categories as c $joining ORDER BY `sorting`");
				$q->execute($binds);
			}
			else
			{
				$q=$db1->query("SELECT * FROM gmk_categories as c $joining ORDER BY `sorting`");
			}
			$n=$q->rowCount();
			if($n<1){?><tr class="row_dark"><td colspan="<?=$owner>0?"5":"4"?>" style="text-align:center">No Categories Found</td></tr><?php }
			//while($r=mysql_fetch_assoc($q))
			while($r=$q->fetch())
			{
				extract($r);
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink"><a href="index.php?p=products&amp;owner=<?=$itemId?>&amp;curpage=<?=urlencode($title)?>"><?=ucwords($title)?></a></td>
					<td style="text-align:right"><input type="hidden" name="visible[<?=$itemId?>]" value="0" /><input type="checkbox" name="visible[<?=$itemId?>]" id="visible[<?=$itemId?>]" <?=$visible==1?"checked='checked'":""?> value="1" /></td>
					<td style="text-align:right"><?php cart_catform_opt($itemId,1);?></td>	
					<?php if(strlen($owner)>0){?>
					<td style="text-align:center"><input type="text" name="sorting[<?=$itemId?>]" value="<?=$sorting?>" class="input_text_small" style="text-align:center" /></td>
					<?php }?>				
					<td class="blocklink" style="text-align:center;white-space:nowrap;">
						<a href="index.php?p=products&amp;showing=catform&amp;cid=<?=$cid?>&amp;curpage=<?=urlencode($title)?>"><img src="img/edit.png" alt="Edit" /></a>
						<?php if($owner>0||strlen($owner)<1){?>
						<a href="#" onclick="javascript:decision('Are you sure you wish to delete this category: <?=$title?> (child products will not be deleted)?','index.php?p=products&amp;action=deletecat&amp;cid=<?=$cid?>&amp;owner=<?=$owner?>')"><img src="img/delete.png" alt="X" /></a>
						<?php }?>
					</td>
				</tr>
				<script type="text/javascript">boxarr["visible"].push("visible[<?=$itemId?>]");</script>
				<?php
			}
			if($owner===0)
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink" colspan="5"><a href="index.php?p=products&amp;owner=">Orphaned Categories &amp; Products</a></td>
				</tr>
				<?php
			}
			?>
			</table>
			
			<p class="submit" style="margin-bottom:30px"><?php if($n>0){?><input type="submit" value="Update Sorting/Status" style="border:0" /><?php }}?></p>
			</form>
		
		<?php /* PRODUCTS */ ?>
		<?php if(1){
			/*$joining1=strlen($owner)>0?"":" LEFT";
			$joining2=strlen($owner)>0?" AND `ownerId`='".intval($owner)."'":"";
			$joining3=strlen($owner)>0?"":" AND `fusionId` IS NULL";
			$string="SELECT `bid`,`brand`,count(p.`pid`) as num FROM (gmk_products as p".$joining1." JOIN fusion as f ON p.`pid`=f.`itemId` AND `itemType`='product' AND `ownerType`='category'".$joining2.") LEFT JOIN gmkbrands as b ON p.`bid`=b.`id` WHERE p.`pid` > 0".$joining3." GROUP BY `bid` ORDER BY `brand`";
			$q=ysql_query($string,$con1) or die(sql_error("Error",$string));
			$r=mysql_fetch_assoc($q);
			$n=mysql_num_rows($q);*/
			$binds=array();//DO NOT RESET. a query lower down is using is with more joining variables
			if(strlen($owner)>0){$binds[]=intval($owner);}
			$joining1=strlen($owner)>0?"":" LEFT";
			$joining2=strlen($owner)>0?" AND `ownerId`=?":"";
			$joining3=strlen($owner)>0?"":" AND `fusionId` IS NULL";
			$string="SELECT `bid`,`brand`,count(p.`pid`) as num FROM (gmk_products as p".$joining1." JOIN fusion as f ON p.`pid`=f.`itemId` AND `itemType`='product' AND `ownerType`='category'".$joining2.") LEFT JOIN gmkbrands as b ON p.`bid`=b.`id` WHERE p.`pid` > 0".$joining3." GROUP BY `bid` ORDER BY `brand`";
			if(count($binds)>0)
			{
				$q=$db1->prepare($string);
				$q->execute($binds);
			}
			else
			{
				$q=$db1->query($string);
			}
			$rr=$q->fetchAll();
			$n=$q->rowCount();
		?>
		<?php if(strlen($owner)>0&&$rr[0]['num']>10){?>
			<form action="index.php" method="get" style="width:98%;margin:auto;">
			<input type="hidden" name="p" value="products" />
			<input type="hidden" name="curpage" value="<?=urlencode($get_arr['curpage'])?>" />
			<input type="hidden" name="owner" value="<?=$owner?>" />
			Limit: <select name="brand">
			<option value="">by brand...</option>
				<?php 
				//mysql_data_seek($q,0);
				//while($r=mysql_fetch_assoc($q))
				foreach($rr as $r)
				{
					?><option value="<?=$r['bid']?>" <?=isset($get_arr['brand'])&&$get_arr['brand']==$r['bid']?"selected='selected'":""?>>by brand: <?=$r['brand']?></option><?php
				}
				?>
			</select>
			<select name="type">
			<option value="notype">by type...</option>
			<?php 
			/*$types=ysql_query("SELECT `type` FROM gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND `itemType`='product' WHERE `ownerId`='".intval($owner)."' GROUP BY `type`",$con1);
			while(list($thetype)=mysql_fetch_row($types))*/
			$types=$db1->prepare("SELECT `type` FROM gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND `itemType`='product' WHERE `ownerId`=? GROUP BY `type`");
			$types->execute(array(intval($owner)));
			while(list($thetype)=$types->fetch())
			{
				$unitype=strlen($thetype)>0?$thetype:"universal";
				?><option value="<?=$unitype?>" <?=isset($get_arr['type'])&&$get_arr['type']==$unitype?"selected='selected'":""?>>by type: <?=ucwords($unitype)?></option><?php
			}
			?>
			</select>
			<input type="submit" value="Submit" />
			</form>
		<?php }?>
			<script type="text/javascript" src="functions.js"></script>
			<script type="text/javascript">boxarr['displayed']=[];</script>
			<?php if(strlen($owner)>0){?>
				<form action="<?=$formaction?>" method="post" id='prods'>
				<input type="hidden" name="action" value="displayedprod" />
			<?php }?>
			<p class="submittop"><a href="index.php?p=products&amp;showing=prodform&amp;owner=<?=$owner?>">Add Product</a></p>
			<table class="linkslist">
			<tr class="head"><td colspan="6">Products</td></tr>
			<tr class="subhead">
				<td style="width:<?=strlen($owner)>0?"70":"80"?>%"><div style="float:left">Product Name</div><div style="float:right">Stock</div></td>
				<?php if(strlen($owner)>0){?>
				<td style="width:10%;text-align:center">New</td>
				<td style="width:10%;text-align:right;white-space:nowrap"><?=MAIN_ONOFF?>&nbsp;<input type="checkbox" onclick="cart_multiCheck(this.form,'displayed',this)" /></td>
				<?php cart_allowpurch_head();?>
				<?php }?>
				<td style="white-space:nowrap;">Sorting</td>
				<td style="text-align:center"></td>
			</tr>
			<?php
			if(isset($get_arr['brand'])&&strlen($get_arr['brand'])>0){$binds[]=$get_arr['brand'];}
			if(isset($get_arr['type'])&&strlen($get_arr['type'])>0&&$get_arr['type']!="notype"&&$get_arr['type']!="universal"){$binds[]=$get_arr['type'];}
			//$joining4=isset($get_arr['brand'])&&strlen($get_arr['brand'])>0?" AND `bid`='$get_arr[brand]'":"";
			//$joining5=isset($get_arr['type'])&&strlen($get_arr['type'])>0&&$get_arr['type']!="notype"?" AND `type`='".($get_arr['type']=="universal"?"":$get_arr['type'])."'":"";
			$joining4=isset($get_arr['brand'])&&strlen($get_arr['brand'])>0?" AND `bid`=?":"";
			$joining5=isset($get_arr['type'])&&strlen($get_arr['type'])>0&&$get_arr['type']!="notype"?" AND `type`='".($get_arr['type']=="universal"?"":"?")."'":"";
			
			$string="SELECT *,p.`pid`,SUM(`nav_qty`) as stock,f.`sorting` as sorting FROM (((gmk_products as p".$joining1." JOIN fusion as f ON p.`pid`=f.`itemId`".$joining2." AND `itemType`='product' AND `ownerType`='category') LEFT JOIN cart_variants as cv ON cv.`pid`=p.`pid`) LEFT JOIN nav_stock as ns ON ns.`nav_skuvar`=cv.`vskuvar`) LEFT JOIN gmkbrands as b ON p.`bid`=b.`id` WHERE p.`pid` > 0".$joining3.$joining4.$joining5." GROUP BY p.`pid` ORDER BY `brand`,f.`sorting`,`prod_title`";
			//$q=ysql_query($string,$con1) or die(sql_error("Error",$string));
			//$n=mysql_num_rows($q);
			if(count($binds)>0)
			{
				$q=$db1->prepare($string);
				$q->execute($binds);
			}
			else
			{
				$q=$db1->query($string);
			}
			$n=$q->rowCount();
			if($n<1){?><tr class="row_dark"><td colspan="6" style="text-align:center">No Products Found</td></tr><?php }
			$thisbrand="";
			$rn=0;
			//while($r=mysql_fetch_assoc($q))
			while($r=$q->fetch())
			{
				extract($r);
				if($thisbrand!=$brand){if(strlen($thisbrand)>0){?></tbody><?php }?><tr class="extrahead" onclick="togglesection('sec<?=$brand?>')" style="cursor:pointer;"><td colspan="6"><div style="float:left"><?=ucwords($brand)?></div><div style="float:right"><a id="tog<?=$brand?>">-</a></div></td></tr><tbody id="sec<?=$brand?>"><?php $thisbrand=$brand;$rn=0;}
				$delmsg=strlen($owner)>0?"Really remove $prod_title from this category? (if owned by no other categories, this product will be moved to the orphaned category)":"Really permanently delete $prod_title?";
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td><div style="float:left">&nbsp;<a href="index.php?p=products&amp;showing=prodform&amp;pid=<?=$pid?>&amp;owner=<?=$owner?>&amp;curpage=<?=urlencode($prod_title)?>"><?=str_replace(array("™","®"),array("&trade;","&reg;"),$prod_title)?></a></div><div style="float:right"><?=$stock?>&nbsp;&nbsp;</div></td>
					<?php if(strlen($owner)>0){?>
					<td style="text-align:center">
					<input type="hidden" name="showasnew[<?=$itemId?>]" value="0" />
					<input type="checkbox" name="showasnew[<?=$itemId?>]" value="1" <?php if($showasnew==1){?>checked="checked"<?php }?> />
					</td>
					<td style="text-align:right">
					<input type="hidden" name="displayed[<?=$itemId?>]" value="0" />
					<input type="checkbox" name="displayed[<?=$itemId?>]" id="displayed[<?=$itemId?>]" value="1" <?php if($displayed==1){?>checked="checked"<?php }?> />
					<script type="text/javascript">boxarr["displayed"].push("displayed[<?=$itemId?>]");</script>
					</td>
					<td style="text-align:right">
					<?php cart_allowpurch_opt($pid,1);?>
					</td>
					<?php }?>
					<td style="white-space:nowrap;text-align:center"><input type="text" name="sorting[<?=$fusionId?>]" value="<?=$sorting?>"  style="width:20px" /></td>
					<td style="text-align:center;white-space:nowrap" class="blocklink"><a href="index.php?p=products&amp;showing=prodform&amp;pid=<?=$pid?>&amp;owner=<?=$owner?>&amp;curpage=<?=urlencode($prod_title)?>"><img src="img/edit.png" alt="Edit" /></a> <a onclick="javascript:decision('<?=htmlspecialchars($delmsg,ENT_QUOTES)?>','index.php?p=products&amp;action=deleteprod&amp;pid=<?=$pid?>&amp;owner=<?=$owner?>&amp;curpage=<?=htmlspecialchars($curpage,ENT_QUOTES,"ISO-8859-1")?>')" style="cursor:pointer"><img src="img/delete.png" alt="Del" /></a></td>
				</tr>
				<?php
				$rn++;
			}
			if(strlen($thisbrand)>0){?></tbody><?php }
			?>
			</table>
			<?php if(strlen($owner)>0){?>
			<p class="submit"><?php if($n>0){?><input type="submit" value="Update New/Status" style="border:0" /><?php }?></p>
			</form>
			<?php }
		}
		break;
}?>