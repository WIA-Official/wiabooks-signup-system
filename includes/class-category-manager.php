<?php
/**
 * WIABooks Category Manager Class
 *
 * Handles automatic category creation with Korean to English slug conversion.
 *
 * @package WIABooks_Signup_System
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WIABooks Category Manager Class
 *
 * @class WIABooks_Category_Manager
 * @version 1.0.0
 */
class WIABooks_Category_Manager {

    /**
     * The single instance of the class
     *
     * @var WIABooks_Category_Manager
     */
    protected static $_instance = null;

    /**
     * Korean romanization map
     *
     * @var array
     */
    private $romanization_map = array();

    /**
     * Main WIABooks_Category_Manager Instance
     *
     * @static
     * @return WIABooks_Category_Manager - Main instance
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
        $this->init_romanization_map();
    }

    /**
     * Initialize Korean romanization map
     */
    private function init_romanization_map() {
        // Korean consonants (초성)
        $cho = array( 'g', 'kk', 'n', 'd', 'tt', 'r', 'm', 'b', 'pp', 's', 'ss', '', 'j', 'jj', 'ch', 'k', 't', 'p', 'h' );

        // Korean vowels (중성)
        $jung = array( 'a', 'ae', 'ya', 'yae', 'eo', 'e', 'yeo', 'ye', 'o', 'wa', 'wae', 'oe', 'yo', 'u', 'wo', 'we', 'wi', 'yu', 'eu', 'ui', 'i' );

        // Korean final consonants (종성)
        $jong = array( '', 'k', 'k', 'k', 'n', 'n', 'n', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'm', 'p', 'p', 't', 't', 'ng', 't', 't', 'k', 't', 'p', 't' );

        $this->romanization_map = array(
            'cho' => $cho,
            'jung' => $jung,
            'jong' => $jong,
        );
    }

    /**
     * Create book category
     *
     * Creates a WordPress category with Korean title and romanized slug
     *
     * @param string $book_title Book title (Korean)
     * @param string $description Book description (optional)
     * @return int|WP_Error Category ID on success, WP_Error on failure
     */
    public function create_book_category( $book_title, $description = '' ) {
        try {
            // Sanitize inputs
            $book_title = sanitize_text_field( $book_title );
            $description = sanitize_textarea_field( $description );

            // Generate slug from Korean title
            $slug = $this->romanize_korean( $book_title );

            // Ensure slug is unique
            $slug = $this->ensure_unique_slug( $slug );

            // Create category
            $category_data = array(
                'cat_name' => $book_title,
                'category_description' => $description,
                'category_nicename' => $slug,
                'taxonomy' => 'category',
            );

            $category_id = wp_insert_category( $category_data );

            if ( is_wp_error( $category_id ) ) {
                error_log( 'WIABooks Category Creation Error: ' . $category_id->get_error_message() );
                return new WP_Error( 'category_creation_failed', __( '카테고리 생성에 실패했습니다.', 'wiabooks-signup' ) );
            }

            // Log category creation
            error_log( sprintf( 'WIABooks: Category created - ID: %d, Title: %s, Slug: %s', $category_id, $book_title, $slug ) );

            return $category_id;

        } catch ( Exception $e ) {
            error_log( 'WIABooks Category Manager Exception: ' . $e->getMessage() );
            return new WP_Error( 'exception', __( '오류가 발생했습니다.', 'wiabooks-signup' ) );
        }
    }

    /**
     * Romanize Korean text to English
     *
     * Converts Korean characters to romanized English using Revised Romanization
     *
     * @param string $text Korean text
     * @return string Romanized text
     */
    public function romanize_korean( $text ) {
        $romanized = '';

        // Convert text to UTF-8 array
        $chars = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );

        foreach ( $chars as $char ) {
            $code = $this->utf8_ord( $char );

            // Check if character is Hangul (Korean)
            if ( $code >= 0xAC00 && $code <= 0xD7A3 ) {
                $romanized .= $this->romanize_hangul_char( $code );
            } elseif ( preg_match( '/[a-zA-Z0-9]/', $char ) ) {
                // Keep alphanumeric characters
                $romanized .= strtolower( $char );
            } elseif ( $char === ' ' ) {
                // Replace spaces with hyphens
                $romanized .= '-';
            }
            // Skip other characters
        }

