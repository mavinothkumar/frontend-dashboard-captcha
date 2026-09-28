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
			$version     = isset( $fed_admin_options['fed_captcha_version'] ) ? $fed_admin_options['fed_captcha_version'] : 'v2';
			$site_key    = isset( $fed_admin_options['fed_captcha_site_key'] ) ? $fed_admin_options['fed_captcha_site_key'] : '';
			$secret_key  = isset( $fed_admin_options['fed_captcha_secrete_key'] ) ? $fed_admin_options['fed_captcha_secrete_key'] : '';
			$v3_score    = isset( $fed_admin_options['fed_captcha_v3_score'] ) ? $fed_admin_options['fed_captcha_v3_score'] : '0.5';
			$v3_badge    = isset( $fed_admin_options['fed_captcha_v3_badge_position'] ) ? $fed_admin_options['fed_captcha_v3_badge_position'] : 'bottomright';
			$in_login    = isset( $fed_admin_options['fed_captcha_in_login_form'] ) ? $fed_admin_options['fed_captcha_in_login_form'] : 'Disable';
			$in_register = isset( $fed_admin_options['fed_captcha_in_register_form'] ) ? $fed_admin_options['fed_captcha_in_register_form'] : 'Disable';
			?>
			<form method="post"
				  class="fed_admin_menu fed_ajax space-y-6"
				  id="fed_captcha_settings_form"
				  action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_admin_setting_form' ) ); ?>">

				<?php fed_wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>
				<?php echo fed_loader(); ?>

				<input type="hidden" name="fed_admin_unique" value="fed_admin_settings_captcha"/>

				<!-- reCAPTCHA Version Selection Cards -->
				<div class="space-y-3">
					<div class="pb-2 border-b border-slate-100 flex items-center justify-between">
						<div>
							<h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 m-0"><?php esc_html_e( 'reCAPTCHA Version / Type', 'frontend-dashboard-captcha' ); ?></h4>
							<p class="text-[11px] text-slate-400 m-0 mt-0.5"><?php esc_html_e( 'Select the exact reCAPTCHA version corresponding to the keys generated in your Google Console.', 'frontend-dashboard-captcha' ); ?></p>
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<!-- Option v2 -->
						<label class="fed-captcha-ver-card p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3.5 <?php echo ( 'v2' === $version ) ? 'bg-indigo-50/80 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs' : 'bg-slate-50/70 border-slate-200/80 hover:bg-slate-100/70'; ?>">
							<input type="radio"
								   name="fed_captcha_version"
								   value="v2"
								   class="sr-only fed_captcha_version_radio"
								   <?php checked( $version, 'v2' ); ?>>
							<div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-base <?php echo ( 'v2' === $version ) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-200 text-slate-600'; ?>">
								<i class="fas fa-check-square"></i>
							</div>
							<div>
								<div class="flex items-center gap-2">
									<h5 class="text-xs font-bold text-slate-900 m-0"><?php esc_html_e( 'reCAPTCHA v2 (Checkbox)', 'frontend-dashboard-captcha' ); ?></h5>
									<span class="inline-flex items-center px-2 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">"I'm not a robot"</span>
								</div>
								<p class="text-[11px] text-slate-500 m-0 mt-1 leading-relaxed">
									<?php esc_html_e( 'Users must interact with a checkbox challenge widget on the login/registration form.', 'frontend-dashboard-captcha' ); ?>
								</p>
							</div>
						</label>

						<!-- Option v3 -->
						<label class="fed-captcha-ver-card p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3.5 <?php echo ( 'v3' === $version ) ? 'bg-indigo-50/80 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs' : 'bg-slate-50/70 border-slate-200/80 hover:bg-slate-100/70'; ?>">
							<input type="radio"
								   name="fed_captcha_version"
								   value="v3"
								   class="sr-only fed_captcha_version_radio"
								   <?php checked( $version, 'v3' ); ?>>
							<div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-base <?php echo ( 'v3' === $version ) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-200 text-slate-600'; ?>">
								<i class="fas fa-shield-alt"></i>
							</div>
							<div>
								<div class="flex items-center gap-2">
									<h5 class="text-xs font-bold text-slate-900 m-0"><?php esc_html_e( 'reCAPTCHA v3 (Score Based)', 'frontend-dashboard-captcha' ); ?></h5>
									<span class="inline-flex items-center px-2 py-0.2 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Invisible / Seamless</span>
								</div>
								<p class="text-[11px] text-slate-500 m-0 mt-1 leading-relaxed">
									<?php esc_html_e( 'Verifies interactions in the background with a trust score (0.0 to 1.0) without friction or checkbox puzzles.', 'frontend-dashboard-captcha' ); ?>
								</p>
							</div>
						</label>
					</div>
				</div>

				<!-- Info Banner -->
				<div class="p-4 bg-indigo-50/70 border border-indigo-100/80 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
					<div class="flex items-start gap-3">
						<div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm shrink-0 mt-0.5 sm:mt-0">
							<i class="fas fa-info-circle"></i>
						</div>
						<div>
							<h4 class="text-xs font-bold text-indigo-950 m-0" id="fed_captcha_banner_title">
								<?php echo ( 'v3' === $version ) ? esc_html__( 'Google reCAPTCHA v3 (Score Based) Integration', 'frontend-dashboard-captcha' ) : esc_html__( 'Google reCAPTCHA v2 (Checkbox) Integration', 'frontend-dashboard-captcha' ); ?>
							</h4>
							<p class="text-[11px] text-indigo-700/90 m-0 mt-0.5" id="fed_captcha_banner_desc">
								<?php echo ( 'v3' === $version ) ? esc_html__( 'Requires Google reCAPTCHA v3 (Score Based) Site Key and Secret Key from your Google reCAPTCHA admin console.', 'frontend-dashboard-captcha' ) : esc_html__( 'Requires Google reCAPTCHA v2 ("I\'m not a robot" Checkbox) keys to protect login and registration forms.', 'frontend-dashboard-captcha' ); ?>
							</p>
						</div>
					</div>
					<a href="https://www.google.com/recaptcha/admin"
					   target="_blank"
					   rel="noopener noreferrer"
					   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white hover:text-white focus:text-white text-xs font-semibold rounded-xl transition-colors shrink-0 no-underline shadow-2xs"
					   style="color: #ffffff !important; text-decoration: none !important;">
						<span style="color: #ffffff !important;"><?php esc_html_e( 'Get API Keys', 'frontend-dashboard-captcha' ); ?></span>
						<i class="fas fa-external-link-alt text-[10px]" style="color: #ffffff !important;"></i>
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
								<?php esc_html_e( 'Site Key', 'frontend-dashboard-captcha' ); ?> <span class="text-rose-500">*</span>
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
								<?php esc_html_e( 'Secret Key', 'frontend-dashboard-captcha' ); ?> <span class="text-rose-500">*</span>
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

				<!-- v3 Specific Settings -->
				<div id="fed_v3_settings_section" class="space-y-4 <?php echo ( 'v3' === $version ) ? '' : 'hidden'; ?>">
					<div class="pb-2 border-b border-slate-100">
						<h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 m-0"><?php esc_html_e( 'reCAPTCHA v3 Tuning & Behavior', 'frontend-dashboard-captcha' ); ?></h4>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
						<!-- Score Threshold -->
						<div class="space-y-2 p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80">
							<div class="flex items-center justify-between">
								<label for="fed_captcha_v3_score" class="text-xs font-bold text-slate-800">
									<?php esc_html_e( 'Score Sensitivity Threshold', 'frontend-dashboard-captcha' ); ?>
								</label>
								<span id="fed_v3_score_display" class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-700">
									<?php echo esc_html( $v3_score ); ?>
								</span>
							</div>
							<input type="range"
								   id="fed_captcha_v3_score"
								   name="fed_captcha_v3_score"
								   min="0.1"
								   max="0.9"
								   step="0.1"
								   value="<?php echo esc_attr( $v3_score ); ?>"
								   class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
							<div class="flex justify-between text-[10px] text-slate-400 font-semibold">
								<span>0.1 (Permissive / Relaxed)</span>
								<span class="text-indigo-600 font-bold">0.5 (Default)</span>
								<span>0.9 (Strict / Sensitive)</span>
							</div>
							<p class="text-[11px] text-slate-500 m-0 pt-1">
								<?php esc_html_e( 'Google returns a score from 0.0 (bot) to 1.0 (human). Submissions scoring below this threshold are blocked as spam.', 'frontend-dashboard-captcha' ); ?>
							</p>
						</div>

						<!-- Badge Position -->
						<div class="space-y-2 p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80">
							<label for="fed_captcha_v3_badge_position" class="block text-xs font-bold text-slate-800">
								<?php esc_html_e( 'Badge Position', 'frontend-dashboard-captcha' ); ?>
							</label>
							<select name="fed_captcha_v3_badge_position"
									id="fed_captcha_v3_badge_position"
									class="w-full">
								<option value="bottomright" <?php selected( $v3_badge, 'bottomright' ); ?>><?php esc_html_e( 'Bottom Right (Default Floating Badge)', 'frontend-dashboard-captcha' ); ?></option>
								<option value="bottomleft" <?php selected( $v3_badge, 'bottomleft' ); ?>><?php esc_html_e( 'Bottom Left', 'frontend-dashboard-captcha' ); ?></option>
								<option value="inline" <?php selected( $v3_badge, 'inline' ); ?>><?php esc_html_e( 'Inline in Form', 'frontend-dashboard-captcha' ); ?></option>
								<option value="hide" <?php selected( $v3_badge, 'hide' ); ?>><?php esc_html_e( 'Hide Badge (CSS Invisible)', 'frontend-dashboard-captcha' ); ?></option>
							</select>
							<p class="text-[11px] text-slate-500 m-0 pt-1">
								<?php esc_html_e( 'If choosing to hide the badge, Google Terms of Service and Privacy Policy should be displayed on your site.', 'frontend-dashboard-captcha' ); ?>
							</p>
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
								<p class="text-[11px] text-slate-500 select-none m-0"><?php esc_html_e( 'Display / execute reCAPTCHA on login form to prevent brute-force attacks.', 'frontend-dashboard-captcha' ); ?></p>
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
								<p class="text-[11px] text-slate-500 select-none m-0"><?php esc_html_e( 'Display / execute reCAPTCHA on registration form to prevent spam user signups.', 'frontend-dashboard-captcha' ); ?></p>
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
						<span style="color: #ffffff !important;"><?php esc_html_e( 'Save Changes', 'frontend-dashboard-captcha' ); ?></span>
					</button>
				</div>
			</form>

			<script>
			jQuery(document).ready(function($) {
				// Version card selection
				$(document).on('change', '.fed_captcha_version_radio', function() {
					var val = $(this).val();
					$('.fed-captcha-ver-card').removeClass('bg-indigo-50/80 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs')
					                          .addClass('bg-slate-50/70 border-slate-200/80');
					$('.fed-captcha-ver-card .w-10').removeClass('bg-indigo-600 text-white shadow-xs')
					                                .addClass('bg-slate-200 text-slate-600');

					var $activeCard = $(this).closest('.fed-captcha-ver-card');
					$activeCard.addClass('bg-indigo-50/80 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs')
					           .removeClass('bg-slate-50/70 border-slate-200/80');
					$activeCard.find('.w-10').addClass('bg-indigo-600 text-white shadow-xs')
					                        .removeClass('bg-slate-200 text-slate-600');

					if (val === 'v3') {
						$('#fed_v3_settings_section').removeClass('hidden');
						$('#fed_captcha_banner_title').text('Google reCAPTCHA v3 (Score Based) Integration');
						$('#fed_captcha_banner_desc').text('Requires Google reCAPTCHA v3 (Score Based) Site Key and Secret Key from your Google reCAPTCHA admin console.');
					} else {
						$('#fed_v3_settings_section').addClass('hidden');
						$('#fed_captcha_banner_title').text('Google reCAPTCHA v2 (Checkbox) Integration');
						$('#fed_captcha_banner_desc').text('Requires Google reCAPTCHA v2 ("I\'m not a robot" Checkbox) keys to protect login and registration forms.');
					}
				});

				// Slider update
				$(document).on('input', '#fed_captcha_v3_score', function() {
					$('#fed_v3_score_display').text($(this).val());
				});
			});
			</script>
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
					'fed_captcha_version'          => isset( $post_data['fed_captcha_version'] ) ? sanitize_text_field( $post_data['fed_captcha_version'] ) : 'v2',
					'fed_captcha_site_key'         => isset( $post_data['fed_captcha_site_key'] ) ? sanitize_text_field( $post_data['fed_captcha_site_key'] ) : '',
					'fed_captcha_secrete_key'      => isset( $post_data['fed_captcha_secrete_key'] ) ? sanitize_text_field( $post_data['fed_captcha_secrete_key'] ) : '',
					'fed_captcha_v3_score'         => isset( $post_data['fed_captcha_v3_score'] ) ? sanitize_text_field( $post_data['fed_captcha_v3_score'] ) : '0.5',
					'fed_captcha_v3_badge_position'=> isset( $post_data['fed_captcha_v3_badge_position'] ) ? sanitize_text_field( $post_data['fed_captcha_v3_badge_position'] ) : 'bottomright',
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
			$fed_captcha = get_option( 'fed_admin_settings_captcha' );
			if ( ! is_array( $fed_captcha ) ) {
				return;
			}

			$site_key = isset( $fed_captcha['fed_captcha_site_key'] ) ? trim( $fed_captcha['fed_captcha_site_key'] ) : '';
			if ( empty( $site_key ) ) {
				return;
			}

			$version = isset( $fed_captcha['fed_captcha_version'] ) ? $fed_captcha['fed_captcha_version'] : 'v2';

			if ( 'v3' === $version ) {
				wp_enqueue_script(
					'recaptcha-v3',
					'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $site_key ),
					array( 'jquery' ),
					BC_FED_CAPTCHA_PLUGIN_VERSION,
					true
				);

				$badge_pos = isset( $fed_captcha['fed_captcha_v3_badge_position'] ) ? $fed_captcha['fed_captcha_v3_badge_position'] : 'bottomright';
				if ( 'hide' === $badge_pos ) {
					wp_add_inline_style( 'fed_style', '.grecaptcha-badge { visibility: hidden !important; }' );
				} elseif ( 'bottomleft' === $badge_pos ) {
					wp_add_inline_style( 'fed_style', '.grecaptcha-badge { left: 0 !important; right: auto !important; }' );
				} elseif ( 'inline' === $badge_pos ) {
					wp_add_inline_style( 'fed_style', '.grecaptcha-badge { position: relative !important; left: auto !important; right: auto !important; bottom: auto !important; }' );
				}
			} else {
				wp_enqueue_script(
					'recaptcha',
					'https://www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit&hl=en',
					array( 'jquery' ),
					BC_FED_CAPTCHA_PLUGIN_VERSION,
					true
				);
			}
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
			if ( 'recaptcha' !== $handle && 'recaptcha-v3' !== $handle ) {
				return $tag;
			}

			return str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}

	}

	new FEDC_Menu();
}