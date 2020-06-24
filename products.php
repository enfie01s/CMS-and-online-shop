<div><img src='./content/images/main/title_products.jpg' alt='Viewing Products' /></div>
<?php
/* set up vars */
$prodmod=array_search("products",$modules_pages);
$viewtype=isset($get_arr['simplesearch']) ? 2 : (isset($get_arr['gunfinder_submit'])||isset($get_arr['riflefinder_submit'])?1:0);

$perrow = 3;
$maxrows = 4;
$perpage = $perrow*$maxrows;
$maxpagelinks = 5;
$produrlstring = "";
$get_page=isset($get_page)&&!isset($_GET['p'])?$get_page:(isset($get_arr['page'])?intval($get_arr['page']):1);
$_SESSION['sort_by']=isset($_REQUEST['sortby'])?cleanCols('gmk_products',$_REQUEST['sortby']):"min(price)";/* old f.`sorting` */
$_SESSION['sort_dir']=isset($_REQUEST['sortdir'])&&$_REQUEST['sortdir']=="DESC"?"DESC":"ASC";
$get_type=isset($_REQUEST['type'])&&!isset($_REQUEST['gunfinder_submit'])&&!isset($_REQUEST['riflefinder_submit'])?$_REQUEST['type']:"";
if(!isset($_GET['p']))
{
$sorting="";
}
else
{
$sorting="&amp;sortby=".urlencode($_SESSION['sort_by'])."&amp;sortdir=".urlencode($_SESSION['sort_dir']).(strlen($get_type)>0?"&amp;type=".urlencode($get_type):"");
}
$visible=array();//18
$types=array();
$prods_num=0;
if(isset($_SESSION['view'])){unset($_SESSION['view']);}
/* vars */
$depth=count($subdirs);
$arr=$prodscats;
$info=$pcdetail;
for($dp=0;$dp<$depth;$dp++)
{
	$arr=$arr[urldecode($subdirs[$dp])];
	$info=$info[urldecode($subdirs[$dp])];
}
$simpleresults=$subdirs[0]=="products"&&(!isset($subdirs[1])||$subdirs[1]!="item")?1:0;
if($simpleresults==0&&(!is_array($arr)||(isset($subdirs[1])&&$subdirs[1]=="item"&&isset($subdirs[2])&&strlen(isset($subdirs[2]))>0)))//product
{
	$stat=isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0?"":" AND displayed=1";
	/*$id = trim(mysql_real_escape_string(intval($_GET['id'])));
	$query_string = "SELECT *,p.`description` as description FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `pid`='$id'".$stat." ORDER BY `order` ASC";
	$query = ysql_query($query_string,$con1) or die(sql_error("Error",$query_string));
	if(mysql_num_rows($query)>0)*/
	$id = (isset($subdirs[1])&&$subdirs[1]=="item"&&isset($subdirs[2])&&strlen(isset($subdirs[2]))>0)?$subdirs[2]:trim(intval($info['id']));
	$query_string = "SELECT *,p.`description` as description FROM (((gmk_products as p JOIN fusion as f ON p.`pid`=f.`itemId` AND f.`itemType`='product' AND f.`ownerType`='category') JOIN gmk_categories as c ON c.`cid`=f.`ownerId`) LEFT JOIN gmkbrands as b ON b.`id`=p.`bid`) LEFT JOIN cart_variants as v USING(`pid`) WHERE `pid`=?".$stat." ORDER BY `order` ASC";
	$query = $db1->prepare($query_string);
	$query->execute(array($id));
	if($query->rowCount()>0)
	{
		?><noscript><?php
		if(!isset($_SESSION['view'])&&$islocal!=1&&!$inhouse!=1){
		//ysql_query("UPDATE gmk_products SET `views`=`views`+1 WHERE `pid`='".$id."'",$con1);
		$q=$db1->prepare("UPDATE gmk_products SET `views`=`views`+1 WHERE `pid`=?");
		$q->execute(array($id));
		$_SESSION['view']=1;
		}
		?></noscript><?php
		//$result = mysql_fetch_assoc($query);
		$result = $query->fetchAll();
		if(strlen($stat)<1&&$result[0]['displayed']==0){?><div class="notice">Warning: This product status is set to off, switch on to make it viewable by the public!</div><?php }
		$find = array("vname","price","kg");
		$replace = array("Description","RRP Inc. VAT","weight");
		
		$scopecols1 = array("lens"=>"Lens Coating","windage"=>"Windage &amp; Elevation adjustment dials","reticule"=>"Reticule Options|Listed reticule styles are available on select models","eyepiece_focus"=>"eyepiece focus design","parallax"=>"user parallax adjustment control","magnification"=>"magnification system","maintube_dia"=>"Maintube Diameter and Material","power_selector"=>"power selector style","waterproof"=>"waterproof/fog proof technology","ballistics"=>"Ballistics Aiming System","shop_serviceable"=>"Custom Shop Serviceable","guarantee"=>"Leupold Full Lifetime Guarantee");
		//$rangecols1
		
		$prodpath="./".$images_arr['product']['path'];
		$exists=file_exists($prodpath.$result[0]['pid'].".png")?1:0;
		$timage=$exists==1?$prodpath.$result[0]['pid'].".png":$prodpath."unavail.png";
		$big=is_file($prodpath."big/".$result[0]['pid'].".jpg")?$result[0]['pid'].".jpg":0;
		if($big==0)
		{
			$stageW=400;
			$stageH=400;
		}
		else
		{
			list($w,$h)=getimagesize($prodpath."big/".$result[0][PFIELDID].".jpg");
			$stageW=$w/2;
			$stageH=$h/2;
		}
		$stageW=$deviceType=="phone"?"96%":$stageW."px";
		$stageH=$deviceType=="phone"?"":"height:".$stageH."px;";
		if($id!=332){
		?>
		<div id="prod_detail_brand"><?=$result[0]['brand']?></div>
		<div id="prod_detail_model"><?=str_replace(array("&lt;","&gt;","&amp;nbsp;"),array("<",">","&nbsp;"),htmlentities($result[0]['prod_title'],ENT_QUOTES,"UTF-8"))?></div>
		<?php 
		}else{
			?><img src="./content/images/banners/486Parallelo-banner.jpg" alt="<?=$result[0]['brand']." - ".str_replace(array("&lt;","&gt;","&amp;nbsp;"),array("<",">","&nbsp;"),htmlentities($result[0]['prod_title'],ENT_QUOTES,"UTF-8"))?>" /><br /><br /><?php
		}
		if($big==0){		
			$linkpre=$deviceType=="computer"?"<a href='?p=image&amp;id=".$result[0]['pid']."'>":"";
			$linksuff=$deviceType=="computer"?"</a>":"";
			?>	
		<div id="prod_detail_model_img">
		<div style="position:absolute;top:8px;left:8px;z-index:3;"><?=$linkpre?><img src='./content/images/logos/<?=strtolower($result[0]['brand'])?><?php if($result[0]['premium'] == "y"){?>_premium<?php }?>.gif' alt='<?=ucwords($result[0]['brand'])?>' /><?=$linksuff?></div>
		<?=$linkpre?><img src='<?=$timage?>' alt='' border="0" class="gunimg" /><?=$linksuff?><?php if($deviveType=="computer"){?><br /><a href='?p=image&amp;id=<?=$result[0]['pid']?>' class="enlarge"><img src='./content/images/main/enlarge.png' alt='+ enlarge' /></a><?php }?>
		</div>
		<?php }?>
	
		<?php if($big!=0){?>
		<div style="width:<?=$stageW?>;<?=$stageH?>padding:7px 7px 15px;background:#8b8b8b;"> 
			<img src="<?=$prodpath?>big/<?=$big?>" style="width:100%;position:relative;top:0;left:0;" alt="" id="img_02" />
			<div><?=$deviceType=="phone"?"Click around":"Move your mouse over"?> the image to zoom</div>
		</div>
		<?php }?>
		<div id="prod_detail_description"><?=str_replace("\r\n","",$result[0]['description'])?></div>
		<?php
		$flashfile=$prodpath."flv/".$id.".flv";
		if(is_file($flashfile)&&$deviceType=="computer")
		{
			?>
			<div id="dvd">
				<?php if($id=='188'){?><div style="float:left"><h1>Vinci Assembly Video</h1><?php }?>
				<object id="Object1" type="application/x-shockwave-flash" data="content/flash/player.swf" width="320" height="240">
					<param name="movie" value="content/flash/player.swf" />
					<param name="allowFullScreen" value="true" />
					<param name="wmode" value="opaque" />
					<param name="allowScriptAccess" value="sameDomain" />
					<param name="quality" value="high" />
					<param name="menu" value="true" />
					<param name="autoplay" value="false" />
					<param name="autoload" value="false" />
					<param name="FlashVars" value="configxml=content/xml/playerconfig.xml&flv=<?=$mainbase."/".$flashfile?>&width=320&height=240" />
				</object>
				<?php if($id=='188'){?>
				</div>
				<div style="float:left;margin-left:10px;">
				<h1>Vinci Challenge Video</h1>
				<object id="Object2" type="application/x-shockwave-flash" data="content/flash/player.swf" width="320" height="240">
					<param name="movie" value="content/flash/player.swf" />
					<param name="allowFullScreen" value="true" />
					<param name="wmode" value="opaque" />
					<param name="allowScriptAccess" value="sameDomain" />
					<param name="quality" value="high" />
					<param name="menu" value="true" />
					<param name="autoplay" value="false" />
					<param name="autoload" value="false" />
					<param name="FlashVars" value="configxml=content/xml/playerconfig.xml&flv=<?=$mainbase?>/content/flash/movies/Vinci_tricks.flv&width=320&height=240" />
				</object>
				</div>
				<div style="clear:both;"></div>
				<?php }?>
			</div>
			<?php 
		}
		$extrapics=glob("./content/images/products/".$id."/*.jpg");
		$expicscount=count($extrapics);
		if($expicscount>0)
		{
			$ex=0;
			?><h2>Extra Images</h2><dfn>(Click to enlarge)</dfn><table><tr><?php
			foreach($extrapics as $extrapic){
			$ex++;
			$rowdivide=$ex%5;
			$totalover=$expicscount%5;
			$cellstoadd=5-$totalover;
			?><td style="text-align:center;background:#FFF"><span class="lbox"><a href="<?=$extrapic?>" style="display:inline-block" title="Engraving: <?=str_replace(".jpg","",basename($extrapic))?>"><img src="<?=$extrapic?>" alt="Engraving: <?=str_replace(".jpg","",basename($extrapic))?>" style="height:100px;vertical-align:bottom;" /></a></span></td><?php
				if($cellstoadd<5&&$ex>=$expicscount){for($x=1;$x<=$cellstoadd;$x++){?><td></td><?php }}
				if($rowdivide==0||($ex>=$expicscount&&$x-1==$cellstoadd)){?></tr><?php if($ex<$expicscount){?><tr><?php }}			
			}
			?></table><?php
		}
		$fieldcols=$deviceType=="phone"?array():$fields[$result[0]['ctype']];
		$phonecols=$deviceType=="phone"?array_diff($fields[$result[0]['ctype']],$fieldcols):array();
		if($deviceType=="phone"){$fieldcols['variations']="";}
		
		?>
		<table id="product_options">
			<tr>
				<?php 
				
				$width = floor(100/count($fieldcols));
				foreach($fieldcols as $name => $col){?>
				<td class="table_header" id="<?=str_replace(" ","_",$name)?>_header" style="width:<?=$width?>%;<?php if($col == "price"){?>text-align:right<?php }?>"><?=ucwords($name)?></td>
				<?php }?>
			</tr>
		<?php
		foreach($result as $row => $optsresult)
		{
			$class = (!isset($class) || $class == 1) ? 0 : 1;
			?>
			<tr>
				<?php foreach($fieldcols as $name => $col){?>
				<td class="table_row<?=$class?>" style="width:<?=$width?>%;<?php if($col == "price"){?>text-align:right;<?php }?><?php if($col == "vname" || $col == "field1"){?>white-space:nowrap;<?php }?>">
				<?php
				if($name=="variations")
				{
					$f=0;
					$f1=count($phonecols);
					foreach($phonecols as $name1 => $col1)
					{
						if($f>1&&$f<$f1){?> | <?php }
						$f++;
						$pre1 = ($col1 == "price") ? "&pound;" : "";
						$suff1 = ($col1 == "kg") ? " kg" : "";	
						$resss=$col1 == "price" && $optsresult[$col1] <= 0?"TBA":$optsresult[$col1];			
						?>
						<?=($col1=="vname"?"<strong style='text-decoration:underline'>":"").ucwords($name1).": ".(strlen($optsresult[$col1])>0?$pre1.htmlspecialchars(str_replace("-v-NONE","",$resss),ENT_QUOTES,"ISO-8859-1").$suff1:"").($col1=="vname"?"</strong><br />":"")?>
						<?php 
					}
				}
				else
				{				
					$pre = ($col == "price") ? "&pound;" : "";
					$suff = ($col == "kg") ? " kg" : "";		
					$resss=$col == "price" && $optsresult[$col] <= 0?"TBA":$optsresult[$col];		
					?>
					<?=strlen($optsresult[$col])>0?$pre.htmlspecialchars(str_replace("-v-NONE","",$resss),ENT_QUOTES,"ISO-8859-1").$suff:"--"?>
					<?php 
				}?>
				</td>
				<?php }?>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
		include "abbreviations.php";abbreviationsKey(strtolower($result[0]['brand']));	
	}
	else
	{
		?><div id="errorbox"><p>Error</p>Sorry, this product is currently unavailable.</div><?php
	}
}
else//category
{
	$code=isset($get_arr['code'])?$get_arr['code']:($depth>0?$info['id']:"");
	if(!(strlen($code)>0&&in_array(substr($code,3),$visible))){
	?>
	<form action="./<?=$depth>0?$_GET['mp'].(isset($get_page)&&!isset($_GET['p'])?"/page".$get_page:""):"./products"?>" method="post">
		<input type="hidden" name="p" value="products" />
			<div id='quicksearchbar'>Quick Search</div>
				<div id='quicksearchmain'>
	<?php
	}
	switch($viewtype)
	{
		case 1:/*gunfinder*/
			$binds=array();
			$viewing="Search Results:";
			$ctype=isset($get_arr['gunfinder_submit'])?"shotgun":"rifle";
			$binds[]=$ctype;
			//$terms=" AND `ctype`='$ctype'";
			$terms=" AND `ctype`=?";
			$disp="";	
			$colnames=array_flip($fields[$ctype]);
			$colnames["prod_title"]="model";
			$colnames["cid"]="category";
			$url="./products".(isset($get_arr['gunfinder_submit'])?"&amp;gunfinder_submit=1":(isset($get_arr['riflefinder_submit'])?"&amp;riflefinder_submit=1":""));
			$bins=bindIns(implode(",",$visible));
			$binds=array_merge($binds,$bins[1]);
			foreach($get_arr as $col => $value)
			{			
				if(!in_array($col,array("p","gunfinder_submit","riflefinder_submit","sortby","sortdir","page")))
				{
					
					$spre="<span style='color:#FFF'>";
					$ssuff="</span>";
					$colname=array_key_exists($col,$colnames)?ucwords($colnames[$col]):ucwords($col);
					if(!is_array($value)){$url.="&amp;".$col."=".$value;?><input type="hidden" name="<?=$col?>" value="<?=$value?>" /><?php }
					if(!is_array($value)&&strlen($value)>0&&$value!="all"){
						//$terms .= in_array($col,array("field2","vname","field1","prod_title"))?" AND `$col` LIKE '%$value%'":" AND `$col` = '$value'";
						$terms .= in_array($col,array("field2","vname","field1","prod_title"))?" AND `$col` LIKE ?":" AND `$col` = ?";
						$binds[]=in_array($col,array("field2","vname","field1","prod_title"))?"%".$value."%":$value;
						if($col=="LH"){$disp[]= $spre."Suitable for left handed users".$ssuff;}
						else if(strtolower($col)=="premium"){$disp[]= $spre.($value=="y"?"":"Non-")."Premium guns only".$ssuff;}
						else if(strtolower($col)=="cid"){
							/*$cq=ysql_query("SELECT `title` FROM gmk_categories WHERE `cid`='$value'");
							list($catname)=mysql_fetch_row($cq);*/
							$cq=$db1->prepare("SELECT `title` FROM gmk_categories WHERE `cid`=?");
							$cq->execute(array($value));
							list($catname)=$cq->fetch();
							$disp[]= $colname.": ".$spre.$catname.$ssuff;
						}
						else{$disp[]= $colname.": ".$spre.ucwords(stripslashes($value)).$ssuff;}
					}
					else if(is_array($value))
					{
						foreach($value as $val => $v){$url.="&amp;".$col."%5B%5D=".$v;?><input type="hidden" name="<?=$col?>[]" value="<?=$v?>" /><?php }
						if($value[0]>0||$value[1]>0)
						{
							$pre=$col=="price"?"&#163;":"";
							$suff=$col=="kg"?"kg":"";
							$dispstring=$colname.$spre;
							if($value[0]>0){$terms .= " AND `$col` >= ?";$binds[]=$value[0];$dispstring.= " from ".$pre.$value[0].$suff;}
							if($value[1]>0){$terms.=" AND `$col` <= ?";$binds[]=$value[1];$dispstring.= " up to ".$pre.$value[1].$suff;}	
							$dispstring.=$ssuff;					
							$disp[]=$dispstring;
						}
					}
				}
			}
			$query="SELECT `brand`,min(`price`),`prod_title`,p.`pid`,`premium`,`type` FROM (((gmk_products as p JOIN cart_variants as v ON p.`pid`=v.`pid`) JOIN fusion as f ON p.`pid`=f.`itemId`) JOIN gmk_categories as c ON c.`cid`=f.`ownerId` AND f.`ownerType`='category') JOIN gmkbrands as b ON b.`id`=p.`bid` WHERE p.`displayed`='1' AND b.`id` NOT IN(".$bins[0].") $terms GROUP BY p.`pid` ORDER BY ".$_SESSION['sort_by']." ".$_SESSION['sort_dir'];	
			
			$numbits=pagenums($query,$url.$sorting,$perpage,$maxpagelinks,'',$binds);		
			$types=array();	
			?>
			<div style="float:left;padding:6px;border:1px dashed #5D4D47;margin-right:6px;width:430px;color:#A8A69C;">
			<span style='font-weight:bold;color:#ccc'><?=ucwords($ctype)?> Search terms | </span><?=implode(", ",$disp)?>		
			</div>
			<input type="hidden" name="<?=isset($get_arr['gunfinder_submit'])?"gunfinder_submit":"riflefinder_submit"?>" value="1" />						
			<?php
			break;
		case 2:/*simplesearch*/
			$binds=array();
			$viewing="Search: <span style='color:#999'>&#34;</span>".stripslashes($get_arr['simplesearch'])."<span style='color:#999'>&#34;</span>";
			$termfields=array("p.`prod_title`","p.`description`","p.`type`","p.`lens`","p.`windage`","p.`reticule`","p.`eyepiece_focus`","p.`parallax`","p.`magnification`","p.`power_selector`","p.`waterproof`","p.`ballistics`","p.`display`","v.`vname`","v.`field1`","v.`field2`","v.`field3`","v.`field4`","c.`title`","c.`ctype`","c.`description`");
			$simplesearch=strtolower($get_arr['simplesearch']);
			$get_premium=$simplesearch=="beretta premium"||$simplesearch=="premium"?1:0;
			$terms="(";
			$terms.="brand LIKE ?";$binds[]="%".$simplesearch."%";
			foreach($termfields as $tfieldid => $tfield){$terms.=" OR ".$tfield." LIKE ?";$binds[]="%".$simplesearch."%";}	
			if($get_premium){$terms.=" OR premium='y'";}
			$terms.=")";
			$sorting.=$get_premium==1?"&amp;premium=y":"";
			if(count($visible)>0)
			{
				$bins=bindINs(implode(",",$visible));
			}
			$query="SELECT `brand`,min(`price`),`prod_title`,p.`pid`,`premium`,`type`,`introtext`,f.`fusionId`,cf.`allowpurchase`,c.layout,
c.title as path1,
(SELECT title FROM gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.ownerId AND t2.itemType='category' AND t2.ownerType='category' WHERE t2.itemId=c.cid) as path2,
(SELECT cid FROM gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.ownerId AND t2.itemType='category' AND t2.ownerType='category' WHERE t2.itemId=c.cid) as path2id,
(SELECT title FROM gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.ownerId AND t2.itemType='category' AND t2.ownerType='category' WHERE t2.itemId=path2id) as path3,
(SELECT cid FROM gmk_categories as t1 JOIN fusion as t2 ON t1.cid=t2.ownerId AND t2.itemType='category' AND t2.ownerType='category' WHERE t2.itemId=path2id) as path3id FROM ((((gmk_products as p LEFT JOIN cart_fusion as cf ON cf.`pid`=p.`pid`) JOIN cart_variants as v ON p.`pid`=v.`pid`) JOIN fusion as f ON p.`pid`=f.`itemId`) JOIN gmk_categories as c ON c.`cid`=f.`ownerId` AND c.visible=1 AND f.`ownerType`='category') JOIN gmkbrands as b ON b.`id`=p.`bid` WHERE (p.`displayed`='1'".($allowcart?" OR cf.`allowpurchase`='1'":"").") AND b.`id` NOT IN('".$bins[0]."') AND $terms ".(strlen($get_type)>0?" AND `type` = ?":"")." GROUP BY p.`pid` ORDER BY ".$_SESSION['sort_by']." ".$_SESSION['sort_dir'];
			//echo $query;
			if(count($bins[1])>0){$binds=array_merge($binds,$bins[1]);}
			if(strlen($get_type)>0){$binds[]=$get_type;}
			$url="./products&amp;simplesearch=".urlencode($simplesearch);	
			$numbits=pagenums($query,$url.$sorting,$perpage,$maxpagelinks,'',$binds);			
			?>
			<input type="hidden" name="simplesearch" value="<?=$get_arr['simplesearch']?>" />						
			<?php
			break;
		default:
			$viewing=isset($get_arr['viewing'])?$get_arr['viewing']:($depth>0?$subdirs[$depth-1]:"");
			if(stripos($code,"-")!==false){list($cat,$brand)=explode("-",$code);}
			else{$cat=$code;}
			if(isset($brand)&&in_array($brand,$visible))
			{
				?><div class='largenotify'><img src="content/images/main/lightforce.jpg" alt="Coming Soon..." /></div><?php
			}
			else
			{
				$binds=array();
				if(isset($brand)&&strlen($brand)>0){$where.=" p.bid=:brand AND";$binds[':brand']=$brand;}
				if(isset($cat)&&strlen($cat)>0){$where.=" f.ownerId=:cat AND f.ownerType='category' AND f.itemType='product' AND";$binds[':cat']=$cat;}
				$get_premium=isset($get_arr['premium'])?1:0;
				$sorting.=$get_premium==1?"&amp;premium=y":"";
				/*$query="SELECT `brand`,min(`price`),`prod_title`,p.`pid`,`premium`,`type`,`introtext`,f.`fusionId`,cf.`allowpurchase` FROM (((gmk_products as p LEFT JOIN cart_fusion as cf ON cf.`pid`=p.`pid`) JOIN cart_variants as v ON p.`pid`=v.`pid`) JOIN fusion as f ON p.`pid`=f.`itemId`) JOIN gmkbrands as b ON b.`id`=p.`bid` WHERE $where (p.`displayed`='1'".($allowcart?" OR cf.`allowpurchase`='1'":"").") ".(strlen($get_type)>0?" AND `type` = '$get_type'":"").($get_premium==1?" AND `premium` = 'y'":"")." GROUP BY p.`pid` ORDER BY $sort_by $sort_dir";*/
				$query="SELECT `brand`,min(`price`),`prod_title`,p.`pid`,`premium`,`type`,`introtext`,f.`fusionId`,cf.`allowpurchase`,f.`ownerId` FROM (((gmk_products as p LEFT JOIN cart_fusion as cf ON cf.`pid`=p.`pid`) JOIN cart_variants as v ON p.`pid`=v.`pid`) JOIN fusion as f ON p.`pid`=f.`itemId`) JOIN gmkbrands as b ON b.`id`=p.`bid` WHERE $where (p.`displayed`='1'".($allowcart?" OR cf.`allowpurchase`='1'":"").") ".(strlen($get_type)>0?" AND `type` = :type":"").($get_premium==1?" AND `premium` = 'y'":"")." GROUP BY p.`pid` ORDER BY min(price) ".$_SESSION['sort_dir'];			
				$url=$depth>0?"./".$_GET['mp']:"index.php?p=products&amp;code=$cat-$brand&amp;viewing=".urlencode($viewing);
				if(strlen($get_type)>0){$binds[':type']=$get_type;}
				$numbits=pagenums($query,$url.$sorting,$perpage,$maxpagelinks,'',$binds);				
				?>			
				<input type="hidden" name="code" value="<?=$code?>" />
				<input type="hidden" name="viewing" value="<?=$viewing?>" />				
				<?			
			}
			break;
	}
	if(!(isset($code)&&in_array(substr($code,3),$visible))){
	rsort($types);	
	?>
	<?php if($get_premium){?><input type="hidden" name="premium" value="y" /><?php }?>
	<?php if(strlen($get_type)>0){?><input type="hidden" name="type" value="<?=$get_type?>" /><?php }?>
	<?php if(count($types)>1||strlen($get_type)>0){?>
	<div id="quicksearchmain_terms">
		Filter by Type: <?php if(strlen($get_type)>0){?><span><?=ucwords($get_type)?> (<a href="<?=$url."&amp;sortby=".urlencode($_SESSION['sort_by'])."&amp;sortdir=".urlencode($_SESSION['sort_dir'])?>">Remove</a>)</span><?php }else{?><span><?php foreach($types as $type){?> <input type="radio" name="type" value="<?=$type?>" id="label<?=$type?>" onclick="javascript: this.form.submit()" /><label for="label<?=$type?>" style="vertical-align:middle"> <?=ucwords($type)?></label>&nbsp;<?php }?></span><?php }?>
		</div><?php }?>
		<div id='quicksearchmain_sorting'>Sort By Price:&nbsp;&nbsp;<span id='sortbox'>
		<!--<select name="sortby">
		<option value="f.sorting"<?/* if($sort_by == "f.sorting"){*/?> selected="selected"<?/* }*/?>>Default</option>
		<option value="min(price)"<?// if($sort_by == "min(price)"){?> selected="selected"<?// }?>>Price</option>
		</select>&nbsp;&nbsp;--><label for="asc">Low-High&nbsp;&nbsp;<input type="radio" name="sortdir" value="ASC" id="asc"<?php if($_SESSION['sort_dir'] == "ASC"){?> checked="checked"<?php }?> /></label>&nbsp;&nbsp;<label for="desc">High-Low&nbsp;&nbsp;<input type="radio" name="sortdir" value="DESC" id="desc"<?php if($_SESSION['sort_dir'] == "DESC"){?> checked="checked"<?php }?> /></label>
		</span>
		<input type="submit" value="Submit" class="button" />
		</div>
		<div class="clear"></div>				
	</div>
	<div id='infobar'>Viewing <?=urldecode($viewing)?> <span class='pages'><?php if($prods_num > 0){?><?=1+($get_page*$perpage)-$perpage?>-<?=$get_page*$perpage>$prods_num?$prods_num:$get_page*$perpage?> of <?php }?><?=$prods_num?> products</span><?php if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&in_array($prodmod,$mods)){?> <a style="color:#FFF" href="./admin/index.php?p=products&owner=<?=urlencode($cat)?>" target="_blank">Sort Items</a> | <a style="color:#FFF" href="./admin/index.php?p=products&amp;showing=catform&amp;cid=<?=urlencode($cat)?>" target="_blank">Edit Cat</a> | <a style="color:#FFF" href="./admin/index.php?p=products&amp;showing=prodform&amp;owner=<?=urlencode($cat)?>" target="_blank">Add Item</a><?php }?></div>
	</form>	
	<div style='float:left;margin-top:3px;'>
	
	<?php 
	/*$sock=ysql_query($numbits[0]);
	$vest=mysql_fetch_assoc($sock);
	
	if($vest['allowpurchase']==1){?>
	<span style="font-size:20px"><img src="./content/images/main/view_in_shop.png" alt="&#9672;" /></span> Click this icon to view in our online shop.<br />
	<?php }?>
	*/
	?>
	
	
	</div>
	<div style='float:right;margin-top:9px;'><?=$numbits[1]?></div>
	<div class="clear"></div>
	<?php productbox($numbits[0],$binds);?>
	<br />
	<div style='float:left'></div>
	<div style='float:right'><?=$numbits[1]?></div>
	<div class="clear"></div>
	<?php 
	}
}?>