<?php
/*******************************************************************************/
/* Option panel
/*******************************************************************************/
// Load language file
$locale = get_locale();
if ( !empty($locale) )
load_textdomain('post-thumb', str_replace( "\\", "/",ABSPATH) . 'wp-content/plugins/'. dirname(plugin_basename(__FILE__)).'/languages/' . 'post-thumb'.$locale.'.mo');

// Init parameters
$su = get_settings('siteurl');
$up = get_settings('upload_path');
$pa = parse_url(get_settings('siteurl'));
$dn = str_replace($pa['path'],"",$su);
$bp = str_replace($pa['path'],"",str_replace( "\\", "/",ABSPATH));
$bp = substr($bp, 0, strlen($bp)-1);
$pa['path'] = substr($pa['path'], 1, strlen($pa['path'])-1);
$se = get_option('wordtube_options');
if ($se='') {$re='';$wdet='';} else {$re = "MEDIA="; $wdet='Wordtube detected';}

$data = array(  'base_path' => $bp,
		'full_domain_name' => str_replace( "\\", "/",$dn),
		'folder_name' => $pa['path'].'/'.$up.'/pth',
		'default_image' => $pa['path'].'/'.$up.'/default.jpg',
		'use_catname' => 'false',
		'video_regex' => $re,
		'video_default' => $pa['path'].'/'.$up.'/defaultvideo.jpg',
		'stream_check' => 'true',
		'append' => 'false',
		'append_text' => 'thumb_',
		'resize_width' => '60',
		'resize_height' => '60',
		'hs_post' => 'false',
		'hs_wordtube' => 'false',
		'wordtube_width' => '160',
		'wordtube_height' => '120',
		'wordtube_text' => 'wtthumb_',
		'hs_youtube' => 'false',
		'youtube_width' => '160',
		'youtube_height' => '120',
		'hs_post' => 'false',
		'keep_ratio' => 'true',
		'info_update' => 'Create'
		);

