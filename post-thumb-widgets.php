<?php
/*
Plugin Name: Post thumb widget
Plugin URI: http://www.alakhnor.com/post-thumb
Description: Adds sidebar widgets to display post-thumb revisited features
Version: 1.0
Author: Alakhnor
Author URI: http://www.alakhnor.com/post-thumb
*/

function post_thumb_widget()
{
	if ( !function_exists('register_sidebars')) return;
/*********************************************************************************/
/* wordTube widget
/*********************************************************************************/
function web_wordtube($args)
{
	extract($args);

	// Each widget can store its own options. We keep strings here.
	$options = get_option('web_wordtube');
	$title = $options['title'];
	$mediaid = $options['mediaid'];
	$content = '[MEDIA='.$mediaid.']';

	// These lines generate our output.
	echo $before_widget . $before_title . $title . $after_title;
	$url_parts = parse_url(get_bloginfo('home'));
	echo '<p>'.pt_replacevideo($mediaid, $content).'</p>';
	echo $after_widget;
		
}
/*********************************************************************************/
/* wordTube widget control
/*********************************************************************************/
function web_wordtube_control()
{
	global $wpdb;
	$options = get_option('web_wordtube');
	if ( !is_array($options) )
		$options = array('title'=>'', 'mediaid'=>'0');

	if ( $_POST['wordtube-submit'] ) 
        {
        	$options['title'] = strip_tags(stripslashes($_POST['wordtube-title']));
		$options['mediaid'] = $_POST['wordtube-mediaid'];
		update_option('web_wordtube', $options);
	}

	$title = htmlspecialchars($options['title'], ENT_QUOTES);

	// The Box content
	echo '<p style="text-align:right;"><label for="wordtube-title">' . __('Title:') . ' <input style="width: 200px;" id="wordtube-title" name="wordtube-title" type="text" value="'.$title.'" /></label></p>';
	echo '<p style="text-align:right;"><label for="wordtube-mediaid">' . __('Select Media:', 'wpTube'). ' </label>';
	echo '<select size="1" name="wordtube-mediaid" id="wordtube-mediaid">';

	$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY 'vid' ASC ");
	if($tables)
        {
		foreach($tables as $table) {
			echo '<option value="'.$table->vid.'" ';
			if ($table->vid == $options['mediaid']) echo "selected='selected' ";
			echo '>'.$table->name.'</option>'."\n\t";
			}
		}
	echo '</select></p>';
	echo '<input type="hidden" id="wordtube-submit" name="wordtube-submit" value="1" />';
}
/*********************************************************************************/
/* Simple forum widget
/*********************************************************************************/
function web_forum($args)
{
	extract($args);
	$options = get_option('web_forum');
	$title = empty($options['title']) ? __('Forum', 'post-thumb') : $options['title'];

	?>
	<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul>
			<?php sf_recent_posts_tag (); ?>
		</ul>
	<?php echo $after_widget; ?>
	<li style="clear:left;"></li>
        <?php
}
/*********************************************************************************/
/* Random post widget
/*********************************************************************************/
function web_random($args)
{
	extract($args);
	$options = get_option('web_random');
	$k = $options['keepratio'] ? '1' : '0';
	$w = $options['width'];
	$h = $options['height'];
	$l = $options['limit'];
	$st = $options['showtitle'];
	$lb = $options['LBeffect'] ? '1' : '0';
	$title = empty($options['title']) ? __('Random', 'post-thumb') : $options['title'];

	?>
	<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul>
			<?php get_random_thumb ('width='.$w.'&height='.$h.'&limit='.$l.'&keepratio='.$k.'&showtitle='.$st.'&LB_effect='.$lb); ?>
		</ul>
	<?php echo $after_widget; ?>
	<li style="clear:left;"></li>
        <?php
}
/*********************************************************************************/
/* Random post widget control
/*********************************************************************************/
function web_random_control()
{
	$options = $newoptions = get_option('web_random');
	if ( $_POST['web-random-submit'] )
        {
		$newoptions['keepratio'] = 	isset($_POST['web-random-keepratio']);
		$newoptions['width'] = 		strip_tags(stripslashes($_POST['web-random-width']));
		$newoptions['height'] = 	strip_tags(stripslashes($_POST['web-random-height']));
		$newoptions['limit'] = 		strip_tags(stripslashes($_POST['web-random-limit']));
		$newoptions['showtitle'] = 	strip_tags(stripslashes($_POST['web-random-showtitle']));
		$newoptions['showpost'] = 	strip_tags(stripslashes($_POST['web-random-showpost']));
		$newoptions['LBeffect'] = 	isset($_POST['web-random-LBeffect']);
		$newoptions['title'] = 		strip_tags(stripslashes($_POST['web-random-title']));
	}
	if ( $options != $newoptions )
        {
		$options = $newoptions;
		update_option('web_random', $options);
	}
	$title = wp_specialchars($options['title']);
	$keepratio = $options['keepratio'] ? 'checked="checked"' : '';
	if (wp_specialchars($options['width']=='')) $width = '240'; else $width = wp_specialchars($options['width']);
	if (wp_specialchars($options['height']=='')) $height = '200'; else $height = wp_specialchars($options['height']);
	if (wp_specialchars($options['limit']=='')) $limit = '5'; else $limit = wp_specialchars($options['limit']);
	$showtitle = wp_specialchars($options['showtitle']);
	$LBeffect = $options['LBeffect'] ? 'checked="checked"' : '';
?>
	<p><label for="web-random-title"><?php _e('Title:'); ?> <input style="width: 240px;" id="web-random-title" name="web-random-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-random-keepratio"><?php _e('Keep ratio', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $keepratio; ?> id="web-random-keepratio" name="web-random-keepratio" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-random-width" style="text-align:right;"><?php _e('Width', 'post-thumb'); ?> <input style="width: 40px;" type="text" id="web-random-width" name="web-random-width" value="<?php echo $width; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-random-height" style="text-align:right;"><?php _e('Height', 'post-thumb'); ?> <input style="width: 40px;" type="text" id="web-random-height" name="web-random-height" value="<?php echo $height; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-random-limit" style="text-align:right;"><?php _e('Random limit', 'post-thumb'); ?> <input style="width: 40px;" type="text" id="web-random-limit" name="web-random-limit" value="<?php echo $limit; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-random-showtitle" style="text-align:right;"><?php _e('Show title', 'post-thumb'); ?> <input style="width: 40px;" type="text" id="web-random-showtitle" name="web-random-showtitle" value="<?php echo $showtitle; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;margin-bottom:20px;"><label for="web-random-LBeffect"><?php _e('HS effect', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $LBeffect; ?> id="web-random-LBeffect" name="web-random-LBeffect" /></label></p>
	<input type="hidden" id="web-random-submit" name="web-random-submit" value="1" />
<?php
}
/*********************************************************************************/
/* Slideshow widget
/*********************************************************************************/
function web_slideshow($args)
{
	extract($args);
	$options = get_option('web_slideshow');
	$k = $options['keepratio'] ? '1' : '0';
	$w = 240;
	$h = 200;
	$c = $options['category'];
	$title = empty($options['title']) ? __('Slideshow', 'post-thumb') : $options['title'];
	?>
	<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul >
			<?php pt_slideshow('category='.$c.'&altappend=slide&width='.$w.'&height='.$h.'&keepratio='.$k); ?>
  		</ul>
	<?php echo $after_widget; ?>
	<li style="clear:left;"></li>
        <?php
}
/*********************************************************************************/
/* Slideshow widget control
/*********************************************************************************/
function web_slideshow_control()
{
	$options = $newoptions = get_option('web_slideshow');
	if ( $_POST['web-slideshow-submit'] )
        {
		$newoptions['keepratio'] = 	isset($_POST['web-slideshow-keepratio']);
		$newoptions['category'] = 	strip_tags(stripslashes($_POST['web-slideshow-category']));
		$newoptions['title'] = 		strip_tags(stripslashes($_POST['web-slideshow-title']));
	}
	if ( $options != $newoptions )
        {
		$options = $newoptions;
		update_option('web_slideshow', $options);
	}
	$title = wp_specialchars($options['title']);
	$category = wp_specialchars($options['category']);
	$keepratio = $options['keepratio'] ? 'checked="checked"' : '';
?>
	<p><label for="web-slideshow-title"><?php _e('Title:'); ?> <input style="width: 240px;" id="web-slideshow-title" name="web-slideshow-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-slideshow-keepratio"><?php _e('Keep ratio', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $keepratio; ?> id="web-slideshow-keepratio" name="web-slideshow-keepratio" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-slideshow-category" style="text-align:right;"><?php _e('Show only category', 'post-thumb'); ?> <input style="width: 40px;" type="text" value="<?php echo $category; ?>" id="web-slideshow-category" name="web-slideshow-category" /></label></p>
	<input type="hidden" id="web-slideshow-submit" name="web-slideshow-submit" value="1" />
<?php
}
/*********************************************************************************/
/* Get recent posts widget
/*********************************************************************************/
function web_recent($args)
{
	extract($args);
	$options = get_option('web_recent');
	$k = $options['keepratio'] ? '1' : '0';
	$lb = $options['LBeffect'] ? '1' : '0';
	$l = $options['limit'];
	$o = $options['offset'];
	$sp = $options['showpost'];
	$c = $options['category'];
	$title = empty($options['title']) ? __('Recent posts') : $options['title'];
	?>
	<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul>
			<?php get_recent_thumbs('category='.$c.'&keepratio='.$k.'&limit='.$l.'&LB_effect='.$lb.'&showpost='.$sp.'&offset='.$o);	?>
		</ul>
	<?php echo $after_widget; ?>
	<li style="clear:left;"></li>
        <?php
}
/*********************************************************************************/
/* Get recent posts widget control
/*********************************************************************************/
function web_recent_control() 
{
	$options = $newoptions = get_option('web_recent');
	if ( $_POST['web-recent-submit'] ) 
        {
		$newoptions['keepratio'] = 	isset($_POST['web-recent-keepratio']);
		$newoptions['limit'] = 		strip_tags(stripslashes($_POST['web-recent-limit']));
		$newoptions['offset'] = 	strip_tags(stripslashes($_POST['web-recent-offset']));
		$newoptions['category'] = 	strip_tags(stripslashes($_POST['web-recent-category']));
		$newoptions['showpost'] = 	isset($_POST['web-recent-showpost']);
		$newoptions['LBeffect'] = 	isset($_POST['web-recent-LBeffect']);
		$newoptions['title'] = 		strip_tags(stripslashes($_POST['web-recent-title']));
	}
	if ( $options != $newoptions ) 
        {
		$options = $newoptions;
		update_option('web_recent', $options);
	}
	$title = wp_specialchars($options['title']);
	$keepratio = $options['keepratio'] ? 'checked="checked"' : '';
	if (wp_specialchars($options['limit']=='')) $limit = '10'; else $limit = wp_specialchars($options['limit']);
	if (wp_specialchars($options['offset']=='')) $offset = '0'; else $offset = wp_specialchars($options['offset']);
	$category = wp_specialchars($options['category']);
	$showpost = $options['showpost'] ? 'checked="checked"' : '';
	$LBeffect = $options['LBeffect'] ? 'checked="checked"' : '';
?>
	<p><label for="web-recent-title"><?php _e('Title:'); ?> <input style="width: 240px;" id="web-recent-title" name="web-recent-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-recent-keepratio"><?php _e('Keep ratio', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $keepratio; ?> id="web-recent-keepratio" name="web-recent-keepratio" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-recent-limit" style="text-align:right;"><?php _e('Number of posts', 'post-thumb'); ?> <input style="width: 40px;" type="text" id="web-recent-limit" name="web-recent-limit" value="<?php echo $limit; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-recent-offset" style="text-align:right;"><?php _e('Offset of posts', 'post-thumb'); ?> <input style="width: 40px;" type="text" id="web-recent-offset" name="web-recent-offset" value="<?php echo $offset; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-recent-category" style="text-align:right;"><?php _e('Show only category', 'post-thumb'); ?> <input style="width: 40px;" type="text" value="<?php echo $category; ?>" id="web-recent-category" name="web-recent-category" /></label></p>
	<p style="text-align:right;margin-right:20px;margin-bottom:20px;"><label for="web-recent-showpost"><?php _e('Show post', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $showpost; ?> id="web-recent-showpost" name="web-recent-showpost" /></label></p>
	<p style="text-align:right;margin-right:20px;margin-bottom:20px;"><label for="web-recent-LBeffect"><?php _e('HS effect', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $LBeffect; ?> id="web-recent-LBeffect" name="web-recent-LBeffect" /></label></p>
	<input type="hidden" id="web-recent-submit" name="web-recent-submit" value="1" />
<?php
}
/*********************************************************************************/
/* pt-categories widget
/*********************************************************************************/
function web_categories($args) 
{
	extract($args);
	$options = get_option('web_categories');
	$c = $options['count'] ? '1' : '0';
	$h = $options['hierarchical'] ? '1' : '0';
	$title = empty($options['title']) ? __('Categories') : $options['title'];
?>
	<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul>
			<?php pt_list_categories("sort_column=name&title_li=&show_count=$c&hierarchical=$h"); ?>
		</ul>
	<?php echo $after_widget; ?>
	<li style="clear:left;"></li>
<?php
}
/*********************************************************************************/
/* pt-categories widget control
/*********************************************************************************/
function web_categories_control() 
{
	$options = $newoptions = get_option('web_categories');
	if ( $_POST['categories-submit'] ) 
        {
		$newoptions['count'] = isset($_POST['categories-count']);
		$newoptions['hierarchical'] = isset($_POST['categories-hierarchical']);
		$newoptions['title'] = strip_tags(stripslashes($_POST['categories-title']));
	}
	if ( $options != $newoptions ) 
        {
		$options = $newoptions;
		update_option('web_categories', $options);
	}
	$count = $options['count'] ? 'checked="checked"' : '';
	$hierarchical = $options['hierarchical'] ? 'checked="checked"' : '';
	$title = wp_specialchars($options['title']);
?>
	<p><label for="categories-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="categories-title" name="categories-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p style="text-align:right;margin-right:40px;"><label for="categories-count"><?php _e('Show post counts', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $count; ?> id="categories-count" name="categories-count" /></label></p>
	<p style="text-align:right;margin-right:40px;"><label for="categories-hierarchical" style="text-align:right;"><?php _e('Show hierarchy', 'post-thumb'); ?> <input class="checkbox" type="checkbox" <?php echo $hierarchical; ?> id="categories-hierarchical" name="categories-hierarchical" /></label></p>
	<input type="hidden" id="categories-submit" name="categories-submit" value="1" />
<?php
}
/*********************************************************************************/
/* pt-bookmarks widget
/*********************************************************************************/
function web_bookmarks($args)
{
	extract($args);
	$options = get_option('web_bookmarks');
	$b = empty($options['html_title_before']) ? __('<h4>') : $options['html_title_before'];
	$a = empty($options['html_title_after']) ? __('</h4>') : $options['html_title_after'];
	$title = empty($options['title']) ? __('Blogroll') : $options['title'];

	if (is_home()) {
		echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul>';
				pt_list_bookmarks('title_before='.$b.'&title_after='.$a);
			echo '</ul>';
		echo $after_widget;
		echo '<li style="clear:left;"></li>';
	}
}
/*********************************************************************************/
/* pt-bookmarks widget control
/*********************************************************************************/
function web_bookmarks_control()
{
	$options = $newoptions = get_option('web_bookmarks');
	if ( $_POST['bookmarks-submit'] )
        {
		$newoptions['title'] = strip_tags(stripslashes($_POST['bookmarks-title']));
		$newoptions['html_title_before'] = stripslashes($_POST['bookmarks-before']);
		$newoptions['html_title_after'] = stripslashes($_POST['bookmarks-after']);
	}
	if ( $options != $newoptions )
        {
		$options = $newoptions;
		update_option('web_bookmarks', $options);
	}
	$title = wp_specialchars($options['title']);
	$html_title_before = wp_specialchars($options['html_title_before']);
	$html_title_after = $options['html_title_after'];
?>
	<p><label for="bookmarks-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="bookmarks-title" name="bookmarks-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="bookmarks-before" style="text-align:right;"><?php _e('html before title', 'post-thumb'); ?> <input style="width: 200px;" id="bookmarks-before" name="bookmarks-before" type="text" value="<?php echo $html_title_before; ?>"  /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="bookmarks-after" style="text-align:right;"><?php _e('html after title', 'post-thumb'); ?> <input style="width: 200px;" id="bookmarks-after" name="bookmarks-after" type="text" value="<?php echo $html_title_after; ?>" /></label></p>
	<input type="hidden" id="bookmarks-submit" name="bookmarks-submit" value="1" />
<?php
}
/*********************************************************************************/
/* News from rss feed widget
/*********************************************************************************/
function web_news($args)
{
	extract($args);
	$options = get_option('web_news');
	$l = $options['limit'];
	$f1 = $options['feed1'];
	$f2 = $options['feed2'];
	$w = $options['words'];
	$title = empty($options['title']) ? __('News', 'post-thumb') : $options['title'];
	?>
	<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<?php if ($f1 != '') { ?>
			<div class="startseite">
				<div class="jd_news_scroll" id="elm1">
					<ul>
						<?php pt_RSS_Import ($l,$f1,$w);  ?>
					</ul>
				</div>
			</div>
		<?php } ?>
		<?php if ($f2 != '') { ?>
		<?php } ?>
	<?php echo $after_widget; ?>
	<li style="clear:left;"></li>
<?php
}
/*********************************************************************************/
/* News from rss feed widget control
/*********************************************************************************/
function web_news_control()
{
	$options = $newoptions = get_option('web_news');
	if ( $_POST['web-news-submit'] )
        {
		$newoptions['limit'] = 		strip_tags(stripslashes($_POST['web-news-limit']));
		$newoptions['feed1'] = 		strip_tags(stripslashes($_POST['web-news-feed1']));
		$newoptions['feed2'] = 		strip_tags(stripslashes($_POST['web-news-feed2']));
		$newoptions['words'] = 		strip_tags(stripslashes($_POST['web-news-words']));
		$newoptions['title'] = 		strip_tags(stripslashes($_POST['web-news-title']));
	}
	if ( $options != $newoptions ) 
        {
		$options = $newoptions;
		update_option('web_news', $options);
	}
	$title = wp_specialchars($options['title']);
	if (wp_specialchars($options['limit']=='')) $limit = '5'; else $limit = wp_specialchars($options['limit']);
	$feed1 = wp_specialchars($options['feed1']);
	$feed2 = wp_specialchars($options['feed2']);
	if (wp_specialchars($options['words']=='')) $words = '40'; else $words = wp_specialchars($options['words']);
?>
	<p><label for="web-news-title"><?php _e('Title:'); ?> <input style="width: 240px;" id="web-news-title" name="web-news-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-news-limit" style="text-align:right;"><?php _e('Number of posts', 'post-thumb'); ?> <input style="width: 40px;" type="text" id="web-news-limit" name="web-news-limit" value="<?php echo $limit; ?>" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-news-feed1" style="text-align:right;"><?php _e('Feed 1', 'post-thumb'); ?> <input style="width: 280px;" type="text" value="<?php echo $feed1; ?>" id="web-news-feed1" name="web-news-feed1" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-news-feed2" style="text-align:right;"><?php _e('Feed 2', 'post-thumb'); ?> <input style="width: 280px;" type="text" value="<?php echo $feed2; ?>" id="web-news-feed2" name="web-news-feed2" /></label></p>
	<p style="text-align:right;margin-right:20px;"><label for="web-news-words" style="text-align:right;"><?php _e('Number of words', 'post-thumb'); ?> <input style="width: 40px;" type="text" value="<?php echo $words; ?>" id="web-news-words" name="web-news-words" /></label></p>
	<input type="hidden" id="web-news-submit" name="web-news-submit" value="1" />
<?php
}

/*********************************************************************************/
/* Register widgets and widget controls
/*********************************************************************************/
	register_sidebar_widget ( 'pt-wordTube', 'web_wordTube', 'wid-wordtube');
	register_sidebar_widget ( 'pt-forum', 'web_forum', 'wid-forum' );
	register_sidebar_widget ( 'pt-random', 'web_random', 'wid-random' );
	register_sidebar_widget ( 'pt-slideshow', 'web_slideshow', 'wid-slideshow' );
	register_sidebar_widget ( 'pt-recent', 'web_recent', 'wid-recent' );
	register_sidebar_widget ( 'pt-categories', 'web_categories', 'wid-categories' );
	register_sidebar_widget ( 'pt-bookmarks', 'web_bookmarks', 'wid-latest' );
	register_sidebar_widget ( 'pt-news', 'web_news', 'wid-news' );

	register_widget_control ( 'pt-wordTube', 'web_wordtube_control', 300, 100);
	register_widget_control ( 'pt-random', 		'web_random_control', 		300, 240);
	register_widget_control ( 'pt-slideshow', 	'web_slideshow_control', 	300, 130);
	register_widget_control ( 'pt-recent', 		'web_recent_control', 		300, 240);
	register_widget_control ( 'pt-categories', 	'web_categories_control', 	300, 130);
	register_widget_control ( 'pt-bookmarks', 	'web_bookmarks_control', 	300, 150);
	register_widget_control ( 'pt-news', 		'web_news_control', 		400, 210);

}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'post_thumb_widget');

?>
