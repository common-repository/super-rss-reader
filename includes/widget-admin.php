<?php
/**
* Contains WordPress widget class
*
*/

class super_rss_reader_widget extends WP_Widget{

    // Initialize
    public function __construct(){
        $widget_ops = array(
            'classname' => 'widget_super_rss_reader',
            'description' => __( 'An RSS feed reader widget with advanced features', 'super-rss-reader' )
        );
        
        $control_ops = array( 'width' => 500, 'height' => 500 );
        parent::__construct( 'super_rss_reader', 'Super RSS Reader', $widget_ops, $control_ops );
    }
    
    // Display the Widget
    public function widget( $args, $instance ){

        extract( $args );

        if( empty( $instance[ 'title' ] ) ){
            $title = '';
        }else{
            $title = $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;
        }

        echo $before_widget . $title;

        echo '<!-- Start - Super RSS Reader v' . SRR_VERSION . '-->
        <div class="super-rss-reader-widget">';

        SRR_Widget::render_feed( $instance );

        echo '</div><!-- End - Super RSS Reader -->';
        echo $after_widget;

    }
    
    // Save settings
    public function update( $new_instance, $old_instance ){

        $instance = $old_instance;

        $instance[ 'title' ] = sanitize_text_field( $new_instance['title'] );
        $instance[ 'urls' ] = sanitize_textarea_field( $new_instance['urls'] ) ;
        $instance[ 'tab_titles' ] = wp_kses_post( $new_instance['tab_titles'] );
        
        $instance[ 'count' ] = intval( $new_instance['count'] );
        $instance[ 'show_title' ] = intval( isset( $new_instance['show_title'] ) ? $new_instance['show_title'] : 0 );
        $instance[ 'show_date' ] = intval( isset( $new_instance['show_date'] ) ? $new_instance['show_date'] : 0 );
        $instance[ 'show_desc' ] = intval( isset( $new_instance['show_desc'] ) ? $new_instance['show_desc'] : 0 );
        $instance[ 'show_author' ] = intval( isset( $new_instance['show_author'] ) ? $new_instance['show_author'] : 0 );
        $instance[ 'show_thumb' ] = intval( isset( $new_instance['show_thumb'] ) ? $new_instance['show_thumb'] : 0 );
        $instance[ 'strip_desc' ] = intval( $new_instance['strip_desc'] );
        $instance[ 'strip_title' ] = intval( $new_instance['strip_title'] );
        $instance[ 'offset' ] = intval( $new_instance['offset'] );
        $instance[ 'date_format' ] = sanitize_text_field( $new_instance['date_format'] );
        $instance[ 'date_timezone' ] = sanitize_text_field( $new_instance['date_timezone'] );
        $instance[ 'order_by' ] = sanitize_text_field( $new_instance['order_by'] );
        $instance[ 'read_more' ] = sanitize_text_field( $new_instance['read_more'] );
        $instance[ 'add_nofollow' ] = intval( isset( $new_instance['add_nofollow'] ) ? $new_instance['add_nofollow'] : 0 );
        $instance[ 'open_newtab' ] = intval( isset( $new_instance['open_newtab'] ) ? $new_instance['open_newtab'] : 0 );
        $instance[ 'rich_desc' ] = intval( isset( $new_instance['rich_desc'] ) ? $new_instance['rich_desc'] : 0 );
        $instance[ 'link_desc' ] = intval( isset( $new_instance['link_desc'] ) ? $new_instance['link_desc'] : 0 );
        $instance[ 'desc_type' ] = sanitize_text_field( $new_instance['desc_type'] );
        $instance[ 'thumbnail_position' ] = sanitize_text_field( $new_instance['thumbnail_position'] );
        $instance[ 'thumbnail_size' ] = sanitize_text_field( $new_instance['thumbnail_size'] );
        $instance[ 'thumbnail_default' ] = sanitize_text_field( $new_instance['thumbnail_default'] );
        $instance[ 'no_feed_text' ] = wp_kses_post( $new_instance['no_feed_text'] );

        $instance[ 'color_style' ] = sanitize_text_field( $new_instance['color_style']);
        $instance[ 'display_type' ] = sanitize_text_field( $new_instance['display_type']);
        $instance[ 'visible_items' ] = intval( $new_instance['visible_items'] );
        $instance[ 'ticker_speed' ] = intval( $new_instance['ticker_speed'] );
        $instance[ 'scroll_height' ] = sanitize_text_field( $new_instance['scroll_height'] );
        
        return $instance;
    }
    
