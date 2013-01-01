<?php
/*
Plugin Name: Hostiran SMS Plugin
Plugin URI: http://www.hostiran.net/sms/plugins
Description: Send SMS Via Wordoress.
Version: 1.0
Author: Mostafa Soufi
Author URI: URI: http://iran98.org/
License: GPL2
*/
	define('WP_SMS_VERSION', '1.0');
	define('WP_SMS_DIRNAME', 'ir-sms');

	load_plugin_textdomain('wp-sms', 'wp-content/plugins/'.WP_SMS_DIRNAME.'/langs');

	global $wp_sms_db_version, $wpdb;
	$wp_sms_db_version = "1.0";

	function wp_sms_page() {

		if (function_exists('add_options_page')) {

			add_menu_page(__('Wordpres SMS Plugin', 'wp-sms'), __('Wordpres SMS Plugin', 'wp-sms'), 'manage_options', __FILE__, 'wp_sms_setting_page', plugin_dir_url( __FILE__ ).'/images/sms.png');
			add_submenu_page(__FILE__, __('SMS Setting', 'wp-sms'), __('SMS Setting', 'wp-sms'), 'manage_options', __FILE__, 'wp_sms_setting_page');
			add_submenu_page(__FILE__, __('Send SMS', 'wp-sms'), __('Send SMS', 'wp-sms'), 'manage_options', WP_SMS_DIRNAME.'/send', 'wp_send_sms_page');
			add_submenu_page(__FILE__, __('Archives SMS', 'wp-sms'), __('Archives SMS', 'wp-sms'), 'manage_options', WP_SMS_DIRNAME.'/archives', 'wp_archives_sms_page');
			add_submenu_page(__FILE__, __('Members Newsletter', 'wp-sms'), __('Members Newsletter', 'wp-sms'), 'manage_options', WP_SMS_DIRNAME.'/subscribe', 'wp_subscribes_page');
			add_submenu_page(__FILE__, __('About Plugin', 'wp-sms'), __('About Plugin', 'wp-sms'), 'manage_options', WP_SMS_DIRNAME.'/about', 'wp_about_setting_page');

		}

	}
	add_action('admin_menu', 'wp_sms_page');

	include_once("inc/hostiran.class.php");

	$obj = new hostiran();
	
	$obj->user = get_option('wp_username');
	$obj->pass = get_option('wp_password');
	$obj->from = get_option('wp_number');
	

	if($obj->unitrial == true)
	{
		$obj->unit = __('Rial', 'wp-sms');
	} else {
		$obj->unit = __('SMS', 'wp-sms');
	}

	function wp_subscribes() {

		include_once("newsletter/form.php");

	}
	add_shortcode('subscribe', 'wp_subscribes');

	function wp_sms_menu() {

		global $wp_admin_bar;
		$get_last_credit = get_option('wp_last_credit');

		if(is_super_admin() || is_admin_bar_showing()) {

			if($get_last_credit) {

				global $obj;
				$wp_admin_bar->add_menu(array
					(
						'id'		=>	'wp-credit-sms',
						'title'		=>	 sprintf(__('Your Credit: %s %s', 'wp-sms'), number_format($get_last_credit), $obj->unit),
						'href'		=>	get_bloginfo('url').'/wp-admin/admin.php?page='.WP_SMS_DIRNAME.'/'.WP_SMS_DIRNAME.'.php'
					));
			}
			$wp_admin_bar->add_menu(array
				(
					'id'		=>	'wp-send-sms',
					'parent'	=>	'new-content',
					'title'		=>	__('SMS', 'wp-sms'),
					'href'		=>	get_bloginfo('url').'/wp-admin/admin.php?page='.WP_SMS_DIRNAME.'/send'
				));
		} else {
			return false;
		}
	}
	add_action('admin_bar_menu', 'wp_sms_menu');

	function wp_sms_rightnow_discussion() {
		global $obj;
		echo "<tr><td class='b'><a href='".get_bloginfo('url')."/wp-admin/admin.php?page=".WP_SMS_DIRNAME."/".WP_SMS_DIRNAME.".php'>".number_format(get_option('wp_last_credit'))."</a></td><td><a href='".get_bloginfo('url')."/wp-admin/admin.php?page=".WP_SMS_DIRNAME."/".WP_SMS_DIRNAME.".php'>".$obj->unit."</a></td></tr>";
	}
	add_action('right_now_discussion_table_end', 'wp_sms_rightnow_discussion');

	function wp_sms_rightnow_content() {
		global $wpdb, $table_prefix;
		$users = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}sms_subscribes");
		echo "<tr><td class='b'><a href='".get_bloginfo('url')."/wp-admin/admin.php?page=".WP_SMS_DIRNAME."/subscribe'>".$users."</a></td><td><a href='".get_bloginfo('url')."/wp-admin/admin.php?page=".WP_SMS_DIRNAME."/subscribe'>".__('Common', 'wp-sms')."</a></td></tr>";
	}
	add_action('right_now_content_table_end', 'wp_sms_rightnow_content');

	function wp_sms_enable() {

		$get_bloginfo_url = get_admin_url() . "admin.php?page=wp-sms/wp-sms.php";
		echo '<div class="error"><p><img src="'.plugin_dir_url(__FILE__).'/images/exclamation.png" alt="Bottom" align="top"/> '.sprintf(__('Please check the <a href="%s">SMS credit</a> the settings', 'wp-sms'), $get_bloginfo_url).'</p></div>';

	}

	if(!get_option('wp_username') || !get_option('wp_password')) {

		add_action('admin_notices', 'wp_sms_enable');

	}

	function wp_sms_install() {

		global $wp_sms_db_version, $table_prefix, $wpdb;
		
		$archives_table		= $table_prefix . "sms_archives";
		$subscribes_table	= $table_prefix . "sms_subscribes";

		$create_archives_table = ("CREATE TABLE {$archives_table}(
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`date` datetime NOT NULL,
			`message` text CHARACTER SET utf8 NOT NULL,
			`to` text CHARACTER SET utf8 NOT NULL,
			PRIMARY KEY (`ID`))
			DEFAULT CHARSET=utf8");
		
		$create_subscribes_table = ("CREATE TABLE {$subscribes_table}(
			`ID` int(10) NOT NULL AUTO_INCREMENT,
			`date` datetime DEFAULT NULL,
			`name` varchar(20) DEFAULT NULL,
			`mobile` varchar(20) NOT NULL,
			`status` tinyint(1) DEFAULT NULL,
			`activate_key` int(11) DEFAULT NULL,
			PRIMARY KEY (`ID`))
			DEFAULT CHARSET=utf8");

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		dbDelta($create_archives_table);
		dbDelta($create_subscribes_table);
		
		add_option('wp_sms_db_version', 'wp_sms_db_version');

	}
	register_activation_hook(__FILE__, 'wp_sms_install');

	function wp_sms_widget() {

		wp_register_sidebar_widget('wp_sms', __('Subscribe to SMS', 'wp-sms'), 'wp_subscribe_show_widget', array('description'	=>	__('Subscribe to SMS', 'wp-sms')));
		wp_register_widget_control('wp_sms', __('Subscribe to SMS', 'wp-sms'), 'wp_subscribe_control_widget');

	}
	add_action('plugins_loaded', 'wp_sms_widget');

	function wp_subscribe_show_widget($args) {

		extract($args);
			echo $before_title . get_option('wp_sms_widget_name') . $after_title;
			include("newsletter/form.php");

	}

	function wp_subscribe_control_widget() {

		if($_POST['wp_sms_submit_widget']) {
			update_option('wp_sms_widget_name', $_POST['wp_sms_widget_name']);
		}

		include_once('widget.php');

	}

	function wp_subscribe_meta_box() {

		add_meta_box('subscribe-meta-box', __('Subscribe SMS', 'wp-sms'), 'wp_subscribe_post', 'post', 'normal', 'high');

	}

	if(get_option('wp_subscribes_send')) {

		add_action('add_meta_boxes', 'wp_subscribe_meta_box');

	}

	function wp_subscribe_post($post) {

		$values = get_post_custom($post->ID);
		$selected = isset( $values['subscribe_post'] ) ? esc_attr( $values['subscribe_post'][0] ) : '';
		wp_nonce_field('subscribe_box_nonce', 'meta_box_nonce');

		include_once('setting/meta-box.php');
	}

	function wp_subscribe_post_save($post_id) {

		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if(!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'subscribe_box_nonce')) return;
		if(!current_user_can('edit_post')) return;

		if( isset( $_POST['subscribe_post'] ) )
		update_post_meta($post_id, 'subscribe_post', esc_attr($_POST['subscribe_post']));

	}
	add_action('save_post', 'wp_subscribe_post_save');

	function wp_subscribe_send($post_ID) {

		if( !strstr($_POST['_wp_http_referer'], "action=edit")) {
			if(get_post_meta($post_ID, "subscribe_post", true) == 'yes') {
				global $wpdb, $table_prefix, $obj;
				$obj->to = $wpdb->get_col("SELECT mobile FROM {$table_prefix}sms_subscribes");
				$obj->msg = get_the_title($post_ID);

				$obj->send_sms();
				return $post_ID;
			}
		}

	}
	add_action('publish_post', 'wp_subscribe_send');

	function wp_tell_a_freind_head() {

		include_once('tell-a-freind.php');

	}

	function wp_tell_a_freind($content) {

		if(is_single())
		{
			global $obj;
			echo '<span id="send_friend">'.__('Suggested by SMS', 'wp-sms').'</span>';
			echo '
			<form action="" method="post" id="tell_friend_form">
				<table width="100%">
					<tr>
						<td><label for="get_name">'.__('Your name', 'wp-sms').':</label></td>
						<td><label for="get_fname">'.__('Your friend name', 'wp-sms').':</label></td>
						<td><label for="get_fmobile">'.__('Your friend mobile', 'wp-sms').':</label></td>
						<td></td>
					</tr>

					<tr>
						<td><input type="text" name="get_name" id="get_name"/></td>
						<td><input type="text" name="get_fname" id="get_fname"/></td>
						<td><input type="text" name="get_fmobile" id="get_fmobile" value="09"/></td>
						<td><input type="submit" name="send_post" value="'.__('Send', 'wp-sms').'"/></td>
					</tr>
				</table>
			</form>';

			if($_POST['send_post']) {
				$mobile = $_POST['get_fmobile'];
				if($_POST['get_name'] && $_POST['get_fname'] && $_POST['get_fmobile']) {
					if( (strlen($mobile) >= 11) && (substr($mobile, 0, 2) == '09') && (preg_match("([a-zA-Z])", $mobile) == 0) ) {
						$obj->to = array($_POST['get_fmobile']);
						$obj->msg = sprintf(__('Hi %s, the %s post suggested to you by %s. url: %s', 'wp-sms'), $_POST['get_fname'], get_the_title(), $_POST['get_name'], wp_get_shortlink());
						if($obj->send_sms()) {
							_e('SMS was sent with success', 'wp-sms');
						}
					} else {
						_e('Please enter a valid mobile number', 'wp-sms');
					}
				} else {
					_e('Please complete all fields', 'wp-sms');
				}
			}
		}
		return $content;

	}

	if(get_option('wp_suggestion_status')) {
		add_action('wp_head', 'wp_tell_a_freind_head');
		add_action('the_content', 'wp_tell_a_freind');
	}

	if(get_option('wp_notification_new_wp_version')) {

		$update = get_site_transient('update_core');
		$update = $update->updates;
		
		if($update[1]->current > $wp_version) {

			if(get_option('wp_last_send_notification') == false) {
				
				$obj->to = array(get_option('wp_admin_mobile'));
				$obj->msg = sprintf(__('WordPress %s is available! Please update now', 'wp-sms'), $update[1]->current);

				$obj->send_sms();

				update_option('wp_last_send_notification', true);

			}
		} else {
			update_option('wp_last_send_notification', false);
		}
	}

	function wp_sms_setting_page() {
		global $obj;

		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));

			settings_fields('wp_sms_options');
		}

		include_once('setting/setting.php');
	}
	
	function wp_send_sms_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $obj, $wpdb, $table_prefix;
		
		if(isset($_POST['send_sms'])) {
		
			if($_POST['wp_get_message']) {
			
				if($_POST['wp_send_to'] == "wp_subscribe_user") {
					$obj->to = $wpdb->get_col("SELECT mobile FROM {$table_prefix}sms_subscribes");
				}
				else if($_POST['wp_send_to'] == "wp_tellephone") {
					$obj->to = explode(",", $_POST['wp_get_number']);
				}
				
				$obj->msg = $_POST['wp_get_message'];

				if($_POST['wp_flash'] == "true") {
					$obj->isflash = true;
				}
				elseif($_POST['wp_flash'] == "false") {
					$obj->isflash = false;
				}

				if($obj->send_sms()) {
					echo "<div class='updated'><p>" . __('SMS was sent with success', 'wp-sms') . "</p></div>";
					update_option('wp_last_credit', $obj->get_credit());
					
					$wpdb->insert($table_prefix.'sms_archives', array(
						'date'		=>	date('Y-m-d H:i:s' ,current_time('timestamp',0)),
						'message'	=>	$_POST['wp_get_message'],
						'to'		=>	implode(', ', $obj->to)
					));
				}
				
			} else {
				echo "<div class='error'><p>" . __('Please enter a message', 'wp-sms') . "</p></div>";
			}
		}
		
		include_once('setting/send-sms.php');
	}
	
	function wp_archives_sms_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		global $wpdb, $table_prefix;

		if($_POST['doaction']) {

			$get_IDs = implode(",", $_POST['column_ID']);
			$check_ID = $wpdb->query("SELECT * FROM {$table_prefix}sms_archives WHERE ID='".$get_IDs."'");

			switch($_POST['action']) {
				case 'trash':
					if($check_ID) {
						$wpdb->query("DELETE FROM {$table_prefix}sms_archives WHERE ID IN (".$get_IDs.")");
						echo "<div class='updated'><p>" . __('With success was removed', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;
			}
		}

		include_once('setting/archives.php');
	}

	function wp_subscribes_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		global $wpdb, $table_prefix;

		if($_POST['doaction']) {

			$get_IDs = implode(",", $_POST['column_ID']);
			$check_ID = $wpdb->query("SELECT * FROM {$table_prefix}sms_subscribes WHERE ID='".$get_IDs."'");

			switch($_POST['action']) {
				case 'trash':
					if($check_ID) {
						$wpdb->query("DELETE FROM {$table_prefix}sms_subscribes WHERE ID IN (".$get_IDs.")");
						echo "<div class='updated'><p>" . __('With success was removed', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;

				case 'active':
					if($check_ID) {
						$wpdb->query("UPDATE {$table_prefix}sms_subscribes SET `status` = '1' WHERE ID IN (".$get_IDs.")");
						echo "<div class='updated'><p>" . __('User actived.', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;

				case 'deactive':
					if($check_ID) {
						$wpdb->query("UPDATE {$table_prefix}sms_subscribes SET `status` = '0' WHERE ID IN (".$get_IDs.")");
						echo "<div class='updated'><p>" . __('User deactived.', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;
			}

		}

		$name = trim($_POST['wp_subscribe_name']);
		$mobile = trim($_POST['wp_subscribe_mobile']);
		$date = date('Y-m-d H:i:s' ,current_time('timestamp',0));

		if(isset($_POST['wp_add_subscribe'])) {

			if($name && $mobile) {
				if( (strlen($mobile) >= 11) && (substr($mobile, 0, 2) == '09') && (preg_match("([a-zA-Z])", $mobile) == 0) ) {

					$check_mobile = $wpdb->query("SELECT * FROM {$table_prefix}sms_subscribes WHERE mobile='".$mobile."'");

					if(!$check_mobile) {
						$check = $wpdb->query("INSERT INTO {$table_prefix}sms_subscribes (date, name, mobile, status) VALUES ('".$date."', '".$name."', '".$mobile."', '1')");

						if($check) {
							echo "<div class='updated'><p>" . sprintf(__('User <strong>%s</strong> was added successfully.', 'wp-sms'), $name) . "</div></p>";
						}
					} else {
						echo "<div class='error'><p>" . __('Phone number is repeated', 'wp-sms') . "</div></p>";
					}
				} else {
					echo "<div class='error'><p>" . __('Please enter a valid mobile number', 'wp-sms') . "</div></p>";
				}
			} else {
				echo "<div class='error'><p>" . __('Please complete all fields', 'wp-sms') . "</div></p>";
			}

		}

		if(isset($_POST['wp_edit_subscribe'])) {

			if($name && $mobile) {
				if( (strlen($mobile) >= 11) && (substr($mobile, 0, 2) == '09') && (preg_match("([a-zA-Z])", $mobile) == 0) ) {

					$check = $wpdb->query("UPDATE {$table_prefix}sms_subscribes SET `name` = '".$name."', `mobile` = '".$mobile."', `status` = '".$_POST['wp_subscribe_status']."' WHERE `ID` = '".$_GET['ID']."'");

					if($check) {
						echo "<div class='updated'><p>" . sprintf(__('User <strong>%s</strong> was update successfully.', 'wp-sms'), $name) . "</div></p>";
					}

				} else {
					echo "<div class='error'><p>" . __('Please enter a valid mobile number', 'wp-sms') . "</div></p>";
				}
			} else {
				echo "<div class='error'><p>" . __('Please complete all fields', 'wp-sms') . "</div></p>";
			}

		}

		include_once('setting/subscribes.php');
	}

	function wp_about_setting_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		include_once('setting/about.php');
	}
?>