<?php
/*
Plugin Name: No Page Comment
Plugin URI: http://sethalling.com/plugins/no-page-comment
Description: An admin interface to control the default comment and trackback settings on new posts, pages and custom post types.
Version: 1.2
Author: Seth Alling
Author URI: http://sethalling.com/
Text Domain: no-page-comment

	Plugin: Copyright (c) 2011-2015 Seth Alling

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	  _____      _   _                _ _ _                   _...._
	 / ____|    | | | |         /\   | | (_)                .'/  \ _'.
	| (___   ___| |_| |__      /  \  | | |_ _ __   __ _    /##\__/##\_\
	 \___ \ / _ \ __| '_ \    / /\ \ | | | | '_ \ / _` |  |\##/  \##/  |
	 ____) |  __/ |_| | | |  / ____ \| | | | | | | (_| |  |/  \__/  \ _|
	|_____/ \___|\__|_| |_| /_/    \_\_|_|_|_| |_|\__, |   \ _/##\__/#/
	                                               __/ |    '.\##/__.'
	Plugin developed by: http://sethalling.com    |___/       `""""`
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This plugin requires WordPress' );
}

register_activation_hook( __FILE__, 'sta_npc_activate' );

define( 'STA_NPC_WP_VERSION', version_compare( get_bloginfo( 'version' ), '3.4', '>=' ) );

if ( ! function_exists( 'sta_npc_activate' ) ) {
	function sta_npc_activate() {
		sta_npc_load();
		global $sta_npc_plugin;
	}
}

// Set text domain for translation
function sta_load_text_domain() {
	load_plugin_textdomain( 'no-page-comment', false, basename( dirname( __FILE__ ) ) . '/lang/' );
}
add_action( 'init', 'sta_load_text_domain' ); // Set text domain for translation

if ( ! function_exists( 'sta_npc_load' ) ) {

	function sta_npc_load() {

		if ( ! class_exists( 'STA_NPC_Plugin' ) ) {

			class STA_NPC_Plugin {

				var $admin_options_name     = 'sta_npc_options',
				    $admin_options_name_old = 'sta_npc_admin_options_name',
				    $plugin_domain          = 'no-page-comment';
				public $plugin_name         = 'no-page-comment';
				public $plugin_file;
				public $plugin_dir;
				public $wp_posttypes = array(
					'post',
					'page',
					'revision',
					'nav_menu_item',
					'attachment'
				);

				public $excluded_posttypes = array(
					'revision',
					'nav_menu_item',
				);

				public $plugin_ver = '1.2';

				// Plugin Constructor
				function __construct() {
					$this->plugin_dir = plugins_url( '/', __FILE__ );
					$this->plugin_file = $this->plugin_name . '.php';
				}

				// Intialize Admin Options
				function sta_npc_init() {
					$this->sta_npc_get_admin_options();
				}

				// Returns an array of admin options
				function sta_npc_get_admin_options() {

					// Rename options from old options name
					if ( get_option( $this->admin_options_name_old ) ) {
						update_option( $this->admin_options_name, get_option( $this->admin_options_name_old ) );
						delete_option( $this->admin_options_name_old );
					}

					$sta_npc_admin_options = array(
						'disable_comments_post'   => '',
						'disable_trackbacks_post' => '',
						'disable_comments_page'   => 'true',
						'disable_trackbacks_page' => 'true',
						'disable_comments_attachment'   => '',
						'disable_trackbacks_attachment' => ''
					);

					foreach ( get_post_types( '', 'objects' ) as $posttype ) {
						if ( in_array( $posttype->name, $this->wp_posttypes ) )
							continue;

						$sta_npc_admin_options['disable_comments_' . $posttype->name] = 'true';
						$sta_npc_admin_options['disable_trackbacks_' . $posttype->name] = 'true';
					} // end foreach post types

					$sta_npc_options = get_option( $this->admin_options_name );
					if ( ! empty( $sta_npc_options ) ) {

						foreach ( $sta_npc_options as $key => $option )
							$sta_npc_admin_options[$key] = $option;
					}

					update_option( $this->admin_options_name, $sta_npc_admin_options );
					return $sta_npc_admin_options;
				}

				// Print out the admin page
				function sta_npc_print_admin_page() {
					include( dirname( __FILE__ ) . '/no-page-comment-settings.php' );
				}

				// Add stylesheet to admin page
			    function sta_npc_admin_styles( $hook ) {
					if ( $hook == 'settings_page_no-page-comment' ) {
						wp_register_style( 'sta_npc', $this->plugin_dir . '/no-page-comment.css', false, $this->plugin_ver );
						wp_enqueue_style( 'sta_npc' );
					}
			    }

				// Add settings link to plugins page
				function sta_npc_settings_link( $links, $file ) {
					if ( basename( $file ) == $this->plugin_file ) {
						$settings_link = '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_file ) . '">' . __( 'Settings', $this->plugin_domain ) . '</a>';
						array_unshift( $links, $settings_link );
					}
					return $links;
				}

				// Add settings page to options sidebar
				function sta_npc_plugin_admin() {
					if ( function_exists( 'add_options_page' ) ) {
						add_options_page( __( 'No Page Comment Settings', $this->plugin_domain ), __( 'No Page Comment', $this->plugin_domain ), 'manage_options', $this->plugin_file, array( $this, 'sta_npc_print_admin_page' ) );
					}
				}

				// Modify discussion options page with a link to NPC settings page
				function sta_discussion_options() {
					global $pagenow;
					global $post;

					if ( ( is_admin() ) && ( $pagenow == 'options-discussion.php' ) ) {
						$settings_link = __( 'Comment and trackback defaults controlled through', $this->plugin_domain ) . ' <a href="' . admin_url( 'options-general.php?page=' . $this->plugin_file ) . '">' . __( 'No Page Comment Settings', $this->plugin_domain ) . '</a>';
						wp_enqueue_script( 'jquery' ); ?>

						<script type="text/javascript">
						jQuery(document).ready( function() {
							jQuery('label[for="default_ping_status"]').remove().prev('br').remove();
							jQuery('label[for="default_comment_status"]').prev('br').remove();
							jQuery('label[for="default_comment_status"]').next('br').remove();
							jQuery('label[for="default_comment_status"]').next('p').html('<?php echo $settings_link; ?>');
							jQuery('label[for="default_comment_status"]').next('small').html('<?php echo $settings_link; ?>');
							jQuery('label[for="default_comment_status"]').remove();
						});
						</script>

					<?php }
				}

				// Disable comments and trackbacks on at least WP version 4.3 with a hook
				function wpdocs_open_comments_for_myposttype( $status, $post_type, $comment_type ) {
					$sta_npc_options = $this->sta_npc_get_admin_options();

					if ( $comment_type == 'comment' ) { // Check if comment or trackback

						if ( isset( $sta_npc_options['disable_comments_' . $post_type] ) ) {

							if ( $sta_npc_options['disable_comments_' . $post_type] == 'true' ) {
								return 'closed';
							} else {
								return 'open';
							}

						} else {
							return $status;
						}

					} elseif ( $comment_type == 'pingback' ) { // Double check if trackback

						if ( isset( $sta_npc_options['disable_trackbacks_' . $post_type] ) ) {

							if ( $sta_npc_options['disable_trackbacks_' . $post_type] == 'true' ) {
								return 'closed';
							} else {
								return 'open';
							}

						} else {
							return $status;
						}

					} else { // This should never happen, but just in case
						return $status;
					}

				}

				// Disable comments with hook on WP versions 3.4-4.2
				function sta_no_page_comment() {
					global $pagenow;
					$sta_npc_options = $this->sta_npc_get_admin_options();
					if ( ( is_admin() ) && ( $pagenow == 'post-new.php' ) ) {
						$posttype = ( isset( $_GET['post_type'] ) ) ? $_GET['post_type'] : 'post';

						if ( $sta_npc_options['disable_comments_' . $posttype] == 'true' ) {
							return 'closed';
						} else {
							return 'open';
						}
					}
				}

				// Disable trackbacks with hook on versions 3.4-4.2
				function sta_no_page_trackback() {
					global $pagenow;
					$sta_npc_options = $this->sta_npc_get_admin_options();
					if ( ( is_admin() ) && ( $pagenow == 'post-new.php' ) ) {
						$posttype = ( isset( $_GET['post_type'] ) ) ? $_GET['post_type'] : 'post';

						if ( $sta_npc_options['disable_trackbacks_' . $posttype] == 'true' ) {
							return 'closed';
						} else {
							return 'open';
						}
					}
				}

				// Disable comments/trackbacks with jQuery (for WP versions earlier than 3.4)
				function sta_no_page_comment_jquery() {
					global $pagenow;
					global $post;

					$sta_npc_options = $this->sta_npc_get_admin_options();
					if ( ( is_admin() ) && ( $pagenow == 'post-new.php' ) && ( $post->filter == 'raw' ) ) {
						wp_enqueue_script( 'jquery' );
						$posttype = $post->post_type; ?>

						<script type="text/javascript">
						jQuery(document).ready( function() {
							<?php if ( isset($sta_npc_options['disable_comments_' . $posttype]) ) {
								if ( $sta_npc_options['disable_comments_' . $posttype] == 'true' ) { ?>
									if ( jQuery('#comment_status').length ) {
										jQuery('#comment_status').attr('checked', false);
									}
								<?php } else { ?>
									if ( jQuery('#comment_status').length ) {
										jQuery('#comment_status').attr('checked', true);
									}
								<?php }
							}
							if ( isset($sta_npc_options['disable_trackbacks_' . $posttype]) ) {
								if ( $sta_npc_options['disable_trackbacks_' . $posttype] == 'true' ) { ?>
									if ( jQuery('#ping_status').length ) {
										jQuery('#ping_status').attr('checked', false);
									}
								<?php } else { ?>
									if ( jQuery('#ping_status').length ) {
										jQuery('#ping_status').attr('checked', true);
									}
								<?php }
							} ?>
						});
						</script>

					<?php }
				}

				// Add ajax script to admin page
				function sta_no_page_comment_ajax() {
					global $pagenow;
					global $post;
					if ( ( is_admin() ) && ( $pagenow == 'options-general.php' ) && isset( $_GET['page'] ) ) {
						if ( $_GET['page'] == 'no-page-comment.php' ) {
							wp_enqueue_script( 'jquery' );
							// Load Ajax File
							wp_register_script( 'ajax-script', plugins_url( '/page-comment.js', __FILE__ ), array( 'jquery' ) );
							wp_localize_script( 'ajax-script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

							wp_enqueue_script( 'jquery' );
							wp_enqueue_script( 'ajax-script' );
						}
					}
				}

				// Ajax Function for WP Comment DB Modification
				function sta_npc_mod() {
					if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'sta_npc_nonce') ) {
						exit( 'No naughty business please' );
					}

					global $wpdb;

					$result[] = array();
					$comment_type = $_REQUEST['comment_type'];
					$comment_status = $_REQUEST['comment_status'];
					if ( $comment_status == 'open' )
						$comment_new_status = 'closed';
					elseif ( $comment_status == 'closed' ) {
						$comment_new_status = 'open';
					}
					$post_type = $_REQUEST['post_type'];
					$post_label = $_REQUEST['post_label'];


					if ( $comment_type == 'ping' ) {
						$comment_label = 'trackbacks';
						$comment_query = $wpdb->prepare(
							"
							UPDATE $wpdb->posts
							SET ping_status = %s
							WHERE ping_status = %s
							AND post_type = %s
							",
							$comment_new_status,
							$comment_status,
							$post_type
						);
					} else {
						$comment_label = 'comments';
						$comment_query = $wpdb->prepare(
							"
							UPDATE $wpdb->posts
							SET comment_status = %s
							WHERE comment_status = %s
							AND post_type = %s
							",
							$comment_new_status,
							$comment_status,
							$post_type
						);
					}

					if ( $comment_query === FALSE ) {
						$result['type'] = 'error';
						$result['message'] = 'Something went wrong. Please refresh this page and try again.';
					} else {
						$wpdb->query( $comment_query );
						$result['type'] = 'success';
						$result['message'] = 'All ' . $comment_label . ' of ' . $post_label . ' have been marked as ' . $comment_new_status;
					}

					if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
							$result = json_encode( $result );
							echo $result;
					}
					else {
							header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
					}

					die();

				}

				function nopriv_sta_npc_mod() {
					exit( 'No naughty business please' );
				}

				// Disable comments/trackbacks on attachments
				function attachment_comment( $id ) {
					global $wpdb;
					$sta_npc_options = $this->sta_npc_get_admin_options();
					$comment_status = ( $sta_npc_options['disable_comments_attachment'] == 'true' ) ? 'closed' : 'open';
					$trackback_status = ( $sta_npc_options['disable_trackbacks_attachment'] == 'true' ) ? 'closed' : 'open';

					$comment_query = $wpdb->prepare(
						"
						UPDATE $wpdb->posts
						SET comment_status = %s
						WHERE ID = %s
						",
						$comment_status,
						$id
					);

					$trackback_query = $wpdb->prepare(
						"
						UPDATE $wpdb->posts
						SET ping_status = %s
						WHERE ID = %s
						",
						$trackback_status,
						$id
					);

					if ( $comment_query === FALSE ) {
					} else {
						$wpdb->query( $comment_query );
					}

					if ( $trackback_query === FALSE ) {
					} else {
						$wpdb->query( $trackback_query );
					}

				}

			}

		} // End Class STA_NPC_Plugin

		if ( class_exists( 'STA_NPC_Plugin' ) ) {
			global $sta_npc_plugin;
			$sta_npc_plugin = new STA_NPC_Plugin();
		}

		// Actions, Filters and Shortcodes
		if ( isset( $sta_npc_plugin ) ) {
			// Actions
			add_action( 'admin_menu', array( &$sta_npc_plugin, 'sta_npc_plugin_admin' ) ); // Activate admin settings page
			add_action( 'activate_no-page-comment/no-page-comment.php', array( &$sta_npc_plugin, 'sta_npc_init' ) ); // Activate admin options
			add_action( 'admin_enqueue_scripts', array( &$sta_npc_plugin, 'sta_npc_admin_styles' ) ); // Add admin stylesheet
			add_action( 'admin_head', array( &$sta_npc_plugin, 'sta_no_page_comment_ajax' ) ); // Add ajax scripts
			add_action( 'wp_ajax_sta_npc_mod', array( &$sta_npc_plugin, 'sta_npc_mod' ) ); // Add ajax function
			add_action( 'wp_ajax_nopriv_sta_npc_mod', array( &$sta_npc_plugin, 'nopriv_sta_npc_mod' ) ); // Add logged out ajax function
			add_action( 'add_attachment', array( &$sta_npc_plugin, 'attachment_comment' ) ); // Set comment status on new attachments

			// Filters
			add_filter( 'plugin_action_links', array( &$sta_npc_plugin, 'sta_npc_settings_link' ), 10, 2 ); // Add settings link to plugins page
			add_filter( 'admin_head', array(&$sta_npc_plugin, 'sta_discussion_options' ) ); // Change discussion options replace defaults with link to NPC settings

			// Run correct function depending on version
			if ( function_exists( 'get_default_comment_status' ) ) {
				add_filter( 'get_default_comment_status', array(&$sta_npc_plugin, 'wpdocs_open_comments_for_myposttype' ), 10, 3 ); // Comment settings
			} elseif ( STA_NPC_WP_VERSION ) {
				add_filter( 'pre_option_default_comment_status', array(&$sta_npc_plugin, 'sta_no_page_comment' ) ); // Comment settings
				add_filter( 'pre_option_default_ping_status', array(&$sta_npc_plugin, 'sta_no_page_trackback' ) ); // Trackback settings
			} else { // Use jQuery for WordPress versions earlier than 3.4
				add_action( 'admin_head', array( &$sta_npc_plugin, 'sta_no_page_comment_jquery' ) ); // Add jquery scripts
			}

		}

	}

}

sta_npc_load();