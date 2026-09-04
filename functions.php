<?php
/**
 * Append the Version -- Captcha
 */
add_filter(
	'fed_plugin_versions', function ( $version ) {
	return array_merge( $version, array( 'captcha' => 'Captcha' ) );
}
);

/**
 * Get Captcha form
 *
 * @param  string $location  Captcha Location.
 *
 * @return string
 */
function fed_get_captcha_form( $location = '' ) {
	$fed_captcha = get_option( 'fed_admin_settings_captcha' );

	if (
		'login' === $location &&
		isset( $fed_captcha['fed_captcha_in_login_form'] ) &&
		'Enable' === $fed_captcha['fed_captcha_in_login_form']

	) {
		return '<div id="fedLoginCaptcha"></div>';
	}
	if (
		'register' === $location &&
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
		'fed_captcha_enable'   => 'Disable',
	);

	if ( isset( $fed_captcha['fed_captcha_site_key'] ) ) {
		$details['fed_captcha_site_key'] = $fed_captcha['fed_captcha_site_key'];
	}

	if (
		( isset( $fed_captcha['fed_captcha_in_login_form'] ) &&
		  'Enable' === $fed_captcha['fed_captcha_in_login_form'] )
		||
		( isset( $fed_captcha['fed_captcha_in_register_form'] ) &&
		  'Enable' === $fed_captcha['fed_captcha_in_register_form'] )
	) {
		$details['fed_captcha_enable'] = 'Enable';
	}

	return $details;
}

/**
 * Validate Captcha
 *
 * @param  array  $request  Request.
 * @param  string $page  Page.
 *
 * @return bool
 */
function fed_validate_captcha( $request, $page ) {
	$fed_captcha = get_option( 'fed_admin_settings_captcha' );
	if ( ! is_array( $fed_captcha ) ) {
		return true;
	}

	$in_login    = isset( $fed_captcha['fed_captcha_in_login_form'] ) && 'Enable' === $fed_captcha['fed_captcha_in_login_form'];
	$in_register = isset( $fed_captcha['fed_captcha_in_register_form'] ) && 'Enable' === $fed_captcha['fed_captcha_in_register_form'];

	if ( ( 'login' === $page && $in_login ) || ( 'register' === $page && $in_register ) ) {
		$secret = isset( $fed_captcha['fed_captcha_secrete_key'] ) ? $fed_captcha['fed_captcha_secrete_key'] : '';
		if ( empty( $secret ) ) {
			return true;
		}

		$captcha_response = isset( $request['g-recaptcha-response'] ) ? $request['g-recaptcha-response'] : '';

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify', array(
				'body' => array(
					'secret'   => $secret,
					'response' => $captcha_response,
				),
			)
		);

		if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
			$decode = json_decode( $response['body'], true );
			if ( ! empty( $decode['success'] ) ) {
				return true;
			}
		}

		wp_send_json_error( array( 'user' => array( __( 'Invalid Captcha, Please try again', 'frontend-dashboard-captcha' ) ) ) );
		exit();
	}

	return true;
}

add_action( 'init', 'fedc_load_text_domain' );

/**
 * Text Domain
 */
function fedc_load_text_domain() {
	load_plugin_textdomain( 'frontend-dashboard-captcha', false, BC_FED_CAPTCHA_PLUGIN_NAME . '/languages' );
}