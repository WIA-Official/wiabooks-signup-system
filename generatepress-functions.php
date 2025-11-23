<?php
/**
 * GeneratePress Theme Integration Functions
 *
 * Copy this code to your GeneratePress child theme's functions.php file
 * or use the GeneratePress Elements feature to add custom code.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add "My Book" menu item to navigation
 *
 * Adds a "내 책 관리" link to the navigation menu for logged-in authors.
 *
 * @param string $items The HTML list content for the navigation items
 * @param object $args  An object containing wp_nav_menu() arguments
 * @return string Modified menu items
 */
function wiabooks_add_my_book_menu( $items, $args ) {
    // Only add to primary menu
    if ( $args->theme_location !== 'primary' ) {
        return $items;
    }

    // Check if user is logged in and is an author
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();

        if ( in_array( 'author', $user->roles, true ) || in_array( 'administrator', $user->roles, true ) ) {
            $dashboard_link = '<li class="menu-item wiabooks-dashboard-link">' .
                             '<a href="' . esc_url( home_url( '/author-dashboard/' ) ) . '">' .
                             esc_html__( '내 책 관리', 'wiabooks-signup' ) .
                             '</a></li>';

            $items .= $dashboard_link;
        }
    }

    return $items;
}
add_filter( 'wp_nav_menu_items', 'wiabooks_add_my_book_menu', 10, 2 );

/**
 * Add author dashboard widget after header
 *
 * Displays a custom widget/notice for authors on their dashboard page.
 */
function wiabooks_author_dashboard_widget() {
    // Only show on author dashboard page
    if ( ! is_user_logged_in() || ! is_page() ) {
        return;
    }

    global $post;
    if ( ! $post || get_query_var( 'wiabooks_page' ) !== 'author-dashboard' ) {
        return;
    }

    $user_id = get_current_user_id();
    $book_title = get_user_meta( $user_id, 'wiabooks_book_title', true );

    if ( $book_title ) {
        ?>
        <div class="wiabooks-header-notice">
            <div class="container">
                <p><?php echo esc_html( sprintf( __( '현재 작업 중인 책: %s', 'wiabooks-signup' ), $book_title ) ); ?></p>
            </div>
        </div>
        <style>
        .wiabooks-header-notice {
            background: #2271b1;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            font-size: 14px;
        }
        .wiabooks-header-notice p {
            margin: 0;
        }
        </style>
        <?php
    }
}
add_action( 'generate_after_header', 'wiabooks_author_dashboard_widget' );

/**
 * Add custom CSS for WIABooks integration
 */
function wiabooks_generatepress_custom_css() {
    ?>
    <style>
    /* WIABooks Navigation Menu Styling */
    .main-navigation .wiabooks-dashboard-link a {
        background: #2271b1;
        color: #fff;
        padding: 8px 15px;
        border-radius: 4px;
        margin-left: 10px;
    }

    .main-navigation .wiabooks-dashboard-link a:hover {
        background: #135e96;
    }

    /* Author Dashboard Page Styling */
    .wiabooks-author-dashboard .site-content {
        padding-top: 20px;
    }

    /* Book Info Page Styling */
    .wiabooks-book-info-page .site-content {
        padding-top: 20px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main-navigation .wiabooks-dashboard-link a {
            margin-left: 0;
            margin-top: 10px;
            display: block;
            text-align: center;
        }
    }
    </style>
    <?php
}
add_action( 'wp_head', 'wiabooks_generatepress_custom_css' );

/**
 * Customize footer for WIABooks authors
 */
function wiabooks_custom_footer_content() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $user = wp_get_current_user();

    if ( ! in_array( 'author', $user->roles, true ) ) {
        return;
    }

    $user_id = get_current_user_id();
    $stats = array();

    if ( class_exists( 'WIABooks_Permission_Manager' ) ) {
        $permission_manager = WIABooks_Permission_Manager::instance();
        $stats = $permission_manager->get_user_stats( $user_id );
    }

    if ( ! empty( $stats ) && $stats['total_posts'] > 0 ) {
        ?>
        <div class="wiabooks-footer-stats">
            <div class="container">
                <p>
                    <?php
                    echo esc_html(
                        sprintf(
                            __( '오늘도 글쓰기 수고하셨습니다! 현재 %d개의 글을 작성하셨습니다.', 'wiabooks-signup' ),
                            $stats['total_posts']
                        )
                    );
                    ?>
                </p>
            </div>
        </div>
        <style>
        .wiabooks-footer-stats {
            background: #f9f9f9;
            border-top: 1px solid #e0e0e0;
            padding: 15px 0;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .wiabooks-footer-stats p {
            margin: 0;
        }
        </style>
        <?php
    }
}
add_action( 'generate_before_footer', 'wiabooks_custom_footer_content' );

/**
 * Add writing progress indicator to sidebar (if applicable)
 */
function wiabooks_sidebar_progress_widget() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $user = wp_get_current_user();

    if ( ! in_array( 'author', $user->roles, true ) ) {
        return;
    }

    $user_id = get_current_user_id();
    $book_title = get_user_meta( $user_id, 'wiabooks_book_title', true );

    if ( ! $book_title ) {
        return;
    }

    $stats = array( 'total_words' => 0 );

    if ( class_exists( 'WIABooks_Permission_Manager' ) ) {
        $permission_manager = WIABooks_Permission_Manager::instance();
        $stats = $permission_manager->get_user_stats( $user_id );
    }

    $target_words = 50000;
    $current_words = $stats['total_words'];
    $progress_percentage = min( 100, ( $current_words / $target_words ) * 100 );

    ?>
    <div class="widget wiabooks-progress-widget">
        <h3 class="widget-title"><?php esc_html_e( '작성 진행률', 'wiabooks-signup' ); ?></h3>
        <div class="progress-content">
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?php echo esc_attr( $progress_percentage ); ?>%;"></div>
            </div>
            <p class="progress-text">
                <?php echo esc_html( sprintf( __( '%s / %s 단어 (%s%%)', 'wiabooks-signup' ), number_format( $current_words ), number_format( $target_words ), number_format( $progress_percentage, 1 ) ) ); ?>
            </p>
        </div>
    </div>
    <style>
    .wiabooks-progress-widget {
        padding: 20px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .wiabooks-progress-widget .widget-title {
        margin: 0 0 15px 0;
        font-size: 16px;
        font-weight: 600;
    }
    .wiabooks-progress-widget .progress-bar-container {
        width: 100%;
        height: 20px;
        background: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .wiabooks-progress-widget .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #2271b1, #4a9fd8);
        transition: width 0.5s ease;
    }
    .wiabooks-progress-widget .progress-text {
        margin: 0;
        font-size: 13px;
        color: #666;
        text-align: center;
    }
    </style>
    <?php
}
add_action( 'generate_before_main_content', 'wiabooks_sidebar_progress_widget' );

/**
 * Customize page title for WIABooks pages
 *
 * @param string $title The page title
 * @return string Modified title
 */
function wiabooks_custom_page_title( $title ) {
    if ( get_query_var( 'wiabooks_page' ) === 'book-info' ) {
        return __( '책 정보 입력', 'wiabooks-signup' );
    }

    if ( get_query_var( 'wiabooks_page' ) === 'author-dashboard' ) {
        $user = wp_get_current_user();
        return sprintf( __( '%s님의 작가 대시보드', 'wiabooks-signup' ), $user->display_name );
    }

    return $title;
}
add_filter( 'the_title', 'wiabooks_custom_page_title' );

// Note: After copying this code to your GeneratePress theme's functions.php,
// make sure to flush rewrite rules by going to Settings > Permalinks and clicking "Save Changes"
