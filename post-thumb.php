<?php
/*
Plugin Name: Post thumb revisited
Plugin URI: http://www.alakhnor.com/post-thumb
Description: Thumbnail an image from own server from your post. Useful for listing popular posts, post list, etc.
Version: Alakhnor 1.45
Author:  Alakhnor
Author URI: http://www.alakhnor.com/post-thumb

	Copyright (c) 2006 Victor Chang (http://theblemish.com) for post thumb
	Copyright (c) 2007 Alakhnor (http://www.alakhnor.info) for post thumb revisited
	Post Thumbs is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

	This is a WordPress 2 plugin (http://wordpress.org).
        Highslide JS is licensed under a Creative Commons Attribution-NonCommercial 2.5 License: http://creativecommons.org/licenses/by-nc/2.5/
*/

// define URL
$myabspath = str_replace("\\","/",ABSPATH);  // required for windows
define('POSTHUMB_ABSPATH', get_settings('siteurl').'/wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');

require_once('post-thumb-functions.php');
require_once('post-thumb-options.php');

// Comments this line if you do not want to use Highslide effects
require_once('post-thumb-includes.php');


add_option('post_thumbnail_settings',$data,'Post Thumbnail Options');
add_action('admin_menu', 'tb_post_thumb_options');
if (function_exists('pt_include_header')) add_action('wp_head', 'pt_include_header');
if (function_exists('pt_replace_thumb')) add_filter('the_content', 'pt_replace_thumb');

?>