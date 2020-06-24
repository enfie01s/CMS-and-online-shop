<?php $postid=isset($get_arr['postid'])?$get_arr['postid']:"";?>
<?php
$founderrors="";
/*................. STATUS UPDATE ...........*/
if(isset($post_arr['status']))
{
	$statuses=bindIns(implode(",",$post_arr['status']));
	cart_query("UPDATE cart_postage SET `status`='1' WHERE `post_id` IN(".$statuses[0].")",$statuses[1]);
	cart_query("UPDATE cart_postage SET `status`='0' WHERE `post_id` NOT IN(".$statuses[0].")",$statuses[1]);
	if(in_array(7,$post_arr['status'])){cart_query("UPDATE cart_postage_details SET `display`='1' WHERE `post_id`='7'");}//show freepost
	else{cart_query("UPDATE cart_postage_details SET `display`='0' WHERE `post_id`='7'");}
}
/*................ POSTAGE & PACKING INFO UPDATE ...............*/
if(isset($post_arr['pdid']))
{
	$fieldstouse=array('5'=>array('description','field1','field2','field3'),'6'=>array('description','field1','field2','field3','restraints'));
	$fieldsrequired=array();
	$fieldsrequired['5']=array('description'=>'Description - Row ','field1'=>'Range start price - Row ','field2'=>'Range end price - Row ','field3'=>'Value - Row ');
	$fieldsrequired['6']=array('description'=>'Description - Row ','field1'=>'First item price - Row ','field2'=>'Each additional item price - Row ');
	
	$founderrors.=cart_emptyfieldscheck($post_arr,$fieldsrequired[$get_arr['postid']]);
	if(strlen($founderrors)>0)
	{
		$_SESSION['error']=$founderrors;
	}
	else
	{
		foreach($post_arr['pdid'] as $row => $pdid)
		{
			if($post_arr['delete'][$pdid]==1)
			{
				cart_query("DELETE FROM cart_postage_details WHERE `post_details_id`=?",array($pdid));
			}
			else
			{
				$rday=strlen($post_arr['restraint2'][$pdid])>0?$post_arr['restraint2'][$pdid]." ":"";
				$rtime=strlen($rday)>0&&strlen($post_arr['restraint3'][$pdid])==0?"00:00":(strlen($post_arr['restraint3'][$pdid])>0?$post_arr['restraint3'][$pdid]:"");
				$restraintstring=strlen($post_arr['restraint2'][$pdid])>0||strlen($post_arr['restraint3'][$pdid])>0?"time#".$post_arr['restraint1'][$pdid]."#".$rday.$rtime:"";
				
				if($pdid=="new")
				{
					$binds=array($get_arr['postid']);
					$query="INSERT INTO cart_postage_details(`post_id`,`";
					$query.=implode("`,`",$fieldstouse[$get_arr['postid']]);//"description,field1,field2,field3,restraints";
					$query.="`)VALUES(?,";
					$fieldvals="";
					foreach($fieldstouse[$get_arr['postid']] as $field)
					{
						if(strlen($fieldvals)>0){$fieldvals.=",";}
						$fieldvals.="?";
						$binds[]=$post_arr[$field][$pdid];
					}
					$query.=$fieldvals;
					$query.=")";
					cart_query($query,$binds);
				}
				else
				{
					$query="UPDATE cart_postage_details SET ";
					$fieldvals="";
					$binds=array();
					foreach($fieldstouse[$get_arr['postid']] as $field)
					{
						if(strlen($fieldvals)>0){$fieldvals.=",";}
						$fieldvals.="`".$field."`=?";
						$binds[]=$field=="restraints"?$restraintstring:$post_arr[$field][$pdid];
					}
					$query.=$fieldvals;
					$query.=" WHERE `post_details_id`=?";
					$binds[]=$pdid;
					//$cart_debugmode=1;
					cart_query($query,$binds);
				}
			}
		}
		//header("Location: $mainbase/admin.php?p=postage&act=edit&postid=".$get_arr['postid']."");
	}
}
/* FORM HANDLING */
?>


