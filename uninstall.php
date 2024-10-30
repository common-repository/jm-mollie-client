<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
delete_option( 'jm_mollie_client_option_name' );
