<?php 
$basefolder=basename(dirname($_SERVER['PHP_SELF']));
if($basefolder!="admin"){?>
<script type="text/javascript">window.location.href="index.php";</script>
<noscript><meta http-equiv="refresh" content="0;url=index.php" /></noscript>
<?php }//direct access security 
$optid=isset($get_arr['optid'])?$get_arr['optid']:0;
if($optid>0)
{
	$imgsdir="../".$images_arr['variants']['path'].$optid."/";
	if(isset($post_arr['identifier'])&&$post_arr['identifier']=="imgmanager")
	{
		@mkdir($imgsdir);
		$files=glob($imgsdir."*.jpg");
		if(isset($post_arr['del']))
		{
			foreach($post_arr['del'] as $img => $yesno){if($yesno==1){cart_delete_img($imgsdir.$img);}}
		}
		$exclude="";
		if((strlen($_FILES['files']['name']['new-t-main'])<1&&strlen($post_arr['setname'])>0)||(strlen($post_arr['setname'])<1&&(strlen($_FILES['files']['name']['new-t-main'])>0||strlen($_FILES['files']['name']['new-t-prod'])>0||strlen($_FILES['files']['name']['new-t-prod_large'])>0)))
		{
			?><div class="notice">To add a new set you must upload a swatch AND name the image set.</div><?php
			$exclude="new";
		}
		foreach($_FILES['files']['name'] as $fname => $file)
		{
			$expl=explode("-t-",$fname);
			$newname=$expl[0]=="new"?$post_arr['setname']."-t-".$expl[1]:$fname;
			if($expl[0]!=$exclude){cart_fileupload($imgsdir,$file,$_FILES['files']['tmp_name'][$fname],$newname,500,$cart_imgfiletypes);}
		}
	}
	?>
	<form action="<?=$formaction?>" name="imgmanager" enctype="multipart/form-data" method="post">
	<input type="hidden" name="identifier" value="imgmanager" />
	<table class="details">
		<tr class="head">
			<td colspan="4"><div class="titles">Image Set Builder for Option: <a href="index.php?p=cart_variantgroups&amp;act=edit&amp;optid=<?=$optid?>"><?=ucwords($get_arr['optname'])?></a></div></td>
		</tr>
		<tr class="subhead">
			<td style="width:25%">Set Name</td>
			<td style="width:25%">Swatch</td>
			<td style="width:25%">Product Image</td>
			<td style="width:25%">Large Product Image</td>
		</tr>
		<?php
		$groups=array();
		$files=glob($imgsdir."*.jpg");
		foreach($files as $file)
		{
			$imgonly=basename($file);
			$expl=explode("-t-",$imgonly);
			if(!in_array($expl[0],$groups)){$groups[]=$expl[0];}
		}
		foreach($groups as $group)
		{
			$main=$group."-t-main.jpg";
			$prod=$group."-t-prod.jpg";
			$lrg=$group."-t-prod_large.jpg";
			?>
			<tr>
				<td><?=ucwords($group)?></td>
				<td>
					<?php if(file_exists($imgsdir.$main)){?>
						<img src="<?=$imgsdir.$main?>" alt="" style="vertical-align:middle" />
						<input type="hidden" name="del[<?=$main?>]" value="0" />
						<input type="checkbox" id="<?=$main?>" name="del[<?=$main?>]" value="1" />
						<label for="<?=$main?>">Delete Swatch</label>
					<?php }else{?>
						<label for="<?=$main?>">Upload Swatch</label>
						<input type="file" id="<?=$main?>" name="files[<?=str_replace(".jpg","",$main)?>]" accept="image/jpeg" class="input_file" />
					<?php }?>
				</td>
				<td>
					<?php if(file_exists($imgsdir.$prod)){?>
						<img src="<?=$imgsdir.$prod?>" alt="" style="height:50px;vertical-align:middle" />
						<input type="hidden" name="del[<?=$prod?>]" value="0" />
						<input type="checkbox" id="<?=$prod?>" name="del[<?=$prod?>]" value="1" />
						<label for="<?=$prod?>">Delete Image</label>
					<?php }else{?>
						<label for="<?=$prod?>">Upload Image</label>
						<input type="file" id="<?=$prod?>" name="files[<?=str_replace(".jpg","",$prod)?>]" accept="image/jpeg" class="input_file" />
					<?php }?>
				</td>
				<td>
					<?php if(file_exists($imgsdir.$lrg)){?>
						<img src="<?=$imgsdir.$lrg?>" alt="" style="height:50px;vertical-align:middle" />
						<input type="hidden" name="del[<?=$lrg?>]" value="0" />
						<input type="checkbox" id="<?=$lrg?>" name="del[<?=$lrg?>]" value="1" />
						<label for="<?=$lrg?>">Delete Large Image</label>
					<?php }else{?>
						<label for="<?=$lrg?>">Upload Large Image</label>
						<input type="file" id="<?=$lrg?>" name="files[<?=str_replace(".jpg","",$lrg)?>]" accept="image/jpeg" class="input_file" />
					<?php }?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr class="subhead">
			<td colspan="4">Add New Set</td>
		</tr>
		<tr>
			<td><input type="text" name="setname" value="" class="input_text" /></td>
			<td><input type="file" name="files[new-t-main]" accept="image/jpeg" class="input_file" /></td>
			<td><input type="file" name="files[new-t-prod]" accept="image/jpeg" class="input_file" /></td>
			<td><input type="file" name="files[new-t-prod_large]" accept="image/jpeg" class="input_file" /></td>
		</tr>
	</table>
	<p class="submit"><input type="submit" value="Submit" /></p>
	</form>
	<?	
}
else
{
 ?>Option group ID not found<?php 
}
?>

