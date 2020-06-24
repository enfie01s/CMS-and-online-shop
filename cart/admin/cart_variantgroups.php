<?php 
$basefolder=basename(dirname($_SERVER['PHP_SELF']));
if($basefolder!="admin"){?>
<script type="text/javascript">window.location.href="index.php";</script>
<noscript><meta http-equiv="refresh" content="0;url=index.php" /></noscript>
<?php }//direct access security 

/* RUN QUERIES */
if($act=="add"&&isset($post_arr['aid'])&&isset($post_arr['formid'])&&$post_arr['formid']=="variantbuilder")
{$_SESSION['loads'];$act="edit";
	//cart_variant_groups:
	cart_query("INSERT INTO cart_variant_groups(`optname`,`opttype`,`created`,`edited`,`admin_user`) VALUES(?,?,?,?,?)",array($post_arr['optname'],$post_arr['opttype'],$date,$date,$post_arr['aid']));
	$getoptid=$db1->lastInsertId();
	$message="";
	//$message.=emptyfieldscheck($post_arr,array("opttype"=>"Please enter the opttype (eg: 'colour')","optname"=>"Please enter the title","vskuvar"=>"Please choose the stock code for option:","vname"=>"Please enter the option name for option:"));
	
	/*EACH OPTION VALUE*/
	foreach($post_arr['vskuvar'] as $id => $vskuvar)
	{	
		$newfilename=strlen($_FILES['uploadedfile']['tmp_name'][$id])<1?"0":$getoptid."_".$id;
		
		/*NAME OK - CONTINUE UPLOAD IMAGE*/
		if(strlen($_FILES['uploadedfile']['tmp_name'][$id])>0)
		{
			$douploads=cart_fileupload($cprefixpath.$images_arr['variants']['path'],$_FILES['uploadedfile']['name'][$id],$_FILES['uploadedfile']['tmp_name'][$id],$newfilename,'200',$cart_imgfiletypes);
			
			if($douploads!=$cprefixpath.$images_arr['variants']['path'].$newfilename.".jpg"){$message.=$douploads."<br />";}
		}
		
		
		/*INSERT OPTION VALUE TO SQL*/
		cart_query("INSERT INTO cart_variants(`voptid`,`vname`,`vimg`,`vskuvar`) VALUES(?,?,?,?)",array($getoptid,$post_arr['vname'][$id],$newfilename,$vskuvar));
	}
	
	if(strlen($message)>0)
	{
		$_SESSION['error']=$message;
	}
	else
	{
		?><div class="notice">Successfully Added <?=$_SESSION['loads']?></div><?php 
	}
}
/*......................EDIT PRODUCT OPTION AND VALUES.................*/
else if($act=="edit"&&isset($post_arr['aid'])&&isset($getoptid)&&isset($post_arr['formid'])&&$post_arr['formid']=="variantbuilder")
{
	$message="";

	/*ENTER MAIN PRODUCT OPTION TO SQL*/
	cart_query("UPDATE cart_variant_groups SET `optname`=?,`opttype`=?,`edited`=?,`admin_user`=? WHERE `optid`=?",array($post_arr['optname'],$post_arr['opttype'],$date,$post_arr['aid'],$getoptid));
	
	/*EACH OPTION VALUE*/
	foreach($post_arr['vskuvar'] as $id => $vskuvar)
	{
		/*DELETE OPTION VALUE IF CHECKED*/
		if($post_arr['delete'][$id]==1)
		{
			cart_query("DELETE FROM cart_variants WHERE `vid`=?",array($id));
		}
		else
		{
			/*ADD ROW*/
			if($id=='addrow')
			{
				cart_query("INSERT INTO cart_variants(`voptid`,`vname`,`vskuvar`) VALUES(?,?,?)",array($getoptid,$post_arr['vname'][$id],$vskuvar));
			}
			
			/*UPDATE OPTIONS*/
			else
			{
				cart_query("UPDATE cart_variants SET `vname`=?,`vskuvar`=?,`vimg`=? WHERE `vid`=?",array($post_arr['vname'][$id],$vskuvar,$post_arr['vimg'][$id],$id));
			}
		}
	}
	?><div class="notice">Successfully Edited</div><?php 
}
/*.......................PRODUCT OPTIONS: DELETE WHOLE PRODUCT OPTION AND VALUES..................*/
else if($act=="delete")
{	
	$dir="../".$images_arr['variants']['path'].$getoptid."/";
	$files=glob($dir."*.jpg");
	foreach($files as $file)
	{
		cart_delete_img($file);
	}
	cart_delete_img($dir);
	cart_query("DELETE FROM cart_variant_groups WHERE `optid`=?",array($getoptid));
	cart_query("DELETE FROM cart_variants WHERE `voptid`=?",array($getoptid));
}

