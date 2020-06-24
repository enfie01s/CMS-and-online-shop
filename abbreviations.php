<?php
$abbrev = array();
$abbrev['beretta'] = array(
		"SNS"=>"Single non-selective trigger",
		"O/C.F"=>"Optichoke (Flush)",
		"O/C.E"=>"Optichoke (Extension)",
		"O/C.P"=>"Optichoke (Plus)",
		"O/C.HP"=>"Optichoke (High Performance)",
		"DT"=>"Double Trigger",
		"SR"=>"Stepped rib",
		"SH"=>"Straight hand stock",
		"SC"=>"Colour Hardened Finish",
		"PG"=>"Pistol grip stock",
		"M/C"=>"Multichoke",
		"SST"=>"Single selective trigger",
		"G/S"=>"Game scene engraving",
		"S"=>"Skeet",
		"CYL"=>"Cylinder",
		"F"=>"Full",
		"X/G"=>"&quot;X-Tra Grain&quot; stock finish",
		"B/Tail"=>"Beavertail Forend"
	);
$abbrev['benelli'] = array(
		"F"=>"Full / *",
		"IM"=>"3/4 / **",
		"M"=>"1/2 / ***",
		"IC"=>"1/4 / ****",
		"C"=>"CYL / C****",
		"N/RIB"=>"No Rib",
		"V/RIB"=>"Ventilated Rib",
		"S/RIB"=>"Stepped Rib",
		"CF/RIB"=>"Carbon Fibre rib",
		"P/GRIP"=>"Full Pistol grip stock",
		"GA"=>"Gauge",
		"M/C"=>"Mobilchoke",
		"EXT."=>"External choke",
		"R/SIGHT"=>"Rifle sight",
		"G/SIGHT"=>"Adjustable ghost ring backsight",
		"W/OUT"=>"Without",
		"C/TEC"=>"Comfortec stock",
		"D&amp;T"=>"Drilled and tapped receiver"
	);
$abbrev['lanber'] = array(
		"M/C"=>"Multichoke",
		"Ga"=>"Gauge"
	);
$abbrev['franchi'] = array(
		"M/C"=>"Multichoke",
		"Ga"=>"Gauge",
		"E.M/C"=>"Extended multichoke"
	);
$abbrev['arrieta'] = array(
		"DT"=>"Double trigger",
		"SH"=>"Straight hand stock",
		"Ga."=>"Gauge",
		"L/H"=>"Left Handed"
	);
$abbrev['sako'] = array(
		/* xxs */
		"AM"=>".17 Mach 2",
		"AH"=>".17 HMR",
		"B"=>".22 LR",
		"BM"=>".22 WMR",
		"G"=>".204 Ruger*",
		"C"=>".222 Rem",
		"D"=>".223 Rem (1:12)",
		"D-8"=>".223 Rem (1:8)",
		"D-8*"=>".223 Rem (1:8)*",
		"D-8/D-8*"=>".223 Rem (1:8)/.223 Rem (1:8)*",
		/* sml */
		"E"=>".22-250 Rem",
		"H"=>".243 Win",
		"I"=>".260 Rem*",
		"M"=>"7mm-08 Rem*",
		"R"=>".308 Win",
		"K"=>".270 Win",
		/* med */
		"PS"=>".300WSM*",
		"L"=>"7mm Rem Mag*",
		"N"=>"7 x 64*",
		"P"=>".300 Win Mag*",
		"Q"=>".30-06",
		"S"=>".338 Win Mag*",
		"T"=>".375 H&amp;H Mag",
		"U"=>".416 Rem Mag*",
		"V"=>".338 Lapua Mag*",
		"W"=>".338 Federal*",
		/* l */
		"KS"=>".270 WSM*",
		"J"=>".25-06 Rem",
		"F"=>"6.5x55 SE"
		/* xl */
	);
$abbrev['tikka'] = $abbrev['sako'];
	
function abbreviationsKey($brand)
{
	//$the_array = "abbrev_".$brand;//get array name as string (eg: "abbrev_benelli")
	global $abbrev;
	$guntype = ($brand == "sako" || $brand == "tikka") ? "sako &amp; tikka" : $brand;
	$abbr_array = $abbrev[$brand];//make var from string.
	if(count($abbr_array) > 0)
	{
		$colsize = (count($abbr_array) < 8) ? count($abbr_array) : ceil(count($abbr_array)/3);
		$abbvwidth = (count($abbr_array) < 8) ? "259" : "775";
		$totalabbr = count($abbr_array);
		$loop = 0;
		$loop2 = 0;
		?>
		<br />
		<div class="abbv_key_title">Key to <?=$guntype?> abbreviations:</div>
		<div class="abbv_box" style="width:<?=$abbvwidth?>px">
		<?php
		foreach($abbr_array as $abbreviation => $meaning)
		{
			if($loop == 0){?><div style="float:left;width:258px;margin:1px 0px;"><?php }
			$loop++;
			$loop2++;
			?>
			<div style="float:left;width:50px;" class="abbv_key"><?=$abbreviation?> </div><div style="float:left;" class="abbv_meaning"><?=$meaning?></div>
			<div class='clear'></div>
			<?php
			if($loop == $colsize || $loop2 == $totalabbr){?></div><?php $loop = 0;}
		}
		?>
		<div class='clear'></div>
		</div>
		<?php if($brand == "sako" || $brand == "tikka"){?><div>Calibers marked with * are available to special order only</div><?php }
	}
}
?>