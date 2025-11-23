<?php
/**
 * Template: Author Dashboard
 *
 * Displays the author dashboard with book statistics and management options.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current user
$user_id = get_current_user_id();
$user = wp_get_current_user();

// Get user's book information
$book_title = get_user_meta( $user_id, 'wiabooks_book_title', true );
$book_subtitle = get_user_meta( $user_id, 'wiabooks_book_subtitle', true );
$category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );
$credit = get_user_meta( $user_id, 'wiabooks_credit', true );
$signup_date = get_user_meta( $user_id, 'wiabooks_signup_date', true );

// Get statistics
$permission_manager = WIABooks_Permission_Manager::instance();
$stats = $permission_manager->get_user_stats( $user_id );

// Get category
$category = get_category( $category_id );

get_header();
?>

<div class="wiabooks-author-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1><?php echo esc_html( sprintf( __( '%s님의 작가 대시보드', 'wiabooks-signup' ), $user->display_name ) ); ?></h1>
            <p class="dashboard-subtitle"><?php esc_html_e( '당신의 책 작성 현황을 확인하세요', 'wiabooks-signup' ); ?></p>
        </div>

        <div class="dashboard-grid">
            <!-- Book Information Card -->
            <div class="dashboard-card book-info-card">
                <h2><?php esc_html_e( '내 책 정보', 'wiabooks-signup' ); ?></h2>
                <div class="book-details">
                    <div class="book-detail-item">
                        <strong><?php esc_html_e( '책 제목:', 'wiabooks-signup' ); ?></strong>
                        <span><?php echo esc_html( $book_title ); ?></span>
                    </div>

                    <?php if ( $book_subtitle ) : ?>
                    <div class="book-detail-item">
                        <strong><?php esc_html_e( '부제목:', 'wiabooks-signup' ); ?></strong>
                        <span><?php echo esc_html( $book_subtitle ); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="book-detail-item">
                        <strong><?php esc_html_e( '카테고리:', 'wiabooks-signup' ); ?></strong>
                        <span>
                            <?php if ( $category ) : ?>
                                <a href="<?php echo esc_url( get_category_link( $category_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
                            <?php else : ?>
                                <?php esc_html_e( '미설정', 'wiabooks-signup' ); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="book-detail-item">
                        <strong><?php esc_html_e( '가입일:', 'wiabooks-signup' ); ?></strong>
                        <span><?php echo esc_html( $signup_date ? date_i18n( get_option( 'date_format' ), strtotime( $signup_date ) ) : '—' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="dashboard-card stats-card">
                <h2><?php esc_html_e( '작성 통계', 'wiabooks-signup' ); ?></h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo esc_html( $stats['total_posts'] ); ?></div>
                        <div class="stat-label"><?php esc_html_e( '총 게시글', 'wiabooks-signup' ); ?></div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-number"><?php echo esc_html( $stats['published_posts'] ); ?></div>
                        <div class="stat-label"><?php esc_html_e( '발행됨', 'wiabooks-signup' ); ?></div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-number"><?php echo esc_html( $stats['draft_posts'] ); ?></div>
                        <div class="stat-label"><?php esc_html_e( '초안', 'wiabooks-signup' ); ?></div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-number"><?php echo esc_html( number_format( $stats['total_words'] ) ); ?></div>
                        <div class="stat-label"><?php esc_html_e( '총 단어 수', 'wiabooks-signup' ); ?></div>
                    </div>
                </div>
            </div>

            <!-- Credit Card -->
            <div class="dashboard-card credit-card">
                <h2><?php esc_html_e( '크레딧 잔액', 'wiabooks-signup' ); ?></h2>
                <div class="credit-balance">
                    <div class="credit-amount">$<?php echo esc_html( number_format( $credit, 2 ) ); ?></div>
                    <div class="credit-info">
                        <?php
                        $used_credit = 1000 - $credit;
                        echo esc_html( sprintf( __( '$%s 사용됨', 'wiabooks-signup' ), number_format( $used_credit, 2 ) ) );
                        ?>
                    </div>
                    <div class="credit-note">
                        <small><?php esc_html_e( '크레딧은 PDF/EPUB 생성 시 사용됩니다 (향후 제공)', 'wiabooks-signup' ); ?></small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="dashboard-card actions-card">
                <h2><?php esc_html_e( '빠른 작업', 'wiabooks-signup' ); ?></h2>
                <div class="action-buttons">
                    <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="btn btn-primary">
                        <?php esc_html_e( '새 글 작성', 'wiabooks-signup' ); ?>
                    </a>

                    <a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="btn btn-secondary">
                        <?php esc_html_e( '내 글 관리', 'wiabooks-signup' ); ?>
                    </a>

                    <?php if ( $category_id ) : ?>
                    <a href="<?php echo esc_url( get_category_link( $category_id ) ); ?>" class="btn btn-secondary">
                        <?php esc_html_e( '내 책 보기', 'wiabooks-signup' ); ?>
                    </a>
                    <?php endif; ?>

                    <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" class="btn btn-secondary">
                        <?php esc_html_e( '프로필 수정', 'wiabooks-signup' ); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Posts Section -->
        <div class="dashboard-section recent-posts">
            <h2><?php esc_html_e( '최근 게시글', 'wiabooks-signup' ); ?></h2>

            <?php
            $recent_posts = get_posts( array(
                'author' => $user_id,
                'category' => $category_id,
                'posts_per_page' => 5,
                'post_status' => 'any',
            ) );

            if ( ! empty( $recent_posts ) ) :
            ?>
                <table class="posts-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( '제목', 'wiabooks-signup' ); ?></th>
                            <th><?php esc_html_e( '상태', 'wiabooks-signup' ); ?></th>
                            <th><?php esc_html_e( '날짜', 'wiabooks-signup' ); ?></th>
                            <th><?php esc_html_e( '작업', 'wiabooks-signup' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $recent_posts as $post ) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                                        <?php echo esc_html( $post->post_title ); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="post-status status-<?php echo esc_attr( $post->post_status ); ?>">
                                        <?php
                                        $statuses = array(
                                            'publish' => __( '발행됨', 'wiabooks-signup' ),
                                            'draft' => __( '초안', 'wiabooks-signup' ),
                                            'pending' => __( '대기중', 'wiabooks-signup' ),
                                            'private' => __( '비공개', 'wiabooks-signup' ),
                                        );
                                        echo esc_html( isset( $statuses[ $post->post_status ] ) ? $statuses[ $post->post_status ] : $post->post_status );
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>" class="btn-small">
                                        <?php esc_html_e( '수정', 'wiabooks-signup' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="no-posts">
                    <?php esc_html_e( '아직 작성한 글이 없습니다.', 'wiabooks-signup' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">
                        <?php esc_html_e( '첫 번째 글을 작성해보세요!', 'wiabooks-signup' ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <!-- Writing Progress Section -->
        <div class="dashboard-section writing-progress">
            <h2><?php esc_html_e( '작성 진행률', 'wiabooks-signup' ); ?></h2>
            <div class="progress-info">
                <?php
                $target_words = 50000; // Target: 50,000 words for a book
                $current_words = $stats['total_words'];
                $progress_percentage = min( 100, ( $current_words / $target_words ) * 100 );
                ?>

                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo esc_attr( $progress_percentage ); ?>%;"></div>
                </div>

                <div class="progress-stats">
                    <span><?php echo esc_html( sprintf( __( '%s / %s 단어', 'wiabooks-signup' ), number_format( $current_words ), number_format( $target_words ) ) ); ?></span>
                    <span><?php echo esc_html( number_format( $progress_percentage, 1 ) ); ?>%</span>
                </div>

                <p class="progress-message">
                    <?php
                    if ( $progress_percentage >= 100 ) {
                        esc_html_e( '축하합니다! 목표 단어 수를 달성했습니다!', 'wiabooks-signup' );
                    } elseif ( $progress_percentage >= 50 ) {
                        esc_html_e( '절반 이상 완성했습니다. 계속 작성하세요!', 'wiabooks-signup' );
                    } else {
                        esc_html_e( '꾸준히 작성하면 목표를 달성할 수 있습니다!', 'wiabooks-signup' );
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
