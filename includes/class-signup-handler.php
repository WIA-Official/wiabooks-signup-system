<?php
/**
 * WIABooks Signup Handler Class
 *
 * Handles user signup flow, Google login redirection, and book information submission.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WIABooks Signup Handler Class
 *
 * @class WIABooks_Signup_Handler
 * @version 1.0.0
 */
class WIABooks_Signup_Handler {

    /**
     * The single instance of the class
     *
     * @var WIABooks_Signup_Handler
     */
    protected static $_instance = null;

    /**
     * Main WIABooks_Signup_Handler Instance
     *
     * @static
     * @return WIABooks_Signup_Handler - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Hook into Nextend Social Login after login
        add_action( 'nsl_login', array( $this, 'handle_social_login' ), 10, 1 );

        // Add custom page for book information
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'book_info_template_redirect' ) );

        // Handle book information form submission
        add_action( 'wp_ajax_submit_book_info', array( $this, 'handle_book_info_submission' ) );
        add_action( 'wp_ajax_nopriv_submit_book_info', array( $this, 'handle_book_info_submission' ) );

        // Add shortcode for signup form
        add_shortcode( 'wiabooks_signup_form', array( $this, 'render_signup_form' ) );
    }

    /**
     * Handle social login redirect
     *
     * Redirects new users to book information page after Google login
     *
     * @param int $user_id User ID
     */
    public function handle_social_login( $user_id ) {
        try {
            if ( ! $user_id ) {
                return;
            }

            // Check if user already has book information
            $has_book = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

            if ( ! $has_book ) {
                // New user - redirect to book information page
                wp_safe_redirect( home_url( '/book-info/' ) );
                exit;
            } else {
                // Existing user - redirect to dashboard
                wp_safe_redirect( home_url( '/author-dashboard/' ) );
                exit;
            }
        } catch ( Exception $e ) {
            error_log( 'WIABooks Signup Error: ' . $e->getMessage() );
            wp_safe_redirect( home_url() );
            exit;
        }
    }

    /**
     * Add rewrite rules for custom pages
     */
    public function add_rewrite_rules() {
        add_rewrite_rule( '^book-info/?$', 'index.php?wiabooks_page=book-info', 'top' );
        add_rewrite_rule( '^author-dashboard/?$', 'index.php?wiabooks_page=author-dashboard', 'top' );
    }

    /**
     * Add custom query vars
     *
     * @param array $vars Query variables
     * @return array Modified query variables
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'wiabooks_page';
        return $vars;
    }

    /**
     * Template redirect for book info page
     */
    public function book_info_template_redirect() {
        $wiabooks_page = get_query_var( 'wiabooks_page' );

        if ( $wiabooks_page === 'book-info' ) {
            // Check if user is logged in
            if ( ! is_user_logged_in() ) {
                wp_safe_redirect( wp_login_url() );
                exit;
            }

            // Load book info template
            $this->load_template( 'signup-form' );
            exit;
        } elseif ( $wiabooks_page === 'author-dashboard' ) {
            // Check if user is logged in
            if ( ! is_user_logged_in() ) {
                wp_safe_redirect( wp_login_url() );
                exit;
            }

            // Load author dashboard template
            $this->load_template( 'author-dashboard' );
            exit;
        }
    }

    /**
     * Load template file
     *
     * @param string $template_name Template name without .php extension
     */
    private function load_template( $template_name ) {
        $template_path = WIABOOKS_PLUGIN_DIR . 'templates/' . $template_name . '.php';

        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            wp_die( __( 'Template not found.', 'wiabooks-signup' ) );
        }
    }

    /**
     * Handle book information form submission
     */
    public function handle_book_info_submission() {
        try {
            // Verify nonce
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wiabooks_book_info' ) ) {
                wp_send_json_error( array( 'message' => __( '보안 검증에 실패했습니다.', 'wiabooks-signup' ) ) );
            }

            // Check if user is logged in
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( array( 'message' => __( '로그인이 필요합니다.', 'wiabooks-signup' ) ) );
            }

            $user_id = get_current_user_id();

            // Check if user already has a book
            $existing_category = get_user_meta( $user_id, 'wiabooks_book_category_id', true );
            if ( $existing_category ) {
                wp_send_json_error( array( 'message' => __( '이미 책이 등록되어 있습니다.', 'wiabooks-signup' ) ) );
            }

            // Sanitize input data
            $book_title = isset( $_POST['book_title'] ) ? sanitize_text_field( wp_unslash( $_POST['book_title'] ) ) : '';
            $book_subtitle = isset( $_POST['book_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['book_subtitle'] ) ) : '';
            $book_description = isset( $_POST['book_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['book_description'] ) ) : '';

            // Validate required fields
            if ( empty( $book_title ) ) {
                wp_send_json_error( array( 'message' => __( '책 제목은 필수 입력 항목입니다.', 'wiabooks-signup' ) ) );
            }

            // Create category using Category Manager
            $category_manager = WIABooks_Category_Manager::instance();
            $category_id = $category_manager->create_book_category( $book_title, $book_description );

            if ( is_wp_error( $category_id ) ) {
                wp_send_json_error( array( 'message' => $category_id->get_error_message() ) );
            }

            // Save user meta data
            update_user_meta( $user_id, 'wiabooks_book_category_id', $category_id );
            update_user_meta( $user_id, 'wiabooks_book_title', $book_title );
            update_user_meta( $user_id, 'wiabooks_book_subtitle', $book_subtitle );
            update_user_meta( $user_id, 'wiabooks_credit', 1000 ); // Default $1,000 credit
            update_user_meta( $user_id, 'wiabooks_signup_date', current_time( 'mysql' ) );

            // Set user role to Author if not already
            $user = get_user_by( 'id', $user_id );
            if ( $user && ! in_array( 'author', $user->roles, true ) && ! in_array( 'administrator', $user->roles, true ) ) {
                $user->set_role( 'author' );
            }

            // Success response
            wp_send_json_success( array(
                'message' => __( '책 정보가 성공적으로 등록되었습니다!', 'wiabooks-signup' ),
                'redirect_url' => home_url( '/author-dashboard/' ),
            ) );

        } catch ( Exception $e ) {
            error_log( 'WIABooks Book Info Submission Error: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => __( '오류가 발생했습니다. 다시 시도해주세요.', 'wiabooks-signup' ) ) );
        }
    }

    /**
     * Render signup form shortcode
     *
     * @return string HTML output
     */
    public function render_signup_form() {
        if ( is_user_logged_in() ) {
            return '<p>' . __( '이미 로그인되어 있습니다.', 'wiabooks-signup' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="wiabooks-signup-container">
            <h2><?php esc_html_e( '위아북스 회원가입', 'wiabooks-signup' ); ?></h2>
            <p><?php esc_html_e( '구글 계정으로 간편하게 가입하세요.', 'wiabooks-signup' ); ?></p>
            <?php echo do_shortcode( '[nextend_social_login]' ); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
