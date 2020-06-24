<?php 
$theaid=isset($get_arr['auid'])?$get_arr['auid']:"";
/* form handling */
if(isset($post_arr['username']))
{
	$founderrors="";
	$arequire=array("username"=>"Please enter a user name","email"=>"Please enter an email address");
	if($act=="add"){
		$arequire['password']="Please enter a password";
		/*$dupemails=ysql_query("SELECT `".AFIELDEMAIL."` as email FROM ".ATABLE." WHERE `".AFIELDEMAIL."`='$post_arr[email]'");
		$dupemail=mysql_num_rows($dupemails);*/
		$dupemails=$db1->prepare("SELECT `".AFIELDEMAIL."` as email FROM ".ATABLE." WHERE `".AFIELDEMAIL."`=?");
		$dupemails->execute(array($post_arr['email']));
		$dupemail=$dupemails->rowCount();
		if($dupemail>0){$founderrors.="A user already exists with this email address ($post_arr[email]).";}
		/*$dupeusers=ysql_query("SELECT `".AFIELDNAME."` as username FROM ".ATABLE." WHERE `".AFIELDNAME."`='$post_arr[user]'");
		$dupeuser=mysql_num_rows($dupeusers);*/		
		$dupeusers=$db1->prepare("SELECT `".AFIELDNAME."` as username FROM ".ATABLE." WHERE `".AFIELDNAME."`=?");
		$dupeusers->execute(array($post_arr['user']));
		$dupeuser=$dupeusers->rowCount();
		if($dupeuser>0){$founderrors.="A user already exists with this username ($post_arr[user]).";}
	}
	$founderrors.=cart_emptyfieldscheck($post_arr,$arequire);
	$founderrors.=cart_validemail($post_arr['email'],"email");
	if(strlen($founderrors)>0)
	{
		$_SESSION['error']=$founderrors;
	}
	else
	{
		extract($post_arr);
		$binds=array();
		$binds[]=$username;
		$binds[]=$email;
		$binds[]=$date;
		$binds1=array($username,$email);
		if(strlen($password)>0){
			$firstwave=cart_hashandsalt($username,$password);
			$apass="*".cart_hashandsalt($username,$firstwave);
		}
		$passcol=(strlen($password)>0)?",".AFIELDPASS:"";
		$passequal =(strlen($password)>0)?"=":"";
		$passcomma =(strlen($password)>0)?",":"";
		if(strlen($password)>0){$passval="?";$binds[]=$apass;$binds1[]=$apass;}else{$passval="";}
		$permissions="";
		foreach($amods as $perm => $yesno){if($yesno==1){if(strlen($permissions)>0){$permissions.=",";}$permissions.=$perm;}}
		if($act=="add"){
			cart_query("INSERT INTO ".ATABLE."(".AFIELDNAME.",".AFIELDEMAIL.",".AFIELDCREATE." $passcol)VALUES(?,?,? $passcomma $passval)",$binds);
			$theaid=$db1->lastInsertId();
			cart_query("INSERT INTO cart_admin_permissions(user_id,permissions)VALUES(?,?)",array($theaid,$permissions));
			$act="edit";
		}
		else {
			$binds1[]=$theaid;
			cart_query("UPDATE ".ATABLE." SET ".AFIELDNAME."=?,".AFIELDEMAIL."=? $passcol $passequal $passval WHERE ".AFIELDID."=?",$binds1);
			cart_query("UPDATE cart_admin_permissions SET permissions=? WHERE user_id=?",array($permissions,$theaid));
		}
	}
}
else if($act=="delete"&&isset($get_arr['auid']))
{
	cart_query("DELETE FROM ".ATABLE." WHERE `".AFIELDID."`=?",array($get_arr['auid']));
	cart_query("DELETE FROM cart_admin_permissions WHERE `user_id`=?",array($get_arr['auid']));
}
/* form handling */
?><div id="bread"><a href="index.php">Home</a> &raquo; <a href="<?=$self?>">Administrators</a></div><?php 

if(isset($_SESSION['error'])){?><div class="notice"><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }

