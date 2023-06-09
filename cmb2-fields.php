<?php
/**
 * Plugin Name:  CMB2 Fields
 * Plugin URI:
 * Description:
 * Author:   
 * Author URI: 
 * Contributors:
 * Version:
 * Text Domain:  cmb2-fields
 * Domain Path:  /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define plugin constant.
 */
if ( ! defined( 'CMB2AE_CMB2_PLUGIN_FILE' ) ) {
	define( 'CMB2AE_CMB2_PLUGIN_FILE', 'cmb2/init.php' );
}

if ( ! defined( 'CMB2AE_URI' ) ) {
	define( 'CMB2AE_URI', plugins_url( '', __FILE__ ) );
}

if ( ! defined( 'CMB2AE_PATH' ) ) {
	define( 'CMB2AE_PATH', plugin_dir_path( __FILE__ ) );
}

class CMB2_Admin_Extension_Class
{

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '0.2.3';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Initiate CMB2 Admin Extension.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		$this->check_for_cmb2();

		add_action( 'init', array( $this, 'load_textdomain' ), 9 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check for the CMB2 plugin.
	 *
	 * @since 0.0.1
	 */
	private function check_for_cmb2() {

		if ( defined( 'CMB2_LOADED' ) && CMB2_LOADED !== false ) {

			require_once dirname( __FILE__ ) . '/includes/class-meta-box.php';
			require_once dirname( __FILE__ ) . '/includes/class-meta-box-post-type.php';
			require_once dirname( __FILE__ ) . '/includes/class-meta-box-settings.php';
			cmb2ae_metabox();
			return;
		} elseif ( file_exists( WP_PLUGIN_DIR . '/' . CMB2AE_CMB2_PLUGIN_FILE ) ) {

			add_action( 'admin_notices', array( $this, 'cmb2_not_activated' ) );
			return;
		}
		add_action( 'admin_notices', array( $this, 'missing_cmb2' ) );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {

		$lang_path = plugin_basename( dirname( __FILE__ ) ) . '/languages';
		$loaded    = load_muplugin_textdomain( 'cmb2-admin-extension', $lang_path );
		if ( strpos( __FILE__, basename( WPMU_PLUGIN_DIR ) ) === false ) {
			$loaded = load_plugin_textdomain( 'cmb2-admin-extension', false, $lang_path );
		}

		if ( ! $loaded ) {
			$loaded = load_theme_textdomain( 'cmb2-admin-extension', get_stylesheet_directory() . '/languages' );
		}

		if ( ! $loaded ) {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'cmb2-admin-extension' );
			$mofile = dirname( __FILE__ ) . '/languages/cmb2-admin-extension-' . $locale . '.mo';
			load_textdomain( 'cmb2-admin-extension', $mofile );
		}
	}

	/**
	 * Add an error notice if the CMB2 plugin is missing.
	 *
	 * @return void
	 */
	public function missing_cmb2() {

		?>
			<div class="error">
				<p>
					<?php
					printf(
						/* translators: 1: link opener; 2: link closer. */
						esc_html__( 'CMB2 Admin Extension depends on the last version of %1$s the CMB2 plugin %2$s to work!', 'cmb2-admin-extension' ),
						'<a href="https://wordpress.org/plugins/cmb2/">',
						'</a>'
					);
					?>
				</p>
			</div>
		<?php

	}

	/**
	 * Add an error notice if the CMB2 plugin isn't activated.
	 *
	 * @return void
	 */
	public function cmb2_not_activated() {

		?>
			<div class="error">
				<p>
					<?php
					printf(
						/* translators: 1: link opener; 2: link closer. */
						esc_html__( 'The CMB2 plugin is installed but has not been activated. Please %1$s activate %2$s it to use the CMB2 Admin Extension', 'cmb2-admin-extension' ),
						'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">',
						'</a>'
					);
					?>
				</p>
			</div>
		<?php

	}
}

add_action( 
    'plugins_loaded', 
    array( 
        'CMB2_Admin_Extension_Class', 
        'get_instance' 
    ), 
20 );

if ( ! function_exists( 'cmbf' ) ) {
	function cmbf( $id, $field ) {
		return get_post_meta( $id, $field, true );
	}
}