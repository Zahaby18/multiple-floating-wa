<?php
/**
 * Plugin Name:       Multiple Floating WhatsApp
 * Description:       Floating WhatsApp launcher with an unlimited number repeater, per button labels, prefilled messages, color controls and a built in WhatsApp icon.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            GenWork
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       multiple-floating-wa
 * Domain Path:       /languages
 *
 * @package MultipleFloatingWA
 */

defined( 'ABSPATH' ) || exit;

define( 'MFW_VERSION', '1.0.0' );
define( 'MFW_FILE', __FILE__ );
define( 'MFW_DIR', plugin_dir_path( __FILE__ ) );
define( 'MFW_URL', plugin_dir_url( __FILE__ ) );
define( 'MFW_OPTION', 'mfw_settings' );

require_once MFW_DIR . 'includes/class-mfw-icon.php';
require_once MFW_DIR . 'includes/class-mfw-frontend.php';
require_once MFW_DIR . 'includes/class-mfw-admin.php';

/**
 * Main plugin bootstrap.
 */
final class MFW_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var MFW_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Frontend renderer.
	 *
	 * @var MFW_Frontend
	 */
	public $frontend;

	/**
	 * Admin screens.
	 *
	 * @var MFW_Admin
	 */
	public $admin;

	/**
	 * Retrieve the singleton instance.
	 *
	 * @return MFW_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		load_plugin_textdomain( 'multiple-floating-wa', false, dirname( plugin_basename( MFW_FILE ) ) . '/languages' );

		$this->frontend = new MFW_Frontend();
		$this->frontend->hooks();

		if ( is_admin() ) {
			$this->admin = new MFW_Admin();
			$this->admin->hooks();
		}

		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes for manual placement.
	 */
	public function register_shortcodes() {
		add_shortcode( 'multiple_floating_wa', array( $this->frontend, 'shortcode' ) );
		add_shortcode( 'show_wa', array( $this->frontend, 'shortcode' ) );
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'enabled'       => 1,
			'position'      => 'bottom-right',
			'size'          => 'medium',
			'button_color'  => '#0bb3b9',
			'header_color'  => '',
			'panel_color'   => '#ffffff',
			'text_color'    => '#222222',
			'header_title'  => __( 'Chat with us', 'multiple-floating-wa' ),
			'panel_intro'   => '',
			'icon_url'      => '',
			'auto_open'     => 0,
			'hide_desktop'  => 0,
			'hide_tablet'   => 0,
			'hide_mobile'   => 0,
			'open_new_tab'  => 1,
			'items'         => array(
				array(
					'label'   => '',
					'number'  => '',
					'message' => '',
				),
			),
		);
	}

	/**
	 * Stored settings merged with defaults.
	 *
	 * @return array
	 */
	public static function settings() {
		$stored = get_option( MFW_OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$merged = wp_parse_args( $stored, self::defaults() );

		if ( ! is_array( $merged['items'] ) || empty( $merged['items'] ) ) {
			$merged['items'] = self::defaults()['items'];
		}

		return apply_filters( 'mfw_settings', $merged );
	}

	/**
	 * Sanitize a free text value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function sanitize_text( $value ) {
		return trim( sanitize_text_field( (string) $value ) );
	}
}

/**
 * Plugin accessor.
 *
 * @return MFW_Plugin
 */
function mfw() {
	return MFW_Plugin::instance();
}

mfw();

register_activation_hook(
	MFW_FILE,
	function () {
		if ( false === get_option( MFW_OPTION ) ) {
			add_option( MFW_OPTION, MFW_Plugin::defaults() );
		}
	}
);
