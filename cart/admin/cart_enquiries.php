<?php
$contactid=isset($get_arr['eid'])?$get_arr['eid']:"";
if(($act=="delete" && isset($get_arr['eid']))||(isset($post_arr['delenq'])&&count($post_arr['delenq'])>0))
{
	$dels=$act=="delete"?array($get_arr['eid']):array_keys($post_arr['delenq']);
	$del_ins=bindIns(implode(",",$dels));
	cart_query("DELETE FROM cart_contactus WHERE contactus_id IN(".$del_ins[0].")",$del_ins[1]);
}
?>
<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Enquiries</a><?=(strlen($contactid)>0?" ".SEP." Viewing enquiry":"")?></div>

<?php 
if(isset($_SESSION['error'])){?><div class="notice"><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }
switch($act)
{
	case "view":
		/*$enqs=ysql_query("SELECT * FROM cart_contactus WHERE `contactus_id`='$contactid'",CARTDB);
		$enq=mysql_fetch_assoc($enqs);*/
		$enqs=$db1->prepare("SELECT * FROM cart_contactus WHERE `contactus_id`=?");
		$enqs->execute(array($contactid));
		$enq=$enqs->fetch(PDO::FETCH_ASSOC);
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<table class="linkslist" style="margin-bottom:10px;">
			<tr class="head">
				<td colspan="2"><div class="titles">Enquiries</div></td>
			</tr>
			<tr class="subhead">
				<td>Information</td>
				<td>Comments</td>
			</tr>
			<tr>
				<td style="padding:0;border-right:1px solid #A7BDE4;">
					<table style="width:100%">
						<tr class="row_light"><td>Name:</td><td><?=$enq['name']?></td></tr>
						<tr>
							<td class="left_dark">Email Address:</td>
							<td class="right_dark blocklink"><a href="mailto:<?=$enq['email']?>?subject=Thank%20you%20for%20your%20email%20to%20<?=str_replace(" ","%20",$sitename)?>&amp;body=%0D%0D----------------------------------%0DYour original enquiry:%0D<?=$enq['comments']?>"><?=$enq['email']?></a></td>
						</tr>
						<?php if(strlen($enq['workphone'])>0){?>
						<tr>
							<td class="left_light">Telephone:</td>
							<td class="right_light"><?=$enq['workphone']?></td>
						</tr>
						<?php
						}
						if(strlen($enq['address1'])>0||strlen($enq['address2'])>0||strlen($enq['city'])>0||strlen($enq['postcode'])>0||strlen($enq['country'])>0){?>
						<tr>
							<td class="left_<?=strlen($enq['workphone'])>0?"dark":"light"?>" style="vertical-align:top;">Address:</td>
							<td class="right_<?=strlen($enq['workphone'])>0?"dark":"light"?>">
							<?=$enq['address1']?><br />
							<?=$enq['address2']?><br />
							<?=$enq['city']?><br />
							<?=$enq['postcode']?><br />
							<?=$enq['country']?>
							</td>
						</tr>
						<?php }?>
					</table>
				</td>
				<td class="right_light" style="vertical-align:top;"><dfn class="infostrip" style="width:98%">Date received: <?=date("jS M Y g:ia",strtotime($enq['date_created']))?></dfn><?=htmlentities($enq['comments'],ENT_QUOTES,"UTF-8")?></td>
			</tr>			
		</table>
		<p class="submit"><a href="mailto:<?=$enq['email']?>?subject=Thank%20you%20for%20your%20email%20to%20<?=str_replace(" ","%20",$sitename)?>&amp;body=%0D%0D----------------------------------%0DYour original enquiry:%0D<?=$enq['comments']?>">Reply</a> <a href="javascript:decision('Are you sure you wish to delete this enquiry?','<?=$self?>&amp;act=delete&amp;eid=<?=$contactid?>')">Delete</a></p>
		<?php
		break;
	default:
			$pgnums=cart_pagenums("SELECT * FROM cart_contactus ORDER BY `date_created` DESC","$self",30,5,'',array());
			$enqs=$db1->query($pgnums[0]);
			if(strlen($pgnums[1])>0){?><?=$pgnums[1]?><?php }?>
	
		<form action="<?=$self?>" method="post" onsubmit="return confirm('Are you sure you wish to delete the selected enquiries?')">
		<table class="linkslist">
			<tr class="head">
				<td colspan="5"><div class="titles">Enquiries</div></td>
			</tr>
			<tr class="subhead">
				<td style="width:20%">Name</td>
				<td style="width:40%">Excerpt</td>
				<td style="width:20%">Email</td>
				<td style="width:10%">Date</td>
				<td style="width:10%;text-align:center">Delete</td>
			</tr>
			<?php 
			/*$enqs=ysql_query("SELECT * FROM cart_contactus ORDER BY `date_created` ASC",CARTDB);
			$enqnum=mysql_num_rows($enqs);
			while($enq=mysql_fetch_assoc($enqs))*/
			$enqnum=$enqs->rowCount();
			while($enq=$enqs->fetch(PDO::FETCH_ASSOC))
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink"><a href="<?=$self?>&amp;act=view&amp;eid=<?=$enq['contactus_id']?>"><?=$enq['name']?></a></td>
					<td><?=substr($enq['comments'],0,40)."..."?></td>
					<td><span><?=$enq['email']?></span></td>
					<td style="white-space:nowrap"><span><?=$enq['date_created']?></span></td>
					<td style="text-align:center"><span><input type="checkbox" name="delenq[<?=$enq['contactus_id']?>]" value="1" /></span></td>
				</tr>
				<?php 
			}
			if($enqnum<1)
			{
				?>
				<tr class="row_light"><td colspan="5" style="text-align:center"><span>No pending enquiries</span></td></tr>
				<?php
			}
			?>
		</table>
		<?php if($enqnum>0){?><p class="submit"><input type="submit" value="Delete Selected" /></p><?php }?>
		</form>
		<?php
		break;
}
?>