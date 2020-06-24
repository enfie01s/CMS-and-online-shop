<?php
$basefolder=basename(dirname($_SERVER['PHP_SELF']));
if($basefolder!="admin"){?>
<script type="text/javascript">window.location.href="index.php";</script>
<noscript><meta http-equiv="refresh" content="0;url=index.php" /></noscript>
<?php }
if((isset($post_arr['formid'])&&isset($post_arr['updatediscount']))||isset($get_arr['delete_id'])||$post_arr['formid']=="stateupdate")
{
	$founderrors="";
	if(isset($get_arr['delete_id'])){cart_query("DELETE FROM cart_discounts WHERE discount_id=?",array($get_arr['delete_id']));}
	if($post_arr['formid']=="add"||$post_arr['formid']=="edit")
	{
		$fields=explode(",",$post_arr['requiredf']);
		$values=explode(",",$post_arr['requiredv']);
		$required=array_combine($fields,$values);
		$founderrors.=cart_emptyfieldscheck($post_arr,$required);
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		else
		{
			$start=explode("-",$post_arr['date_start']);
			$start_date=mktime(0,0,0,$start[1],$start[2],$start[0]);
			$end=explode("-",$post_arr['date_end']);
			$end_date=mktime(0,0,0,$end[1],$end[2],$end[0]);
			$onoff=$post_arr['state']==1?1:0;
			if($post_arr['formid']=="add")
			{
				$add=$db1->prepare("INSERT INTO cart_discounts(`code`,`discount`,`uselist`,`prodlist`,`date_start`,`date_end`,`date_created`,`state`,`mintotal`,`admin_id`)VALUES(?,?,?,?,?,?,'".date("U")."',?,?,?)");
				$add->execute(array($post_arr['code'],$post_arr['discount'],$post_arr['uselist'],$post_arr['prodlist'],$start_date,$end_date,$onoff,$post_arr['mintotal'],$post_arr['admin_id']));
				//header("Location: $mainbase/admin.php?p=promotions");
			}
			else
			{
				$edit=$db1->prepare("UPDATE cart_discounts SET `code`=?,`discount`=?,`date_start`=?,`date_end`=?,`date_edited`='".date("U")."',`state`=?,`admin_id`=?, `uselist`=?, `prodlist`=?, `mintotal`=? WHERE `discount_id`=?");
				$edit->execute(array($post_arr['code'],$post_arr['discount'],$start_date,$end_date,$onoff,$post_arr['admin_id'],$post_arr['uselist'],$post_arr['prodlist'],$post_arr['mintotal'],$get_arr['discount_id']));
				//header("Location: $mainbase/admin.php?p=promotions");
			}
		}
	}
	else if($post_arr['formid']=="stateupdate")
	{
		$statuseson=bindIns(implode(",",array_keys($post_arr['state'],'1')));
		$bindson=array($post_arr['admin_id']);
		$bindson=array_merge($bindson,$statuseson[1]);
		$statusesoff=bindIns(implode(",",array_keys($post_arr['state'],'0')));
		$bindsoff=array($post_arr['admin_id']);
		$bindsoff=array_merge($bindsoff,$statusesoff[1]);
		if(strlen($statuseson[0])>0)
		{$edit=$db1->prepare("UPDATE cart_discounts SET `state`='1',`admin_id`=? WHERE `discount_id` IN(".$statuseson[0].")");$edit->execute($bindson);}
		if(strlen($statusesoff[0])>0)
		{$edit=$db1->prepare("UPDATE cart_discounts SET `state`='0',`admin_id`=? WHERE `discount_id` IN(".$statusesoff[0].")");$edit->execute($bindsoff);	}
	}
}

?><div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Promotions</a></div><?php

if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }

