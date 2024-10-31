<?php 
/*
Plugin Name: Pinterest Badge
Plugin URI: http://www.pinterestbadge.skipser.com
Description: Adds Pinterest contact badge widget to your blog
Version: 1.8.0
Author: Arun
Author URI: http://www.skipser.com
License: GPL3
*/

/*  
* 	Copyright (C) 2011  Skipser
*	http://skipser.com
*	http://www.pinterestbadge.skipser.com
*
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/********************************************************************/
/*                                                                  */
/*                         Global variables                         */
/*                                                                  */
/********************************************************************/

$USERID = '';
$WIDTH = '180';
$NAMECOL = '3B5998';
$TEXTCOL = '333333';
$BORDERWIDTH = '1';
$SEPCOLOR = 'DDDDDD';
$PINS='9';
$DEBUG_ENABLED = 'false';

/********************************************************************/
/*                                                                  */
/*            Do not change anything below this.                    */
/*                                                                  */
/********************************************************************/

define( 'PINTEREST_PLUGIN_NAME', 'pinterestBadge');
define( 'PINTEREST_PLUGIN_DIRECTORY', 'pinterest-badge');
define( 'PINTEREST_CURRENT_VERSION', '1.8.0' );
define( 'PINTEREST_DEBUG', false);

function pinterestBadge($pinid, $width, $bg, $bcol, $htxt, $pins=9, $pinsize='big', $credit=1) {
	global $USERID, $WIDTH, $NAMECOL, $TEXTCOL, $BORDERWIDTH, $SHOWPINS, $SEPCOLOR;

	$DEBUG_ENABLED='false';
	if (preg_match('/pinterestbadgedebug=true/s', $_SERVER['REQUEST_URI'])) {
		$DEBUG_ENABLED='true';
	}
	$FORCE_REFRESH='false';
	if (preg_match('/fullrefresh=true/s', $_SERVER['REQUEST_URI'])) {
		$FORCE_REFRESH='true';
	}

	$USERID = $pinid;
	if($width != '') { if($BORDERWIDTH != '') {$width=$width-(2*$BORDERWIDTH);} $WIDTH='width:'.$width.'px;'; } else {$WIDTH=''; }
	if(isset($pins)) {
		$PINS=$pins;
	}
	$SHOWCREDIT='false';
	if (isset($credit) && $credit > 0)  {
		$SHOWCREDIT='true';
	}
	if(isset($bg) && $bg != '') { $BACKGROUND = '#'.$bg; } else {$BACKGROUND = 'inherit'; }
	if(isset($htxt) && $htxt != '') { $FOLLOWTEXT = $htxt; } else {$FOLLOWTEXT = 'Recent Pins.'; }
	if($bcol == '') { $BORDERCOLOR='FFFFFF'; $BORDERWIDTH=0;} else { $BORDERCOLOR = $bcol; }
	if($pinsize=='small') {$IMGSIZE = 55; $IMGID='pinimgsmall'; } else { $IMGSIZE = 95; $IMGID='pinimg'; }
	$CONTENT_MARGIN=60;
	$IMGS='';

	// include loader class
	include_once('pinterestbadgehelper.php');

	//Check if we have a pinterest id
	if($USERID == '') { ?>

<div id="pinbadgewrapper2" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper1" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper" style="border:<?php echo $BORDERWIDTH ?>px solid #<?php echo $BORDERCOLOR ?>; <?php echo $WIDTH ?> overflow:hidden; background-color:<?php echo $BACKGROUND ?>; text-align:left;text-shadow:none;margin:0;padding:0;font-family:'lucida grande',tahoma,verdana,arial,sans-serif;font-size:11px;font-weight:normal;color:#333333;clear:both;">
	<div id="pinbadge" >
		<div style="margin:8px;">
		Please give a valid pinterest id.
		</div>
	</div>
	<div style="padding:0 8px; height:20px;">
		<div style="border-top:1px solid #<?php echo $SEPCOLOR ?>;">
			<div id="pinbadgeCredit">
				<p><a href="http://www.pinterestbadge.skipser.com" style="text-decoration:none;"><strong>Pinterest Badge</strong></a> by <a href="http://www.skipser.com" style="text-decoration:none;color:#333333;">Skipser</a></p>
			</div>
		</div>
	</div>
</div>
</div>
</div>
		
<?php
		if($SHOWCREDIT=='false') {
			echo '<script type="text/javascript">var credit=document.getElementById("pinterestbadgeCredit");if(credit){credit.style.display="none";}var foll=document.getElementById("pinterestbadgeFollowerCount");if(foll){foll.style.display="block";}</script>';
		}
		return;
	}

	// initiate an instance of our loader class
	$pin = new pinterestBadge($USERID, $DEBUG_ENABLED, $FORCE_REFRESH);

	// if we can use file caching
	if (pc_caching($DEBUG_ENABLED)) {
		$pin->cache_data = 1;
		$pin->cache_file = WP_CONTENT_DIR . "/cache/pinterestbadges.txt";
		$pin->regexp_file = WP_CONTENT_DIR . "/cache/pinterestregexp.txt";

		// do the scrape
		$data = $pin->pinterestBadge();
		if($DEBUG_ENABLED == 'true') {
			$pin->fullbadgetmpl = <<<EOT
<!-- Pinterest Badge by Skipser -->
<div id="pinbadgewrapper2" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper1" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper" style="border:#BORDERWIDTH#px solid ##BORDERCOLOR#; #WIDTH# overflow:hidden; background-color:#BACKGROUND#; text-align:left;text-shadow:none;margin:0;padding:0;font-family:'lucida grande',tahoma,verdana,arial,sans-serif;font-size:11px;font-weight:normal;color:#333333;clear:both;">
	<div id="pinbadge" >
		<div id="pinimgs">
		#IMGS#
		</div>
		<div id="pinfollow" style="clear:both;">
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
		}
		if($PINS > 0) {
			for($i=1; $i<=$PINS; $i++) {
				$j='pin'.$i;
				$k='pinlink'.$i;
				if($data[$j] != '') {
					$IMGS .= '<a href="'.$data[$k].'"><img src="'.$data[$j].'" id="'.$IMGID.'" /></a>';
				}
			}
		}
		if($SHOWCREDIT=='false') {
			$pin->fullbadgetmpl = preg_replace('/<div id="pinbadgeCreditq"/s', '<div id="pinbadgeCreditq" style="display:none;"', $pin->fullbadgetmpl);
		}

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
		$pin->fullbadgetmpl = preg_replace('/<div id="pinbadgeFollowerCount" style="/', '<div id="pinbadgeFollowerCount" style="display:none;', $pin->fullbadgetmpl);
		$pin->fullbadgetmpl = preg_replace('/<div id="pinbadgeCredit" style="display:none;">/', '<div id="pinbadgeCredit">', $pin->fullbadgetmpl);
		//if($SHOWCREDIT=='false') {
		//	$pin->fullbadgetmpl = preg_replace('/start_credit.*end_credit/s', '', $pin->fullbadgetmpl);
		//}
		echo $pin->fullbadgetmpl;
		if($SHOWCREDIT=='false') {
			echo '<script type="text/javascript">var credit=document.getElementById("pinbadgeCredit");if(credit){credit.style.display="none";}var foll=document.getElementById("pinbadgeFollowerCount");if(foll){foll.style.display="block";}</script>';
		}

	} else { ?>

<div id="pinbadgewrapper2" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper1" style="text-align:left;text-shadow:none;margin:0;padding:0;border:0;clear:both;">
<div id="pinbadgewrapper" style="border:<?php echo $BORDERWIDTH ?>px solid #<?php echo $BORDERCOLOR ?>; <?php echo $WIDTH ?> overflow:hidden; background-color:<?php echo $BACKGROUND ?>; text-align:left;text-shadow:none;margin:0;padding:0;font-family:'lucida grande',tahoma,verdana,arial,sans-serif;font-size:11px;font-weight:normal;color:#333333;clear:both;">
	<div id="pinbadge" >
		<div style="margin:8px;">
		Caching failed. Please contact plugin owner.
		</div>
	</div>
	<div style="padding:0 8px; height:20px;">
		<div style="border-top:1px solid #<?php echo $SEPCOLOR ?>;">
			<div id="pinbadgeCredit" style="display:block;">
				<p><a href="http://www.pinterestbadge.skipser.com" style="text-decoration:none;"><strong>Pinterest Badge</strong></a> by <a href="http://www.skipser.com" style="text-decoration:none;color:#cccccc;">Skipser</a></p>
			</div>
		</div>
	</div>
</div>
</div>
</div>

<?php
	}
}


