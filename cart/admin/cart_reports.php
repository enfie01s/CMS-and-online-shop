<?php
$reporting=isset($get_arr['report'])?$get_arr['report']:"";
$what=isset($post_arr['what'])?$post_arr['what']:(isset($get_arr['what'])?$get_arr['what']:"");
$varfrom=isset($post_arr['from'])?$post_arr['from']:(isset($get_arr['from'])?$get_arr['from']:"");
$varto=isset($post_arr['to'])?$post_arr['to']:(isset($get_arr['to'])?$get_arr['to']:"");
$sstatus=isset($post_arr['sstatus'])?$post_arr['sstatus']:(isset($get_arr['sstatus'])?$get_arr['sstatus']:"");
if(strlen($varfrom)>0&&isset($get_arr['showgen'])){
	$error="";
	if(strlen($varfrom)<1){$error.="Order from date is empty<br />";$higherr[]="from";}
	if(strlen($varto)<1){$error.="Order to date is empty<br />";$higherr[]="to";}
	if(strlen($error)<1){
		$fromexp=explode("-",$varfrom);
		$from=mktime(0,0,0,$fromexp[1],$fromexp[2],$fromexp[0]);
		
		$toexp=explode("-",$varto);
		$to=mktime(23,59,59,$toexp[1],$toexp[2],$toexp[0]);
		
		if($from>=$to){$error.="Order from date must be less than order to date<br />";$higherr[]="from";$higherr[]="to";}
	}
	if(strlen($error)>1){$_SESSION['error']=$error;}
}
?>
<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Reports</a><?=((isset($get_arr['report']))?$breadsep.ucwords($get_arr['report'])." Report":"")?></div>

<?php if(isset($_SESSION['error'])){?><div class="notice"><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }?>

