<?php
/*
 * Plugin Name:       No Page Comment
 * Description:       An admin interface to control the default comment and trackback settings on new posts, pages and custom post types.
 * Version:           1.3.0
 * Requires at least: 6.2
 * Tested up to:      6.8.2
 * Requires PHP:      7.4
 * Author:            Seth Alling
 * Author URI:        https://sethalling.com/
 * Text Domain:       no-page-comment
 *
 * @package NoPageComment
 *
 *   _____      _   _                _ _ _                   _...._
 *  / ____|    | | | |         /\   | | (_)                .'/  \ _'.
 * | (___   ___| |_| |__      /  \  | | |_ _ __   __ _    /##\__/##\_\
 *  \___ \ / _ \ __| '_ \    / /\ \ | | | | '_ \ / _` |  |\##/  \##/  |
 *  ____) |  __/ |_| | | |  / ____ \| | | | | | | (_| |  |/  \__/  \ _|
 * |_____/ \___|\__|_| |_| /_/    \_\_|_|_|_| |_|\__, |   \ _/##\__/#/
 *                                                __/ |    '.\##/__.'
 * Plugin developed by: https://sethalling.com   |___/       `""""`
 */

namespace NoPageComment;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This plugin requires WordPress' );
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Composer autoload.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// Initialize the plugin.
Plugin::get_instance( __FILE__ );