class PinterestBadgeWidget extends WP_Widget {
	/** constructor */
	function PinterestBadgeWidget() {
		parent::WP_Widget(false, $name = 'Pinterest Badge');
		$css = plugins_url().'/pinterest-badge/pinterest.css';
		wp_enqueue_style('pinterestBadge', $css);
		$js = plugins_url().'/pinterest-badge/pinterest_wp.js';
		wp_enqueue_script('pinterestBadge', $js);
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['htxt']);
		?>
			<?php echo $before_widget; ?>
				<?php if ( $title ) echo $before_title . $title . $after_title; ?>
				<?php pinterestBadge($instance['pinid'], $instance['width'], $instance['bg'], $instance['bcol'], $instance['htxt'], $instance['pins'], $instance['pinsize'], $instance['credit']); ?>
			<?php echo $after_widget; ?>
		<?php
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['pinid'] = strip_tags($new_instance['pinid']);
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['bg'] = strip_tags($new_instance['bg']);
		$instance['bcol'] = strip_tags($new_instance['bcol']);
		$instance['hbg'] = strip_tags($new_instance['hbg']);
		$instance['htxt'] = strip_tags($new_instance['htxt']);
		$instance['pins'] = strip_tags($new_instance['pins']);
		$instance['pinsize'] = strip_tags($new_instance['pinsize']);
		$instance['credit'] = ( isset( $new_instance['credit'] ) ? 1 : 0 );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {

		$pinid=''; $width=''; $pins='9'; $bg=''; $bcol = ''; $hbg = '2D2D2D'; $htxt='Recent Pins.'; 

		if ($instance) {
			$pinid = esc_attr($instance['pinid']);
			$width = esc_attr($instance['width']);
			$bg = esc_attr($instance['bg']);
			$bcol = esc_attr($instance['bcol']);
			$hbg = esc_attr($instance['hbg']);
			$htxt = esc_attr($instance['htxt']);
			$pins = esc_attr($instance['pins']);
			$pinsize = esc_attr($instance['pinsize']);
			$credit = isset($instance['credit']) ? $instance['credit'] : true;
		} else {
			$defaults = array('pinid' => '', 'width' => '100', 'bg' => '', 'bcol' => '', 'hbg' => '2D2D2D', 'htxt' => 'Recent Pins.', 'pins' => '9', 'pinsize' =>'big', 'credit' => 'true');
			$instance = wp_parse_args( (array) $instance, $defaults );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('pinid'); ?>"><?php _e('Pinterest ID:'); ?></label> 
			<label><br/>Eg. For <span style="color:red;">www.pinterest.com/skipser</span>, the id is <span style="color:red;">skipser</span>.</label>
			<input class="widefat" id="<?php echo $this->get_field_id('pinid'); ?>" name="<?php echo $this->get_field_name('pinid'); ?>" type="text" value="<?php echo $pinid; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('pins'); ?>"><?php _e('Number of pins to show (max 9):'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('pins'); ?>" name="<?php echo $this->get_field_name('pins'); ?>" type="text" value="<?php echo $pins; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('pinsize'); ?>"><?php _e('Pin Image Size:'); ?></label> 
			<select class="select" id="<?php echo $this->get_field_id( 'pinsize' ); ?>" name="<?php echo $this->get_field_name( 'pinsize' ); ?>" >
				<option <?php if($instance['pinsize'] == 'big') { echo 'selected="selected"'; } ?>>big</option>
				<option <?php if($instance['pinsize'] == 'small') { echo 'selected="selected"'; } ?>>small</option>
			</select>
		</p>
		<p><span style="background-color:#CCCCCC; width:200px;"><h3>...........Title Settings ...........</h3></span></p>
		<p>
			<label for="<?php echo $this->get_field_id('htxt'); ?>"><?php _e('Title:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('htxt'); ?>" name="<?php echo $this->get_field_name('htxt'); ?>" type="text" value="<?php echo $htxt; ?>" />
		</p>
		<p><span style="background-color:#CCCCCC; width:200px;"><h3>...........Other Settings ...........</h3></span></p>
		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Badge Width<strong> (Not mandatory)</strong>: '); ?><abbr style="border-bottom: 1px dotted black; color:<?php echo $color;?>; font-weight:bold;" title="pinterest badge will use the full width automatically. Use this only if you want a narrower badge for some reason.">?</abbr></label>
			<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('bg'); ?>"><?php _e('Background Color:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('bg'); ?>" name="<?php echo $this->get_field_name('bg'); ?>" type="text" value="<?php echo $bg; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('bcol'); ?>"><?php _e('Border Color:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('bcol'); ?>" name="<?php echo $this->get_field_name('bcol'); ?>" type="text" value="<?php echo $bcol; ?>" />
		</p>
		<p>
			<?php (isset($instance['credit']) && $instance['credit'] == true) ? $color = 'green' : $color = 'red'; ?>

			<input class="checkbox" type="checkbox" <?php checked( (bool) $instance['credit'], true ); ?> id="<?php echo $this->get_field_id( 'credit' ); ?>" name="<?php echo $this->get_field_name( 'credit' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'credit' ); ?>"><?php _e('Show developer credit '); ?><abbr style="border-bottom: 1px dotted black; color:<?php echo $color;?>; font-weight:bold;" title="Whether to show the 'pinterest badge by skipser' message when a user clicks on '?'. We really appreciate you leaving this enabled as it is the best way for other people to find out about this plugin (which we've worked very hard on!) and we promise to love you forever if you do. The message is inobtrusive, your followers are always shown.">?</abbr></label>
		</p>
		<?php 
	}

} 

// register PinterestBadge widget
add_action('widgets_init', create_function('', 'return register_widget("PinterestBadgeWidget");'));


function pc_caching($debug) {
	if (!is_dir(WP_CONTENT_DIR . "/cache")) {
		if($debug == 'true') {
			echo ">>>>No cache dir. Creating<br/>";
		}
		mkdir (WP_CONTENT_DIR . "/cache", 0777, true);
	}
	if (is_dir(WP_CONTENT_DIR . "/cache") && is_writable(WP_CONTENT_DIR . "/cache")) {
		$cache = WP_CONTENT_DIR . "/cache/pinterestbadges.txt";
		return true;
	}
	else {
		if($debug == 'true') {
			if (!is_dir(WP_CONTENT_DIR . "/cache")) {
				echo ">>>>Cache dir does not exist<br/>";
			} else {
				echo ">>>>Cache dir is not writable<br/>";
			}
		}
		return false;
	}
}
?>