/* /RUN QUERIES */

if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><?php }?>
<!-- CONTENT -->
<?php
switch($act)
{
	case "add":
	case "edit":
		if(!isset($post_arr['inum'])&&$act=="add")//how many items?
		{
			?>
			<table class="details">
			<tr class="head">
				<td colspan="2"><div class="titles">Variant Groups</div></td>
			</tr>
			<tr class="row_light">
				<td>Enter the number of items there are for this option</td>
				<td>
					<form action="<?=$self?>&amp;act=add" method="post">
					<input type="text" name="inum" class="formfieldm" value="" />
					<input type="submit" name="submit" class="formbutton" value="Build Option" />
					</form>
				</td>
			</tr>
			</table>
			<?php
		}
		else//option builder
		{
			$itemarr=array();
			$vararray=array();
			$vararray['vskuvar']=array();
			$vararray['vname']=array();
			$vararray['vimg']=array();
			$lastsort=0;
			if(isset($getoptid)&&strlen($getoptid)>0){
				/*$valuesQ=ysql_query("SELECT * FROM cart_variant_groups as po LEFT JOIN cart_variants as ov ON ov.`voptid`=po.`optid` WHERE po.`optid`='$getoptid' ORDER BY `vskuvar`",CARTDB);
				$inum=mysql_num_rows($valuesQ);
				while($values=mysql_fetch_assoc($valuesQ))*/
				$valuesQ=$db1->prepare("SELECT * FROM cart_variant_groups as po LEFT JOIN cart_variants as ov ON ov.`voptid`=po.`optid` WHERE po.`optid`=? ORDER BY `vskuvar`");
				$valuesQ->execute(array($getoptid));
				$inum=$valuesQ->rowCount();
				while($values=$valuesQ->fetch(PDO::FETCH_ASSOC))
				{
					$vararray['vskuvar'][$values['vid']]=$values['vskuvar'];
					$vararray['vname'][$values['vid']]=$values['vname'];
					$vararray['vimg'][$values['vid']]=$values['vimg'];
					$editdate=$values['edited'];
					$optname=$values['optname'];
					$opttype=$values['opttype'];
				}
				if(isset($get_arr['addrow'])||(isset($post_arr['inum'])&&$inum<$post_arr['inum']))
				{
					$vararray['vskuvar']['addrow']=isset($post_arr['vskuvar'])?$post_arr['vskuvar']['addrow']:"";
					$vararray['vname']['addrow']=isset($post_arr['vname'])?$post_arr['vname']['addrow']:"";
					$vararray['vimg']['addrow']=isset($post_arr['vimg'])?$post_arr['vimg']['addrow']:"";
				}
			}
			else if(isset($_SESSION['error'])||!isset($getoptid)||strlen($getoptid)<1)
			{
				for($x=0;$x<$post_arr['inum'];$x++)
				{
					$vararray['vskuvar'][$x]=isset($post_arr['vskuvar'])?$post_arr['vskuvar'][$x]:"";
					$vararray['vname'][$x]=isset($post_arr['vname'])?$post_arr['vname'][$x]:"";
					$vararray['vimg'][$x]=isset($post_arr['vimg'])?$post_arr['vimg'][$x]:"";
				}
				$optname=isset($post_arr['optname'])?$post_arr['optname']:"";
			}
			$formarr=($act=="add"||isset($_SESSION['error']))?$post_arr:$values;
			$navs=array();
			/*$navsQ=ysql_query("SELECT `nav_skuvar`,`nav_sku`,`nav_description`,`nav_variant_desc`,`nav_variant` FROM nav_stock ORDER BY `nav_skuvar`",CARTDB);
			while($navsr=mysql_fetch_row($navsQ))*/
			$navsQ=$db1->query("SELECT `nav_skuvar`,`nav_sku`,`nav_description`,`nav_variant_desc`,`nav_variant` FROM nav_stock ORDER BY `nav_skuvar`");
			while($navsr=$navsQ->fetch(PDO::FETCH_NUM))
			{$navs[]=array($navsr[0],$navsr[1],$navsr[2],$navsr[3],$navsr[4]);}
			?>
			<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
			<form action="<?=$formaction?>" method="post" enctype="multipart/form-data" id="vbuilder" name="vbuilder" onsubmit="return checkForm('vbuilder');">
			
			<input type="hidden" name="aid" value="<?=$uaa['aid']?>" />
			<input type="hidden" name="formid" value="variantbuilder" />
			<input type="hidden" name="inum" value="<?=((isset($getoptid))?((isset($get_arr['addrow']))?$inum+1:$inum):$post_arr['inum'])?>" />
			
			<table class="details">
			<tr class="head">
				<td colspan="<?=$act=="edit"?"4":"2"?>"><div class="titles">Variant Group Builder</div><?php if($act=="edit"){?><div class="links"><a href="index.php?p=cart_images&amp;optid=<?=$getoptid?>&amp;optname=<?=urlencode($optname)?>">Image Manager</a></div><?php }?></td>
			</tr>
			<?php if($act=="edit"){?>
			<tr class="infohead">
				<td colspan="<?=(($act=="edit")?"4":"2")?>">Last updated on: <?=date("F d, Y H:i:s",$editdate)?></td>
			</tr>
			<?php }?>
			<tr>
				<td class="left_light">Variant Group Title</td>
				<td class="right_light" colspan="<?=(($act=="edit")?"3":"1")?>"><input type="text" name="optname" id="optname" class="formfield" <?=highlighterrors($higherr,"optname")?> value="<?=$optname?>" /></td>
			</tr>
			<tr>
				<td class="left_dark">Variant Group Type</td>
				<td class="right_dark" colspan="<?=(($act=="edit")?"3":"2")?>">
				<select name="opttype" <?=highlighterrors($higherr,"opttype")?>>
					<option value="Colour" <?=strtolower($opttype)=="colour"?"selected='selected'":""?>>Colour</option>
					<option value="Size" <?=strtolower($opttype)=="size"?"selected='selected'":""?>>Size</option>
					<option value="Colour/Size" <?=strtolower($opttype)=="colour/size"?"selected='selected'":""?>>Colour &amp; Size</option>
				</select>
				</td>
			</tr>
			<tr class="subhead">
				<td style="width:15%">Variant Name</td>
				<td style="width:30%">Stock Code/Variant</td>
				<?php if($act=="edit"){?>
				<td style="width:35%;text-align:center">Image Set</td>
				<td style="text-align:center">Delete</td>
				<?php }?>
			</tr>
			<?php 
			$x=0;
			$vnames="";
			$vskuvars="";
			foreach($vararray['vskuvar'] as $id => $value)
			{
				//$newfilename="../".$images_arr["variants"]["path"].$getoptid."_".$id.".jpg";
				if(strlen($vnames)>0){$vnames.=",";}$vnames.="vname_".$id;
				if(strlen($vskuvars)>0){$vskuvars.=",";}$vskuvars.="vskuvar_".$id;
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				$imgsdir="../".$images_arr['variants']['path'].$getoptid."/";
				?>
				<tr class="<?=$row_class?>">
					<td><input type="text" name="vname[<?=$id?>]" id="vname_<?=$id?>" class="formfieldm" <?=highlighterrors($higherr,"vname_".$id)?> value="<?=$vararray['vname'][$id]?>" /></td>
					<td>
					<select name="vskuvar[<?=$id?>]" id="vskuvar_<?=$id?>" class="formfield" <?=highlighterrors($higherr,"vskuvar_".$id)?>>
					<option value="">Please select...</option>
					<?php foreach($navs as $nav){ 
					$nav3=trim($nav[3]);
					$desc=(substr($nav3,-3,3)==", U")?substr($nav3,0,(strlen($nav3)-3)):$nav3;
					?>
						<option value="<?=$nav[0]?>" <?=($vararray['vskuvar'][$id]==$nav[0]?"selected='selected'":"")?>><?=$nav[1]?> (<?=str_replace(array("V000","V00"),array("V","V"),$nav[4])?>) <?=$nav[2]?> <?=$desc?></option>
					<?php }?>
					</select>
					</td>
					<?php if($act=="edit"){$imgs=glob($imgsdir."*-t-main.jpg");?>
					<td style="text-align:center">
					<?php if(count($imgs)<1){?>
					<a href="index.php?p=cart_images&amp;optid=<?=$getoptid?>&amp;optname=<?=urlencode($optname)?>">Add an image set</a>
					<?php }else{?>
					<select name="vimg[<?=$id?>]">
						<option value="">Select Image Set</option>
						<?php
						
						foreach($imgs as $img)
						{
							$imgonly=basename($img);
							$expl=explode("-t-",$imgonly);
								?><option value="<?=$expl[0]?>" <?php if($vararray['vimg'][$id]==$expl[0]){?>selected="selected"<?php }?> style="background:url(<?=$img?>) no-repeat -25px -10px;padding: 2px 0 2px 23px;margin-bottom:1px;"><?=$expl[0]?></option><?php
						}
					}
					?>
					</select>
					</td>
					<?php }?>
				<?php if($act=="edit"&&$id!="addrow"){?><td style="text-align:center"><input type="checkbox" name="delete[<?=$id?>]" value="1" /></td><?php }?>
				</tr>
				<?php
				$x++;
			}
			?><input type="hidden" name="required" value="optname,<?=$vnames?>,<?=$vskuvars?>" />
			<?php if($act=="edit"){?>
			
			<tr class="infohead">
				<td colspan="<?=(($act=="edit")?"4":"2")?>"><?=$inum?> records found</td>
			</tr>
			<?php }?>
			</table>
			<p class="submit"><?php if(!isset($_GET['addrow'])&&$act=="edit"){?><a href="<?=$self?>&amp;act=edit&amp;optid=<?=$getoptid?>&amp;addrow=1"><img src="<?=$cart_adminpath?>/images/addrow.png" alt="Add another row" /></a> <?php }?><input type="image" src="<?=$cart_adminpath?>/images/submit.png" name="submit" style="border:0" value="<?=(($act=="edit")?"Update":"Create")?> Option" /></p>
			</form>
			<?php
		}
		break;
	default:
		$searched=(isset($post_arr['search']))?$post_arr['search']:"";
		/*if(isset($post_arr['search'])){$where="WHERE `optname` LIKE '%".$post_arr['search']."%'";}else{$where="";}
		$pgnums=pagenums("SELECT * FROM cart_variant_groups $where ORDER BY `optname`",$self,30,5);
		$query=$pgnums[0];
		$optsQ=ysql_query($query,CARTDB);*/
		$binds=array();
		if(isset($post_arr['search'])){$where="WHERE `optname` LIKE ?";$binds[]="%".$post_arr['search']."%";}else{$where="";}
		$pgnums=pagenums("SELECT * FROM cart_variant_groups $where ORDER BY `optname`",$self,30,5,'',$binds);
		$query=$pgnums[0];
		$optsQ=$db1->query($query);
		?><script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<table class="linkslist">
		<tr class="head">
			<td colspan="4"><div class="titles">Variants</div><div class="links"><a href="<?=$self?>&amp;act=add">Add new option</a></div></td>
		</tr>
		<?php if(strlen($pgnums[1])>0){?>
		<tr class="infohead">
			<td colspan="4"><?=$pgnums[1]?></td>
		</tr>
		<?php }?>
		<tr>
			<td colspan="4" style="vertical-align:middle;padding:3px;">
			<div style="float:left"><form action="<?=$self?>" method="post">Search <input type="text" name="search" value="<?=$searched?>" style="vertical-align:middle" /> <input type="submit" name="submit" class="formbutton" value="Search" style="vertical-align:middle" /></form></div>
			<div style="float:right;vertical-align:middle;line-height:200%"><a href="<?=$self?>">View all</a></div>
			</td>
		</tr>
		<tr class="subhead">
			<td style="width:70%">Option title</td>
			<td style="width:10%;text-align:center">Description</td>
			<td style="width:10%;text-align:center">Edit</td>
			<td style="width:10%;text-align:center">Delete</td>
		</tr>
		<?php
		while($opts=mysql_fetch_assoc($optsQ))
		{
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			?>
			<tr class="<?=$row_class?>">
				<td><span><?=$opts['optname']?></span></td>
				<td style="text-align:center"><?=$opts['opttype']?></td>
				<td class="blocklink" style="text-align:center"><a href="<?=$self?>&amp;act=edit&amp;optid=<?=$opts['optid']?>">Edit</a></td>
				<td class="blocklink" style="text-align:center"><a href="javascript:decision('Are you sure you wish to delete this product option and all option values?', '<?=$self?>&amp;act=delete&amp;optid=<?=$opts['optid']?>')">Delete</a></td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
		break;
}
?>
<!-- /CONTENT -->
<?php unset($_SESSION['error']); ?>