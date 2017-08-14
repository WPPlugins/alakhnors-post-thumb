<?php
/********************************************************************************************************/
/* List of functions
/*
/*    User functions :
/*        function get_thumb			: Get thumbs with default options
/*        function get_thumb_array		: Get thumbs with default options - return array
/*        function get_random_thumb		: Return a random thumbnail with permalink and post title.
/*        function get_recent_thumbs		: Display recents thumbnails
/*        function exclude_regex		: Exclude some REGEX from a content
/*        function tb_post_thumb_test		: Test if image in post
/*
/*    Utility functions :
/*        function tb_post_thumb		: Main function
/*        function tb_post_thumb_array		: Generate thumbnail and return array
/*        function LB_effect			: Display an link/image with an highslide effect
/*
/********************************************************************************************************/

require_once('post-thumb-image-editor.php');

/* ================================================================================================= */
/* User functions
/* ================================================================================================= */

/***********************************************************************************/
/* Test if image in post
/* $img_only = true  : return true if an image is found in the current post
/* $img_only = false : return true if an image or a video is found in the current post
/***********************************************************************************/
function tb_post_thumb_test($img_only=false)
{
        global $post;
        return tb_content_test ($img_only, $post->post_content);
}
/***********************************************************************************/
/* Get thumbs with default options
/*
/* ALTAPPEND : text to append to create thumbnail name. Overide default if exists.
/* WIDTH : resize width. Overide default if greater than 0.
/* HEIGHT : resize height. Overide default if greater than 0.
/* HCROP : horizontal crop. Crop if greater than 0.
/* VCROP : vertical crop. Crop if greater than 0.
/* KEEPRATIO : if set to 1, image ratio is kept. Overide default if exists.
/* BASENAME : force generation of thumbnail and use generic name. Default to 0.
/* TEXTBOX : write post title at the bottom of the thumbnail if = 1. Default to 0.
/* MYCLASS : output class name in html  <a class="myclass" href=...>
/***********************************************************************************/
function get_thumb ($arg='')
{
        global $post;
        return tb_post_thumb('URL='.get_permalink($post->ID).'&ALTTEXT='.$post->post_title.'&'.$arg);
}
/***********************************************************************************/
/* Get thumbs with default options - return array
/*
/* ALTAPPEND : text to append to create thumbnail name. Overide default if exists.
/* WIDTH : resize width. Overide default if greater than 0.
/* HEIGHT : resize height. Overide default if greater than 0.
/* HCROP : horizontal crop. Crop if greater than 0.
/* VCROP : vertical crop. Crop if greater than 0.
/* KEEPRATIO : if set to 1, image ratio is kept. Overide default if exists.
/* BASENAME : force generation of thumbnail and use generic name. Default to 0.
/* TEXTBOX : write post title at the bottom of the thumbnail if = 1. Default to 0.
/***********************************************************************************/
function get_thumb_array ($arg='')
{
        global $post;
        return tb_post_thumb_array ('URL='.get_permalink($post->ID).'&ALTTEXT='.$post->post_title.'&'.$arg);
}
/***********************************************************************************/
/* Return a random thumb with permalink and post title.
/*
/* LIMIT : number of posts to test to obtain an image. Default is 5.
/* WIDTH : resize width. Overide default if greater than 0.
/* HEIGHT : resize height. Overide default if greater than 0.
/* HCROP : horizontal crop. Crop if greater than 0.
/* VCROP : vertical crop. Crop if greater than 0.
/* KEEPRATIO : if set to 1, image ratio is kept. Overide default if exists.
/* TEXTBOX : write post title at the bottom of the thumbnail if = 1. Default to 0.
/* MYCLASS : output class name in html  <a class="myclass" href=...>
/***********************************************************************************/
function get_random_thumb ($arg='')
{
	global $post;
	parse_str($arg, $new_args);
	$new_args = array_change_key_case($new_args, CASE_UPPER);

	if (isset($new_args['LIMIT'])) { $limit = $new_args['LIMIT']; settype($limit,"integer"); } else $limit = 5;
        if (isset($new_args['MYCLASSHREF'])) $myclasshref = $new_args['MYCLASSHREF']; else $myclasshref = '';
        if (isset($new_args['MYCLASSIMG'])) $myclassimg = $new_args['MYCLASSIMG']; else $myclassimg = '';
        if (isset($new_args['LB_EFFECT']))
        {	if ($new_args['LB_EFFECT']==1) $LB_effect = true; else $LB_effect = false;}  else $LB_effect = false;

	$post_its = random_post($limit);
	foreach ($post_its as $post_it) :
		if (tb_content_test(true, $post_it->post_content))
		{
        		$post = wp_get_single_post($post_it->ID);
			setup_postdata($post);
                if ($LB_effect)
                {
			$post_array = get_thumb_array ($arg.'&ALTAPPEND=thumbrandom_&BASENAME=1');
			echo LB_effect ('hs_overlay', 'drop-shadow', 700, 500, $post_array);
                }
                else
                {
			$post_array = tb_post_thumb_array('URL='.get_permalink($post->ID).'&ALTTEXT='.$post->post_title.'&ALTAPPEND=thumbrandom_&BASENAME=1&'.$arg);
        		$post_link = tb_post_thumb_gen_image($post_array['post_url'],
                    		                   		$post_array['server_image'],
    		                                   		$post_array['image_location'],
								$post_array['alt_text'],
								$post_array['show_title'],
								$myclasshref,
								$myclassimg);
			if (empty($post_link)) echo _e('No image found'); else print $post_link;
                }
                        break;
		}
	endforeach;
	flush();
}
/***********************************************************************************/
/* Display recent posts
/***********************************************************************************/
function get_recent_thumbs ($arg)
{
	global $post;
	parse_str($arg, $new_args);
	$new_args = array_change_key_case($new_args, CASE_UPPER);

	if (isset($new_args['LIMIT'])) { $limit = $new_args['LIMIT']; settype($limit,"integer"); } else $limit = 8;
	if (isset($new_args['OFFSET'])) { $offset = $new_args['OFFSET']; settype($offset,"integer"); } else $offset = 0;
        if (isset($new_args['SHOWPOST']))
        {	if ($new_args['SHOWPOST']==1) $showpost = true; else $showpost = false;}  else $showpost = false;
        if (isset($new_args['LB_EFFECT']))
        {	if ($new_args['LB_EFFECT']==1) $LB_effect = true; else $LB_effect = false;}  else $LB_effect = false;

	if (isset($new_args['CATEGORY'])) 
	{
		$cat_ID = $new_args['CATEGORY'];
		$posts = get_posts('category='.$cat_ID.'&numberposts='.$limit.'&offset='.$offset);
	}
        else
		$posts = get_posts('numberposts='.$limit.'&offset='.$offset);

	foreach ($posts as $post) :
		setup_postdata($post);
		$test_img = tb_post_thumb_test(true);
		$html_body = $post->post_content;
                if ($LB_effect)
                {
			$post_array = get_thumb_array ($arg);
			?><li class="img_thumb" style="display:inline;"><?php
				if ($test_img && !$showpost)
       					echo LB_effect ('hs_overlay', 'drop-shadow', 700, 500, $post_array, '', 'rc');
				else
					echo LB_effect ('hs_newwindow', 'beveled', 700, 500, $post_array, $html_body, 'rc');
			?></li><?php
                }
                else
                {
			$post_link = get_thumb($arg);
			if ( !empty($post_link) ) { echo $post_link; }
                }
	endforeach;
}
/***********************************************************************************/
/* Exclude some REGEX from a content
/***********************************************************************************/
function exclude_regex ($content)
{
    $result = $content;
    $reg_coolplayer = '/\[coolplayer](.*?)\[\/coolplayer]/i';
    $reg_youtube = '/\[youtube](.*?)\[\/youtube]/i';
    $reg_dailymotion = '/\[dailymotion](.*?)\[\/dailymotion]/i';
    $reg_googlevideo = '/\[googlevideo](.*?)\[\/googlevideo]/i';
    $reg_wordtube = '/\[MEDIA=(.*?)]/i';

    $pt_youtube = '/\[youtube=\((.*?)\]/i';
    $pt_dailymotion = '/\[dailymotion=\((.*?)\]/i';

    $content = preg_replace($reg_coolplayer, '...', $content);
    $content = preg_replace($reg_youtube, '...', $content);
    $content = preg_replace($reg_dailymotion, '...', $content);
    $content = preg_replace($reg_googlevideo, '...', $content);
    $content = preg_replace($reg_wordtube, '...', $content);
    $content = preg_replace($pt_youtube, '...', $content);
    $content = preg_replace($pt_dailymotion, '...', $content);

    return $content;
}
/***********************************************************************************/
/* Main function
/*
/* URL : must be a valid URL. Text to link to thumbnail.
/* ALTTEXT : text to show on mouseover
/* ALTAPPEND : text to append to create thumbnail name. Overide default if exists.
/* WIDTH : resize width. Overide default if greater than 0.
/* HEIGHT : resize height. Overide default if greater than 0.
/* HCROP : horizontal crop. Crop if greater than 0.
/* VCROP : vertical crop. Crop if greater than 0.
/* KEEPRATIO : if set to 1, image ratio is kept. Overide default if exists.
/* BASENAME : force generation of thumbnail and use generic name. Default to 0.
/* TEXTBOX : write post title at the bottom of the thumbnail if = 1. Default to 0.
/* MYCLASSHREF : output class name in html  <a class="myclass" href=...>
/* MYCLASSIMG : output class name in html  <a class="myclass" href=...>
/***********************************************************************************/
function tb_post_thumb($arg='')
{
	global $post;
        parse_str($arg, $new_args);
        $new_args = array_change_key_case($new_args, CASE_UPPER);

        if (isset($new_args['MYCLASSHREF'])) $myclasshref = $new_args['MYCLASSHREF']; else $myclasshref = '';
        if (isset($new_args['MYCLASSIMG'])) $myclassimg = $new_args['MYCLASSIMG']; else $myclassimg = '';

        $thumb_array = tb_post_thumb_array ($arg);

        return tb_post_thumb_gen_image($thumb_array['post_url'],
                                       $thumb_array['server_image'],
                                       $thumb_array['image_location'],
                                       $thumb_array['alt_text'],
                                       $thumb_array['show_title'],
                                       $myclasshref,
                                       $myclassimg,
                                       $thumb_array['title']);
}
/***********************************************************************************/
/* Generate thumbnail and return array
/*
/* Parameters:
/* URL : must be a valid URL. Text to link to thumbnail.
/* ALTTEXT : text to show on mouseover
/* ALTAPPEND : text to append to create thumbnail name. Overide default if exists.
/* WIDTH : resize width. Overide default if greater than 0.
/* HEIGHT : resize height. Overide default if greater than 0.
/* HCROP : horizontal crop. Crop if greater than 0.
/* VCROP : vertical crop. Crop if greater than 0.
/* KEEPRATIO : if set to 1, image ratio is kept. Overide default if exists.
/* BASENAME : force generation of thumbnail and use generic name. Default to 0.
/* TEXTBOX : write post title at the bottom of the thumbnail if = 1. Default to 0.
/* USECATNAME : will use a specific default image for each category if 1. Default to 0.
/* TITLE: choose what to display in title. T=post title, C=Content, E=Excerpt
/*
/* Array:
/*	'post_url'       = post url (permalink)
/*	'server_image'   = absolute path to thumbnail
/*	'image_location' = thumbnail url
/*	'alt_text'       = post title
/*	'post_ID'        = post ID
/*	'the_image'      = image url
/*	'show_title'     = SHOWTITLE result (html code string)
/*	'title'          = TITLE result (title tag)
/*
/***********************************************************************************/
function tb_post_thumb_array ($arg='')
{
	global $post;
	$settings = get_option('post_thumbnail_settings');

        parse_str($arg, $new_args);
        $new_args = array_change_key_case($new_args, CASE_UPPER);
        if (isset($new_args['URL']))       $post_url = $new_args['URL'];                    else $post_url ='';
        if (isset($new_args['ALTTEXT']))   $alt_text = str_clean ($new_args['ALTTEXT']);    else $alt_text ='';
        if (isset($new_args['ALTAPPEND'])) $alt_append = $new_args['ALTAPPEND'];            else $alt_append ='';
	if ($alt_append != '')  $settings['append_text']   = $alt_append;

        if (isset($new_args['WIDTH']))     $resize_width = $new_args['WIDTH'];              else $resize_width = 0;
        if ($resize_width > 0)  $settings['resize_width']  = $resize_width;

        if (isset($new_args['HEIGHT']))    $resize_height = $new_args['HEIGHT'];            else $resize_height = 0;
        if ($resize_height > 0) $settings['resize_height'] = $resize_height;

        if (isset($new_args['HCROP']))     $crop_x = $new_args['HCROP'];                    else $crop_x = 0;
        if (isset($new_args['VCROP']))     $crop_y = $new_args['VCROP'];                    else $crop_y = 0;
        if (isset($new_args['KEEPRATIO']))
        {	if ($new_args['KEEPRATIO']==1) $keep_ratio = true; else $keep_ratio = false;
		$settings['keep_ratio'] = $keep_ratio; }                                    else $keep_ratio = $settings['keep_ratio'];

        if (isset($new_args['USECATNAME']))
        {	
		if ($new_args['USECATNAME']==1) $use_catname = true; else $use_catname = false;
		$settings['use_catname'] = $use_catname; 
	}
	else 
        {
        	if ($settings['use_catname']=='true') $use_catname = true; else $use_catname = false;
	}

        if (isset($new_args['BASENAME']))
        {	if ($new_args['BASENAME']==1) $base_name = true; else $base_name = false;}  else $base_name = false;
        if (isset($new_args['SHOWTITLE'])) $show_title = tb_return_title($new_args['SHOWTITLE']);	    else $show_title = '';
        if (isset($new_args['TITLE'])) 		$img_title = tb_get_title($new_args['TITLE']);	    		else $img_title = '';
        if (isset($new_args['TEXTBOX']))
        {	if ($new_args['TEXTBOX']==1) $textBox = true; else $textBox = false;}       else $textBox = false;

        define('POSTTHUMB_URLPATH', str_replace($settings['full_domain_name'], '/',get_settings('siteurl')).'/wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');

        // finds an image from the post content
	if (preg_match('/<img (.*?)src=["'."']".'(.*?)["'."']".'/i',$post->post_content,$matches))
        {
		// put matches into recognizable vars
		$the_image = tb_thumb_absolute($matches[2]);

		// detects if an image is already linked to a thumbnail
		$pattern = '/<a(.*?)href=["'."']".'(.*?).(bmp|jpg|jpeg|gif|png)["'."']".'(.*?)>(.*?)<img(.*?)src=["'."']".'(.*?)["'."']".'(.*?)>/i';
		if (preg_match($pattern,$post->post_content,$matches)) $the_image = tb_thumb_absolute($matches[2].'.'.$matches[3]);

                $the_image_server = str_replace($settings['full_domain_name'], $settings['base_path'], $the_image);

		// check if image exists on server
		// if doesn't exist, can't do anything so return default image
		if (!remote_file_exists($the_image_server))
                {
                   $thumb_array = return_default_image ($post_url, $alttext, $settings, $post->ID, $show_title, $use_catname, $img_title);
		}

		$dest_path = pathinfo($the_image_server);
		if ($base_name)
                   $dest_path_temp['basename'] = 'azerty123456789'.'.'.$dest_path['extension'];
                else
                   $dest_path_temp['basename'] = $dest_path['basename'];

		// dir to save thumbnail to
		$save_dir = $settings['base_path'].'/'.$settings['folder_name'];

		// name to save to - Adds append text
		if ($settings['append'] == 'true')
                {
		   $filename = substr($dest_path_temp['basename'], 0, strrpos($dest_path_temp['basename'], "."));
		   $rename_to = $filename.$settings['append_text'].'.'.$dest_path['extension'];
		}
		else
                   $rename_to = $settings['append_text'].$dest_path_temp['basename'];

		// checks if file already exists - returns location if it does
		if (remote_file_exists($save_dir.'/'.$rename_to) && (!$base_name)) 
                {
                        $imagelocation = $settings['full_domain_name'].'/'.$settings['folder_name'].'/'.$rename_to;
			$thumb_array['post_url']       	= $post_url;
			$thumb_array['server_image']   	= $save_dir.'/'.$rename_to;
			$thumb_array['image_location'] 	= $imagelocation;
			$thumb_array['alt_text']       	= $alt_text;
			$thumb_array['post_ID']        	= $post->ID;
			$thumb_array['the_image']      	= $the_image;
			$thumb_array['show_title']     	= $show_title;
			$thumb_array['title']     	= $img_title;
		}
                else
                {
			// if file has to be generated, generates thumbnails
			$thumb = new ImageEditor($dest_path['basename'],$dest_path['dirname'].'/');
			$thumb->resize($settings['resize_width'], $settings['resize_height'], $crop_x, $crop_y, $settings['keep_ratio']);

	                // Adds text box if option is checked
       	        	if ($textBox) $thumb->AddBox (true, 0, 0, 0, 15, $post->post_title, 255, 255, 255, 15);

			$thumb->outputFile($save_dir."/".$rename_to, "");
			$imagelocation = $settings['full_domain_name']."/".$settings['folder_name']."/".$rename_to;
			$thumb_array['post_url']       	= $post_url;
			$thumb_array['server_image']   	= $save_dir.'/'.$rename_to;
			$thumb_array['image_location'] 	= $imagelocation;
			$thumb_array['alt_text']       	= $alt_text;
			$thumb_array['post_ID']        	= $post->ID;
			$thumb_array['the_image']      	= $the_image;
			$thumb_array['show_title']     	= $show_title;
			$thumb_array['title']     	= $img_title;
                }
	}
        // If no image found in the post content - checks for video
        else
        {
		if (!empty($settings['video_regex']) && tb_post_thumb_check_video($settings['video_regex']))
		{
			$settings['default_image'] = $settings['video_default'];
		}
		if ($settings['stream_check'])
		{
			$settings['default_image'] = tb_post_thumb_check_stream($settings['default_image']);
		}
		$thumb_array = return_default_image ($post_url, $alt_text, $settings, $post->ID, $show_title, $use_catname, $img_title);
	}
	return $thumb_array;
}
/***********************************************************************************/
/* Generate thumbnail and return array
/*
/* Parameters:
/* URL : must be a valid URL. Text to link to thumbnail.
/* ALTTEXT : text to show on mouseover
/* ALTAPPEND : text to append to create thumbnail name. Override default if exists.
/* WIDTH : resize width. Override default if greater than 0.
/* HEIGHT : resize height. Override default if greater than 0.
/* HCROP : horizontal crop. Crop if greater than 0.
/* VCROP : vertical crop. Crop if greater than 0.
/* KEEPRATIO : if set to 1, image ratio is kept. Override default if exists.
/* BASENAME : force generation of thumbnail and use generic name. Default to 0.
/* TEXTBOX : write post title at the bottom of the thumbnail if = 1. Default to 0.
/* USECATNAME : will use a specific default image for each category if 1. Default to 0.
/*
/* Array:
/*	'post_url'       = post url (permalink)
/*	'server_image'   = absolute path to thumbnail
/*	'image_location' = thumbnail url
/*	'alt_text'       = post title
/*	'post_ID'        = post ID
/*	'the_image'      = image url
/*	'show_title'     = SHOWTITLE result (html code string)
/*
/***********************************************************************************/
function tb_image_thumb_array ($arg='')
{
	$settings = get_option('post_thumbnail_settings');

        parse_str($arg, $new_args);
        $new_args = array_change_key_case($new_args, CASE_UPPER);
        if (isset($new_args['IMG']))       $img_url = $new_args['IMG'];                    else $img_url ='';
        if (isset($new_args['URL']))       $post_url = $new_args['URL'];                    else $post_url ='';
        if (isset($new_args['ALTTEXT']))   $alt_text = str_clean ($new_args['ALTTEXT']);    else $alt_text ='';
        if (isset($new_args['ALTAPPEND'])) $alt_append = $new_args['ALTAPPEND'];            else $alt_append ='';
	if ($alt_append != '')  $settings['append_text']   = $alt_append;

        if (isset($new_args['WIDTH']))     $resize_width = $new_args['WIDTH'];              else $resize_width = 0;
        if ($resize_width > 0)  $settings['resize_width']  = $resize_width;

        if (isset($new_args['HEIGHT']))    $resize_height = $new_args['HEIGHT'];            else $resize_height = 0;
        if ($resize_height > 0) $settings['resize_height'] = $resize_height;

        if (isset($new_args['HCROP']))     $crop_x = $new_args['HCROP'];                    else $crop_x = 0;
        if (isset($new_args['VCROP']))     $crop_y = $new_args['VCROP'];                    else $crop_y = 0;
        if (isset($new_args['KEEPRATIO']))
        {	if ($new_args['KEEPRATIO']==1) $keep_ratio = true; else $keep_ratio = false;
		$settings['keep_ratio'] = $keep_ratio; }                                    else $keep_ratio = $settings['keep_ratio'];

        if (isset($new_args['USECATNAME']))
        {	
		if ($new_args['USECATNAME']==1) $use_catname = true; else $use_catname = false;
		$settings['use_catname'] = $use_catname; 
	}
	else 
        	if ($settings['use_catname']=='true') $use_catname = true; else $use_catname = false;

        if (isset($new_args['BASENAME']))
        {	if ($new_args['BASENAME']==1) $base_name = true; else $base_name = false;}  else $base_name = false;
        if (isset($new_args['SHOWTITLE'])) 	$show_title = tb_return_title($new_args['SHOWTITLE']);	    	else $show_title = '';
        if (isset($new_args['TITLE'])) 		$img_title = tb_get_title($new_args['TITLE']);	    		else $img_title = '';
        if (isset($new_args['TEXT'])) 		$text = $new_args['TEXT'];	    				else $text = '';
        if (isset($new_args['TEXTBOX']))
        {	if ($new_args['TEXTBOX']==1) $textBox = true; else $textBox = false;}       else $textBox = false;

        define('POSTTHUMB_URLPATH', str_replace($settings['full_domain_name'], '/',get_settings('siteurl')).'/wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');

	// put matches into recognizable vars
	$the_image = $img_url;

	$the_image_server = str_replace($settings['full_domain_name'], $settings['base_path'], $the_image);

	// check if image exists on server
	// if doesn't exist, can't do anything so return default image
	if (!remote_file_exists($the_image_server))
	{
		$thumb_array = return_default_image ($post_url, $alttext, $settings, $post->ID, $show_title, $use_catname, $img_title);
	}

	$dest_path = pathinfo($the_image_server);
	if ($base_name)
		$dest_path_temp['basename'] = 'azerty123456789'.'.'.$dest_path['extension'];
	else
		$dest_path_temp['basename'] = $dest_path['basename'];

	// dir to save thumbnail to
	$save_dir = $settings['base_path'].'/'.$settings['folder_name'];

	// name to save to - Adds append text
	if ($settings['append'] == 'true')
	{
		$filename = substr($dest_path_temp['basename'], 0, strrpos($dest_path_temp['basename'], "."));
		$rename_to = $filename.$settings['append_text'].'.'.$dest_path['extension'];
	}
	else
		$rename_to = $settings['append_text'].$dest_path_temp['basename'];

	// checks if file already exists - returns location if it does
	if (remote_file_exists($save_dir.'/'.$rename_to) && (!$base_name))
	{
		$imagelocation = $settings['full_domain_name'].'/'.$settings['folder_name'].'/'.$rename_to;
		$thumb_array['post_url']       	= $post_url;
		$thumb_array['server_image']   	= $save_dir.'/'.$rename_to;
		$thumb_array['image_location'] 	= $imagelocation;
		$thumb_array['alt_text']       	= $alt_text;
		$thumb_array['post_ID']        	= $post->ID;
		$thumb_array['the_image']      	= $the_image;
		$thumb_array['show_title']     	= $show_title;
		$thumb_array['title']     	= $img_title;
	}
	else
	{
		// if file has to be generated, generates thumbnails
		$thumb = new ImageEditor($dest_path['basename'],$dest_path['dirname'].'/');
		$thumb->resize($settings['resize_width'], $settings['resize_height'], $crop_x, $crop_y, $settings['keep_ratio']);

		// Adds text box if option is checked
		if ($textBox) $thumb->AddBox (true, 0, 0, 0, 15, $text, 255, 255, 255, 15);

		$thumb->outputFile($save_dir."/".$rename_to, "");
		$imagelocation = $settings['full_domain_name']."/".$settings['folder_name']."/".$rename_to;
		$thumb_array['post_url']       	= $post_url;
		$thumb_array['server_image']   	= $save_dir.'/'.$rename_to;
		$thumb_array['image_location'] 	= $imagelocation;
		$thumb_array['alt_text']       	= $alt_text;
		$thumb_array['post_ID']        	= $post->ID;
		$thumb_array['the_image']      	= $the_image;
		$thumb_array['show_title']     	= $show_title;
		$thumb_array['title']     	= $img_title;
	}
	return $thumb_array;
}
/*========================================================================================================*/
/* Utility functions
/*========================================================================================================*/

