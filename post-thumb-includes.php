<?php
/********************************************************************************************************/
/* List of functions
/*
/*        function pt_thumbed_link		: return html with icon and text link
/*        function pt_get_effect		: return a string with hs effect added
/*        function pt_RSS_Import		: display rss
/*        function pt_list_categories		: wp_list_categories with hs effect
/*        function pt_list_bookmarks		: wp_list_bookmarks with hs effect
/*        function pt_previous_post_link	: previous_post_link with inframe tag added
/*        function pt_next_post_link		: next_post_link with inframe tag added
/*
/********************************************************************************************************/

/****************************************************************/
/* Displays a simple slideshow
/* Array:
/*	'post_url'       = post url (permalink)
/*	'server_image'   = absolute path to thumbnail
/*	'image_location' = thumbnail url
/*	'alt_text'       = post title
/*	'post_ID'        = post ID
/*	'the_image'      = image url
/*	'show_title'     = SHOWTITLE result (html code string)
/****************************************************************/
function pt_slideshow ($arg)
{
	global $post;
	parse_str($arg, $new_args);
	$new_args = array_change_key_case($new_args, CASE_UPPER);
	$hs_style = 'drop-shadow';
	$html_slideshow = '<div id="rotator">';

	if (isset($new_args['LIMIT'])) { $limit = $new_args['LIMIT']; settype($limit,"integer"); } else $limit = 8;

	if (isset($new_args['CATEGORY']))
	{
		$cat_ID = $new_args['CATEGORY'];
		$posts = get_posts('category='.$cat_ID.'&numberposts='.$limit.'&offset=0');
	}
        else
		$posts = get_posts('numberposts='.$limit.'&offset=0');

	foreach ($posts as $post) :
		setup_postdata($post);
		$post_array = get_thumb_array ($arg);
	        $html_slideshow .= pt_get_effect ('hs_newwindow', $hs_style, $post_array['post_url'], 700, 500, 'ss'.$post_array['post_ID'], $post_array['alt_text'], $post_array['the_image']);
	endforeach;
	$html_slideshow .= '</div>';
	echo $html_slideshow;
}
/****************************************************************/
/* Includes features in header
/****************************************************************/
function pt_include_header()
{
/* highslide includes ============================== */ ?>
	<script type="text/javascript" src="<?php echo POSTHUMB_ABSPATH; ?>highslide/highslide.js"></script>
	<script type="text/javascript" src="<?php echo POSTHUMB_ABSPATH; ?>highslide/highslide-html.js"></script>
	<script type="text/javascript" src="<?php echo POSTHUMB_ABSPATH; ?>highslide/swfobject.js"></script>
	<link rel="stylesheet" href="<?php echo POSTHUMB_ABSPATH; ?>style_hs.css" type="text/css" media="screen" />

	<script type="text/javascript">
		hs.graphicsDir = "<?php echo POSTHUMB_ABSPATH;; ?>highslide/graphics/";
		hs.outlineType = "drop-shadow";
		hs.outlineWhileAnimating = true;
		window.onload = function()
				{
					hs.preloadImages(5);
				}
	</script>

<?php /* slideshow includes ============================== */ ?>
	<script type="text/javascript" src="<?php echo POSTHUMB_ABSPATH; ?>slideshow/xfade2.js"></script>
	<link rel="stylesheet" href="<?php echo POSTHUMB_ABSPATH; ?>slideshow/slideshow1.css" type="text/css" media="screen" />

<?php
	is_inframe();
}
/****************************************************************/
/* Hack wordTube
/* Lookup for video content
/****************************************************************/
function pt_replacevideo($video_id = 0, $content='', $match='') {
global $wpdb;

	// Search for width & height
	$trail = '';
        $play_width = 0;
	$play_height = 0;
	if (preg_match('/\[(.*?)WIDTH=([0-9]+%?)(.*?)\]/i', $match, $foo1))
	{
		$play_width = $foo1[2];
		$trail .= '|WIDTH='.$play_width;
	}
	if (preg_match('/\[(.*?)HEIGHT=([0-9]+%?)(.*?)\]/i', $match, $foo2))
	{
		$play_height = $foo2[2];
		$trail .= '|HEIGHT='.$play_height;
	}

	// check for player type and prefer the mediaplayer
	if (file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) $playertype = 'mp3player.swf';
	if (file_exists(WORDTUBE_ABSPATH.'flvplayer.swf')) $playertype = 'flvplayer.swf';
	if (file_exists(WORDTUBE_ABSPATH.'mediaplayer.swf')) $playertype = 'mediaplayer.swf';
	if (!$playertype) return '';

	// Init highslide parameters
	$hs_style = 'drop-shadow';
	$outlineType = "outlineType: '".$hs_style."'";
	$position = "align: 'center'";

	// Init wordTube and retrieve media
	$wordtube_options = get_option('wordtube_options');
	$pt_options = get_option('post_thumbnail_settings');
	$pt_width = $pt_options['wordtube_width'];
	$pt_height = $pt_options['wordtube_height'];
	$pt_append = $pt_options['wordtube_text'];
	$settings = '';
	$act_video = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE vid = $video_id ");
	$act_name = $act_video[0]->name;  // wozu ?
	$act_file = $act_video[0]->file;
	$act_image = $act_video[0]->image;
	$act_creator = $act_video[0]->creator;
	$path_parts = pathinfo($act_file);
	if ($play_width == 0) $act_width = $act_video[0]->width; else $act_width = $play_width;
	if ($play_height == 0) $act_height = $act_video[0]->height; else $act_height = $play_height;
	$hs_width = $act_width+20;
        $body = '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see the wordTube Media Player.';

	// Prepare the script string
	if (strtoupper($path_parts["extension"]) == "MP3") {
		if ($wordtube_options[showeq]) {
			$settings .= "\n\t".'so'.$ID.'.addVariable("showeq", "true");';
//			$act_height = 70; // fixed for equalizer
			if (file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) $playertype = 'mp3player.swf';
		}
		$text = 'Click to listen';
	}
	if (strtoupper($path_parts["extension"]) == "FLV") {
		$text = 'Click to view';
	}
	$ID = $video_id.rand();
	// Prepare highslide html
	$thumb = tb_image_thumb_array('img='.$act_video[0]->image.'&keepratio=0&width='.$pt_width.'&height='.$pt_height.'&altappend='.$pt_append.'&textbox=1&text='.$text);
	$highslide = pt_get_swfobject($video_id, $ID, '#', $act_name, $act_creator.' - '.$act_name, $thumb['image_location'], $body, $hs_width, '#');

	if ($act_video[0]->autostart) $settings .= "\n\t".'so'.$ID.'.addVariable("autostart", "true");';
	if ($wordtube_options[usewatermark]) $settings .= "\n\t".'so'.$ID.'.addVariable("logo", "'.$wordtube_options[watermarkurl].'");';
	if ($wordtube_options[repeat]) $settings .= "\n\t".'so'.$ID.'.addVariable("repeat", "true");';
	if ($wordtube_options[overstretch]) $settings .= "\n\t".'so'.$ID.'.addVariable("overstretch", "'.$wordtube_options[overstretch].'");';
	if ($wordtube_options[showdigits]) $settings .= "\n\t".'so'.$ID.'.addVariable("showdigits", "true");';
	if ($wordtube_options[showfsbutton]) $settings .= "\n\t".'so'.$ID.'.addVariable("showfsbutton", "true");';
	if ($wordtube_options[statistic]) $settings .= "\n\t".'so'.$ID.'.addVariable("callback", "'.WORDTUBE_URLPATH.'wordtube-statistics.php");';
		
	$settings .= "\n\t".'so'.$ID.'.addVariable("backcolor", "0x'.$wordtube_options[backcolor].'");';
	$settings .= "\n\t".'so'.$ID.'.addVariable("frontcolor", "0x'.$wordtube_options[frontcolor].'");';
	$settings .= "\n\t".'so'.$ID.'.addVariable("lightcolor", "0x'.$wordtube_options[lightcolor].'");';
	$settings .= "\n\t".'so'.$ID.'.addVariable("volume", "'.$wordtube_options[volume].'");';
	$settings .= "\n\t".'so'.$ID.'.addVariable("bufferlength", "'.$wordtube_options[bufferlength].'");';

	// neeeded for IE problems
	$settings .= "\n\t".'so'.$ID.'.addVariable("width", "'.$act_width.'");';
	$settings .= "\n\t".'so'.$ID.'.addVariable("height", "'.$act_height.'");';

	if ($wordtube_options[showfsbutton]) {
		// obsolete in V3.5 (for Flash V9)
		$page_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; // need for fullscreen mode
		$fullscreen_path= WORDTUBE_URLPATH.'fullscreen.html';
		$settings .= "\n\t".'so'.$ID.'.addVariable("fullscreenpage", "'.$fullscreen_path.'");';
		$settings .= "\n\t".'so'.$ID.'.addVariable("fsreturnpage", "'.$page_url.'");';
		// required for V3.5
		$settings .= "\n\t".'so'.$ID.'.addParam("allowfullscreen", "true");';
	} else {
		// transparent didn't work with fullscreen mode
		$settings .= "\n\t".'so'.$ID.'.addVariable("showfsbutton", "false");';
		$settings .= "\n\t".'so'.$ID.'.addParam("wmode", "transparent");';
	}

    	$replace = "\n\t".'<script type="text/javascript">';
	if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'<!--';
	if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'//<![CDATA[';
	$replace .= "\n\t".'var so'.$ID.' = new SWFObject("'.WORDTUBE_URLPATH.$playertype.'", "'.$video_id.'", "'.$act_width.'", "'.$act_height.'", "7", "#FFFFFF");';
	$replace .= "\n\t".'so'.$ID.'.addVariable("file", "'.$act_file.'");';
	$replace .= "\n\t".'so'.$ID.'.addVariable("image", "'.$act_image.'");';
	$replace .= $settings;
	if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'//]]>'; // Wordpress change the CDATA end tag
	if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'// -->'; 
	$replace .= "\n\t".'</script>'."\n";

	// return custom message for RSS feeds
	if (is_feed()) {
		$replace = ""; // remove media file from RSS feed
		if (!empty($act_image)) $replace .= '<br /><img src="'.$act_image.'"><br />'."\n";
		if ($wordtube_options[activaterss]) $replace .= "[".$wordtube_options[rssmessage]."]";
	}

	$replace .= $highslide;
	$pattern = '[MEDIA='.$video_id.$trail.']';
	$return = str_replace($pattern, $replace, $content);

	return $return;
}
/****************************************************************/
/* Filter function if Highslide is activated
/****************************************************************/
function pt_replace_thumb($content)
{
global $wpdb;

	$position = "align: 'center'";
	$settings = get_option('post_thumbnail_settings');
	$ywidth = $settings['youtube_width'];
	$yheight = $settings['youtube_height'];
	if ($settings['hs_youtube']=='true') $isytube = true; else $isytube = false;
	if ($settings['hs_wordtube']=='true') $iswtube = true; else $iswtube = false;
	$replacement = '';

	if ($settings['hs_post'] == 'true')
	{
		// Replace thumbnail
		$pattern = '/<a(.*?)href=["|'."'](.*?).(bmp|jpg|jpeg|gif|png)['".'|"](.*?)>(.*?)<img(.*?)src=["'."|'](.*?)['".'|"](.*?)>/i';
		if (preg_match($pattern,$content,$matches))
		{
			$href = '<a href="$2.$3" onclick="return hs.expand(this, {captionId: '."'caption1'".'})" id="thumb1" class="highslide">';
			$img_src = '<img src="$7" $6 $8 alt="">';
			$replacement = $href.$img_src;
		}
		$return = preg_replace($pattern, $replacement, $content);

		// Replace wordTube ptplaylist
		if ($iswtube)
                {
		$pattern = '/\[PTPLAYLIST=\((.*?)\)(.*?)]/i';
		if (preg_match_all($pattern,$return,$matches))
		{
			$i=0;
			foreach ($matches[0] as $match) :
				$vid_array = explode(",",$matches[1][$i]);
				if ($matches[1][$i] != '0') $where = "WHERE vid IN ('" . implode("','", $vid_array) . "')";
				$dbresults = $wpdb->get_results("SELECT * FROM $wpdb->wordtube $where");

				if ($dbresults)
                        	{
					$replacement = '';
					$mp3 = preg_match('/\[(.*?)MP3(.*?)\]/i', $matches[0][$i], $foo1);
					$flv = preg_match('/\[(.*?)FLV(.*?)\]/i', $matches[0][$i], $foo2);
					$wid = preg_match('/\[(.*?)WIDTH=([0-9]+%?)(.*?)\]/i', $matches[0][$i], $foo3);
					$play_width = $foo3[2];
					$hei = preg_match('/\[(.*?)HEIGHT=([0-9]+%?)(.*?)\]/i', $matches[0][$i], $foo4);
					$play_height = $foo4[2];
					if ($wid and $hei) $playtrail = '|WIDTH='.$play_width.'|HEIGHT='.$play_height; else $playtrail = '';
					foreach ($dbresults as $dbresult) :
						$med_url = pathinfo($dbresult->file);
						if ($mp3)
						{
							if (strtoupper($med_url['extension']) == 'MP3') $replacement .= '[MEDIA='.$dbresult->vid.$playtrail.']';
						}
						elseif ($flv)
						{
							if (strtoupper($med_url['extension']) == 'FLV') $replacement .= '[MEDIA='.$dbresult->vid.$playtrail.']';
						}
						else $replacement .= '[MEDIA='.$dbresult->vid.$playtrail.']';
	        			endforeach;
	        			$return = str_replace($matches[0][$i], $replacement, $return);
				}
				$i++;
			endforeach;
		}}

		// Replace wordTube MEDIA with parameters
		if ($iswtube)
                {
			$pattern = '/\[MEDIA=([0-9]+%?)\]/i';
			if (preg_match_all($pattern,$return,$matches))
			{
				$i = 0;
				foreach ($matches[1] as $match) :
					$return = pt_replacevideo($match, $return, $matches[0][$i]);
					$i++;
	        		endforeach;
			}
			$pattern = '/\[MEDIA=([0-9]+%?)(.*?)\]/i';
			if (preg_match_all($pattern,$return,$matches))
			{
				$i = 0;
				foreach ($matches[1] as $match) :
					$return = pt_replacevideo($match, $return, $matches[0][$i]);
					$i++;
		        	endforeach;
			}
                }

		// Replace Dailymotion
		if ($isytube)
                {
		$pattern = '/\[dailymotion=\((.*?)\)(.*?)\]/i';
		$pat_title = '/\[dailymotion=\((.*?)\)(.*?)title=\((.*?)\)(.*?)\]/i';
		$pat_link = '/\[dailymotion=\((.*?)\)(.*?)link=\((.*?)\)(.*?)\]/i';
		$pat_pic = '/\[dailymotion=\((.*?)\)(.*?)pic=\((.*?)\)(.*?)\]/i';
		if (preg_match_all($pattern,$return,$matches))
		{
			$i=0;
			foreach ($matches[1] as $match) :
				if (preg_match($pat_title, $matches[0][$i], $mat_title)) { $title = $mat_title[3]; $text = $title; }
				else { $title = 'Dailymotion video'; $text = 'Direct link'; }
				if (preg_match($pat_link, $matches[0][$i], $mat_link)) $link = $mat_link[3];
				else $link = '';
				$url = 'http://www.dailymotion.com/video/'.$link;
				if (preg_match($pat_pic, $matches[0][$i], $mat_pic)) $pic = $mat_pic[3];
				else $pic = '';
 				$thumb = 'http://static-05.dailymotion.com/dyn/preview/160x120/'.$pic.'.jpg" width="'.$ywidth.'" height="'.$yheight;

				$highslide ='<script type="text/javascript">'."\n\t".
						'var so'.$i.'d = new SWFObject("http://www.dailymotion.com/swf/'.$match.'"'.
                                                ', "1", "425", "356", "7", "#FFFFFF")'."\n\t".
                                                'so'.$i.'d.addVariable("autoStart", "1")'."\n".
						'</script>'."\n";

				$replacement =	pt_get_swfobject($match, $i.'d', $url, $title, $text, $thumb, '<a href="'.$url.'"></a>', 450, $url).$highslide;
				$return = str_replace($matches[0][$i],$replacement, $return);
				$i++;
			endforeach;
		}}

		// Replace Youtube
		if ($isytube)
                {
		$pattern = '/\[youtube=\((.*?)\)(.*?)\]/i';
		$pat_title = '/\[youtube=\((.*?)\)(.*?)title=\((.*?)\)\]/i';
		if (preg_match_all($pattern,$return,$matches))
		{
			$i=0;
			foreach ($matches[1] as $match) :

				$thumb = 'http://img.youtube.com/vi/'.$match.'/0.jpg" width="'.$ywidth.'" height="'.$yheight;
				$url = 'http://www.youtube.com/watch?v='.$match;
				if (preg_match($pat_title, $matches[0][$i], $mat_title)) $title = $mat_title[3];
				else $title = 'Youtube video';

				$replacement ='<script type="text/javascript">'.
						"\n\t".'var so'.$i.'y = new SWFObject("http://www.youtube.com/v/'.$match.'&autoplay=true"'.
                                                ', "1", "425", "350", "7", "#FFFFFF")'.
						"\n".'</script>'."\n";

				$highslide = pt_get_swfobject($match, $i.'y', $url, $title, $title, $thumb, '<a href="'.$url.'"></a>', 450, $url);
				$replacement .=	$highslide;
				$return = str_replace($matches[0][$i],$replacement, $return);
				$i++;
			endforeach;
		}}
		return $return;
	}
	else return $content;
}
/****************************************************************/
/* Returns a highslide swfobject
/****************************************************************/
function pt_get_swfobject($match, $i, $url, $title, $text, $thumb, $body, $width, $defaulturl='#')
{
	$position = "align: 'center'";
	$ret = 	'<a href="'.$defaulturl.'" onclick="return hs.htmlExpand(this, { contentId: '."'".'highslide-html'.$i."'".', '.
           		$position.', swfObject: so'.$i.',  allowSizeReduction: false } )" class="highslide">'."\n\t".
   			'<img src="'.$thumb.'" title="'.__('Click to view: ').$title.'" alt=""/>'."\n".
		'</a>'."\n".
		'<div class="highslide-html-content" id="highslide-html'.$i.'" style="width: '.$width.'px;">'."\n\t".
			'<div style="height:20px; padding: 2px">'."\n\t\t".
				'<a href="#" onclick="return hs.close(this)" class="control">Close</a>'."\n\t\t".
				'<a href="#" onclick="return false" class="highslide-move control">Move</a>'."\n\t".
			'</div>'."\n\t".
			'<div class="highslide-body" style="padding: 0 10px 10px 10px">'."\n\t\t".
			$body."\n\t".
			'</div>'."\n\t";
	if ($url != '') $ret .= '<div style="text-align: center; background-color: white; border-top: 1px solid silver; padding: 5px 0; line-height: 20px;">'."\n\t\t".
					'<a href="'.$url.'" title="'.$title.'">'.$text.'</a>'."\n\t".
				'</div>'."\n";
	$ret .= '</div>'."\n";
	return $ret;
}
/****************************************************************/
/* Returns a formatted url for inframe display
/****************************************************************/
function pt_return_get ($url)
{
	$look_get = strpos($url,'?');
	$end_char = substr($url, -1, 1);
	if ($end_char == '/') $url_inframe = substr($url, 0, strlen($url)-1); else $url_inframe = $url;
	if ($look_get !== false) $url_inframe .= "&amp;inframe=1"; else $url_inframe .= "?inframe=1";
	return $url_inframe;
}
/****************************************************************/
/* Return a highslide string to display icon and html
/****************************************************************/
function pt_thumbed_link ($hs_style, $hs_url, $hs_width, $hs_height, $hs_ID, $hs_text, $hs_image='', $hr_text='', $hr_title='')
{
	if ($hr_text == '') $hr_text = $hs_text;
	if ($hr_title == '') $hr_title = $hr_text;
	if ($hs_image=='') $hs_image = POSTHUMB_ABSPATH.'images/pong.gif';

	// gather icon html
	$hs_link = pt_get_effect ('hs_newwindow', $hs_style, $hs_url, $hs_width, $hs_height, $hs_ID, $hs_text, $hs_image);

	// gather text html
	$hr_link = '<a href="'.$hs_url.'" title="'.$hr_title.'">'.$hr_text.'</a>';

	return $hs_link.' '.$hr_link;
}
/****************************************************************/
/* Return a highslide string for display
/*
/* Parameters
/*	� $hs_function
/*	� $hs_style
/*	� $hs_url: url to link to
/*	� $hs_width: expanded width
/*	� $hs_height: expanded height
/*	� $hs_ID: used to named "id" tags inside highslide display
/*	� $hs_text: used for title
/*	� $hs_image: thumbnail url
/*	� $hs_img_url: image url (if hs_overlay is used)
/*	� $hs_slasha: string appended to main html code. It's usually used to display additional informations (title, author, date).
/*	� $hs_content: used to display html content with 'hs_html' effect.
/*
/* Possible effects are ($hs_function):
/*	� hs_newwindow: open an iframe with a new web page from a thumbnail. Size is given by width and height parameters.
/*	� hs_overlay: display an image. Size is image size.
/*	� hs_html: display an html content. Width is given by width parameter, height adjusts to content.
/*	� hs_link: display an iframe from a link. Size is given by width and height parameters.
/*
/* Frames can be bordered by 4 different aspects ($hs_style):
/*	� rounded-white: white border with rounded corner.
/*	� drop-shadow: white border with shadow effect.
/*	� beveled: grey smooth border.
/*	� outer-glow: white border glowing outside.
/*
/****************************************************************/
function pt_get_effect ($hs_function, $hs_style, $hs_url, $hs_width, $hs_height, $hs_ID, $hs_text, $hs_image='', $hs_img_url='', $hs_slasha='', $hs_content='', $hs_caption='', $hs_title='')
{
	$position = "align: 'center'";
        $outlineType = "outlineType: '".$hs_style."'";
        $url_inframe = pt_return_get($hs_url);
        if ($hs_caption=='') $hs_caption = __('Direct Link', 'post-thumb');
        if ($hs_title=='') $hs_title=$hs_text;

	switch ($hs_function)
	{
		//  Highslide effect: pop-up a navigation windows from a linked thumbnail
		case 'hs_newwindow' :
			return "\n".'<a href="'.$url_inframe.'" title="'.$hs_title.'" onclick="return hs.htmlExpand(this, { contentId: '."'html-newwindow".$hs_ID."'".
				', objectType: '."'iframe'".', '.$outlineType.', '.$position.', objectWidth: '.$hs_width.', objectHeight: '.$hs_height.
				', objectLoadTime: '."'after'".'} )" class="highslide">'.
				'<img src="'.$hs_image.'" alt="'.$hs_text.'" /></a>'.
			'<div class="highslide-html-content" id="html-newwindow'.$hs_ID.'" style="width: '.$hs_width.'px">'.
				'<div class="highslide-move" style="background-color: white; border: 0; height: 18px; padding: 2px; cursor: default">'.
					'<a href="#" onclick="return hs.close(this)" class="control">Close</a>'.
				'</div>'.
				'<div class="highslide-body" style="max-height: '.$hs_height.'px"></div>'.
				'<div style="text-align: center; background-color: white; border-top: 1px solid silver; padding: 5px 0">'.
					'<small><i><a href="'.$hs_url.'" title="'.$hs_text.'">'.$hs_caption.'</a></i></small>'.
				'</div>'.
			'</div>'.
                        $hs_slasha;
		break;

		// Highslide effect: display image from a linked thumbnail
		case 'hs_overlay' :
			$caption = "captionID: '".$hs_ID."'";
			return '<a href="'.$hs_img_url.'" title="'.$hs_title.'" class="highslide" '.
                        	'onclick="return hs.expand(this, {'.$caption.', '.$outlineType.', '.$position.'})">'.
				'<img src="'.$hs_image.'" alt="'.$hs_text.'" title="Click to enlarge" /></a>'.
				'<div class="highslide-caption" id="caption'.$hs_ID.'">'.
				'</div>'.
                                $hs_slasha;
		break;

		// Highslide effect: pop-up a navigation windows from a link
		case 'hs_link' :
			return '<a href="'.$hs_url.'" title="'.$hs_title.'" onclick="return hs.htmlExpand(this, { contentId: '."'html-link".$hs_ID."'".',
					objectType: '."'iframe'".', '.$outlineType.', '.$position.', objectWidth: '.$hs_width.
					', objectHeight: '.$hs_height.', objectLoadTime: '."'after'".'} )" class="highslide">'.$hs_text.
                                '</a>'.
				'<div class="highslide-html-content" id="html-link'.$hs_ID.'" style="width: '.$hs_width.'px">'.
					'<div class="highslide-move" style="background-color: white; border: 0; height: 18px; padding: 2px; cursor: default">'.
						'<a href="#" onclick="return hs.close(this)" class="control">Close</a>'.
					'</div>'.
					'<div class="highslide-body"></div>'.
				'</div>'.
                                $hs_slasha;
		break;

		// Highslide effect: pop-up a html windows from a thumbnail
		case 'hs_html' :
			return "\n".'<a href="#" title="'.$hs_title.'" onclick="return hs.htmlExpand(this, { contentId: '."'highslide-html".$hs_ID."'".
					', '.$outlineType.', '.$position.' } )" class="highslide">'."\n\t".
					'<img src="'.$hs_image.'" alt="'.$hs_text.'" title="Click to enlarge" />'."\n".
                                '</a>'."\n".
				'<div class="highslide-html-content" id="highslide-html'.$hs_ID.'" style="width: '.$hs_width.'px;">'."\n\t".
					'<div style="background-color: white; height:20px; padding: 2px">'."\n\t\t".
						'<a href="#" onclick="return hs.close(this)" class="control">Close</a>'."\n\t\t".
						'<a href="#" onclick="return false" class="highslide-move control">Move</a>'."\n\t".
					'</div>'."\n\t".
					'<div class="highslide-body" style="max-height: 550px; background-color: white; padding: 0 10px 10px 10px">'."\n\t\t".
						'<h2>'.$hs_text.'</h2><br />'.$hs_content."\n\t".
					'</div>'."\n\t".
					'<div style="text-align: center; background-color: white; border-top: 1px solid silver; padding: 5px 0; line-height: 20px;">'."\n\t\t".
						'<small><i><a href="'.$hs_url.'" title="'.$hs_title.'">Lien direct</a></i></small>'."\n\t".
					'</div>'."\n".
				'</div>'."\n".
                                $hs_slasha;
		break;
	}
}
/****************************************************************/
/* test if a call is in a frame
/****************************************************************/
function is_inframe()
{  
	if (isset($_GET['inframe']))
	{
		$inframe = $_GET['inframe'];
	}
	else
	{
		$inframe = 0;
	}
	define('POSTHUMB_INFRAME', $inframe);

}
/****************************************************************/
/* Return a cleaned string
/****************************************************************/
function encode_html ($item)
{
	$umlaute = array('€','‚','ƒ','„','…','†','‡','ˆ','‰','Š','‹','Œ','Ž','‘','’','“','”','•','–','—','˜','™','š','›','œ','ž','Ÿ','¡','¢','£','¤','¥','¦','§','¨','©','ª','«','¬','®','¯','°','±','²','³','´','µ','¶','·','¸','¹','º','»','¼','½','¾','¿','À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','×','Ø','Ù','Ú','Û','Ü','Ý','Þ','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ð','ñ','ò','ó','ô','õ','ö','÷','ø','ù','ú','û','ü','ý','þ','ÿ',utf8_encode('€'),utf8_encode('‚'),utf8_encode('ƒ'),utf8_encode('„'),utf8_encode('…'),utf8_encode('†'),utf8_encode('‡'),utf8_encode('ˆ'),utf8_encode('‰'),utf8_encode('Š'),utf8_encode('‹'),utf8_encode('Œ'),utf8_encode('Ž'),utf8_encode('‘'),utf8_encode('’'),utf8_encode('“'),utf8_encode('”'),utf8_encode('•'),utf8_encode('–'),utf8_encode('—'),utf8_encode('˜'),utf8_encode('™'),utf8_encode('š'),utf8_encode('›'),utf8_encode('œ'),utf8_encode('ž'),utf8_encode('Ÿ'),utf8_encode('¡'),utf8_encode('¢'),utf8_encode('£'),utf8_encode('¤'),utf8_encode('¥'),utf8_encode('¦'),utf8_encode('§'),utf8_encode('¨'),utf8_encode('©'),utf8_encode('ª'),utf8_encode('«'),utf8_encode('¬'),utf8_encode('®'),utf8_encode('¯'),utf8_encode('°'),utf8_encode('±'),utf8_encode('²'),utf8_encode('³'),utf8_encode('´'),utf8_encode('µ'),utf8_encode('¶'),utf8_encode('·'),utf8_encode('¸'),utf8_encode('¹'),utf8_encode('º'),utf8_encode('»'),utf8_encode('¼'),utf8_encode('½'),utf8_encode('¾'),utf8_encode('¿'),utf8_encode('À'),utf8_encode('Á'),utf8_encode('Â'),utf8_encode('Ã'),utf8_encode('Ä'),utf8_encode('Å'),utf8_encode('Æ'),utf8_encode('Ç'),utf8_encode('È'),utf8_encode('É'),utf8_encode('Ê'),utf8_encode('Ë'),utf8_encode('Ì'),utf8_encode('Í'),utf8_encode('Î'),utf8_encode('Ï'),utf8_encode('Ð'),utf8_encode('Ñ'),utf8_encode('Ò'),utf8_encode('Ó'),utf8_encode('Ô'),utf8_encode('Õ'),utf8_encode('Ö'),utf8_encode('×'),utf8_encode('Ø'),utf8_encode('Ù'),utf8_encode('Ú'),utf8_encode('Û'),utf8_encode('Ü'),utf8_encode('Ý'),utf8_encode('Þ'),utf8_encode('ß'),utf8_encode('à'),utf8_encode('á'),utf8_encode('â'),utf8_encode('ã'),utf8_encode('ä'),utf8_encode('å'),utf8_encode('æ'),utf8_encode('ç'),utf8_encode('è'),utf8_encode('é'),utf8_encode('ê'),utf8_encode('ë'),utf8_encode('ì'),utf8_encode('í'),utf8_encode('î'),utf8_encode('ï'),utf8_encode('ð'),utf8_encode('ñ'),utf8_encode('ò'),utf8_encode('ó'),utf8_encode('ô'),utf8_encode('õ'),utf8_encode('ö'),utf8_encode('÷'),utf8_encode('ø'),utf8_encode('ù'),utf8_encode('ú'),utf8_encode('û'),utf8_encode('ü'),utf8_encode('ý'),utf8_encode('þ'),utf8_encode('ÿ'),chr(128),chr(129),chr(130),chr(131),chr(132),chr(133),chr(134),chr(135),chr(136),chr(137),chr(138),chr(139),chr(140),chr(141),chr(142),chr(143),chr(144),chr(145),chr(146),chr(147),chr(148),chr(149),chr(150),chr(151),chr(152),chr(153),chr(154),chr(155),chr(156),chr(157),chr(158),chr(159),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(182),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255),chr(256));
	$htmlcode = array('&euro;','&sbquo;','&fnof;','&bdquo;','&hellip;','&dagger;','&Dagger;','&circ;','&permil;','&Scaron;','&lsaquo;','&OElig;','&#x017D;','&lsquo;','&rsquo;','&ldquo;','&rdquo;','&bull;','&ndash;','&mdash;','&tilde;','&trade;','&scaron;','&rsaquo;','&oelig;','&#x017E;','&Yuml;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&supl;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&euro;','&sbquo;','&fnof;','&bdquo;','&hellip;','&dagger;','&Dagger;','&circ;','&permil;','&Scaron;','&lsaquo;','&OElig;','&#x017D;','&lsquo;','&rsquo;','&ldquo;','&rdquo;','&bull;','&ndash;','&mdash;','&tilde;','&trade;','&scaron;','&rsaquo;','&oelig;','&#x017E;','&Yuml;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&supl;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&euro;','','&sbquo;','&fnof;','&bdquo;','&hellip;','&dagger;','&Dagger;','&circ;','&permil;','&Scaron;','&lsaquo;','&OElig;','','&#x017D;','','','&lsquo;','&rsquo;','&ldquo;','&rdquo;','&bull;','&ndash;','&mdash;','&tilde;','&trade;','&scaron;','&rsaquo;','&oelig;','','&#x017E;','&Yuml;','&nbsp;','&iexcl;','&iexcl;','&iexcl;','&iexcl;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','­&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&supl;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');
	$ret = str_replace($umlaute, $htmlcode, $item);
        return $ret;
}
/****************************************************************/
/* Includes WP functions in a PT way
/****************************************************************/
// For function fetch_rss
if(file_exists(ABSPATH . WPINC . '/rss.php')) {
	@require_once (ABSPATH . WPINC . '/rss.php');
	// It's Wordpress 1.5.2 or 2.x. since it has been loaded successfully
} elseif (file_exists(ABSPATH . WPINC . '/rss-functions.php')) {
	@require_once (ABSPATH . WPINC . '/rss-functions.php');
	// In Wordpress 2.1, a new file name is being used
} else {
	die (__('Error in file: ' . __FILE__ . ' on line: ' . __LINE__ . '.<br />The Wordpress file "rss-functions.php" or "rss.php" could not be included.'));
}

