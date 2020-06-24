<?php if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security
include "sagepay/includes.php";
$breadstring=$breadsep."<a href='./cart_basket'>Shopping Basket</a>".$breadsep."<a href='./cart_co_address'>Billing &amp; delivery address</a>".$breadsep.(cart_postapplicable()?"Postage":"Additional")." information";
include "cart_head.php";
if($strServerType=="DIRECT"){$postage_post=SECUREBASE."/cart_co_review";}
else{$postage_post=SECUREBASE."/cart/sagepay/transactionRegister.php";}

?>
<h2 id="pagetitle"><?=cart_postapplicable()?"Postage":"Additional"?> information</h2>
<?php if(cart_postapplicable()){?>Default Postage Method: <?php if ($_SESSION['address_details']['delivery']['country']=="IE") {?>P&amp;P &#163;14.95<?php $irelandset="set";  } else if($freepost==1){echo $freepostdesc;}else{?>P&amp;P &#163;5.00<?php }}?>
<form action="<?=$postage_post?>" method="post">
	
	<table>
		<tr class="head"><td><div class="titles"><?=cart_postapplicable()?"Select postage choice":"Notes"?></div></td></tr>
		<tr class="row_light">
			<td>
			<?php 		
			if ($irelandset=="set")
			{
			/*$postageq=ysql_query("SELECT pm.`post_id` as post_id,pmd.`restraints` as restraints,pmd.`post_details_id` as post_details_id,pmd.`description` as description FROM cart_postage as pm,cart_postage_details as pmd WHERE pmd.`post_details_id`='10' AND `display`='1' GROUP BY pmd.`post_details_id`",CARTDB);*/
			$postageq=$db1->query("SELECT pm.`post_id` as post_id,pmd.`restraints` as restraints,pmd.`post_details_id` as post_details_id,pmd.`description` as description FROM cart_postage as pm,cart_postage_details as pmd WHERE pmd.`post_details_id`='10' AND `display`='1' GROUP BY pmd.`post_details_id`");
			}
			
			else if(cart_postapplicable()||$strServerType=="DIRECT"){	
			/*$postageq=ysql_query("SELECT pm.`post_id` as post_id,pmd.`restraints` as restraints,pmd.`post_details_id` as post_details_id,pmd.`description` as description FROM cart_postage as pm,cart_postage_details as pmd WHERE pm.`post_id`=pmd.`post_id` AND `status`='1' AND `display`='1'",CARTDB);}
			if(isset($postageq)){
				while($postage=mysql_fetch_array($postageq))*/
				$postageq=$db1->query("SELECT pm.`post_id` as post_id,pmd.`restraints` as restraints,pmd.`post_details_id` as post_details_id,pmd.`description` as description FROM cart_postage as pm,cart_postage_details as pmd WHERE pm.`post_id`=pmd.`post_id` AND `status`='1' AND `display`='1'");}
			if(isset($postageq)){
				while($postage=$postageq->fetch())
				{   
					$price=cart_postagecalc($sub_total,$postage['post_details_id']);
					$disable="";$bwhiteout="";$blwhiteout="";
					$restraints=explode("#",$postage['restraints']);
					 
					if(strlen($restraints[0])>0)
					{
						$stamp=stristr($restraints[2]," ")!=null?strtotime($restraints[2]):strtotime(date("l",strtotime("Tomorrow")).$restraints[2]);
						if(cart_postage_expired($stamp))
						{
							$disable="disabled='disabled'";$bwhiteout="style='color:#999999'";$blwhiteout="style='color:#a0bed1'";
						}
					}
					?>
					<input type="radio" name="shipping" value="<?=$postage['post_details_id']?>" id="ship<?=$postage['post_details_id']?>" checked='checked' <?=$disable?> />
					<label for="ship<?=$postage['post_details_id']?>" <?=$bwhiteout?>>
									
					<?=$price==0&&$postage['post_id']==5?"Free Delivery":htmlentities($postage['description'],ENT_QUOTES,"UTF-8")?>
								 
												
					<?php
					if(strlen($postage['restraints'])>0){echo " (for orders placed ".$restraints[1]." ".(stristr($restraints[2]," ")!=null?date("l h:ia\)",$stamp):date("h:ia\)",$stamp));}
					
					?>
					<span <?=$blwhiteout?>>&#163;<?=$price?></span></label>
					<br />
					<?php 
				}
			}?>
			</td>
		</tr>
		<?php if($strServerType=="SERVER"){?>
		<tr class="row_dark">
			<td>
			<textarea name="comments" id="comments" class="formfield" style="height:70px;width:98%;" onFocus="this.select()"><?=isset($_SESSION['comments'])?$_SESSION['comments']:"Special requirements"?></textarea><br /><?php if(cart_postapplicable()){?><dfn>* Please note: All goods MUST be signed for BY THE ADDRESSEE and will not be left with a neighbour or unattended.</dfn><?php }?>
			</td>
		</tr>
	<?php }?>
	</table>
	<br />
	<input type="submit" name="postmethod" value="Continue" class="formbutton" style="float:right" />
</form>
<?php include "cart_foot.php";?>
