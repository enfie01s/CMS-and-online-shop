<script type="text/javascript" src="functions.js"></script>
<?php
$id=isset($get_arr['id'])?$get_arr['id']:(isset($post_arr['id'])?$post_arr['id']:"");
$disp=array("F","T");
/* Perform actions */
if(isset($get_arr['delete'])&&strlen($id)!=0)
{
	//$dir=$get_arr['delete']=="thumbnail"?"thumbs/".$id.".jpg":$get_arr['delete']."/".$id.".jpg";
	unlink($prefixpath.$get_arr['delete']);
}
if(isset($get_arr['iddel']))
{
	sql_query("DELETE FROM gmknews WHERE `id`='" . intval($get_arr['iddel']) . "'",$con1);
	foreach($images_arr['news']['images'] as $dir => $size)
	{
		$dir=$dir=="thumbnail"?"":$dir."/";
		foreach($imgfiletypes as $type){
			@unlink("../".$images_arr['news']['path'].$dir.$get_arr['iddel'].".".$type);
		}
		
	}
	//del all added imgs too
	$addimgs=glob("../".$images_arr['news']['path'].$get_arr['iddel']."_*.jpg");
	foreach($addimgs as $i => $im)
	{
		@unlink($im);		
	}
	@unlink($prefixpath.$images_arr['news']['path']."flv/".$get_arr['iddel'].".flv");
}
if(isset($get_arr['deleteflash']))
{
	if($debugmode==0){@unlink($prefixpath.$images_arr['news']['path']."flv/".$get_arr['deleteflash'].".flv");}
	else{echo "Delete ".$prefixpath.$images_arr['news']['path']."flv/".$get_arr['deleteflash'].".flv";}
}
if(isset($post_arr['submittedfrom'])&&($post_arr['submittedfrom']==="news"||$post_arr['submittedfrom']==="newsitem"))
{
	if($post_arr['submittedfrom']==="news")
	{
		$statuseson=bindIns(implode(",",array_keys($post_arr['display'],$disp[1])));
		$statusesoff=bindIns(implode(",",array_keys($post_arr['display'],$disp[0])));
		sql_query("UPDATE gmknews SET `display`='".$disp[1]."' WHERE `id` IN(".$statuseson[0].")",$db1,$statuseson[1]);
		sql_query("UPDATE gmknews SET `display`='".$disp[0]."' WHERE `id` IN(".$statusesoff[0].")",$db1,$statusesoff[1]);
		?><div class="notice">News successfully updated.</div><?php
	}
	else if($post_arr['submittedfrom']==="newsitem")
	{
		//sql part
		$date = strtotime($post_arr['year']."-".$post_arr['month']."-".$post_arr['day']);
		
		$htitle = trim(strip_tags($post_arr['title']));		
		$content = $post_arr['content'];
		$cleancontent=addslashes(str_replace("<br>","<br />",$content));
		$intro = $post_arr['intro'];		
		$type = $post_arr['type'];
		$rawhtml = $post_arr['rawhtml'];
		$display = $post_arr['display'];
		$error="";
		switch ($act)
		{
			case 'new':
				sql_query("INSERT INTO gmknews (`title`, `intro`, `content`, `date`,`display`,`rawhtml`) VALUES (?,?,?,?,?,?)",$db1,array($htitle,$intro,$cleancontent ,$date,$display,$rawhtml));	
				$act="update";
				$id=$db1->lastInsertId();
				break;
			case 'update':
				sql_query("UPDATE gmknews SET `title`=?, `intro`=?, `display`=?, `content`=?, `date`=?, `rawhtml`=? WHERE `id`='" . intval($id) . "'",$db1,array($htitle,$intro,$display,$cleancontent,$date,$rawhtml));
				
				break;
		}
		if(isset($_FILES['addedfile'])&&count($_FILES['addedfile']['name'])>0)
		{
			$addimgs=glob("../".$images_arr['news']['path'].$id."_*.jpg");
			$idadd=count($addimgs)+1;
			$uploadedafile=uploadfile($prefixpath.$images_arr['news']['path'],$_FILES['addedfile']['name'],$_FILES['addedfile']['tmp_name'],$id."_".$idadd,'100',$imgfiletypes);
		}
		if(isset($_FILES['uploadedfile'])&&count($_FILES['uploadedfile']['name'])>0)
		{
			foreach($_FILES['uploadedfile']['name'] as $size => $fname)
			{
				if(strlen($fname)>0)
				{
					$uppath=$images_arr['news']['path'];
					if($size=="thumbnail"){$uppath.="thumbs/";}
					else if($size=="tiny"){$uppath.="tiny/";}
					$uploadto=$prefixpath.$uppath;
					$uploadedfile=uploadfile($uploadto,$_FILES['uploadedfile']['name'][$size],$_FILES['uploadedfile']['tmp_name'][$size],$id,'100',$imgfiletypes);
					if($uploadedfile!=$uploadto.$id.".jpg"){if(strlen($error)>0){$error.="<br />";}$error.=$uploadedfile;}
				}				
			}
		}
		if(strlen($_FILES['flash']['name'])>0)
		{ 
			$path=$prefixpath.$images_arr['news']['path']."flv/";
			$uploadedfile=uploadfile($path,$_FILES['flash']['name'],$_FILES['flash']['tmp_name'],$id,'600',$imgfiletypes);
			if($uploadedfile!=$path.$id.".flv")
				{$error.=$uploadedfile."<br />";}
		}
		if(strlen($error)<1){?><div class="notice">News successfully updated.</div><?php }else{ ?><div class="notice"><?=$error?></div><?php }
	}
}

