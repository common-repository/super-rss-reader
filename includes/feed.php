<?php
/**
* Prepares the feed HTML
*
*/

if( ! defined( 'ABSPATH' ) ) exit;

class SRR_Feed{

    public $options = array();

    public function __construct( $options ){

        $this->options = wp_parse_args( $options, SRR_Options::defaults() );

    }

    public function html(){

        $urls = trim( $this->options['urls'] );
        $tab_titles = $this->options['tab_titles'];
        $count = intval( $this->options['count'] );

        $show_title = intval( $this->options['show_title'] );
        $show_date = intval( $this->options['show_date'] );
        $show_desc = intval( $this->options['show_desc'] );
        $show_author = intval( $this->options['show_author'] );
        $show_thumb = intval( $this->options['show_thumb'] );
        $open_newtab = intval( $this->options['open_newtab'] );
        $add_nofollow = intval( $this->options['add_nofollow'] );
        $strip_desc = intval( $this->options['strip_desc'] );
        $strip_title = intval( $this->options['strip_title'] );
        $offset = intval( $this->options['offset'] );
        $date_format = $this->options['date_format'];
        $date_timezone = $this->options['date_timezone'];
        $order_by = $this->options['order_by'];
        $read_more = $this->options['read_more'];
        $rich_desc = intval( $this->options['rich_desc'] );
        $link_desc = intval( $this->options['link_desc'] );
        $desc_type = $this->options['desc_type'];
        $thumbnail_position = $this->options['thumbnail_position'];
        $thumbnail_size = $this->options['thumbnail_size'];
        $thumbnail_default = $this->options['thumbnail_default'];
        $no_feed_text = $this->options['no_feed_text'];

        $color_theme = $this->options['color_style'];
        $display_type = $this->options['display_type'];
        $visible_items = intval( $this->options['visible_items'] );
        $ticker_speed = intval( $this->options['ticker_speed'] ) * 1000;
        $scroll_height = $this->options['scroll_height'];

        if( empty( $urls ) ){
            return '';
        }

        $count = ( $offset > 0 ) ? $count + $offset : $count;

        $url_delim = strpos( $urls, ',' ) !== false ? ',' : "\n";
        $tab_title_delim = strpos( $tab_titles, ',' ) !== false ? ',' : "\n";

        $urls = explode( $url_delim, $urls );
        $tab_titles = explode( $tab_title_delim, $tab_titles );
        $url_count = count( $urls );

        $feeds = array();
        $html = '';
        $no_feed_html = '<div>' . wp_kses_post( $no_feed_text ) . '</div>';

        $classes = array( 'srr-wrap', 'srr-style-' . $color_theme );
        if( $display_type == 'vertical_ticker' ) array_push( $classes, 'srr-vticker' );
        if( $display_type == 'scroll' ) array_push( $classes, 'srr-scroll' );
        $class = implode( ' ', $classes );

        $style = '';
        if( $display_type == 'scroll' ){
            $style .= '--srr-height: ' . $scroll_height;
        }
        if( !empty( $style ) ) $style = 'style="' . esc_attr( $style ) . '"';

        // Fetch the feed
        for( $i=0; $i < $url_count; $i++ ){
            $feed_url = trim( $urls[$i] );

            // Skip if the RSS feed URL is same as the site URL
            if ( in_array( untrailingslashit( $feed_url ), array( site_url(), home_url() ), true ) ) {
                continue;
            }

            $feed = fetch_feed( $feed_url );

            if( is_wp_error( $feed ) ){
                $feed_title = __( 'Error' );
            }else{
                $feed_title = ( isset( $tab_titles[$i] ) && !empty( $tab_titles[$i] ) ) ? $tab_titles[$i] : strip_tags( $feed->get_title() ?? '' );
            }

            $feeds[ $feed_url ] = array(
                'id' => rand( 100, 999 ),
                'feed' => $feed,
                'title' => $feed_title
            );
        }

        // Generate tabs
        if( $url_count > 1 ){
            $html .= '<ul class="srr-tab-wrap srr-tab-style-' . esc_attr( $color_theme ) . ' srr-clearfix">';
            foreach( $feeds as $url => $data ){
                $id = $data[ 'id' ];
                $feed = $data[ 'feed' ];
                $html .= '<li data-tab="srr-tab-' . esc_attr( $id ) . '">' . wp_kses_post( $data[ 'title' ] ) . '</li>';
            }
            $html .= '</ul>';
        }

        // Generate feed items
        foreach( $feeds as $url => $data ){

            $id = $data[ 'id' ];
            $feed = $data[ 'feed' ];

            // Check for feed errors
            if ( is_wp_error( $feed ) ){
                $html .= '<div class="srr-wrap srr-style-' . esc_attr( $color_theme ) .'" data-id="srr-tab-' . esc_attr( $id ) . '"><p>RSS Error: ' . wp_kses_post( $feed->get_error_message() ) . '</p></div>';
                continue;
            }

            if( method_exists( $feed, 'enable_order_by_date' ) ){
                if( in_array( $order_by, array( 'date', 'date_reverse' ) ) ){
                    $feed->enable_order_by_date( true );
                }else{
                    $feed->enable_order_by_date( false );
                }
            }

            // Outer wrap start
            $html .= '<div class="' . esc_attr( $class ) . '" data-visible="' . esc_attr( $visible_items ) . '" data-speed="' . esc_attr( $ticker_speed ) . '" data-id="srr-tab-' . esc_attr( $id ) . '" ' . $style . '>';
            $html .= '<div class="srr-inner">';

            $max_items = $feed->get_item_quantity();

            // Check feed items
            if ( $max_items == 0 ){
                $html .= $no_feed_html;
            }else{

                $feed_items = $feed->get_items();
                $feed_items = $this->process_items( $feed_items, $count, $order_by );
                $j=1;

                // Loop through each feed item
                foreach( $feed_items as $item ){

                    // Offset items
                    if( $j <= $offset ){
                        $j++;
                        continue;
                    }

                    // Link
                    $link = $item->get_link();
                    while ( stristr( $link, 'http' ) != $link ){ $link = substr( $link, 1 ); }
                    $link = strip_tags($link);

                    // Title
                    $title = strip_tags( $item->get_title() ?? '' );
                    $title_full = $title;

                    if ( empty( $title ) ){
                        $title = __( 'No Title', 'super-rss-reader' );
                    }

                    if( $strip_title > 0 && strlen( $title ) > $strip_title ){
                        $title = wp_trim_words( $title, $strip_title );
                    }

                    // Open links in new tab
                    $new_tab = $open_newtab ? ' target="_blank"' : '';

                    // Add no follow attribute
                    $no_follow = $add_nofollow ? ' rel="nofollow noopener noreferrer"' : '';

                    if( empty( $link ) ){
                        $link = '#';
                        $new_tab = '';
                    }

                    // Date
                    $date = '';
                    $date_full = strip_tags( $item->get_date() ?? '' );

                    if( strtolower( $date_format ) == 'relative' ){
                        $item_date = $item->get_date( 'U' );
                        if( $item_date ){
                            $date = human_time_diff( $item_date, current_time( 'U' ) ) . ' ' . __( 'ago', 'super-rss-reader' );
                        }else{
                            $date = __( 'Today', 'super-rss-reader' );
                        }
                    }else{
                        $date = SRR_Utilities::date_i18n( $date_format, $item->get_date( 'U' ), $date_timezone );
                    }

                    // Thumbnail
                    $thumb = '';
                    if ( $show_thumb == 1 ){
                        $thumb_url = $this->get_thumbnail_url( $item, $thumbnail_default );
                        $thumb_url = apply_filters( 'srr_mod_thumbnail_url', $thumb_url, $item, $feed, $thumbnail_default );
                        if( !empty( $thumb_url ) ){
                            if( strpos( $thumbnail_size, ',' ) ){
                                $thumb_size_split = explode( ',', $thumbnail_size );
                                $thumb_width = $thumb_size_split[0];
                                $thumb_height = $thumb_size_split[1];
                            }else{
                                $thumb_width = $thumb_height = $thumbnail_size;
                            }
                            $thumb_styles = array(
                                'width' => $thumb_width,
                                'height' => $thumb_height
                            );
                            $thumb_style = '';
                            foreach( $thumb_styles as $prop => $val ){
                                $thumb_style .= "$prop:$val;";
                            }
                            $thumb = '<a href="' . esc_url( $link ) . '" class="srr-thumb srr-thumb-' . esc_attr( $thumbnail_position ) . '" style="' . esc_attr( $thumb_style ) . '" ' . $new_tab . $no_follow . '><img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $title_full ) . '" align="left"' . ( wp_lazy_loading_enabled( 'img', 'srr-thumbnail' ) ? ' loading="lazy"' : '' ) . ' /></a>';
                        }
                    }

                    // Description
                    $desc = '';
                    if( $show_desc ){
                        $desc_content = ( $desc_type == 'summary' ) ? ( $item->get_description() ?? '') : ( $item->get_content() ?? '' );
                        if( $rich_desc ){
                            $desc = wp_kses_post( strip_tags( $desc_content, '<p><a><img><em><strong><font><strike><s><u><b><i><br>' ) );
                        }else{
                            $desc = str_replace( array( "\n", "\r" ), ' ', strip_tags( @html_entity_decode( $desc_content, ENT_QUOTES, get_option('blog_charset') ) ) );

                            if( $strip_desc != 0 ){
                                $desc = wp_trim_words( $desc, $strip_desc );
                                if ( '[...]' == substr( $desc, -5 ) ){
                                    $desc = substr( $desc, 0, -5 );
                                }else if ( '&hellip;' == substr( $desc, -8 ) ){
                                    $desc = substr( $desc, 0, -8 );
                                }else if ( '...' == substr( $desc, -3 ) ){
                                    $desc = substr( $desc, 0, -3 );
                                }elseif ( '[&hellip;]' != substr( $desc, -10 ) ){
                                    $desc .= '';
                                }
                            }

                            $desc = trim( esc_html( $desc ) );
                            if( !empty( $desc ) ){
                                if( $link_desc ){
                                    $desc = '<a href="' . esc_url( $link ) . '"' . $new_tab . $no_follow . ' class="srr-desc-link">' . $desc . '</a>';
                                }
                                $read_more_link = !empty( $read_more ) ? ' <a href="' . esc_url( $link ) . '" title="' . esc_attr__( 'Read more', 'super-rss-reader' ) . '"' . $new_tab . $no_follow . ' class="srr-read-more">' . esc_html( $read_more ) . '</a>' : '';
                                $desc = $desc . $read_more_link;
                            }

                        }
                    }

                    // Author
                    $author = '';
                    if( $show_author ){
                        $author = $item->get_author();
                        if ( is_object( $author ) && $author->get_name() ) {
                            $author = strip_tags( $author->get_name() );
                        }
                    }

                    $t_title = '';
                    $t_meta = '';
                    $t_thumb = '';
                    $t_desc = '';

                    if( $show_title ){
                        $t_title .= '<div class="srr-title"><a href="' . esc_url( $link ) . '"' . $new_tab . $no_follow . ' title="' . esc_attr( $title_full ) . '">' . esc_html( $title ) . '</a></div>';
                    }

                    // Metadata
                    if( $show_date || $show_author ){
                        $t_meta .= '<div class="srr-meta">';
                        if( $show_date && !empty( $date ) ){
                            $t_meta .= '<time class="srr-date" title="' . esc_attr( $date_full ) . ' UTC">' . esc_html( $date ) . '</time>';
                        }

                        if( $show_author && !empty( $author ) ){
                            $t_meta .= ' - <cite class="srr-author">' . esc_html( $author ) . '</cite>';
                        }
                        $t_meta .= '</div>'; // End meta
                    }

                    if ( $show_thumb ){
                        $t_thumb .= $thumb;
                    }

                    if( $show_desc && !empty( $desc ) ){
                        $t_desc .= '<div class="srr-summary srr-clearfix">';
                        $t_desc .= $rich_desc ? $desc : ( '<p>' . $desc . '</p>' );
                        $t_desc .= '</div>'; // End summary
                    }

                    $f_data = apply_filters( 'srr_mod_item_html', array(
                        'title' => $t_title,
                        'meta' => $t_meta,
                        'thumbnail' => $t_thumb,
                        'description' => $t_desc,
                        'before' => '',
                        'after' => ''
                    ), $url, $item );

                    $f_title = !isset( $f_data[ 'title' ] ) ? '' : $f_data[ 'title' ];
                    $f_meta = !isset( $f_data[ 'meta' ] ) ? '' : $f_data[ 'meta' ];
                    $f_thumb = !isset( $f_data[ 'thumbnail' ] ) ? '' : $f_data[ 'thumbnail' ];
                    $f_desc = !isset( $f_data[ 'description' ] ) ? '' : $f_data[ 'description' ];
                    $f_before = !isset( $f_data[ 'before' ] ) ? '' : $f_data[ 'before' ];
                    $f_after = !isset( $f_data[ 'after' ] ) ? '' : $f_data[ 'after' ];

                    // Display the feed items
                    $html .= '<div class="srr-item ' . ( ( $j%2 == 0 ) ? 'srr-stripe' : '') . '">';
                    $html .= '<div class="srr-item-in srr-clearfix">';
                    $html .= $f_before;
                    $html .= $f_title . $f_meta . $f_thumb . $f_desc;
                    $html .= $f_after;
                    $html .= '</div>'; // End item inner clearfix
                    $html .= '</div>'; // End feed item

                    $j++;
                }

                if( $j == 1 ){
                    $html .= $no_feed_html;
                }

            }
            
            // Outer wrap end
            $html .= '</div></div>' ;
            
            if( !is_wp_error( $feed ) )
                $feed->__destruct();

            unset( $feed );

        }

        $html = '<div class="srr-main">' . $html . '</div>';

        return $html;

    }