<?php
switch($reporting)
{
	case "stock":
	case "products":
		/*ysql_query("CREATE OR REPLACE VIEW pf AS SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDEXTRA."` as extra,p.`".PFIELDNAME."` as title,f.`fusionId`,f.`ownerId`,p.`displayed`,cf.`allowpurchase` FROM (".PTABLE." as p LEFT JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND `itemType`='product') JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` GROUP BY p.`".PFIELDID."`",CARTDB);*/
		$q=$db1->query("CREATE OR REPLACE VIEW pf AS SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDEXTRA."` as extra,p.`".PFIELDNAME."` as title,f.`fusionId`,f.`ownerId`,p.`displayed`,cf.`allowpurchase` FROM (".PTABLE." as p LEFT JOIN fusion as f ON p.`".PFIELDID."`=f.`itemId` AND `itemType`='product') JOIN cart_fusion as cf ON p.`".PFIELDID."`=cf.`pid` GROUP BY p.`".PFIELDID."`");
		if($reporting=="stock")
		{
			$sqlQ="
			SELECT pf.`extra`,`title`,pf.`prod_id`,`vname` as item_desc,pf.`ownerId` as owner,n.`nav_qty` as total,pf.`fusionId` as fid,pf.`displayed` as onoff,n.`nav_sku` as sku FROM (pf LEFT JOIN cart_variants as cv ON cv.`pid`=pf.`prod_id`) LEFT JOIN nav_stock as n ON n.`nav_skuvar`=cv.`vskuvar` WHERE pf.`fusionId` is not null AND cv.`vskuvar` is not null ORDER BY pf.`title`,cv.`vskuvar`";
		}
		else if($reporting=="products")
		{
			$sqlQ="
			SELECT pf.`extra`,pf.`prod_id` as prod_id,pf.`title` as title,pf.`fusionId` as fid,pf.`ownerId` as owner,cv.`vskuvar`,SUM(n.`nav_qty`) as total,pf.`allowpurchase` 
			FROM (pf JOIN cart_variants as cv ON cv.`pid`=pf.`prod_id`) LEFT JOIN nav_stock as n ON n.`nav_skuvar`=cv.`vskuvar` GROUP BY pf.`prod_id` ORDER BY pf.`allowpurchase` DESC,pf.`title`,cv.`vskuvar`
			";
		}
		$pgnumsarray=cart_pagenums($sqlQ,$indexpage."?p=cart_reports&amp;report=".$reporting,30,5);//div float left and clear
		
		$sqlQ=$pgnumsarray[0];
		/*$reportq=ysql_query($sqlQ,CARTDB) or die(sql_error("Error","Query failed: '$sqlQ'<br />".mysql_error()));
		$reportnums=mysql_num_rows($reportq);*/
		$reportq=$db1->query($sqlQ);
		$reportnums=$reportq->rowCount();
		?><div style="width:98%;margin:auto"><?=$pgnumsarray[1]?></div>
		<table class="linkslist">
		<tr class="head">
			<td colspan="<?=$reporting=="stock"?"7":"5"?>"><div class="titles"><?=ucwords($reporting)?> Report</div></td>
		</tr>
		<tr class="subhead">
			<td style="width:5%"><?=$reporting=="stock"?"On/Off":"ID"?></td>
			<?=$reporting=="stock"?"<td>Type</td><td>Description</td>":""?>
			<td style="width:60%"><?php if($reporting=="stock"){?>Used by <?php }?>Product:</td>
			<?=$reporting=="products"?"<td style='text-align:center;'>Allow Purchase</td>":""?>
			<td style="text-align:center;width:10%">Stock</td>
			<td style="text-align:center;width:5%"></td>
		</tr>
			
		<?php
		//while($report=mysql_fetch_assoc($reportq))
		while($report=$reportq->fetch(PDO::FETCH_ASSOC))
		{
			$editlink=$reporting=="stock"?$indexpage."?p=cart_variantgroups&amp;act=edit&amp;optid=".$report['opt']:$indexpage."?p=products&amp;act=update&amp;showing=prodform&amp;owner=$report[owner]&amp;pid=$report[prod_id]&amp;curpage=".urlencode($report['title']);
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			?>
			<tr class="<?=$row_class?>">
				<td><?=($reporting=="stock"?($report['onoff']==1?"<span style='color:green'>On</span>":"<span style='color:red'>Off</span>"):"<span>".$report['prod_id']."</span>")?></td>
				<?=$reporting=="stock"?"<td><span>$report[description]</span></td><td nowrap='nowrap'><span>$report[item_desc]</span></td>":""?>
				<?=($report['fid']!=null?"<td class='blocklink'><a href='$indexpage?p=products&amp;act=update&amp;showing=prodform&amp;owner=$report[owner]&amp;pid=$report[prod_id]&amp;curpage=".urlencode($report['title'])."' title='".cart_getparents($report['owner']," / ")."'>".$report['title'].(strlen($report['extra'])>0?" ($report[extra])":"")."</a>":"<td><span>".$report['title'].(strlen($report['extra'])>0?" ($report[extra])":"")." <i>(Orphan product)</i></span>")?></td>
				<?=$reporting=="products"?"<td style='text-align:center;'>".$report['allowpurchase']."</td>":""?>
				<td style="text-align:center"><span><?=(($report['total']>0)?$report['total']:"0")?></span></td>
				<td class="blocklink" style="text-align:center"><a href="<?=$editlink?>"><img src="img/edit.png" alt="Edit" /></a></td>
			</tr>
			<?php
		}
		?>
		</table>
		<div style="width:98%;margin:auto"><?=$pgnumsarray[1]?></div>
		<?php
		break;
	case "order":
		if(isset($get_arr['showgen']))
		{
			idhighlighterrors($higherr,"from",array("from_Month_ID","from_Day_ID","from_Year_ID"));
			idhighlighterrors($higherr,"to",array("to_Month_ID","to_Day_ID","to_Year_ID"));
			$pbdstyle=$what=="Orders contents by date"||$what=="Products ordered by date"?"style='display:inline'":"style='display:none'";
			$obdstyle=$what=="Orders contents by date"||$what=="Products ordered by date"?"style='display:none'":"style='display:inline'";
			?>
			<form action="<?=$self?>&amp;report=order&amp;showgen=1" method="post">
			<table class="details">
				<tr class="head">
					<td colspan="2"><div class="titles">Order Report</div></td>
				</tr>
				<tr>
					<td class="first left_light">Order Status:</td>
					<td class="right_light">
						<div id="pbd" <?=$pbdstyle?>><i>Order status not needed for this report</i></div>
						<div id="obd" <?=$obdstyle?>>
						<select name="sstatus" class="formfieldm">
						<option value="all" <?php if(strlen($sstatus)>0&&$sstatus=="all"){?>selected="selected"<?php }?>>All</option>
						<?php foreach($cart_orderstatuses as $dbstatus => $displaystatus){if($dbstatus!="Pending"){?>
							<option value="<?=$dbstatus?>" <?php if($sstatus==$dbstatus){?>selected="selected"<?php }?>><?=$displaystatus?></option>
						<?php }}?>
						</select> 
						</div>
					</td>
				</tr>
				<tr>
					<td class="left_dark">Order From <dfn>(yyyy-mm-dd)</dfn></td>
					<td class="right_dark"><input type="date" name="from" value="<?=strlen($varfrom)>0?date("Y-m-d",$from):date("Y-m-d")?>" <?=highlighterrors($higherr,"from")?> /></td>
				</tr>
				<tr>
					<td class="left_light">Order To <dfn>(yyyy-mm-dd)</dfn></td>
					<td class="right_light"><input type="date" name="to" value="<?=strlen($varto)>0?date("Y-m-d",$to):date("Y-m-d")?>" max="<?=date("Y-m-d")?>" <?=highlighterrors($higherr,"to")?> /></td>
				</tr>
				<tr>
					<td class="left_dark"></td>
					<td class="right_dark">
					<input type="radio" name="what" value="Orders by date" id="1" onclick="javascript:document.getElementById('pbd').style.display='none';document.getElementById('obd').style.display='inline'" <?=strlen($what)>0?cart_is_selected("what","","Orders by date",(isset($post_arr['what'])?$post_arr:$get_arr),"check"):"checked='checked'"?> /><label for="1">Orders by date</label><br />
					<input type="radio" name="what" value="Orders contents by date" id="2" onclick="javascript:document.getElementById('obd').style.display='none';document.getElementById('pbd').style.display='inline'" <?=strlen($what)>0?cart_is_selected("what","","Orders contents by date",(isset($post_arr['what'])?$post_arr:$get_arr),"check"):""?> /><label for="2">Orders contents by date</label><br />
					<input type="radio" name="what" value="Products ordered by date" id="3" onclick="javascript:document.getElementById('obd').style.display='none';document.getElementById('pbd').style.display='inline'" <?=strlen($what)>0?cart_is_selected("what","","Products ordered by date",(isset($post_arr['what'])?$post_arr:$get_arr),"check"):""?> /><label for="3">Products ordered by date</label></td>
				</tr>
			</table>
			<p class="submit"><input type="submit" value="Generate Report" /></p>
			</form>
			<br />
			<?php
		}
		if(!isset($get_arr['showgen'])||(strlen($varfrom)>0&&strlen($error)<1))
		{/*
			$rangefrom=strlen($varfrom)>0?"WHERE `date_ordered` >='$from'":"WHERE `date_ordered` >='".strtotime("today")."'";
			$rangeto=strlen($varto)>0?(strlen($varfrom)>0?"AND":"WHERE")." `date_ordered` <='$to'":"";
			$status=strlen($sstatus)>0&&$sstatus!="all"?(strlen($varfrom)>0?"AND":"WHERE")." `order_status` ='".$sstatus."'":"";*/
			$binds=array();
			$rangefrom="WHERE `date_ordered` >=?";
			$binds[]=strlen($varfrom)>0?$from:strtotime("today");
			$rangeto=strlen($varto)>0?(strlen($varfrom)>0?"AND":"WHERE")." `date_ordered` <=?":"";
			if(strlen($varto)>0){$binds[]=$to;}
			$status=strlen($sstatus)>0&&$sstatus!="all"?(strlen($varfrom)>0?"AND":"WHERE")." `order_status` =?":"";
			if(strlen($sstatus)>0&&$sstatus!="all"){$binds[]=$sstatus;}
			
			if($what=="Orders contents by date")
			{
				$pgnums=cart_pagenums("
				SELECT `invoice`,`qty`,`title`,`price`,`iorder_status`,`date_ordered`,`total_price`,o.`order_id` as order_id,(SELECT count(`order_id`) FROM cart_orderproducts WHERE `order_id`=o.order_id GROUP BY `order_id`) as numforrows
				 FROM cart_orders as o JOIN cart_orderproducts as op ON o.`order_id`=op.`order_id` $rangefrom $rangeto
				  ORDER BY `iorder_status` DESC,`invoice` ASC,`title` ASC
				",$self."&amp;report=order&amp;showgen=1&amp;sstatus=$sstatus&amp;from=".urlencode($varfrom)."&amp;to=".urlencode($varto)."&amp;what=$what",30,5,'',$binds);
				$query=$pgnums[0];
				/*$todayq=ysql_query($query,CARTDB);
				$rowcount=mysql_num_rows($todayq);*/
				$todayq=$db1->prepare($query);
				$todayq->execute($binds);
				$rowcount=$todayq->rowCount();
				?><div style="width:98%;margin:auto"><?=$pgnums[1]?></div>
				<table class="linkslist">
				<tr class="head">
					<td colspan="7"><div class="titles"><?=$what?></div></td>
				</tr>
				<tr class="subhead">
					<td style="width:5%;text-align:center">Invoice</td>
					<td width="5%" style="text-align:center">Ordered</td>
					<td style="width:5%;text-align:center">Qty</td>
					<td width="45%">Description</td>
					<td width="15%" style="text-align:center">Price (-VAT)</td>
					<td width="15%" style="text-align:center">Price (+VAT)</td>
					<td style="width:10%;text-align:center">Status</td>
				</tr>
				<?php 
				$invloop="";
				/* SOLVE PROBLEM INV NUM SPLIT BY PAGES CAUSE ROWSPAN ISSUE */
				//while($today=mysql_fetch_assoc($todayq))
				while($today=$todayq->fetch(PDO::FETCH_ASSOC))
				{
					if($invloop!=$today['invoice']){
						$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
						//$num_rowsq=mysql_query("SELECT `order_id` FROM cart_orderproducts WHERE `order_id`='$today[order_id]'",CARTDB);
						$num_rows=$today['numforrows'];//mysql_num_rows($num_rowsq);
						$tdheight=20*$num_rows;
					}
					?>
					<tr class="<?=$row_class?>">
						<?php if($num_rows<2||($num_rows>1&&$invloop!=$today['invoice'])){?>
						<td class="blocklink" style="text-align:center;" rowspan="<?=$num_rows?>"><a href="<?=$indexpage?>?p=cart_invoices&amp;act=view&amp;invoice=<?=$today['invoice']?>" style="line-height:<?=$tdheight?>px;vertical-align:middle"><?=$today['invoice']?></a></td>
						<?php }?>
						<td style="text-align:center"><span><?=date("d/m/Y",$today['date_ordered'])?></span></td>
						<td style="text-align:center"><span><?=$today['qty']?></span></td>
						<td><span><?=$today['title']?></span></td>
						<td style="text-align:center"><span>&#163;<?=number_format($today['price'],2)?></span></td>
						<td style="text-align:center"><span>&#163;<?=cart_addvat($today['price'],1)?></span></td>
						<td style="text-align:center"><span><?=$today['iorder_status']==1?"Complete":"Incomplete"?></span></td>
					</tr>
					<?php 
					if($invloop!=$today['invoice']){$invloop=$today['invoice'];}
				}
				if($rowcount==0){?>
					<tr class="row_light">
						<td colspan="7" style="text-align:center">No products found for this time period</td>
					</tr>
				<?php }?>
				</table>
				<div style="width:98%;margin:auto"><?=$pgnums[1]?></div>
				<?php
			}
			else if($what=="Products ordered by date")
			{
				$pgnums=cart_pagenums("SELECT SUM(`qty`) as qty,`title`,SUM(`price`) as price,SUM(op.`discount`) as dis,`price` as unitprice,`prod_id`,`ownerId`,(SELECT count(`order_id`) FROM cart_orderproducts WHERE `order_id`=o.order_id GROUP BY `order_id`) as numforrows FROM (cart_orders as o JOIN cart_orderproducts as op USING(`order_id`)) LEFT JOIN fusion as f ON f.`itemId`=op.`prod_id` $rangefrom $rangeto GROUP BY op.`prod_id` ORDER BY `iorder_status` DESC,`invoice` ASC,`title` ASC ",$self."&amp;report=order&amp;showgen=1&amp;sstatus=$sstatus&amp;from=".urlencode($varfrom)."&amp;to=".urlencode($varto)."&amp;what=$what",30,5,'',$binds);
				
				$query=$pgnums[0];
				//$todayq=ysql_query($query,CARTDB);
				//$rowcount=mysql_num_rows($todayq);
				$todayq=$db1->prepare($query);
				$todayq->execute($binds);
				$rowcount=$todayq->rowCount();
				?><div style="width:98%;margin:auto"><?=$pgnums[1]?></div>
				<table class="linkslist">
				<tr class="head">
					<td colspan="5"><div class="titles"><?=$what?></div></td>
				</tr>
				<tr class="subhead">
					<td style="width:5%;text-align:center">Qty</td>
					<td width="40%">Description</td>
					<td width="15%" style="text-align:center">Unit Price</td>
					<td width="15%" style="text-align:center">Total (-VAT)</td>
					<td width="15%" style="text-align:center">Total (+VAT)</td>
				</tr>
				<?php 
				$invloop="";
				//while($today=mysql_fetch_assoc($todayq))
				while($today=$todayq->fetch(PDO::FETCH_ASSOC))
				{
						$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
					if($invloop!=$today['invoice']){
						//$num_rowsq=mysql_query("SELECT `order_id` FROM cart_orderproducts WHERE `order_id`='$today[order_id]'",CARTDB);
						$num_rows=$today['numforrows'];//mysql_num_rows($num_rowsq);
						$tdheight=20*$num_rows;
					}
					?>
					<tr class="<?=$row_class?>">
						<td style="text-align:center"><span><?=$today['qty']?></span></td>
						<td><a href="index.php?p=products&amp;showing=prodform&amp;pid=<?=$today['prod_id']?>&amp;owner=<?=$today['ownerId']?>&amp;curpage=<?=urlencode($today['title'])?>"><?=$today['title']?></a></td>
						<td style="text-align:center"><span>&#163;<?=number_format($today['unitprice'],2)?></span></td>
						<td style="text-align:center"><span>&#163;<?=number_format($today['price'],2)?></span></td>
						<td style="text-align:center"><span>&#163;<?=cart_addvat($today['price']-$today['dis'],1)?> inc. discounts</span></td>
					</tr>
					<?php 
					if($invloop!=$today['invoice']){$invloop=$today['invoice'];}
				}
				if($rowcount==0){?>
					<tr class="row_light">
						<td colspan="5" style="text-align:center">No products found for this time period</td>
					</tr>
				<?php }?>
				</table>
				<div style="width:98%;margin:auto"><?=$pgnums[1]?></div><?php
			}
			else
			{
				/*$todaycq=ysql_query("SELECT count(`order_id`),SUM(`total_price`),AVG(`total_price`),MAX(`total_price`) FROM cart_orders $rangefrom $rangeto $status AND `iorder_status`='1'",CARTDB);
				$todayiq=ysql_query("SELECT count(`order_id`),SUM(`total_price`),AVG(`total_price`),MAX(`total_price`) FROM cart_orders $rangefrom $rangeto $status AND `iorder_status`='0'",CARTDB);
				$todayc=mysql_fetch_row($todaycq);
				$todayi=mysql_fetch_row($todayiq);*/
				$todaycq=$db1->prepare("SELECT count(`order_id`),SUM(`total_price`),AVG(`total_price`),MAX(`total_price`) FROM cart_orders $rangefrom $rangeto $status AND `iorder_status`='1'");
				$todayiq=$db1->prepare("SELECT count(`order_id`),SUM(`total_price`),AVG(`total_price`),MAX(`total_price`) FROM cart_orders $rangefrom $rangeto $status AND `iorder_status`='0'");
				$todaycq->execute($binds);
				$todayiq->execute($binds);
				$todayc=$todaycq->fetch(PDO::FETCH_NUM);
				$todayi=$todayiq->fetch(PDO::FETCH_NUM);
				$from=strlen($from)<1?mktime(0,0,0,date('m'),date('d'),date('Y')):$from;
				$to=strlen($to)<1?mktime(23,59,0,date('m'),date('d'),date('Y')):$to;
				?>
				
				<table class="details">
					<tr class="head">
						<td colspan="2"><div class="titles"><?php if(!isset($get_arr['showgen'])){?>Today's Orders<?php }else{echo $what;if(strlen($varfrom)>0){?> (between <?=date("d/m/Y",$from)?> and <?=date("d/m/Y",$to)?>)<?php }}?></div></td>
					</tr>
					<tr class="subhead">
						<td colspan="2"><strong>Complete Orders</strong></td>
					</tr>
					<tr>
						<td class="first left_light">Total orders</td>
						<td class="right_light"><?php if($todayc[0]>0){?><a href="<?=$indexpage?>?p=cart_invoices&amp;from=<?=$from?>&amp;to=<?=$to?>&amp;istatus=1&amp;sstatus=all"><?php }?><?=$todayc[0]?><?php if($todayc[0]>0){?></a><?php }?></td>
					</tr>
					<tr>
						<td class="left_dark">Total Sales</td>
						<td class="right_dark">&#163;<?=number_format($todayc[1],2)?></td>
					</tr>
					<tr>
						<td class="left_light">Average Order</td>
						<td class="right_light">&#163;<?=number_format($todayc[2],2)?></td>
					</tr>
					<tr>
						<td class="left_dark">Largest Order</td>
						<td class="right_dark">&#163;<?=number_format($todayc[3],2)?></td>
					</tr>
					<tr class="subhead">
						<td colspan="2"><strong>Incomplete Orders</strong></td>
					</tr>
					<tr>
						<td class="left_light">Total orders</td>
						<td class="right_light"><?php if($todayi[0]>0){?><a href="<?=$indexpage?>?p=cart_invoices&amp;from=<?=$from?>&amp;to=<?=$to?>&amp;istatus=0&amp;sstatus=all"><?php }?><?=$todayi[0]?><?php if($todayi[0]>0){?></a><?php }?></td>
					</tr>
					<tr>
						<td class="left_dark">Total Sales</td>
						<td class="right_dark">&#163;<?=number_format($todayi[1],2)?></td>
					</tr>
					<tr>
						<td class="left_light">Average Order</td>
						<td class="right_light">&#163;<?=number_format($todayi[2],2)?></td>
					</tr>
					<tr>
						<td class="left_dark">Largest Order</td>
						<td class="right_dark">&#163;<?=number_format($todayi[3],2)?></td>
					</tr>
					<tr class="subhead">
						<td colspan="2"><strong>Grand Total</strong></td>
					</tr>
					<tr>
						<td class="left_light">Total orders</td>
						<td class="right_light"><?=($todayc[0]+$todayi[0])?></td>
					</tr>
					<tr>
						<td class="left_dark">Total Sales</td>
						<td class="right_dark">&#163;<?=number_format(($todayc[1]+$todayi[1]),2)?></td>
					</tr>
				</table>
				<?php
			}
		}
		break;
	default:			
		?>
		<table class="linkslist">
			<tr class="head">
				<td colspan="2"><div class="titles">Reports</div></td>
			</tr>
			<tr class="row_light">
				<td class="first blocklink"><a href="<?=$self?>&amp;report=order&amp;showgen=1">Order report</a></td>
				<td><span>Choose specific start and end dates to view total sales</span></td>
			</tr>
			<tr class="row_dark">
				<td class="blocklink"><a href="<?=$self?>&amp;report=products">Product report</a></td>
				<td><span>View all products in your shop</span></td>
			</tr>
			<tr class="row_light">
				<td class="blocklink"><a href="<?=$self?>&amp;report=stock">Stock report</a></td>
				<td><span>View stock levels for your products</span></td>
			</tr>
			<tr class="row_dark">
				<td class="blocklink"><a href="<?=$self?>&amp;report=order">Today's Orders</a></td>
				<td><span>View total sales for todays date</span></td>
			</tr>
		</table>
		<?php 
		break;
}
$reportmonth=strtotime("-1 month");
$themonthstart=mktime(0,0,0,date("n",$reportmonth),1,date("Y",$reportmonth));//last month
$themonthend=mktime(0,0,0,date("n"),1,date("Y"));
?>
<table style="margin-top:20px">
<tr class="head"><td colspan="2"><?=date("F",$reportmonth)?>'s quick report</td></tr>
<?php
$popprods=$db1->prepare("SELECT p.`".PFIELDNAME."`,p.`".PFIELDID."`,`ownerId`,count(op.`prod_id`) FROM ((cart_orders as o JOIN cart_orderproducts as op USING(`order_id`)) JOIN ".PTABLE." as p ON p.`".PFIELDID."`=op.`prod_id`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` WHERE `date_ordered` BETWEEN ? AND ? GROUP BY op.`prod_id` ORDER BY count(op.`prod_id`) DESC LIMIT 2");
$popprods->execute(array($themonthstart,$themonthend));
$r=0;
$row=!isset($row)||$row=="_dark"?"_light":"_dark";
while(list($popprod,$popprodid,$popprodowner,$popprodcount)=$popprods->fetch(PDO::FETCH_NUM))
{	
	?>
	<tr class="row<?=$row?>">
	<?php if($r==0){?><td rowspan="2" style="width:25%">Top 2 ordered products</td><?php $r=1;}?>
	<td style="width:75%">
	<?=$popprod?> x<?=$popprodcount?>
	</td>
	</tr>
	<?php
}
$monthsumQ=$db1->prepare("SELECT MAX(total_price),SUM(total_price),count(*) FROM cart_orders WHERE `date_ordered` BETWEEN ? AND ?");
$monthsumQ->execute(array($themonthstart,$themonthend));
list($tmax,$tamount,$tcount)=$monthsumQ->fetch();
$row=!isset($row)||$row=="_dark"?"_light":"_dark";
?>
<tr class="row<?=$row?>">
<td>Total Orders</td>
<td><?=$tcount?></td>
</tr>
<?php $row=!isset($row)||$row=="_dark"?"_light":"_dark";?>
<tr class="row<?=$row?>">
<td>Total amount taken</td>
<td>&#163;<?=number_format($tamount,2)?></td>
</tr>
<?php $row=!isset($row)||$row=="_dark"?"_light":"_dark";?>
<tr class="row<?=$row?>">
<td>Largest Order</td>
<td>&#163;<?=number_format($tmax,2)?></td>
</tr>
</table>
<script type="text/javascript" src="<?=$cart_path?>/jquery.tools.min.js"></script>
<script type="text/javascript">if($(":date")!==null){$(":date").dateinput({format: 'yyyy-mm-dd',selectors: true});}</script>