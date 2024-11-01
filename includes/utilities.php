<?php
/**
* Common utilities for the plugin
*
*/

if( ! defined( 'ABSPATH' ) ) exit;

class SRR_Utilities{

    public static function init(){

        add_filter( 'the_excerpt_rss', array( __CLASS__, 'insert_featured_image_rss_feed' ) );
        add_filter( 'the_content_feed', array( __CLASS__, 'insert_featured_image_rss_feed' ) );

        add_filter( 'commentrss2_item', array( __CLASS__, 'insert_avatar_comments_feed' ), 10, 2 );

        add_filter( 'wp_feed_options', array( __CLASS__, 'set_user_agent' ), 10, 2 );

    }

    public static function insert_featured_image_rss_feed( $content ){

        global $post;

        preg_match_all( '~<img.*?src=["\']+(.*?)["\']+~', $content ?? '', $image_urls );

        if( empty( $image_urls[1] ) && has_post_thumbnail( $post->ID ) ) {
            $content = '<p>' . get_the_post_thumbnail( $post->ID ) . '</p>' . $content;
        }

        return $content;

    }

    public static function insert_avatar_comments_feed( $comment_id, $comment_post_id ){

        $comment = get_comment( $comment_id );
        $avatar_url = get_avatar_url( $comment );

        if( $avatar_url ){
            echo '<media:thumbnail url="' . esc_attr( $avatar_url ) . '" />';
        }

    }

    public static function set_user_agent( &$feed, $url ) {

        $feed->set_useragent( 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36' );

    }

    public static function date_i18n( $format, $timestamp, $timezone ){

        try{
            $timezone = new DateTimeZone( trim( $timezone ) );
        }catch( Exception $e ) {
            $timezone = new DateTimeZone( 'UTC' );
        }

        $date = wp_date( $format, $timestamp, $timezone );

        return $date;

    }

    public static function is_valid_image_url( $item, $url ){

        // Does it begin with http?
        if( strpos( $url, 'http' ) === 0 ){
            return true;
        }

        // If it does not begin with http, then check if the RSS feed is of the current domain
        $wp_domain = parse_url( get_site_url(), PHP_URL_HOST );
        return strpos( $item->get_link(), $wp_domain ) !== false;

    }

    public static function parse_image_url( $item, $content ){

        $attributes = array(
            'src' => '~<img.*?src=["\']+(.*?)["\']+~',
            'srcset' => '~<img.*?srcset=["\']+(.*?)["\']+~'
        );

        foreach( $attributes as $attribute => $regex ){
            preg_match( $regex, $content ?? '', $urls );

            if( empty( $urls ) ){
                continue;
            }
            $parsed_url = $urls[1];

            if( $attribute == 'srcset' ){
                $srcset_urls = explode( ',' , $parsed_url );
                $parsed_url = explode( ' ', $srcset_urls[0] )[0];
            }

            if( self::is_valid_image_url( $item, $parsed_url ) ){
                return $parsed_url;
            }
        }

        return '';

    }

}

SRR_Utilities::init();

?>