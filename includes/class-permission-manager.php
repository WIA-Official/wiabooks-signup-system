<?php
/**
 * WIABooks Permission Manager Class
 *
 * Handles user permissions and category-based access control.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WIABooks Permission Manager Class
 *
 * @class WIABooks_Permission_Manager
 * @version 1.0.0
 */
class WIABooks_Permission_Manager {

    /**
     * The single instance of the class
     *
     * @var WIABooks_Permission_Manager
     */
    protected static $_instance = null;

    /**
     * Main WIABooks_Permission_Manager Instance
     *
     * @static
     * @return WIABooks_Permission_Manager - Main instance
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
        // Add custom capabilities
        add_action( 'init', array( $this, 'add_custom_capabilities' ) );

        // Filter posts in admin
        add_filter( 'pre_get_posts', array( $this, 'filter_posts_by_category' ) );

        // Check permissions before editing/deleting posts
        add_filter( 'user_has_cap', array( $this, 'check_post_edit_permission' ), 10, 4 );

        // Remove category dropdown from non-admin users
        add_action( 'admin_head-post-new.php', array( $this, 'customize_post_editor' ) );
        add_action( 'admin_head-post.php', array( $this, 'customize_post_editor' ) );

        // Auto-assign category when creating post
        add_action( 'save_post', array( $this, 'auto_assign_category' ), 10, 3 );

        // Restrict category selection in admin
        add_filter( 'wp_dropdown_cats', array( $this, 'restrict_category_dropdown' ), 10, 2 );
    }

    /**
     * Add custom capabilities to author role
     */
    public function add_custom_capabilities() {
        $role = get_role( 'author' );

        if ( $role ) {
            // Add custom capability for managing own category posts
            $role->add_cap( 'edit_own_category_posts' );
        }
    }

    /**
     * Filter posts by category in admin
     *
     * Authors can only see posts from their own category
     *
     * @param WP_Query $query WordPress query object
     * @return WP_Query Modified query
     */
    public function filter_posts_by_category( $query ) {
        // Only apply in admin area for posts
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return $query;
        }

        // Skip for administrators
        if ( current_user_can( 'manage_options' ) ) {
            return $query;
        }

        // Only apply to post queries
        if ( $query->get( 'post_type' ) !== 'post' && ! $query->is_singular() ) {
            return $query;
        }

        $user_id = get_current_user_id();
        $user_category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

        if ( $user_category_id ) {
            $query->set( 'cat', $user_category_id );
        }

        return $query;
    }

    /**
     * Check if user has permission to edit/delete post
     *
     * @param array   $allcaps All capabilities
     * @param array   $caps    Required capabilities
     * @param array   $args    Additional arguments
     * @param WP_User $user    User object
     * @return array Modified capabilities
     */
    public function check_post_edit_permission( $allcaps, $caps, $args, $user ) {
        // Skip for administrators
        if ( isset( $allcaps['manage_options'] ) && $allcaps['manage_options'] ) {
            return $allcaps;
        }

        // Check if this is a post edit/delete capability check
        if ( ! isset( $args[0] ) || ( $args[0] !== 'edit_post' && $args[0] !== 'delete_post' ) ) {
            return $allcaps;
        }

        // Get post ID
        if ( ! isset( $args[2] ) ) {
            return $allcaps;
        }

        $post_id = $args[2];
        $post = get_post( $post_id );

        if ( ! $post ) {
            return $allcaps;
        }

        // Get user's category
        $user_category_id = get_user_meta( $user->ID, 'wiabooks_book_category_id', true );

        if ( ! $user_category_id ) {
            // User has no category assigned - deny
            foreach ( $caps as $cap ) {
                $allcaps[ $cap ] = false;
            }
            return $allcaps;
        }

        // Get post categories
        $post_categories = wp_get_post_categories( $post_id );

        // Check if post belongs to user's category
        if ( ! in_array( $user_category_id, $post_categories, true ) ) {
            // Post is not in user's category - deny
            foreach ( $caps as $cap ) {
                $allcaps[ $cap ] = false;
            }
        }

        return $allcaps;
    }

    /**
     * Customize post editor for authors
     */
    public function customize_post_editor() {
        // Skip for administrators
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        $user_id = get_current_user_id();
        $user_category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

        if ( ! $user_category_id ) {
            return;
        }

        $category = get_category( $user_category_id );

        if ( ! $category ) {
            return;
        }

        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Hide category metabox
            $('#categorydiv').hide();

            // Add notice about automatic category assignment
            $('#titlediv').after(
                '<div class="notice notice-info inline"><p>' +
                '<?php echo esc_js( sprintf( __( '이 글은 자동으로 "%s" 카테고리에 할당됩니다.', 'wiabooks-signup' ), esc_html( $category->name ) ) ); ?>' +
                '</p></div>'
            );
        });
        </script>
        <?php
    }

    /**
     * Auto-assign category when creating/updating post
     *
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     * @param bool    $update  Whether this is an update
     */
    public function auto_assign_category( $post_id, $post, $update ) {
        // Skip autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Skip revisions
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Only for posts
        if ( $post->post_type !== 'post' ) {
            return;
        }

        // Skip for administrators
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        $user_id = $post->post_author;
        $user_category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

        if ( $user_category_id ) {
            // Set the post to user's category
            wp_set_post_categories( $post_id, array( $user_category_id ), false );
        }
    }

    /**
     * Restrict category dropdown for authors
     *
     * @param string $output HTML output
     * @param array  $args   Arguments
     * @return string Modified HTML output
     */
    public function restrict_category_dropdown( $output, $args ) {
        // Skip for administrators
        if ( current_user_can( 'manage_options' ) ) {
            return $output;
        }

        $user_id = get_current_user_id();
        $user_category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

        if ( ! $user_category_id ) {
            return $output;
        }

        // Get user's category
        $category = get_category( $user_category_id );

        if ( ! $category ) {
            return $output;
        }

        // Create simple dropdown with only user's category
        $new_output = '<select name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $args['class'] ) . '">';
        $new_output .= '<option value="' . esc_attr( $category->term_id ) . '" selected="selected">' . esc_html( $category->name ) . '</option>';
        $new_output .= '</select>';

        return $new_output;
    }

    /**
     * Check if user can access category
     *
     * @param int $user_id     User ID
     * @param int $category_id Category ID
     * @return bool True if user can access category
     */
    public function user_can_access_category( $user_id, $category_id ) {
        // Administrators can access all categories
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        // Check if category matches user's book category
        $user_category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

        return (int) $user_category_id === (int) $category_id;
    }

    /**
     * Get user statistics
     *
     * @param int $user_id User ID
     * @return array Statistics
     */
    public function get_user_stats( $user_id ) {
        $user_category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

        $stats = array(
            'total_posts' => 0,
            'published_posts' => 0,
            'draft_posts' => 0,
            'total_words' => 0,
            'credit_balance' => get_user_meta( $user_id, 'wiabooks_credit', true ),
        );

        if ( ! $user_category_id ) {
            return $stats;
        }

        // Get posts count
        $posts = get_posts( array(
            'author' => $user_id,
            'category' => $user_category_id,
            'post_status' => 'any',
            'posts_per_page' => -1,
        ) );

        $stats['total_posts'] = count( $posts );

        foreach ( $posts as $post ) {
            if ( $post->post_status === 'publish' ) {
                $stats['published_posts']++;
            } elseif ( $post->post_status === 'draft' ) {
                $stats['draft_posts']++;
            }

            // Count words
            $stats['total_words'] += str_word_count( wp_strip_all_tags( $post->post_content ) );
        }

        return $stats;
    }
}
