<?php
/**
 * Created by Buffercode.
 * User: M A Vinoth Kumar
 */

/**
 * Append the Version -- Captcha
 */
add_filter( 'fed_plugin_versions', function ( $version ) {
	return array_merge( $version, array( 'captcha' => 'Captcha' ) );
} );

/**
 * Get Captcha form
 *
 * @param string $location Captcha Location.
 *
 * @return string
 */
function fed_get_captcha_form( $location = '' ) {
	$fed_captcha = get_option( 'fed_admin_settings_captcha' );

	if ( $location === 'login' &&
	     isset( $fed_captcha['fed_captcha_in_login_form'] ) &&
	     'Enable' === $fed_captcha['fed_captcha_in_login_form']

	) {
		return '<div id="fedLoginCaptcha"></div>';
	}
	if ( $location === 'register' &&
	     isset( $fed_captcha['fed_captcha_in_register_form'] ) &&
	     'Enable' === $fed_captcha['fed_captcha_in_register_form']
	) {
		return '<div id="fedRegisterCaptcha"></div>';
	}


	return false;
}

/**
 * Get Captcha Site Key
 *
 */
function fed_get_captcha_details() {
	$fed_captcha = get_option( 'fed_admin_settings_captcha' );

	$details = array(
		'fed_captcha_site_key' => '',
		'fed_captcha_enable'   => 'Disable'
	);

	if ( isset( $fed_captcha['fed_captcha_site_key'] ) ) {
		$details['fed_captcha_site_key'] = $fed_captcha['fed_captcha_site_key'];
	}

	if ( ( isset( $fed_captcha['fed_captcha_in_login_form'] ) &&
	       $fed_captcha['fed_captcha_in_login_form'] === 'Enable' )
	     ||
	     ( isset( $fed_captcha['fed_captcha_in_register_form'] ) &&
	       $fed_captcha['fed_captcha_in_register_form'] === 'Enable' )
	) {
		$details['fed_captcha_enable'] = 'Enable';
	}

	return $details;
}
/**
 * Validate Captcha
 */
function fed_validate_captcha( $request, $page ) {
	$fed_captcha = get_option( 'fed_admin_settings_captcha' );
	if (
		( $page === 'login' && $fed_captcha['fed_captcha_in_login_form'] == 'Enable' ) ||
		( $page === 'register' && $fed_captcha['fed_captcha_in_register_form'] == 'Enable' )
	) {
		$secret    = $fed_captcha['fed_captcha_secrete_key'];
		$recaptcha = new \ReCaptcha\ReCaptcha( $secret, new \ReCaptcha\RequestMethod\SocketPost() );
		$resp      = $recaptcha->verify( $request['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] );

		if ( $resp->isSuccess() ) {
			return true;
		}
		wp_send_json_error( array( 'user' => array( 'Invalid Captcha, Please try again' ) ) );
		exit();
	}

	return true;

}

add_action('init', 'fedc_load_text_domain');

function fedc_load_text_domain(  ) {
	load_plugin_textdomain( 'frontend-dashboard-captcha', false, BC_FED_CAPTCHA_PLUGIN_NAME . '/languages' );
}