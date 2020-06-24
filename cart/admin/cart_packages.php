<?php
$basefolder=basename(dirname($_SERVER['PHP_SELF']));
if($basefolder!="admin"){?>
<script type="text/javascript">window.location.href="index.php";</script>
<noscript><meta http-equiv="refresh" content="0;url=index.php" /></noscript>
<?php }
$kitowner=(isset($get_arr['kitowner']))?$get_arr['kitowner']:"";

/* ........................PRODUCT KITS: ADD PRODUCT INTO KIT...................*/
if(isset($post_arr['item'])&&$submittedfrom=="edit_kit")
{
	cart_query("INSERT INTO cart_kits(`ownerId`,`itemId`,`qty`,`in_kit_list`) VALUES(?,?,'1','0')",array($kitowner,$post_arr['item']));
	
	//ysql_query("UPDATE products SET kit=2 WHERE prod_id='$get_arr[kprod_id]'");
	//header("Location: admin.php?p=packages&act=edit&kprod_id=$get_arr[kprod_id]&genfroogle=1");
}
else if($act=="edit")
{
	/* ........................PRODUCT KITS: REMOVE PRODUCT FROM KIT...................*/
	if(isset($get_arr['delete']))
	{
		cart_query("DELETE FROM cart_kits WHERE `kid`=?",array($get_arr['delete']));
		//header("Location: admin.php?p=packages&act=edit&kprod_id=$get_arr[kprod_id]&genfroogle=1");
	}
	/* ........................PRODUCT KITS: UPDATE QTY AND SORTING...................*/
	else if(isset($post_arr['qty']))
	{
		$show=($post_arr['in_kit_list']==1)?1:0;
		foreach($post_arr['qty'] as $item_id => $qty)
		{
			cart_query("UPDATE cart_kits SET `qty`=? WHERE `ownerId`=? AND `itemId`=?",array($qty,$kitowner,$item_id));
		}
		cart_query("UPDATE cart_kits SET `in_kit_list`=? WHERE `ownerId`=?",array($show,$kitowner));
		//ysql_query("UPDATE products SET kit=$show WHERE prod_id='$post_arr[prodid]'");
		//header("Location: admin.php?p=packages&act=edit&kprod_id=$_GET[kprod_id]&genfroogle=1");
	}
	
}
/* ...................... PRODUCT KITS: NO DISASSEMBLE JOHNNY FIVE! ................*/
else if(isset($get_arr['disassemble']))
{
	cart_query("DELETE FROM cart_kits WHERE `ownerId`=?",array($get_arr['disassemble']));
	//ysql_query("UPDATE products SET kit=0 WHERE prod_id='$get_arr[delete]'");
	//header("Location: admin.php?p=packages&genfroogle=1");
}

?>

