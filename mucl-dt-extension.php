<?php
/**
* Plugin Name: Extension for Distributor - Multisite Cloner
* Plugin URI: https://wordpress.org/plugins/mucl-dt-extension
* Description: Fixes integration between cloned sites and the Distributor plugin.
* Version: 1.0
* Author: Hugo Moran
* Author URI: http://tipit.net
* License: License: GPL2+
**/



// Since an original site would not know about any Distributor connections
// in the duplicate of a site containing connections, this functions connects the original
// with the latest created clone.
function mucl_add_dt_connections( $blog_site ) {


    // Get site IDs
    $latest_clone_site_id = $blog_site;
    if ( isset($_POST['wpmuclone_default_blog']) ) {
        $cloned_site_id = intval($_POST['wpmuclone_default_blog']);
    } else {
        $cloned_site_id = get_option('wpmuclone_default_blog');
    }

    // Search through cloned site for distributed posts
    switch_to_blog( $cloned_site_id );
    $args = array(
        'meta_key' => 'dt_original_blog_id',
        'post_type' => 'any'
    );
    $posts_query = new WP_Query( $args );
    $distributed_posts = $posts_query->posts;

    foreach ( $distributed_posts as $post ){

        //Find its original blog ID, original post ID, then add the new dt connection.
        $original_blog_id = get_post_meta( $post->ID, 'dt_original_blog_id' )[0];
        $original_post_id = get_post_meta( $post->ID, 'dt_original_post_id' )[0];

        switch_to_blog( $original_blog_id );

        $dt_connection = get_post_meta( $original_post_id, 'dt_connection_map' );

        $dt_connection[0]['internal'][$latest_clone_site_id] = $dt_connection[0]['internal'][$cloned_site_id];

        update_post_meta( $original_post_id, 'dt_connection_map', $dt_connection[0] );
        restore_current_blog();
    }

    restore_current_blog();
}
add_action('wpmu_new_blog', 'mucl_add_dt_connections', 2, 1  );