        // Clean up the slug
        $romanized = preg_replace( '/[^a-z0-9-]/', '', $romanized );
        $romanized = preg_replace( '/-+/', '-', $romanized );
        $romanized = trim( $romanized, '-' );

        return $romanized;
    }

    /**
     * Romanize a single Hangul character
     *
     * @param int $code Unicode code point
     * @return string Romanized character
     */
    private function romanize_hangul_char( $code ) {
        $code -= 0xAC00;

        $cho_index = (int) ( $code / 588 );
        $jung_index = (int) ( ( $code % 588 ) / 28 );
        $jong_index = $code % 28;

        $cho = $this->romanization_map['cho'][ $cho_index ];
        $jung = $this->romanization_map['jung'][ $jung_index ];
        $jong = $this->romanization_map['jong'][ $jong_index ];

        return $cho . $jung . $jong;
    }

    /**
     * Get UTF-8 character code
     *
     * @param string $char UTF-8 character
     * @return int Unicode code point
     */
    private function utf8_ord( $char ) {
        $ord0 = ord( $char[0] );

        if ( $ord0 >= 0 && $ord0 <= 127 ) {
            return $ord0;
        }

        $ord1 = ord( $char[1] );

        if ( $ord0 >= 192 && $ord0 <= 223 ) {
            return ( $ord0 - 192 ) * 64 + ( $ord1 - 128 );
        }

        $ord2 = ord( $char[2] );

        if ( $ord0 >= 224 && $ord0 <= 239 ) {
            return ( $ord0 - 224 ) * 4096 + ( $ord1 - 128 ) * 64 + ( $ord2 - 128 );
        }

        $ord3 = ord( $char[3] );

        if ( $ord0 >= 240 && $ord0 <= 247 ) {
            return ( $ord0 - 240 ) * 262144 + ( $ord1 - 128 ) * 4096 + ( $ord2 - 128 ) * 64 + ( $ord3 - 128 );
        }

        return 0;
    }

    /**
     * Ensure slug is unique
     *
     * Appends number to slug if it already exists
     *
     * @param string $slug Original slug
     * @return string Unique slug
     */
    private function ensure_unique_slug( $slug ) {
        $original_slug = $slug;
        $counter = 1;

        while ( term_exists( $slug, 'category' ) ) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get user's book category
     *
     * @param int $user_id User ID
     * @return object|null Category object or null
     */
    public function get_user_book_category( $user_id ) {
        $category_id = get_user_meta( $user_id, 'wiabooks_book_category_id', true );

        if ( ! $category_id ) {
            return null;
        }

        return get_category( $category_id );
    }

    /**
     * Update book category
     *
     * @param int $category_id Category ID
     * @param string $title New title (optional)
     * @param string $description New description (optional)
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_book_category( $category_id, $title = '', $description = '' ) {
        try {
            $args = array();

            if ( ! empty( $title ) ) {
                $args['name'] = sanitize_text_field( $title );
                $args['slug'] = $this->romanize_korean( $title );
            }

            if ( ! empty( $description ) ) {
                $args['description'] = sanitize_textarea_field( $description );
            }

            if ( empty( $args ) ) {
                return new WP_Error( 'no_data', __( '업데이트할 데이터가 없습니다.', 'wiabooks-signup' ) );
            }

            $result = wp_update_term( $category_id, 'category', $args );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            return true;

        } catch ( Exception $e ) {
            error_log( 'WIABooks Category Update Error: ' . $e->getMessage() );
            return new WP_Error( 'exception', __( '오류가 발생했습니다.', 'wiabooks-signup' ) );
        }
    }

    /**
     * Delete book category
     *
     * @param int $category_id Category ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_book_category( $category_id ) {
        try {
            // Check if category has posts
            $posts = get_posts( array(
                'category' => $category_id,
                'posts_per_page' => 1,
            ) );

            if ( ! empty( $posts ) ) {
                return new WP_Error( 'has_posts', __( '이 카테고리에는 게시글이 있어 삭제할 수 없습니다.', 'wiabooks-signup' ) );
            }

            $result = wp_delete_term( $category_id, 'category' );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            return true;

        } catch ( Exception $e ) {
            error_log( 'WIABooks Category Delete Error: ' . $e->getMessage() );
            return new WP_Error( 'exception', __( '오류가 발생했습니다.', 'wiabooks-signup' ) );
        }
    }
}
