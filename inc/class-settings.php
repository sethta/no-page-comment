<?php

namespace NoPageComment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	private static $plugin_file;
	private static $plugin_dir;
	private static $admin_slug;

	public static function init() {
		self::$plugin_file = Plugin::get_plugin_file();
		self::$plugin_dir  = plugins_url( '', self::$plugin_file );
		self::$admin_slug  = basename( self::$plugin_file, '.php' );

		add_filter( 'get_default_comment_status', [ __CLASS__, 'get_default_comment_status' ], 10, 3 );
		add_filter( 'plugin_action_links_' . plugin_basename( self::$plugin_file ), [ __CLASS__, 'settings_link' ] );

		add_action( 'admin_menu', [ __CLASS__, 'add_settings_page' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_styles' ] );
		add_action( 'admin_head', [ __CLASS__, 'add_ajax_script' ] );
		add_action( 'admin_head', [ __CLASS__, 'discussion_options_link' ] );
		add_action( 'wp_ajax_sta_npc_mod', [ __CLASS__, 'ajax_mod' ] );
		add_action( 'wp_ajax_nopriv_sta_npc_mod', [ __CLASS__, 'ajax_mod_nopriv' ] );
		add_action( 'add_attachment', [ __CLASS__, 'attachment_comment' ] );
	}

	public static function get_admin_slug() {
		return self::$admin_slug;
	}

	public static function get_admin_options() {
		return get_option( 'sta_npc_options', [] );
	}

	public static function get_default_comment_status( $status, $post_type, $comment_type ) {
		$options = self::get_admin_options();

		if ( $comment_type === 'comment' ) {
			$key = 'disable_comments_' . $post_type;

			if ( ! isset( $options[ $key ] ) ) {
				return $status;
			}

			return $options[ $key ] === 'true' ? 'closed' : 'open';
		}

		if ( $comment_type === 'pingback' || $comment_type === 'ping' ) {
			$key = 'disable_trackbacks_' . $post_type;

			if ( ! isset( $options[ $key ] ) ) {
				return $status;
			}

			return $options[ $key ] === 'true' ? 'closed' : 'open';
		}

		return $status;
	}

	public static function settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::get_admin_slug() ) ) . '">' . esc_html__( 'Settings', 'no-page-comment' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public static function add_settings_page() {
		add_options_page(
			__( 'No Page Comment Settings', 'no-page-comment' ),
			__( 'No Page Comment', 'no-page-comment' ),
			'manage_options',
			self::get_admin_slug(),
			[ Settings_View::class, 'print_admin_page' ]
		);
	}

	public static function admin_styles( $hook ) {
		if ( $hook === 'settings_page_no-page-comment' ) {
			wp_register_style( 'sta_npc', self::$plugin_dir . '/no-page-comment.css', false, Plugin::get_plugin_version() );
			wp_enqueue_style( 'sta_npc' );
		}
	}

	public static function add_ajax_script() {
		global $pagenow, $post;

		if ( is_admin() && $pagenow === 'options-general.php' ) {
			// Only allow users who can manage options to enqueue the admin script.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( $screen && 'settings_page_no-page-comment' === $screen->id ) {
				wp_register_script( 'sta-npc', plugins_url( '/no-page-comment.js', self::$plugin_file ), [ 'jquery' ], Plugin::get_plugin_version(), true );
				wp_localize_script( 'sta-npc', 'staNpc', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
				wp_enqueue_script( 'sta-npc' );
			}
		}
	}

	public static function discussion_options_link() {
		global $pagenow, $post;

		if ( is_admin() && $pagenow === 'options-discussion.php' ) {
			$settings_link = sprintf(
				'%1$s <a href="%2$s">%3$s</a>',
				esc_html__( 'Comment and trackback defaults controlled through', 'no-page-comment' ),
				esc_url( admin_url( 'options-general.php?page=' . self::get_admin_slug() ) ),
				esc_html__( 'No Page Comment Settings', 'no-page-comment' )
			);

			wp_enqueue_script( 'jquery' );

			$js_link = wp_json_encode( wp_kses_post( $settings_link ) );
			?>
			<script type="text/javascript">
			jQuery(document).ready( function() {
				var settingsLink = <?php echo esc_js( $js_link ); ?>;
				jQuery('label[for="default_ping_status"]').remove().prev('br').remove();
				jQuery('label[for="default_comment_status"]').prev('br').remove();
				jQuery('label[for="default_comment_status"]').next('br').remove();
				jQuery('label[for="default_comment_status"]').next('p').html(settingsLink);
				jQuery('label[for="default_comment_status"]').next('small').html(settingsLink);
				jQuery('label[for="default_comment_status"]').remove();
			});
			</script>
			<?php
		}
	}

	public static function ajax_mod() {
		// Verify and sanitize nonce.
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'sta_npc_nonce' ) ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_error( __( "You don't have permission to perform this action.", 'no-page-comment' ) );
			}

			wp_die( esc_html__( "You don't have permission to perform this action.", 'no-page-comment' ), '', [ 'response' => 403 ] );
		}

		global $wpdb;

		// Sanitize and validate input
		$comment_type   = isset( $_REQUEST['comment_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['comment_type'] ) ) : '';
		$comment_status = isset( $_REQUEST['comment_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['comment_status'] ) ) : '';
		$post_type      = isset( $_REQUEST['post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ) : '';
		$post_label     = isset( $_REQUEST['post_label'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_label'] ) ) : '';

		// Determine new status
		if ( $comment_status === 'open' ) {
			$comment_new_status = 'closed';
		} elseif ( $comment_status === 'closed' ) {
			$comment_new_status = 'open';
		} else {
				wp_send_json_error( [ 'message' => __( 'Invalid comment status.', 'no-page-comment' ) ] );
		}

		// Build query
		if ( $comment_type === 'ping' ) {
			$comment_label = 'trackbacks';
			$query         = $wpdb->prepare(
				"UPDATE $wpdb->posts SET ping_status = %s WHERE ping_status = %s AND post_type = %s",
				$comment_new_status,
				$comment_status,
				$post_type
			);
		} elseif ( $comment_type === 'comment' ) {
			$comment_label = 'comments';
			$query         = $wpdb->prepare(
				"UPDATE $wpdb->posts SET comment_status = %s WHERE comment_status = %s AND post_type = %s",
				$comment_new_status,
				$comment_status,
				$post_type
			);
		} else {
				wp_send_json_error( [ 'message' => __( 'Invalid comment type.', 'no-page-comment' ) ] );
		}

		$result = [];
		if ( empty( $query ) ) {
			$result['type']    = 'error';
			$result['message'] = __( 'Something went wrong. Please refresh this page and try again.', 'no-page-comment' );
		} else {
			// Direct DB query because we want to do this in bulk, and using WP functions goes one at a time, which can be much slower.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- justified bulk update; query is prepared above
			$wpdb->query( $query );
			$result['type'] = 'success';
			// translators: 1: comment label, 2: post type label, 3: new status.
			$result['message'] = sprintf( __( 'All %1$s of %2$s have been marked as %3$s.', 'no-page-comment' ), $comment_label, $post_label, $comment_new_status );
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json( $result );
		} else {
			$ref = wp_get_referer();
			if ( $ref ) {
				wp_safe_redirect( $ref );
			} else {
				wp_safe_redirect( admin_url() );
			}
			exit;
		}
	}

	public static function ajax_mod_nopriv() {
		exit( "Ah ah ah. You didn't say the magic word." );
	}

	public static function attachment_comment( $id ) {
		global $wpdb;
		$options          = self::get_admin_options();
		$comment_status   = ( $options['disable_comments_attachment'] ?? 'false' ) === 'true' ? 'closed' : false;
		$trackback_status = ( $options['disable_trackbacks_attachment'] ?? 'false' ) === 'true' ? 'closed' : false;

		if ( $comment_status !== false ) {
			// Direct DB query for bulk performance reasons.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- justified bulk update
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->posts SET comment_status = %s WHERE ID = %d",
					$comment_status,
					$id
				)
			);
		}

		if ( $trackback_status !== false ) {
			// Direct DB query for bulk performance reasons.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- justified bulk update
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->posts SET ping_status = %s WHERE ID = %d",
					$trackback_status,
					$id
				)
			);
		}
	}
}
