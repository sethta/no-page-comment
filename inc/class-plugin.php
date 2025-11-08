<?php

namespace NoPageComment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	private static $instance = null;
	private static $plugin_version = '1.3';
	private static $plugin_file;

	public static function get_instance( $plugin_file = '' ) {
		if ( self::$instance === null ) {
			self::$plugin_file = $plugin_file;
			self::$instance    = new self();
		}

		return self::$instance;
	}

	public static function get_plugin_file() {
		return self::$plugin_file;
	}

	public static function get_plugin_version() {
		return self::$plugin_version;
	}

	public static function load_text_domain() {
		load_plugin_textdomain( 'no-page-comment', false, basename( self::get_plugin_file() ) . '/lang/' );
	}

	/**
	 * Ensures stored plugin versions are up to date.
	 */
	public function ensure_version_consistency() {
		$stored_plugin_version = get_option( 'sta_npc_version' );

		if ( $stored_plugin_version !== self::$plugin_version ) {
			update_option( 'sta_npc_version', self::$plugin_version );
		}
	}

	/**
	 * Activation handler: migrate old options and ensure defaults exist.
	 */
	public static function activate() {
		// Default option keys (based on legacy defaults)
		$defaults = [
			'disable_comments_post'         => '',
			'disable_trackbacks_post'       => '',
			'disable_comments_page'         => 'true',
			'disable_trackbacks_page'       => 'true',
			'disable_comments_attachment'   => '',
			'disable_trackbacks_attachment' => '',
		];

		// Add defaults for other post types (legacy behavior set these true)
		$builtin = [ 'post', 'page', 'revision', 'nav_menu_item', 'attachment' ];
		foreach ( get_post_types( '', 'names' ) as $pt ) {
			if ( in_array( $pt, $builtin, true ) ) {
				continue;
			}
			$defaults[ 'disable_comments_' . $pt ]   = 'true';
			$defaults[ 'disable_trackbacks_' . $pt ] = 'true';
		}

		// Migrate legacy option name if present
		$old = get_option( 'sta_npc_admin_options_name' );
		if ( $old !== false ) {
			update_option( 'sta_npc_options', $old );
			delete_option( 'sta_npc_admin_options_name' );
			update_option( 'sta_npc_version', self::$plugin_version );
			return;
		}

		$stored = get_option( 'sta_npc_options' );
		if ( is_array( $stored ) && ! empty( $stored ) ) {
			$merged = array_merge( $defaults, $stored );
		} else {
			$merged = $defaults;
		}

		update_option( 'sta_npc_options', $merged );
		update_option( 'sta_npc_version', self::$plugin_version );

		// Record activation timestamp (used previously in bootstrap).
		update_option( 'sta_npc_activation', gmdate( 'YmdHis' ) );
	}

	/**
	 * Deactivation handler.
	 */
	public static function deactivate() {
		delete_option( 'sta_npc_activation' );
	}

	public function init() {
		// Don't load plugin on heartbeats
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && $_POST['action'] === 'heartbeat' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No need for nonce verification as we are using this to check for heartbeat requests.
			return;
		}

		Notification::init();
		Settings::init();

		$this->ensure_version_consistency();
	}

	private function define_hooks() {
		if ( ! empty( self::$plugin_file ) ) {
			register_activation_hook( self::$plugin_file, [ __CLASS__, 'activate' ] );
			register_deactivation_hook( self::$plugin_file, [ __CLASS__, 'deactivate' ] );
		}

		add_action( 'plugins_loaded', [ $this, 'init' ] );
		add_action( 'init', [ $this, 'load_text_domain' ] );
	}

	private function __construct() {
		$this->define_hooks();
	}
}
