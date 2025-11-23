<?php
/**
 * Plugin Name: WIABooks Signup System
 * Plugin URI: https://github.com/WIA-Official/wiabooks-signup-system
 * Description: 위아북스 회원가입 및 책 관리 시스템. Nextend Social Login을 통한 구글 로그인, 자동 카테고리 생성, 작가별 권한 관리 기능을 제공합니다.
 * Version: 1.0.0
 * Author: WIA Official
 * Author URI: https://wiabooks.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wiabooks-signup
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WIABOOKS_VERSION', '1.0.0' );
define( 'WIABOOKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WIABOOKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WIABOOKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main WIABooks Signup System Class
 *
 * @class WIABooks_Signup_System
 * @version 1.0.0
 */
final class WIABooks_Signup_System {

    /**
     * The single instance of the class
     *
     * @var WIABooks_Signup_System
     */
    protected static $_instance = null;

    /**
     * Main WIABooks_Signup_System Instance
     *
     * Ensures only one instance of WIABooks_Signup_System is loaded or can be loaded.
     *
     * @static
     * @return WIABooks_Signup_System - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WIABooks_Signup_System Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required core files
     */
    private function includes() {
        // Core classes
        require_once WIABOOKS_PLUGIN_DIR . 'includes/class-signup-handler.php';
        require_once WIABOOKS_PLUGIN_DIR . 'includes/class-category-manager.php';
        require_once WIABOOKS_PLUGIN_DIR . 'includes/class-permission-manager.php';
        require_once WIABOOKS_PLUGIN_DIR . 'includes/class-admin-dashboard.php';
    }

    /**
     * Hook into actions and filters
     */
    private function init_hooks() {
        // Activation & Deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Initialize plugin
        add_action( 'plugins_loaded', array( $this, 'init' ), 0 );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Load plugin textdomain
        load_plugin_textdomain( 'wiabooks-signup', false, dirname( WIABOOKS_PLUGIN_BASENAME ) . '/languages' );

        // Initialize core components
        WIABooks_Signup_Handler::instance();
        WIABooks_Category_Manager::instance();
        WIABooks_Permission_Manager::instance();

        // Initialize admin dashboard if in admin area
        if ( is_admin() ) {
            WIABooks_Admin_Dashboard::instance();
        }

        // Trigger action after initialization
        do_action( 'wiabooks_signup_loaded' );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create necessary database tables if needed (future expansion)
        $this->create_options();

        // Set default credit for existing users
        $this->set_default_credits();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create plugin options
     */
    private function create_options() {
        // Default plugin settings
        $default_options = array(
            'default_credit' => 1000,
            'enable_credit_system' => false, // Disabled by default, will be enabled in future
            'redirect_after_signup' => 'dashboard',
        );

        add_option( 'wiabooks_signup_settings', $default_options );
    }

    /**
     * Set default credits for all existing users
     */
    private function set_default_credits() {
        $users = get_users( array( 'fields' => 'ID' ) );

        foreach ( $users as $user_id ) {
            if ( ! get_user_meta( $user_id, 'wiabooks_credit', true ) ) {
                update_user_meta( $user_id, 'wiabooks_credit', 1000 );
            }
        }
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Frontend CSS
        wp_enqueue_style(
            'wiabooks-signup-style',
            WIABOOKS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WIABOOKS_VERSION
        );

        // Frontend JS
        wp_enqueue_script(
            'wiabooks-signup-script',
            WIABOOKS_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            WIABOOKS_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            'wiabooks-signup-script',
            'wiabooks_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'wiabooks_nonce' ),
            )
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts( $hook ) {
        // Only load on our admin pages
        if ( strpos( $hook, 'wiabooks' ) === false ) {
            return;
        }

        // Admin CSS
        wp_enqueue_style(
            'wiabooks-admin-style',
            WIABOOKS_PLUGIN_URL . 'admin/admin-style.css',
            array(),
            WIABOOKS_VERSION
        );

        // Admin JS
        wp_enqueue_script(
            'wiabooks-admin-script',
            WIABOOKS_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            WIABOOKS_VERSION,
            true
        );
    }
}

/**
 * Returns the main instance of WIABooks_Signup_System
 *
 * @return WIABooks_Signup_System
 */
function WIABooks() {
    return WIABooks_Signup_System::instance();
}

// Global for backwards compatibility
$GLOBALS['wiabooks'] = WIABooks();
