<?php
/**
 * Frontend Dashboard Captcha.
 *
 * @package Frontend Dashboard.
 */
if ( ! class_exists( 'FEDC_Menu' ) ) {
	/**
	 * Class FEDC_Menu
	 */
	class FEDC_Menu {
		/**
		 * FEDE_Menu constructor.
		 */
		public function __construct() {
			add_filter(
				'fed_admin_dashboard_settings_menu_header', array(
					$this,
					'fed_captcha_admin_dashboard_settings_menu_header',
				)
			);

			add_action( 'fed_admin_settings_login_action', array( $this, 'fed_captcha_save_admin_settings' ) );
			add_action(
				'fed_enqueue_script_style_frontend', array(
					$this,
					'fed_captcha_enqueue_script_style_frontend',
				)
			);
			add_action( 'fed_login_before_validation', array( $this, 'fed_captcha_login_before_validation' ) );
			add_action( 'fed_register_before_validation', array( $this, 'fed_captcha_register_before_validation' ) );
			add_filter( 'fed_convert_php_js_var', array( $this, 'fed_captcha_convert_php_js_var' ) );
			add_filter( 'fed_login_only_filter', array( $this, 'fed_captcha_login_only_filter' ) );
			add_filter( 'fed_register_only_filter', array( $this, 'fed_captcha_register_only_filter' ) );
			add_filter( 'fed_custom_input_fields', array( $this, 'fed_captcha_custom_input_fields' ), 10, 2 );
			add_filter( 'script_loader_tag', array( $this, 'fed_add_async_attribute' ), 10, 2 );
		}

		/**
		 * @param $menu
		 *
		 * @return array
		 */
		public function fed_captcha_admin_dashboard_settings_menu_header( $menu ) {
			return array_merge(
				$menu, array(
					'captcha' => array(
						'icon_class' => 'fa fa-random',
						'name'       => __( 'Captcha', 'frontend-dashboard-captcha' ),
						'callable'   => array( 'object' => $this, 'method' => 'fed_captcha_show_admin_settings' ),
					),
				)
			);
		}

		/**
		 * Captcha Show Admin Settings.
		 */
		public function fed_captcha_show_admin_settings() {
			$fed_admin_options = get_option( 'fed_admin_settings_captcha' );
			?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><?php _e( 'Captcha', 'frontend-dashboard' ) ?></h3>
				</div>
				<div class="panel-body">
					<form method="post"
							class="fed_admin_menu fed_ajax"
							action="<?php echo admin_url( 'admin-ajax.php?action=fed_admin_setting_form' ) ?>">

						<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ) ?>

						<?php echo fed_loader(); ?>

						<input type="hidden"
								name="fed_admin_unique"
								value="fed_admin_settings_captcha"/>

						<div class="container">
							<div class="fed_admin_panel_container">
								<div class="fed_admin_panel_content_wrapper">
									<div class="row">
										<div class="col-md-4 fed_menu_title"><?php _e(
												'Captcha Site Key', 'frontend-dashboard-captcha'
											) ?></div>
										<div class="col-md-4">
											<?php echo fed_input_box(
												'fed_captcha_site_key', array(
												'placeholder' => __(
													'Please enter Captcha Site Key', 'frontend-dashboard-captcha'
												),
												'value'       => isset( $fed_admin_options['fed_captcha_site_key'] ) ? $fed_admin_options['fed_captcha_site_key'] : '',
											), 'single_line'
											) ?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4 fed_menu_title"><?php _e(
												'Captcha Secrete Key', 'frontend-dashboard-captcha'
											) ?></div>
										<div class="col-md-4">
											<?php echo fed_input_box(
												'fed_captcha_secrete_key', array(
												'placeholder' => __(
													'Please enter Captcha Secrete Key', 'frontend-dashboard-captcha'
												),
												'value'       => isset( $fed_admin_options['fed_captcha_secrete_key'] ) ? $fed_admin_options['fed_captcha_secrete_key'] : '',
											), 'single_line'
											) ?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4 fed_menu_title"><?php _e(
												'Enable in Login Form', 'frontend-dashboard-captcha'
											) ?></div>
										<div class="col-md-4">
											<?php echo fed_input_box(
												'fed_captcha_in_login_form', array(
												'value'         => isset( $fed_admin_options['fed_captcha_in_login_form'] ) ? $fed_admin_options['fed_captcha_in_login_form'] : 'Disable',
												'default_value' => 'Enable',
											), 'checkbox'
											) ?>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4 fed_menu_title"><?php _e(
											'Enable in Register Form', 'frontend-dashboard-captcha'
										) ?></div>
									<div class="col-md-4">
										<?php echo fed_input_box(
											'fed_captcha_in_register_form', array(
											'value'         => isset( $fed_admin_options['fed_captcha_in_register_form'] ) ? $fed_admin_options['fed_captcha_in_register_form'] : 'Disable',
											'default_value' => 'Enable',
										), 'checkbox'
										) ?>
									</div>
								</div>
							</div>
						</div>
						<div class="row p-t-b-20">
							<div class="col-md-12"><?php _e(
									'Note: You can get the Captcha key here:', 'frontend-dashboard-captcha'
								) ?>
								<a href="<?php echo 'https://www.google.com/recaptcha/admin'; ?>"
										target="_blank"><?php _e(
										'Frontend Dashboard Captcha', 'frontend-dashboard-captcha'
									) ?></a>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<input type="submit" class="btn btn-primary"
										value="<?php _e( 'Submit', 'frontend-dashboard-captcha' ) ?>"/>
							</div>
						</div>
					</form>
				</div>
			</div>

			<?php

		}

		/**
		 * @param $request
		 */
		public function fed_captcha_save_admin_settings( $request ) {
			if ( isset( $request['fed_admin_unique'] ) && 'fed_admin_settings_captcha' === $request['fed_admin_unique'] ) {
				$request = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

				$fed_admin_settings_captcha = array(
					'fed_captcha_site_key'         => isset( $request['fed_captcha_site_key'] ) ? sanitize_text_field(
						$request['fed_captcha_site_key']
					) : '',
					'fed_captcha_secrete_key'      => isset( $request['fed_captcha_secrete_key'] ) ? sanitize_text_field(
						$request['fed_captcha_secrete_key']
					) : '',
					'fed_captcha_in_login_form'    => isset( $request['fed_captcha_in_login_form'] ) ? sanitize_text_field(
						$request['fed_captcha_in_login_form']
					) : 'Disable',
					'fed_captcha_in_register_form' => isset( $request['fed_captcha_in_register_form'] ) ? sanitize_text_field(
						$request['fed_captcha_in_register_form']
					) : 'Disable',
				);

				apply_filters( 'fed_admin_settings_captcha', $fed_admin_settings_captcha );

				update_option( 'fed_admin_settings_captcha', $fed_admin_settings_captcha );

				wp_send_json_success(
					array(
						'message' => 'Captcha Updated Successfully',
					)
				);
			}
		}

		/**
		 * @param $login
		 *
		 * @return mixed
		 */
		public function fed_captcha_login_only_filter( $login ) {
			$captcha = fed_get_captcha_form( 'login' );
			if ( $captcha ) {
				$login['content']['captcha'] = array(
					'name'        => '',
					'input'       => fed_input_box(
						'login', array(
						'content' => $captcha,
					), 'content'
					),
					'input_order' => 999,
				);
			}

			return $login;
		}

		/**
		 * @param $register
		 *
		 * @return mixed
		 */
		public function fed_captcha_register_only_filter( $register ) {
			if ( $captcha = fed_get_captcha_form( 'register' ) ) {
				$register['content']['captcha'] = array(
					'name'        => '',
					'input'       => fed_input_box(
						'register', array(
						'content' => $captcha,
					), 'content'
					),
					'input_order' => 999,
				);
			}

			return $register;
		}

		/**
		 * @param $input
		 * @param $attr
		 *
		 * @return mixed
		 */
		public function fed_captcha_custom_input_fields( $input, $attr ) {
			if ( $attr['input_type'] === 'content' ) {
				$input = $attr['content'];
			}

			return $input;
		}

		/**
		 * @param $post
		 */
		public function fed_captcha_login_before_validation( $post ) {
			fed_validate_captcha( $post, 'login' );
		}

		/**
		 * @param $post
		 */
		public function fed_captcha_register_before_validation( $post ) {
			fed_validate_captcha( $post, 'register' );
		}

		public function fed_captcha_enqueue_script_style_frontend() {
			wp_enqueue_script(
				'recaptcha', 'https://www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit&hl=en',
				array(), BC_FED_PLUGIN_VERSION, 'all'
			);
		}

		/**
		 * @param $convert
		 *
		 * @return mixed
		 */
		public function fed_captcha_convert_php_js_var( $convert ) {
			$convert['fed_captcha_details'] = fed_get_captcha_details();

			return $convert;
		}


		/**
		 * Add Async Attribute
		 *
		 * @param  string $tag  Tag
		 * @param  string $handle  Handle
		 *
		 * @return mixed
		 */
		public function fed_add_async_attribute( $tag, $handle ) {
			if ( 'recaptcha' !== $handle ) {
				return $tag;
			}

			return str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}

	}

	new FEDC_Menu();
}