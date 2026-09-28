<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * @return string|bool
 */
function fed_get_captcha_form( $location = '' ) {
	$fed_captcha = get_option( 'fed_admin_settings_captcha' );
	if ( ! is_array( $fed_captcha ) ) {
		return false;
	}

	$version     = isset( $fed_captcha['fed_captcha_version'] ) ? $fed_captcha['fed_captcha_version'] : 'v2';
	$in_login    = isset( $fed_captcha['fed_captcha_in_login_form'] ) && 'Enable' === $fed_captcha['fed_captcha_in_login_form'];
	$in_register = isset( $fed_captcha['fed_captcha_in_register_form'] ) && 'Enable' === $fed_captcha['fed_captcha_in_register_form'];

	if ( ( 'login' === $location && $in_login ) || ( 'register' === $location && $in_register ) ) {
		if ( 'v3' === $version ) {
			// For v3, no visible checkbox container should be rendered. A hidden input receives the execution token.
			return '<input type="hidden" name="g-recaptcha-response" class="fed_recaptcha_v3_token" value="" />';
		}

		// For v2, render the checkbox container
		if ( 'login' === $location ) {
			return '<div id="fedLoginCaptcha"></div>';
		}
		if ( 'register' === $location ) {
			return '<div id="fedRegisterCaptcha"></div>';
		}
	}

	return false;
}

/**
 * Get Captcha Site Key & Config
 *
 * @return array
 */
function fed_get_captcha_details() {
	$fed_captcha = get_option( 'fed_admin_settings_captcha' );

	$details = array(
		'fed_captcha_version'     => isset( $fed_captcha['fed_captcha_version'] ) ? $fed_captcha['fed_captcha_version'] : 'v2',
		'fed_captcha_site_key'    => isset( $fed_captcha['fed_captcha_site_key'] ) ? trim( $fed_captcha['fed_captcha_site_key'] ) : '',
		'fed_captcha_enable'      => 'Disable',
		'fed_captcha_in_login'    => isset( $fed_captcha['fed_captcha_in_login_form'] ) ? $fed_captcha['fed_captcha_in_login_form'] : 'Disable',
		'fed_captcha_in_register' => isset( $fed_captcha['fed_captcha_in_register_form'] ) ? $fed_captcha['fed_captcha_in_register_form'] : 'Disable',
		'fed_captcha_v3_score'    => isset( $fed_captcha['fed_captcha_v3_score'] ) ? (float) $fed_captcha['fed_captcha_v3_score'] : 0.5,
		'fed_captcha_badge_pos'   => isset( $fed_captcha['fed_captcha_v3_badge_position'] ) ? $fed_captcha['fed_captcha_v3_badge_position'] : 'bottomright',
	);

	if (
		( isset( $fed_captcha['fed_captcha_in_login_form'] ) && 'Enable' === $fed_captcha['fed_captcha_in_login_form'] )
		||
		( isset( $fed_captcha['fed_captcha_in_register_form'] ) && 'Enable' === $fed_captcha['fed_captcha_in_register_form'] )
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
		$secret = isset( $fed_captcha['fed_captcha_secrete_key'] ) ? trim( $fed_captcha['fed_captcha_secrete_key'] ) : '';
		if ( empty( $secret ) ) {
			return true;
		}

		$captcha_response = isset( $request['g-recaptcha-response'] ) ? trim( $request['g-recaptcha-response'] ) : '';

		if ( empty( $captcha_response ) ) {
			wp_send_json_error( array( 'user' => array( __( 'Please complete the reCAPTCHA verification.', 'frontend-dashboard-captcha' ) ) ) );
			exit();
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify', array(
				'timeout' => 15,
				'body'    => array(
					'secret'   => $secret,
					'response' => $captcha_response,
					'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'user' => array( __( 'Unable to connect to reCAPTCHA service. Please try again.', 'frontend-dashboard-captcha' ) ) ) );
			exit();
		}

		$body   = wp_remote_retrieve_body( $response );
		$decode = json_decode( $body, true );

		if ( ! is_array( $decode ) || empty( $decode['success'] ) ) {
			$error_codes = isset( $decode['error-codes'] ) ? implode( ', ', (array) $decode['error-codes'] ) : '';
			$msg         = __( 'reCAPTCHA verification failed. Please try again.', 'frontend-dashboard-captcha' );
			if ( false !== strpos( $error_codes, 'invalid-input-secret' ) ) {
				$msg = __( 'Invalid reCAPTCHA secret key. Please check your admin settings.', 'frontend-dashboard-captcha' );
			}
			wp_send_json_error( array( 'user' => array( $msg ) ) );
			exit();
		}

		$version = isset( $fed_captcha['fed_captcha_version'] ) ? $fed_captcha['fed_captcha_version'] : 'v2';
		if ( 'v3' === $version ) {
			$threshold = isset( $fed_captcha['fed_captcha_v3_score'] ) ? (float) $fed_captcha['fed_captcha_v3_score'] : 0.5;
			$score     = isset( $decode['score'] ) ? (float) $decode['score'] : 1.0;

			if ( $score < $threshold ) {
				wp_send_json_error( array( 'user' => array( __( 'reCAPTCHA verification failed. Trust score below threshold (anti-spam protection).', 'frontend-dashboard-captcha' ) ) ) );
				exit();
			}
		}

		return true;
	}

	return true;
}