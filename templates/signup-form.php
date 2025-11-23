<?php
/**
 * Template: Book Information Form
 *
 * Displays the book information form for new users.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="wiabooks-book-info-page">
    <div class="container">
        <div class="wiabooks-form-wrapper">
            <h1 class="page-title"><?php esc_html_e( '책 정보 입력', 'wiabooks-signup' ); ?></h1>
            <p class="page-description"><?php esc_html_e( '환영합니다! 당신의 책 정보를 입력해주세요.', 'wiabooks-signup' ); ?></p>

            <form id="wiabooks-book-info-form" class="wiabooks-form">
                <?php wp_nonce_field( 'wiabooks_book_info', 'wiabooks_book_info_nonce' ); ?>

                <div class="form-group">
                    <label for="book_title" class="required"><?php esc_html_e( '책 제목', 'wiabooks-signup' ); ?> *</label>
                    <input
                        type="text"
                        id="book_title"
                        name="book_title"
                        class="form-control"
                        required
                        placeholder="<?php esc_attr_e( '예: 나의 첫 소설', 'wiabooks-signup' ); ?>"
                    />
                    <small class="form-text"><?php esc_html_e( '책의 메인 제목을 입력하세요. (필수)', 'wiabooks-signup' ); ?></small>
                </div>

                <div class="form-group">
                    <label for="book_subtitle"><?php esc_html_e( '책 소제목', 'wiabooks-signup' ); ?></label>
                    <input
                        type="text"
                        id="book_subtitle"
                        name="book_subtitle"
                        class="form-control"
                        placeholder="<?php esc_attr_e( '예: 인생의 이야기', 'wiabooks-signup' ); ?>"
                    />
                    <small class="form-text"><?php esc_html_e( '책의 부제목을 입력하세요. (선택)', 'wiabooks-signup' ); ?></small>
                </div>

                <div class="form-group">
                    <label for="book_description"><?php esc_html_e( '책 소개', 'wiabooks-signup' ); ?></label>
                    <textarea
                        id="book_description"
                        name="book_description"
                        class="form-control"
                        rows="5"
                        placeholder="<?php esc_attr_e( '책에 대한 간단한 소개를 입력하세요...', 'wiabooks-signup' ); ?>"
                    ></textarea>
                    <small class="form-text"><?php esc_html_e( '책에 대한 간단한 설명을 입력하세요. (선택)', 'wiabooks-signup' ); ?></small>
                </div>

                <div class="form-notice">
                    <p><strong><?php esc_html_e( '알림:', 'wiabooks-signup' ); ?></strong></p>
                    <ul>
                        <li><?php esc_html_e( '입력한 책 제목으로 자동으로 카테고리가 생성됩니다.', 'wiabooks-signup' ); ?></li>
                        <li><?php esc_html_e( '생성된 카테고리에만 글을 작성할 수 있습니다.', 'wiabooks-signup' ); ?></li>
                        <li><?php esc_html_e( '무료로 $1,000 크레딧이 제공됩니다.', 'wiabooks-signup' ); ?></li>
                    </ul>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submit-book-info">
                        <?php esc_html_e( '시작하기', 'wiabooks-signup' ); ?>
                    </button>
                </div>

                <div id="form-message" class="form-message" style="display: none;"></div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#wiabooks-book-info-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $('#submit-book-info');
        var $message = $('#form-message');

        // Disable submit button
        $submitBtn.prop('disabled', true).text('<?php esc_html_e( '처리 중...', 'wiabooks-signup' ); ?>');
        $message.hide();

        // Prepare data
        var formData = {
            action: 'submit_book_info',
            nonce: $('#wiabooks_book_info_nonce').val(),
            book_title: $('#book_title').val(),
            book_subtitle: $('#book_subtitle').val(),
            book_description: $('#book_description').val()
        };

        // AJAX request
        $.ajax({
            url: wiabooks_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message)
                        .fadeIn();

                    // Redirect after 1 second
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
                } else {
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html(response.data.message)
                        .fadeIn();

                    $submitBtn.prop('disabled', false).text('<?php esc_html_e( '시작하기', 'wiabooks-signup' ); ?>');
                }
            },
            error: function() {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html('<?php esc_html_e( '오류가 발생했습니다. 다시 시도해주세요.', 'wiabooks-signup' ); ?>')
                    .fadeIn();

                $submitBtn.prop('disabled', false).text('<?php esc_html_e( '시작하기', 'wiabooks-signup' ); ?>');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
