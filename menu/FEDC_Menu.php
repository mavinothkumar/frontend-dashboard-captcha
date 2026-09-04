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
						'icon_class' => 'fas fa-shield-alt',
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
			if ( ! is_array( $fed_admin_options ) ) {
				$fed_admin_options = array();
			}

			$tabs = apply_filters(
				'fed_captcha_admin_settings_tabs',
				array(
					'fed_captcha_general' => array(
						'icon'      => 'fas fa-shield-alt',
						'name'      => __( 'reCAPTCHA Settings', 'frontend-dashboard-captcha' ),
						'callable'  => array( 'object' => $this, 'method' => 'fed_captcha_general_tab' ),
						'arguments' => $fed_admin_options,
					),
				)
			);

			fed_common_layouts_admin_settings( $fed_admin_options, $tabs );
		}

		/**
		 * Captcha General Tab.
		 *
		 * @param array $fed_admin_options Admin Options.
		 */
		public function fed_captcha_general_tab( $fed_admin_options ) {
			$site_key    = isset( $fed_admin_options['fed_captcha_site_key'] ) ? $fed_admin_options['fed_captcha_site_key'] : '';
			$secret_key  = isset( $fed_admin_options['fed_captcha_secrete_key'] ) ? $fed_admin_options['fed_captcha_secrete_key'] : '';
			$in_login    = isset( $fed_admin_options['fed_captcha_in_login_form'] ) ? $fed_admin_options['fed_captcha_in_login_form'] : 'Disable';
			$in_register = isset( $fed_admin_options['fed_captcha_in_register_form'] ) ? $fed_admin_options['fed_captcha_in_register_form'] : 'Disable';
			?>
			<form method="post"
				  class="fed_admin_menu fed_ajax space-y-6"
				  action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_admin_setting_form' ) ); ?>">

				<?php fed_wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>
				<?php echo fed_loader(); ?>

				<input type="hidden" name="fed_admin_unique" value="fed_admin_settings_captcha"/>

				<!-- Info Banner -->
				<div class="p-4 bg-indigo-50/70 border border-indigo-100/80 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
					<div class="flex items-start gap-3">
						<div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm shrink-0 mt-0.5 sm:mt-0">
							<i class="fas fa-info-circle"></i>
						</div>
						<div>
							<h4 class="text-xs font-bold text-indigo-950 m-0"><?php esc_html_e( 'Google reCAPTCHA v2 Integration', 'frontend-dashboard-captcha' ); ?></h4>
							<p class="text-[11px] text-indigo-700/90 m-0 mt-0.5">
								<?php esc_html_e( 'Requires Google reCAPTCHA v2 ("I\'m not a robot" Checkbox) keys to protect login and registration forms.', 'frontend-dashboard-captcha' ); ?>
							</p>
						</div>
					</div>
					<a href="https://www.google.com/recaptcha/admin"
					   target="_blank"
					   rel="noopener noreferrer"
					   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors shrink-0 no-underline shadow-2xs">
						<span><?php esc_html_e( 'Get API Keys', 'frontend-dashboard-captcha' ); ?></span>
						<i class="fas fa-external-link-alt text-[10px]"></i>
					</a>
				</div>

				<!-- API Credentials -->
				<div class="space-y-4">
					<div class="pb-2 border-b border-slate-100">
						<h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 m-0"><?php esc_html_e( 'API Credentials', 'frontend-dashboard-captcha' ); ?></h4>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
						<div class="space-y-1.5">
							<label for="fed_captcha_site_key" class="block text-xs font-bold text-slate-700">
								<?php esc_html_e( 'Site Key', 'frontend-dashboard-captcha' ); ?>
							</label>
							<div class="flex items-center rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-indigo-500 transition-all overflow-hidden shadow-2xs">
								<span class="px-3.5 py-2.5 text-slate-400 text-xs bg-slate-100/70 border-r border-slate-200/80 shrink-0 flex items-center justify-center">
									<i class="fas fa-key"></i>
								</span>
								<input type="text"
									   name="fed_captcha_site_key"
									   id="fed_captcha_site_key"
									   value="<?php echo esc_attr( $site_key ); ?>"
									   placeholder="<?php esc_attr_e( 'Enter your Google reCAPTCHA Site Key', 'frontend-dashboard-captcha' ); ?>"
									   class="w-full px-3.5 py-2.5 text-xs text-slate-800 font-mono"
									   style="border: none !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
							</div>
							<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Public site key provided in Google reCAPTCHA Console.', 'frontend-dashboard-captcha' ); ?></p>
						</div>

						<div class="space-y-1.5">
							<label for="fed_captcha_secrete_key" class="block text-xs font-bold text-slate-700">
								<?php esc_html_e( 'Secret Key', 'frontend-dashboard-captcha' ); ?>
							</label>
							<div class="flex items-center rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-indigo-500 transition-all overflow-hidden shadow-2xs">
								<span class="px-3.5 py-2.5 text-slate-400 text-xs bg-slate-100/70 border-r border-slate-200/80 shrink-0 flex items-center justify-center">
									<i class="fas fa-lock"></i>
								</span>
								<input type="text"
									   name="fed_captcha_secrete_key"
									   id="fed_captcha_secrete_key"
									   value="<?php echo esc_attr( $secret_key ); ?>"
									   placeholder="<?php esc_attr_e( 'Enter your Google reCAPTCHA Secret Key', 'frontend-dashboard-captcha' ); ?>"
									   class="w-full px-3.5 py-2.5 text-xs text-slate-800 font-mono"
									   style="border: none !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
							</div>
							<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Keep this secret key safe. Used for server-side verification.', 'frontend-dashboard-captcha' ); ?></p>
						</div>
					</div>
				</div>

				<!-- Form Protection Toggles -->
				<div class="space-y-4 pt-2">
					<div class="pb-2 border-b border-slate-100">
						<h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 m-0"><?php esc_html_e( 'Form Protection', 'frontend-dashboard-captcha' ); ?></h4>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<label class="p-4 bg-slate-50/80 hover:bg-slate-100/70 border border-slate-200/80 rounded-2xl flex items-start justify-between gap-4 cursor-pointer transition-all">
							<div class="space-y-1">
								<div class="flex items-center gap-2">
									<i class="fas fa-sign-in-alt text-slate-500 text-xs"></i>
									<span class="text-xs font-bold text-slate-800 select-none"><?php esc_html_e( 'Login Form', 'frontend-dashboard-captcha' ); ?></span>
								</div>
								<p class="text-[11px] text-slate-500 select-none m-0"><?php esc_html_e( 'Display reCAPTCHA on login form to prevent brute-force attacks.', 'frontend-dashboard-captcha' ); ?></p>
							</div>
							<input type="checkbox"
								   name="fed_captcha_in_login_form"
								   value="Enable"
								   <?php checked( $in_login, 'Enable' ); ?>
								   class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer mt-0.5" />
						</label>

						<label class="p-4 bg-slate-50/80 hover:bg-slate-100/70 border border-slate-200/80 rounded-2xl flex items-start justify-between gap-4 cursor-pointer transition-all">
							<div class="space-y-1">
								<div class="flex items-center gap-2">
									<i class="fas fa-user-plus text-slate-500 text-xs"></i>
									<span class="text-xs font-bold text-slate-800 select-none"><?php esc_html_e( 'Register Form', 'frontend-dashboard-captcha' ); ?></span>
								</div>
								<p class="text-[11px] text-slate-500 select-none m-0"><?php esc_html_e( 'Display reCAPTCHA on registration form to prevent spam user signups.', 'frontend-dashboard-captcha' ); ?></p>
							</div>
							<input type="checkbox"
								   name="fed_captcha_in_register_form"
								   value="Enable"
								   <?php checked( $in_register, 'Enable' ); ?>
								   class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer mt-0.5" />
						</label>
					</div>
				</div>

				<!-- Submit Button -->
				<div class="pt-4 border-t border-slate-100 flex items-center justify-end">
					<button type="submit" class="fed-btn-primary h-11 inline-flex items-center justify-center gap-2 px-6 rounded-xl font-semibold text-xs tracking-wide shadow-sm transition-all active:scale-95 cursor-pointer">
						<i class="fas fa-save text-xs" style="color: #ffffff !important;"></i>
						<span style="color: #ffffff !important;"><?php esc_html_e( 'Save Changes', 'frontend-dashboard' ); ?></span>
					</button>
				</div>
			</form>
			<?php
		}

		/**
		 * @param $request
		 */
		public function fed_captcha_save_admin_settings( $request ) {
			if ( isset( $request['fed_admin_unique'] ) && 'fed_admin_settings_captcha' === $request['fed_admin_unique'] ) {
				$post_data = wp_unslash( $_POST );
				fed_verify_nonce( $post_data );

				$fed_admin_settings_captcha = array(
					'fed_captcha_site_key'         => isset( $post_data['fed_captcha_site_key'] ) ? sanitize_text_field( $post_data['fed_captcha_site_key'] ) : '',
					'fed_captcha_secrete_key'      => isset( $post_data['fed_captcha_secrete_key'] ) ? sanitize_text_field( $post_data['fed_captcha_secrete_key'] ) : '',
					'fed_captcha_in_login_form'    => isset( $post_data['fed_captcha_in_login_form'] ) ? 'Enable' : 'Disable',
					'fed_captcha_in_register_form' => isset( $post_data['fed_captcha_in_register_form'] ) ? 'Enable' : 'Disable',
				);

				apply_filters( 'fed_admin_settings_captcha', $fed_admin_settings_captcha );

				update_option( 'fed_admin_settings_captcha', $fed_admin_settings_captcha );

				wp_send_json_success(
					array(
						'message' => __( 'Captcha Settings Updated Successfully', 'frontend-dashboard-captcha' ),
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