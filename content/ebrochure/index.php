<?php
$b_page = $_GET['b'];
//include "../functions.php";
require_once "../Mobile_Detect.php";
$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

if($deviceType=="computer"){

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>GMK eBrochure</title>

<link href="css/gmk.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="js/liquid.js"></script>
<script type="text/javascript" src="js/swfobject.js"></script>
<script type="text/javascript" src="js/flippingbook.php?b=<?php print $b_page; ?>"></script>
<script type="text/javascript" src="js/bookSettings.php?b=<?php print $b_page; ?>"></script>

</head>
<body>
<div id="fbContainer">
    <a class="altlink" href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"><div id="altmsg">Download Adobe Flash Player.</div></a>
</div>

<div id="fbFooter">
    <div id="fbContents">
        <select id="fbContentsMenu" name="fbContentsMenu"></select>
        <span class="fbPaginationMinor">p.&nbsp;</span>
        <span id="fbCurrentPages">1</span>
        <span id="fbTotalPages" class="fbPaginationMinor"></span>
    </div>
    <div id="fbMenu">
        <img src="img/btnDiv.gif" width="13" height="40" border="0" />
        <img src="img/btnZoom.gif" width="36" height="40" border="0" id="fbZoomButton" alt="Zoom" />
        <img src="img/btnPrint.gif" width="36" height="40" border="0" id="fbPrintButton" alt="Print Page" />
        <img src="img/btnDownload.gif" width="36" height="40" border="0" id="fbDownloadButton" alt="Download" />
        <a href="../brochures/<?=$b_page?>/<?=$b_page?>.pdf" target="_blank"><img src="img/btnDownloadAlt.gif" width="36" height="40" border="0" id="fbDownloadButtonAlt" alt="Alternate Download" /></a>
        <img src="img/btnDiv.gif" width="13" height="40" border="0" />
        <img src="img/btnPrevious.gif" width="36" height="40" border="0" id="fbBackButton" />
        <img src="img/btnNext.gif" width="36" height="40" border="0" id="fbForwardButton" />
    </div>
</div>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1536461-1";
urchinTracker();
</script>
</body>
</html>
<?php }else{
  $file = '../brochures/'.$b_page.'/'.$b_page.'.pdf';
  $filename = $b_page.'.pdf';
  header('Content-type: application/pdf');
  header('Content-Disposition: inline; filename="' . $filename . '"');
  header('Content-Transfer-Encoding: binary');
  header('Accept-Ranges: bytes');
  @readfile($file);
}?>