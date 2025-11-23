/**
 * WIABooks Frontend JavaScript
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Document ready
     */
    $(document).ready(function() {
        // Add any additional frontend JavaScript here
        // Form validation is already handled in the template

        // Example: Add smooth scrolling
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
    });

})(jQuery);
