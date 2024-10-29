<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://authsafe.ai
 * @since      2.0.0
 *
 * @package    Authsafe
 * @subpackage Authsafe/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php // Google Analytics - Settings Display

   if (!function_exists('add_action')) die(); 

?>
<style>
		.btn {
			background-color: #ccc;
			color: #000;
			padding: 5px 10px;
			border: none;
			cursor: pointer;
		}

		.active {
		   background-color: #4B9CD3;
		}

		.content {
		    margin-top: 20px;
		}

</style>

<div class="wrap">
	
	<h1><?php echo AUTHSAFE_NAME; ?> <small><?php echo 'v'. AUTHSAFE_VERSION; ?></small></h1>
	
	<!-- <div class="ats-toggle-all"><a href="<?php echo admin_url(AUTHSAFE_PATH); ?>"><?php esc_html_e('Toggle all panels', 'ats-authsafe'); ?></a></div> -->
    
	<button id="button1" class="btn active">Login Policy settings</button>
    <button id="button2" class="btn">Payment Policy settings</button>

	<form id="myForm" method="post" action="options.php">

	  <?php settings_fields('ats_plugin_policy_options'); 
	   $disable = false;
	    if (is_plugin_active('woocommerce/woocommerce.php')) {
			$disable = true;
		} else {
			$disable = false;
		} 
		// echo "PHP Disable Value: " . ($disable ? 'true' : 'false');
	  ?>
		
		<div class="metabox-holder">
				
			<div id="authsafePortletSettings" class="postbox">
					
				<h2 class="ats-portlet-title"><?php esc_html_e('AuthSafe Policy Settings', 'authsafe'); ?></h2>	
				<?php
				if($disable == false)
				{
					echo '<p class="ats-portlet-title" style="color: red;">Important: Our Authsafe requires WooCommerce to function. Please ensure WooCommerce is installed and activated to utilize this plugin effectively. If you need assistance, feel free to contact us.</p>';
				}
                ?>	
				

				<div class="ats-portlet toggle<?php if (!isset($_GET['settings-updated'])) echo ' default-hidden'; ?>">

				       <div id="div1" class="content" style="display: ;">
							<table id="table1" class="widefat">
								<tr>
									<th>Below you can set the AuthSafe incident policies. These policies are defined to help the application understand on how to handle AuthSafe responses.</th>
								</tr>
								<tr>
									<th colspan="2">1. Allow</th>
								</tr>
								<tr>
									<td><em>AuthSafe responds with Allow when the risk score is <b>Medium</b>. Below you can set whether you want AuthSafe to send device approval email when the risk score is observed to be medium.</em></td>
								</tr>
								<tr>
									<td><input id="ats_policy_options[ats_medium_email]" name="ats_policy_options[ats_medium_email]" type="checkbox" value="1" <?php if (isset($ats_policy_options['ats_medium_email'])) echo ((esc_attr($ats_policy_options['ats_medium_email'])==1)?'checked="checked"':''); ?>><label for="ats_policy_options[ats_medium_email]"><?php esc_html_e('Send Device Approval E-mail', 'authsafe') ?></label></td>
								</tr>
								<tr>
									<th colspan="2">2. Challenge</th>
								</tr>
								<tr>
									<td><em>AuthSafe responds with Challenge when the risk score is <b>High</b>. Below you can set whether you want AuthSafe to send password reset email when the risk score is observed to be high.</em></td>
								</tr>
								<tr>
									<td><input id="ats_policy_options[ats_challenge_email]" name="ats_policy_options[ats_challenge_email]" type="checkbox" value="1" <?php if (isset($ats_policy_options['ats_challenge_email'])) echo ((esc_attr($ats_policy_options['ats_challenge_email'])==1)?'checked="checked"':''); ?>><label for="ats_policy_options[ats_challenge_email]"><?php esc_html_e('Send Password Reset E-mail', 'authsafe') ?></label></td>
								</tr>
								<tr>
									<th colspan="2">3. Deny</th>
								</tr>
								<tr>
									<td><em>AuthSafe responds with Deny when the risk score is <b>Critical</b>. Below you can set whether you want AuthSafe to send password reset email when the risk score is observed to be high.</em></td>
								</tr>
								<tr>
									<td><input id="ats_policy_options[ats_deny_email]" name="ats_policy_options[ats_deny_email]" type="checkbox" value="1" <?php if (isset($ats_policy_options['ats_deny_email'])) echo ((esc_attr($ats_policy_options['ats_deny_email'])==1)?'checked="checked"':''); ?>><label for="ats_policy_options[ats_deny_email]"><?php esc_html_e('Send Password Reset E-mail', 'authsafe') ?></label></td>
								</tr>
								
							</table>
                        </div>

						<div id="div2" class="content" style="display: none;">
						    <table id="table2" class="widefat">
							<tr>
									<th>Below you can set the AuthSafe Transaction policies. These policies are defined to help the application understand on how to handle AuthSafe responses.</th>
								</tr>
								<tr>
									<th colspan="2">1. Payment Allow</th>
								</tr>
								<tr>
									<td><em>AuthSafe responds with Allow when the risk score is <b>Medium</b>. Below you can set whether you want AuthSafe to send device approval email when the risk score is observed to be medium.</em></td>
								</tr>
								<tr>
								<td><input id="ats_policy_options[ats_allow_payment_email]" name="ats_policy_options[ats_allow_payment_email]" type="checkbox" value="1" <?php if (isset($ats_policy_options['ats_allow_payment_email'])) echo ((esc_attr($ats_policy_options['ats_allow_payment_email'])==1)?'checked="checked"':''); ?>><label for="ats_policy_options[ats_allow_payment_email]"><?php esc_html_e('Send Transaction Unusual Activity E-mail', 'authsafe') ?></label></td>
								</tr>
								<tr>
								<tr>
									<th colspan="2">2. Payment Challenge</th>
								</tr>
								<tr>
									<td><em>AuthSafe responds with Challenge when the risk score is <b>High</b>. Below you can set whether you want AuthSafe to send device approval email when the risk score is observed to be medium.</em></td>
								</tr>
								<tr>
								<td><input id="ats_policy_options[ats_challenge_payment_email]" name="ats_policy_options[ats_challenge_payment_email]" type="checkbox" value="1" <?php if (isset($ats_policy_options['ats_challenge_payment_email'])) echo ((esc_attr($ats_policy_options['ats_challenge_payment_email'])==1)?'checked="checked"':''); ?>><label for="ats_policy_options[ats_challenge_payment_email]"><?php esc_html_e('Send Transaction Challenge Activity E-mail', 'authsafe') ?></label></td>
								</tr>
								<tr>

									<th colspan="2">3. Payment Deny</th>
								</tr>
								<tr>
									<td><em>AuthSafe responds with Deny when the risk score is <b>Critical</b>. Below you can set whether you want AuthSafe to send password reset email when the risk score is observed to be critical.</em></td>
								</tr>
								<tr>
									<td><input id="ats_policy_options[ats_deny_payment_email]" name="ats_policy_options[ats_deny_payment_email]" type="checkbox" value="1" <?php if (isset($ats_policy_options['ats_deny_payment_email'])) echo ((esc_attr($ats_policy_options['ats_deny_payment_email'])==1)?'checked="checked"':''); ?>><label for="ats_policy_options['ats_deny_payment_email]"><?php esc_html_e('Send Transaction Suspicious Activity E-mail', 'authsafe') ?></label></td>
								</tr>
								
                            </table>
                        </div>

							<table  class="widefat">
							<tr>
									<td><hr></td>
									</tr>
									<tr>
										<th colspan="2"><strong>Shadow Mode</strong></th>
									</tr>
									<tr>
										<td><em>AuthSafe will not take any action against users regardless of their risk.</em></td>
									</tr>
									<tr>
										<td><input id="ats_policy_options[ats_shadow_mode]" name="ats_policy_options[ats_shadow_mode]" type="checkbox" value="1" <?php if (isset($ats_policy_options['ats_shadow_mode'])) echo ((esc_attr($ats_policy_options['ats_shadow_mode'])==1)?'checked="checked"':''); ?>><label for="ats_policy_options[ats_shadow_mode]"><?php esc_html_e('Enable Shadow Mode', 'authsafe') ?></label></td>
									</tr>
									<tr>
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
<script type="text/javascript">
  
	var button1 = document.getElementById("button1");
	var button2 = document.getElementById("button2");
	var div1 = document.getElementById("div1");
	var div2 = document.getElementById("div2");

	// for disable or able 

	document.addEventListener('DOMContentLoaded', function() {

		button1.addEventListener("click", function() {
		div1.style.display = "block";
		div2.style.display = "none";
		button1.classList.add("active");
		button2.classList.remove("active");
		});

		button2.addEventListener("click", function() {
		div1.style.display = "none";
		div2.style.display = "block";
		button1.classList.remove("active");
		button2.classList.add("active");
		});
		// Replace 'your_condition_variable' with the actual condition you want to check
		
	
		var condition = <?php echo json_encode($disable); ?>;
		console.log(condition);

		// Select all checkbox input elements
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');

		// Loop through checkboxes and set the 'disabled' property based on the condition
		checkboxes.forEach(function(checkbox) {
			checkbox.disabled = !condition;
		});
	});


</script>
