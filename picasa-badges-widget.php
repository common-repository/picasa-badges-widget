<?php
/*
Plugin Name: Picasa Badges Widget
Author URI: http://vandemeulenhof.com
Plugin URI: http://vandemeulenhof.com/picasa-badges-widget/
Description: Based on the Flickr Badges Widget, soon more info and screenshots!
Author: Robby van de Meulenhof
Version: 1.0

*/
 
load_plugin_textdomain( 'picasabadgeswidget', '/wp-content/plugins/picasa-badges-widget' );

function picasa_badges_widget_square($dim){
	return ($dim ==32) || ($dim ==48) || ($dim ==64) || ($dim ==75) || ($dim ==80) || ($dim ==92);
}

function picasa_badges_widget_albums(){
	$options = get_option('picasa_badges_widget_PicasaSidebar');
	$items = $options['items'];
	$maxres = $options['items'];
	
	if(file_exists(ABSPATH . WPINC . '/rss.php') )
		require_once(ABSPATH . WPINC . '/rss.php');
	else
		require_once(ABSPATH . WPINC . '/rss-functions.php');

	$rss = fetch_rss("http://picasaweb.google.com/data/feed/base/user/".$options['username']."?kind=album&alt=rss&access=public&max-results=".$maxres);
	if (is_array($rss->items)) {
		$i = 0;
		foreach($rss->items as $item) {
			$titlu = $item['title'];
			$titlu = str_replace("'","",$titlu);
			$titlu = str_replace('"',"",$titlu);
			$link = $item['link'];

			if($i==0){

					$rss2 = fetch_rss(str_replace("entry","feed",$item['guid'])."&kind=photo");
					if (!$rss2)
						continue;
					$j=0;
					foreach($rss2->items as $item2) {
						$titlu = $item2['title'];
						$titlu = str_replace("'","",$titlu);
						$titlu = str_replace('"',"",$titlu);
						preg_match('/.*src="(.*?)".*/',$item2['description'],$sub);
						$path = $sub[1];
						$path = str_replace("s288","s".$options['width'].(picasa_badges_widget_square($options['width'])?"-c":""),$path);
						$pozele[$j++] = "<a href='".$item2['link']."' target='_blank'><img src='".$path."' class='picasa-widget-img' alt='".$titlu."' title='".$titlu."'></a>";
					}
					
					rsort($pozele);
					for($k=0;$k<$items;$k++){
						$result .= $pozele[$k];
					} 
				}

			$i++;
		}
	}
	echo "".$result;
}

function picasa_badges_widget($args) {
	$options = get_option('picasa_badges_widget_PicasaSidebar');
	$title = $options['title'];
    extract($args);
	//echo $before_widget;
	echo $before_title . ($title==""?__('Latest photos','picasabadgeswidget'):$title) . $after_title;
	picasa_badges_widget_albums();
	//echo $after_widget; 
}

function picasa_badges_widget_control() {
	$options = $newoptions = get_option('picasa_badges_widget_PicasaSidebar');
	if ( $_POST["PicasaSidebar-submit"] ) {
		$newoptions['title'] = trim(strip_tags(stripslashes($_POST["PicasaSidebar-title"])));
		$newoptions['username'] = strip_tags(stripslashes($_POST["PicasaSidebar-username"]));
		$newoptions['items'] = trim(strip_tags(stripslashes($_POST["PicasaSidebar-items"])));
		$newoptions['width'] = trim(strip_tags(stripslashes($_POST["PicasaSidebar-width"])));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('picasa_badges_widget_PicasaSidebar', $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	$username = htmlspecialchars($options['username'], ENT_QUOTES);
	$items = htmlspecialchars($options['items'], ENT_QUOTES);
	$width = htmlspecialchars($options['width'], ENT_QUOTES);
	$e=($username=="");
	?>
	<p><label for="PicasaSidebar-title"><?php _e('Widget Title','picasabadgeswidget');?>:</label><br>
	<input class="widefat" id="PicasaSidebar-title" name="PicasaSidebar-title" type="text" value="<?php echo ($e?__("Photos","picasabadgeswidget"):$title); ?>" /></p>
	<p><label for="PicasaSidebar-feeds"><?php _e('Picasa username','picasabadgeswidget');?>:</label><br>
	<input class="widefat" id="PicasaSidebar-username" name="PicasaSidebar-username" value="<?php echo ($e?__("Your Picasa username","picasabadgeswidget"):$username); ?>" /></p>	
	<p><label for="PicasaSidebar-number"><?php _e('Number of items','picasabadgeswidget');?>:<br><small><?php _e('albums or pictures, based on widget mode','picasabadgeswidget');?></small> </label><br>
	<input class="widefat" id="PicasaSidebar-items" name="PicasaSidebar-items" type="text" value="<?php echo ($e?4:$items); ?>" /></p>	
	<p><label for="PicasaSidebar-width"><?php _e('Width of the thumbnails','picasabadgeswidget');?>:<br></label><br>
	<select id="PicasaSidebar-width" name="PicasaSidebar-width">
	<option value="32" <?php echo (($width == '32') ? 'selected' : ''); ?>>32</option>
	<option value="48" <?php echo (($width == '48') ? 'selected' : ''); ?>>48</option>
	<option value="64" <?php echo (($width == '64') ? 'selected' : ''); ?>>64</option>
	<option value="75" <?php echo (($width == '75') ? 'selected' : ''); ?>>75</option>
	<option value="80" <?php echo (($width == '80') ? 'selected' : ''); ?>>80</option>
	<option value="92" <?php echo (($width == '92') ? 'selected' : ''); ?>>92</option>
	</select></p>		
	<input type="hidden" id="PicasaSidebar-submit" name="PicasaSidebar-submit" value="1" />
	<?php
}


function init_picasa_badges_widget(){
	register_sidebar_widget("Picasa Badges Widget", "picasa_badges_widget");
	register_widget_control("Picasa Badges Widget", "picasa_badges_widget_control");
}
add_action("plugins_loaded", "init_picasa_badges_widget");

?>