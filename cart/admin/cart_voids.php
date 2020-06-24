<div id="bread"><a href="index.php">Home</a> <?=SEP?> Voided Orders</div>
		<?php
		if(isset($post_arr['sure']))
		{
			$oids=array_values($post_arr['oid']);
			$oins=bindIns(implode(",",$oids));
			cart_query("DELETE FROM cart_orders WHERE `order_id` IN(".$oins[0].")",$oins[1]);
			cart_query("DELETE FROM cart_orderproducts WHERE `order_id` IN(".$oins[0].")",$oins[1]);
			cart_query("DELETE FROM cart_orderkits WHERE `order_id` IN(".$oins[0].")",$oins[1]);
			cart_query("DELETE FROM cart_ordership WHERE `order_id` IN(".$oins[0].")",$oins[1]);
			echo "<div class='notice'>Successfully deleted all selected void orders and their shipping records</div>";
		}
		else{
			/*$voids=ysql_query("SELECT o.`order_id`,o.`invoice`,o.`date_ordered`,o.`cust_id`,o.`firstname`,o.`lastname`,o.`pay_method`,o.`pay_status`,o.`iorder_status` FROM (cart_orders as o LEFT JOIN cart_orderproducts as op ON op.`order_id`=o.`order_id`) LEFT JOIN cart_orderkits as ok ON ok.`order_id`=o.`order_id` WHERE o.`order_status`='void' GROUP BY o.`order_id`");echo mysql_error();
			$num_voids=mysql_num_rows($voids);*/
			$voids=$db1->query("SELECT o.`order_id`,o.`invoice`,o.`date_ordered`,o.`cust_id`,o.`firstname`,o.`lastname`,o.`pay_method`,o.`pay_status`,o.`iorder_status` FROM (cart_orders as o LEFT JOIN cart_orderproducts as op ON op.`order_id`=o.`order_id`) LEFT JOIN cart_orderkits as ok ON ok.`order_id`=o.`order_id` WHERE o.`order_status`='void' GROUP BY o.`order_id`");
			$num_voids=$voids->rowCount();
			if($num_voids>0){?><script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script><form action="<?=MAINBASE?>/admin/index.php?p=cart_voids" method="post" id="voids" onsubmit="return confirm('Are you sure you want to delete all records of the selected void orders?');"><input type="hidden" name="sure" value="1" />
			<?php }?>
			<table class="details">
			<tr class="head">
				<td colspan="8"><div class="titles">Voided Invoices</div></td>
			</tr>
			<tr class="subhead">
				<td style="width:5%;text-align:center">Invoice</td>
				<td style="width:20%;text-align:left">Order Date</td>
				<td style="width:30%">Customer</td>
				<td style="width:5%;text-align:center">Method</td>
				<td style="width:10%;text-align:center">Pay Status</td>
				<td style="width:10%;text-align:center">Complete?</td>
				<td style="width:5%;text-align:center">View</td>
				<td style="width:5%;text-align:center"><input type="checkbox" onclick="cart_multiCheck(this.form,'oid[]',this)" /></td>
			</tr>
			<?php if($num_voids>0){
				//while($inv=mysql_fetch_assoc($voids))
				while($inv=$voids->fetch(PDO::FETCH_ASSOC))
			{$row=((!isset($row)||$row=="_light")?"_dark":"_light");?>
			<tr class="row<?=$row?>">
				<td style="text-align:center"><?=$inv['invoice']?></td>
				<td style="text-align:left"><?=date("F j\, Y",$inv['date_ordered'])?></td>
				<td class="blocklinks"><?php if(strlen($inv['cust_id'])>0&&$inv['cust_id']>0){?><a href="index.php?p=cart_customers&amp;act=view&amp;cust_id=<?=$inv['cust_id']?>"><?php }?><?=ucwords($inv['firstname']." ".$inv['lastname'])?><?php if(strlen($inv['cust_id'])>0&&$inv['cust_id']>0){?></a><?php }?></td>
				<td style="text-align:center"><?=$inv['pay_method']?></td>
				<td style="text-align:center"><?=(($inv['pay_status']==1)?"Paid":"Unpaid")?></td>
				<td style="text-align:center"><?=(($inv['iorder_status']==1)?"Complete":"Incomplete")?></td>
				<td style="text-align:center"><a href="index.php?p=cart_invoices&amp;act=view&amp;invoice=<?=$inv['invoice']?>">View</a></td>
				<td style="text-align:center"><input type="checkbox" name="oid[]" value="<?=$inv['order_id']?>" /></td>
			</tr>
			<?php }}else{?>
			<tr>
				<td class="row0" colspan="8" style="text-align:center">No void orders found</td>
			</tr>
			<?php }?>
			</table>
			<?php if($num_voids>0){?><p class="submit"><input type="submit" value="Delete Selected" /></form><?php }?>
			<?php 
		}
		?>