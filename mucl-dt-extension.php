<?php
/**
* Plugin Name: Multisite Cloner - Distributor Extension
* Plugin URI: https://wordpress.org/plugins/multisite-cloner
* Description: Fixes integration between cloned sites and the Distributor plugin.
* Version: 1.2.0
* Author: Hugo Moran
* Author URI: http://tipit.net
* License: License: GPL2+
**/



// Since an original site would not know about any Distributor connections
// in the duplicate of a duplicate, this plugin connects the original
// with the latest created site.

add_action('wpmu_new_blog', 'mucldt_add_dt_connections', 2, 1 );
add_action( 'network_admin_menu', 'mucldt_create_page' );

function mucldt_create_page() {
		add_submenu_page(
		'settings.php',
		'Distributor Map Fixer',
		'Distributor Map Fixer',
		'manage_options',
		'mucl-dt-extension/mucl-dt-extension.php',
		'mucldt_admin_page'
	);
}

function mucldt_add_dt_connections( $latest_clone_site_id ) {
	switch_to_blog( $latest_clone_site_id );
	// Search through site for distributed posts
	$args = array(
		'meta_key' => 'dt_original_blog_id',
		'post_type' => 'any',
		'posts_per_page' => -1,
	);
	$posts_query = new WP_Query( $args );
	$distributed_posts = $posts_query->posts;

	// Getting the original blog ID and post ID from every distributed post.
	$og_blog_and_post_ids = array();
	foreach ( $distributed_posts as $post ) {
		$original_blog_id = get_post_meta( $post->ID, 'dt_original_blog_id' )[0];
		$original_post_id = get_post_meta( $post->ID, 'dt_original_post_id' )[0];
		if ( !isset( $og_blog_and_post_ids[$original_blog_id] ) ) {
			$og_blog_and_post_ids[$original_blog_id] = array();
		}
		array_push( $og_blog_and_post_ids[$original_blog_id],
					array(
						'og_post_id' => $original_post_id,
						'post_id'   => $post->ID,
					));
	}

	foreach ( $og_blog_and_post_ids as $blog_id => $og_post_id) {
		// Switching to original blog to create and add new site connection.
		switch_to_blog( $blog_id );
		foreach ( $og_post_id as $post ) {
			$dt_connection = get_post_meta( $post['og_post_id'], 'dt_connection_map' );
			if ( isset( $dt_connection ) ) {
				$dt_connection[0]['internal'][$latest_clone_site_id] = array(
					'post_id' => $post['post_id'],
					'time' => time(),
				);
				update_post_meta( $post['og_post_id'], 'dt_connection_map', $dt_connection[0] );
			}
		}
	}
	restore_current_blog();

	return true;
}

function mucldt_admin_page() {
	$network_sites = get_sites();
	$plugin_url = admin_url(
		'network/settings.php?page=mucl-dt-extension%2Fmucl-dt-extension.php');
	?>
	<div class="wrap">
		<h2>Distributor Map Fixer</h2>
		<p>Since an original site would not know about any Distributor
		 connections in the duplicate of a site containing connections,
		 this fix<br>connects the original with the latest created clone.
		</p>
		<form action="<?php echo $plugin_url ?>" method="POST">
			<table class="form-table">
			<tr>
				<th scope="row"><label for="latest">Site to fix:</label></th>
				<td>
					<select name="latest" id="latest">
					<?php foreach ($network_sites as $site) {
							echo '<option value="'
							. $site->blog_id
							. '" name="'
							. $site->domain
							. '"' . '>'
							. $site->domain
							. '</option>';
							}
					?>
					</select>
				</td>
			</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button button-primary" value="Fix it">
			</p>
		</form>
	</div>
	<?php

	if( isset($_POST['latest']) && $_SERVER['REQUEST_METHOD'] == "POST") {
		if ( mucldt_add_dt_connections( $_POST['latest'] ) ) {
			echo "Fix succesfully executed.";
		} else { echo "There was an error executing the fix."; }
	} else {  }
}

