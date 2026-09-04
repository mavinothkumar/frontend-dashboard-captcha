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
	if (
		( 'login' === $page && 'Enable' == $fed_captcha['fed_captcha_in_login_form'] ) ||
		( 'register' === $page && 'Enable' == $fed_captcha['fed_captcha_in_register_form'] )
	) {
		$secret = $fed_captcha['fed_captcha_secrete_key'];


		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify', array(
				'body' => array(
					'secret'   => $secret,
					'response' => $request['g-recaptcha-response'],
				),
			)
		);

		if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
			$decode = json_decode( $response['body'], true );
			if ( $decode['success'] && ! empty( $decode['success'] ) ) {
				return true;
			}
		}

		wp_send_json_error( array( 'user' => array( 'Invalid Captcha, Please try again' ) ) );
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