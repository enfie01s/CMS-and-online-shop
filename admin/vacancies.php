<script type="text/javascript" src="functions.js"></script>
<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Vacancies</a></div>
<?php 
$id=isset($get_arr['id'])?$get_arr['id']:(isset($post_arr['id'])?$post_arr['id']:"");
if($act==="update"||$act==="new")
{
	$folder=$prefixpath."content/vacancies/".$id;
	if(isset($get_arr['delete'])&&strlen($id)!=0)
	{
		@unlink($folder."/".$get_arr['delete']);
	}
	if(isset($post_arr['title']))
	{
		$vac=$post_arr;
		$vac['start']=$post_arr['year']."-".$post_arr['month']."-".$post_arr['day'];
		$vac['expire']=$post_arr['eyear']."-".$post_arr['emonth']."-".$post_arr['eday'];
		$vac['description']=$post_arr['content'];
		if($act=="update")
		{
			/*ysql_query("UPDATE `vacancies` SET `title`='{$post_arr['title']}',`description`='{$post_arr['content']}',`addpdf1`='{$post_arr['addpdf1']}', `addpdf2`='{$post_arr['addpdf2']}',`start`='{$vac['start']} 00:00:00',`expire`='{$vac['expire']} 00:00:00',`display`='{$post_arr['display']}',`force`='{$post_arr['force']}',`featured`='{$post_arr['featured']}' WHERE `id`='{$post_arr['id']}'",CON1);*/
			$q=$db1->prepare("UPDATE `vacancies` SET `title`=?,`description`=?,`addpdf1`=?, `addpdf2`=?,`start`=?,`expire`=?,`display`=?,`force`=?,`featured`=? WHERE `id`=?");
			$q->execute(array($post_arr['title'],$post_arr['content'],$post_arr['addpdf1'],$post_arr['addpdf2'],$vac['start']." 00:00:00",$vac['expire']." 00:00:00",$post_arr['display'],$post_arr['force'],$post_arr['featured'],$post_arr['id']));
		}
		else //new
		{
			/*ysql_query("INSERT INTO vacancies (`title`,`description`,`addpdf1`,`addpdf2`,`start`,`expire`,`display`,`force`,`featured`)VALUES('{$post_arr['title']}','{$post_arr['content']}','{$post_arr['addpdf1']}','{$post_arr['addpdf2']}','{$vac['start']} 00:00:00','{$vac['expire']} 00:00:00',0,0,'{$post_arr['featured']}')",CON1);*/
			$q=$db1->prepare("INSERT INTO vacancies (`title`,`description`,`addpdf1`,`addpdf2`,`start`,`expire`,`display`,`force`,`featured`)VALUES(?,?,?,?,?,?,0,0,?)");
			$q->execute(array($post_arr['title'],$post_arr['content'],$post_arr['addpdf1'],$post_arr['addpdf2'],$vac['start']." 00:00:00",$vac['expire']." 00:00:00",$post_arr['featured']));
			$id=$db1->lastInsertId();
			$act="update";
			$folder=$prefixpath."content/vacancies/".$id;
		}
		if($post_arr['featured']==1)
		{
			//ysql_query("UPDATE vacancies SET featured='0' WHERE `id`!='$id'",CON1);
			$q=$db1->prepare("UPDATE vacancies SET featured='0' WHERE `id`!=?");
			$q->execute(array($id));
		}
		//print_r($_FILES);	
		if(!is_dir($folder)){mkdir($folder,0777);}//make folder
		
		foreach($_FILES['file']['name'] as $name => $f)
		{
			if($_FILES['file']['error'][$name]==0&&strlen($post_arr[$name])>0)
			{
				if(is_file($folder."/".$name.".pdf"))
				{
					@unlink($folder."/".$name.".pdf");/*remove if new being added*/
				}
				move_uploaded_file($_FILES['file']['tmp_name'][$name],$folder."/".$name.".pdf");
			}
		}
		/* */	
	}
	else if($act!="new")
	{
		/*$vacQ=ysql_query("SELECT * FROM vacancies WHERE id='{$id}'",CON1);
		$vac=mysql_fetch_assoc($vacQ);*/
		$vacQ=$db1->prepare("SELECT * FROM vacancies WHERE id=?");
		$vacQ->execute(array($id));
		$vac=$vacQ->fetch();
	}
	else
	{
		$vac=array('id','title','description','start'=>date("Y-m-d"),'expire'=>date("Y-m-d"),'addpdf1','addpdf2');
	}
	list($year,$month,$day)=explode("-",substr($vac['start'],0,10));//yr,mn,day
	list($eyear,$emonth,$eday)=explode("-",substr($vac['expire'],0,10));//yr,mn,day	
	?>
	<form name="vaced" id="vaced" enctype="multipart/form-data" action="index.php?p=vacancies&amp;act=<?=$act?><?=strlen($id)>0?"&amp;id=".$id:""?>" method="post" onsubmit="return checkForm('vaced');">
	<input type="hidden" name="required" value="title,description,start,expire,mainpdf" />
	<input type="hidden" name="submittedfrom" value="vacancyitem" />
	<input type="hidden" name="act" value="<?=$act?>" />
	<input type="hidden" name="id" value="<?=$id?>" />
	<table class="details">
		<tr class="head">
			<td colspan="2"><?=$act=="update"?"Edit":"Add"?> Vacancy<?php if($act=="update"){?> - Current Status: <?=$vac['display'] == 1?"<span style='border-bottom:2px solid #70DB70;'>Displayed</span>":"<span style='border-bottom:2px solid #FF0000;'>Not Displayed</span>"?><?=strlen($vac['id'])>0?" [<a href='../vacancies&amp;id=".$id."' target='_blank'>View on site</a>]":""?><?php }?></td>
		</tr>
		<tr>
			<td class="left_dark" id="titletitle">Title:</td>
			<td class="right_dark"><input name="title" type="text" size="70" maxlength="70" value="<?=stripslashes($vac['title'])?>" /></td>
		</tr>
		<tr>
			<td class="left_light" id="descriptiontitle">Description:</td>
			<td class="right_light"><textarea id="content" name="content" style="height: 300px; width: 688px;color:#FFF;background:#010C39;border:1px solid #666;" class="tinymce"><?=str_replace(array("\\r\\n","\\"),array("",""),$vac['description'])?></textarea></td>
		</tr>
		<tr>
			<td class="left_dark" id="yeartitle">Start Date:</td>
			<td class="right_dark">
			<select name="month"><?php for($x=1;$x<13;$x++){?><option value="<?=$x?>" <?php if($month == $x){?>selected="selected"<?php }?>><?=gmdate("M",gmmktime(0,0,0,$x,1,$year))?></option><?php }?></select>
			<select name="day"><?php for($x=1;$x<32;$x++){?><option value="<?=$x?>" <?php if($day == $x){?>selected="selected"<?php }?>><?=$x?></option><?php }?></select>
			<input type="text" name="year" value="<?=$year?>" style="width:30px" />
			</td>
		</tr>
		<tr>
			<td class="left_light" id="eyeartitle">Expires:</td>
			<td class="right_light">
			<select name="emonth"><?php for($x=1;$x<13;$x++){?><option value="<?=$x?>" <?php if($emonth == $x){?>selected="selected"<?php }?>><?=gmdate("M",gmmktime(0,0,0,$x,1,$eyear))?></option><?php }?></select>
			<select name="eday"><?php for($x=1;$x<32;$x++){?><option value="<?=$x?>" <?php if($eday == $x){?>selected="selected"<?php }?>><?=$x?></option><?php }?></select>
			<input type="text" name="eyear" value="<?=$eyear?>" style="width:30px" />
			</td>
		</tr>
		<tr>
			<td class="left_dark">Status</td>
			<td class="right_dark"><label for="displayT" class="yes"><input type="radio" name="display" id="displayT" value="1" <?=$vac['display']=='1'?"checked='checked'":""?> /> On</label><label for="displayF" class="no"><input type="radio" name="display" id="displayF" value="0" <?=$vac['display']=='1'?"":"checked='checked'"?> /> Off</label></td>
		</tr>
		<tr>
			<td class="left_light">Ignore Expiry?</td>
			<td class="right_light"><label for="forceT" class="yes"><input type="radio" name="force" id="forceT" value="1" <?=$vac['force']=='1'?"checked='checked'":""?> /> On</label><label for="forceF" class="no"><input type="radio" name="force" id="forceF" value="0" <?=$vac['force']=='1'?"":"checked='checked'"?> /> Off</label></td>
		</tr>
		<tr>
			<td class="left_dark">Featured</td>
			<td class="right_dark"><label for="featuredY" class="yes"><input type="radio" name="featured" id="featuredY" value="1" <?=$vac['featured']=='1'?"checked='checked'":""?> /> On</label><label for="dfeaturedF" class="no"><input type="radio" name="featured" id="featuredF" value="0" <?=$vac['featured']=='1'?"":"checked='checked'"?> /> Off</label></td>
		</tr>
		<?php
		$row="_dark";
		$files=array("mainpdf"=>"Main PDF","addpdf1"=>"Additional PDF 1","addpdf2"=>"Additional PDF 2");
		$fkeys=array_keys($files);
		foreach($files as $t => $n)
		{
			$row=!isset($row)||$row=="_light"?"_dark":"_light";
			?>
			<tr>
				<td class="left<?=$row?>" id="mainpdftitle"><?=$n?>:</td>
				<td class="right<?=$row?>"><div style="float:left;"><input name="file[<?=$t?>]" type="file" accept="application/pdf" />
				<?php if($t==$fkeys[0]){?><input type="hidden" name="<?=$t?>" value="<?=$t?>" /><?php }
				else{?>Name: <input type="text" name="<?=$t?>" value="<?=$vac[$t]?>" style="width:400px" /><?php }?>
				</div>
				<?php if(file_exists($folder."/".$t.".pdf")){?>
					<div style="float:right;text-align:right"><a href="<?=$mainbase."/content/vacancies/".$id."/".$t.".pdf"?>" target="_blank">View Current</a> <a href="javascript:decision('Delete <?=$n?>?', 'index.php?p=vacancies&amp;act=<?=$act?>&amp;id=<?=$id?>&amp;delete=<?=$t?>.pdf')" ><img src="img/delete.png" alt="X" /></a></div>
				<?php }?>
				</td>
			</tr>
			<?php
		}?>		
		</table>
		<p class="submit"><input type="submit" alt="Submit" value="Submit" style="border:0" /></p>
		</form>
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
}
else
{
	if(isset($get_arr['iddel']))
	{
		sql_query("DELETE FROM vacancies WHERE `id`=?",$db1,array(intval($get_arr['iddel'])));
		$folder=$prefixpath."content/vacancies/".$get_arr['iddel'];
		$fns=glob($folder."/*");
		foreach($fns as $f){
				@unlink($f);
		}
		@unlink($folder);
	}
	if(isset($post_arr['submittedfrom'])&&$post_arr['submittedfrom']=="vacancylist")
	{
		$onarr=array();
		$offarr=array();
		foreach($post_arr['display'] as $ii => $st)
		{
			if($st==0){array_push($offarr,$ii);}
			else{array_push($onarr,$ii);}
		}
		$onfor=array();
		$offfor=array();
		foreach($post_arr['force'] as $ii => $st)
		{
			if($st==0){array_push($offfor,$ii);}
			else{array_push($onfor,$ii);}
		}
		/*ysql_query("UPDATE vacancies SET display='0' WHERE `id` IN('".implode("','",$offarr)."')",CON1);
		ysql_query("UPDATE vacancies SET display='1' WHERE `id` IN('".implode("','",$onarr)."')",CON1);
		
		ysql_query("UPDATE vacancies SET `force`='0' WHERE `id` IN('".implode("','",$offfor)."')",CON1);
		ysql_query("UPDATE vacancies SET `force`='1' WHERE `id` IN('".implode("','",$onfor)."')",CON1);
		
		ysql_query("UPDATE vacancies SET featured='1' WHERE `id`='{$post_arr['featured']}'",CON1);
		ysql_query("UPDATE vacancies SET featured='0' WHERE `id`!='{$post_arr['featured']}'",CON1);
	}
	$result = ysql_query("SELECT * FROM vacancies ORDER BY `start` DESC",$con1) or die(sql_error("Error"));*/
		$offa=bindIns(implode(",",$offarr));
		$offb=bindIns(implode(",",$offfor));
		$ona=bindIns(implode(",",$onarr));
		$onb=bindIns(implode(",",$onfor));
		$q=$db1->prepare("UPDATE vacancies SET display='0' WHERE `id` IN(".$offa[0].")");$q->execute($offa[1]);
		$q=$db1->prepare("UPDATE vacancies SET display='1' WHERE `id` IN(".$ona[0].")");$q->execute($ona[1]);
		
		$q=$db1->prepare("UPDATE vacancies SET `force`='0' WHERE `id` IN(".$offb[0].")");$q->execute($offb[1]);
		$q=$db1->prepare("UPDATE vacancies SET `force`='1' WHERE `id` IN(".$onb[0].")");$q->execute($onb[1]);
		
		$q=$db1->prepare("UPDATE vacancies SET featured='1' WHERE `id`=?");$q->execute(array($post_arr['featured']));
		$q=$db1->prepare("UPDATE vacancies SET featured='0' WHERE `id`!=?");$q->execute(array($post_arr['featured']));
	}
	$result = $db1->query("SELECT * FROM vacancies ORDER BY `start` DESC");
	?>
	<p class="submittop"><a href="index.php?p=vacancies&amp;act=new">Add Vacancy</a></p>
	<form method="post" name="vac" action="index.php?p=vacancies">
	<input type="hidden" name="submittedfrom" value="vacancylist" />	
	<table class="linkslist"> 
		<tr class="head">
			<td colspan="7">Listing Vacancies</td>
		</tr> 
		<tr class="subhead">
				<td style="width:74%"><div class="titles">Vacancy Title</div></td>
				<td style="width:10%;text-align:center">Start</td>
				<td style="width:10%;text-align:center">Expire</td>
				<td style="width:6%;text-align:center">Display</td>
				<td style="width:6%;text-align:center">Persist</td>
				<td style="width:6%;text-align:center">Featured</td>
				<td style="width:6%;text-align:center" colspan="2"></td>
		</tr> 
		<?php 
		//while ($info = mysql_fetch_array($result))
		while ($info = $result->fetch())
		{ 
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			?>
			<tr class="<?=$row_class?>">
				<td><a href="index.php?p=vacancies&amp;act=update&amp;id=<?=$info['id']?>"><?=htmlspecialchars($info['title'],ENT_QUOTES,"ISO-8859-1")?></a></td>
				<td style="text-align:center"><?=date("jS M y",strtotime($info['start']))?></td>
				<td style="text-align:center"><?=date("jS M y",strtotime($info['expire']))?></td>
				<td style="text-align:center"><input type="hidden" name="display[<?=$info['id']?>]" value="0" />
					 <input id="display_<?=$info['id']?>" name="display[<?=$info['id']?>]" type="checkbox"<?php if ($info['display'] == 1) { ?> checked="checked"<?php } ?> value="1" />
				</td>
				<td style="text-align:center"><input type="hidden" name="force[<?=$info['id']?>]" value="0" />
					 <input id="force_<?=$info['id']?>" name="force[<?=$info['id']?>]" type="checkbox"<?php if ($info['force'] == 1) { ?> checked="checked"<?php } ?> value="1" />
				</td>
				<td style="text-align:center">
					 <input id="featured_<?=$info['id']?>" name="featured" type="radio"<?php if ($info['featured'] == 1) { ?> checked="checked"<?php } ?> value="<?=$info['id']?>" />
				</td>
				<td style="text-align:center;white-space:nowrap" class="blocklink"><a href="index.php?p=vacancies&amp;act=update&amp;id=<?=$info['id']?>"><img src="img/edit.png" alt="Edit" /></a> <a href="#" onclick="javascript:decision('Delete <?=htmlspecialchars($info['title'],ENT_QUOTES,"ISO-8859-1")?>?','index.php?p=vacancies&amp;iddel=<?=$info['id']?>')"><img src="img/delete.png" alt="X" /></a>
					</td>
			</tr>
			<?php 
		}
		?>
	</table>
	<p class="submit"><a href="index.php?p=vacancies&amp;act=new">Add Vacancy</a> <input type="submit" alt="Submit" value="Save Changes" style="border:0" /></p>
	</form>
	<?php
}
?>