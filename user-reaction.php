<?php

// Create user reaction table
add_action( 'init', 'hd_create_user_reaction_table' );

/**
 * Create Database table for user reactions.
 */
function hd_create_user_reaction_table() {

    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
	$charset_collate    = $wpdb->get_charset_collate();
    $table_name         = $wpdb->prefix . 'hd_user_reations';
    
	$table_sql = "CREATE TABLE `$table_name` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`user_id` bigint(20) NOT NULL,
			`post_id` bigint(20) NOT NULL,
			`reaction_id` int(10) NOT NULL,
            `post_type` varchar(50) NOT NULL,
			`reaction_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY post_id (post_id),
            KEY reaction_id (reaction_id)
			) {$charset_collate};";

	dbDelta( $table_sql );
}

/**
 * Display reaction buttons with total reacted count and reacted emojis.
 *
 * @param  int $post_id
 * @param  string $item_type
 */
function hd_get_reaction_buttons( $post_id, $item_type = 'post_type' ) {

    if ( empty( $post_id ) || 0 === $post_id ) {

        return;
    }

    $reaction_types = array( '1' => 'like-icon', '2' => 'insightful-icon', '3' => 'good-idea-icon', '4' => 'wow-icon', '5' => 'celebrate-icon' );
    $base_path      = get_template_directory_uri() . '/assets/images/reaction-icon/';

    ?>
    <div class="reaction-wrapper">
        <div class="reaction-inner">
            <?php
            
            $user_logged_in = is_user_logged_in();
            $reaction = '';

            if ( $user_logged_in ) {
                
                $current_user_id    = get_current_user_id();
                $reaction           = hd_get_user_reaction( $current_user_id, $post_id );
            }
            
            $like_button_class  = ! empty( $reaction ) && isset( $reaction_types[ $reaction ] ) ? 'btn reaction-main-like reacted' : 'btn reaction-main-like';
            if ( $user_logged_in ) {
                ?>
                <div class="reaction-list-type">
                    <a href="javascript:void(0);" class="<?php echo esc_attr( $like_button_class ); ?>"><i class="fa fa-thumbs-up" aria-hidden="true"></i>Like</a>
                    <div class="reaction-icon-modal">
                        <ul class="reaction-item-list" data-item="<?php echo esc_attr( $post_id ); ?>" data-item-type="<?php echo esc_attr( $item_type ); ?>" data-log="<?php echo esc_attr( $user_logged_in ); ?>">
                            <?php
                            foreach( $reaction_types as $key => $type ) {
        
                                $action         = 'add';
                                $reaction_class = 'hd-reaction-type';
                                $reaction_img   = $base_path . $type . '.svg';

                                if ( (int) $reaction === $key ) {
                                    
                                    $action         = 'remove';
                                    $reaction_class .= ' reacted';
                                }
                                ?>
                                <li>
                                    <a href="javascript:void(0);" class="<?php echo esc_attr( $reaction_class ); ?>" data-action="<?php echo esc_attr( $action ); ?>" data-reaction="<?php echo esc_attr( $key ); ?>">
                                        <img width="30" src="<?php echo esc_url( $reaction_img ); ?>" alt="<?php echo esc_attr( $type ); ?>" />
                                    </a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>                  
                </div>
                <?php
            }
            hd_get_reacted_html( $post_id );
            ?>
        </div>
    </div>
    <?php
}


/**
 * Display reacted item list and total reacted post count.
 *
 * @param  int $post_id 
 */
function hd_get_reacted_html( $post_id ) {

    if ( empty( $post_id ) ) {
        return;
    }

    $total_reactions = hd_get_total_reactions( $post_id );
    ?>            
    <div class="user-reacted-item">
        <ul class="reacted-list">
            <?php hd_get_reacted_item_list( $post_id ); ?>
        </ul>
        <span class="react-count"><?php echo esc_html( 0 < (int) $total_reactions ? $total_reactions : '' ); ?></span>
    </div>
    <?php
}

/**
 * Display user reacted icons.
 *
 * @param  mixed $post_id 
 */
function hd_get_reacted_item_list( $post_id ) {
    
    global $wpdb;

    $reaction_types = array( '1' => 'like-icon', '2' => 'insightful-icon', '3' => 'good-idea-icon', '4' => 'wow-icon', '5' => 'celebrate-icon' );
    $base_path      = get_template_directory_uri() . '/assets/images/reaction-icon/';

    $table_name     = $wpdb->prefix . 'hd_user_reations';
    $prepare_sql    = $wpdb->prepare( "SELECT DISTINCT reaction_id FROM `$table_name` WHERE post_id = %d ORDER BY id DESC", $post_id );

    $react_results  = $wpdb->get_results( $prepare_sql );

    if ( ! empty( $react_results ) ) {
        
        foreach ( $react_results as $react ) {
            
            $reaction_img   = $base_path . $reaction_types[ $react->reaction_id ] . '.svg';
            ?>
            <li>
                <img width="30" class="reacted-img" src="<?php echo esc_url( $reaction_img ); ?>" alt="<?php echo esc_attr( $reaction_types[ $react->reaction_id ] ); ?>" />
            </li>
            <?php
        }
    }    
}


/**
 * Get user reaction for specific post.
 *
 * @param  int $user_id
 * @param  int $post_id
 * 
 * @return mixed return int if user reaction found in the table for given post_id otherwise empty string or false.
 */
function hd_get_user_reaction( $user_id, $post_id ) {

    global $wpdb;

    $reaction = '';

    if ( empty( $user_id ) || empty( $post_id ) ) {

        return $reaction;
    }

    $table_name     = $wpdb->prefix . 'hd_user_reations';
    $prepare_sql    = $wpdb->prepare( "SELECT reaction_id FROM `$table_name` WHERE user_id = %d AND post_id = %d", $user_id, $post_id );
    $reaction_type  = $wpdb->get_row( $prepare_sql );

    if ( ! empty( $reaction_type ) ) {
        
        $reaction = $reaction_type->reaction_id;
    }

    return $reaction;
}


/**
 * Get total reaction of the post.
 *
 * @param  int $post_id
 * @return int
 */
function hd_get_total_reactions( $post_id ) {

    global $wpdb;

    $total_reactions = 0;

    if ( empty( $post_id ) ) {

        return $total_reactions;
    }

    $table_name         = $wpdb->prefix . 'hd_user_reations';
    $prepare_sql        = $wpdb->prepare( "SELECT COUNT(*) FROM `$table_name` WHERE post_id = %d", $post_id );
    $total_reactions    = $wpdb->get_var( $prepare_sql );

    return $total_reactions;
}


/**
 * Add new user reaction to the custom table.
 *
 * @param  int $user_id
 * @param  int $post_id
 * @param  int $reaction_id
 * @param  string $item_type
 * 
 * @return mixed return int if reaction added successfully otherwise false.
 */

function hd_add_new_reaction( $user_id, $post_id, $reaction_id, $item_type = 'post_type' ) {

    global $wpdb;
    
    if ( empty( $post_id ) || empty( $reaction_id ) ) {        
        
        return false;
    }

    if ( empty( $item_type ) || 'post_type' === $item_type ) {
        
        $reaction_post_type = get_post_type( $post_id );

    } else {
        
        $reaction_post_type = $item_type;
    }
    
    $current_time       = current_time( 'Y-m-d H:i:s' );
    $table_name         = $wpdb->prefix . 'hd_user_reations';

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id'       => $user_id,
            'post_id'       => $post_id,
            'reaction_id'   => $reaction_id,
            'post_type'     => $reaction_post_type,
            'reaction_time' => $current_time
        ),
        array(
            '%d',
            '%d',
            '%d',
            '%s',
            '%s'
        )
    );    

    return $inserted;
}

/**
 * Update user reaction.
 *
 * @param  int $user_id
 * @param  int $post_id
 * @param  int $reaction_id
 * 
 * @return mixed return int if reaction updated successfully otherwise false.
 */
function hd_update_reaction( $user_id, $post_id, $reaction_id ) {

    global $wpdb;    

    if ( empty( $user_id ) || empty( $post_id ) || empty( $reaction_id ) ) {        
        
        return false;
    }

    $current_time   = current_time( 'Y-m-d H:i:s' );
    $table_name     = $wpdb->prefix . 'hd_user_reations';

    $updated = $wpdb->update(
        $table_name,
        array(
            'reaction_id'   => $reaction_id,
            'reaction_time' => $current_time,
        ),
        array(
            'user_id'   => $user_id,
            'post_id'   => $post_id,    
        ),
        array(
            '%d',
            '%s'            
        ),
        array(
            '%d',
            '%d'
        )
    );

    return $updated;

}

/**
 * Remove user reaction.
 *
 * @param  int $user_id
 * @param  int $post_id
 * 
 * @return mixed return int if reaction removed successfully otherwise false.
 */
function hd_remove_reaction( $user_id, $post_id ) {

    global $wpdb;

    if ( empty( $user_id ) || empty( $post_id ) ) {        
        
        return false;
    }

    $table_name = $wpdb->prefix . 'hd_user_reations';
    $deleted    = $wpdb->delete(
        $table_name,
        array(
            'user_id'   => $user_id,
            'post_id'   => $post_id
        ),
        array(
            '%d',
            '%d'
        )
    );

    return $deleted;
}

//User reaction ajax
add_action( 'wp_ajax_hd_update_post_reaction', 'hd_update_post_reaction_callback' );
add_action( 'wp_ajax_nopriv_hd_update_post_reaction', 'hd_update_post_reaction_callback' );

/**
 * User reaction ajax to handle add, update or delete reaction based on user activity.
 */
function hd_update_post_reaction_callback() {

    check_ajax_referer( 'hd-ajax-nonce', 'hdNonce' );

    $post_id        = filter_input( INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT );
    $reaction_id    = filter_input( INPUT_POST, 'rid', FILTER_SANITIZE_NUMBER_INT );
    $action         = filter_input( INPUT_POST, 'item_action', FILTER_SANITIZE_STRING );
    $item_type      = filter_input( INPUT_POST, 'item_type', FILTER_SANITIZE_STRING );
    $result         = array( 'success' => false );    

    if ( ( isset( $post_id ) && ! empty( $post_id ) ) && ( isset( $reaction_id ) && ! empty( $reaction_id ) ) && ( isset( $action )  && ! empty( $action ) ) ) {

        $current_user_id    = 0;
        $reaction           = '';
        
        if ( is_user_logged_in() ) {
            
            $current_user_id    = get_current_user_id();
            $reaction           = hd_get_user_reaction( $current_user_id, $post_id );

            if ( empty( $reaction ) && 'add' === strtolower( $action ) ) {

                //Add new reaction
                $inserted               = hd_add_new_reaction( $current_user_id, $post_id, $reaction_id, $item_type );
                $result[ 'success' ]    = $inserted ? true : false;
                $result[ 'action' ]     = 'remove';
    
            } else if ( ! empty( $reaction ) && $reaction === $reaction_id && 'remove' === strtolower( $action ) ) {
    
                //remove reaction
                $deleted                = hd_remove_reaction( $current_user_id, $post_id );
                $result[ 'success' ]    = $deleted ? true : false;
                $result[ 'action' ]     = 'add';
    
            } else if ( ! empty( $reaction ) && $reaction !== $reaction_id && 'add' === strtolower( $action ) ) {
    
                //update reaction            
                $updated                = hd_update_reaction( $current_user_id, $post_id, $reaction_id );
                $result[ 'success' ]    = $updated ? true : false;
                $result[ 'action' ]     = 'remove';
            }
    
            if ( $result[ 'success' ] ) {
    
                //total reactions            
                $result[ 'total' ]  = hd_get_total_reactions( $post_id );
                
                ob_start();
                
                hd_get_reacted_item_list( $post_id );
    
                $reacted_list = ob_get_clean();
    
                $result[ 'reacted_list' ]   = $reacted_list;
            }
        }
    }

    echo wp_json_encode( $result );

	wp_die();
}

//shortcode for reaction button.
add_shortcode( 'reaction_button', 'hd_display_reaction_button_callback' );


/**
 * Display reaction button. item_id and item_type required when place the shortcode outside of the post or inner post.
 *
 * @param  array $atts
 * 
 * @return string
 */
function hd_display_reaction_button_callback( $atts ) {
    
    $atts = shortcode_atts( array(
		'item_id'   => get_the_ID(),
		'item_type' => 'post_type',
    ), $atts );
    
    ob_start();

    hd_get_reaction_buttons( $atts[ 'item_id' ], $atts[ 'item_type' ] );

    return ob_get_clean();
}

//Shortcode for display reacted items with count
add_shortcode( 'reacted_items', 'hd_display_reacted_items_callback' );

/**
 * Display reacted icons and count according to post id. item_id required when place the shortcode outside of the post or inner post.
 *
 * @param  array $atts
 * 
 * @return string
 */
function hd_display_reacted_items_callback( $atts ) {
    
    $atts = shortcode_atts( array(
		'item_id'   => get_the_ID(),		
    ), $atts );
    
    ob_start();

    hd_get_reacted_html( $atts[ 'item_id' ] );

    return ob_get_clean();
}