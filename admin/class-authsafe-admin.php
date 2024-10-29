<?php

/**
 * The admin-specific functionality of the AuthSafe plugin.
 *
 * @link       https://authsafe.ai
 * @since      2.0.0
 *
 * @package    Authsafe
 * @subpackage Authsafe/admin
 * @author     Jinendra Khobare <jinendra@securelayer7.net>
 */
class Authsafe_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $authsafe    The ID of this plugin.
	 */
	private $authsafe;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $authsafe       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $authsafe, $version ) {

		$this->authsafe = $authsafe;          // authsafe
		$this->version = $version;            // 2.0.0

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		/**
		 * Include admin-specific CSS files.
		 * 
		 */

		$s = wp_enqueue_style( $this->authsafe, plugin_dir_url( __FILE__ ) . 'css/authsafe-admin.css', array(), $this->version, 'all' );	

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * Include admin-specific Javascript files.
		 * 
		 */

		wp_enqueue_script( $this->authsafe.'-admin', plugin_dir_url( __FILE__ ) . 'js/authsafe-admin.js', array(), "1.0.10", true );
		
		extract($this->ats_settings_options());

		if ( (isset($ats_property_id) && !empty($ats_property_id)) &&
			 (isset($ats_property_secret) && !empty($ats_property_secret))
		) {
			wp_enqueue_script( $this->authsafe, PIXEL_URL.$ats_property_id, array(), '2.0.0', false );
			if(is_user_logged_in()) {
				wp_add_inline_script( $this->authsafe, '_authsafe("userInit", "'.esc_attr(get_current_user_id()).'");', 'after' );
			}
		}

	}
		
	public function add_menu() {
		
		$menu_slug = 'authsafe';
		//$menu_slug = 'ats-property-config';
		$title_page = esc_html__('AuthSafe Setting', $menu_slug);
		$title_menu = esc_html__('AuthSafe Setting', $menu_slug);

		add_menu_page(	  $title_page, $title_menu, 'manage_options', $menu_slug, array($this, 'display_settings'), $icon_url = '', 10 );
		
		$title_page = esc_html__('AuthSafe Policies', $menu_slug);
		$title_menu = esc_html__('AuthSafe Policies', $menu_slug);

		add_submenu_page( $menu_slug, $title_page, $title_menu, 'manage_options', 'authsafe-policies', array($this, 'policies_settings') );
		
		#add_options_page($title_page, $title_menu, 'manage_options', $menu_slug, array($this, 'display_settings') );
		
	}

	public function display_settings()
	{
			
		$ats_options = get_option('ats_options', Authsafe::default_options());
		
		require_once AUTHSAFE_DIR .'admin/partials/authsafe-admin-display.php';

	}

	public function policies_settings()
	{
			
		$ats_policy_options = get_option('ats_policy_options', Authsafe::default_policy_options());
		
		require_once AUTHSAFE_DIR .'admin/partials/authsafe-admin-policies.php';
		
	}
		
	public function add_settings() {
		
		register_setting('ats_plugin_options', 'ats_options', array($this, 'validate_settings'));
		register_setting('ats_plugin_policy_options', 'ats_policy_options', array($this, 'validate_policy_settings'));

	}
		
	public function validate_settings($input) {

		$input['ats_property_id'] = wp_filter_nohtml_kses($input['ats_property_id']);
		
		if (isset($input['ats_property_id']) && (!is_numeric($input['ats_property_id']) || (strlen($input['ats_property_id']) != 16) ) ) {
			
			$input['ats_property_id'] = '';
			
			$message  = esc_html__('Error: Property ID is invalid', 'authsafe');
			
			add_settings_error('ats_property_id', 'invalid-property-id', $message, 'error');
			
		}
		
		if (isset($input['ats_property_secret']) && (empty($input['ats_property_secret'])) ) {
			
			$input['ats_property_secret'] = '';
			
			$message  = esc_html__('Error: Property Secret is invalid', 'authsafe');
			
			add_settings_error('ats_property_secret', 'invalid-property-secret', $message, 'error');
			
		}
		
		return $input;
		
	}
	
	public function validate_policy_settings($input) {

		$input['ats_medium_email'] = wp_filter_nohtml_kses($input['ats_medium_email']);
		$input['ats_challenge_email'] = wp_filter_nohtml_kses($input['ats_challenge_email']);
		$input['ats_deny_email'] = wp_filter_nohtml_kses($input['ats_deny_email']);
		$input['ats_allow_payment_email'] = wp_filter_nohtml_kses($input['ats_allow_payment_email']);
		$input['ats_challenge_payment_email'] = wp_filter_nohtml_kses($input['ats_challenge_payment_email']);
		$input['ats_deny_payment_email'] = wp_filter_nohtml_kses($input['ats_deny_payment_email']);
		
		return $input;
		
	}
		
	public function action_links($links, $file) {

		if ($file === AUTHSAFE_FILE && current_user_can('manage_options')) {
			
			$settings = '<a href="'. admin_url(AUTHSAFE_PATH) .'">'. esc_html__('Settings', 'authsafe') .'</a>';
			
			array_unshift($links, $settings);
			
		}
		
		return $links;
		
	}

	function add_dashboard_notification() 
	{
	
		$options = get_option('ats_options', Authsafe::default_options());
			
		$p_id = $options['ats_property_id'];
		$s_key = $options['ats_property_secret'];
		$current_time = date('Y-m-d H:i:s');

		// 24 Hour Notification Sleep if Property & Secret Key Is Not Configure 
		if (!isset($_COOKIE['notification_closed']))
		{
			
			if(!$p_id || !$s_key)
			{
				echo '<div style="background-color: #DC143C; color: white;" class="notice notice-info is-dismissible" data-notification-value="authsafe_sleep">';
				echo '<p><big><b>Authsafe</b></big> - Please configure your Property ID and Secret Key.<a style="color: blue;" href="https://ciso.sh/wp-admin/admin.php?page=authsafe" class="alert-link"> Click to Configure it</a></p>';
				echo '</div>';

				?>
					<script>
						jQuery(document).ready(function($) {
							$(document).on('click', '.notice.is-dismissible .notice-dismiss', function() {
									const notification = $(this).closest('.notice');
									const notificationValue = notification.data('notification-value');

									function formatDateToPHPFormat(date) {
									const year = date.getFullYear();
									const month = (date.getMonth() + 1).toString().padStart(2, '0');
									const day = date.getDate().toString().padStart(2, '0');
									const hours = date.getHours().toString().padStart(2, '0');
									const minutes = date.getMinutes().toString().padStart(2, '0');
									const seconds = date.getSeconds().toString().padStart(2, '0');

									return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
								}

								const now = new Date();
								const formattedDate = formatDateToPHPFormat(now);
								
								const cookieName = 'notification_closed';
								const cookieValue = formattedDate;
								const expiration = new Date();
								expiration.setTime(expiration.getTime() + 24 * 60 * 60 * 1000); // Add 5 hours in milliseconds

								document.cookie = cookieName + '=' + cookieValue + '; expires=' + expiration.toUTCString() + '; path=/';
							});
						});
					</script>
				<?php
			}
		}
		else
		{
			
			$timestamp1 = $current_time;
			$timestamp2 = $last_notification;

			// Convert the timestamps to DateTime objects
			$datetime1 = new DateTime($timestamp1);
			$datetime2 = new DateTime($timestamp2);

			// Calculate the difference between the two DateTime objects
			$interval = $datetime1->diff($datetime2);

			if ($interval->h >= 24 || $interval->d > 0) 
			{
				if(!$p_id || !$s_key)
			    {
					echo '<div style="background-color: #DC143C; color: white;" class="notice notice-info is-dismissible" data-notification-value="authsafe_sleep">';
					echo '<p><big><b>Authsafe</b></big> - Please configure your Property ID and Secret Key.<a style="color: blue;" href="https://ciso.sh/wp-admin/admin.php?page=authsafe" class="alert-link"> Click to Configure it</a></p>';
					echo '</div>';

					?>
						<script>
							jQuery(document).ready(function($) {
								$(document).on('click', '.notice.is-dismissible .notice-dismiss', function() {
									const notification = $(this).closest('.notice');
									const notificationValue = notification.data('notification-value');

									function formatDateToPHPFormat(date) {
										const year = date.getFullYear();
										const month = (date.getMonth() + 1).toString().padStart(2, '0');
										const day = date.getDate().toString().padStart(2, '0');
										const hours = date.getHours().toString().padStart(2, '0');
										const minutes = date.getMinutes().toString().padStart(2, '0');
										const seconds = date.getSeconds().toString().padStart(2, '0');

										return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
									}

									const now = new Date();
									const formattedDate = formatDateToPHPFormat(now);
									
									const cookieName = 'notification_closed';
									const cookieValue = formattedDate;
									const expiration = new Date();
									expiration.setTime(expiration.getTime() + 24 * 60 * 60 * 1000); // Add 24 hours in milliseconds

									document.cookie = cookieName + '=' + cookieValue + '; expires=' + expiration.toUTCString() + '; path=/';
								});
						    });
						</script>
					<?php
			   }
			}						
		}
	}
		
	public function select_menu($items, $menu) {
		
		$options = get_option('ats_options', Authsafe::default_options());
		
		$checked = '';
		
		$output = '';
		
		$class = '';
		
		foreach ($items as $item) {
			
			$key = isset($options[$menu]) ? $options[$menu] : '';
			
			$value = isset($item['value']) ? $item['value'] : '';
			
			$checked = ($value == $key) ? ' checked="checked"' : '';
			
			$output .= '<div class="ats-radio-inputs'. esc_attr($class) .'">';
			$output .= '<label>';
			$output .= '<input type="radio" name="ats_options['. esc_attr($menu) .']" value="'. esc_attr($item['value']) .'"'. $checked .'> ';
			$output .= '<span>'. $item['label'] .'</span>'; //
			$output .= '</label>';
			$output .= '</div>';
			
		}
		
		return $output;
		
	}

	public function wp_login_track($username, WP_User $user)
	{
		          
		$device_id = '';
		if (isset($_POST['device_id'])) {
			$device_id = sanitize_text_field($_POST["device_id"]);
		}
		
		$options = get_option('ats_options', Authsafe::default_options());
				
		$ats_policy_options = get_option('ats_policy_options', Authsafe::default_options());
        
		require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
		$obj = new AuthSafe\AuthSafe([
			'property_id' => $options['ats_property_id'],
			'property_secret' => $options['ats_property_secret']
		]);
       
		$res = $obj->loginAttempt('login_succeeded',$user->data->ID,$device_id,array('email'=>$user->data->user_email,'username'=>$username));

		if($res)
		{
			
			if(!isset($ats_policy_options['ats_shadow_mode'])) 
			{
		
				if(gettype($res) == "string") {
					/*var_dump($res);
					die();*/
				} else 
				{
				
					$status = $res["status"];
					$severity = $res["severity"];
					$site_title = get_bloginfo( 'name' );

					/* The below code is checking the value of the variable ['ats_medium_email']
					and assigning the value to the variable . If the value is truthy,
					is set to true. Otherwise, it is set to false. */

					$checkbox_allow = $ats_policy_options['ats_medium_email'];
					if($checkbox_allow)
					{
						$checkbox_allow = true;
					}
					else
					{
						$checkbox_allow = false;
					}
					$checkbox_deny = $ats_policy_options['ats_deny_email'];
					if($checkbox_deny)
					{
						$checkbox_deny = true;
					}
					else
					{
						$checkbox_deny = false;
					}
					$checkbox_challenge = $ats_policy_options['ats_challenge_email'];
					if($checkbox_challenge)
					{
						$checkbox_challenge = true;
					}
					else
					{
						$checkbox_challenge = false;
					}
					
					if($status == "challenge" && $checkbox_challenge == true) {
                       
						$lost_pass_url = wp_lostpassword_url();

						$device = $res["device"];
						$to = $user->data->user_email;
						
						$subject = 'Suspicious activity detected on this account';
						$body = '<!doctype html>'.
						'<html>'.
						  '<head>'.
							'<meta name="viewport" content="width=device-width">'.
							'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
							'<title>'.$subject.'</title>'.
							'<style>'.
							'@media only screen and (max-width: 620px) {'.
							  'table[class=body] h1 {'.
								'font-size: 28px !important;'.
								'margin-bottom: 10px !important;'.
							  '}'.
							  'table[class=body] p,'.
									'table[class=body] ul,'.
									'table[class=body] ol,'.
									'table[class=body] td,'.
									'table[class=body] span,'.
									'table[class=body] a {'.
								'font-size: 16px !important;'.
							  '}'.
							  'table[class=body] .wrapper,'.
									'table[class=body] .article {'.
								'padding: 10px !important;'.
							  '}'.
							  'table[class=body] .content {'.
								'padding: 0 !important;'.
							  '}'.
							  'table[class=body] .container {'.
								'padding: 0 !important;'.
								'width: 100% !important;'.
							  '}'.
							  'table[class=body] .main {'.
								'border-left-width: 0 !important;'.
								'border-radius: 0 !important;'.
								'border-right-width: 0 !important;'.
							  '}'.
							  'table[class=body] .btn table {'.
								'width: 100% !important;'.
							  '}'.
							  'table[class=body] .btn a {'.
								'width: 100% !important;'.
							  '}'.
							  'table[class=body] .img-responsive {'.
								'height: auto !important;'.
								'max-width: 100% !important;'.
								'width: auto !important;'.
							  '}'.
							'}'.
							'@media all {'.
							  '.ExternalClass {'.
								'width: 100%;'.
							  '}'.
							  '.ExternalClass,'.
									'.ExternalClass p,'.
									'.ExternalClass span,'.
									'.ExternalClass font,'.
									'.ExternalClass td,'.
									'.ExternalClass div {'.
								'line-height: 100%;'.
							  '}'.
							  '.apple-link a {'.
								'color: inherit !important;'.
								'font-family: inherit !important;'.
								'font-size: inherit !important;'.
								'font-weight: inherit !important;'.
								'line-height: inherit !important;'.
								'text-decoration: none !important;'.
							  '}'.
							  '#MessageViewBody a {'.
								'color: inherit;'.
								'text-decoration: none;'.
								'font-size: inherit;'.
								'font-family: inherit;'.
								'font-weight: inherit;'.
								'line-height: inherit;'.
							  '}'.
							  '.btn-primary table td:hover {'.
								'background-color: #34495e !important;'.
							  '}'.
							  '.btn-primary a:hover {'.
								'background-color: #34495e !important;'.
								'border-color: #34495e !important;'.
							  '}'.
							'}'.
							'</style>'.
						  '</head>'.
						  '<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
							'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
							'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
							  '<tr>'.
								'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
								  '<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
									'<!-- START CENTERED WHITE CONTAINER -->'.
									'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
									  '<!-- START MAIN CONTENT AREA -->'.
									  '<tr>'.
										'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
										  '<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											  '<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Hi <strong>'.$user->data->display_name.'</strong>,</p>'.
											  '</td>'.
											'</tr>'.
											'<tr>'.
											  '<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Suspicious login detected on the below device:</p>'.
											  '</td>'.
											'</tr>'.
											'<tr>'.
											  '<td>'.
												'<table border="0" cellpadding="0" cellspacing="0"style="width: 100%; border-collapse: collapse; Margin-bottom: 15px;">'.
													'<tr>'.
														'<th style="border: 1px solid rgba(0, 0, 0, 1);">Device</th>'.
														'<th style="border: 1px solid rgba(0, 0, 0, 1);">IP Address</th>'.
														'<th style="border: 1px solid rgba(0, 0, 0, 1);">Location</th>'.
													'</tr>'.
													'<tr>'.
														'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['name'].'</td>'.
														'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['ip'].'</td>'.
														'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['location'].'</td>'.
													'</tr>'.
												'</table>'.
											  '</td>'.
											'</tr>'.
											'<tr>'.
											  '<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">We highly recommend to reset your password on this device if it was you. Click below to reset your password:</p>'.
											  '</td>'.
											'</tr>'.
											'<tr>'.
											  '<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"><a href="'.$lost_pass_url.'">'.$lost_pass_url.'</a></p>'.
											  '</td>'.
											'</tr>'.
											'<tr>'.
											  '<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Regards,<br>'.$site_title.' Team</p>'.
											  '</td>'.
											'</tr>'.
											'<tr>'.
											  '<td>'.
												'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
											  '</td>'.
											'</tr>'.
										  '</table>'.
										'</td>'.
									  '</tr>'.
									'<!-- END MAIN CONTENT AREA -->'.
									'</table>'.
									'<!-- START FOOTER -->'.
									'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
									  '<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
										'<tr>'.
										  '<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
											'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
										  '</td>'.
										'</tr>'.
									  '</table>'.
									'</div>'.
									'<!-- END FOOTER -->'.
								  '<!-- END CENTERED WHITE CONTAINER -->'.
								  '</div>'.
								'</td>'.
								'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
							  '</tr>'.
							'</table>'.
						  '</body>'.
						'</html>';
						$headers = array('Content-Type: text/html; charset=UTF-8');

						$mailResult = wp_mail( $to, $subject, $body, $headers );

					} else if($status == "deny" && $checkbox_deny == true) {

						$device = $res["device"];
						$to = $user->data->user_email;
						$subject = 'Urgent: Highly Suspicious Log In Detected on Your Account';
						$body = '<!doctype html>'.
						'<html>'.
						'<head>'.
							'<meta name="viewport" content="width=device-width">'.
							'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
							'<title>'.$subject.'</title>'.
							'<style>'.
							'@media only screen and (max-width: 620px) {'.
							'table[class=body] h1 {'.
								'font-size: 28px !important;'.
								'margin-bottom: 10px !important;'.
							'}'.
							'table[class=body] p,'.
									'table[class=body] ul,'.
									'table[class=body] ol,'.
									'table[class=body] td,'.
									'table[class=body] span,'.
									'table[class=body] a {'.
								'font-size: 16px !important;'.
							'}'.
							'table[class=body] .wrapper,'.
									'table[class=body] .article {'.
								'padding: 10px !important;'.
							'}'.
							'table[class=body] .content {'.
								'padding: 0 !important;'.
							'}'.
							'table[class=body] .container {'.
								'padding: 0 !important;'.
								'width: 100% !important;'.
							'}'.
							'table[class=body] .main {'.
								'border-left-width: 0 !important;'.
								'border-radius: 0 !important;'.
								'border-right-width: 0 !important;'.
							'}'.
							'table[class=body] .btn table {'.
								'width: 100% !important;'.
							'}'.
							'table[class=body] .btn a {'.
								'width: 100% !important;'.
							'}'.
							'table[class=body] .img-responsive {'.
								'height: auto !important;'.
								'max-width: 100% !important;'.
								'width: auto !important;'.
							'}'.
							'}'.
							'@media all {'.
							'.ExternalClass {'.
								'width: 100%;'.
							'}'.
							'.ExternalClass,'.
									'.ExternalClass p,'.
									'.ExternalClass span,'.
									'.ExternalClass font,'.
									'.ExternalClass td,'.
									'.ExternalClass div {'.
								'line-height: 100%;'.
							'}'.
							'.apple-link a {'.
								'color: inherit !important;'.
								'font-family: inherit !important;'.
								'font-size: inherit !important;'.
								'font-weight: inherit !important;'.
								'line-height: inherit !important;'.
								'text-decoration: none !important;'.
							'}'.
							'#MessageViewBody a {'.
								'color: inherit;'.
								'text-decoration: none;'.
								'font-size: inherit;'.
								'font-family: inherit;'.
								'font-weight: inherit;'.
								'line-height: inherit;'.
							'}'.
							'.btn-primary table td:hover {'.
								'background-color: #34495e !important;'.
							'}'.
							'.btn-primary a:hover {'.
								'background-color: #34495e !important;'.
								'border-color: #34495e !important;'.
							'}'.
							'}'.
							'</style>'.
						'</head>'.
						'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
							'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
							'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
							'<tr>'.
								'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
								'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
									'<!-- START CENTERED WHITE CONTAINER -->'.
									'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
									'<!-- START MAIN CONTENT AREA -->'.
									'<tr>'.
										'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
										'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											'<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Dear <strong>'.$username.'</strong>,</p>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">We have blocked a highly suspicious log in from your account. </p>'.
											'</td>'.
											'</tr>'.
											'<tr>'.
											'<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Within the next few minutes, you would receive a follow-up email containing a secure link to initiate the password reset process. Please keep an eye on your inbox to set a new password.</p>'.								
											'</td>'.
								
											'<tr>'.
											'<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Regards,<br>'.$site_title.' Team</p>'.
											'</td>'.
											'</tr>'.
											'<tr>'.
											'<td>'.
												'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
											'</td>'.
											'</tr>'.
										'</table>'.
										'</td>'.
									'</tr>'.
									'<!-- END MAIN CONTENT AREA -->'.
									'</table>'.
									'<!-- START FOOTER -->'.
									'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
									'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
										'<tr>'.
										'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
											'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
										'</td>'.
										'</tr>'.
									'</table>'.
									'</div>'.
									'<!-- END FOOTER -->'.
								'<!-- END CENTERED WHITE CONTAINER -->'.
								'</div>'.
								'</td>'.
								'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
							'</tr>'.
							'</table>'.
						'</body>'.
						'</html>';
						$headers = array('Content-Type: text/html; charset=UTF-8');
						$mailResult = wp_mail( $to, $subject, $body, $headers );
						sleep(5);   // delayed for 5 seconds to send the email by wordpress 

						$results = retrieve_password( $user->data->user_login );

						if ( true === $results ) {
							$message = 'Your account is blocked due to suspicious activities. We have sent a password reset email, please reset your password to activate your account again.';
						} else {
							$message = $results->get_error_message();
						}
						$login_url = site_url( 'wp-login.php?action=as_ch_dn&as_msg='.urlencode($message), 'login' );
						wp_logout();
						wp_safe_redirect( $login_url );
						exit;

					} else if($status == "allow" && $severity == "medium" && $checkbox_allow == true) 
					{

						global $wpdb;
						$table_name = $wpdb->prefix . 'authsafe_sleep'; 
					
						// $current_time = date('Y-m-d H:i:s');
						$current_time = date('Y-m-d H:i:s');

						$user_id = $user->data->ID;
						$exist_one = false;

						// Prepare and execute the SQL query to fetch all data from the table
						$query = "SELECT * FROM $table_name";
						$results = $wpdb->get_results($query, ARRAY_A);

						// Check if there are any results
						if ($results) 
						{
							// Loop through the results and do something with each row
							foreach ($results as $row)
							{
								$cid = $row['cid'];
								if($cid == $user_id)
								{
									$exist_one = true;   // If record found then update query proceed 
									break;
								}
							}
						}

						if($exist_one)
						{
							global $wpdb;
							$table_name = $wpdb->prefix . 'authsafe_sleep';           

							$query = "SELECT * FROM $table_name";
							$results = $wpdb->get_results($query, ARRAY_A);
							$d_time = 23; 
							// Check if there are any results
							if ($results) {
								foreach ($results as $row) {
									if($user_id == $row['cid'])
									{
										$d_time = $row['c_time'];      // Fetching the last timestamp
										break; 
									}
								}
							}
							// Current Timestamp
							$timestamp1 = $current_time;
							// Last Timestamp which store first time in database
							$timestamp2 = $d_time;

							// Convert the timestamps to DateTime objects
							$datetime1 = new DateTime($timestamp1);
							$datetime2 = new DateTime($timestamp2);

							// Calculate the difference between the two DateTime objects
							$interval = $datetime1->diff($datetime2);

							// $totalMinutes = ($interval->h * 60) + $interval->i;

							// Check if the difference is equal to or greater than 24 hours
							if ($interval->h >= 24 || $interval->d > 0) {
							// if($totalMinutes >= 5)
							// {
								$device = $res["device"];
								$to = $user->data->user_email;
								$subject = 'Unusual activity detected on this account';
								$body = '<!doctype html>'.
								'<html>'.
								'<head>'.
									'<meta name="viewport" content="width=device-width">'.
									'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
									'<title>'.$subject.'</title>'.
									'<style>'.
									'@media only screen and (max-width: 620px) {'.
									'table[class=body] h1 {'.
										'font-size: 28px !important;'.
										'margin-bottom: 10px !important;'.
									'}'.
									'table[class=body] p,'.
											'table[class=body] ul,'.
											'table[class=body] ol,'.
											'table[class=body] td,'.
											'table[class=body] span,'.
											'table[class=body] a {'.
										'font-size: 16px !important;'.
									'}'.
									'table[class=body] .wrapper,'.
											'table[class=body] .article {'.
										'padding: 10px !important;'.
									'}'.
									'table[class=body] .content {'.
										'padding: 0 !important;'.
									'}'.
									'table[class=body] .container {'.
										'padding: 0 !important;'.
										'width: 100% !important;'.
									'}'.
									'table[class=body] .main {'.
										'border-left-width: 0 !important;'.
										'border-radius: 0 !important;'.
										'border-right-width: 0 !important;'.
									'}'.
									'table[class=body] .btn table {'.
										'width: 100% !important;'.
									'}'.
									'table[class=body] .btn a {'.
										'width: 100% !important;'.
									'}'.
									'table[class=body] .img-responsive {'.
										'height: auto !important;'.
										'max-width: 100% !important;'.
										'width: auto !important;'.
									'}'.
									'}'.
									'@media all {'.
									'.ExternalClass {'.
										'width: 100%;'.
									'}'.
									'.ExternalClass,'.
											'.ExternalClass p,'.
											'.ExternalClass span,'.
											'.ExternalClass font,'.
											'.ExternalClass td,'.
											'.ExternalClass div {'.
										'line-height: 100%;'.
									'}'.
									'.apple-link a {'.
										'color: inherit !important;'.
										'font-family: inherit !important;'.
										'font-size: inherit !important;'.
										'font-weight: inherit !important;'.
										'line-height: inherit !important;'.
										'text-decoration: none !important;'.
									'}'.
									'#MessageViewBody a {'.
										'color: inherit;'.
										'text-decoration: none;'.
										'font-size: inherit;'.
										'font-family: inherit;'.
										'font-weight: inherit;'.
										'line-height: inherit;'.
									'}'.
									'.btn-primary table td:hover {'.
										'background-color: #34495e !important;'.
									'}'.
									'.btn-primary a:hover {'.
										'background-color: #34495e !important;'.
										'border-color: #34495e !important;'.
									'}'.
									'}'.
									'</style>'.
								'</head>'.
								'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
									'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
									'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
									'<tr>'.
										'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
										'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
										'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
											'<!-- START CENTERED WHITE CONTAINER -->'.
											'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
											'<!-- START MAIN CONTENT AREA -->'.
											'<tr>'.
												'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
												'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
													'<tr>'.
													'<td>'.
														'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Hi <strong>'.$user->data->display_name.'</strong>,</p>'.
														'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">There is some unusual activity detected on this account. Did you recently use this device to perform some activity?</p>'.
													'</td>'.
													'</tr>'.
													'<tr>'.
													'<td>'.
														'<table border="0" cellpadding="0" cellspacing="0"style="width: 100%; border-collapse: collapse; Margin-bottom: 15px;">'.
															'<tr>'.
																'<th style="border: 1px solid rgba(0, 0, 0, 1);">Device</th>'.
																'<th style="border: 1px solid rgba(0, 0, 0, 1);">IP Address</th>'.
																'<th style="border: 1px solid rgba(0, 0, 0, 1);">Location</th>'.
															'</tr>'.
															'<tr>'.
																'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['name'].'</td>'.
																'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['ip'].'</td>'.
																'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['location'].'</td>'.
															'</tr>'.
														'</table>'.
													'</td>'.
													'</tr>'.
													'<tr>'.
													'<td>'.
														'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"><a href="'.site_url().'?as_did_1337575='.$device["device_id"].'&asda_ac=1" style="display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center;text-decoration: none;vertical-align: middle;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;user-select: none;background-color: #0d6efd;border: 1px solid #0d6efd;padding: 4px 12px;font-size: 14px;border-radius: 0.25rem; margin-right: 10px;">This was me</button></a><a href="'.site_url().'?as_did_1337575='.$device["device_id"].'&asda_ac=0" style="display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center;text-decoration: none;vertical-align: middle;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;user-select: none;background-color: #bb2d3b;border: 1px solid #b02a37;padding: 4px 12px;font-size: 14px;border-radius: 0.25rem;">This was not me</button></a></p>'.
													'</td>'.
													'</tr>'.
													'<tr>'.
													'<td>'.
														'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Regards,<br>'.$site_title.' Team</p>'.
													'</td>'.
													'</tr>'.
													'<tr>'.
													'<td>'.
														'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
													'</td>'.
													'</tr>'.
												'</table>'.
												'</td>'.
											'</tr>'.
											'<!-- END MAIN CONTENT AREA -->'.
											'</table>'.
											'<!-- START FOOTER -->'.
											'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
											'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
												'<tr>'.
												'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
													'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
												'</td>'.
												'</tr>'.
											'</table>'.
											'</div>'.
											'<!-- END FOOTER -->'.
										'<!-- END CENTERED WHITE CONTAINER -->'.
										'</div>'.
										'</td>'.
										'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
									'</tr>'.
									'</table>'.
								'</body>'.
								'</html>';
								$headers = array('Content-Type: text/html; charset=UTF-8');
								$mailResult = wp_mail( $to, $subject, $body, $headers );
							
								// Update the timestamp

								global $wpdb;
								$table_name = $wpdb->prefix . 'authsafe_sleep'; 

								$data = array(
									'c_time' => $current_time,
								);
								
								// The WHERE clause to specify the rows to be updated (for example, updating the row with 'id' equal to 1)
								$where = array(
									'cid' => $user_id,
								);

								// Execute the update query
								$updated = $wpdb->update($table_name, $data, $where);
								
							} 
						}
						else
						{
							global $wpdb;
							$table_name = $wpdb->prefix . 'authsafe_sleep'; 
						
							// Prepare data for insertion
							$data = array(
								array(
									'cid' => $user_id,
									'c_time' =>  $current_time,
								),
								
							);
						
							// Insert data into the custom table
							foreach ($data as $row) {
								$wpdb->insert($table_name, $row);
							}
							
							// Mail Trigger 
							$device = $res["device"];
							$to = $user->data->user_email;
							$subject = 'Unusual activity detected on this account';
							$body = '<!doctype html>'.
							'<html>'.
							'<head>'.
								'<meta name="viewport" content="width=device-width">'.
								'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
								'<title>'.$subject.'</title>'.
								'<style>'.
								'@media only screen and (max-width: 620px) {'.
								'table[class=body] h1 {'.
									'font-size: 28px !important;'.
									'margin-bottom: 10px !important;'.
								'}'.
								'table[class=body] p,'.
										'table[class=body] ul,'.
										'table[class=body] ol,'.
										'table[class=body] td,'.
										'table[class=body] span,'.
										'table[class=body] a {'.
									'font-size: 16px !important;'.
								'}'.
								'table[class=body] .wrapper,'.
										'table[class=body] .article {'.
									'padding: 10px !important;'.
								'}'.
								'table[class=body] .content {'.
									'padding: 0 !important;'.
								'}'.
								'table[class=body] .container {'.
									'padding: 0 !important;'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .main {'.
									'border-left-width: 0 !important;'.
									'border-radius: 0 !important;'.
									'border-right-width: 0 !important;'.
								'}'.
								'table[class=body] .btn table {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .btn a {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .img-responsive {'.
									'height: auto !important;'.
									'max-width: 100% !important;'.
									'width: auto !important;'.
								'}'.
								'}'.
								'@media all {'.
								'.ExternalClass {'.
									'width: 100%;'.
								'}'.
								'.ExternalClass,'.
										'.ExternalClass p,'.
										'.ExternalClass span,'.
										'.ExternalClass font,'.
										'.ExternalClass td,'.
										'.ExternalClass div {'.
									'line-height: 100%;'.
								'}'.
								'.apple-link a {'.
									'color: inherit !important;'.
									'font-family: inherit !important;'.
									'font-size: inherit !important;'.
									'font-weight: inherit !important;'.
									'line-height: inherit !important;'.
									'text-decoration: none !important;'.
								'}'.
								'#MessageViewBody a {'.
									'color: inherit;'.
									'text-decoration: none;'.
									'font-size: inherit;'.
									'font-family: inherit;'.
									'font-weight: inherit;'.
									'line-height: inherit;'.
								'}'.
								'.btn-primary table td:hover {'.
									'background-color: #34495e !important;'.
								'}'.
								'.btn-primary a:hover {'.
									'background-color: #34495e !important;'.
									'border-color: #34495e !important;'.
								'}'.
								'}'.
								'</style>'.
							'</head>'.
							'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
								'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
								'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
								'<tr>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
									'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
									'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
										'<!-- START CENTERED WHITE CONTAINER -->'.
										'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
										'<!-- START MAIN CONTENT AREA -->'.
										'<tr>'.
											'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
											'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Hi <strong>'.$user->data->display_name.'</strong>,</p>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">There is some unusual activity detected on this account. Did you recently use this device to perform some activity?</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<table border="0" cellpadding="0" cellspacing="0"style="width: 100%; border-collapse: collapse; Margin-bottom: 15px;">'.
														'<tr>'.
															'<th style="border: 1px solid rgba(0, 0, 0, 1);">Device</th>'.
															'<th style="border: 1px solid rgba(0, 0, 0, 1);">IP Address</th>'.
															'<th style="border: 1px solid rgba(0, 0, 0, 1);">Location</th>'.
														'</tr>'.
														'<tr>'.
															'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['name'].'</td>'.
															'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['ip'].'</td>'.
															'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['location'].'</td>'.
														'</tr>'.
													'</table>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"><a href="'.site_url().'?as_did_1337575='.$device["device_id"].'&asda_ac=1" style="display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center;text-decoration: none;vertical-align: middle;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;user-select: none;background-color: #0d6efd;border: 1px solid #0d6efd;padding: 4px 12px;font-size: 14px;border-radius: 0.25rem; margin-right: 10px;">This was me</button></a><a href="'.site_url().'?as_did_1337575='.$device["device_id"].'&asda_ac=0" style="display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center;text-decoration: none;vertical-align: middle;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;user-select: none;background-color: #bb2d3b;border: 1px solid #b02a37;padding: 4px 12px;font-size: 14px;border-radius: 0.25rem;">This was not me</button></a></p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Regards,<br>'.$site_title.' Team</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
												'</td>'.
												'</tr>'.
											'</table>'.
											'</td>'.
										'</tr>'.
										'<!-- END MAIN CONTENT AREA -->'.
										'</table>'.
										'<!-- START FOOTER -->'.
										'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
										'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
												'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
											'</td>'.
											'</tr>'.
										'</table>'.
										'</div>'.
										'<!-- END FOOTER -->'.
									'<!-- END CENTERED WHITE CONTAINER -->'.
									'</div>'.
									'</td>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'</tr>'.
								'</table>'.
							'</body>'.
							'</html>';
							$headers = array('Content-Type: text/html; charset=UTF-8');
							$mailResult = wp_mail( $to, $subject, $body, $headers );
							
							
						}

				    }
					
				}
			}
			
		}
		
	}

	public function payment_attempt($order_id)            
	{
		$device_id = sanitize_text_field($_COOKIE["device___id"]);
		
		if (isset($_COOKIE['device___id'])) {           
			unset($_COOKIE['device___id']);
            setcookie('device___id', '', time() + 10, '/');
		}  

		$options = get_option('ats_options', Authsafe::default_options());
					
		$ats_policy_options = get_option('ats_policy_options', Authsafe::default_options());
					
		require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
		$obj = new AuthSafe\AuthSafe([
			'property_id' => $options['ats_property_id'],
			'property_secret' => $options['ats_property_secret']
		]);

		$customer = WC()->customer;

		$user_id = $customer->get_id(); 
		$first_name = $customer->get_first_name(); 
		$last_name = $customer->get_last_name(); 
		$email = $customer->get_email();
		$phone_number = WC()->customer->get_billing_phone();
		$formattedDate = date('Y-F-d');

		$cart = WC()->cart;
		$product_total = $cart->get_total();
		
		// Remove the Indian Rupee symbol (₹) from the total value
		$total_value_without_rs = preg_replace('/[^\d.]/', '', $product_total);

		// Convert the modified total value to a numeric format (float)
		$total_float = (float) $total_value_without_rs;

		$total_array = str_split($total_float);
				
		// Remove the first,second,third and four value from the array
		$removedValue = array_shift($total_array);  
		$removedValue = array_shift($total_array);

		// Convert array into string 
		$total_price = implode('', $total_array);
		
		$current_user = wp_get_current_user();
		$user_login_id = $current_user->user_login;

		$transactionId = uniqid();
			

		$item_s = array();
		$item_p = array();
					
		// Loop through the cart items
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) 
		{

			$product_id = $cart_item['product_id'];
			$product = wc_get_product( $product_id );

			$product_name = $product->get_name();
			$product_price = $product->get_price();
			$product_id = $product->get_id();
			$quantity = $cart_item['quantity'];
			$item_p = ['item_name' => $product_name,'item_price' => $product_price,'item_id' => $product_id,'item_quantity' => $quantity];
			array_push($item_s,$item_p);
				
		}
					
		$transactionExtras = array(
			'email' => $email,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'user_id' => $user_id,                    
			'username' => $first_name.' '.$last_name, 
			'transaction_type' => 'purchase_attempt',
			'transaction_amount' => $total_price,            
			'phone_no' => $phone_number,
			'transaction_id' => $transactionId,
			'items' => json_encode($item_s)
		);
		
		if ( is_user_logged_in() )
		{			
			$res = $obj->transactionAttempt('attempt_succeeded',$device_id,$transactionExtras);

			if($res) {
				
				if(!isset($ats_policy_options['ats_shadow_mode'])) {
			
					if(gettype($res) == "string") {
						/*var_dump($res);
						die();*/
					} else {
							
						$status = $res["status"];
						$severity = $res["severity"];
						$site_title = get_bloginfo( 'name' );

						/* The below code is checking the value of the variable . If the value is truthy
						(evaluates to true), it sets  to true. Otherwise, it sets  to
						false. */

						$checkbox_allow = $ats_policy_options['ats_allow_payment_email'];
						if($checkbox_allow)
						{
							$checkbox_allow = true;
						}
						else
						{
							$checkbox_allow = false;
						}
						$checkbox_challenge = $ats_policy_options['ats_challenge_payment_email'];
						if($checkbox_challenge)
						{
							$checkbox_challenge = true;
						}
						else
						{
							$checkbox_challenge = false;
						}
						$checkbox_deny = $ats_policy_options['ats_deny_payment_email'];
						if($checkbox_deny)
						{
							$checkbox_deny = true;
						}
						else
						{
							$checkbox_deny = false;
						}
        
						if($status == "allow" && $severity == 'medium' && $checkbox_allow == true) 
						{			
							$device = $res["device"];
							$to = $email;
							$subject = 'Important: Recent Transaction and Security Notification';
							$body = '<!doctype html>'.
							'<html>'.
							'<head>'.
								'<meta name="viewport" content="width=device-width">'.
								'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
								'<title>'.$subject.'</title>'.
								'<style>'.
								'@media only screen and (max-width: 620px) {'.
								'table[class=body] h1 {'.
									'font-size: 28px !important;'.
									'margin-bottom: 10px !important;'.
								'}'.
								'table[class=body] p,'.
										'table[class=body] ul,'.
										'table[class=body] ol,'.
										'table[class=body] td,'.
										'table[class=body] span,'.
										'table[class=body] a {'.
									'font-size: 16px !important;'.
								'}'.
								'table[class=body] .wrapper,'.
										'table[class=body] .article {'.
									'padding: 10px !important;'.
								'}'.
								'table[class=body] .content {'.
									'padding: 0 !important;'.
								'}'.
								'table[class=body] .container {'.
									'padding: 0 !important;'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .main {'.
									'border-left-width: 0 !important;'.
									'border-radius: 0 !important;'.
									'border-right-width: 0 !important;'.
								'}'.
								'table[class=body] .btn table {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .btn a {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .img-responsive {'.
									'height: auto !important;'.
									'max-width: 100% !important;'.
									'width: auto !important;'.
								'}'.
								'}'.
								'@media all {'.
								'.ExternalClass {'.
									'width: 100%;'.
								'}'.
								'.ExternalClass,'.
										'.ExternalClass p,'.
										'.ExternalClass span,'.
										'.ExternalClass font,'.
										'.ExternalClass td,'.
										'.ExternalClass div {'.
									'line-height: 100%;'.
								'}'.
								'.apple-link a {'.
									'color: inherit !important;'.
									'font-family: inherit !important;'.
									'font-size: inherit !important;'.
									'font-weight: inherit !important;'.
									'line-height: inherit !important;'.
									'text-decoration: none !important;'.
								'}'.
								'#MessageViewBody a {'.
									'color: inherit;'.
									'text-decoration: none;'.
									'font-size: inherit;'.
									'font-family: inherit;'.
									'font-weight: inherit;'.
									'line-height: inherit;'.
								'}'.
								'.btn-primary table td:hover {'.
									'background-color: #34495e !important;'.
								'}'.
								'.btn-primary a:hover {'.
									'background-color: #34495e !important;'.
									'border-color: #34495e !important;'.
								'}'.
								'}'.
								'</style>'.
							'</head>'.
							'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
								'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
								'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
								'<tr>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
									'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
									'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
										'<!-- START CENTERED WHITE CONTAINER -->'.
										'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
										'<!-- START MAIN CONTENT AREA -->'.
										'<tr>'.
											'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
											'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Dear <strong>'.$first_name.' '. $last_name .'</strong>,</p>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">We wanted to bring to your attention a matter concerning a transaction on your account. </p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">We have identified a successful transaction from your account for <b>Amount</b> '. $product_total .' on <b>'. $formattedDate .'</b> that appeared to have some characteristics indicative of potential suspicious activity.  </p>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">To ensure your account\'s security, we recommend taking prompt action. Consider changing your password and reviewing your account settings. </p>'.								
												'</td>'.
												'</tr>'.
												
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Regards,<br>'.$site_title.' Team</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
												'</td>'.
												'</tr>'.
											'</table>'.
											'</td>'.
										'</tr>'.
										'<!-- END MAIN CONTENT AREA -->'.
										'</table>'.
										'<!-- START FOOTER -->'.
										'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
										'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
												'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
											'</td>'.
											'</tr>'.
										'</table>'.
										'</div>'.
										'<!-- END FOOTER -->'.
									'<!-- END CENTERED WHITE CONTAINER -->'.
									'</div>'.
									'</td>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'</tr>'.
								'</table>'.
							'</body>'.
							'</html>';
							$headers = array('Content-Type: text/html; charset=UTF-8');
							$mailResult = wp_mail( $to, $subject, $body, $headers );		
						} 
						if($status == "challenge" && $severity == "high" && $checkbox_challenge == true)
						{
							$device = $res["device"];
							$to = $email;
							$subject = 'Important: Suspicious Transaction Detected on Your Account';
							$body = '<!doctype html>'.
							'<html>'.
							'<head>'.
								'<meta name="viewport" content="width=device-width">'.
								'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
								'<title>'.$subject.'</title>'.
								'<style>'.
								'@media only screen and (max-width: 620px) {'.
								'table[class=body] h1 {'.
									'font-size: 28px !important;'.
									'margin-bottom: 10px !important;'.
								'}'.
								'table[class=body] p,'.
										'table[class=body] ul,'.
										'table[class=body] ol,'.
										'table[class=body] td,'.
										'table[class=body] span,'.
										'table[class=body] a {'.
									'font-size: 16px !important;'.
								'}'.
								'table[class=body] .wrapper,'.
										'table[class=body] .article {'.
									'padding: 10px !important;'.
								'}'.
								'table[class=body] .content {'.
									'padding: 0 !important;'.
								'}'.
								'table[class=body] .container {'.
									'padding: 0 !important;'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .main {'.
									'border-left-width: 0 !important;'.
									'border-radius: 0 !important;'.
									'border-right-width: 0 !important;'.
								'}'.
								'table[class=body] .btn table {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .btn a {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .img-responsive {'.
									'height: auto !important;'.
									'max-width: 100% !important;'.
									'width: auto !important;'.
								'}'.
								'}'.
								'@media all {'.
								'.ExternalClass {'.
									'width: 100%;'.
								'}'.
								'.ExternalClass,'.
										'.ExternalClass p,'.
										'.ExternalClass span,'.
										'.ExternalClass font,'.
										'.ExternalClass td,'.
										'.ExternalClass div {'.
									'line-height: 100%;'.
								'}'.
								'.apple-link a {'.
									'color: inherit !important;'.
									'font-family: inherit !important;'.
									'font-size: inherit !important;'.
									'font-weight: inherit !important;'.
									'line-height: inherit !important;'.
									'text-decoration: none !important;'.
								'}'.
								'#MessageViewBody a {'.
									'color: inherit;'.
									'text-decoration: none;'.
									'font-size: inherit;'.
									'font-family: inherit;'.
									'font-weight: inherit;'.
									'line-height: inherit;'.
								'}'.
								'.btn-primary table td:hover {'.
									'background-color: #34495e !important;'.
								'}'.
								'.btn-primary a:hover {'.
									'background-color: #34495e !important;'.
									'border-color: #34495e !important;'.
								'}'.
								'}'.
								'</style>'.
							'</head>'.
							'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
								'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
								'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
								'<tr>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
									'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
									'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
										'<!-- START CENTERED WHITE CONTAINER -->'.
										'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
										'<!-- START MAIN CONTENT AREA -->'.
										'<tr>'.
											'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
											'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Dear <strong>'.$first_name.' '. $last_name .'</strong>,</p>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">We wanted to bring a matter to your immediate attention regarding a recent transaction activity from your account.</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">We have identified a successful transaction from your account for <b>Amount</b> '. $product_total .' on <b>'. $formattedDate .'</b>.</p>'.
												'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Click below to let us know if this was you or not. If not, we advise changing your password immediately to protect your account.  </p>'.								
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<table border="0" cellpadding="0" cellspacing="0"style="width: 100%; border-collapse: collapse; Margin-bottom: 15px;">'.
														'<tr>'.
															'<th style="border: 1px solid rgba(0, 0, 0, 1);">Device</th>'.
															'<th style="border: 1px solid rgba(0, 0, 0, 1);">IP Address</th>'.
															'<th style="border: 1px solid rgba(0, 0, 0, 1);">Location</th>'.
														'</tr>'.
														'<tr>'.
															'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['name'].'</td>'.
															'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['ip'].'</td>'.
															'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['location'].'</td>'.
														'</tr>'.
													'</table>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"><a href="'.site_url().'?as_did_1337575='.$device["device_id"].'&asda_ac=1" style="display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center;text-decoration: none;vertical-align: middle;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;user-select: none;background-color: #0d6efd;border: 1px solid #0d6efd;padding: 4px 12px;font-size: 14px;border-radius: 0.25rem; margin-right: 10px;">This was me</button></a><a href="'.site_url().'?as_did_1337575='.$device["device_id"].'&asda_ac=0" style="display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center;text-decoration: none;vertical-align: middle;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;user-select: none;background-color: #bb2d3b;border: 1px solid #b02a37;padding: 4px 12px;font-size: 14px;border-radius: 0.25rem;">This was not me</button></a></p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Regards,<br>'.$site_title.' Team</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
												'</td>'.
												'</tr>'.
											'</table>'.
											'</td>'.
										'</tr>'.
										'<!-- END MAIN CONTENT AREA -->'.
										'</table>'.
										'<!-- START FOOTER -->'.
										'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
										'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
												'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
											'</td>'.
											'</tr>'.
										'</table>'.
										'</div>'.
										'<!-- END FOOTER -->'.
									'<!-- END CENTERED WHITE CONTAINER -->'.
									'</div>'.
									'</td>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'</tr>'.
								'</table>'.
							'</body>'.
							'</html>';
							$headers = array('Content-Type: text/html; charset=UTF-8');
							$mailResult = wp_mail( $to, $subject, $body, $headers );		
						}
						if($status == "deny" && $severity == "critical" && $checkbox_deny == true)
						{		
							$device = $res["device"];
							$to = $email;
							$subject = 'Urgent: Highly Suspicious Transaction Detected on Your Account';
							$body = '<!doctype html>'.
							'<html>'.
							'<head>'.
								'<meta name="viewport" content="width=device-width">'.
								'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
								'<title>'.$subject.'</title>'.
								'<style>'.
								'@media only screen and (max-width: 620px) {'.
								'table[class=body] h1 {'.
									'font-size: 28px !important;'.
									'margin-bottom: 10px !important;'.
								'}'.
								'table[class=body] p,'.
										'table[class=body] ul,'.
										'table[class=body] ol,'.
										'table[class=body] td,'.
										'table[class=body] span,'.
										'table[class=body] a {'.
									'font-size: 16px !important;'.
								'}'.
								'table[class=body] .wrapper,'.
										'table[class=body] .article {'.
									'padding: 10px !important;'.
								'}'.
								'table[class=body] .content {'.
									'padding: 0 !important;'.
								'}'.
								'table[class=body] .container {'.
									'padding: 0 !important;'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .main {'.
									'border-left-width: 0 !important;'.
									'border-radius: 0 !important;'.
									'border-right-width: 0 !important;'.
								'}'.
								'table[class=body] .btn table {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .btn a {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .img-responsive {'.
									'height: auto !important;'.
									'max-width: 100% !important;'.
									'width: auto !important;'.
								'}'.
								'}'.
								'@media all {'.
								'.ExternalClass {'.
									'width: 100%;'.
								'}'.
								'.ExternalClass,'.
										'.ExternalClass p,'.
										'.ExternalClass span,'.
										'.ExternalClass font,'.
										'.ExternalClass td,'.
										'.ExternalClass div {'.
									'line-height: 100%;'.
								'}'.
								'.apple-link a {'.
									'color: inherit !important;'.
									'font-family: inherit !important;'.
									'font-size: inherit !important;'.
									'font-weight: inherit !important;'.
									'line-height: inherit !important;'.
									'text-decoration: none !important;'.
								'}'.
								'#MessageViewBody a {'.
									'color: inherit;'.
									'text-decoration: none;'.
									'font-size: inherit;'.
									'font-family: inherit;'.
									'font-weight: inherit;'.
									'line-height: inherit;'.
								'}'.
								'.btn-primary table td:hover {'.
									'background-color: #34495e !important;'.
								'}'.
								'.btn-primary a:hover {'.
									'background-color: #34495e !important;'.
									'border-color: #34495e !important;'.
								'}'.
								'}'.
								'</style>'.
							'</head>'.
							'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
								'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
								'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
								'<tr>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
									'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
									'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
										'<!-- START CENTERED WHITE CONTAINER -->'.
										'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
										'<!-- START MAIN CONTENT AREA -->'.
										'<tr>'.
											'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
											'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Dear <strong>'.$first_name.' '. $last_name .'</strong>,</p>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">We have blocked a highly suspicious transaction from your account for <b>Amount</b> '. $product_total .' on '. $formattedDate.'. </p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Within the next few minutes, you would receive a follow-up email containing a secure link to initiate the password reset process. Please keep an eye on your inbox to set a new password.</p>'.								
												'</td>'.
									
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Regards,<br>'.$site_title.' Team</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
												'</td>'.
												'</tr>'.
											'</table>'.
											'</td>'.
										'</tr>'.
										'<!-- END MAIN CONTENT AREA -->'.
										'</table>'.
										'<!-- START FOOTER -->'.
										'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
										'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
												'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
											'</td>'.
											'</tr>'.
										'</table>'.
										'</div>'.
										'<!-- END FOOTER -->'.
									'<!-- END CENTERED WHITE CONTAINER -->'.
									'</div>'.
									'</td>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'</tr>'.
								'</table>'.
							'</body>'.
							'</html>';
							$headers = array('Content-Type: text/html; charset=UTF-8');
							$mailResult = wp_mail( $to, $subject, $body, $headers );
							sleep(10);
							$current_user = wp_get_current_user();
							$results = retrieve_password($current_user->user_login);									

							if ( true === $results ) {
								$message = 'Your account is blocked due to Suspicious Transaction Activities. We have sent a password reset email, please reset your password to activate your account again.';
							} else {
								$message = $results->get_error_message();
							}
							$login_url = site_url( 'wp-login.php?action=as_ch_dn&as_msg='.urlencode($message), 'login' );
							wp_logout();
							wp_safe_redirect( $login_url );
							exit;
						} 
								
					}
				}
				
			}
		}
		else
		{
						
			$transactionExtras = array(
				'email' => 'Unregister User',
				'first_name' => 'Unregister User',
				'last_name' => 'Unregister User',
				'user_id' => 'Unregister User',               
				'username' => 'Unregister User',             
				'phone_no' => 'Unregister User',
				'transaction_type' => 'purchase',
				'transaction_amount' =>'Unregister User',   
				'items' => json_encode($item_s)
			);

			$res = $obj->transactionAttempt('transaction_attempt',$device_id,$transactionExtras);

				if($res) {
					
					if(!isset($ats_policy_options['ats_shadow_mode'])) {
				
						if(gettype($res) == "string") {
							/*var_dump($res);
							die();*/
						} else {
						
							$status = $res["status"];
							$severity = $res["severity"];
							$site_title = get_bloginfo( 'name' );


							// Check the policy page checkbox -> click or not click
							$checkbox_allow = $ats_policy_options['ats_allow_payment_email'];
							if($checkbox_allow)
							{
								$checkbox_allow = true;
							}
							else
							{
								$checkbox_allow = false;
							}
							$checkbox_challenge = $ats_policy_options['ats_challenge_payment_email'];
							if($checkbox_challenge)
							{
								$checkbox_challenge = true;
							}
							else
							{
								$checkbox_challenge = false;
							}
							$checkbox_deny = $ats_policy_options['ats_deny_payment_email'];
							if($checkbox_deny)
							{
								$checkbox_deny = true;
							}
							else
							{
								$checkbox_deny = false;
							}

							if($status == "allow" && $severity == "medium" && $checkbox_allow == true) 
							{
								// die('Medium also working');
							} 
							if($status == "challenge" && $severity == "high" && $checkbox_challenge == true)
							{
								// die('Challenge also working');
							}
							else if($status == "deny" && $severity == "critical" && $checkbox_deny == true)
							{
								// die('deny also working');
							} 
							
						}
					}
					
				}
		}
    }

	public function payment_complete($order_id)
	{

		/* The above code is retrieving the value of a custom field from the WooCommerce session and then
		echoing it. */
		
		$device_id = WC()->session->get('custom_field_value');
		$device_id = sanitize_text_field($device_id);     // Sanitize the device ID

		$options = get_option('ats_options', Authsafe::default_options());
			
		$ats_policy_options = get_option('ats_policy_options', Authsafe::default_options());
		
		require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
		$obj = new AuthSafe\AuthSafe([
			'property_id' => $options['ats_property_id'],
			'property_secret' => $options['ats_property_secret']
		]);

		$customer = WC()->customer;
		$user_id = $customer->get_id(); 
		$first_name = $customer->get_first_name(); 
		$last_name = $customer->get_last_name(); 
		$email = $customer->get_email(); 
			
		$order = wc_get_order($order_id);
		$orderid = $order_id;

		$payment_status = $order->status;  
		$currency = $order->currency;
		$payment_date = $order->date_created;
		$total_amount = $order->total;
		$Order_Key = $order->order_key;
		$payment_method = $order->payment_method;
		$payment_method_title = $order->payment_method_title;
		$transaction_id = $order->transaction_id;
		$customer_ip_address = $order->customer_ip_address;
		$customer_user_agent = $order->customer_user_agent;
		$date_paid = $order->date_paid;
			
		$b_fname = $order->data['billing']['first_name'];
		$b_lname = $order->data['billing']['last_name'];
		$b_company = $order->data['billing']['company'];
		$b_address1 = $order->data['billing']['address_1'];
		$b_address2 = $order->data['billing']['address_2'];
		$b_city = $order->data['billing']['city'];
		$b_state = $order->data['billing']['state'];
		$b_country = $order->data['billing']['country'];
		$b_email = $order->data['billing']['email'];
		$b_phone_number = $order->data['billing']['phone'];

		$sh_fname = $order->data['shipping']['first_name'];
		$sh_lname = $order->data['shipping']['last_name'];
		$sh_company = $order->data['shipping']['company'];
		$sh_address1 = $order->data['shipping']['address_1'];
		$sh_address2 = $order->data['shipping']['address_2'];
		$sh_city = $order->data['shipping']['city'];
		$sh_state = $order->data['shipping']['state'];
		$sh_country = $order->data['shipping']['country'];
		$sh_phone_number = $order->data['shipping']['phone'];

		$item_s = array();
		$item_p = array();
		$cart = WC()->cart;   // Get the cart object
		$product_total = $cart->get_total();

		$order = wc_get_order($order_id);
		$items = $order->get_items();
		$Total_Expense = 0;		
		foreach ($items as $item_id => $item) 
		{

			$product_name = $item->get_name();
			$quantity = $item->get_quantity();
			$product_id = $item->get_product_id();
			$product = wc_get_product($product_id);
			$product_total = $product->get_regular_price();
			$product_id = $item->get_id();
			$total = $item->get_total();
			$Total_Expense = $Total_Expense + $total;
			$item_p = ['item_id' => $product_id,'item_name' => $product_name,'item_price' => $product_total,'item_quantity' => $quantity];
			array_push($item_s,$item_p);
						
		}

			
		if($payment_method == "cod")
		{
			$transaction_id = uniqid();
		}

		$transactionFields = array(
			'user_id' => $user_id,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'username' => $first_name.' '.$last_name, 
			'transaction_type' => 'purchase_succeeded',
			'email' => $email,
			'OrderID' => $orderid,
			'payment_status' => $payment_status,  // ev
			'currency' => $currency,
			'transaction_currency' => $currency,
			'transaction_amount' => $total_amount,
			'payment_date' => $payment_date,
			'total_amount' => $total_amount,
			'order_key' => $Order_Key,
			'payment_mode' => $payment_method,
			'payment_provider' => $payment_method_title,
			'transaction_id' => $transaction_id,
			'customer_ip_address' => $customer_ip_address,
			'customer_user_agent' => $customer_user_agent,
			'date_paid' => $date_paid,
			'billing_country' => $b_country,
			'billing_state' => $b_state,
			'billing_city' => $b_city,
			'billing_fname' => $b_fname,
			'billing_lname' => $b_lname,
			'billing_email' => $b_email,
			'billing_company' => $b_company,
			'billing_address1' => $b_address1,
			'billing_address2' => $b_address2,
			'billing_email' => $b_email,
			'phone_no' => $b_phone_number,
			'shipping_country' => $sh_country,
			'shipping_state' => $sh_state,
			'shipping_city' => $sh_city,
			'shipping_fname' => $sh_fname,
			'shipping_lname' => $sh_lname,
			// 'shipping_email' => $sh_email,
			'shipping_company' => $sh_company,
			'shipping_address1' => $sh_address1,
			'shipping_address2' => $sh_address2,
			// 'shipping_email' => $sh_email,
			'shipping_phone_numsher' => $sh_phone_number,
			'items' => json_encode($item_s)
		);

        if ( is_user_logged_in() ) 
		{	
			if($payment_status == 'processing')
			{
				$payment_status = 'transaction_succeeded';
			}
			else
			{
				$payment_status = 'transaction_failed';
			}
			$res = $obj->transactionAttempt($payment_status,$device_id,$transactionFields);

			if($res) {
						
				if(!isset($ats_policy_options['ats_shadow_mode'])) {
			
					if(gettype($res) == "string") {
						/*var_dump($res);
						die();*/
					} else {
					
						$status = $res["status"];
						$severity = $res["severity"];
						$site_title = get_bloginfo( 'name' );

						/* The above code is checking the value of the variable . If the value is truthy
						(evaluates to true), it sets  to true. Otherwise, it sets  to false. */

						$checkbox_allow = $ats_policy_options['ats_allow_payment_email'];
						if($checkbox_allow)
						{
							$checkbox_allow = true;
						}
						else
						{
							$checkbox_allow = false;
						}
						$checkbox_challenge = $ats_policy_options['ats_challenge_payment_email'];
						if($checkbox_challenge)
						{
							$checkbox_challenge = true;
						}
						else
						{ 
							$checkbox_challenge = false;
						}
						$checkbox_deny = $ats_policy_options['ats_deny_payment_email'];
						if($checkbox_deny)
						{
							$checkbox_deny = true;
						}
						else
						{ 
							$checkbox_deny = false;
						}

						if($checkbox_deny == true || $checkbox_allow == true || $checkbox_challenge == true && $status == "allow" || $status == "deny" && $status == "challenge" || $status == "low") 
						{
							$lost_pass_url = wp_lostpassword_url();

							$device = $res["device"];
							$to = $email;
							
							$subject = 'Authsafe - Payment Successfully Done';
							$body = '<!doctype html>'.
							'<html>'.
							'<head>'.
								'<meta name="viewport" content="width=device-width">'.
								'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
								'<title>'.$subject.'</title>'.
								'<style>'.
								'@media only screen and (max-width: 620px) {'.
								'table[class=body] h1 {'.
									'font-size: 28px !important;'.
									'margin-bottom: 10px !important;'.
								'}'.
								'table[class=body] p,'.
										'table[class=body] ul,'.
										'table[class=body] ol,'.
										'table[class=body] td,'.
										'table[class=body] span,'.
										'table[class=body] a {'.
									'font-size: 16px !important;'.
								'}'.
								'table[class=body] .wrapper,'.
										'table[class=body] .article {'.
									'padding: 10px !important;'.
								'}'.
								'table[class=body] .content {'.
									'padding: 0 !important;'.
								'}'.
								'table[class=body] .container {'.
									'padding: 0 !important;'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .main {'.
									'border-left-width: 0 !important;'.
									'border-radius: 0 !important;'.
									'border-right-width: 0 !important;'.
								'}'.
								'table[class=body] .btn table {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .btn a {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .img-responsive {'.
									'height: auto !important;'.
									'max-width: 100% !important;'.
									'width: auto !important;'.
								'}'.
								'}'.
								'@media all {'.
								'.ExternalClass {'.
									'width: 100%;'.
								'}'.
								'.ExternalClass,'.
										'.ExternalClass p,'.
										'.ExternalClass span,'.
										'.ExternalClass font,'.
										'.ExternalClass td,'.
										'.ExternalClass div {'.
									'line-height: 100%;'.
								'}'.
								'.apple-link a {'.
									'color: inherit !important;'.
									'font-family: inherit !important;'.
									'font-size: inherit !important;'.
									'font-weight: inherit !important;'.
									'line-height: inherit !important;'.
									'text-decoration: none !important;'.
								'}'.
								'#MessageViewBody a {'.
									'color: inherit;'.
									'text-decoration: none;'.
									'font-size: inherit;'.
									'font-family: inherit;'.
									'font-weight: inherit;'.
									'line-height: inherit;'.
								'}'.
								'.btn-primary table td:hover {'.
									'background-color: #34495e !important;'.
								'}'.
								'.btn-primary a:hover {'.
									'background-color: #34495e !important;'.
									'border-color: #34495e !important;'.
								'}'.
								'}'.
								'</style>'.
							'</head>'.
							'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
								'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
								'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
								'<tr>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
									'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
									'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
										'<!-- START CENTERED WHITE CONTAINER -->'.
										'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
										'<!-- START MAIN CONTENT AREA -->'.
										'<tr>'.
											'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
											'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Hi <strong>'.$first_name.' '.$last_name.'</strong>,</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p>Thank you for your recent transaction. Your payment of '. $Total_Expense .' for '. $site_title .' has been successfully processed. If you have any questions or need assistance, please feel free to contact us.</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Here is some detaile about last Order:</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">1) Billing Email - '.$b_email.'</p>'.
												'</td>'.
												'</tr>'.
												// '<tr>'.
												// '<td>'.
												// 	'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">2) IP - '.$device['ip'].'</p>'.$device['ip']
												// '</td>'.
												// '</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">3) Location - Country('.$b_country.'), State('.$b_state.'), City('.$b_city.')</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">4) Billing Phone Number - '.$b_phone_number.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">5) Total Amount of Transaction - '.$Total_Expense.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">6) Transaction ID - '.$transaction_id.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">7) Order ID - '.$order_id.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">8) Payment Status - '.$payment_status.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.

												'<tr>'.
														'<td>'.
															'<table border="0" cellpadding="0" cellspacing="0"style="width: 100%; border-collapse: collapse; Margin-bottom: 15px;">'.
																'<tr>'.
																	'<th style="border: 1px solid rgba(0, 0, 0, 1);">Status</th>'.
																	'<th style="border: 1px solid rgba(0, 0, 0, 1);">IP Address</th>'.
																	'<th style="border: 1px solid rgba(0, 0, 0, 1);">Severity</th>'.
																'</tr>'.
																'<tr>'.
																	'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$res["status"].'</td>'.
																	'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$device['ip'].'</td>'.
																	'<td style="border: 1px solid rgba(0, 0, 0, 1); text-align: center;">'.$res["severity"].'</td>'.
																'</tr>'.
															'</table>'.
														'</td>'.
												'</tr>'.
													
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Thank you for your order. We truly value our loyal customers. Thanks for making us who we are</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
												'</td>'.
												'</tr>'.
											'</table>'.
											'</td>'.
										'</tr>'.
										'<!-- END MAIN CONTENT AREA -->'.
										'</table>'.
										'<!-- START FOOTER -->'.
										'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
										'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
												'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
											'</td>'.
											'</tr>'.
										'</table>'.
										'</div>'.
										'<!-- END FOOTER -->'.
									'<!-- END CENTERED WHITE CONTAINER -->'.
									'</div>'.
									'</td>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'</tr>'.
								'</table>'.
							'</body>'.
							'</html>';
							$headers = array('Content-Type: text/html; charset=UTF-8');

							$mailResult = wp_mail( $to, $subject, $body, $headers );

							
						} 
						// if($status == "allow" && $severity == "medium" && $checkbox_allow == true) 
						// {
							// die('Medium also working');
						// }
						// if($status == "challenge" && $severity == "high" && $checkbox_challenge == true)
						// {
                            // die('Challenge also working');
						// }
						// if($status == "deny" && $severity == "critical" && $checkbox_deny == true)
						// {
							// die('Deny also working');
						// } 
					}
				}
				
			}
		}
		else
		{	
			if($payment_status == 'processing')
			{
				$payment_status = 'transaction_succeeded';
			}
			else
			{
				$payment_status = 'transaction_failed';
			}
			$res = $obj->transactionAttempt($payment_status,$device_id,$transactionFields);

			if($res) {
						
				if(!isset($ats_policy_options['ats_shadow_mode'])) {
			
					if(gettype($res) == "string") {
						/*var_dump($res);
						die();*/
					} else 
					{

						$status = $res["status"];
						$severity = $res["severity"];
						$site_title = get_bloginfo( 'name' );

						$checkbox_allow = $ats_policy_options['ats_allow_payment_email'];
						if($checkbox_allow)
						{
							$checkbox_allow = true;
						}
						else
						{
							$checkbox_allow = false;
						}
						$checkbox_challenge = $ats_policy_options['ats_challenge_payment_email'];
						if($checkbox_challenge)
						{
							$checkbox_challenge = true;
						}
						else
						{ 
							$checkbox_challenge = false;
						}
						$checkbox_deny = $ats_policy_options['ats_deny_payment_email'];
						if($checkbox_deny)
						{
							$checkbox_deny = true;
						}
						else
						{ 
							$checkbox_deny = false;
						}

						if($checkbox_deny == true || $checkbox_allow == true || $checkbox_challenge == true && $status == "allow" || $status == "deny" || $status == "challenge") 
						{
							$lost_pass_url = wp_lostpassword_url();

							$device = $res["device"];
							$to = $email;
							
							$subject = 'Authsafe - Payment Successfully Done';
							$body = '<!doctype html>'.
							'<html>'.
							'<head>'.
								'<meta name="viewport" content="width=device-width">'.
								'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.
								'<title>'.$subject.'</title>'.
								'<style>'.
								'@media only screen and (max-width: 620px) {'.
								'table[class=body] h1 {'.
									'font-size: 28px !important;'.
									'margin-bottom: 10px !important;'.
								'}'.
								'table[class=body] p,'.
										'table[class=body] ul,'.
										'table[class=body] ol,'.
										'table[class=body] td,'.
										'table[class=body] span,'.
										'table[class=body] a {'.
									'font-size: 16px !important;'.
								'}'.
								'table[class=body] .wrapper,'.
										'table[class=body] .article {'.
									'padding: 10px !important;'.
								'}'.
								'table[class=body] .content {'.
									'padding: 0 !important;'.
								'}'.
								'table[class=body] .container {'.
									'padding: 0 !important;'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .main {'.
									'border-left-width: 0 !important;'.
									'border-radius: 0 !important;'.
									'border-right-width: 0 !important;'.
								'}'.
								'table[class=body] .btn table {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .btn a {'.
									'width: 100% !important;'.
								'}'.
								'table[class=body] .img-responsive {'.
									'height: auto !important;'.
									'max-width: 100% !important;'.
									'width: auto !important;'.
								'}'.
								'}'.
								'@media all {'.
								'.ExternalClass {'.
									'width: 100%;'.
								'}'.
								'.ExternalClass,'.
										'.ExternalClass p,'.
										'.ExternalClass span,'.
										'.ExternalClass font,'.
										'.ExternalClass td,'.
										'.ExternalClass div {'.
									'line-height: 100%;'.
								'}'.
								'.apple-link a {'.
									'color: inherit !important;'.
									'font-family: inherit !important;'.
									'font-size: inherit !important;'.
									'font-weight: inherit !important;'.
									'line-height: inherit !important;'.
									'text-decoration: none !important;'.
								'}'.
								'#MessageViewBody a {'.
									'color: inherit;'.
									'text-decoration: none;'.
									'font-size: inherit;'.
									'font-family: inherit;'.
									'font-weight: inherit;'.
									'line-height: inherit;'.
								'}'.
								'.btn-primary table td:hover {'.
									'background-color: #34495e !important;'.
								'}'.
								'.btn-primary a:hover {'.
									'background-color: #34495e !important;'.
									'border-color: #34495e !important;'.
								'}'.
								'}'.
								'</style>'.
							'</head>'.
							'<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">'.
								'<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>'.
								'<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">'.
								'<tr>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
									'<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">'.
									'<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">'.
										'<!-- START CENTERED WHITE CONTAINER -->'.
										'<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">'.
										'<!-- START MAIN CONTENT AREA -->'.
										'<tr>'.
											'<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">'.
											'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0 0 20px 0;">Hi <strong>'.$first_name.' '.$last_name.'</strong>,</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p>Thank you for your recent transaction. Your payment of ['. $Total_Expense .'] for ['. $site_title .'] has been successfully processed. If you have any questions or need assistance, please feel free to contact us.</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Here is some detaile about last Order:</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">1) Billing Email - '.$b_email.'</p>'.
												'</td>'.
												'</tr>'.
												// '<tr>'.
												// '<td>'.
												// 	'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">2) IP - '.$device['ip'].'</p>'.$device['ip']
												// '</td>'.
												// '</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">3) Location - Country('.$b_country.'), State('.$b_state.'), City('.$b_city.')</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">4) Billing Phone Number - '.$b_phone_number.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">5) Total Amount of Transaction - '.$Total_Expense.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">6) Transaction ID - '.$transaction_id.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">7) Order ID - '.$order_id.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">8) Payment Status - '.$payment_status.'</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Thank you for your order. We truly value our loyal customers. Thanks for making us who we are</p>'.
												'</td>'.
												'</tr>'.
												'<tr>'.
												'<td>'.
													'<p style="font-family: sans-serif; font-size: 10px; font-weight: normal; margin: 0; Margin-bottom: 0px; color: #999999;">This email is shot from authsafe.ai.</p>'.
												'</td>'.
												'</tr>'.
											'</table>'.
											'</td>'.
										'</tr>'.
										'<!-- END MAIN CONTENT AREA -->'.
										'</table>'.
										'<!-- START FOOTER -->'.
										'<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">'.
										'<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">'.
											'<tr>'.
											'<td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">'.
												'Powered by <a href="http://authsafe.ai" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">Authsafe.ai</a>.'.
											'</td>'.
											'</tr>'.
										'</table>'.
										'</div>'.
										'<!-- END FOOTER -->'.
									'<!-- END CENTERED WHITE CONTAINER -->'.
									'</div>'.
									'</td>'.
									'<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>'.
								'</tr>'.
								'</table>'.
							'</body>'.
							'</html>';
							$headers = array('Content-Type: text/html; charset=UTF-8');

							$mailResult = wp_mail( $to, $subject, $body, $headers );
							
						} 
						// if($status == "allow" && $severity == "medium" && $checkbox_allow == true) 
						// {
							// die('Medium also working');
						// }
						// if($status == "challenge" && $severity == "high" && $checkbox_challenge == true)
						// {
                            // die('Challenge also working');
						// }
						// if($status == "deny" && $severity == "critical" && $checkbox_deny == true)
						// {
							// die('Deny also working');
						// } 
					}
				}
				
			}
		}
	}

	public function ats_settings_options() {
		
		$options = get_option('ats_options', Authsafe::default_options());
		
		$property_id = (isset($options['ats_property_id']) && !empty($options['ats_property_id'])) ? $options['ats_property_id'] : '';
		
		$property_secret = (isset($options['ats_property_secret']) && !empty($options['ats_property_secret'])) ? $options['ats_property_secret'] : '';
		
		$options_array = array(
			'options'         => $options,
			'ats_property_id' => $property_id,
			'ats_property_secret' => $property_secret,
			// 'pixel_track' 	  => $pixel_track,
			// 'login_track'     => $login_track,
			// 'reset_track'     => $reset_track
		);
		
		return apply_filters('ats_settings_options_array', $options_array);
		
	}

	public function as_login_message( $message ) {
		return $message;
	}

	public function wp_login_failed_track($username, WP_Error $error )
	{
		$device_id = '';
		if (isset($_POST['device_id'])) {
			$device_id = sanitize_text_field($_POST["device_id"]);
		}
		$options = get_option('ats_options', Authsafe::default_options());

		if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
			$user = get_user_by('email',$username);
		} else {
			$user = get_user_by('login',$username);
		}

		require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
		$obj = new AuthSafe\AuthSafe([
			'property_id' => $options['ats_property_id'],
			'property_secret' => $options['ats_property_secret']
		]);

		if ($user) {
			$res = $obj->loginAttempt('login_failed',$user->data->ID,$device_id,array('email'=>$user->data->user_email,'username'=>$user->data->user_login));
		} else {
			$res = $obj->loginAttempt('login_failed','',$device_id);
		}
	}

	// public function wp_logout_track($user_id)
	// {
	// 	$device_id = '';
	// 	if (isset($_REQUEST['did'])) {
	// 		$device_id = sanitize_text_field($_REQUEST['did']);
	// 	}
	// 	$user = get_userdata($user_id);

	// 	$options = get_option('ats_options', Authsafe::default_options());

	// 	require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
	// 	$obj = new AuthSafe\AuthSafe([
	// 	  'property_id' => $options['ats_property_id'],
	// 	  'property_secret' => $options['ats_property_secret']
	// 	]);
	// 	$res = $obj->loginAttempt('logout',$user->data->ID,$device_id,array('email'=>$user->data->user_email,'username'=>$user->data->user_login));
	// 	// echo '<pre';
	// 	// print_r($res);
	// 	// die();	
	// }

	public function password_reset_track(WP_User $user, $new_pass)
	{
		$device_id = '';
		if (isset($_POST['device_id'])) {
			$device_id = sanitize_text_field($_POST["device_id"]);
		}
		$options = get_option('ats_options', Authsafe::default_options());

		require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
		$obj = new AuthSafe\AuthSafe([
		  'property_id' => $options['ats_property_id'],
		  'property_secret' => $options['ats_property_secret']
		]);
		$res = $obj->passwordResetAttempt('reset_password_succeeded',$user->data->ID,$device_id,array('email'=>$user->data->user_email,'username'=>$user->data->user_login));
	}

	public function password_reset_failed_track($errors, $user_data)
	{
		$device_id = '';
		if (isset($_POST['device_id'])) {
			$device_id = sanitize_text_field($_POST["device_id"]);
		}
		if (!$user_data) {
			$res = $obj->passwordResetAttempt('reset_password_failed','',$device_id);
		}
	}

}
