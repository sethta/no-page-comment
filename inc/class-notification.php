<?php

namespace NoPageComment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notification {
	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'register_assets' ] );
		add_action( 'admin_notices', [ __CLASS__, 'show_notice' ] );
		add_action( 'wp_ajax_sta_npc_notification_dismiss', [ __CLASS__, 'ajax_dismiss' ] );
	}

	public static function register_assets() {
		if ( ! is_admin() ) {
			return;
		}

		wp_register_script( 'sta-npc', plugins_url( 'no-page-comment.js', dirname( __DIR__ ) . '/no-page-comment.php' ), [ 'jquery' ], Plugin::get_plugin_version(), true );
		wp_localize_script( 'sta-npc', 'staNpcNotice', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sta_npc_notification_nonce' ),
		] );
	}

	private static function has_inactive_ecc() {
		// Required for plugin scanning functions.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all = get_plugins();
		foreach ( $all as $path => $meta ) {
			$lower_path = strtolower( $path );
			$lower_name = isset( $meta['Name'] ) ? strtolower( $meta['Name'] ) : '';

			if ( strpos( $lower_path, 'easy-critical' ) !== false || strpos( $lower_name, 'easy critical' ) !== false || strpos( $lower_name, 'easy-critical' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	private static function has_ecc_plugin() {
		if ( class_exists( 'EasyCriticalCSS\\Plugin' ) ) {
			return true;
		}

		$has_ecc = get_option( 'sta_npc_has_ecc' );
		$need_rescan = false;
		if ( ! is_array( $has_ecc ) || ! isset( $has_ecc['exists'] ) || ! isset( $has_ecc['checked'] ) ) {
			$need_rescan = true;
		} else if ( time() - (int) $has_ecc['checked'] > ( 60 * DAY_IN_SECONDS ) ) {
			$need_rescan = true;
		}

		if ( $need_rescan ) {
			$has_inactive_ecc = self::has_inactive_ecc();
			$has_ecc = [ 'exists' => $has_inactive_ecc, 'checked' => time() ];
			update_option( 'sta_npc_has_ecc', $has_ecc );
		}

		if ( ! empty( $has_ecc['exists'] ) ) {
			return true;
		}

		return false;
	}

	public static function show_notice() {
		if ( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( self::has_ecc_plugin() ) {
			return;
		}

		$notif_dismissed_time = get_option( 'sta_npc_ecc_notif', 0 );
		if ( $notif_dismissed_time && time() - (int) $notif_dismissed_time < ( 120 * DAY_IN_SECONDS ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return;
		}

		$allowed_screens = [
			'dashboard',
			'dashboard-network',
			'plugins',
			'plugins-network',
			'settings_page_no-page-comment',
		];

		if ( ! in_array( $screen->id, $allowed_screens, true ) ) {
			return;
		}

		wp_enqueue_script( 'sta-npc-notice' );
		$install_url = admin_url( 'plugin-install.php?s=criticalcss&tab=search&type=author' );
		$button_text = __( 'View', 'no-page-comment' );
		if ( current_user_can( 'install_plugins' ) ) {
			$install_url = self_admin_url( 'update.php?action=install-plugin&plugin=easy-critical-css&_wpnonce=' . wp_create_nonce( 'install-plugin_easy-critical-css' ) );
			$button_text = __( 'Install', 'no-page-comment' );
		}

		?>
		<div class="notice notice-info is-dismissible sta-npc-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( 'sta_npc_notification_nonce' ) ); ?>">
			<p>
				<?php esc_html_e( 'Help your pages load faster for readers with Easy Critical CSS.', 'no-page-comment' ); ?>
				<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer" style="margin-left:12px;"><?php echo esc_html( $button_text ); ?></a>
				<a href="#" class="notice-dismiss sta-npc-notice-dismiss" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'no-page-comment' ); ?>"></a>
			</p>
		</div>
		<?php
	}

	public static function ajax_dismiss() {
		check_ajax_referer( 'sta_npc_notification_nonce', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions', 'no-page-comment' ) ] );
		}

		update_option( 'sta_npc_ecc_notif', time() );

		wp_send_json_success();
	}
}
