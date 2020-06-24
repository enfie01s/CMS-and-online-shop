<?php $httppath = "http://www.gmk.co.uk";if(!isset($page)){header("Location: ".$httppath."index.php");}?>
<?php include "abbreviations.php";?>
<div><img src="./content/images/main/title_gunfinder.jpg" alt="Gun Finder" /></div>
<div>To perform a more detailed search of our products, please use the forms below.</div>
<br /><br />
<div class='gunfinderdiv' style="width:500px;margin:0px auto;">
	<fieldset><legend>Shotgun Finder</legend>
		<form action="index.php" method="get" name="gunfinder_form" id="gunfinder_form"> 
			<input type="hidden" class="hidden" name="p" value="products" />
			<table class="emailform">
				<tr>
					<td style="width:30%" class="formlabel">Model:</td>
					<td style="width:70%" class="forminput"><input type="text" name="prod_title" size="30" value="<?php if(isset($_SESSION['terms']['prod_title'])&&$_SESSION['finder_submit']=="shotgun_finder"){echo $_SESSION['terms']['prod_title'];}?>" /></td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Brand: </td><td style="width:70%" class="forminput">
					<select name="brand">
						<option value="all">- Search All -</option>
						<?php
						/*$query = ysql_query("SELECT `brand`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `ctype`='shotgun' AND `brand` != '' AND `brand` != '-' AND `brand` != 'n/a' GROUP BY `brand`",$con1);
						while($row = mysql_fetch_array($query))*/
						$query = $db1->query("SELECT `brand`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `ctype`='shotgun' AND `brand` != '' AND `brand` != '-' AND `brand` != 'n/a' GROUP BY `brand`");
						while($row = $query->fetch())
						{
							$thevalue = htmlspecialchars($row['brand'],ENT_QUOTES,"ISO-8859-1");
							?>
							<option value="<?=$thevalue?>" <?php if(isset($_SESSION['terms']['brand']) && $_SESSION['terms']['brand'] == $thevalue&&$_SESSION['finder_submit']=="shotgun_finder"){?>selected="selected"<?php }?>><?=ucwords($thevalue)?></option>
							<?php
						}
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Category: </td><td style="width:70%" class="forminput">
					<select name="cid">
						<option value="all">- Search All -</option>
						<?php
						/*$query = ysql_query("SELECT `title`,c.`cid`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `title` != '' AND `title` != '-' AND `title` != 'n/a' AND `ctype`='shotgun' GROUP BY `title`",$con1);
						while($row = mysql_fetch_array($query))*/
						$query = $db1->query("SELECT `title`,c.`cid`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `title` != '' AND `title` != '-' AND `title` != 'n/a' AND `ctype`='shotgun' GROUP BY `title`");
						while($row = $query->fetch())
						{
							$thevalue = htmlspecialchars($row['title'],ENT_QUOTES,"ISO-8859-1");
							?>
							<option value="<?=$row['cid']?>" <?php if(isset($_SESSION['terms']['title']) && $_SESSION['terms']['title'] == $thevalue&&$_SESSION['finder_submit']=="shotgun_finder"){?>selected="selected"<?php }?>><?=ucwords($thevalue)?></option>
							<?php
						}
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Gauge: </td><td style="width:70%" class="forminput">
					<select name="field1">
						<option value="all">- Search All -</option>
						<?php
						/*$query = ysql_query("SELECT `field1`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `field1` != '' AND `field1` != '-' AND `field1` != 'n/a' AND `field1` != ' ' AND `ctype`='shotgun' GROUP BY `field1`",$con1);
						while($row = mysql_fetch_array($query))*/
						$query = $db1->query("SELECT `field1`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `field1` != '' AND `field1` != '-' AND `field1` != 'n/a' AND `field1` != ' ' AND `ctype`='shotgun' GROUP BY `field1`");
						while($row = $query->fetch())
						{
							$thevalue = htmlspecialchars($row['field1'],ENT_QUOTES,"ISO-8859-1");
							?>
							<option value="<?=$thevalue?>" <?php if(isset($_SESSION['terms']['gauge']) && $_SESSION['terms']['gauge'] == $thevalue&&$_SESSION['finder_submit']=="shotgun_finder"){?>selected="selected"<?php }?>><?=ucwords($thevalue)?></option>
							<?php
						}
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Chokes: </td><td style="width:70%" class="forminput">
					<select name="field4">
						<option value="all">- Search All -</option>
						<?php
						/*$query = ysql_query("SELECT `field4`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `field4` != '' AND `field4` != '-' AND `field4` != 'n/a' AND `field4` != ' ' AND `ctype`='shotgun' GROUP BY `field4`",$con1);
						while($row = mysql_fetch_array($query))*/
						$query = $db1->query("SELECT `field4`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `field4` != '' AND `field4` != '-' AND `field4` != 'n/a' AND `field4` != ' ' AND `ctype`='shotgun' GROUP BY `field4`");
						while($row = $query->fetch())
						{
							$thevalue = htmlspecialchars($row['field4'],ENT_QUOTES,"ISO-8859-1");
							?>
							<option value="<?=$thevalue?>" <?php if(isset($_SESSION['terms']['chokes']) && $_SESSION['terms']['chokes'] == $thevalue&&$_SESSION['finder_submit']=="shotgun_finder"){?>selected="selected"<?php }?>><?=ucwords($thevalue)?></option>
							<?php
						}
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Barrel: </td><td style="width:70%" class="forminput">
					<select name="field2">
						<option value="all">- Search All -</option>
						<?php
						$new = array();$barrel = array();
						/*$query = ysql_query("SELECT `field2` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `field2` != '' AND `field2` != '-' AND `field2` != ' ' AND `ctype`='shotgun' GROUP BY `field2`",$con1);
						while($row = mysql_fetch_assoc($query))*/
						$query = $db1->query("SELECT `field2` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `field2` != '' AND `field2` != '-' AND `field2` != ' ' AND `ctype`='shotgun' GROUP BY `field2`");
						while($row = $query->fetch())
						{
							$new2 = explode(",",htmlspecialchars(str_replace('"','',$row['field2']),ENT_QUOTES,"ISO-8859-1"));
							foreach($new2 as $new1){array_push($new,trim($new1));}
							$barrel = array_unique(array_merge($barrel,$new));
							sort($barrel);
						}
						foreach($barrel as $barr)
						{
							?>
							<option value="<?=$barr?>&quot;" <?php if(isset($_SESSION['terms']['barrel']) && $_SESSION['terms']['barrel'] == $barr&&$_SESSION['finder_submit']=="shotgun_finder"){?>selected="selected"<?php }?>><?=$barr?>&quot;</option>
							<?php
						}
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td class="formlabel" style="vertical-align:top;width:30%">Weight:</td>
					<td style="width:70%" class="forminput">&nbsp;&nbsp;&nbsp;From <input type="text" name="kg[]" value="<?php if(isset($_SESSION['terms']['kg'][0])&&$_SESSION['finder_submit']=="shotgun_finder"){echo $_SESSION['terms']['kg'][0];}?>" style="width:50px" /> kg to <input type="text" name="kg[]" size="4" value="<?php if(isset($_SESSION['terms']['kg'][1])){echo $_SESSION['terms']['kg'][1];}?>" style="width:50px" /> kg</td>
				</tr>
				<tr>
					<td class="formlabel" style="vertical-align:top;width:30%">Retail:</td>
					<td style="width:70%" class="forminput">From &pound; <input type="text" name="price[]" value="<?php if(isset($_SESSION['terms']['price'][0])&&$_SESSION['finder_submit']=="shotgun_finder"){echo $_SESSION['terms']['price'][0];}?>" style="width:50px" />&nbsp;&nbsp;&nbsp;to &pound; <input type="text" name="price[]" size="8" value="<?php if(isset($_SESSION['terms']['price'][1])&&$_SESSION['finder_submit']=="shotgun_finder"){echo $_SESSION['terms']['price'][1];}?>" style="width:50px" /></td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Premium:</td>
					<td style="width:70%" class="forminput">
						<label for="pyes"><input type="radio" id="pyes" name="premium" value="y" <?php if(isset($_SESSION['terms']['premium']) && $_SESSION['terms']['premium'] == "y"&&$_SESSION['finder_submit']=="shotgun_finder"){?>checked="checked"<?php }?> />&nbsp;Yes</label>&nbsp;&nbsp;&nbsp;
						<label for="pno"><input type="radio" id="pno" name="premium" value="n" <?php if(isset($_SESSION['terms']['premium']) && $_SESSION['terms']['premium'] == "n"&&$_SESSION['finder_submit']=="shotgun_finder"){?>checked="checked"<?php }?> />&nbsp;No</label>&nbsp;
						<label for="pall"><input type="radio" id="pall" name="premium" value="all" <?php if(!isset($_SESSION['terms']['premium']) || ($_SESSION['terms']['premium'] != "y" && $_SESSION['terms']['premium'] != "n"&&$_SESSION['finder_submit']=="shotgun_finder")){?>checked="checked"<?php }?> />&nbsp;Search All</label>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Type:</td>
					<td style="width:70%" class="forminput">
						<label for="tcomp"><input type="radio" id="tcomp" name="type" value="field" <?php if(isset($_SESSION['terms']['type']) && $_SESSION['terms']['type'] == "field"&&$_SESSION['finder_submit']=="shotgun_finder"){?>checked="checked"<?php }?> />&nbsp;Field</label>&nbsp;
						<label for="tfield"><input type="radio" id="tfield" name="type" value="competition" <?php if(isset($_SESSION['terms']['type']) && $_SESSION['terms']['type'] == "competition"&&$_SESSION['finder_submit']=="shotgun_finder"){?>checked="checked"<?php }?> />&nbsp;Competition</label>
						<label for="tall"><input type="radio" id="tall" name="type" value="all" <?php if(!isset($_SESSION['terms']['type']) || ($_SESSION['terms']['type'] != "field" && $_SESSION['terms']['type'] != "competition"&&$_SESSION['finder_submit']=="shotgun_finder")){?>checked="checked"<?php }?> />&nbsp;Search All</label>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Left Handed:</td>
					<td style="width:70%" class="forminput">
						<label for="lhyes"><input type="checkbox" id="lhyes" name="LH" value="1" <?php if(isset($_SESSION['terms']['LH']) && $_SESSION['terms']['LH'] == "1"&&$_SESSION['finder_submit']=="shotgun_finder"){?>checked="checked"<?php }?> /> Limit to left handed/ambidextrous.</label>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td><input type="submit" name="gunfinder_submit" value="&nbsp;Submit&nbsp;" /> <input id="reset_form" disabled="disabled" name="reset_form" onclick="return blankForm('gunfinder_form')" type="hidden" value=" Reset " /></td>
				</tr>
			</table>
		</form>
	</fieldset>
	<br />
	<fieldset><legend>Rifle Finder</legend>
		<form action="index.php" method="get" name="riflefinder_form" id="riflefinder_form"> 
			<input type="hidden" class="hidden" name="p" value="products" />
			
			<table class="emailform">
				<tr>
					<td style="width:30%" class="formlabel">Model:</td>
					<td style="width:70%" class="forminput"><input type="text" name="prod_title" size="30" value="<?php if(isset($_SESSION['terms']['prod_title'])&&$_SESSION['finder_submit']=="rifle_finder"){echo $_SESSION['terms']['prod_title'];}?>" /></td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Brand: </td><td style="width:70%" class="forminput">
					<select name="brand">
						<option value="all">- Search All -</option>
						<?php
						/*$query = ysql_query("SELECT `brand`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `ctype`='rifle' AND `brand` != '' AND `brand` != '-' AND `brand` != 'n/a' GROUP BY `brand`",$con1);
						while($row = mysql_fetch_array($query))*/
						$query = $db1->query("SELECT `brand`,`id`,`pid` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `ctype`='rifle' AND `brand` != '' AND `brand` != '-' AND `brand` != 'n/a' GROUP BY `brand`");
						while($row = $query->fetch())
						{
							$thevalue = htmlspecialchars($row['brand'],ENT_QUOTES,"ISO-8859-1");
							?>
							<option value="<?=$thevalue?>" <?php if(isset($_SESSION['terms']['brand']) && $_SESSION['terms']['brand'] == $thevalue&&$_SESSION['finder_submit']=="rifle_finder"){?>selected="selected"<?php }?>><?=ucwords($thevalue)?></option>
							<?php
						}
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Calibre:</td><td style="width:70%" class="forminput">
					<select name="field1">
						<option value="all">- Search All -</option>
						<?php
						$new = array();$caliber = array();
						/*$query = ysql_query("SELECT `field1` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `ctype`='rifle' AND `field1` != '' AND `field1` != '-' AND `field1` != ' ' GROUP BY `field1`",$con1);
						while($row = mysql_fetch_assoc($query))*/
						$query = $db1->query("SELECT `field1` FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `displayed`='1' AND `ctype`='rifle' AND `field1` != '' AND `field1` != '-' AND `field1` != ' ' GROUP BY `field1`");
						while($row = $query->fetch())
						{
							$new2 = explode(",",htmlspecialchars($row['field1'],ENT_QUOTES,"ISO-8859-1"));
							foreach($new2 as $new1){array_push($new,trim(str_replace("*","",$new1)));}
							$caliber = array_unique(array_merge($caliber,$new));
							sort($caliber);
						}
						foreach($caliber as $cal)
						{
							$val = ($cal == ".177/.22")?$cal:$abbrev['sako'][$cal];
							?>
							<option value="<?=$cal?>" <?php if(isset($_SESSION['terms']['caliber']) && $_SESSION['terms']['caliber'] == $cal){?>selected="selected"<?php }?>><?=$val?></option>
							<?php
						}
						?>
					</select> <span class='info'>* = available to special order only</span>
					</td>
				</tr>
				<tr>
					<td style="width:30%" class="formlabel">Retail:</td>
					<td style="width:70%" class="forminput">From &pound; <input type="text" name="price[]" value="<?php if(isset($_SESSION['terms']['price'][0])&&$_SESSION['finder_submit']=="rifle_finder"){echo $_SESSION['terms']['price'][0];}?>" style="width:50px" /> to &pound; <input type="text" name="price[]" value="<?php if(isset($_SESSION['terms']['price'][1])&&$_SESSION['finder_submit']=="rifle_finder"){echo $_SESSION['terms']['price'][1];}?>" style="width:50px" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td><input type="submit" name="riflefinder_submit" value="&nbsp;Submit&nbsp;" /> <input id="rreset_form" disabled="disabled" name="rreset_form" onclick="return blankForm('riflefinder_form')" type="hidden" value=" Reset " /></td>
				</tr>
			</table>
		</form>
	</fieldset>
</div>

 <script type="text/javascript">
 <!--//
	function changeInputType(oldObject, oType) {
		var newObject = document.createElement('input');
		newObject.type = oType;
		if(oldObject.size) newObject.size = oldObject.size;
		if(oldObject.value) newObject.value = oldObject.value;
		if(oldObject.name) newObject.name = oldObject.name;
		if(oldObject.id) newObject.id = oldObject.id;
		if(oldObject.onclick) newObject.onclick = oldObject.onclick;
		if(oldObject.className) newObject.className = oldObject.className;
		oldObject.parentNode.replaceChild(newObject,oldObject);
		return newObject;
	}
	changeInputType(document.getElementById("reset_form"), "reset");
	changeInputType(document.getElementById("rreset_form"), "reset");
 //-->
 </script>