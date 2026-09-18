<?php
/**
 * Runs when the plugin is deleted from the plugins screen.
 *
 * @package MultipleFloatingWA
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'mfw_settings' );

if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'mfw_settings' );
		restore_current_blog();
	}
}
