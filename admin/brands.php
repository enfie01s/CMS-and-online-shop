<script type="text/javascript" src="functions.js"></script>
<?php
$id=isset($get_arr['id'])?$get_arr['id']:(isset($post_arr['id'])?$post_arr['id']:"");
//$act=isset($_GET['act'])?$_GET['act']:"view";

/* Perform actions */
if(isset($_POST['title']))
{
	if($_POST['action']=="update")
	{
		$q=$db1->prepare("UPDATE gmkbrands SET `brand`=?,`Website`=?,`introtext`=? WHERE `id`=?");
		$q->execute(array($_POST['title'],$_POST['Website'],$_POST['content'],$_POST['id']));
	}
	else//new
	{
		$q=$db1->prepare("INSERT INTO gmkbrands (`brand`,`Website`,`introtext`)VALUES(?,?,?)");
		$q->execute(array($_POST['title'],$_POST['Website'],$_POST['content']));
	}
}
if(isset($_GET['iddel']))
{
	$q=$db1->prepare("DELETE FROM gmkbrands WHERE `id`=?");
	$q->execute(array($_GET['iddel']));
}
if(isset($_POST['sorting']))
{
	$q=$db1->prepare("UPDATE gmkbrands SET sorting=? WHERE id=?");
	foreach($_POST['sorting'] as $bid => $sort)
	{
		$q->execute(array($sort,$bid));
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
		$q = $db1->prepare("SELECT * FROM gmkbrands WHERE `id`=?");
		$q->execute(array(intval($id)));	
		$r = $q->fetch();
	}
	?>
	<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Brands</a></div>
	<form name="branded" id="branded" enctype="multipart/form-data" action="index.php?p=brands&amp;act=<?=$act?><?=strlen($id)>0?"&amp;id=".$id:""?>" method="post" onsubmit="return checkForm('branded');">
	<input type="hidden" name="required" value="brand" />
	<input type="hidden" name="action" value="<?=$act?>" />
	<input type="hidden" name="id" value="<?=$id?>" />
	<table class="details">
		<tr class="head">
			<td colspan="2">Edit Brand</td>
		</tr>
		<tr>
			<td class="left_light" id="titletitle">Brand Name:</td>
			<td class="right_light"><input name="title" type="text" size="70" maxlength="70" value="<?=htmlspecialchars($r['brand'])?>" /></td>
		</tr>
		<tr>
			<td class="left_dark" id="titletitle">Website:</td>
			<td class="right_dark"><input name="Website" type="text" size="70" maxlength="70" value="<?=htmlspecialchars($r['Website'])?>" /></td>
		</tr>
		<tr>
			<td class="left_light" style="vertical-align:top" id="contenttitle">Intro Text:</td>
			<td class="right_light">
			<textarea id="content" name="content" style="height: 300px; width: 688px;color:#FFF;background:#010C39;border:1px solid #666" class="tinymce"><?=str_replace(array("\\r\\n","\\"),array("",""),$r['introtext'])?></textarea>
			</td>
		</tr>
		</table>
		<p class="submit"><input type="submit" alt="Submit" value="Submit" style="border:0" /></p>
		</form>
	<?php
}
/* display list */
else
{
	$q = $db1->query("SELECT * FROM gmkbrands ORDER BY `sorting`");
	?>
	<div id="bread"><a href="index.php">Home</a> &raquo; <a href="<?=$self?>">Brands</a></div>
	<p class="submittop"><a href="index.php?p=brands&amp;act=new">Add Brand</a></p>
	<form method="post" name="brands" action="index.php?p=brands">
	<input type="hidden" name="submittedfrom" value="brands" />	
	<table class="linkslist"> 
		<tr class="head">
			<td colspan="5">All Brands</td>
		</tr> 
		<tr class="subhead">
				<td style="width:74%"><div class="titles">Brand</div></td>
				<td style="width:10%;text-align:center">Sorting</td>
				<td style="text-align:center"></td>
		</tr> 
		<?php 
		//while($info_news = mysql_fetch_array($result_news))
		while($r = $q->fetch())
		{ 
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			?>
			<tr class="<?=$row_class?>">
				<td><a href="index.php?p=brands&amp;act=update&amp;id=<?=$r['id']?>"><?=stripslashes($r['brand'])?></a></td>
				<td style="text-align:center"><input id="sorting_<?=$r['id']?>" name="sorting[<?=$r['id']?>]" type="text" value="<?=$r['sorting']?>" style="width:30px" />
				</td>
				<td style="text-align:center" class="blocklink"><a href="#" onclick="javascript:decision('Delete <?=htmlspecialchars($r['brand'],ENT_QUOTES,"ISO-8859-1")?>?','index.php?p=brands&amp;iddel=<?=$r['id']?>')"><img src="img/delete.png" alt="X" /></a>
					</td>
			</tr>
			<?php 
		}
		?>
	</table>
	<p class="submit"><a href="index.php?p=brands&amp;act=new">Add Brand</a> <input type="submit" alt="Submit" value="Update Sorting" style="border:0" /></p>
	</form>
	<?php
}
?>