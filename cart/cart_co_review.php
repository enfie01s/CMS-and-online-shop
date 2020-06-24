<?php if(basename($_SERVER['PHP_SELF'])!="index.php"&&$_SERVER['REQUEST_METHOD']!="POST"){die("Access Denied");}//direct access security 
$strCart=$_SESSION['cart'];
if(!is_array($strCart)||count($strCart)==0) 
{
	redirection(MAINBASE."/cart_basket");
	exit();
}
$breadstring=$breadsep."<a href='./cart_basket'>Shopping Basket</a>".$breadsep."<a href='./cart_co_address'>Billing &amp; delivery address</a>".$breadsep."<a href='./cart_co_postage'>Postage information</a>".$breadsep."Review Order";
include "cart_head.php";
if(isset($post_arr['shipping'])){$_SESSION['shipping']=$post_arr['shipping'];}
?>
<h2 id="pagetitle">Review Order</h2>
<?php
if(isset($_SESSION['shipping']))
{
	if($freepost!=1)
	{
		$pq="SELECT `restraints`,cpd.`description` as description,`field1`,`field2`,`field3`,cpd.`post_id` as post_id FROM cart_postage as cp,cart_postage_details as cpd WHERE cp.`post_id`=cpd.`post_id` AND `post_details_id`='".$_SESSION['shipping']."'";
		/*
		$pq="SELECT `restraints`,cpd.`description` as description,`field1`,`field2`,`field3`,cpd.`post_id` as post_id FROM cart_postage as cp,cart_postage_details as cpd WHERE cp.`post_id`=cpd.`post_id` AND `post_details_id`='".$_SESSION['shipping']."'";
		$postageq=ysql_query($pq,CARTDB);		
		$postage=mysql_fetch_assoc($postageq);*/
		$pq="SELECT `restraints`,cpd.`description` as description,`field1`,`field2`,`field3`,cpd.`post_id` as post_id FROM cart_postage as cp,cart_postage_details as cpd WHERE cp.`post_id`=cpd.`post_id` AND `post_details_id`=?";
		$postageq=$db1->prepare($pq);		
		$postageq->execute(array($_SESSION['shipping']));
		$postage=$postageq->fetch(PDO::FETCH_ASSOC);
		//print_r($postage);
		//echo $pq;
		$restraints=explode("#",$postage['restraints']);
		if(strlen($restraints[0])>0)
		{
			$stamp=strtotime($restraints[2]);
		}
	}
	if(cart_postage_expired($stamp)&&strlen($restraints[0])>0&&$freepost!=1)
	{
		echo "Time restrains for chosen postage method have expired, please go back and choose a different method";
	}
	else
	{
		?>
		<div class="review">
			<div class="pg_content_left"><h3>Payment Method</h3><p class="note">Credit/Debit Card</p></div>
			<div class="pg_content_right"><h3>Postage Method</h3><p class="note"><?=$freepost==1?"Free Delivery":htmlspecialchars($postage['description'],ENT_QUOTES)?></p></div>
		</div>
		<div class="review">
			<div class="pg_content_left">
			<h3>Billing Address</h3>
			<address>
			<?php
			foreach($_SESSION['address_details']['billing'] as $detail => $info)
			{
				if(!in_array($detail,array("website","company")))
				{
					echo($detail=="county"?cart_get_county($info):($detail=="country"?cart_get_country($info):$info)).($detail!="firstname"&&$info!=""?"<br />":" ");
					}
			}
			?><br />&nbsp;
			</address>
			</div>
			<div class="pg_content_right">
			<h3>Delivery Address</h3>
			<address>
			<?php
			if($_SESSION['address_details']['delivery']['sameasbilling']==1)
			{
				echo "Same as billing";
			}
			else
			{
				foreach($_SESSION['address_details']['delivery'] as $detail => $info)
				{
					if(!in_array($detail,array("website","company","sameasbilling"))){
						echo (($detail=="county")?cart_get_county($info):(($detail=="country")?cart_get_country($info):$info)).(($detail!="firstname"&&$info!="")?"<br />":" ");
					}
				}
			}
			?><br />&nbsp;
			</address>
			</div>
		</div>
		<?php cart_contents(0);?>
		<form action="<?=SECUREBASE?>/cart_co_payment&amp;co=payment" method="post" style="float:right">
		<?=$basket_total<1&&$discount>0?"<input type='hidden' name='navigate' value='proceed' />":""?>
		<p class="submit"><input type="submit" name="complete_order" value="Proceed<?=$basket_total<1&&$discount>0?"":" to Payment"?>" />	</p>
		</form>
	
		<?php
	}
}
include "cart_foot.php";
?>