switch($act){
	case "add":
	case "edit":
		//$query=ysql_query("SELECT * FROM cart_discounts WHERE `discount_id`='".$get_arr['discount_id']."'",CARTDB);$result=mysql_fetch_assoc($query);
		$query=$db1->prepare("SELECT * FROM cart_discounts WHERE `discount_id`=?");
		$query->execute(array($get_arr['discount_id']));
		$result=$query->fetch(PDO::FETCH_ASSOC);
		if(!isset($_SESSION['promotions'])){$_SESSION['promotions']=array();}
		if(!isset($_SESSION['promotions']['postdata'])||isset($post_arr['formid'])){$_SESSION['promotions']['postdata']=isset($post_arr['formid'])?$post_arr:$result;}
		
		$postdata=$_SESSION['promotions']['postdata'];
		$code=cart_posted_value("code","","",$postdata);
		$discount=cart_posted_value("discount","","",$postdata);
		$mintotal=cart_posted_value("mintotal","","0",$postdata);
		$prodlist=cart_posted_value("prodlist","","",$postdata);//1,34,28
		if(!isset($_SESSION['promotions']['items'])){$_SESSION['promotions']['items']=strlen($prodlist)>0?explode(",",$prodlist):array();}
		$startraw=cart_posted_value("date_start","",date("U"),$postdata);//not posted - result in unix, default in unix
		$start=isset($postdata['formid'])?$startraw:date("Y-m-d",$startraw);
		$endraw=cart_posted_value("date_end","",date("U"),$postdata);
		$end=isset($postdata['formid'])?$endraw:date("Y-m-d",$endraw);
		$state=$act=="edit"||isset($postdata['formid'])?cart_is_selected("state","","1",$postdata,"check"):"";
		$uselist=$act=="edit"||isset($postdata['formid'])?cart_is_selected("uselist","","1",$postdata,"check"):"";
		$owned=!isset($post_arr['dept'])||isset($post_arr['backtodept_x'])?0:$post_arr['dept'];
		if(isset($post_arr['prodpop_x'])&&count($post_arr['list'])>0)
		{
			foreach($post_arr['list'] as $pop)
			{
				array_splice($_SESSION['promotions']['items'],array_search($pop,$_SESSION['promotions']['items']),1);
			}
		}
		else if(isset($post_arr['item']))
		{
			foreach($post_arr['item'] as $prod){
				array_push($_SESSION['promotions']['items'],$prod);$_SESSION['promotions']['items']=array_unique($_SESSION['promotions']['items']);
			}
		}
		sort($_SESSION['promotions']['items']);
		?><script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<form action="<?=$self."&amp;act=".$act.(isset($get_arr['discount_id'])?"&amp;discount_id=".$get_arr['discount_id']."":"")?>" method="post" name="editform" id="editform" onsubmit="return checkForm('editform')">
		<input type="hidden" name="formid" value="<?=$act?>" />
		<input type="hidden" name="admin_id" value="<?=$uaa['admin_id']?>" />
		<input type="hidden" name="required" value="code,discount,date_start,date_end" />
		<table class="details">
			<tr class="head">
				<td colspan="2"><div class="titles"><?=ucwords($act)?>ing Promotion</div></td>
			</tr>
			<tr>
				<td class="left_light">Discount code</td>
				<td class="right_light"><input type="text" name="code" value="<?=$code?>" class="formfield" <?=cart_highlighterrors($higherr,"code")?> /></td>
			</tr>
			<tr>
				<td class="left_dark">Discount value</td>
				<td class="right_dark"><input type="text" name="discount" value="<?=$discount?>" class="formfields" <?=cart_highlighterrors($higherr,"discount")?> />%</td>
			</tr>
			<tr>
				<td class="left_light">Minimum order</td>
				<td class="right_light">&#163;<input type="text" name="mintotal" value="<?=$mintotal?>" class="formfields" <?=highlighterrors($higherr,"mintotal")?> /></td>
			</tr>
			<tr>
				<td class="left_dark">Start date <dfn>(YYYY-MM-DD)</dfn></td>
				<td class="right_dark"><input type="date" name="date_start" value="<?=$start?>" class="formfieldm" min="<?=date("Y-m-d",strtotime("1 year ago"))?>" <?=cart_highlighterrors($higherr,"date_start")?> /></td>
			</tr>
			<tr>
				<td class="left_light">End date <dfn>(YYYY-MM-DD)</dfn></td>
				<td class="right_light"><input type="date" name="date_end" value="<?=$end?>" class="formfieldm" <?=cart_highlighterrors($higherr,"date_end")?> /></td>
			</tr>
			<tr>
				<td class="left_dark">Status</td>
				<td class="right_dark">			
				<label for="state1" class="yes"><input type="radio" name="state" id="state1" value="1" <?=$state?> /> On</label><label for="state0" class="no"><input type="radio" name="state" id="state0" value="0" <?=$state=="checked='checked'"?"":"checked='checked'"?> /> Off</label>
				</td>
			</tr>
			<tr>
				<td class="left_light">Use products list <dfn>(Defaults to global if list is empty)</dfn></td>
				<td class="right_light">
				<label for="uselist1" class="yes"><input type="radio" name="uselist" id="uselist1" value="1" <?=$uselist?> /> Yes</label><label for="uselist0" class="no"><input type="radio" name="uselist" id="uselist0" value="0" <?=$uselist=="checked='checked'"?"":"checked='checked'"?> /> No</label>
				</td>
			</tr>
			<tr class="subhead">
				<td style="text-align:center"><?=!isset($post_arr['dept'])||isset($post_arr['backtodept_x'])?"Departments":"Products"?></td>
				<td style="text-align:center">Selected products list</td>
			</tr>
			<tr>
				<td class="left_light" style="text-align:center">
				<?php if(!isset($post_arr['dept'])||isset($post_arr['backtodept_x'])){
					$deptidlist=array();
					/*$deptidsQ=ysql_query("SELECT `fusionId`,`ownerId`,`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM ((".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` AND `excludediscount`!='1' AND `allowpurchase`='1') JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND `ownerType`='category' AND `itemType`='product') LEFT JOIN ".CTABLE." as c ON f.`ownerId`=c.`".CFIELDID."` GROUP BY `ownerId` ORDER BY `ownerId`",CARTDB) or die(sql_error("Error"));
					while($deptids=mysql_fetch_assoc($deptidsQ))*/
					$binds=array();
					$deptidsQ=$db1->query("SELECT `fusionId`,`ownerId`,`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM ((".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` AND `excludediscount`!='1' AND `allowpurchase`='1') JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND `ownerType`='category' AND `itemType`='product') LEFT JOIN ".CTABLE." as c ON f.`ownerId`=c.`".CFIELDID."` GROUP BY `ownerId` ORDER BY `ownerId`");
					while($deptids=$deptidsQ->fetch(PDO::FETCH_ASSOC))
					{
						$deptidlist[]="?";
						$binds[]=$deptids['ownerId'];
					}
					/*$deptsQ=ysql_query("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM (".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category') JOIN cart_catopts as co ON co.`cat_id`=c.`".CFIELDID."` WHERE c.`".CFIELDID."` IN('".implode("','",$deptidlist)."') AND co.`showincart`='1' ORDER BY `ownerId`,".CFIELDID,CARTDB) or die(sql_error("Error"));*/
					$deptsQ=$db1->prepare("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM (".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category') JOIN cart_catopts as co ON co.`cat_id`=c.`".CFIELDID."` WHERE c.`".CFIELDID."` IN(".implode(",",$deptidlist).") AND co.`showincart`='1' ORDER BY `ownerId`,".CFIELDID);
					$deptsQ->execute($binds);
					?>
						<select name="dept" style="width:350px" size="10">
							<?php if(in_array('0',$deptidlist)){?><option value="0" <?php if($owned==0){?>selected="selected"<?php }?>>Home Page</option><?php }?>
							<?php
							//while($depts=mysql_fetch_assoc($deptsQ))
							while($depts=$deptsQ->fetch(PDO::FETCH_ASSOC))
							{	
								if($depts['ownerId']!=0){
									$parentsgot=cart_getparents($depts['ownerId']," / ");
									if($par != $parentsgot){if(strlen($par)>0){?></optgroup><?php }?>
									<optgroup label="<?=$parentsgot?>"><?php $par = $parentsgot;} 
								}
								?>
								<option value="<?=$depts['cat_id']?>" <?php if($owned==$depts['cat_id']){?>selected="selected"<?php }?>>
								<?=ucwords($depts['title'])?></option><?php
								
							}
							?>
						</select>
						<p class="submit"><input type="submit" name="submitdept" value="View Items" style="border:0" /></p>
									
				<?php }else{
				
					/*$catparentq=ysql_query("SELECT `fusionId`,`itemId`,`ownerId`,c.`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' WHERE `".CFIELDID."`='$post_arr[dept]'",CARTDB);
					$catparent=mysql_fetch_assoc($catparentq);
						$itemsQ=ysql_query("SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title FROM (".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` AND `excludediscount`='0' AND `allowpurchase`='1') JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='category' WHERE `ownerId`='$catparent[itemId]' AND `itemId` NOT IN('".implode("','",$_SESSION['promotions']['items'])."') ORDER BY p.`".PFIELDNAME."`",CARTDB);*/
					$catparentq=$db1->prepare("SELECT `fusionId`,`itemId`,`ownerId`,c.`".CFIELDID."` as cat_id,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' WHERE `".CFIELDID."`=?");
					$catparentq->execute(array($post_arr['dept']));
					$catparent=$catparentq->fetch(PDO::FETCH_ASSOC);
					$binds=array();
					$binds[]=$catparent['itemId'];
					$ins=bindIns(implode(",",$_SESSION['promotions']['items']));
					$itemsQ=$db1->prepare("SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title FROM (".PTABLE." as p JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` AND `excludediscount`='0' AND `allowpurchase`='1') JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='category' WHERE `ownerId`=? AND `itemId` NOT IN(".$ins[0].") ORDER BY p.`".PFIELDNAME."`");
					$binds=array_merge($binds,$ins[1]);
					$itemsQ->execute($binds);
					?>
						<?php if($owned!=0){?><input type="hidden" name="dept" value="<?=$owned?>" /><?php }?>
						<select name="item[]" style="width:350px;margin:auto" multiple="multiple" size="10">
						<optgroup label="<?php if($catparent['ownerId']!=0){echo cart_getparents($catparent['ownerId']," / ")." / "; }?><?=ucwords($catparent['title'])?>">
						<?php
						//while($items=mysql_fetch_assoc($itemsQ))
						while($items=$itemsQ->fetch(PDO::FETCH_ASSOC))
						{
							?>
							<option value="<?=$items['prod_id']?>"><?=$items['title']?></option>
							<?php
						}
						?>
						</optgroup>
						</select>
						<p class="submit"><input type="submit" name="backtodept" value="&#60;&#60; Back" style="border:0" /> <input type="submit" name="submitdept" value="Add &#62;&#62;" style="border:0" /></p>
				<?php }?>
				</td>
				<td class="right_light" style="vertical-align:top;text-align:center">
					<?php
					//$prods=isset($_SESSION['promotions']['items'])?implode("','",$_SESSION['promotions']['items']):array();
					$prods=isset($_SESSION['promotions']['items'])?implode(",",$_SESSION['promotions']['items']):"";
					$ins=bindIns($prods);
					?>
					<?php if($owned!=0){?><input type="hidden" name="dept" value="<?=$owned?>" /><?php }?>
					<select name="list[]" style="width:350px" multiple="multiple" size="10">
					<?php
					/*$selectedq=ysql_query("SELECT `".PFIELDNAME."` as title,`".PFIELDID."` as prod_id FROM ".PTABLE." WHERE `".PFIELDID."` IN('$prods')",CARTDB);
					while($selected=mysql_fetch_assoc($selectedq))*/
					$selectedq=$db1->prepare("SELECT `".PFIELDNAME."` as title,`".PFIELDID."` as prod_id FROM ".PTABLE." WHERE `".PFIELDID."` IN(".$ins[0].")");
					$selectedq->execute($ins[1]);
					while($selected=$selectedq->fetch(PDO::FETCH_ASSOC))
					{
						?><option value="<?=$selected['prod_id']?>"><?=$selected['title']?></option><?php
					}
					?>
					</select>
					<p class="submit"><input type="submit" name="prodpop" value="Remove from list" style="border:0" /></p>
					
					<input type="hidden" name="prodlist" value="<?=implode(",",$_SESSION['promotions']['items'])?>" />
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="updatediscount" value="<?=$act=="edit"?"Update":"Add"?> discount" style="border:0" /></p>
		</form>
		<?php
		break;
	case "orders":
		/*$query=ysql_query("SELECT *,co.`firstname`,co.`lastname` FROM cart_orders as co LEFT JOIN cart_customers as cc USING(`cust_id`) WHERE `discount_code`='$get_arr[code]'",CARTDB);
		$num=mysql_num_rows($query);*/
		$query=$db1->prepare("SELECT *,co.`firstname`,co.`lastname` FROM cart_orders as co LEFT JOIN cart_customers as cc USING(`cust_id`) WHERE `discount_code`=?");
		$query->execute(array($get_arr['code']));
		$num=$query->rowCount();
		?>
		<table class="linkslist">
		<tr class="head">
			<td colspan="5"><div class="titles">Orders Using Promotion <?=$get_arr['code']?></div></td>
		</tr>
		<tr class="infohead">
			<td colspan="5"><?=$num?> record<?=$num==1?"":"s"?> found</td>
		</tr>
		<tr class="subhead">
			<td style="width:10%;text-align:center">Order ID</td>
			<td style="width:10%;text-align:center">Date ordered</td>
			<td style="width:60%">Customer</td>
			<td style="width:10%;text-align:center">Discount</td>
			<td style="width:10%;text-align:center">Order Total</td>
		</tr>
		<?php 
		if($num>0)
		{
			//while($result=mysql_fetch_assoc($query))
			while($result=$query->fetch(PDO::FETCH_ASSOC))
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink" style="text-align:center"><a href="index.php?p=cart_invoices&amp;act=view&amp;invoice=<?=$result['invoice']?>"><?=$result['order_id']?></td>
					<td style="text-align:center"><?=date("d/m/Y",$result['date_ordered'])?></td>
					<?php if(strlen($result['cust_id'])>0&&$result['cust_id']>0){?>
						<td class="blocklink"><a href="index.php?p=cart_customers&amp;act=view&amp;cust_id=<?=$result['cust_id']?>">
					<?php }else{?>
						<td><span>
					<?php }?>
					<?=ucwords($result['firstname']." ".$result['lastname'])?>
					<?php if(strlen($result['cust_id'])>0&&$result['cust_id']>0){?>
						</a>
					<?php }else{?>
						</span>
					<?php }?>
					</td>
					<td style="text-align:center"><?=$result['discount']?>%</td>
					<td style="text-align:center">&#163;<?=number_format($result['total_price'],2)?></td>
				</tr>
				<?php
			}
			?></table><?php
		}
		break;
	default:
		/*$query=ysql_query("SELECT count(`order_id`) as orders,`date_start`,`date_end`,`date_created`,d.`discount_id`,d.`state`,`code`,d.`discount` FROM cart_discounts as d LEFT JOIN cart_orders as c ON `discount_code`=`code` AND c.`discount`=d.`discount` GROUP BY `discount_id` ORDER BY `date_end` ASC",CARTDB);						
		$num=mysql_num_rows($query);*/
		$query=$db1->query("SELECT count(`order_id`) as orders,`date_start`,`date_end`,`date_created`,d.`discount_id`,d.`state`,`code`,d.`discount` FROM cart_discounts as d LEFT JOIN cart_orders as c ON `discount_code`=`code` AND c.`discount`=d.`discount` GROUP BY `discount_id` ORDER BY `date_end` ASC");						
		$num=$query->rowCount();
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<form action="<?=$self?>" method="post">
		<input type="hidden" name="formid" value="stateupdate" />
		<input type="hidden" name="admin_id" value="<?=$uaa['admin_id']?>" />
		<p class="submittop"><a href="<?=$self?>&amp;act=add">Add new discount</a></p>
		<table class="linkslist">
			<tr class="head">
				<td colspan="8"><div class="titles">Promotions</div></td>
			</tr>
			<?php 
			if($num>0)
			{?>
				<tr class="subhead">
					<td style="width:25%">Code</td>
					<td style="width:10%;text-align:center">Discount</td>
					<td style="width:10%;text-align:center">Times Used</td>
					<td style="width:10%;text-align:center">Start</td>
					<td style="width:10%;text-align:center">End</td>
					<td style="width:10%;text-align:center">Build Date</td>
					<td style="width:5%;text-align:center">Active</td>
					<td style="width:5%;text-align:center"></td>
				</tr>
				<?php
				//while($result=mysql_fetch_assoc($query))
				while($result=$query->fetch(PDO::FETCH_ASSOC))
				{
					$row_class=!isset($row_class)||$row_class=="row_light"?"row_dark":"row_light";
					?>
					<tr class="<?=$row_class?>">
						<td><span><?=$result['code']?></span></td>
						<td style="text-align:center"><span><?=$result['discount']?>%</span></td>
						<td class="blocklink" style="text-align:center"><a href="<?=$self?>&amp;act=orders&amp;code=<?=$result['code']?>"><?=$result['orders']?></a></td>
						<td style="text-align:center"><span><?=date("d/m/Y",$result['date_start'])?></span></td>
						<td style="text-align:center"><span><?=date("d/m/Y",$result['date_end'])?></span></td>
						<td style="text-align:center"><span><?=date("d/m/Y",$result['date_created'])?></span></td>
						<td style="text-align:center"><span><input type="hidden" name="state[<?=$result['discount_id']?>]" value="0" /><input type="checkbox" name="state[<?=$result['discount_id']?>]" value="1" <?php if($result['state']==1){?>checked="checked"<?php }?> /></span></td>
						<td class="blocklink" style="text-align:center;white-space:nowrap"><a href="<?=$self?>&amp;act=edit&amp;discount_id=<?=$result['discount_id']?>"><img src="img/edit.png" alt="Edit" /></a> <a href="javascript:decision('Are you sure you wish to delete this promotion?', '<?=$self?>&amp;delete_id=<?=$result['discount_id']?>')"><img src="img/delete.png" alt="Delete" /></a></td>
					</tr>
					<?php
				}
			}else{
				?>
				<tr class="row_dark"><td colspan="8">No current promotions</td></tr>
				<?php
			}
			?>
		</table>
		<?php if($num>0){?><p class="submit"><input type="submit" value="Update status" style="border:0" /></p><?php }?>
		</form>
		<?php
		break;
}
?>
<script type="text/javascript" src="<?=$cart_path?>/jquery.tools.min.js"></script>
<script type="text/javascript">if($(":date")!==null){$(":date").dateinput({format: 'yyyy-mm-dd',selectors: true});}</script>