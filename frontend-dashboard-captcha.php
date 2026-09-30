<?php
/**
 * Plugin Name: Frontend Dashboard Captcha
 * Plugin URI: https://buffercode.com/plugin/frontend-dashboard-captcha
 * Description: Frontend Dashboard Captcha WordPress plugin is a supportive plugin for Frontend Dashboard again spam in
 * Login and Register form.
 * Version: 3.0.2
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Requires Plugins: frontend-dashboard
 * Author: vinoth06
 * Author URI: http://buffercode.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frontend-dashboard-captcha
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$fed_check = get_option( 'fed_plugin_version' );

include_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( $fed_check && is_plugin_active( 'frontend-dashboard/frontend-dashboard.php' ) ) {

	/**
	 * Version Number
	 */
	define( 'BC_FED_CAPTCHA_PLUGIN_VERSION', '3.0.2' );

	/**
	 * App Name
	 */
	define( 'BC_FED_CAPTCHA_APP_NAME', 'Frontend Dashboard Captcha' );

	/**
	 * Root Path
	 */
	define( 'BC_FED_CAPTCHA_PLUGIN', __FILE__ );
	/**
	 * Plugin Base Name
	 */
	define( 'BC_FED_CAPTCHA_PLUGIN_BASENAME', plugin_basename( BC_FED_CAPTCHA_PLUGIN ) );
	/**
	 * Plugin Name
	 */
	define( 'BC_FED_CAPTCHA_PLUGIN_NAME', trim( dirname( BC_FED_CAPTCHA_PLUGIN_BASENAME ), '/' ) );
	/**
	 * Plugin Directory
	 */
	define( 'BC_FED_CAPTCHA_PLUGIN_DIR', untrailingslashit( dirname( BC_FED_CAPTCHA_PLUGIN ) ) );


	require_once BC_FED_CAPTCHA_PLUGIN_DIR . '/vendor/captcha/autoload.php';
	require_once BC_FED_CAPTCHA_PLUGIN_DIR . '/menu/FEDC_Menu.php';
	require_once BC_FED_CAPTCHA_PLUGIN_DIR . '/functions.php';
} else {
	add_action( 'admin_notices', 'fed_global_admin_notification_captcha' );
	/**
	 * Admin Notification
	 */
	function fed_global_admin_notification_captcha() {
		?>
		<div class="notice notice-warning">
			<p>
				<b>
					<?php
					printf(
						/* translators: %s: Link to Frontend Dashboard plugin */
						esc_html__( 'Please install %s to use this plugin [Frontend Dashboard Captcha]', 'frontend-dashboard-captcha' ),
						'<a href="' . esc_url( 'https://buffercode.com/plugin/frontend-dashboard' ) . '">' . esc_html__( 'Frontend Dashboard', 'frontend-dashboard-captcha' ) . '</a>'
					);
					?>
				</b>
			</p>
		</div>
		<?php
	}
}
