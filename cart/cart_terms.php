<?php if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
include "cart_head.php";
/*$posties=ysql_query("SELECT (SELECT `field2`+0.01 FROM cart_postage_details WHERE `post_details_id`='5') as freefrom,(SELECT `field3` FROM cart_postage_details WHERE `post_details_id`='5') as paypr,(SELECT `field3` FROM cart_postage_details WHERE `post_details_id`='10') as iepr
 FROM cart_postage_details");
list($freefr,$paidpr,$iepr)=mysql_fetch_row($posties);*/
$posties=$db1->query("SELECT (SELECT `field2`+0.01 FROM cart_postage_details WHERE `post_details_id`='5') as freefrom,(SELECT `field3` FROM cart_postage_details WHERE `post_details_id`='5') as paypr,(SELECT `field3` FROM cart_postage_details WHERE `post_details_id`='10') as iepr
 FROM cart_postage_details");
list($freefr,$paidpr,$iepr)=$posties->fetch();
?>
<img src="<?=$root_to_cart?>images/title_terms.jpg" alt="Terms &amp; Conditions" /><br /><br />
<h2 style="text-decoration:underline">WEBSITE TERMS AND CONDITIONS OF USE, SUPPLY &amp; SALE</h2>
<p style="font-weight:bold">This page (as well as any links, documents or references on it) advises you of the terms &amp; conditions of use for our website; <?=str_replace("http://","",$webby)?>. Please make sure you read these terms &amp; conditions carefully before you use this site or make any purchases. You should be aware that by using our site and/or ordering any products through our site, you are agreeing to be bound by these terms and conditions. If you do not agree to be bound by these terms of use, please refrain from using or purchasing from our website.</p>
<ol style="padding-left:0;list-style-position:inside">
<li>
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Definitions</span>
	<ol style="list-style-position:outside">
		<li>We are GMK Ltd. We are the leading shooting sports distributor in the UK. Our office address is <?=$sales_addy?>. We are a registered company in England (<?=$coreg?>).</li>
		<li>The website is <?=str_replace("http://","",$webby)?>. Our electronic mail address is <?=$cart_order_email?>. Our phone number is <?=$sales_phone?>. Our fax number is <?=$sales_fax?>.</li>
		<li>You are the website user. You become the customer when placing an order. We will also refer to you as 'your' or 'the customer'. Us/We/Our refers to us here at GMK Ltd. When you purchase from us, we become your retailer.</li>
		<li>Our VAT number is <?=$vatreg?></li>
		<li>The language for the conclusion of contracts is British English only.</li>
	</ol>
</li>

<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Your status &amp; account responsibilities</span>
	<ol style="list-style-position:outside">
		<li>You are responsible for maintaining the confidentiality of your account, should you choose to activate one with GMK Ltd.</li>
		<li>You agree to be responsible for all activity that takes place under your account, which is why you must be vigilante when it comes to protecting your password and unauthorised access to your computer.</li>
		<li>You must contact us immediately if you feel that you have any reason to believe that your accounts security may have been compromised, or if you feel that your account has been used in an unauthorised manner.</li>
		<li>If you choose to register an account with GMK Ltd, you must ensure all details are complete and correct, and inform us immediately of any mistakes or changes to be made.</li>
		<li>By placing an order on our site, or by opening an account, you warrant that you are legally capable of entering into binding contracts, and you are at least 18 years old.</li>
		<li>You may not transfer, assign, charge, sub-contract or otherwise dispose of a contract without our prior written consent.</li>
	</ol>
</li>

<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Shipping &amp; Deliveries</span>
	<ol style="list-style-position:outside">
		<li>We will ensure that all orders will be shipped in a timely fashion. This will usually be within 48 hours on a weekday. If an order is placed on a weekend, then the order will be dispatched the following working day.</li>
		<li>We use a separate delivery company for our goods. This means that upon shipping, the risk of loss passes to this delivery company. If you receive an order confirmation, but you do not receive your goods in a timely fashion, please contact the delivery company for further details. If the delivery company are unable to help, please contact us and we shall try to help.</li>
		<li>We cannot be held liable for delayed order should our delivery company not arrive to collect our shipments on your order day. If this unlikely event occurs, then the order will leave us the following working day.</li>
		<li>We cannot be held liable for any inconvenience that is caused by delays which are down to our delivery company. This could include (but not limited to) the courier re-directing a parcel to an incorrect depot, or being unable to find a customer address.</li>
		<li>Unless otherwise stated, dispatch dates are not guaranteed and should not be relied upon.</li>
		<li>Our couriers will only ship and deliver to the addressee. The delivery address MUST be in the UK or Ireland. We do not ship to overseas territories. If you would prefer the delivery address to be different to your billing address (e.g. a work address or neighbour address) please ensure you insert this address in the Delivery Details section.</li>
		<li>On all orders a &#163;<?=number_format($paidpr,2)?> (Inc. VAT) delivery charge will be applied. This excludes Ireland, where a standard charge of &#163;<?=number_format($iepr,2)?> will apply.</li>
		<li>Claims for incomplete or non-deliveries must be made within 10 working days of our receipt of your order</li>
		<li>There must be someone present at the delivery address to sign for the delivery. Goods cannot be left unattended in a designated safe place. If you feel that you may not be able to sign for delivery, please consider entering a different delivery address (e.g. work address, neighbours address)</li>
	</ol>
</li>

<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Prices, Payment &amp; Availability</span>
	<ol style="list-style-position:outside">
		<li>Prices, orders &amp; bills are displayed in Pound Sterling and include VAT at <?=$vat?>%.</li>
		<li>These prices are subject to change without prior notice. You will not be affected by a price change if you have already received an order confirmation email.</li>
		<li>All item prices are exclusive of delivery charges. See (3.7) for details of delivery charges.</li>
		<li>We will only have items on the website that are currently in stock. If an item is out of stock, it will not be displayed on the website. On the rare occasion that a product becomes unavailable after an order is placed, we will contact you to arrange a refund, or suitable alternative.</li>
		<li>Despite our best efforts, occasionally an item on the website may be mispriced. A product will always be sold at the price stated on the website, except in cases of massive &amp; obvious error. See (13.2) for more details.</li>
		<li>Payment must be made at the time of ordering. Payment method can be MasterCard, Visa, Switch, Solo or Delta. Failure to provide correct billing information will lead to a cancelled or delayed order. The card security number will be required on all orders, and on some payments further 3D verification may be required. On the rare occasion that payment is not accepted online, please contact the sales office (contact details in 1.2). Payment can then be taken over the phone.</li>
		<li>Our website only sells to individuals who can pay with debit or credit cards. We accept no other method of payment, such as cash, cheque or postal order.</li>
		<li>Although payment is handled by a third party (SagePay), your privacy is of utmost importance. No payment card details are ever kept by GMK Ltd, no matter whether they are taken online or over the phone. For more details, read our privacy policy statement.</li>
		<li>After payment has been received, you will be sent an email from us acknowledging your order. Please note that this does not mean that the order has been accepted or that we are guaranteeing supply. Your order constitutes an offer to us to buy an item. All orders are subject to acceptance by us. If we do not accept your order, we will refund payment via the original payment method.</li>
	</ol>
</li>


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Guarantee &amp; Returns Policy</span>
	<ol style="list-style-position:outside">
		<li>The following information about claiming on the guarantee refers ONLY to products that have been purchased directly from this website. If you purchased products from another retailer, you MUST contact this retailer directly.</li>	
		<li>If you wish to make a claim on the guarantee, a receipt, email order confirmation or any other proof of purchase will be required. If a proof of purchase can not be provided, it cannot be accepted as a guarantee claim, and an estimate of repair will be made instead (see 5.8.2).</li> 
		<li>Contact in the first instance should be made to GMK Ltd, please find out contact details in (1.2)
		<li>
			<span style="text-decoration:underline;font-weight:bold;">Returns or Exchange</span>
			<ol style="list-style-position:outside">
				<li>You are able to cancel or exchange any order placed on our website which has not yet been dispatched from our warehouse. There will be no charge.</li>
				<li>To cancel or change an order which has not been dispatched from our warehouse, please contact the sales office (contact details in 1.2) between the hours of 8:30am - 5:00pm weekdays. Please have your order number to hand.</li>
				<li>If you wish to cancel or change an order, and the order has already been dispatched from our warehouse, please follow the relevant returns procedure in (5.7)</li>
				<li>Unwanted Goods can be exchanged, please follow the returns procedure in (5.7) and then re-order your preferred product online.</li>
				<li>To exchange an unwanted good, please return the goods to us within 28 calendar days of the delivery date. Please ensure that the returned goods are clearly labelled with your original order details.</li>
				<li>We are not under the obligation to accept unwanted goods simply because they can be found cheaper elsewhere.</li>
			</ol>
		</li> 
		<li>
			<span style="text-decoration:underline;font-weight:bold;">Returns Procedure for new / unused product</span>
			<ol style="list-style-position:outside">
				<li>All products must be returned to us unused and fully resaleable condition (though they can have been tried on first). You have a legal obligation to take reasonable care of the products while they are in your possession.</li> 
				<li>You may cancel the order contract with us at any time within seven working days, and receive a full refund. If you choose to return the items to us, this must be done immediately after the time that you inform us that you wish to cancel the contract.</li> 
				<li>We recommend that items are returned via an insured parcel service, and that you retain proof of postage as we cannot credit items that we do not receive.</li> 
				<li>If you need to return an item to us because we have sent an incorrect item, please contact us (contact details using the details in 1.2). We will make suitable arrangements to collect and exchange the product for the correct one, with no costs to be paid by you.</li> 
				<li>Please include with your return - your dispatch print note, or your customer details so we are able to ascertain where the return has come from. Please also include a brief note with regards to the action you would like us to take (e.g. return as unwanted so full refund, exchange to be made etc.).</li> 
				<li>All returns are to be sent to our address, detailed in (1.1).</li>
			</ol>
		</li>
		<li>
			<span style="text-decoration:underline;font-weight:bold;">Return Procedure for faulty goods</span>

			<ol style="list-style-position:outside">
				<li>As stated in (5.1), this procedure only applies if you have purchased a product from this website and the item is within its warranty (see 5.2)</li>
				<li>If you wish to return an item that is outside its warranty, please contact the GMK Sales office (see details in 1.2) for estimates and details on chargeable repairs.</li>
				<li>We recommend that items are returned via an insured parcel service, and that you retain proof of postage as we cannot credit items that we do not receive.</li>
				<li>It is vital that the items returned are clean &amp; dry to the highest standard possible. Soiled items cannot be inspected thoroughly, nor assessed, and they also present a hygiene risk. Items that are deemed to be too soiled to assess will be returned to you for cleaning.</li>
				<li>Please return your goods to us (address in 1.1) and include in the return parcel your dispatch print note, or your customer details so we are able to ascertain where the return has come from. Please also include a brief note with regards to the fault, as well as a proof of purchase, (see 5.4)</li>
				<li>Please allow 21 days for refunds to be processed.</li>
			</ol>
		</li>
	</ol>
</li>


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Feedback &amp; Complaints</span>
	<ol style="list-style-position:outside">
		<li>We welcome any comments about our service, whether they are positive or negative. We encourage customers to make a review of their purchase through our website. This ensures that future customers can make an informed choice with their purchase.</li>
		<li>If you have reason to complain about our service, please either email, call or write to the GMK Sales office. All of LLC's contact details can be found on the 'contact us' page.</li>
		<li>We aim to respond to complaints within three (3) working days. Our response will be either a course of action to resolve the complaint, or a timescale for resolution.</li>
	</ol>
</li>


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Alteration of Service or Amendments to the Conditions</span>
	<ol style="list-style-position:outside">
		<li>We reserve the right to revise and amend our website, policies, and these Terms &amp; Conditions at any time without prior notice. These changes may be to reflect changes in market conditions, changes in technology, changes n payment methods, changes in relevant laws and changes in our systems capabilities. Your usage of the website and your orders will be subject to the policies and Terms and Conditions in force at that time, unless these conditions are required to change by law or government authority.</li>
	</ol>
</li>


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Events beyond our reasonable control</span>
	<ol style="list-style-position:outside">
		<li>We cannot be held responsible for any delay or failure to comply with our obligations under these conditions if the delay or failure arises from any cause which is beyond our reasonable control.</li>
		<li>Examples of events that are beyond our reasonable control could include but are not limited to industrial action, civil commotion, natural disasters or restrictions caused by acts of parliament.</li>
	</ol>
</li> 


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Governing Law &amp; Jurisdiction</span>
	<ol style="list-style-position:outside">
		<li>Your use of the website, any purchase by you on the website of any products and these conditions will be governed by and construed in accordance with the laws of England and Wales and will be deemed to have occurred in England. You agree, as we do, to submit to the non-exclusive jurisdiction of the English courts. Your statutory rights are not affected by these Terms and Conditions of Sale.</li>
	</ol>
</li>


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Use of Images &amp; linking policy</span>
	<ol style="list-style-position:outside">
		<li>You are not authorised to use images on this site for any purpose except for one off personal use.</li>
		<li>If you wish to reproduce the images on this website, please contact the GMK Ltd Sales office to discuss a one off use.</li>
		<li>You may not modify any image that you have been authorised to download or print.</li>
		<li>Our status on any downloaded or printed image must always be acknowledged.</li>
		<li>You may link to this website without permission, but you must link to the homepage and not deep within the site. However, if you do decide to link to us, you agree that you will indemnify us in full if any action is taken against us by any third party, or even by you, by virtue of the link you have created.</li>
	</ol>
</li> 


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Electronic communications</span>
	<ol style="list-style-position:outside">
		<li>If you contact us via e-mail, it is deemed that you are communicating with us electronically. In this instance, you are consenting for us to contact you electronically. These communications will be for order discussions only. On occasions, we will send marketing or offers via electronic mail. If you do not wish to receive this mail, please un-tick the relevant box when processing your order.</li>
		<li>We will never pass on details of your e-mail address to a third party. For more details, please see our privacy policy statement.</li>
	</ol>
</li> 


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Resale</span>
	<ol style="list-style-position:outside">
		<li>You may purchase products from the website only for personal use and not for resale purposes. If you are interested in becoming a stockist of GMK products, please contact the GMK Sales office by using the details on the 'contact us' page.</li>
	</ol>
</li> 


<li style="padding-top:10px;">
	<span style="text-decoration:underline;font-weight:bold;color:#FFF">Disclaimer &amp; Waiver</span>
	<ol style="list-style-position:outside">
		<li>Please note that colours may appear different depending on the settings of your monitor and operating system. Colour reproduction of products is as close as the graphic design process allows.</li>
		<li>All reasonable efforts are made to ensure that products on the website are up to date and accurate. Designs may be changed and improved without prior notice. Illustrations are not binding.</li>
		<li>If you breach our terms and conditions and we take no action, we will still be entitled to use our rights and remedies in any other situation where you breach these conditions.</li>
		<li>We reserve the right to refuse access to the website, terminate accounts, remove or edit content, or cancel orders at our discretion. If we cancel orders, it will be without charge to you.</li>
		<li>We are not liable for contents on third party websites that we link to this site.</li>
		<li>When ordering a choke it is your responsibility to ensure you select the correct choke system for the barrels of your gun. GMK accept no responsibility for any damage caused to barrels by incorrect choke fit.</li>
	</ol>
</li>

</ol>

<span class="headbold">Order and Returns information</span>
<p>Please see our dedicated page <a href="./cart_returns">here</a>.</p>
<?php include "cart_foot.php";?>