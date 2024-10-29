<?php

/**
 * This file is used to markup the AuthSafe Settings page of the plugin.
 *
 * @link       https://authsafe.ai
 * @since      2.0.0
 *
 * @package    Authsafe
 * @subpackage Authsafe/admin/partials
 */
?>

<?php // AuthSafe Configuration

if (!function_exists('add_action')) die(); ?>

<div class="wrap">
	
	<h1><?php echo AUTHSAFE_NAME; ?> <small><?php echo 'v'. AUTHSAFE_VERSION; ?></small></h1>
	
	<!-- <div class="ats-toggle-all"><a href="<?php //echo admin_url(AUTHSAFE_PATH); ?>"><?php //esc_html_e('Toggle all panels', 'ats-authsafe'); ?></a></div> -->

	<form method="post" action="options.php">

		<?php settings_fields('ats_plugin_options'); ?>
		
		<div class="metabox-holder">
				
			<div id="authsafePortletSettings" class="postbox">
					
				<h2 class="ats-portlet-title"><?php esc_html_e('Property Settings', 'authsafe'); ?></h2>
				
				<div class="ats-portlet toggle<?php if (!isset($_GET['settings-updated'])) echo ' default-hidden'; ?>">
						
					<table class="widefat">
						<tr>
							<th><label for="ats_options[ats_property_id]"><?php esc_html_e('Property ID', 'authsafe') ?></label></th>
							<td><input id="ats_options[ats_property_id]" name="ats_options[ats_property_id]" type="text" size="30" maxlength="30" value="<?php if (isset($ats_options['ats_property_id'])) echo esc_attr($ats_options['ats_property_id']); ?>"><p class="help-block"><em>Property ID is unique 16 digit number for your property. Property ID is required by AuthSafe for API requests authentication along with Property Secret.</em></p></td>
						</tr>
						<tr>
							<th><label for="ats_options[ats_property_secret]"><?php esc_html_e('Property Secret', 'authsafe') ?></label></th>
							<td><input id="ats_options[ats_property_secret]" name="ats_options[ats_property_secret]" type="text" size="30" maxlength="30" value="<?php if (isset($ats_options['ats_property_secret'])) echo esc_attr($ats_options['ats_property_secret']); ?>"><p class="help-block"><em>Property Secret is a unique secret key which is required by AuthSafe for API requests authentication along with Property ID.</em></p></td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'authsafe'); ?>" />
							</td>
						</tr>
					</table>
					
				</div>
				
			</div>
			
		</div>

	</form>
	
</div>