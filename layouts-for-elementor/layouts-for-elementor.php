<?php
/**
 * Plugin Name: Layouts for Elementor
 * Plugin URI: https://www.techeshta.com/product/layouts-for-elementor/
 * Description: Beautifully designed, Free templates, Handcrafted for popular Elementor page builder.
 * Version: 2.0
 * Author: Techeshta
 * Author URI: https://www.techeshta.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Elementor tested up to: 4.2.4
 * Elementor Pro tested up to: 4.0.3
 * Text Domain: layouts-for-elementor
 * Domain Path: /languages/
 *
 * @package LFE
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'LFE_FILE', __FILE__ );
define( 'LFE_DIR', plugin_dir_path( LFE_FILE ) );
define( 'LFE_URL', plugins_url( '/', LFE_FILE ) );
define( 'LFE_TEXTDOMAIN', 'layouts-for-elementor' );

/**
 * Main Plugin Layout_For_Elementor class.
 */
class Layout_For_Elementor {

	/**
	 * Layout_For_Elementor constructor.
	 *
	 * The main plugin actions registered for WordPress.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'lfe_check_dependencies' ) );
		$this->hooks();
		$this->lfe_include_files();
	}

	/**
	 * Register the plugin's WordPress hooks.
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'lfe_admin_scripts' ) );
	}

	/**
	 * Load the plugin's Elementor-dependent class files.
	 *
	 * Only loaded once Elementor itself has finished loading, since both
	 * files reference Elementor classes.
	 */
	public function lfe_include_files() {
		if ( did_action( 'elementor/loaded' ) ) {
			include_once LFE_DIR . 'includes/class-layout-importer.php';
			include_once LFE_DIR . 'includes/api/class-layouts-remote.php';
		}
	}

	/**
	 * Check plugin dependencies.
	 *
	 * Verifies Elementor is active and, if so, meets the minimum required
	 * version. Hooks the admin menu registration once dependencies are met.
	 */
	public function lfe_check_dependencies() {

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'lfe_layouts_widget_fail_load' ) );
			return;
		}

		add_action( 'admin_menu', array( $this, 'lfe_menu' ) );

		$elementor_version_required = '1.1.2';
		if ( ! version_compare( ELEMENTOR_VERSION, $elementor_version_required, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'lfe_layouts_elementor_update_notice' ) );
			return;
		}
	}

	/**
	 * Display an admin notice if Elementor is not installed or activated.
	 */
	public function lfe_layouts_widget_fail_load() {

		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin            = 'elementor/elementor.php';
		$file_path         = 'elementor/elementor.php';
		$installed_plugins = get_plugins();

		if ( isset( $installed_plugins[ $file_path ] ) ) { // Elementor is installed but not active.
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

			$message  = '<p><strong>' . esc_html__( 'Layouts for Elementor', 'layouts-for-elementor' ) . '</strong>' . esc_html__( ' widgets not working because you need to activate the Elementor plugin.', 'layouts-for-elementor' ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $activation_url ), esc_html__( 'Activate Elementor Now', 'layouts-for-elementor' ) ) . '</p>';
		} else { // Elementor is not installed.
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );

			$message  = '<p><strong>' . esc_html__( 'Layouts for Elementor', 'layouts-for-elementor' ) . '</strong>' . esc_html__( ' widgets not working because you need to install the Elementor plugin', 'layouts-for-elementor' ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $install_url ), esc_html__( 'Install Elementor Now', 'layouts-for-elementor' ) ) . '</p>';
		}

		echo '<div class="error"><p>' . wp_kses_post( $message ) . '</p></div>';
	}

	/**
	 * Display an admin notice if the active Elementor version is too old.
	 */
	public function lfe_layouts_elementor_update_notice() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$file_path = 'elementor/elementor.php';

		$upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
		$message      = '<p><strong>' . esc_html__( 'Layouts for Elementor', 'layouts-for-elementor' ) . '</strong>' . esc_html__( ' widgets not working because you are using an old version of Elementor.', 'layouts-for-elementor' ) . '</p>';
		$message     .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $upgrade_link ), esc_html__( 'Update Elementor Now', 'layouts-for-elementor' ) ) . '</p>';
		echo '<div class="error">' . wp_kses_post( $message ) . '</div>';
	}

	/**
	 * Register and enqueue the plugin's admin CSS/JS on its own admin screen.
	 */
	public function lfe_admin_scripts() {
		$screen = get_current_screen();

		wp_register_style( 'lfe-admin-stylesheets', LFE_URL . 'assets/css/admin.css', array(), '2.0', 'all' );
		wp_register_style( 'lfe-toastify-stylesheets', LFE_URL . 'assets/css/toastify.css', array(), '2.0', 'all' );
		wp_register_script( 'lfe-admin-script', LFE_URL . 'assets/js/admin.js', array( 'jquery' ), '1.0.0', true );
		wp_register_script( 'lfe-toastify-script', LFE_URL . 'assets/js/toastify.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script(
			'lfe-admin-script',
			'js_object',
			array(
				'lfe_loading'  => __( 'Importing...', 'layouts-for-elementor' ),
				'lfe_msg'      => __( 'Your page is successfully imported!', 'layouts-for-elementor' ),
				'lfe_crt_page' => __( 'Please Enter Page Name.', 'layouts-for-elementor' ),
				'lfe_sync'     => __( 'Syncing...', 'layouts-for-elementor' ),
				'lfe_sync_suc' => __( 'Templates library refreshed', 'layouts-for-elementor' ),
				'lfe_sync_fai' => __( 'Error in library Syncing', 'layouts-for-elementor' ),
				'lfe_url'      => LFE_URL,
				'nonce'        => wp_create_nonce( 'ajax-nonce' ),
			)
		);

		$screen_id = $screen ? $screen->id : '';
		if ( in_array( $screen_id, array( 'toplevel_page_lfe_layouts', 'layouts_page_lfe_started' ), true ) ) {
			wp_enqueue_style( 'lfe-admin-stylesheets' );
			wp_enqueue_style( 'lfe-toastify-stylesheets' );
			wp_enqueue_script( 'lfe-toastify-script' );
			wp_enqueue_script( 'lfe-admin-script' );
			add_thickbox();
		}
	}

	/**
	 * Register the "Layouts" top-level admin menu page.
	 */
	public function lfe_menu() {
		add_menu_page( __( 'Layouts', 'layouts-for-elementor' ), __( 'Layouts', 'layouts-for-elementor' ), 'manage_options', 'lfe_layouts', array( $this, 'lfe_render_layouts_page' ), LFE_URL . 'assets/images/layouts-for-elementor.png' );
	}

	/**
	 * Render the "Layouts" admin screen.
	 */
	public function lfe_render_layouts_page() {
		include_once LFE_DIR . 'includes/layouts.php';
	}
}

// Starts our plugin class, easy!
new Layout_For_Elementor();