<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Postage &amp; Packing</a><?php if($postid!=""){?> <?=SEP?> Editing postage options<?php }?></div>

<?php if(isset($_SESSION['error'])){?><div class="notice"><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }?>

<?php 
switch($act){
	case "edit":		
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<form action="<?=$formaction?>" method="post" name="editform" id="editform" onsubmit="return checkForm('editform')">
		<p class="submittop"><a href="<?=$self?>&amp;act=edit&amp;postid=<?=$postid?>&amp;addrow=1">Add new row</a></p>
		<table class="details">
		<?php
		$titleadded=0;
		$requiredfields="";
		/*$pdetailsq=ysql_query("SELECT * FROM cart_postage as pm LEFT JOIN cart_postage_details as pmd ON  pm.`post_id`=pmd.`post_id` WHERE pm.`post_id`='$postid'",CARTDB);
		while($pdetails=mysql_fetch_assoc($pdetailsq))*/
		$pdetailsq=$db1->prepare("SELECT * FROM cart_postage as pm LEFT JOIN cart_postage_details as pmd ON  pm.`post_id`=pmd.`post_id` WHERE pm.`post_id`=?");
		$pdetailsq->execute(array($postid));
		while($pdetails=$pdetailsq->fetch(PDO::FETCH_ASSOC))
		{
			$pdid=$pdetails['post_details_id'];
			$pdesc=cart_posted_value("description",$pdid,$pdetails['description'],$post_arr);
			$prestraints=cart_posted_value("restraints",$pdid,$pdetails['restraints'],$post_arr);
			$pfield1=cart_posted_value("field1",$pdid,$pdetails['field1'],$post_arr);
			$pfield2=cart_posted_value("field2",$pdid,$pdetails['field2'],$post_arr);
			$pfield3=cart_posted_value("field3",$pdid,$pdetails['field3'],$post_arr);
			if(strlen($requiredfields)>0){$requiredfields.=",";}
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			if($titleadded==0)
			{
				$titleadded=1;
				?>
				<tr class="head">
					<td colspan="6"><div class="titles"><?=ucwords($pdetails['methodname'])?></div></td>
				</tr>
				<tr class="row_dark">
					<td colspan="6"><?=$pdetails['helptext']?></td>
				</tr>
				<tr class="subhead">
					<td style="width:10%">ID</td>
					<td style="width:<?php if($postid==6){?>30<?php }else{?>53<?php }?>%">Description</td>
					<td style="width:<?php if($postid==6){?>35<?php }else{?>12<?php }?>%"><?php if($postid==6){?>Special<?php }else{?>Range Start<?php }?></td>
					<td style="width:10%"><?php if($postid==6){?>First item<?php }else{?>Range End<?php }?></td>
					<td style="width:10%"><?php if($postid==6){?>Each additional<?php }else{?>Value<?php }?></td>
					<td style="width:5%">Delete</td>
				</tr>
				<?php 
			}
			?>
			<tr class="<?=$row_class?>">
				<td><?=$pdetails['post_details_id']?><input type="hidden" name="pdid[]" value="<?=$pdid?>" /></td>
				<td><input type="text" id="description_<?=$pdid?>" name="description[<?=$pdid?>]" value="<?=htmlentities($pdesc,ENT_QUOTES,"UTF-8")?>" class="input_text" <?=cart_highlighterrors($higherr,"description_".$pdid)?> /></td>
				<?php if($postid==6){
					$requiredfields.="description_$pdid,field1_$pdid,field2_$pdid";				
					?>
					<td>Before 
					<?php $rvals=explode("#",$prestraints);$rvaldaytime=stristr($rvals[2]," ")!=null?explode(" ",$rvals[2]):array('0'=>'','1'=>$rvals[2]);?>
					<input type="hidden" name="restraint0[<?=$pdid?>]" value="time" />
					<input type="hidden" name="restraint1[<?=$pdid?>]" value="before" />
					<!--<select name="restraint1[<?//=$pdetails['post_details_id']?>]">
					<option value="">-When-</option>
					<option value="before" <?php //if($rvals[1]=="before"){?>selected="selected"<?php //}?>>Before</option>
					<option value="after" <?php //if($rvals[1]=="after"){?>selected="selected"<?php //}?>>After</option>
					</select>-->
					<select name="restraint2[<?=$pdid?>]">
					<option value="">-Day-</option>
					<?php for($x=0;$x<7;$x++){?><option value="<?=ucwords($daysofweek[$x])?>" <?php if(ucwords($daysofweek[$x])==$rvaldaytime[0]){?>selected="selected"<?php }?>><?=ucwords($daysofweek[$x])?></option><?php }?>
					</select>
					<select name="restraint3[<?=$pdid?>]">
					<option value="">-Time-</option>
					<?php for($x=0;$x<24;$x++){?><option value="<?=($x<9?'0':'').$x?>:00" <?php if(($x<9?'0':'').$x.":00"==$rvaldaytime[1]){?>selected="selected"<?php }?>><?=$x?>:00</option><?php }?>
					</select>
					</td>
					<td style="white-space:nowrap">&#163;<input type="text" id="field1_<?=$pdid?>" name="field1[<?=$pdid?>]" value="<?=$pfield1?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field1_".$pdid)?> /></td>
					<td style="white-space:nowrap">&#163;<input type="text" id="field2_<?=$pdid?>" name="field2[<?=$pdid?>]" value="<?=$pfield2?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field2_".$pdid)?> /><input type="hidden" id="field3_<?=$pdid?>" name="field3[<?=$pdid?>]" value="<?=$pfield1?>" /></td>
				<?php }else{
					$requiredfields.="description_$pdid,field1_$pdid,field2_$pdid,field3_$pdid";?>
					<td style="white-space:nowrap"><dfn>Between</dfn><br />&#163;<input type="text" id="field1_<?=$pdid?>" name="field1[<?=$pdid?>]" value="<?=$pfield1?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field1_".$pdid)?> /></td>
					<td style="white-space:nowrap"><dfn>and</dfn><br />&#163;<input type="text" id="field2_<?=$pdid?>" name="field2[<?=$pdid?>]" value="<?=$pfield2?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field2_".$pdid)?> /></td>
					<td style="white-space:nowrap"><dfn>charge</dfn><br />&#163;<input type="text" id="field3_<?=$pdid?>" name="field3[<?=$pdid?>]" value="<?=$pfield3?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field3_".$pdid)?> /></td>
				<?php }?>
				<td style="text-align:center"><input type="checkbox" name="delete[<?=$pdid?>]" value="1" /></td>
			</tr>
			<?php
		}
		if(isset($get_arr['addrow']))
		{	
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			?>
			<tr class="<?=$row_class?>">
				<td>New type<input type="hidden" name="pdid[]" value="new" /></td>
				<td><input type="text" id="description_new" name="description[new]" value="<?=cart_posted_value("description","new","",$post_arr)?>" class="input_text" <?=cart_highlighterrors($higherr,"description_new")?> /></td>
				<?php if($postid==6){
					$requiredfields.=",description_new,field1_new,field2_new";?>
					<td>Before 
						<input type="hidden" name="restraint0[new]" value="time" />
						<input type="hidden" name="restraint1[new]" value="before" />
						<!--<select name="restraint1[new]">
						<option value="">-When-</option>
						<option value="before">Before</option>
						<option value="after">After</option>
						</select>-->
						<select name="restraint2[new]">
						<option value="">-Day-</option>
						<?php for($x=0;$x<7;$x++){?><option value="<?=ucwords($daysofweek[$x])?>" <?php if(ucwords($daysofweek[$x])==$rvaldaytime[0]){?>selected="selected"<?php }?>><?=ucwords($daysofweek[$x])?></option><?php }?>
						</select>
						<select name="restraint3[new]">
						<option value="">-Time-</option>
						<?php for($x=0;$x<24;$x++){?><option value="<?=($x<9?'0':'').$x?>:00" <?php if(($x<9?'0':'').$x.":00"==$rvaldaytime[1]){?>selected="selected"<?php }?>><?=$x?>:00</option><?php }?>
						</select>
					</td>
					<td>&#163;<input type="text" id="field1_new" name="field1[new]" value="<?=cart_posted_value("field1","new","",$post_arr)?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field1_new")?> /></td>
					<td>&#163;<input type="text" id="field2_new" name="field2[new]" value="<?=cart_posted_value("field2","new","",$post_arr)?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field2_new")?> /></td>
				<?php }else{
					$requiredfields.=",description_new,field1_new,field2_new,field3_new";?>
					<td style="white-space:nowrap"><dfn>Between</dfn><br />&#163;<input type="text" id="field1_new" name="field1[new]" value="<?=cart_posted_value("field1","new","",$post_arr)?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field1_new")?> /></td>
					<td style="white-space:nowrap"><dfn>and</dfn><br />&#163;<input type="text" id="field2_new" name="field2[new]" value="<?=cart_posted_value("field2","new","",$post_arr)?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field2_new")?> /></td>
					<td style="white-space:nowrap"><dfn>charge</dfn><br />&#163;<input type="text" id="field3_new" name="field3[new]" value="<?=cart_posted_value("field3","new","",$post_arr)?>" class="input_text_med" <?=cart_highlighterrors($higherr,"field3_new")?> /></td>
				<?php }?>
				<td>&#160;</td>
			</tr>
			<?php
		}
		?>
		</table>
		<input type="hidden" name="required" value="<?=$requiredfields?>" />
		<p class="submit"><input type="submit" value="Save Changes" /></p>
		</form>
		<?php
		break;
	default:
		$status=array();
		/*$ppq=ysql_query("SELECT `post_id`,`status` FROM cart_postage WHERE `post_id` IN('5','6','7')",CARTDB);
		while($pp=mysql_fetch_row($ppq))*/
		$ppq=$db1->query("SELECT `post_id`,`status` FROM cart_postage WHERE `post_id` IN('5','6','7')");
		while($pp=$ppq->fetch(PDO::FETCH_NUM))
		{$status[$pp[0]]=$pp[1];}
		//print_r($post_arr['status']);
		?>
		<form action="<?=$formaction?>" method="post">
		<table class="linkslist">
			<tr class="head">
				<td colspan="3"><div class="titles">Postage &amp; Packing</div></td>
			</tr>
			<tr class="subhead">
				<td>Status</td>
				<td>Postage method</td>
				<td>Description</td>
			</tr>
			<tr class="row_light">
				<td><span><input type="radio" name="status[]" value="5" <?=$status[5]==1?"checked='checked'":""?> /></span></td>
				<td class="blocklink"><a href="<?=$self?>&amp;act=edit&amp;postid=5">Postal Range</a></td>
				<td><span>Postage charge based on range of total order</span></td>
			</tr>
			<tr class="row_dark">
				<td><span><input type="radio" name="status[]" value="7" <?=$status[7]==1?"checked='checked'":""?> /></span></td>
				<td><span>No Postage</span></td>
				<td><span>All orders will have Free postage</span></td>
			</tr>
			<tr class="subhead">
				<td colspan="3">Postage options below can work in conjunction with any method above</td>
			</tr>
			<tr class="row_light">
				<td><span><input type="checkbox" name="status[]" value="6" <?=$status[6]==1?"checked='checked'":""?> /></span></td>
				<td class="blocklink"><a href="<?=$self?>&amp;act=edit&amp;postid=6">Special Rate</a></td>
				<td><span>Setup special rates which users can select during checkout</span></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" value="Apply Changes" /></p>
		</form>
		<?php
		break;
}?>