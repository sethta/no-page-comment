<?php
// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$sta_npc_options = [
	'sta_npc_options',
	'sta_npc_version',
	'sta_npc_activation',
	'sta_npc_admin_options_name', // Very old legacy option in case older installs still have it
];

foreach ( $sta_npc_options as $sta_npc_option ) {
	delete_option( $sta_npc_option );
}

// If this is a multisite installation also remove site/network options.
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	foreach ( $sta_npc_options as $sta_npc_option ) {
		if ( function_exists( 'delete_site_option' ) ) {
			delete_site_option( $sta_npc_option );
		}
	}
}