    function process_items( $items, $count, $order_by ){

        if( count( $items ) == 0 ){
            return $items;
        }

        // For shuffle order - Shuffle first and then slice as per count
        if( $order_by == 'random' ){
            shuffle( $items );
            return array_slice( $items, 0, $count );
        }

        if( $order_by == 'date_reverse' ){
            $items = array_reverse( $items );
        }

        // For date based order - slice first and then order
        $items = array_slice( $items, 0, $count );

        return $items;

    }

    function get_thumbnail_url( $item, $thumbnail_default ){

        // Try to get from the item enclosure
        $enclosure = $item->get_enclosure();

        if ( $enclosure->get_thumbnail() ) {
            return $enclosure->get_thumbnail();
        }

        if ( $enclosure->get_link() ) {
            $enclosure_link = $enclosure->get_link();
            if( SRR_Utilities::is_valid_image_url( $item, $enclosure_link) ){
                return $enclosure_link;
            }
        }

        // Try to get from item content
        $content = $item->get_content();
        $image = SRR_Utilities::parse_image_url( $item, $content );

        if( !empty( $image ) ){
            return $image;
        }

        // Try to get the image tag finally if available
        $image = $item->get_item_tags( '', 'image' );

        if( isset( $image[0]['data'] ) ){
            return $image[0]['data'];
        }

        return trim( $thumbnail_default );

    }

}

?>