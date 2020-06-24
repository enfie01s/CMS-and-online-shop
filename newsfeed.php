<?php header("Content-Type: application/xml; charset=ISO-8859-1"); 
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
	<title>GMK News &amp; Events</title>
  <link>http://www.gmk.co.uk/news</link>
  <description>Product and Event updates</description>
	<copyright>'.date("Y").' GMK Ltd. All rights reserved.</copyright>
	<atom:link href="http://www.gmk.co.uk/newsfeed.php" rel="self" type="application/rss+xml" />
';

include "../config.inc.php";
$newsarray = array();
$x=0;
/*$query = ysql_query("SELECT * FROM gmknews WHERE `display`='T' ORDER BY `date` DESC, `id` DESC",$con1) or die(sql_error("Error"));
while($result = mysql_fetch_assoc($query))*/
$query = $db1->query("SELECT * FROM gmknews WHERE `display`='T' ORDER BY `date` DESC, `id` DESC");
while($result = $query->fetch())
{
	$newsarray[$x] = $result;
	$x++;
}
foreach($newsarray as $i => $news)
{
	$date=date("r",$news['date']);
	?>
	<item>
	<title><?=str_replace("&pound;","&#163;",htmlspecialchars($news['title'],ENT_QUOTES,"ISO-8859-1"))?></title>
	<pubDate><?=$date?></pubDate>
	<link>http://www.gmk.co.uk/news/id/<?=$i?></link>
	<description><?=str_replace(array("Â","£","&pound;"),array("","&#163;","&#163;"),htmlspecialchars($news['intro'],ENT_QUOTES,"ISO-8859-1"))?></description>
	<guid>http://www.gmk.co.uk/news/id/<?=$i?></guid>
	</item><?php
}
echo '</channel></rss>';
?>