/****************************************************************/
/* Includes rss feed import in a PT way
/****************************************************************/
function pt_RSS_Import ($display=0,$feedurl,$word=100, $hs_style='beveled', $hs_width=800, $hs_height=500) 
{
	if ($feedurl)
        {
		$rss = fetch_rss($feedurl);
		if ($display == 0) return $rss;
		else
		{
			foreach ($rss->items as $item) :
				if ($display == 0) break;

                     			$altcount = $display%2;
                     			$href = $item['link'];
                     			$desc = trim($item['description']);
                     			$item['fulltitle']=$item['title'];
                     			// Do you have problems with special characters, then comment the follow four lines
    					$umlaute = array('€','‚','ƒ','„','…','†','‡','ˆ','‰','Š','‹','Œ','Ž','‘','’','“','”','•','–','—','˜','™','š','›','œ','ž','Ÿ','¡','¢','£','¤','¥','¦','§','¨','©','ª','«','¬','®','¯','°','±','²','³','´','µ','¶','·','¸','¹','º','»','¼','½','¾','¿','À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','×','Ø','Ù','Ú','Û','Ü','Ý','Þ','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ð','ñ','ò','ó','ô','õ','ö','÷','ø','ù','ú','û','ü','ý','þ','ÿ',utf8_encode('€'),utf8_encode('‚'),utf8_encode('ƒ'),utf8_encode('„'),utf8_encode('…'),utf8_encode('†'),utf8_encode('‡'),utf8_encode('ˆ'),utf8_encode('‰'),utf8_encode('Š'),utf8_encode('‹'),utf8_encode('Œ'),utf8_encode('Ž'),utf8_encode('‘'),utf8_encode('’'),utf8_encode('“'),utf8_encode('”'),utf8_encode('•'),utf8_encode('–'),utf8_encode('—'),utf8_encode('˜'),utf8_encode('™'),utf8_encode('š'),utf8_encode('›'),utf8_encode('œ'),utf8_encode('ž'),utf8_encode('Ÿ'),utf8_encode('¡'),utf8_encode('¢'),utf8_encode('£'),utf8_encode('¤'),utf8_encode('¥'),utf8_encode('¦'),utf8_encode('§'),utf8_encode('¨'),utf8_encode('©'),utf8_encode('ª'),utf8_encode('«'),utf8_encode('¬'),utf8_encode('®'),utf8_encode('¯'),utf8_encode('°'),utf8_encode('±'),utf8_encode('²'),utf8_encode('³'),utf8_encode('´'),utf8_encode('µ'),utf8_encode('¶'),utf8_encode('·'),utf8_encode('¸'),utf8_encode('¹'),utf8_encode('º'),utf8_encode('»'),utf8_encode('¼'),utf8_encode('½'),utf8_encode('¾'),utf8_encode('¿'),utf8_encode('À'),utf8_encode('Á'),utf8_encode('Â'),utf8_encode('Ã'),utf8_encode('Ä'),utf8_encode('Å'),utf8_encode('Æ'),utf8_encode('Ç'),utf8_encode('È'),utf8_encode('É'),utf8_encode('Ê'),utf8_encode('Ë'),utf8_encode('Ì'),utf8_encode('Í'),utf8_encode('Î'),utf8_encode('Ï'),utf8_encode('Ð'),utf8_encode('Ñ'),utf8_encode('Ò'),utf8_encode('Ó'),utf8_encode('Ô'),utf8_encode('Õ'),utf8_encode('Ö'),utf8_encode('×'),utf8_encode('Ø'),utf8_encode('Ù'),utf8_encode('Ú'),utf8_encode('Û'),utf8_encode('Ü'),utf8_encode('Ý'),utf8_encode('Þ'),utf8_encode('ß'),utf8_encode('à'),utf8_encode('á'),utf8_encode('â'),utf8_encode('ã'),utf8_encode('ä'),utf8_encode('å'),utf8_encode('æ'),utf8_encode('ç'),utf8_encode('è'),utf8_encode('é'),utf8_encode('ê'),utf8_encode('ë'),utf8_encode('ì'),utf8_encode('í'),utf8_encode('î'),utf8_encode('ï'),utf8_encode('ð'),utf8_encode('ñ'),utf8_encode('ò'),utf8_encode('ó'),utf8_encode('ô'),utf8_encode('õ'),utf8_encode('ö'),utf8_encode('÷'),utf8_encode('ø'),utf8_encode('ù'),utf8_encode('ú'),utf8_encode('û'),utf8_encode('ü'),utf8_encode('ý'),utf8_encode('þ'),utf8_encode('ÿ'),chr(128),chr(129),chr(130),chr(131),chr(132),chr(133),chr(134),chr(135),chr(136),chr(137),chr(138),chr(139),chr(140),chr(141),chr(142),chr(143),chr(144),chr(145),chr(146),chr(147),chr(148),chr(149),chr(150),chr(151),chr(152),chr(153),chr(154),chr(155),chr(156),chr(157),chr(158),chr(159),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(182),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255),chr(256));
    					$htmlcode = array('&euro;','&sbquo;','&fnof;','&bdquo;','&hellip;','&dagger;','&Dagger;','&circ;','&permil;','&Scaron;','&lsaquo;','&OElig;','&#x017D;','&lsquo;','&rsquo;','&ldquo;','&rdquo;','&bull;','&ndash;','&mdash;','&tilde;','&trade;','&scaron;','&rsaquo;','&oelig;','&#x017E;','&Yuml;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&supl;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&euro;','&sbquo;','&fnof;','&bdquo;','&hellip;','&dagger;','&Dagger;','&circ;','&permil;','&Scaron;','&lsaquo;','&OElig;','&#x017D;','&lsquo;','&rsquo;','&ldquo;','&rdquo;','&bull;','&ndash;','&mdash;','&tilde;','&trade;','&scaron;','&rsaquo;','&oelig;','&#x017E;','&Yuml;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&supl;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&euro;','','&sbquo;','&fnof;','&bdquo;','&hellip;','&dagger;','&Dagger;','&circ;','&permil;','&Scaron;','&lsaquo;','&OElig;','','&#x017D;','','','&lsquo;','&rsquo;','&ldquo;','&rdquo;','&bull;','&ndash;','&mdash;','&tilde;','&trade;','&scaron;','&rsaquo;','&oelig;','','&#x017E;','&Yuml;','&nbsp;','&iexcl;','&iexcl;','&iexcl;','&iexcl;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','­&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&supl;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');
    					$item = str_replace($umlaute, $htmlcode, $item);
    					$desc = str_replace($umlaute, $htmlcode, $desc);
                     			if(strlen($item['description'])>$word)
                     			{
                         			$item['description']=substr($item['description'],0,$word).' ... ';
                     			}
                     			if ($altcount==0)
                     			{
                         			echo '<li><h5>'.pt_get_effect ('hs_link', $hs_style, $href, $hs_width, $hs_height, $display, $item['description'], '', '', '', '', '', $desc).'</h5></li>';
                     			}
                     			else
                     			{
                         			echo '<li><h6>'.pt_get_effect ('hs_link', $hs_style, $href, $hs_width, $hs_height, $display, $item['description'], '', '', '', '', '', $desc).'</h6></li>';
                        		// Descriptions and more-Link
                     			}
                     		$display--;
            		endforeach;
            	}
    	}
}
/****************************************************************/
/* Redefine Walker_Category
/****************************************************************/
if (get_bloginfo('version')>='2.1')
{
class pt_Walker_Category extends Walker
{
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'category_parent', 'id' => 'cat_ID'); //TODO: decouple this

	function start_lvl($output, $depth, $args) 
        {
		if ( 'list' != $args['style'] )
			return $output;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
		return $output;
	}

	function end_lvl($output, $depth, $args) 
        {
		if ( 'list' != $args['style'] )
			return $output;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
		return $output;
	}

	function start_el($output, $category, $depth, $args, $c_width, $c_height) 
        {
		extract($args);

		$cat_name = attribute_escape( $category->cat_name);
		$hs_link = get_category_link( $category->cat_ID );

		$link2 = '<a href="' . get_category_link( $category->cat_ID ) . '" ';

		if ( $use_desc_for_title == 0 || empty($category->category_description) )
		{
			$hs_text = sprintf(__( 'View all posts filed under %s' ), $cat_name);
			$link2 .= 'title="' . sprintf(__( 'View all posts filed under %s' ), $cat_name) . '"';
		}
		else
		{
			$link2 .= 'title="' . attribute_escape( apply_filters( 'category_description', $category->category_description, $category )) . '"';
		}

		$link2 .= '>';
		$link2 .= apply_filters( 'list_cats', $category->cat_name, $category ).'</a>';

	        // Change for Highslide
	        $ping = POSTHUMB_ABSPATH.'images/pong.gif';

                $hs_link = pt_get_effect ('hs_newwindow', 'rounded-white', $hs_link, $c_width, $c_height, $category->cat_ID, $hs_text, $ping, '', '', '', __('Category', 'post-thumb').' '.$cat_name);

                $link = $hs_link.' '.$link2;

		if ( (! empty($feed_image)) || (! empty($feed)) ) 
                {
			$link .= ' ';

			if ( empty($feed_image) )
				$link .= '(';

	 		$link .= '<a href="' . get_category_rss_link( 0, $category->cat_ID, $category->category_nicename ) . '" ';

			if ( empty($feed) )
				$alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
			else {
				$title = ' title="' . $feed . '"';
				$alt = ' alt="' . $feed . '"';
				$name = $feed;
				$link .= $title;
			}

			$link .= '>';

			if ( empty($feed_image) )
				$link .= $name;
			else
				$link .= "<img src='$feed_image'$alt$title" . ' />';
			        $link .= '</a>';
			if ( empty($feed_image) )
				$link .= ')';
		}

		if ( isset($show_count) && $show_count )
			$link .= ' (' . intval($category->category_count) . ')';

		if ( isset($show_date) && $show_date )
                {
			$link .= ' ' . gmdate('Y-m-d', $category->last_update_timestamp);
		}

		if ( $current_category )
			$_current_category = get_category( $current_category );

		if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			if ( $current_category && ($category->cat_ID == $current_category) )
				$output .=  ' class="current-cat"';
			elseif ( $_current_category && ($category->cat_ID == $_current_category->category_parent) )
				$output .=  ' class="current-cat-parent"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}

		return $output;
	}

	function end_el($output, $page, $depth, $args) 
        {
		if ( 'list' != $args['style'] )
			return $output;

		$output .= "</li>\n";
		return $output;
	}
}
/****************************************************************/
/* Redefines walk_category_tree
/****************************************************************/
function pt_walk_category_tree()
{
	$walker = new pt_Walker_Category;
	$args = func_get_args();
	return call_user_func_array(array(&$walker, 'walk'), $args);
}
/****************************************************************/
/* Redefines wp_list_categories
/****************************************************************/
function pt_list_categories ($args = '')
{
	if ( is_array($args) ) $r = &$args;
        else parse_str($args, $r);

	$defaults = array('show_option_all' => '', 'orderby' => 'name',
		          'order' => 'ASC', 'show_last_update' => 0, 'style' => 'list',
		          'show_count' => 0, 'hide_empty' => 1, 'use_desc_for_title' => 1,
		          'child_of' => 0, 'feed' => '', 'feed_image' => '', 'exclude' => '',
	                  'hierarchical' => true, 'title_li' => __('Categories'), 'width' => '800', 'height' => '500');

	$r = array_merge($defaults, $r);
	if ( !isset($r['pad_counts']) && $r['show_count'] && $r['hierarchical'] ) $r['pad_counts'] = true;
	if ( isset($r['show_date']) ) $r['include_last_update_time'] = $r['show_date'];
	extract($r);

	$categories = get_categories($r);

	$output = '';
	if ( $title_li && 'list' == $style )
			$output = '<li class="categories">' . $r['title_li'] . '<ul>';

	if ( empty($categories) ) {
		if ( 'list' == $style )
			$output .= '<li>' . __("No categories") . '</li>';
		else
			$output .= __("No categories");
	} else {
		global $wp_query;

		if ( is_category() )
			$r['current_category'] = $wp_query->get_queried_object_id();

		if ( $hierarchical )
			$depth = 0;  // Walk the full depth.
		else
			$depth = -1; // Flat.

		$output .= pt_walk_category_tree($categories, $depth, $r, $r['width'], $r['height']);
	}

	if ( $title_li && 'list' == $style )
		$output .= '</ul></li>';

	echo apply_filters('wp_list_categories', $output);
}
/****************************************************************/
/* Redefines _walk_bookmarks
/****************************************************************/
function pt_walk_bookmarks($bookmarks, $args = '' ) 
{
	if ( is_array($args) )
		$b_r = &$args;
	else
		parse_str($args, $r);

   $position = "'center'";
   $outlineType = "'rounded-white'";

	$defaults = array('show_updated' => 0, 'show_description' => 0, 'show_images' => 1, 'before' => '<li>',
		'after' => '</li>', 'between' => "\n");
	$b_r = array_merge($defaults, $b_r);
	extract($b_r);

	foreach ( (array) $bookmarks as $bookmark ) {
		if ( !isset($bookmark->recently_updated) )
			$bookmark->recently_updated = false;
		$output .= $before;
		if ( $show_updated && $bookmark->recently_updated )
			$output .= get_option('links_recently_updated_prepend');

		$the_link = '#';
		if ( !empty($bookmark->link_url) )
			$the_link = wp_specialchars($bookmark->link_url);

		$rel = $bookmark->link_rel;
		$ID = $bookmark->link_id;
		if ( '' != $rel )
			$rel = ' rel="' . $rel . '"';

		$desc = attribute_escape($bookmark->link_description);
		$name = attribute_escape($bookmark->link_name);
		$title = $desc;

		if ( $show_updated )
			if ( '00' != substr($bookmark->link_updated_f, 0, 2) ) {
				$title .= ' ';
				$title .= sprintf(__('Last updated: %s'), date(get_option('links_updated_date_format'), $bookmark->link_updated_f + (get_option('gmt_offset') * 3600)));
				$title .= ')';
			}

		if ( '' != $title ) $title = ' title="' . $title . '"';
		if ( '' != $alt ) $alt = ' alt="' . $name . '"';

		$target = $bookmark->link_target;
		if ( '' != $target )
			$target = ' target="' . $target . '"';

		$output .= '<a href="' . $the_link . '"' . $title . $target.
                           ' onclick="return hs.htmlExpand(this, { contentId: '."'highslide-bookmark".$ID."'".', objectType: '.
                           "'iframe'".', outlineType: '.$outlineType.', align: '.$position.', objectWidth: '.$b_r['width'].', objectHeight: '.$b_r['height'].', objectLoadTime: '."'after'".'} )" class="highslide">';

		if ( $bookmark->link_image != null && $show_images ) {
			if ( strpos($bookmark->link_image, 'http') !== false )
				$output .= "<img src=\"$bookmark->link_image\" $alt $title />";
			else // If it's a relative path
				$output .= "<img src=\"" . get_option('siteurl') . "$bookmark->link_image\" $alt $title />";
		} else {
			$output .= $name;
		}

		$output .= '</a>'.
                           '<div class="highslide-html-content" id="highslide-bookmark'.$ID.'" style="width: '.$b_r['width'].'px">'.
	                      '<div class="highslide-move" style="background-color: white; border: 0; height: 18px; padding: 2px; cursor: default">'.
	                         '<a href="#" onclick="return hs.close(this)" class="control">Close</a>'.
	                      '</div>'.
                              '<div class="highslide-body"></div>'.
                              '<div style="text-align: center; background-color: white; border-top: 1px solid silver; padding: 5px 0">'.
		                 '<small>Boomarks Powered by <i>Highslide JS</i></small>'.
			      '</div>'.
			   '</div>';

		if ( $show_updated && $bookmark->recently_updated )
			$output .= get_option('links_recently_updated_append');

		if ( $show_description && '' != $desc )
			$output .= $between . $desc;
		$output .= "$after\n";
	} // end foreach

	return $output;
}
/****************************************************************/
/* Redefines wp_list_bookmarks
/****************************************************************/
function pt_list_bookmarks($args = '') {
	if ( is_array($args) )
		$b_r = &$args;
	else
		parse_str($args, $b_r);

	$defaults = array('orderby' => 'name', 'order' => 'ASC', 'limit' => -1, 'category' => '',
		'category_name' => '', 'hide_invisible' => 1, 'show_updated' => 0, 'echo' => 1,
		'categorize' => 1, 'title_li' => __('Bookmarks'), 'title_before' => '<h2>', 'title_after' => '</h2>',
		'category_orderby' => 'name', 'category_order' => 'ASC', 'class' => 'linkcat',
		'category_before' => '<li id="%id" class="%class">', 'category_after' => '</li>', 'width' => '800', 'height' => '500');
	$b_r = array_merge($defaults, $b_r);
	extract($b_r);

	$output = '';

	if ( $categorize ) {
		//Split the bookmarks into ul's for each category
		$cats = get_categories("type=link&category_name=$category_name&include=$category&orderby=$category_orderby&order=$category_order&hierarchical=0");

		foreach ( (array) $cats as $cat ) {
			$bookmarks = get_bookmarks("limit=$limit&category={$cat->cat_ID}&show_updated=$show_updated&orderby=$orderby&order=$order&hide_invisible=$hide_invisible&show_updated=$show_updated");
			if ( empty($bookmarks) )
				continue;
			$output .= str_replace(array('%id', '%class'), array("linkcat-$cat->cat_ID", $class), $category_before);
			$output .= "$title_before$cat->cat_name$title_after\n\t<ul>\n";
			$output .= pt_walk_bookmarks($bookmarks, $b_r);
			$output .= "\n\t</ul>\n$category_after\n";
		}
	} else {
		//output one single list using title_li for the title
		$bookmarks = get_bookmarks("limit=$limit&category=$category&show_updated=$show_updated&orderby=$orderby&order=$order&hide_invisible=$hide_invisible&show_updated=$show_updated");
		
		if ( !empty($bookmarks) ) {
			$output .= str_replace(array('%id', '%class'), array("linkuncat", $class), $category_before);
			$output .= "$title_before$title_li$title_after\n\t<ul>\n";
			$output .= pt_walk_bookmarks($bookmarks, $b_r);
			$output .= "\n\t</ul>\n$category_after\n";
		}
	}

	if ( !$echo )
		return $output;
	echo $output;
}
/****************************************************************/
/* End of excluded functions for WP version before 2.1
/****************************************************************/
}
else
{
/****************************************************************/
/* Begins inclusion of functions for WP version before 2.1
/****************************************************************/
// out of the WordPress loop
function pt_list_categories($args = '') {
	parse_str($args, $r);
	if ( !isset($r['optionall']))
		$r['optionall'] = 0;
	if ( !isset($r['all']))
		$r['all'] = 'All';
	if ( !isset($r['sort_column']) )
		$r['sort_column'] = 'ID';
	if ( !isset($r['sort_order']) )
		$r['sort_order'] = 'asc';
	if ( !isset($r['file']) )
		$r['file'] = '';
	if ( !isset($r['list']) )
		$r['list'] = true;
	if ( !isset($r['optiondates']) )
		$r['optiondates'] = 0;
	if ( !isset($r['optioncount']) )
		$r['optioncount'] = 0;
	if ( !isset($r['hide_empty']) )
		$r['hide_empty'] = 1;
	if ( !isset($r['use_desc_for_title']) )
		$r['use_desc_for_title'] = 1;
	if ( !isset($r['children']) )
		$r['children'] = true;
	if ( !isset($r['child_of']) )
		$r['child_of'] = 0;
	if ( !isset($r['categories']) )
		$r['categories'] = 0;
	if ( !isset($r['recurse']) )
		$r['recurse'] = 0;
	if ( !isset($r['feed']) )
		$r['feed'] = '';
	if ( !isset($r['feed_image']) )
		$r['feed_image'] = '';
	if ( !isset($r['exclude']) )
		$r['exclude'] = '';
	if ( !isset($r['hierarchical']) )
		$r['hierarchical'] = true;
	if ( !isset($r['width']) )
		$r['width'] = 700;
	if ( !isset($r['height']) )
		$r['height'] = 500;

	return pt_list_cats($r['optionall'], $r['all'], $r['sort_column'], $r['sort_order'], $r['file'],
        			$r['list'], $r['optiondates'], $r['optioncount'], $r['hide_empty'], $r['use_desc_for_title'], 
                                $r['children'], $r['child_of'], $r['categories'], $r['recurse'], $r['feed'], $r['feed_image'], 
                                $r['exclude'], $r['hierarchical'], $r['width'], $r['height']);
}

function pt_list_cats($optionall = 1, $all = 'All', $sort_column = 'ID', $sort_order = 'asc', $file = '', $list = true, $optiondates = 0, $optioncount = 0, $hide_empty = 1, $use_desc_for_title = 1, $children=FALSE, $child_of=0, $categories=0, $recurse=0, $feed = '', $feed_image = '', $exclude = '', $hierarchical=FALSE, $width=700, $height=500) {
	global $wpdb, $wp_query;
	// Optiondates now works
	if ( '' == $file )
		$file = get_settings('home') . '/';

	$exclusions = '';
	if ( !empty($exclude) ) {
		$excats = preg_split('/[\s,]+/',$exclude);
		if ( count($excats) ) {
			foreach ( $excats as $excat ) {
				$exclusions .= ' AND cat_ID <> ' . intval($excat) . ' ';
			}
		}
	}

	$exclusions = apply_filters('list_cats_exclusions', $exclusions );

	if ( intval($categories) == 0 ) {
		$sort_column = 'cat_'.$sort_column;

		$query = "
			SELECT cat_ID, cat_name, category_nicename, category_description, category_parent, category_count
			FROM $wpdb->categories
			WHERE cat_ID > 0 $exclusions
			ORDER BY $sort_column $sort_order";

		$categories = $wpdb->get_results($query);
	}

	if ( $optiondates ) {
		$cat_dates = $wpdb->get_results("	SELECT category_id,
		UNIX_TIMESTAMP( MAX(post_date) ) AS ts
		FROM $wpdb->posts, $wpdb->post2cat, $wpdb->categories
		WHERE post_status = 'publish' AND post_id = ID $exclusions
		GROUP BY category_id");
		foreach ( $cat_dates as $cat_date ) {
			$category_timestamp["$cat_date->category_id"] = $cat_date->ts;
		}
	}

	$num_found=0;
	$thelist = "";

	foreach ( (array) $categories as $category ) {
		if ( ( intval($hide_empty) == 0 || $category->category_count) && (!$hierarchical || $category->category_parent == $child_of) ) {
			$num_found++;
			$link = '<a href="'.get_category_link($category->cat_ID).'" ';
			if ( $use_desc_for_title == 0 || empty($category->category_description) )
				$link .= 'title="'. sprintf(__("View all posts filed under %s"), attribute_escape($category->cat_name)) . '"';
			else
				$link .= 'title="' . attribute_escape(apply_filters('category_description',$category->category_description,$category)) . '"';
			$link .= '>';
			$link .= apply_filters('list_cats', $category->cat_name, $category).'</a>';

			if ( (! empty($feed_image)) || (! empty($feed)) ) {

				$link .= ' ';

				if ( empty($feed_image) )
					$link .= '(';

				$link .= '<a href="' . get_category_rss_link(0, $category->cat_ID, $category->category_nicename) . '"';

				if ( !empty($feed) ) {
					$title = ' title="' . $feed . '"';
					$alt = ' alt="' . $feed . '"';
					$name = $feed;
					$link .= $title;
				}

				$link .= '>';

				if ( !empty($feed_image) )
					$link .= "<img src='$feed_image' $alt$title" . ' />';
				else
					$link .= $name;

				$link .= '</a>';

				if (empty($feed_image))
					$link .= ')';
			}

			if ( intval($optioncount) == 1 )
				$link .= ' ('.intval($category->category_count).')';

			if ( $optiondates ) {
				if ( $optiondates == 1 )
					$optiondates = 'Y-m-d';
				$link .= ' ' . gmdate($optiondates, $category_timestamp["$category->cat_ID"]);
			}

			if ( $list ) {
				$thelist .= "\t<li";
				if (($category->cat_ID == $wp_query->get_queried_object_id()) && is_category()) {
					$thelist .=  ' class="current-cat"';
				}
				$thelist .= ">$link\n";
			} else {
				$thelist .= "\t$link<br />\n";
			}

			if ($hierarchical && $children)
				$thelist .= list_cats($optionall, $all, $sort_column, $sort_order, $file, $list, $optiondates, $optioncount, $hide_empty, $use_desc_for_title, $hierarchical, $category->cat_ID, $categories, 1, $feed, $feed_image, $exclude, $hierarchical);
			if ($list)
				$thelist .= "</li>\n";
		}
	}
	if ( !$num_found && !$child_of ) {
		if ( $list ) {
			$before = '<li>';
			$after = '</li>';
		}
		echo $before . __("No categories") . $after . "\n";
		return;
	}
	if ( $list && $child_of && $num_found && $recurse ) {
		$pre = "\t\t<ul class='children'>";
		$post = "\t\t</ul>\n";
	} else {
		$pre = $post = '';
	}
	$thelist = $pre . $thelist . $post;
	if ( $recurse )
		return $thelist;
	echo apply_filters('list_cats', $thelist);
}
}
/****************************************************************/
/* Redefines previous_post_link
/****************************************************************/
function pt_previous_post_link($format='&laquo; %link', $link='%title', $in_same_cat = false, $excluded_categories = '') {

	if ( is_attachment() )
		$post = & get_post($GLOBALS['post']->post_parent);
	else
		$post = get_previous_post($in_same_cat, $excluded_categories);

	if ( !$post )
		return;

        $hs_url = get_permalink($post->ID);
        $url_inframe = pt_return_get($hs_url);
	$title = apply_filters('the_title', $post->post_title, $post);
	$string = '<a href="'.$url_inframe.'">';
	$link = str_replace('%title', $title, $link);
	$link = $pre . $string . $link . '</a>';

	$format = str_replace('%link', $link, $format);

	echo $format;
}
/****************************************************************/
/* Redefines next_post_link
/****************************************************************/
function pt_next_post_link($format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '') {
	$post = get_next_post($in_same_cat, $excluded_categories);

	if ( !$post )
		return;

        $hs_url = get_permalink($post->ID);
        $url_inframe = pt_return_get($hs_url);
	$title = apply_filters('the_title', $post->post_title, $post);
	$string = '<a href="'.$url_inframe.'">';
	$link = str_replace('%title', $title, $link);
	$link = $string . $link . '</a>';
	$format = str_replace('%link', $link, $format);

	echo $format;
}


?>