/* display news item form */
if($act==="update"||$act==="new")
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
			plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,advlist",

			// Theme options
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,forecolor,|,bullist,numlist,|,outdent,indent,|,code,preview,fullscreen",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,replace,|,link,unlink,image,charmap,iespell,media,advhr,|,tablecontrols,|,visualaid,cleanup",
			
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
			external_image_list_url : "tinymce/image_list.php?dir=<?=urlencode('../../'.$images_arr['news']['path'])?>",
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
	if($act !== "new")
	{
		/*$r = ysql_query("SELECT * FROM gmknews WHERE `id`='" . intval($id) . "'",$con1) or die(sql_error("Error"));	
		$i = mysql_fetch_array($r);*/
		$r = $db1->prepare("SELECT * FROM gmknews WHERE `id`=?");
		$r->execute(array(intval($id)));	
		$i = $r->fetch();
	}
	$content = $act !== "new"?$i['content']:"";
	$intro = $act !== "new"?$i['intro']:"";
	$heading = $act !== "new"?stripslashes($i['title']):"";
	$display = $act !== "new"?$i['display']:"";
	$rawhtml = $act !== "new"?$i['rawhtml']:"";
	$date=$act !== "new"?$i['date']:date("U");
	$month=date("m",$date);
	$day=date("d",$date);
	$year=date("Y",$date);
	?>
	<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">News</a></div>
	<form name="newsed" id="newsed" enctype="multipart/form-data" action="index.php?p=news&amp;act=<?=$act?><?=strlen($id)>0?"&amp;id=".$id:""?>" method="post" onsubmit="return checkForm('newsed');">
	<input type="hidden" name="required" value="title,year,intro,content" />
	<input type="hidden" name="submittedfrom" value="newsitem" />
	<input type="hidden" name="action" value="<?=$action?>" />
	<input type="hidden" name="id" value="<?=$id?>" />
	<p class="submittop"><a class="button" href="http://www.photoshoponlinefree.com/" target="_blank">Photo Editor</a></p>
	<table class="details">
		<tr class="head">
			<td colspan="2">Edit News Article - Current Status: <?=$display == $disp[1]?"<span style='border-bottom:2px solid #70DB70;'>Displayed</span>":"<span style='border-bottom:2px solid #FF0000;'>Not Displayed</span>"?><?=strlen($id)>0?" [<a href='../news/id/".$id."' target='_blank'>View on site</a>]":""?></td>
		</tr>
		<tr>
			<td class="left_light" id="titletitle">Title:</td>
			<td class="right_light"><input name="title" type="text" size="70" maxlength="70" value="<?=htmlspecialchars($heading)?>" /></td>
		</tr>
		<tr>
			<td class="left_dark" id="yeartitle">Date:</td>
			<td class="right_dark">
			<select name="month"><?php for($x=1;$x<13;$x++){?><option value="<?=$x?>" <?php if($month == $x){?>selected="selected"<?php }?>><?=gmdate("M",gmmktime(0,0,0,$x,1,$year))?></option><?php }?></select>
			<select name="day"><?php for($x=1;$x<32;$x++){?><option value="<?=$x?>" <?php if($day == $x){?>selected="selected"<?php }?>><?=$x?></option><?php }?></select>
			<input type="text" name="year" value="<?=$year?>" style="width:30px" />
			</td>
		</tr>
			<tr>
				<td class="left_light">Status</td>
				<td class="right_light"><label for="displayT" class="yes"><input type="radio" name="display" id="displayT" value="T" <?=$display=='T'?"checked='checked'":""?> /> On</label><label for="displayF" class="no"><input type="radio" name="display" id="displayF" value="F" <?=$display=='T'?"":"checked='checked'"?> /> Off</label></td>
			</tr>
		<?php 
		foreach($images_arr['news']['images'] as $name => $size)
		{
			$image="../".$images_arr['news']['path'];
			if($name=="thumbnail"){$image.="thumbs/";}
			else if($name=="tiny"){$image.="tiny/";}
			$image.=$id.".jpg";	
			$rowcss=!isset($rowcss)||$rowcss=="_light"?"_dark":"_light";	
			?>
			<tr>
				<td class="left<?=$rowcss?>"><?=ucwords($name)?> Image:<br />JPG (<?=$size?>)</td>
				<td class="right<?=$rowcss?>"><div style="float:left;"><input name="uploadedfile[<?=$name?>]" type="file" accept="image/jpeg" /></div>
				
				<?php if(file_exists($image)){?>
				<div style="float:right;text-align:right">					
					<ul class="enlarge">
						<li>
							<img src="img/spacer.gif" style="background:url(<?=$image?>) no-repeat center center" class="thumb" alt="" />
							<span><img src="<?=$image?>" alt="" /><br /><?=ucwords($name)?> Image</span>
							</li>						
					</ul> 		
						 <a href="javascript:decision('Delete <?=$name?> image?', 'index.php?p=news&amp;act=<?=$act?>&amp;id=<?=$id?>&amp;delete=<?=str_replace("../","/",$image)?>')" ><img src="img/delete.png" alt="X" /></a>		
				</div>
				<?php }?>
				
				
				</td>
			</tr>
			<?php 
		}
		$rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	
		?>
		<tr>
				<td class="left<?=$rowcss?>">Video File (.flv)</td>
				<td class="right<?=$rowcss?>">
				<?php $flashfile="../".$images_arr['news']['path']."flv/".$id.".flv";?>
				<div style="float:left"><input type="file" name="flash" class="input_file" /></div>
				<div style="float:right"><?php if(file_exists($flashfile)){?>[<a href="javascript:decision('Really delete flash video?','index.php?p=news&amp;act=<?=$act?>&amp;id=<?=$id?>&amp;deleteflash=<?=$id?>')">Delete current file</a>]<?php }else{?>No video found.<?php }?></div><div class="clear"></div>
				</td>
			</tr>
			<tr class="subhead">
			<td colspan="2">Additional Images</td>
			</tr>
			<?php 
			$rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";	
			$addimgs=glob("../".$images_arr['news']['path'].$id."_*.jpg");
			?>
			<tr>
				<td class="row_<?=$rowcss?>" colspan="2">
				<?php foreach($addimgs as $i => $im){?>
				<div style="display:inline-block;position:relative;top:0;left:0;margin-right:5px;margin-top:5px">					
					<ul class="enlarge">
						<li>
							<img src="img/spacer.gif" style="background:url(<?=$im?>) no-repeat center center;height:80px !important;" class="thumb" alt="" />
							<span class="cen"><img src="<?=$im?>" alt="" /><br /><?=ucwords(basename($im))?></span>
							</li>						
					</ul> 		
						 <a href="javascript:decision('Delete <?=basename($im)?> image?', 'index.php?p=news&amp;act=<?=$act?>&amp;id=<?=$id?>&amp;delete=<?=str_replace("../","/",$im)?>')" style="position:absolute;top:-5px;left:-5px;"><img src="img/delete.png" alt="X" /></a>		
				</div> 
				<?php }?>
				<div style="display:inline-block">Add an image JPG (<?=$images_arr['news']['images']['large']?>)<input name="addedfile" type="file" accept="image/jpeg" /></div>
				</td>
			</tr>
			<tr class="subhead">
			<td colspan="2">Page Text</td>
			</tr>
		<tr>
			<td class="left_light" style="vertical-align:top" id="introtitle">Intro text:</td>
			<td class="right_light">
			<textarea id="text" name="intro" rows="3" cols="100" maxlength="500" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;width:688px"><?=str_replace("\\","",htmlspecialchars($intro))?></textarea>
			</td>
		</tr>
		<?php $rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";?>
		<tr>
			<td class="left_dark" style="vertical-align:top" id="contenttitle">Content:</td>
			<td class="right_dark">
			<textarea id="content" name="content" style="height: 300px; width: 688px;color:#FFF;background:#010C39;border:1px solid #666" class="tinymce"><?=str_replace(array("\\r\\n","\\"),array("",""),$content)?></textarea>
			</td>
		</tr>
		<?php 
		if($uaa['super']==1)
		{
		$rowcss=!isset($rowcss)||$rowcss=="_dark"?"_light":"_dark";?>
		<tr>
			<td class="left_light" style="vertical-align:top" id="rawhtmltitle">Raw HTML:</td>
			<td class="right_light">
			<textarea id="rawhtml" name="rawhtml" style="height: 250px; width: 688px;color:#333333;background:#FFF;border:1px solid #888"><?=str_replace(array("\\r\\n","\\"),array("",""),$rawhtml)?></textarea>
			</td>
		</tr>
		<?php }?>
		</table>
		<p class="submit"><input type="submit" alt="Submit" value="Submit" style="border:0" /></p>
		</form>
	<?php
}
/* display news list */
else
{
	//$result_news = ysql_query("SELECT * FROM gmknews ORDER BY `date` DESC",$con1) or die(sql_error("Error"));
	$result_news = $db1->query("SELECT * FROM gmknews ORDER BY `date` DESC,id DESC");
	?>
	<div id="bread"><a href="index.php">Home</a> &raquo; <a href="<?=$self?>">News</a></div>
	<p class="submittop"><a href="index.php?p=news&amp;act=new">Add Article</a></p>
	<form method="post" name="news" action="index.php?p=news">
	<input type="hidden" name="submittedfrom" value="news" />	
	<table class="linkslist"> 
		<tr class="head">
			<td colspan="5">Listing News Articles</td>
		</tr> 
		<tr class="subhead">
				<td style="width:74%"><div class="titles">Article Title</div></td>
				<td style="width:10%;text-align:center">Date</td>
				<td style="width:8%;text-align:center">Display</td>
				<td style="width:8%;text-align:center" colspan="2"></td>
		</tr> 
		<?php 
		//while($info_news = mysql_fetch_array($result_news))
		while($info_news = $result_news->fetch())
		{ 
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			?>
			<tr class="<?=$row_class?>">
				<td><a href="index.php?p=news&amp;act=update&amp;id=<?=$info_news['id']?>"><?=htmlspecialchars($info_news['title'],ENT_QUOTES,"ISO-8859-1")?></a></td>
				<td style="text-align:center"><?=date("jS M y",$info_news['date'])?></td>
				<td style="text-align:center"><input type="hidden" name="display[<?=$info_news['id']?>]" value="<?=$disp[0]?>" />
					 <input id="display_<?=$info_news['id']?>" name="display[<?=$info_news['id']?>]" type="checkbox"<?php if ($info_news['display'] == $disp[1]) { ?> checked="checked"<?php } ?> value="<?=$disp[1]?>" />
				</td>
				<td style="text-align:center" class="blocklink"><a href="index.php?p=news&amp;act=update&amp;id=<?=$info_news['id']?>"><img src="img/edit.png" alt="Edit" /></a> <a href="#" onclick="javascript:decision('Delete <?=htmlspecialchars($info_news['title'],ENT_QUOTES,"ISO-8859-1")?>?','index.php?p=news&amp;iddel=<?=$info_news['id']?>')"><img src="img/delete.png" alt="X" /></a>
					</td>
			</tr>
			<?php 
		}
		?>
	</table>
	<p class="submit"><a href="index.php?p=news&amp;act=new">Add Article</a> <input type="submit" alt="Submit"value="Set Display" style="border:0" /></p>
	</form>
	<?php
}
?>