    // Widget form
    public function form( $instance ){

        $instance = wp_parse_args( (array) $instance, SRR_Options::defaults() );

        $title = isset( $instance['title'] ) ? $instance[ 'title' ] : '';
        $urls = $instance['urls'];
        $tab_titles = $instance['tab_titles'];
        
        $count = $instance['count'];
        $show_title = $instance['show_title'];
        $show_date = $instance['show_date'];
        $show_desc = $instance['show_desc'];
        $show_author = $instance['show_author'];
        $show_thumb = $instance['show_thumb'];
        $open_newtab = $instance['open_newtab'];
        $add_nofollow = $instance['add_nofollow'];
        $strip_desc = $instance['strip_desc'];
        $strip_title = $instance['strip_title'];
        $offset = $instance['offset'];
        $date_format = $instance['date_format'];
        $date_timezone = $instance['date_timezone'];
        $order_by = $instance['order_by'];
        $read_more = $instance['read_more'];
        $rich_desc = $instance['rich_desc'];
        $desc_type = $instance['desc_type'];
        $link_desc = $instance['link_desc'];
        $thumbnail_position = $instance['thumbnail_position'];
        $thumbnail_size = $instance['thumbnail_size'];
        $thumbnail_default = $instance['thumbnail_default'];
        $no_feed_text = $instance['no_feed_text'];
        
        $color_style = $instance['color_style'];
        $display_type = $instance['display_type'];
        $visible_items = $instance['visible_items'];
        $ticker_speed = $instance['ticker_speed'];
        $scroll_height = $instance['scroll_height'];

        $option_lists = SRR_Options::select_options();

        // Replacing commas with new lines
        $urls = str_replace( ',', "\n", $urls );
        $tab_titles = str_replace( ',', "\n", $tab_titles );

        ?>

        <div class="srr_settings">

        <div class="srr_row">
            <div class="srr_label srr_xsm"><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e( 'Title', 'super-rss-reader' ); ?></label></div>
            <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat"/></div>
        </div>

        <div class="srr_row">
            <div class="srr_label srr_xsm"><label for="<?php echo esc_attr( $this->get_field_id('urls') ); ?>"><?php esc_html_e( 'URL(s)', 'super-rss-reader' ); ?></label></div>
            <div class="srr_field"><textarea id="<?php echo esc_attr( $this->get_field_id('urls') ); ?>" name="<?php echo esc_attr( $this->get_field_name('urls') ); ?>" class="widefat"><?php echo esc_html( $urls ); ?></textarea>
            <small class="srr_small_text"><?php esc_html_e( 'Can enter multiple RSS/atom feed URLs in new line', 'super-rss-reader' ); ?></small></div>
        </div>

        <div class="srr_row">
            <div class="srr_label srr_xsm"><label for="<?php echo esc_attr( $this->get_field_id('tab_titles') ); ?>"><?php esc_html_e( 'Tab titles', 'super-rss-reader' ); ?></label></div>
            <div class="srr_field"><textarea id="<?php echo esc_attr( $this->get_field_id('tab_titles') ); ?>" name="<?php echo esc_attr( $this->get_field_name('tab_titles') ); ?>" class="widefat"><?php echo esc_html( $tab_titles ); ?></textarea>
            <small class="srr_small_text"><?php esc_html_e( 'Enter corresponding tab titles in new line. Leave empty to take from feed.', 'super-rss-reader' ); ?></small></div>
        </div>

        <ul class="srr_tab_list">
            <li><a href="#" data-tab="general" class="active"><?php esc_html_e( 'General', 'super-rss-reader' ); ?></a></li>
            <li><a href="#" data-tab="content"><?php esc_html_e( 'Content', 'super-rss-reader' ); ?></a></li>
            <li><a href="#" data-tab="display"><?php esc_html_e( 'Display', 'super-rss-reader' ); ?></a></li>
            <li><a href="#" data-tab="filter"><?php esc_html_e( 'Filter', 'super-rss-reader' ); ?><span class="srr_pro_tag">PRO</span></a></li>
        </ul>

        <section data-tab-id="general" class="active">

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('count') ); ?>"><?php esc_html_e( 'Total items to show', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'Number of feed items to be displayed', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('count') ); ?>" name="<?php echo esc_attr( $this->get_field_name('count') ); ?>" type="number" value="<?php echo esc_attr( $count ); ?>" class="widefat" /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('show_title') ); ?>"><?php esc_html_e( 'Show title', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('show_title') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_title') ); ?>" value="1" <?php echo $show_title == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('show_desc') ); ?>"><?php esc_html_e( 'Show Description', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('show_desc') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_desc') ); ?>" value="1" <?php echo $show_desc == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('show_date') ); ?>"><?php esc_html_e( 'Show Date', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('show_date') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_date') ); ?>" value="1" <?php echo $show_date == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>
            
            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('show_author') ); ?>"><?php esc_html_e( 'Show Author', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('show_author') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_author') ); ?>" value="1" <?php echo $show_author == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('show_thumb') ); ?>"><?php esc_html_e( 'Show thumbnail if present', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('show_thumb') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_thumb') ); ?>" value="1" <?php echo $show_thumb == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>
        </section>

        <section data-tab-id="content">

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('add_nofollow') ); ?>"><?php esc_html_e( 'Add "no follow" attribute to links', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('add_nofollow') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('add_nofollow') ); ?>" value="1" <?php echo $add_nofollow == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('open_newtab') ); ?>"><?php esc_html_e( 'Open links in new tab', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('open_newtab') ); ?>" type="checkbox"  name="<?php echo esc_attr( $this->get_field_name('open_newtab') ); ?>" value="1" <?php echo $open_newtab == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('strip_title') ); ?>"><?php esc_html_e( 'Trim title to words', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The number of words to be displayed. Use 0 to disable trimming', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('strip_title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('strip_title') ); ?>" type="number" value="<?php echo esc_attr( $strip_title ); ?>" class="widefat" /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('offset') ); ?>"><?php esc_html_e( 'Offset', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The number of RSS feed items to skip from the top', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('offset') ); ?>" name="<?php echo esc_attr( $this->get_field_name('offset') ); ?>" type="number" value="<?php echo esc_attr( $offset ); ?>" class="widefat" /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('order_by') ); ?>"><?php esc_html_e( 'Order feed items by', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field">
                <?php
                    echo '<select name="' . $this->get_field_name('order_by') . '" id="' . $this->get_field_id('order_by') . '">';
                    foreach( $option_lists[ 'order_by' ] as $k => $v ){
                        echo '<option value="' . $k . '" ' . selected( $order_by, $k, false ) . '>' . $v . '</option>';
                    }
                    echo '</select>';
                ?>
                </div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('date_format') ); ?>"><?php esc_html_e( 'Date format', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The format of the feed item date.', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('date_format') ); ?>" name="<?php echo esc_attr( $this->get_field_name('date_format') ); ?>" type="text" value="<?php echo esc_attr( $date_format ); ?>" class="widefat" />
                    <small class="srr_small_text"><a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank"><?php esc_html_e( 'Refer format codes here', 'super-rss-reader' ); ?></a> <?php echo wp_kses( __( 'Default: <code>j F Y</code> or type <code>relative</code> for relative format (example 2 days ago)', 'super-rss-reader' ), array( 'code' => array() ) ); ?></small>
                </div>
            </div>

            <h4><?php esc_html_e( 'Description', 'super-rss-reader' ); ?></h4>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('desc_type') ); ?>"><?php esc_html_e( 'Description to prefer', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'Sometimes feed items have both summary and the full post content. Select the type to prefer.', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field">
                <?php
                    echo '<select name="' . esc_attr( $this->get_field_name('desc_type') ) . '" id="' . esc_attr( $this->get_field_id('desc_type') ) . '">';
                    foreach( $option_lists[ 'desc_type' ] as $k => $v ){
                        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $desc_type, $k, false ) . '>' . esc_html( $v ) . '</option>';
                    }
                    echo '</select>';
                ?>
                </div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('strip_desc') ); ?>"><?php esc_html_e( 'Trim description to words', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The number of words to be displayed. Use 0 to disable trimming', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('strip_desc') ); ?>" name="<?php echo esc_attr( $this->get_field_name('strip_desc') ); ?>" type="number" value="<?php echo esc_attr( $strip_desc ); ?>" class="widefat" /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('read_more') ); ?>"><?php esc_html_e( 'Read more text', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'Leave blank to hide read more text', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('read_more') ); ?>" name="<?php echo esc_attr( $this->get_field_name('read_more') ); ?>" type="text" value="<?php echo esc_attr( $read_more ); ?>" class="widefat" /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('link_desc') ); ?>"><?php esc_html_e( 'Link description text', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'Wrap the description text with the link of the RSS feed item. Works only when rich description is disabled.', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('link_desc') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('link_desc') ); ?>" value="1" <?php echo $link_desc == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('rich_desc') ); ?>"><?php esc_html_e( 'Enable rich description', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('rich_desc') ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('rich_desc') ); ?>" value="1" <?php echo $rich_desc == "1" ? 'checked="checked"' : ""; ?> /></div>
            </div>

            <?php if( $rich_desc == 1 ): ?>
            <span class="srr_note"><?php esc_html_e( 'Note: You have enabled "Full/Rich HTML". If no description is present, then the full content will be displayed. Please make sure that the feed(s) are from trusted sources and do not contain any harmful scripts. If there are some alignment issues in the description, please use custom CSS to fix that.', 'super-rss-reader' ); ?></span>
            <?php endif; ?>

            <h4><?php esc_html_e( 'Thumbnail', 'super-rss-reader' ); ?></h4>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('thumbnail_position') ); ?>"><?php esc_html_e( 'Thumbnail position', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field">
                <?php
                    echo '<select name="' . esc_attr( $this->get_field_name('thumbnail_position') ) . '" id="' . esc_attr( $this->get_field_id('thumbnail_position') ) . '">';
                    foreach( $option_lists[ 'thumbnail_position' ] as $k => $v ){
                        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $thumbnail_position, $k, false ) . '>' . esc_html( $v ) . '</option>';
                    }
                    echo '</select>';
                ?>
                </div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('thumbnail_size') ); ?>"><?php esc_html_e( 'Thumbnail size', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The size of the thumbnail including the units. Example 64px, 10%', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('thumbnail_size') ); ?>" name="<?php echo esc_attr( $this->get_field_name('thumbnail_size') ); ?>" type="text" value="<?php echo esc_attr( $thumbnail_size ); ?>" class="widefat" /></div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('thumbnail_force') ); ?>"><?php esc_html_e( 'Fetch thumbnail from the page (feed URL) directly if not available.', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'If feed item does not have an image, then fetch it from the page directly. This feature is available in the PRO version', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field">
                    <select>
                        <option disabled selected>No</option>
                        <option disabled>When absent in feed item</option>
                        <option disabled>Always</option>
                    </select>
                    <a class="srr_pro_tag" href="https://www.aakashweb.com/wordpress-plugins/super-rss-reader/?utm_source=admin&utm_medium=thumbnail&utm_campaign=srr-pro#pro" target="_blank" title="Upgrade to PRO version">PRO</a>
                </div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('thumbnail_default') ); ?>"><?php esc_html_e( 'Default thumbnail image', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The URL of the default thumbnail image if not present. Leave empty to skip thumbnail if not present.', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('thumbnail_default') ); ?>" name="<?php echo esc_attr( $this->get_field_name('thumbnail_default') ); ?>" type="text" value="<?php echo esc_attr( $thumbnail_default ); ?>" class="widefat" /></div>
            </div>

            <h4><?php esc_html_e( 'Misc', 'super-rss-reader' ); ?></h4>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('date_timezone') ); ?>"><?php esc_html_e( 'Date timezone', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The timezone of the feed item date.', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('date_timezone') ); ?>" name="<?php echo esc_attr( $this->get_field_name('date_timezone') ); ?>" type="text" value="<?php echo esc_attr( $date_timezone ); ?>" class="widefat" />
                    <small class="srr_small_text"><a href="https://en.wikipedia.org/wiki/List_of_tz_database_time_zones" target="_blank"><?php esc_html_e( 'Refer timezone name here', 'super-rss-reader' ); ?>.</a> <?php esc_html_e( 'Example: ', 'super-rss-reader' ); ?> <code>Asia/Taipei</code> <?php esc_html_e( 'Default: ', 'super-rss-reader' ); ?> <code>UTC</code></small>
                </div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label for="<?php echo esc_attr( $this->get_field_id('no_feed_text') ); ?>"><?php esc_html_e( 'No feed items text', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'Text to display when there are no feed items', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('no_feed_text') ); ?>" name="<?php echo esc_attr( $this->get_field_name('no_feed_text') ); ?>" type="text" value="<?php echo esc_attr( $no_feed_text ); ?>" class="widefat" /></div>
            </div>

        </section>

        <section data-tab-id="display">

            <div class="srr_row">
                <div class="srr_label srr_sm"><label for="<?php echo esc_attr( $this->get_field_id('color_style') ); ?>"><?php esc_html_e( 'Color theme', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field">
                <?php
                    echo '<select name="' . esc_attr( $this->get_field_name('color_style') ) . '" id="' . esc_attr( $this->get_field_id('color_style') ) . '">';
                    foreach( $option_lists[ 'color_style' ] as $k => $v ){
                        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $color_style, $k, false ) . '>' . esc_html( $v ) . '</option>';
                    }
                    echo '</select>';
                ?>
                </div>
            </div>

            <div class="srr_row">
                <div class="srr_label srr_sm"><label for="<?php echo esc_attr( $this->get_field_id('display_type') ); ?>"><?php esc_html_e( 'Display type', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field">
                <?php
                    echo '<select name="' . esc_attr( $this->get_field_name('display_type') ) . '" id="' . esc_attr( $this->get_field_id('display_type') ) . '">';
                    foreach( $option_lists[ 'display_type' ] as $k => $v ){
                        echo '<option value="' . esc_attr( $k ) . '" ' . selected( $display_type, $k, false ) . '>' . esc_html( $v ) . '</option>';
                    }
                    echo '</select>';
                ?>
                </div>
            </div>

            <h4><?php esc_html_e( 'Ticker type settings', 'super-rss-reader' ); ?></h4>

            <div class="srr_row">
                <div class="srr_label srr_sm"><label for="<?php echo esc_attr( $this->get_field_id('ticker_speed') ); ?>"><?php esc_html_e( 'Ticker speed', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('ticker_speed') ); ?>" name="<?php echo esc_attr( $this->get_field_name('ticker_speed') ); ?>" type="number" value="<?php echo esc_attr( $ticker_speed ); ?>" title="Speed of the ticker in seconds"/> seconds</div>
            </div>

            <div class="srr_row">
                <div class="srr_label srr_sm"><label for="<?php echo esc_attr( $this->get_field_id('visible_items') ); ?>"><?php esc_html_e( 'Widget height', 'super-rss-reader' ); ?><?php $this->tt( __( 'The height of the widget when display type is "ticker"', 'super-rss-reader' ) ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('visible_items') ); ?>" name="<?php echo esc_attr( $this->get_field_name('visible_items') ); ?>" type="number" value="<?php echo esc_attr( $visible_items ); ?>" /><br/>
                <small class="srr_small_text"><?php esc_html_e( 'Set value less than 20 to show visible feed items. Example: 5 items', 'super-rss-reader' ); ?></small></br>
                <small class="srr_small_text"><?php esc_html_e( 'Set value greater than 20 for fixed widget height. Example: 400 px', 'super-rss-reader' ); ?></small></div>
            </div>

            <h4><?php esc_html_e( 'Scroll type settings', 'super-rss-reader' ); ?></h4>

            <div class="srr_row">
                <div class="srr_label srr_sm"><label for="<?php echo esc_attr( $this->get_field_id('scroll_height') ); ?>"><?php esc_html_e( 'Maximum height of the widget', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field"><input id="<?php echo esc_attr( $this->get_field_id('scroll_height') ); ?>" name="<?php echo esc_attr( $this->get_field_name('scroll_height') ); ?>" type="text" value="<?php echo esc_attr( $scroll_height ); ?>" title="Maximum height of the widget"/><br/>
                <small class="srr_small_text"><?php esc_html_e( 'Example: 400px', 'super-rss-reader' ); ?></small>
                </div>
            </div>

        </section>

        <section data-tab-id="filter" class="srr_pro_sec">

            <p>You can build rules to show/hide feed items based on feed title, URL and description. This feature is available in the <a href="https://www.aakashweb.com/wordpress-plugins/super-rss-reader/?utm_source=admin&utm_medium=filter&utm_campaign=srr-pro#pro" target="_blank">PRO version</a>.</p>

            <div>
            <h4><?php esc_html_e( 'Filter RSS feed items', 'super-rss-reader' ); ?></h4>

            <div class="srr_row">
                <div class="srr_label"><label><?php esc_html_e( 'Filter type', 'super-rss-reader' ); ?></label></div>
                <div class="srr_field">
                <?php
                    echo '<select disabled="disabled">';
                    echo '<option>Show items based on filter</option>';
                    echo '</select>';
                ?>
                </div>
            </div>

            <div class="srr_row">
                <div class="srr_label"><label><?php esc_html_e( 'Filter rules', 'super-rss-reader' ); ?></label><?php $this->tt( __( 'The rules for the keyword filters', 'super-rss-reader' ) ); ?></div>
                <div class="srr_field"><a href="#"><?php esc_html_e( 'View/Edit filter rules', 'super-rss-reader' ); ?></a></div>
            </div>
            </div>

        </section>

        </div>

        <div class="srr_pro">
            <div class="srr_pro_intro">
                <div><span class="srr_pro_label">PRO</span></div>
                <p>Get the PRO version to enjoy more features like<br/> <span>Shortcode, Grid display, Filter by keyword, Pagination</span> and more !</p>
                <div><span class="dashicons dashicons-arrow-down-alt2 srr_pro_more"></span></div>
            </div>
            <div class="srr_pro_details">
                <ul class="srr_pro_features">
                    <li>Merge feeds - <span>Merge multiple feeds into one</span></li>
                    <li>Shortcode - <span>Display RSS feed anywhere in your website</span></li>
                    <li>Grid display - <span>Display feed item in rows and columns</span></li>
                    <li>Paginated display - <span>Display feed item in different pages.</span></li>
                    <li>Filter by keyword - <span>Show/hide feed items based on keyword</span></li>
                    <li>Custom template - <span>Change order of feed item content, add HTML</span></li>
                    <li>Fetch thumbnail - <span>Forcefully fetches the thumbnail from feed URL.</span></li>
                    <li>4+ new color themes</li>
                    <li>Updates and support for 1 year</li>
                </ul>
                <p><a href="https://www.aakashweb.com/wordpress-plugins/super-rss-reader/?utm_source=admin&utm_medium=widget-get&utm_campaign=srr-pro#pro" class="button button-primary">Get PRO version</a> <a href="https://www.aakashweb.com/wordpress-plugins/super-rss-reader/?utm_source=admin&utm_medium=widget-info&utm_campaign=srr-pro" class="button">More information</a></p>
            </div>
        </div>

        <div class="srr_info">
          <p><a href="https://www.aakashweb.com/docs/super-rss-reader/" target="_blank">Docs</a> | <a href="https://www.aakashweb.com/forum/discuss/wordpress-plugins/super-rss-reader/" target="_blank">Report issue</a> | <a href="https://wordpress.org/support/plugin/super-rss-reader/reviews/?rate=5#new-post" target="_blank">Rate 5 stars & review</a> | v<?php echo SRR_VERSION; ?></p>
        </div>

        <?php
    }

    public function tt( $text ){
        echo '<div class="srr_tt" tabindex="0"><span class="dashicons dashicons-editor-help"></span><span class="srr_tt_text"><span>' . esc_html( $text ) . '</span></span></div>';
    }

}

?>