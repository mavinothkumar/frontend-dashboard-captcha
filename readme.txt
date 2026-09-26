=== Frontend Dashboard Captcha ===
Contributors: vinoth06, buffercode
Tags: dashboard, frontend dashboard, captcha, recaptcha, turnstile, spam protection, login, registration
Donate link: https://www.paypal.com/paypalme2/buffercode
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend Dashboard Captcha protects against spam bot submissions in Login and Registration forms with Google reCAPTCHA, Cloudflare Turnstile, and Math Captcha.

== Description ==

> #### Notice
> This is a free add-on plugin for [Frontend Dashboard](https://buffercode.com/plugin/frontend-dashboard). Please install and activate Frontend Dashboard (v3.0.0+) to use this plugin.

**Frontend Dashboard Captcha** shields your frontend login and registration portals against brute force attacks, credential stuffing, and automated bot accounts.

### Features
* **Google reCAPTCHA v2 / v3**: Seamless integration with Google's invisible and interactive challenge widgets.
* **Cloudflare Turnstile Support**: Modern, privacy-first CAPTCHA alternative.
* **Math Captcha**: Lightweight, self-hosted mathematical question challenges without third-party API dependencies.
* **Login Protection**: Prevent brute-force login attacks on custom frontend login pages.
* **Registration Protection**: Stop automated botnet registrations on frontend registration forms.
* **Unified Admin Settings**: Easily configure site keys and secret keys directly in the Frontend Dashboard settings panel.

== Installation ==

1. Upload the `frontend-dashboard-captcha` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure **Frontend Dashboard** is also installed and activated.
4. Navigate to **Frontend Dashboard > Settings > Login > Captcha** to configure your credentials.
5. Save settings.

== Changelog ==

= 3.0.0 =
* Complete modernization: Full integration with Frontend Dashboard 3.0 App Shell.
* Added support for Cloudflare Turnstile and Math Captcha alongside Google reCAPTCHA v2/v3.
* Redesigned settings panel and improved error message handling on failed validations.
* Enhanced client-side validation and responsive widget rendering.
* Full compatibility with WordPress 6.7 and PHP 8.1 / 8.2 / 8.3.

= v1.4.1 [18-May-2020] =
* Bug fixes

= v1.2 [28-Oct-2019] =
* Bug fixes

= v1.1.1 [24-December-2017] =
* Translation updated

= v1.1 [06-December-2017] =
* Bug fixes

= v1.0 [06-September-2017] =
* Public release

== Upgrade Notice ==

= 3.0.0 =
Major release: Full integration with Frontend Dashboard 3.0, support for Cloudflare Turnstile, and WordPress 6.7 compatibility.

= v1.2 [28-Oct-2019] =
* Bug fixes

== Screenshots ==
1. Frontend Login with Captcha
2. Frontend Register with Captcha
3. Frontend Dashboard Captcha Configuration
