<?php 

$USERID      = $_GET['uid'];
if(isset($_GET['width'])) { $WIDTH = "width:".$_GET['width']."px;";} else {$WIDTH = ""; }
if(isset($_GET['debug'])) { $DEBUG = $_GET['debug']; } else {  $DEBUG   = 'false'; }
if(isset($_GET['bcol'])) { $BORDERCOLOR    = $_GET['bcol']; } else {  $BORDERCOLOR    = 'AAAAAA'; }
if(isset($_GET['bwidth'])) { $BORDERWIDTH    = $_GET['bwidth']; } else {  $BORDERWIDTH    = '1'; }
if(isset($_GET['sepcol'])) { $SEPCOLOR    = $_GET['sepcol']; } else {  $SEPCOLOR    = 'DDD'; }
if(isset($_GET['pins'])) { $PINS    = $_GET['pins']; } else {  $PINS    = '9'; }
if(isset($_GET['pinsize'])) { $pinsize    = $_GET['pinsize']; } else {  $pinsize    = 'big'; }
if(isset($_GET['followtext'])) { $FOLLOWTEXT    = $_GET['followtext']; } else {  $FOLLOWTEXT    = 'Recent Pins'; }
if(isset($_GET['bg'])) { $BACKGROUND = '#'.$_GET['bg']; } else {$BACKGROUND = 'inherit'; }

$CONTENT_MARGIN=60;
$IMGS='';
if($pinsize=='small') {$IMGSIZE = 55; $IMGID='pinimgsmall'; } else { $IMGSIZE = 95; $IMGID='pinimg'; }
$TEXTCOL = '333333';


function gc_caching() {
	if (!is_dir("cache")) {
		mkdir ("cache", 0777, true);
	}
	if (is_dir("cache") && is_writable("cache")) {
		$cache = "cache/pinterestbadges.txt";
		return true;
	}
	else {
		return false;
	}
}
// include loader class
if($DEBUG == 'true') {
	include_once('pinterestbadgehelper_debug.php');
} else {
	include_once('pinterestbadgehelper.php');
}

// initiate an instance of our loader class
$pin = new pinterestBadge($USERID);
// if we can use file caching
?>
<html>
<head>
	<link rel="stylesheet" href="pinterest.css" type="text/css" />
<body>
<?php
if (gc_caching()) {
	$pin->cache_data = 1;
	$pin->cache_file = "cache/pinterestbadges.txt";
	$pin->regexp_file = "cache/pinterestregexp.txt";

	// do the scrape
	$data = $pin->pinterestBadge();
	if($PINS > 0) {
			for($i=1; $i<=$PINS; $i++) {
				$j='pin'.$i;
				$k='pinlink'.$i;
				if($data[$j] != '') {
					$IMGS .= '<a href="'.$data[$k].'"><img src="'.$data[$j].'" id="'.$IMGID.'" /></a>';
				}
			}
		}
$pin->fullbadgetmpl = <<<EOT
<!-- Pinterest Badge by Skipser -->
<div id="pinbadgewrapper2" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper1" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper" style="border:#BORDERWIDTH#px solid ##BORDERCOLOR#; #WIDTH# overflow:hidden; background-color:#BACKGROUND#; text-align:left;text-shadow:none;margin:0;padding:0;font-family:'lucida grande',tahoma,verdana,arial,sans-serif;font-size:11px;font-weight:normal;color:#333333;clear:both;">
	<div id="pinbadge" >
		<div id="pinimgs">
		#IMGS#
		</div>
		<div id="pinfollow">
			<a href="http://pinterest.com/#USERID#/"><img src="http://passets-cdn.pinterest.com/images/follow-on-pinterest-button.png" width="156" height="26" alt="Follow Me on Pinterest" /></a>
		</div>
	</div>
	<div style="clear:both;">
		<div style="padding:0 8px; height:20px;">
			<div style="border-top:1px solid ##SEPCOLOR#;">
				<div id="pinbadgeFollowerCount" style="color:##TEXTCOL#;">Followed by <strong>#DATA_COUNT#</strong> people.</div>
				<!--start_credit-->
				<div id="pinbadgeCreditq"><a href="http://www.skipser.com" onclick='var credit=document.getElementById("pinbadgeCredit");var foll = document.getElementById("pinbadgeFollowerCount");if (credit.style.display == "none"){credit.style.display = "block";foll.style.display="none";}else{credit.style.display = "none";foll.style.display="block";} return false;'>?</a></div>
				<div id="pinbadgeCredit" style="display:none;">
					<p><a href="http://www.pinterestbadge.skipser.com" style="text-decoration:none;"><strong>Pinterest Badge</strong></a> by <a href="http://www.skipser.com" style="text-decoration:none;color:#333333;font-weight:normal;">Skipser</a></p>
				</div>
				<!--end_credit-->
			</div>
		</div>
	</div>
</div>
</div>
</div>
EOT;

	$pin->fullbadgetmpl = preg_replace('/#USERID#/', $USERID, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#DATA_COUNT#/', $data['count'], $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#WIDTH#/', $WIDTH, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#BACKGROUND#/', $BACKGROUND, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#BORDERWIDTH#/', $BORDERWIDTH, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#BORDERCOLOR#/', $BORDERCOLOR, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#SEPCOLOR#/', $SEPCOLOR, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#CONTENT_MARGIN#/', $CONTENT_MARGIN, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#TEXTCOL#/', $TEXTCOL, $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/#IMGS#/', $IMGS, $pin->fullbadgetmpl);
	echo $pin->fullbadgetmpl;
} else { ?>

<div id="gplusbadgewrapper2" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="gplusbadgewrapper1" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="gplusbadgewrapper" style="border:<?php echo $BORDERWIDTH ?>px solid #<?php echo $BORDERCOLOR ?>; <?php echo $WIDTH ?> overflow:hidden; background-color:#<?php echo $BACKGROUND ?>; text-align:left;text-shadow:none;margin:0;padding:0;font-family:'lucida grande',tahoma,verdana,arial,sans-serif;font-size:11px;font-weight:normal;color:#333333;clear:both;">
	<div id="gplusbadge_header" style="background-color:#<?php echo $HEADERBG ?>;" >
		<span style="padding-left:5px;float:left;color:#<?php echo $HEADER_TXTCOL ?>;"><?php echo rawurldecode($FOLLOWTEXT) ?></span><img src="https://ssl.gstatic.com/images/icons/gplus-16.png" alt="" style="border:0;width:16px;height:16px;float:right;margin:6px 5px 0 0;" />
	</div>
	<div id="gplusbadge" >
		<div style="margin:8px;">
		Caching failed. Please contact plugin owner.
		</div>
	</div>
	<div style="padding:0 8px; height:20px;">
		<div style="border-top:1px solid #<?php echo $SEPCOLOR ?>;">
			<div id="gplusbadgeCredit" style="display:block;">
				<p><a href="http://www.gplusbadge.skipser.com" style="text-decoration:none;"><strong>Gplus Badge</strong></a> by <a href="http://www.skipser.com" style="text-decoration:none;"><strong>Skipser</strong></a></p>
			</div>
		</div>
	</div>
</div>
</div>
</div>

<?php } ?>
</body></html>