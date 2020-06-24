<?php
$revid=isset($get_arr['rid'])?$get_arr['rid']:"";
if(($act=="delete" && isset($get_arr['rid']))||(isset($post_arr['delrev'])&&count($post_arr['delrev'])>0))
{
	$dels=$act=="delete"?array($get_arr['rid']):array_keys($post_arr['delrev']);
	if(count($dels)>0)
	{
		$del_ins=bindIns(implode(",",$dels));
		$q=$db1->prepare("DELETE FROM cart_reviews WHERE cust_rev_id IN(".$del_ins[0].")");
		$q->execute($del_ins[1]);
		//echo "DELETE FROM cart_reviews WHERE cust_rev_id IN(".$del_ins[0].")";
		//print_r($del_ins[1]);
	}
}
if(isset($post_arr['state'])&&count($post_arr['state'])>0)
{
	if(count($post_arr['state'])>0)
	{
		$q=$db1->prepare("UPDATE cart_reviews SET state=? WHERE cust_rev_id=?");
		foreach($post_arr['state'] as $i => $s)
		{
			$q->execute(array($s,$i));
		}
	}
}
?><div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Reviews</a><?=(strlen($revid)>0?" ".SEP." Viewing review":"")?></div>
<?php 
if(isset($_SESSION['error'])){?><div class="notice"><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }
switch($act)
{
	case "view":
		/*$enqs=ysql_query("SELECT * FROM cart_reviews WHERE `cust_rev_id`='$revid'",CARTDB);
		$enq=mysql_fetch_assoc($enqs);*/
		$enqs=$db1->prepare("SELECT * FROM cart_reviews as t1 JOIN cart_customers as t2 USING(cust_id) WHERE `cust_rev_id`=?");
		$enqs->execute(array($revid));
		$enq=$enqs->fetch(PDO::FETCH_ASSOC);
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<table class="linkslist" style="margin-bottom:10px;">
			<tr class="head">
				<td colspan="2"><div class="titles">Review <?=cart_stars($enq['rank'])?></div></td>
			</tr>
			<tr class="subhead">
				<td>Information</td>
				<td>Comments</td>
			</tr>
			<tr>
				<td style="padding:0;border-right:1px solid #A7BDE4;">
					<table style="width:100%">
						<tr class="row_light"><td>Name:</td><td><?=$enq['firstname']." ".$enq['lastname']?></td></tr>
						<tr>
							<td class="left_dark">Email Address:</td>
							<td class="right_dark blocklink"><a href="mailto:<?=$enq['email']?>?subject=Thank%20you%20for%20your%20email%20to%20<?=str_replace(" ","%20",$sitename)?>&amp;body=%0D%0D----------------------------------%0DYour review:%0D<?=$enq['comment']?>"><?=$enq['email']?></a></td>
						</tr>
					</table>
				</td>
				<td class="right_light" style="vertical-align:top;"><dfn class="infostrip" style="width:98%">Date received: <?=date("jS M Y g:ia",strtotime($enq['date_created']))?></dfn><?=htmlentities($enq['comment'],ENT_QUOTES,"UTF-8")?></td>
			</tr>			
		</table>
		<p class="submit"><a href="mailto:<?=$enq['email']?>?subject=Thank%20you%20for%20your%20email%20to%20<?=str_replace(" ","%20",$sitename)?>&amp;body=%0D%0D----------------------------------%0DYour review:%0D<?=$enq['comment']?>">Reply</a> <a href="javascript:decision('Are you sure you wish to delete this enquiry?','<?=$self?>&amp;act=delete&amp;rid=<?=$revid?>')">Delete</a></p>
		<?php
		break;
	default:
		?><form action="<?=$self?>" method="post" onsubmit="return confirm('Are you sure you wish to delete/update the selected enquiries?')">
		<table class="linkslist">
			<tr class="head">
				<td colspan="6"><div class="titles">Reviews</div></td>
			</tr>
			<tr class="subhead">
				<td style="width:40%">Name</td>
				<td style="width:30%">Title</td>
				<td style="white-space:nowrap">Rank</td>
				<td style="width:20%">Date</td>
				<td style="width:5%;text-align:center">Display</td>
				<td style="width:5%;text-align:center">Delete</td>
			</tr>
			<?php 
			/*$enqs=ysql_query("SELECT * FROM cart_reviews ORDER BY `date_created` ASC",CARTDB);
			$enqnum=mysql_num_rows($enqs);
			while($enq=mysql_fetch_assoc($enqs))*/
			$enqs=$db1->query("SELECT cust_rev_id,t2.firstname,t2.lastname,t1.title,t1.date_created,t1.state,t1.rank FROM cart_reviews as t1 JOIN cart_customers as t2 USING(cust_id) ORDER BY `date_created` ASC");
			$enqnum=$enqs->rowCount();
			while($enq=$enqs->fetch(PDO::FETCH_ASSOC))
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink"><a href="<?=$self?>&amp;act=view&amp;rid=<?=$enq['cust_rev_id']?>"><?=$enq['firstname']." ".$enq['lastname']?></a></td>
					<td><span><?=$enq['title']?></span></td>
					<td style="white-space:nowrap"><span><?=cart_stars($enq['rank'])?></span></td>
					<td><span><?=$enq['date_created']?></span></td>
					<td style="text-align:center"><span><input type="hidden" name="state[<?=$enq['cust_rev_id']?>]" value="0" /><input type="checkbox" name="state[<?=$enq['cust_rev_id']?>]" value="1" <?=$enq['state']==1?"checked='checked'":""?> /></span></td>
					<td style="text-align:center"><span><input type="checkbox" name="delrev[<?=$enq['cust_rev_id']?>]" value="1" /></span></td>
				</tr>
				<?php 
			}
			if($enqnum<1)
			{
				?>
				<tr class="row_light"><td colspan="5" style="text-align:center"><span>No reviews found</span></td></tr>
				<?php
			}
			?>
		</table>
		<?php if($enqnum>0){?><p class="submit"><input type="submit" value="Submit" /></p><?php }?>
		</form>
		<?php
		break;
}
?>