function tb_post_thumb_options_subpanel() {
  	if (isset($_POST['info_update']) == 'Update') 
	{
	  	if ($_POST['use_catname'] == 'on') $_POST['use_catname'] = 'true';
	  	else $_POST['use_catname'] = 'false';
	  	if ($_POST['stream_check'] == 'on') $_POST['stream_check'] = 'true';
	  	else $_POST['stream_check'] = 'false';
	  	if ($_POST['append'] == 'on') $_POST['append'] = 'true';
	  	else $_POST['append'] = 'false';
	  	if ($_POST['keep_ratio'] == 'on') $_POST['keep_ratio'] = 'true';
	  	else $_POST['keep_ratio'] = 'false';
	  	if ($_POST['hs_post'] == 'on') $_POST['hs_post'] = 'true';
	  	else $_POST['hs_post'] = 'false';
	  	if ($_POST['hs_wordtube'] == 'on') $_POST['hs_wordtube'] = 'true';
	  	else $_POST['hs_wordtube'] = 'false';
	  	if ($_POST['hs_youtube'] == 'on') $_POST['hs_youtube'] = 'true';
	  	else $_POST['hs_youtube'] = 'false';
	  	if (get_magic_quotes_gpc()) $_POST['video_regex'] = stripslashes($_POST['video_regex']);

	  	$new_options = array( 'default_image' => $_POST['default_image'],
				 	'full_domain_name' => $_POST['full_domain_name'],
					'base_path' => $_POST['base_path'],
					'folder_name' => $_POST['folder_name'],
					'append' => $_POST['append'],
					'append_text' => $_POST['append_text'],
					'resize_width' => $_POST['width'],
					'resize_height' => $_POST['height'],
					'keep_ratio' => $_POST['keep_ratio'],
					'hs_post' => $_POST['hs_post'],
					'hs_wordtube' => $_POST['hs_wordtube'],
					'wordtube_width' => $_POST['wordtube_width'],
					'wordtube_height' => $_POST['wordtube_height'],
					'wordtube_text' => $_POST['wordtube_text'],
					'hs_youtube' => $_POST['hs_youtube'],
					'youtube_width' => $_POST['youtube_width'],
					'youtube_height' => $_POST['youtube_height'],
					'use_catname' => $_POST['use_catname'],
					'stream_check' => $_POST['stream_check'],
					'video_regex' => $_POST['video_regex'],
					'video_default' => $_POST['video_default']
					);

/*******************************************************************************/
/* Validates options
/*******************************************************************************/
		$update_error = pt_validate_options($new_options);

		update_option('post_thumbnail_settings',$new_options);
	    	?><div class="updated">
	    		<?php if (!empty($update_error)) : ?>
	    			<strong><?php _e('Update error:', 'post-thumb'); ?></strong> <?php echo $update_error; ?>
	    		<?php else : ?>
	    			<strong><?php _e('Settings saved', 'post-thumb'); ?></strong>
	    		<?php endif; ?>
	    	</div><?php
	}
	$post_thumbnail_settings = get_option('post_thumbnail_settings');
	?>
   <div class=wrap>
      <form method="post">
		<h2><?php _e('Post Thumbnail Options', 'post-thumb'); ?></h2>
		<fieldset name="options">
			<table cellpadding="3" cellspacing="0" width="100%">
				<tr>
					<td colspan="2" bgcolor="#dddddd">
						<strong><?php _e('Location settings', 'post-thumb') ?></strong>
					</td>
				</tr>
				<tr>
					<td colspan="2">
				</tr>
				<tr>
					<td><strong><?php _e('Base path', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="base_path" value="<?php echo $post_thumbnail_settings['base_path']; ?>" size="60" />
				</tr>
				<tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<?php _e('Absolute path to website. For example, /httpdocs or /yourdomain.com. Used to find location of thumbnails on server. http://yourdomain.com/images/pth/thumb_picture.jpg would actually be /httpdocs/images/pth/thumb_picture.jpg.', 'post-thumb'); ?>
                                        <p></p></td>
				</tr>
				<tr>
					<td><strong><?php _e('Full domain name', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="full_domain_name" value="<?php echo $post_thumbnail_settings['full_domain_name']; ?>" size="60" /></td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<?php _e('Full domain name. Includes the http://.', 'post-thumb'); ?>
                                        <p></p></td>
				</tr>
				<tr>
					<td><strong><?php _e('Folder name', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="folder_name" value="<?php echo $post_thumbnail_settings['folder_name']; ?>" size="60" /></td>
				</tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<?php _e('Set the relative path to thumbs. Make sure directory exists and is writable.', 'post-thumb'); ?>
                                        <p></p></td>
				</tr>
				<tr>
					<td><strong><?php _e('Default image', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="default_image" value="<?php echo $post_thumbnail_settings['default_image']; ?>" size="60" /></td>
				</tr>
				<tr>
				<tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<?php _e('The location of the default image to use if no picture can be found. Enter in the relative url, eg. images/default.jpg', 'post-thumb'); ?>
					<br />
					<?php _e('If category names are used, this will override Default Image and Default Image for Videos', 'post-thumb'); ?>
                                        <p></p></td>
				</tr>
				<tr>
					<td><strong><?php _e('Use Category Names?', 'post-thumb'); ?></strong></td>
					<td><input type="checkbox" name="use_catname" <?php if ($post_thumbnail_settings['use_catname'] == 'true') echo 'checked'; ?> /></td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#dddddd">
						<strong><?php _e('Video image settings', 'post-thumb'); ?></strong>
					</td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<p><?php _e('If you want to scan a post for a video and use a default image. Uses regex to scan for video.'); ?></p>
					</td>
				</tr>
				<tr>
					<td><strong><?php _e('Video regex:', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="video_regex" value="<?php echo htmlentities($post_thumbnail_settings['video_regex']); ?>" size="60" /></td>
				</tr>
				<tr>
					<td><strong><?php _e('Video default image:', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="video_default" value="<?php echo $post_thumbnail_settings['video_default']; ?>" size="60" />
                                        <p></p><p></p></td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#dddddd">
						<strong><?php _e('Stream Video image settings', 'post-thumb'); ?></strong>
					</td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<p><?php _e('If you want to scan a post for a stream video. Supports Youtube, Gvideo and Dailymotion. Will display a thumb for each specific source.', 'post-thumb'); ?></p>
					</td>
				</tr>
				<tr>
					<td><strong><?php _e('Stream Check:', 'post-thumb'); ?></strong></td>
					<td><input type="checkbox" name="stream_check" <?php if ($post_thumbnail_settings['stream_check'] == 'true') echo 'checked'; ?> />
                                        <p></p><p></p></td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#dddddd">
						<strong><?php _e('Filename settings', 'post-thumb'); ?></strong>
					</td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<p><?php _e('Choose to put text before image name or after. Unchecking will put text before.', 'post-thumb'); ?></p>
					<p><?php _e('Choose text to append or prepend image with. Example: pthumb.yourimage.jpg', 'post-thumb'); ?></p>
					</td>
				</tr>
				<tr>
					<td><strong><?php _e('Append', 'post-thumb'); ?></strong></td>
					<td><input type="checkbox" name="append" <?php if ($post_thumbnail_settings['append'] == 'true') echo 'checked'; ?> /></td>
				</tr>
				<tr>
					<td><strong><?php _e('Append / Prepend text', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="append_text" value="<?php echo $post_thumbnail_settings['append_text']; ?>" size="60" /></td>
                                        <p></p><p></p></td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#dddddd">
						<strong><?php _e('Image settings', 'post-thumb'); ?></strong>
					</td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#f6f6f6">
					<p><?php _e('Choose your resize width and height. Will resize in proportion to original width and height. If you do not care about proportions, uncheck keep ratio.', 'post-thumb'); ?></p>
					</td>
				</tr>
				<tr>
					<td><strong><?php _e('Resize width', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="width" value="<?php echo $post_thumbnail_settings['resize_width']; ?>" size="10" /></td>
				</tr>
				<tr>
					<td><strong><?php _e('Resize height', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="height" value="<?php echo $post_thumbnail_settings['resize_height']; ?>" size="10" /></td>
				</tr>
				<tr>
					<td><strong><?php _e('Keep ratio?', 'post-thumb'); ?></strong></td>
					<td><input type="checkbox" name="keep_ratio" <?php if ($post_thumbnail_settings['keep_ratio'] == 'true') echo 'checked'; ?> />
                                        <p></p><p></p></td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#dddddd">
						<strong><?php _e('Highslide settings', 'post-thumb'); ?></strong>
					</td>
				</tr>
				<tr>
					<td><p></p><p></p><strong><?php _e('Use Highslide in posts?', 'post-thumb'); ?></strong>
                                        </td>
					<td><input type="checkbox" name="hs_post" <?php if ($post_thumbnail_settings['hs_post'] == 'true') echo 'checked'; ?> />
					</td>
				</tr>
				<tr>
					<td><strong><?php _e('Detect wordTube Media?', 'post-thumb'); ?></strong>
                                        </td>
					<td><input type="checkbox" name="hs_wordtube" <?php if ($post_thumbnail_settings['hs_wordtube'] == 'true') echo 'checked'; ?> />
                                        </td>
				</tr>
				<tr>
					<td>
                                        <strong><?php _e('wordTube width', 'post-thumb'); ?></strong>
					<input type="text" name="wordtube_width" value="<?php echo $post_thumbnail_settings['wordtube_width']; ?>" size="10" />
                                        </td>
					<td>
                                        <strong><?php _e('wordTube height', 'post-thumb'); ?></strong>
					<input type="text" name="wordtube_height" value="<?php echo $post_thumbnail_settings['wordtube_height']; ?>" size="10" />
                                        </td>
				</tr>
				<tr>
					<td><strong><?php _e('wordTube text', 'post-thumb'); ?></strong></td>
					<td><input type="text" name="wordtube_text" value="<?php echo $post_thumbnail_settings['wordtube_text']; ?>" size="60" /></td>
                                        <p></p><p></p></td>
				</tr>
				<tr>
					<td><strong><?php _e('Detect Youtube video?', 'post-thumb'); ?></strong>
                                        </td>
					<td><input type="checkbox" name="hs_youtube" <?php if ($post_thumbnail_settings['hs_youtube'] == 'true') echo 'checked'; ?> />
					</td>
					<td>
				</tr>
				<tr>
					<td>
                                        <strong><?php _e('Youtube width', 'post-thumb'); ?></strong>
					<input type="text" name="youtube_width" value="<?php echo $post_thumbnail_settings['youtube_width']; ?>" size="10" />
                                        </td>
					<td>
                                        <strong><?php _e('Youtube height', 'post-thumb'); ?></strong>
					<input type="text" name="youtube_height" value="<?php echo $post_thumbnail_settings['youtube_height']; ?>" size="10" />
                                        </td>
				</tr>
			</table>
		</fieldset>
         <div class="submit">
         <input type="submit" name="info_update" value="<?php _e('Update', 'post-thumb'); ?>" /></div>
      </form>
   </div><?php
}

?>