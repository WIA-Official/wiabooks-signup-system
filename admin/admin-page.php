<?php
/**
 * Admin Page Template
 *
 * Main admin dashboard page for WIABooks.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get statistics
$permission_manager = WIABooks_Permission_Manager::instance();

// Get all authors with books
$authors = get_users( array(
    'role' => 'author',
    'meta_key' => 'wiabooks_book_category_id',
    'meta_compare' => 'EXISTS',
    'orderby' => 'registered',
    'order' => 'DESC',
) );
?>

<div class="wrap wiabooks-admin-wrap">
    <h1><?php esc_html_e( 'WIABooks 회원 관리', 'wiabooks-signup' ); ?></h1>

    <div class="wiabooks-admin-header">
        <p><?php esc_html_e( '위아북스 회원 및 책 관리 시스템', 'wiabooks-signup' ); ?></p>
    </div>

    <div class="wiabooks-admin-grid">
        <!-- Quick Stats -->
        <div class="wiabooks-admin-card">
            <h2><?php esc_html_e( '빠른 통계', 'wiabooks-signup' ); ?></h2>
            <div class="stats-summary">
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( '총 작가 수:', 'wiabooks-signup' ); ?></span>
                    <span class="stat-value"><?php echo esc_html( count( $authors ) ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( '총 책 수:', 'wiabooks-signup' ); ?></span>
                    <span class="stat-value"><?php echo esc_html( count( $authors ) ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( '총 게시글 수:', 'wiabooks-signup' ); ?></span>
                    <span class="stat-value"><?php echo esc_html( wp_count_posts()->publish ); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Authors Table -->
    <div class="wiabooks-authors-section">
        <h2><?php esc_html_e( '작가 및 책 목록', 'wiabooks-signup' ); ?></h2>

        <?php if ( ! empty( $authors ) ) : ?>
            <table class="wp-list-table widefat fixed striped wiabooks-authors-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '작가명', 'wiabooks-signup' ); ?></th>
                        <th><?php esc_html_e( '이메일', 'wiabooks-signup' ); ?></th>
                        <th><?php esc_html_e( '책 제목', 'wiabooks-signup' ); ?></th>
                        <th><?php esc_html_e( '카테고리', 'wiabooks-signup' ); ?></th>
                        <th><?php esc_html_e( '게시글 수', 'wiabooks-signup' ); ?></th>
                        <th><?php esc_html_e( '크레딧', 'wiabooks-signup' ); ?></th>
                        <th><?php esc_html_e( '가입일', 'wiabooks-signup' ); ?></th>
                        <th><?php esc_html_e( '작업', 'wiabooks-signup' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $authors as $author ) : ?>
                        <?php
                        $book_title = get_user_meta( $author->ID, 'wiabooks_book_title', true );
                        $category_id = get_user_meta( $author->ID, 'wiabooks_book_category_id', true );
                        $credit = get_user_meta( $author->ID, 'wiabooks_credit', true );
                        $signup_date = get_user_meta( $author->ID, 'wiabooks_signup_date', true );
                        $stats = $permission_manager->get_user_stats( $author->ID );
                        $category = get_category( $category_id );
                        ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url( get_edit_user_link( $author->ID ) ); ?>">
                                        <?php echo esc_html( $author->display_name ); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html( $author->user_email ); ?></td>
                            <td><?php echo esc_html( $book_title ? $book_title : '—' ); ?></td>
                            <td>
                                <?php if ( $category ) : ?>
                                    <a href="<?php echo esc_url( get_category_link( $category_id ) ); ?>">
                                        <?php echo esc_html( $category->name ); ?>
                                    </a>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge">
                                    <?php echo esc_html( $stats['total_posts'] ); ?>
                                    <small>(<?php echo esc_html( sprintf( __( '%d 발행', 'wiabooks-signup' ), $stats['published_posts'] ) ); ?>)</small>
                                </span>
                            </td>
                            <td>
                                <span class="credit-amount">
                                    $<?php echo esc_html( number_format( $credit, 2 ) ); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                if ( $signup_date ) {
                                    echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $signup_date ) ) );
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( get_edit_user_link( $author->ID ) ); ?>" class="button button-small">
                                    <?php esc_html_e( '수정', 'wiabooks-signup' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="notice notice-info">
                <p><?php esc_html_e( '아직 가입한 작가가 없습니다.', 'wiabooks-signup' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Additional Information -->
    <div class="wiabooks-info-section">
        <h2><?php esc_html_e( '플러그인 정보', 'wiabooks-signup' ); ?></h2>
        <div class="wiabooks-info-box">
            <p><strong><?php esc_html_e( 'WIABooks 회원가입 시스템', 'wiabooks-signup' ); ?></strong></p>
            <p><?php echo esc_html( sprintf( __( '버전: %s', 'wiabooks-signup' ), WIABOOKS_VERSION ) ); ?></p>
            <p><?php esc_html_e( '기능: 구글 로그인, 자동 카테고리 생성, 작가별 권한 관리', 'wiabooks-signup' ); ?></p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wiabooks-settings' ) ); ?>" class="button">
                    <?php esc_html_e( '설정으로 이동', 'wiabooks-signup' ); ?>
                </a>
            </p>
        </div>
    </div>
</div>
