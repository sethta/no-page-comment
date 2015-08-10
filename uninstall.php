
<?php
// If uninstall is not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

$option_name = 'sta_npc_admin_options_name';

delete_option( $option_name );