<?php if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); }?>
<!-- CONTENT -->
<?php
switch($act)
{
	case "add":
		?>
		<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Product Packages</a></div>
		<table class="details">
		<tr class="head">
			<td colspan="2"><div class="titles">Choose product to build on</div></td>
		</tr>
		<tr class="row_light">
			<td style="width:50%;text-align:center">
			<form action="<?=$self?>&amp;act=add" method="post">
			<select name="dept" size="10" style="width:300px">
			<option value="0" <?php if($post_arr['dept']==0){?>selected="selected"<?php }?>>Home Page</option>
			<option value="orphaned" <?php if($post_arr['dept']=="orphaned"){?>selected="selected"<?php }?>>All orphan products</option>
			<option value="onlyinprods" <?php if($post_arr['dept']=="onlyinprods"){?>selected="selected"<?php }?>>Products with only product(s) as parent</option>
			<?php
			$par="";
			$curloop=1;
			/*$deptsQ=ysql_query("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cid,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' ORDER BY `ownerId`,c.`".CFIELDID."`",CARTDB);
			$deptsnum=mysql_num_rows($deptsQ);
			while($depts=mysql_fetch_assoc($deptsQ))*/
			$deptsQ=$db1->query("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cid,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' ORDER BY `ownerId`,c.`".CFIELDID."`");
			$deptsnum=$deptsQ->rowCount();
			while($depts=$deptsQ->fetch(PDO::FETCH_ASSOC))
			{
				if($depts['ownerId']!=0){
					$parentsgot=cart_getparents($depts['ownerId']);
					if($par != $parentsgot){if(strlen($par)>0){?></optgroup><?php }?>
					<optgroup label="<?=$parentsgot?>"><?php $par = $parentsgot;} 
				}
				?>
				<option value="<?=$depts['cid']?>" <?php if($post_arr['dept']==$depts['cid']){?>selected="selected"<?php }?>>
				<?=ucwords($depts['title'])?></option><?php
				if($depts['ownerId']!=0&&strlen($par)>0&&$deptsnum==$curloop){?></optgroup><?php }
				$curloop++;
			}
			?>
			</select>
			<p class="submit"><input type="submit" name="submitdept" style="border:0;" value="View Items" /></p>
			</form>
			</td>
			<td style="width:50%;text-align:center">
			<form action="<?=$self?>" method="get">
			<input type="hidden" name="p" value="cart_packages" />
			<input type="hidden" name="act" value="edit" />
			<select name="kitowner" size="10" style="width:300px">
			<?php
			$binds=array();
			if(isset($post_arr['dept'])&&$post_arr['dept']=="orphaned")
			{
				/*$sqlQ="SELECT p.".PFIELDID." as prod_id,p.".PFIELDNAME." as title".(strlen(PFIELDEXTRA)>0?",p.".PFIELDEXTRA." as extra":"")." FROM ".PTABLE." as p LEFT JOIN fusion as f ON f.itemId=p.".PFIELDID." AND f.itemType='product' AND p.".PFIELDID."!='$kitowner' WHERE fusionId IS NULL";*/
				$sqlQ="SELECT p.".PFIELDID." as prod_id,p.".PFIELDNAME." as title".(strlen(PFIELDEXTRA)>0?",p.".PFIELDEXTRA." as extra":"")." FROM ".PTABLE." as p LEFT JOIN fusion as f ON f.itemId=p.".PFIELDID." AND f.itemType='product' AND p.".PFIELDID."!=? WHERE fusionId IS NULL";
				$binds[]=$kitowner;
			}
			else if(isset($post_arr['dept'])&&$post_arr['dept']=="onlyinprods")
			{
				$ids="";
				/*$notinacatQ=ysql_query("SELECT `itemId` FROM fusion WHERE `itemType`='product' AND `ownerType`='category' GROUP BY `itemId`",CARTDB);
				while($notinacat=mysql_fetch_assoc($notinacatQ))*/
				$notinacatQ=$db1->query("SELECT `itemId` FROM fusion WHERE `itemType`='product' AND `ownerType`='category' GROUP BY `itemId`");
				while($notinacat=$notinacatQ->fetch())
				{if($ids!=""){$ids.=",";}$ids.="?";$binds[]=$notinacat['itemId'];}
				$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM fusion as f,".PTABLE." as p WHERE f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='product' AND `itemId` NOT IN($ids) AND `itemId`!=? GROUP BY `itemId`";
				$binds[]=$kitowner;
			}
			else
			{
				/*$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='category' WHERE `ownerId`='$post_arr[dept]' AND p.`".PFIELDID."`!='$kitowner' ORDER BY `sorting`;";*/
				$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='category' WHERE `ownerId`=? AND p.`".PFIELDID."`!=? ORDER BY `sorting`;";
				$binds[]=$post_arr['dept'];
				$binds[]=$kitowner;
			}
			/*$itemsQ=ysql_query($sqlQ,CARTDB);
			while($items=mysql_fetch_assoc($itemsQ))*/
			$itemsQ=$db1->prepare($sqlQ);
			$itemsQ->execute($binds);
			while($items=$itemsQ->fetch())
			{
				?>
				<option value="<?=$items['prod_id']?>"><?=$items['title']?><?php if(strlen(PFIELDEXTRA)>0){?> (<?=$items['extra']?>)<?php }?></option>
				<?php
			}
			?>
			</select>
			<p class="submit"><input type="submit" name="submititem" style="border:0;" value="Add to package" /></p>
			</form>
			</td>
		</tr>			
		</table>
		<?php
		break;
	case "edit":
		if($kitowner!="")
		{
			/*$q=ysql_query("SELECT `qty`,p.`".PFIELDNAME."` as title,`in_kit_list`,k.`ownerId` as kitowner".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p LEFT JOIN cart_kits as k ON k.`ownerId`=p.`".PFIELDID."` WHERE p.`".PFIELDID."`='$kitowner'",CARTDB);
			$n=mysql_num_rows($q);
			$r=mysql_fetch_assoc($q);*/
			$q=$db1->prepare("SELECT `qty`,p.`".PFIELDNAME."` as title,`in_kit_list`,k.`ownerId` as kitowner".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p LEFT JOIN cart_kits as k ON k.`ownerId`=p.`".PFIELDID."` WHERE p.`".PFIELDID."`=?");
			$q->execute(array($kitowner));
			$n=$q->rowCount();
			$r=$q->fetch();
			?>
			<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
			<form action="<?=$self?>&amp;act=<?=$act?>&amp;kitowner=<?=$kitowner?>" method="post">
			<input type="hidden" name="submittedfrom" value="edit_kit" />
			<div id="bread">
			<a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Product Packages</a></div>
			<div style="width:98%;margin:auto;"><div style="float:left;vertical-align:middle;line-height:160%;">Package Name: <?=$r['title']?><?php if(strlen(PFIELDEXTRA)>0){?> (<?=$r['extra']?>)<?php }?></div><div style="float:right;vertical-align:middle;line-height:160%">Allow individual variant choices <a href='' title='Leave unchecked to automatically choose first available variant for each product in this package.'>?</a> <input type="checkbox" style="vertical-align:middle" name="in_kit_list" value="1" <?php if($r['in_kit_list']==1){?>checked="checked"<?php }?> /></div>
			</div>
			<table class="linkslist">
			<tr class="head">
				<td colspan="5"><div class="titles">Build Package</div></td>
			</tr>
			<tr class="subhead">
				<td style="width:65%">Product</td>
				<td style="width:5%;text-align:center">Qty</td>
				<td style="width:10%;text-align:center">Remove</td>
			</tr>
			<?php
			/*$q=ysql_query("SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id,`kid`,`qty`,`in_kit_list`,k.`itemId` as itemId,f.`ownerId` as owner_id".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM (cart_kits as k JOIN ".PTABLE." as p ON k.`itemId`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=k.`itemId` WHERE k.`ownerId`='$kitowner' GROUP BY `kid`",CARTDB);
			$n=mysql_num_rows($q);
			if($n<1){?><tr class="row_dark"><td colspan="5" style="text-align:center">Package currently empty</td></tr><?php }
			while($r=mysql_fetch_assoc($q))*/
			$q=$db1->prepare("SELECT p.`".PFIELDNAME."` as title,p.`".PFIELDID."` as prod_id,`kid`,`qty`,`in_kit_list`,k.`itemId` as itemId,f.`ownerId` as owner_id".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM (cart_kits as k JOIN ".PTABLE." as p ON k.`itemId`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=k.`itemId` WHERE k.`ownerId`=? GROUP BY `kid`");
			$q->execute(array($kitowner));
			$n=$q->rowCount();
			if($n<1){?><tr class="row_dark"><td colspan="5" style="text-align:center">Package currently empty</td></tr><?php }
			//while($r=mysql_fetch_assoc($q))
			while($r=$q->fetch())
			{
				$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
				?>
				<tr class="<?=$row_class?>">
					<td class="blocklink"><a href="index.php?p=products&amp;table=products&amp;act=update&amp;pid=<?=$r['itemId']?>&amp;owner=<?=$r['owner_id']?>" target="_blank"><?=$r['title']?><?=isset($r['extra'])?" (".$r['extra'].")":""?></a></td>
					<td style="text-align:center"><input type="text" name="qty[<?=$r['itemId']?>]" value="<?=$r['qty']?>" class="input_text_small" /></td>
					<td class="blocklink" style="text-align:center">
					<?php if($n==1){?>
					<a href="javascript:decision('Really delete this product from the kit? this will cause the kit to be disassembled if you leave the page without adding another product','<?=$self?>&amp;act=<?=$act?>&amp;kitowner=<?=$kitowner?>&amp;delete=<?=$r['kid']?>')">Remove</a>
					<?php }else{?>
					<a href="<?=$self?>&amp;act=<?=$act?>&amp;kitowner=<?=$kitowner?>&amp;delete=<?=$r['kid']?>">Remove</a>
					<?php }?>
					</td>
				</tr>
				<?php
			}
		
			?>
			</table>
			<p class="submit"><input type="submit" value="Update Package" style="border:0;" /></p>
			</form>
			
			<table class="details" style="margin-top:30px">
			<tr class="head">
				<td colspan="2"><div class="titles">Add to Package</div></td>
			</tr>
			<?php if(isset($get_arr['submititem'])){?>
			<tr class="extrahead">
				<td colspan="2" style="text-align:center;font-style:normal;font-weight:bold;font-size:12px">You must add at least one item to build this new package</td>
			</tr>
			<?php }?>
			<tr class="row_light">
				<td style="width:50%;text-align:center">
				<form action="<?=$self?>&amp;act=<?=$act?>&amp;kitowner=<?=$kitowner?>" method="post">
				<select name="dept" size="10" style="width:300px">
				<option value="0" <?php if($post_arr['dept']==0){?>selected="selected"<?php }?>>Home Page</option>
				<option value="orphaned" <?php if($post_arr['dept']=="orphaned"){?>selected="selected"<?php }?>>All orphan products</option>
				<option value="onlyinprods" <?php if($post_arr['dept']=="onlyinprods"){?>selected="selected"<?php }?>>Products with only product(s) as parent</option>
				<?php
				$par="";
				$curloop=1;
				/*$deptsQ=ysql_query("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cid,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' ORDER BY `ownerId`,c.`".CFIELDID."`",CARTDB);
				$deptsnum=mysql_num_rows($deptsQ);
				while($depts=mysql_fetch_assoc($deptsQ))*/
				$deptsQ=$db1->query("SELECT `fusionId`,`ownerId`,c.`".CFIELDID."` as cid,c.`".CFIELDNAME."` as title FROM ".CTABLE." as c JOIN fusion as f ON f.`itemId`=c.`".CFIELDID."` AND `itemType`='category' ORDER BY `ownerId`,c.`".CFIELDID."`");
				$deptsnum=$deptsQ->rowCount();
				while($depts=$deptsQ->fetch())
				{
					if($depts['ownerId']!=0){
						$parentsgot=cart_getparents($depts['ownerId']);
						if($par != $parentsgot){if(strlen($par)>0){?></optgroup><?php }?>
						<optgroup label="<?=$parentsgot?>"><?php $par = $parentsgot;} 
					}
					?>
					<option value="<?=$depts['cid']?>" <?php if($post_arr['dept']==$depts['cid']){?>selected="selected"<?php }?>>
					<?=ucwords($depts['title'])?></option><?php
					if($depts['ownerId']!=0&&strlen($par)>0&&$deptsnum==$curloop){?></optgroup><?php }
					$curloop++;
				}
				?>
				</select>
				<p class="submit"><input type="submit" name="submitdept" style="border:0;" value="View Items" /></p>
				</form>
				</td>
				<td style="width:50%;text-align:center">
				<form action="<?=$self?>&amp;act=<?=$act?>&amp;kitowner=<?=$kitowner?>" method="post">
				<input type="hidden" name="submittedfrom" value="edit_kit" />
				<select name="item" size="10" style="width:300px">
				<?php
				$binds=array();
				if(isset($post_arr['dept'])&&$post_arr['dept']=="orphaned")
				{
					/*$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND f.`itemType`='product' WHERE `fusionId` IS NULL AND p.`".PFIELDID."`!='$kitowner'";*/
					$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND f.`itemType`='product' WHERE `fusionId` IS NULL AND p.`".PFIELDID."`!=?";
					$binds[]=$kitowner;
				}
				else if(isset($post_arr['dept'])&&$post_arr['dept']=="onlyinprods")
				{
					$ids="";
					/*$notinacatQ=ysql_query("SELECT `itemId` FROM fusion WHERE `itemType`='product' AND `ownerType`='category' GROUP BY `itemId`",CARTDB);
					while($notinacat=mysql_fetch_assoc($notinacatQ))
					{if($ids!=""){$ids.=",";}$ids.="'$notinacat[itemId]'";}
					$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.".PFIELDNAME." as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM fusion as f,".PTABLE." as p WHERE f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='product' AND `itemId` NOT IN($ids) AND `itemId`!='$kitowner' GROUP BY `itemId`";*/
					$notinacatQ=$db1->query("SELECT `itemId` FROM fusion WHERE `itemType`='product' AND `ownerType`='category' GROUP BY `itemId`");
					while($notinacat=$notinacatQ->fetch(PDO::FETCH_ASSOC))
					{if($ids!=""){$ids.=",";}$ids.="?";$binds[]=$notinacat['itemId'];}
					$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.".PFIELDNAME." as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM fusion as f,".PTABLE." as p WHERE f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='product' AND `itemId` NOT IN($ids) AND `itemId`!=? GROUP BY `itemId`";
					$binds[]=$kitowner;
				}
				else
				{
					/*$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='category' WHERE `ownerId`='$post_arr[dept]' AND p.`".PFIELDID."`!='$kitowner' ORDER BY `sorting`;";*/
					$sqlQ="SELECT p.`".PFIELDID."` as prod_id,p.`".PFIELDNAME."` as title".(strlen(PFIELDEXTRA)>0?",p.`".PFIELDEXTRA."` as extra":"")." FROM ".PTABLE." as p JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' AND `ownerType`='category' WHERE `ownerId`=? AND p.`".PFIELDID."`!=? ORDER BY `sorting`;";
					$binds[]=$post_arr['dept'];$binds[]=$kitowner;
				}
				/*$itemsQ=ysql_query($sqlQ,CARTDB);
				while($items=mysql_fetch_assoc($itemsQ))*/
				$itemsQ=$db1->prepare($sqlQ);
				$itemsQ->execute($binds);
				while($items=$itemsQ->fetch(PDO::FETCH_ASSOC))
				{
					?>
					<option value="<?=$items['prod_id']?>"><?=$items['title']?><?php if(strlen(PFIELDEXTRA)>0){?> (<?=$items['extra']?>)<?php }?></option>
					<?php
				}
				?>
				</select>
				<p class="submit"><input type="submit" style="border:0;" value="Add to package" /></p>
				</form>
				</td>
			</tr>			
			</table>
			<?php
		}
		break;
	default:
		/*$q=ysql_query("SELECT p.`".PFIELDID."` as prod_id,count(p.`".PFIELDID."`) as prod_num,`kid`, p.`".PFIELDNAME."` as title,`fusionId`,f.`ownerId` as owner_id FROM (cart_kits as k JOIN ".PTABLE." as p ON k.`ownerId`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' GROUP BY k.`ownerId`",CARTDB);
		$n=mysql_num_rows($q);*/
		$q=$db1->query("SELECT p.`".PFIELDID."` as prod_id,count(p.`".PFIELDID."`) as prod_num,`kid`, p.`".PFIELDNAME."` as title,`fusionId`,f.`ownerId` as owner_id FROM (cart_kits as k JOIN ".PTABLE." as p ON k.`ownerId`=p.`".PFIELDID."`) LEFT JOIN fusion as f ON f.`itemId`=p.`".PFIELDID."` AND `itemType`='product' GROUP BY k.`ownerId`");
		$n=$q->rowCount();
		?>
		<script type="text/javascript" src="<?=$cart_path?>/cart_functions.js"></script>
		<div id="bread"><a href="index.php">Home</a> <?=SEP?> <a href="<?=$self?>">Product Packages</a></div>
		<p class="submittop"><a href="<?=$self?>&amp;act=add">Create new package</a></p>
		<table class="linkslist">
		<tr class="head">
			<td colspan="4"><div class="titles">Product Packages</div></td>
		</tr>
		<tr class="subhead">
			<td style="width:70%">Name</td>
			<td style="width:15%;text-align:center">Unique Products</td>
			<td style="width:5%;text-align:center">Edit</td>
			<td style="width:10%;text-align:center">Disassemble</td>
		</tr>
		<?php 
		//while($r=mysql_fetch_assoc($q))
		while($r=$q->fetch(PDO::FETCH_ASSOC))
		{
			$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
			?>
			<tr class="<?=$row_class?>">
				<td class="blocklink"><a href="index.php?p=products&amp;table=products&amp;act=update&amp;pid=<?=$r['prod_id']?>&amp;owner=<?=$r['owner_id']?>" target="_blank"><?=$r['title']?></a></td>
				<td style="text-align:center"><?=$r['prod_num']?></td>
				<td class="blocklink" style="text-align:center"><a href="<?=$self?>&amp;act=edit&amp;kitowner=<?=$r['prod_id']?>">Edit</a></td>
				<td class="blocklink" style="text-align:center"><a href="javascript:decision('This will disassemble the package and leave the main product as an individual product','<?=$self?>&amp;disassemble=<?=$r['prod_id']?>')">Disassemble</a></td>
			</tr>
		<?php }if($n<1){?>
		<tr class="row_dark"><td colspan="4" style="text-align:center">No kits found</td></tr>
		<?php }?>
		</table>
		<?php 
		break;
}
?>