switch($act)
{
	case "edit":
	case "add":
		if($act=="edit"){
			/*$admins=ysql_query("SELECT ap.`permissions` as permissions,a.`".AFIELDNAME."` as username,a.`".AFIELDID."` as admin_id,a.`".AFIELDPASS."` as password,a.`".AFIELDEMAIL."` as email FROM ".ATABLE." as a JOIN cart_admin_permissions as ap ON a.`".AFIELDID."`=ap.`user_id` WHERE `".AFIELDID."`='$theaid'",CARTDB);
			$admin=mysql_fetch_assoc($admins);*/
			$admins=$db1->prepare("SELECT ap.`permissions` as permissions,a.`".AFIELDNAME."` as username,a.`".AFIELDID."` as admin_id,a.`".AFIELDPASS."` as password,a.`".AFIELDEMAIL."` as email FROM ".ATABLE." as a JOIN cart_admin_permissions as ap ON a.`".AFIELDID."`=ap.`user_id` WHERE `".AFIELDID."`=?");
			$admins->execute(array($theaid));
			$admin=$admins->fetch(PDO::FETCH_ASSOC);
			$auths=explode(",",$admin['permissions']);
			$data=isset($post_arr['username'])?$post_arr:$admin;
		}else{
			$auths=isset($post_arr['amods'])?array_keys($post_arr['amods']):array();
			$data=isset($post_arr['username'])?$post_arr:array();
		}
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<form action="<?=$self?>&amp;act=<?=$act?><?php if($act=="edit"){?>&amp;auid=<?=$theaid?><?php }?>" method="post" name="editform" id="editform" onsubmit="return checkForm('editform')">
		<input type="hidden" name="required" value="username,email<?=$act=="add"?",password":""?>" />
		<table class="details">
			<tr class="head">
				<td colspan="2"><div class="titles"><?=$act=="add"?"Add":"Edit"?> Administrator <?=$act!="add"?cart_posted_value("username","","",$data):""?></div></td>
			</tr>
			<tr>
				<td class="first left_light"><label for="username">Username <span>*</span></label></td>
				<td class="right_light"><input type="text" name="username" id="username" value="<?=cart_posted_value("username","","",$data)?>" class="input_text" <?=cart_highlighterrors($higherr,"username")?> required /></td>
			</tr>
			<tr>
				<td class="left_dark"><label for="password">Password<?=$act=="add"?" <span>*</span>":""?></label></td>
				<td class="right_dark"><input type="password" name="password" id="password" value="" class="input_text" <?=cart_highlighterrors($higherr,"password")?><?=$act=="add"?" required":""?> /></td>
			</tr>
			<tr>
				<td class="left_light"><label for="email">Email <span>*</span></label></td>
				<td class="right_light"><input type="email" name="email" id="email" value="<?=cart_posted_value("email","","",$data)?>" class="input_text" <?=cart_highlighterrors($higherr,"email")?> required /></td>
			</tr>
			<tr class="subhead">
				<td colspan="2">Authorisations</td>
			</tr>
			<tr class='row_light'>
			<?php 
			$x=1;
			$totalmodules=count($modules)-1;
			$tdremainder=$totalmodules%2;
			//$col_class="_light";
			foreach($modules as $modid => $modname)
			{
				if($modid>0)
				{
					$bdr_l=!isset($bdr_l)||$bdr_l==0?"1px":0;
					$bdr_r=!isset($bdr_r)||$bdr_r=="1px"?0:"1px";
					$bdr_b=($x>=$totalmodules && $tdremainder==1) || ($x>=$totalmodules-1 && $tdremainder==0)?"1px":0;
					?>
					<td style="border-width:0 <?=$bdr_r?> <?=$bdr_b?> <?=$bdr_l?>;">
					<div style="float:left"><?=$modname?></div>
					<div style="float:right"><label for="amods1[<?=$modid?>]" class="yes"><input type="radio" name="amods[<?=$modid?>]" id="amods1[<?=$modid?>]" value="1" <?=in_array($modid,$auths)?"checked='checked'":""?> /> Yes</label><label for="amods0[<?=$modid?>]" class="no"><input type="radio" name="amods[<?=$modid?>]" id="amods0[<?=$modid?>]" value="0" <?=in_array($modid,$auths)?"":"checked='checked'"?> /> No</label></div>
					</td>
					<?php 
					if($x==$totalmodules&&$tdremainder==1){
					echo "<td style='border-width:0 1px 1px 0;'></td>";
					}
					if($modid%2==0&&$x<$totalmodules){$col_class=!isset($col_class)||$col_class=="_light"?"_dark":"_light";echo "</tr><tr class='row".$col_class."'>";}
					$x++;
				}
			}?>
			</tr>
		</table>
		<p class="submit"><input type="submit" value="<?=$act=="add"?"Add":"Update"?> account" /></p>
		</form>
		<?php
		break;
	default:
		/*$admins=ysql_query("SELECT `".AFIELDNAME."` as username,`".AFIELDID."` as admin_id,`".AFIELDCREATE."` as date_created,`".AFIELDLASTIN."` as date_lastin,`".AFIELDSUPER."` as super_admin FROM ".ATABLE." ORDER BY `date_lastin` DESC",CARTDB);
		$adminnum=mysql_num_rows($admins);*/
		$admins=$db1->query("SELECT `".AFIELDNAME."` as username,`".AFIELDID."` as admin_id,`".AFIELDCREATE."` as date_created,`".AFIELDLASTIN."` as date_lastin,`".AFIELDSUPER."` as super_admin FROM ".ATABLE." ORDER BY `date_lastin` DESC");		
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<p class="submittop"><a href="<?=$self?>&amp;act=add">Add new user</a></p>
		<table class="linkslist">
			<tr class="head">
				<td colspan="5"><div class="titles">Listing <?=$admins->rowCount()?> Administrators</div></td>
			</tr>
			<tr class="subhead">
				<td style="width:53%">Username</td>
				<td style="width:20%">Signup date</td>
				<td style="width:20%">Last Seen</td>
				<td style="width:7%;text-align:center;"></td>
			</tr>
			<?php while($admin=$admins->fetch(PDO::FETCH_ASSOC)){$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";?>
			<tr class="<?=$row_class?>">
				<td><a href="<?=$self?>&amp;act=edit&amp;auid=<?=$admin['admin_id']?>"><?=$admin['username']?></a></td>
				<td><span><?=date("F d, Y",$admin['date_created'])?></span></td>
				<td><span><?=date("F d, Y G:i",$admin['date_lastin'])?></span></td>
				<td class="blocklink" style="text-align:center;">
					<a href="<?=$self?>&amp;act=edit&amp;auid=<?=$admin['admin_id']?>"><img src="img/edit.png" alt="Edit" /></a>
					<?php if($admin['super_admin']!=1){?>
						<a href="javascript:decision('Are you sure you wish to delete this administrator?', '<?=$self?>&amp;act=delete&amp;auid=<?=$admin['admin_id']?>')"><img src="img/delete.png" alt="X" /></a>
					<?php }else{?>
						<img src="img/delete.png" alt="Super Admin cannot be deleted" style="filter:alpha(opacity=30);opacity:0.3;" />
					<?php }?>
				</td>
			</tr>
			<?php }?>
		</table>
		<?php 
		break;
}?>
		