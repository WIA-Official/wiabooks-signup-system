<?php
/**
 * WIABooks Admin Dashboard Class
 *
 * Handles admin dashboard pages and statistics.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WIABooks Admin Dashboard Class
 *
 * @class WIABooks_Admin_Dashboard
 * @version 1.0.0
 */
class WIABooks_Admin_Dashboard {

    /**
     * The single instance of the class
     *
     * @var WIABooks_Admin_Dashboard
     */
    protected static $_instance = null;

    /**
     * Main WIABooks_Admin_Dashboard Instance
     *
     * @static
     * @return WIABooks_Admin_Dashboard - Main instance
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
        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Add dashboard widgets
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );

        // Add user columns
        add_filter( 'manage_users_columns', array( $this, 'add_user_columns' ) );
        add_filter( 'manage_users_custom_column', array( $this, 'render_user_columns' ), 10, 3 );
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __( 'WIABooks 회원 관리', 'wiabooks-signup' ),
            __( 'WIABooks', 'wiabooks-signup' ),
            'manage_options',
            'wiabooks-dashboard',
            array( $this, 'render_dashboard_page' ),
            'dashicons-book-alt',
            30
        );

        // Statistics submenu
        add_submenu_page(
            'wiabooks-dashboard',
            __( '통계', 'wiabooks-signup' ),
            __( '통계', 'wiabooks-signup' ),
            'manage_options',
            'wiabooks-stats',
            array( $this, 'render_stats_page' )
        );

        // Settings submenu
        add_submenu_page(
            'wiabooks-dashboard',
            __( '설정', 'wiabooks-signup' ),
            __( '설정', 'wiabooks-signup' ),
            'manage_options',
            'wiabooks-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render main dashboard page
     */
    public function render_dashboard_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '이 페이지에 접근할 권한이 없습니다.', 'wiabooks-signup' ) );
        }

        // Include admin page template
        require_once WIABOOKS_PLUGIN_DIR . 'admin/admin-page.php';
    }

    /**
     * Render statistics page
     */
    public function render_stats_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '이 페이지에 접근할 권한이 없습니다.', 'wiabooks-signup' ) );
        }

        $stats = $this->get_global_stats();

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WIABooks 통계', 'wiabooks-signup' ); ?></h1>

            <div class="wiabooks-stats-grid">
                <div class="wiabooks-stat-card">
                    <h3><?php esc_html_e( '총 작가 수', 'wiabooks-signup' ); ?></h3>
                    <div class="stat-number"><?php echo esc_html( $stats['total_authors'] ); ?></div>
                </div>

                <div class="wiabooks-stat-card">
                    <h3><?php esc_html_e( '총 책 수', 'wiabooks-signup' ); ?></h3>
                    <div class="stat-number"><?php echo esc_html( $stats['total_books'] ); ?></div>
                </div>

                <div class="wiabooks-stat-card">
                    <h3><?php esc_html_e( '총 게시글 수', 'wiabooks-signup' ); ?></h3>
                    <div class="stat-number"><?php echo esc_html( $stats['total_posts'] ); ?></div>
                </div>

                <div class="wiabooks-stat-card">
                    <h3><?php esc_html_e( '총 크레딧 사용량', 'wiabooks-signup' ); ?></h3>
                    <div class="stat-number">$<?php echo esc_html( number_format( $stats['total_credits_used'], 2 ) ); ?></div>
                </div>
            </div>

            <h2><?php esc_html_e( '최근 가입 작가', 'wiabooks-signup' ); ?></h2>
            <?php $this->render_recent_authors_table(); ?>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '이 페이지에 접근할 권한이 없습니다.', 'wiabooks-signup' ) );
        }

        // Handle form submission
        if ( isset( $_POST['wiabooks_save_settings'] ) ) {
            check_admin_referer( 'wiabooks_settings_nonce' );

            $settings = array(
                'default_credit' => isset( $_POST['default_credit'] ) ? intval( $_POST['default_credit'] ) : 1000,
                'enable_credit_system' => isset( $_POST['enable_credit_system'] ) ? true : false,
                'redirect_after_signup' => isset( $_POST['redirect_after_signup'] ) ? sanitize_text_field( wp_unslash( $_POST['redirect_after_signup'] ) ) : 'dashboard',
            );

            update_option( 'wiabooks_signup_settings', $settings );

            echo '<div class="notice notice-success"><p>' . esc_html__( '설정이 저장되었습니다.', 'wiabooks-signup' ) . '</p></div>';
        }

        $settings = get_option( 'wiabooks_signup_settings', array(
            'default_credit' => 1000,
            'enable_credit_system' => false,
            'redirect_after_signup' => 'dashboard',
        ) );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WIABooks 설정', 'wiabooks-signup' ); ?></h1>

            <form method="post" action="">
                <?php wp_nonce_field( 'wiabooks_settings_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="default_credit"><?php esc_html_e( '기본 크레딧', 'wiabooks-signup' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_credit" name="default_credit" value="<?php echo esc_attr( $settings['default_credit'] ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( '신규 가입자에게 제공되는 기본 크레딧 금액', 'wiabooks-signup' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="enable_credit_system"><?php esc_html_e( '크레딧 시스템 활성화', 'wiabooks-signup' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enable_credit_system" name="enable_credit_system" value="1" <?php checked( $settings['enable_credit_system'], true ); ?> />
                            <p class="description"><?php esc_html_e( '크레딧 시스템을 활성화합니다 (향후 PDF/EPUB 생성 시 사용)', 'wiabooks-signup' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="redirect_after_signup"><?php esc_html_e( '가입 후 리다이렉트', 'wiabooks-signup' ); ?></label>
                        </th>
                        <td>
                            <select id="redirect_after_signup" name="redirect_after_signup">
                                <option value="dashboard" <?php selected( $settings['redirect_after_signup'], 'dashboard' ); ?>><?php esc_html_e( '작가 대시보드', 'wiabooks-signup' ); ?></option>
                                <option value="home" <?php selected( $settings['redirect_after_signup'], 'home' ); ?>><?php esc_html_e( '홈페이지', 'wiabooks-signup' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="wiabooks_save_settings" class="button button-primary" value="<?php esc_attr_e( '변경사항 저장', 'wiabooks-signup' ); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'wiabooks_overview_widget',
            __( 'WIABooks 개요', 'wiabooks-signup' ),
            array( $this, 'render_overview_widget' )
        );
    }

    /**
     * Render overview widget
     */
    public function render_overview_widget() {
        $stats = $this->get_global_stats();

        ?>
        <div class="wiabooks-widget-stats">
            <ul>
                <li><strong><?php esc_html_e( '총 작가:', 'wiabooks-signup' ); ?></strong> <?php echo esc_html( $stats['total_authors'] ); ?></li>
                <li><strong><?php esc_html_e( '총 책:', 'wiabooks-signup' ); ?></strong> <?php echo esc_html( $stats['total_books'] ); ?></li>
                <li><strong><?php esc_html_e( '총 게시글:', 'wiabooks-signup' ); ?></strong> <?php echo esc_html( $stats['total_posts'] ); ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Add custom user columns
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_user_columns( $columns ) {
        $columns['wiabooks_book'] = __( '책 제목', 'wiabooks-signup' );
        $columns['wiabooks_category'] = __( '카테고리', 'wiabooks-signup' );
        $columns['wiabooks_credit'] = __( '크레딧', 'wiabooks-signup' );
        return $columns;
    }

    /**
     * Render custom user columns
     *
     * @param string $output      Custom column output
     * @param string $column_name Column name
     * @param int    $user_id     User ID
     * @return string Column output
     */
    public function render_user_columns( $output, $column_name, $user_id ) {
        switch ( $column_name ) {
            case 'wiabooks_book':
                $book_title = get_user_meta( $user_id, 'wiabooks_book_title', true );
                return $book_title ? esc_html( $book_title ) : '—';

            case 'wiabooks_category':
                $category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );
                if ( $category_id ) {
                    $category = get_category( $category_id );
                    if ( $category ) {
                        return '<a href="' . esc_url( get_category_link( $category_id ) ) . '">' . esc_html( $category->name ) . '</a>';
                    }
                }
                return '—';

            case 'wiabooks_credit':
                $credit = get_user_meta( $user_id, 'wiabooks_credit', true );
                return $credit ? '$' . esc_html( number_format( $credit, 2 ) ) : '$0.00';

            default:
                return $output;
        }
    }

    /**
     * Get global statistics
     *
     * @return array Statistics
     */
    private function get_global_stats() {
        // Get all authors
        $authors = get_users( array(
            'role' => 'author',
            'meta_key' => 'wiabooks_book_category_id',
            'meta_compare' => 'EXISTS',
        ) );

        $total_authors = count( $authors );
        $total_books = $total_authors; // One book per author
        $total_posts = wp_count_posts()->publish;
        $total_credits_used = 0;

        // Calculate total credits used (assuming default is 1000)
        foreach ( $authors as $author ) {
            $current_credit = get_user_meta( $author->ID, 'wiabooks_credit', true );
            $total_credits_used += ( 1000 - $current_credit );
        }

        return array(
            'total_authors' => $total_authors,
            'total_books' => $total_books,
            'total_posts' => $total_posts,
            'total_credits_used' => $total_credits_used,
        );
    }

    /**
     * Render recent authors table
     */
    private function render_recent_authors_table() {
        $recent_authors = get_users( array(
            'role' => 'author',
            'meta_key' => 'wiabooks_signup_date',
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'number' => 10,
        ) );

        if ( empty( $recent_authors ) ) {
            echo '<p>' . esc_html__( '아직 가입한 작가가 없습니다.', 'wiabooks-signup' ) . '</p>';
            return;
        }

        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( '작가명', 'wiabooks-signup' ); ?></th>
                    <th><?php esc_html_e( '이메일', 'wiabooks-signup' ); ?></th>
                    <th><?php esc_html_e( '책 제목', 'wiabooks-signup' ); ?></th>
                    <th><?php esc_html_e( '가입일', 'wiabooks-signup' ); ?></th>
                    <th><?php esc_html_e( '크레딧', 'wiabooks-signup' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $recent_authors as $author ) : ?>
                    <?php
                    $book_title = get_user_meta( $author->ID, 'wiabooks_book_title', true );
                    $signup_date = get_user_meta( $author->ID, 'wiabooks_signup_date', true );
                    $credit = get_user_meta( $author->ID, 'wiabooks_credit', true );
                    ?>
                    <tr>
                        <td><a href="<?php echo esc_url( get_edit_user_link( $author->ID ) ); ?>"><?php echo esc_html( $author->display_name ); ?></a></td>
                        <td><?php echo esc_html( $author->user_email ); ?></td>
                        <td><?php echo esc_html( $book_title ? $book_title : '—' ); ?></td>
                        <td><?php echo esc_html( $signup_date ? date_i18n( get_option( 'date_format' ), strtotime( $signup_date ) ) : '—' ); ?></td>
                        <td>$<?php echo esc_html( number_format( $credit, 2 ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}