/***********************************************************************************/
/* Test if image in content
/* $img_only = true  : return true if an image is found in the current post
/* $img_only = false : return true if an image or a video is found in the current post
/***********************************************************************************/
function tb_content_test($img_only=false, $content='')
{
	$settings = get_option('post_thumbnail_settings');

	// find an image from the post content
	if (preg_match('/<img(.*?)src=["'."']".'(.*?)["'."']".'/i',$content,$matches)) return true;
        else
        {
           if (!empty($settings['video_regex']) && (!$img_only) && tb_content_check_video($settings['video_regex'], $content)) return true;
           if (($settings['stream_check']) && (!$img_only) && (tb_content_test_stream($content))) return true;
	   return false;
	}
}
/****************************************************************/
/* Generate html string to return
/****************************************************************/
function tb_post_thumb_gen_image($post_url, $server_image, $site_image, $alt_text='', $show_title='', $myclasshref='', $myclassimg='', $title='')
{
        if ($myclasshref=='') $a = '<a '; else $a = '<a class="'.$myclasshref.'" ';
        if ($myclassimg=='') $img = '<img '; else $img = '<img class="'.$myclassimg.'" ';
        if ($title=='') $title = $alt_text;

	list($width, $height, $type, $attr) = getimagesize($server_image);
	
	// format the link below the thumb if needed
        if ($show_title == '') $slasha = '</a>';
	else
	{
		$slasha = '</a><br /><a href="'.$post_url.'" style = "width:'.$width.';"><span>'.$show_title.'</span></a>';
	}

        // return the html code
	if ($post_url=='')
	      return $a.'href="#">'.$img.'src="'.$site_image.'" '.$attr.' alt="'.$alt_text.'" /></a>';
	else
              return $a.'href="'.$post_url.'" title="'.$title.'">'.$img.'src="'.$site_image.'" alt="'.$alt_text.'" />'.$slasha;
}
/****************************************************************/
/* Return LB_effect for current post
/****************************************************************/
function pt_LB_effect ($arg='', $hs_function='hs_link', $hs_style='beveled', $hs_width=700, $hs_height=500)
{
	global $post;

	$html_body = $post->post_content;
	$post_link = get_thumb_array ($arg);
//	echo $post_link;
	echo LB_effect ($hs_function, $hs_style, $hs_width, $hs_height, $post_link, $html_body);

}
/****************************************************************/
/* Return html string including effect depending on gb_function
/****************************************************************/
function LB_effect ($hs_function='hs_html', $style='rounded-white', $width='700', $height='500', $arg_array, $html_content='', $tag='')
{
	$post_url = $arg_array['post_url'];
	$site_image = $arg_array['image_location'];
	if (function_exists('jLanguage_processTitle'))
		$arg_array['alt_text'] = jLanguage_processTitle($arg_array['alt_text']);
	$alt_text = $arg_array['alt_text'];
	$id_ID = $arg_array['post_ID'].$tag;
	$show_title = $arg_array['show_title'];
	$html_body = __($html_content);
	$img_url = $arg_array['the_image'];
	if (empty($arg_array['title'])) $title = $arg_array['alt_text']; else $title=$arg_array['title'];

	$position = "align: 'center'";
	$outlineType = "outlineType: '".$style."'";

	if ($show_title == '') $slasha = '';
	else
	{
		$slasha = '<br /><a href="'.$post_url.'" title="'.$alt_text.'"><span>'.$show_title.'</span></a>';
	}

	return pt_get_effect ($hs_function, $style, $post_url, $width, $height, $id_ID, $alt_text, $site_image, $img_url, $slasha, $html_body, '', $title);

}
/****************************************************************/
/* Check if there is a video in content using REGEX
/****************************************************************/
function tb_content_check_video($regex, $content)
{
	if (preg_match('/'.$regex.'/i',$content,$matches)) return true;
	else return false;
}
/****************************************************************/
/* Check if there is a video in post using REGEX
/****************************************************************/
function tb_post_thumb_check_video($regex)
{
	global $post;
	return tb_content_check_video($regex, $post->post_content);
}
/****************************************************************/
/* Check if there is a video stream in content using pre-formatted strings
/****************************************************************/
function tb_content_test_stream($content)
{
	if (!(strpos($content, 'http://www.youtube.com/watch') === false))
	{  return true; }
	if (!(strpos($content, 'http://www.dailymotion.com/swf') === false))
	{  return true; }
	if (!(strpos($content, 'http://video.google.fr/videoplay') === false))
	{  return true; }
	if (!(strpos($content, 'http://video.google.com/videoplay') === false))
	{  return true; }
	return false;
}
/****************************************************************/
/* Check if there is a video stream in post using pre-formatted strings
/****************************************************************/
function tb_post_thumb_test_stream()
{
   global $post;
   return tb_content_test_stream($post->post_content);
}
/****************************************************************/
/* Returns adhoc thumbnail for videostream
/****************************************************************/
function tb_post_thumb_check_stream($default_img)
{
	global $post;
	$def_img = $default_img ;
	if (!(strpos($post->post_content, 'http://www.youtube.com/watch') === false))
	{
		$def_img = POSTTHUMB_URLPATH.'images/youtube.png';
	}
	if (!(strpos(strtoupper($post->post_content), '[YOUTUBE=(') === false))
	{
		$def_img = POSTTHUMB_URLPATH.'images/youtube.png';
	}
	if (!(strpos(strtoupper($post->post_content), '[DAILYMOTION=(') === false))
	{
		$def_img = POSTTHUMB_URLPATH.'/images/dailymotion.png';
	}
	if (!(strpos($post->post_content, 'http://www.dailymotion.com/swf') === false))
	{
		$def_img = POSTTHUMB_URLPATH.'/images/dailymotion.png';
	}
	if (!(strpos($post->post_content, 'http://video.google.fr/videoplay') === false))
	{
		$def_img = POSTTHUMB_URLPATH.'/images/gvideo.png';
	}
	if (!(strpos($post->post_content, 'http://video.google.com/videoplay') === false))
	{
		$def_img = POSTTHUMB_URLPATH.'/images/gvideo.png';
	}
	return $def_img;
}
/****************************************************************
* Test if remote image exists
* @param url to test
* @return true if file exists
****************************************************************/
function remote_file_exists ($url)
{
    $url_fopen_is_allowed = ini_set('allow_url_fopen', '1');
    if (($url_fopen_is_allowed === false) && (allow_url_open != true)) return file_exists($url);
    if (allow_url_fopen == true)
      if (@fclose(@fopen($url, 'r'))) {return true;} else {return false;}
      else {return @file_exists($url);}
}
/****************************************************************/
/* Test if remote image exists
/****************************************************************/
function remote_file_exists2 ($url)
{
	$file_exists = @file_exists($url);
	if ($file_exists) return true;
	else
        // file_exists not working on remote url
	{
		//check if allow_url_fopen is enabled
		$url_fopen_is_allowed = ini_set('allow_url_fopen', '1');
		if (($url_fopen_is_allowed === false) && (ALLOW_URL_OPEN != true)) return @file_exists($url);
		// If enabled, check remote url
		if (ALLOW_URL_FOPEN == true)
		{
			$imgfile = @fopen($url, 'r');
			if ($imgfile)
			{
				@close($imgfile);
				return true;
			}
                        else
                        {
				return false;
			}
		}
		else
		// If not, probably return false^^
                {
			return @file_exists($url);
		}
	}
}
/****************************************************************/
/* Get random post
/****************************************************************/
function random_post ($limit)
{
	global $wpdb, $wp_version, $tableposts;

	if ($wp_version < '2.1')
		$post_type_sql = "AND post_status = 'publish'";
	else
		$post_type_sql = "AND post_status = 'publish' AND post_type = 'post'";

	$order_by_sql = "rand()";

	// query records that contain img tags, ordered randomly
	// do not select images from password protected posts
	$sql = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_content,
			$wpdb->posts.post_date, $wpdb->posts.post_author
		FROM $wpdb->posts
		WHERE post_password = ''
		$post_type_sql
		ORDER BY $order_by_sql
		LIMIT $limit";
	return $wpdb->get_results($sql);
}
/****************************************************************/
/* Return absolute path
/****************************************************************/
function tb_thumb_absolute ($the_image)
{
	$test_img = parse_url($the_image);
	if (empty($test_img['scheme']))
		$the_img = canonicalize(str_replace( "\\", "/",get_settings('siteurl').str_replace( "//", "/",'/../'.$the_image)));
        else
		$the_img = $the_image;
	return $the_img;
}
/****************************************************************/
/* retourne un chemin canonique a partir d'un chemin contenant des ../
/****************************************************************/
function canonicalize($address)
{
	$address = explode('/', $address);
	$keys = array_keys($address, '..');

	foreach($keys AS $keypos => $key)
	{
		array_splice($address, $key - ($keypos * 2 + 1), 2);
	}

	$address = implode('/', $address);
	$address = str_replace('./', '', $address);
	return $address;
}
/****************************************************************/
/* Return default image
/****************************************************************/
function return_default_image ($post_url, $alt_text, $settings, $post_ID, $show_title='', $use_catname=false, $title='')
{
	if ($use_catname)
	{
		if (get_bloginfo('version')>='2.1')
			$myposts = wp_get_post_categories($post_ID);
		else
			$myposts = wp_get_post_cats('1', $post_ID);

		$array_def = pathinfo($settings['default_image']);
		$filename = substr($array_def['basename'], 0, strrpos($array_def['basename'], "."));
		$i=0;
		foreach ($myposts as $mypost)
		{
			$new_def = $array_def['dirname'].'/'.$filename.'cat-'.$mypost.'.'.$array_def['extension'];
			if (remote_file_exists($settings['full_domain_name']."/".$new_def))
			{
                        	$settings['default_image']=$new_def;
                        	break;
			}
		}
	}

	$default_array['post_url']       	= $post_url;
	$default_array['server_image']   	= $settings['base_path']."/".$settings['default_image'];
	$default_array['image_location'] 	= $settings['full_domain_name']."/".$settings['default_image'];
	$default_array['alt_text']       	= $alt_text;
	$default_array['generate']       	= true;
	$default_array['post_ID']        	= $post_ID;
	$default_array['the_image']      	= $post_url;
	$default_array['show_title']     	= $show_title;
	$default_array['title']     		= $title;


	return $default_array;
}
/****************************************************************/
/* Add post-thumb option
/****************************************************************/
function tb_post_thumb_options()
{
	if (function_exists('add_options_page'))
        {
		add_options_page('Post Thumbnails', 'Post Thumbs', 8, basename(__FILE__), 'tb_post_thumb_options_subpanel');
	}
}
/****************************************************************/
/* Add post-thumb option TITLE
/****************************************************************/
function get_the_excerpt_revisited($excerpt_length=120, $more_link_text="...", $no_more=false)
{
	global $post;
	$ellipsis = 0;
	$output = '';

	if (!empty($post->post_password))  // if there's a password
        { 
		if ($_COOKIE['wp-postpass_'.COOKIEHASH] != $post->post_password) { // and it doesn't match cookie
			if(is_feed()) { // if this runs in a feed
				$output = __('There is no excerpt because this is a protected post.');
			} else {
	            $output = get_the_password_form();
			}
		}
		return $output;
	}


	$text = strip_tags(stripslashes($post->post_content));
	if (function_exists('jLanguage_processTitle'))
		$text = jLanguage_processTitle($text);

	$pattern = '/\[MEDIA=([0-9]+%?)\]/i';
        $text = preg_replace($pattern,'',$text);
	$pattern = '/\[MEDIA=([0-9]+%?)(.*?)\]/i';
        $text = preg_replace($pattern,'',$text);
	$pattern = '/\[PTPLAYLIST=\((.*?)\)(.*?)]/i';
        $text = preg_replace($pattern,'',$text);
	$pattern = '/\[dailymotion=\((.*?)\)(.*?)\]/i';
        $text = preg_replace($pattern,'',$text);
	$pattern = '/\[youtube=\((.*?)\)(.*?)\]/i';
        $text = preg_replace($pattern,'',$text);

	$text = rtrim($text, "\s\n\t\r\0\x0B");
	if($excerpt_length < 0 || $text=='')
        {
		$output = $text;
	}
        else 
        {
		if(!$no_more && strpos($text, '<!--more-->'))
                {
			$text = explode('<!--more-->', $text, 2);
			$l = count($text[0]);
			$more_link = 1;
		} 
                else
                {
			$text = explode(' ', $text);
			if(count($text) > $excerpt_length) 
                        {
				$l = $excerpt_length;
				$ellipsis = 1;
			} 
                        else 
                        {
				$l = count($text);
				$more_link_text = '';
				$ellipsis = 0;
			}
		}
		for ($i=0; $i<$l; $i++)	$output .= $text[$i] . ' ';
	}

	$output = rtrim($output, "\s\n\t\r\0\x0B");
	$output .= ($ellipsis) ? '...' : '';

	return $output;
}
/****************************************************************/
/* Add post-thumb option TITLE
/****************************************************************/
function tb_get_title ($title='')
{
	global $post;
	$arg = strtoupper($title);
	switch ($arg)
	{
		case 'C':
			$ret = get_the_excerpt_revisited(-1);
			break;
		case 'E':
			$ret = get_the_excerpt_revisited(40);
			break;
		case 'T':
			if (function_exists('jLanguage_processTitle'))
				$ret = jLanguage_processTitle($post->post_title);
			else
                        	$ret = $post->post_title;
			break;
	}
	return $ret;
}
/****************************************************************/
/* Add post-thumb option SHOWTITLE
/****************************************************************/
function tb_return_title ($showtitle='')
{
	global $post;
	$arg = strtoupper($showtitle);
	$ret = '';
	$sep = ', ';
	while ($arg <>'') :
		if (strlen($arg)==1) $sep = '';
		$test = substr($arg, 0, 1);
		switch ($test)
		{
			case 'A':
			$ret .= get_author_name($post->post_author).$sep;
			break;
			case 'D':
			$ret .= substr($post->post_date, 0, 10).$sep;
			break;
			case 'T':
			if (function_exists('jLanguage_processTitle'))
				$title = jLanguage_processTitle($post->post_title);
			else
                                $title = $post->post_title;

			$ret .= $title.$sep;
			break;
		}
		$arg = substr($arg, 1);
        endwhile;
	return $ret;
}
/****************************************************************/
/* Return a string cleaned of annoying '\'
/****************************************************************/
function str_clean ($item)
{
	return str_replace(array("\`", "\'", '\"'), array("`", "'", '"'), $item);
}
/****************************************************************/
/* Test if a path is writable
/****************************************************************/
function test_folder_name ($is_writable_dir)
{
	$rs = ABSPATH.'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/index.htm';
	$rt = $is_writable_dir.'/index.htm';

	if (is_dir($is_writable_dir))
	{
		if (@copy($rs, $rt)===false)
		{
			$iswritable['error_writable'] = false;
			$iswritable['error_msg'] = __('Directory: ', 'post-thumb').$is_writable_dir.' '.__('may not be writeable.', 'post-thumb');
		}
		else
		{
                        unlink($rt);
			$iswritable['error_writable'] = true;
                	$iswritable['error_msg'] = '';
		}
	}
	else
	{
		$iswritable['error_writable'] = false;
                $iswritable['error_msg'] = __('Directory: ', 'post-thumb').$is_writable_dir.' '.__('does not exist!', 'post-thumb');
	}
        return $iswritable;
}
/****************************************************************/
/* Validates update options
/****************************************************************/
function pt_validate_options($new_options)
{
	$update_error='';
        // Test resize values
	if (!is_numeric($new_options['resize_width']))
	{
		$update_error = __("Resize width must be a number");
		$new_options['resize_width'] = '60';
	}
	else
		if (!is_numeric($new_options['resize_height']))
		{
			$update_error = __("Resize height must be a number");
			$new_options['resize_height'] = '60';
		}

        // Test default image
	$video_exists = file_exists($new_options['base_path'].'/'.$new_options['video_default']);
	if (!$video_exists) $update_error = __('Video default image not found on server.', 'post-thumb');

        // Test default image
	$default_exists = file_exists($new_options['base_path'].'/'.$new_options['default_image']);
	if (!$default_exists) $update_error = __('Default image not found on server.', 'post-thumb');

        // Test folder name
	$writable = test_folder_name($new_options['base_path'].'/'.$new_options['folder_name']);
	if (!$writable['error_writable']) $update_error = $writable['error_msg'];

	return $update_error;
}
?>