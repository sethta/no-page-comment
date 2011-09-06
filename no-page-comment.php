<?php
/*
Plugin Name: No Page Comment
Plugin URI: http://sethalling.com/plugins/no-page-comment
Description: A plugin that uses javascript to disable comments by default on a page, but leave them enabled on posts, while still giving you the ability to individually set them on a page or post basis. 
Version: 0.3
Author: Seth Alling
Author URI: http://sethalling.com/

    Plugin: Copyright (c) 2011 Seth Alling

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
*/

register_activation_hook(__FILE__, 'sta_npc_activate');

if ( ! function_exists('sta_npc_activate') ) {
	function sta_npc_activate() {
		sta_npc_load();
		global $sta_npc_plugin;
	}
}

if ( ! function_exists('sta_npc_load') ) {
	function sta_npc_load() {
		if ( ! class_exists('STA_NPC_Plugin') ) {
			class STA_NPC_Plugin {
				var $admin_options_name = 'sta_npc_admin_options_name';
				var $admin_users_name   = 'sta_npc_admin_options_name';
				var $plugin_domain      = 'sta_npc_lang';
				public $plugin_name     = 'no-page-comment';
				public $plugin_file;
				public $plugin_dir;
				
				// Plugin Constructor
				function sta_npc_plugin() {
					$this->plugin_dir = WP_PLUGIN_URL.'/'.$this->plugin_name;
					$this->plugin_file = $this->plugin_name . '.php';
				}
				
				// Intialize Admin Options
				function sta_npc_init() {
					$this->sta_npc_get_admin_options();
				}
				
				// Returns an array of admin options
				function sta_npc_get_admin_options() {
					$sta_npc_admin_options = array(
						'disable_comments'   => 'true',
						'disable_trackbacks' => 'true'
					);
					$sta_npc_options = get_option($this->admin_options_name);
					if ( ! empty($sta_npc_options) ) {
						foreach ($sta_npc_options as $key => $option)
							$sta_npc_admin_options[$key] = $option;
					}				
					update_option($this->admin_options_name, $sta_npc_admin_options);
					return $sta_npc_admin_options;
				}
				
				// Prints out the admin page
				function sta_npc_print_admin_page() {
					$sta_npc_options = $this->sta_npc_get_admin_options();
										
					if ( isset($_POST['update_sta_npc_plugin_settings']) ) {
						if ( isset($_POST['sta_npc_disable_comments']) ) {
							$sta_npc_options['disable_comments'] = $_POST['sta_npc_disable_comments'];
						}
						if ( isset($_POST['sta_npc_disable_trackbacks']) ) {
							$sta_npc_options['disable_trackbacks'] = $_POST['sta_npc_disable_trackbacks'];
						}
						update_option($this->admin_options_name, $sta_npc_options);
						?>
						<div class="updated"><p><strong><?php _e('Settings Updated.', $this->plugin_domain);?></strong></p></div>
					<?php } ?>
					<div class=wrap>
						<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" style="float:left; width:500px;">
							<h2>No Page Comment Settings</h2>

							<h3>Disable comments on new pages:</h3>
							<p><label for="sta_npc_disable_comments_yes" style="width:70px; float:left;"><input type="radio" id="sta_npc_disable_comments_yes" name="sta_npc_disable_comments" value="true" <?php if ( $sta_npc_options['disable_comments'] == 'true' ) { _e('checked="checked"', $this->plugin_domain); } ?> /> Yes</label><label for="sta_npc_disable_comments_no" style="width:70px; float:left;"><input type="radio" id="sta_npc_disable_comments_no" name="sta_npc_disable_comments" value="false" <?php if ( $sta_npc_options['disable_comments'] == 'false' ) { _e('checked="checked"', $this->plugin_domain); } ?>/> No</label></p><br style="clear:both;" />
		
							<h3>Disable trackbacks on new pages:</h3>
							<p><label for="sta_npc_disable_trackbacks_yes" style="width:70px; float:left;"><input type="radio" id="sta_npc_disable_trackbacks_yes" name="sta_npc_disable_trackbacks" value="true" <?php if ( $sta_npc_options['disable_trackbacks'] == 'true' ) { _e('checked="checked"', $this->plugin_domain); } ?> /> Yes</label><label for="sta_npc_disable_trackbacks_no" style="width:70px; float:left;"><input type="radio" id="sta_npc_disable_trackbacks_no" name="sta_npc_disable_trackbacks" value="false" <?php if ( $sta_npc_options['disable_trackbacks'] == 'false' ) { _e('checked="checked"', $this->plugin_domain); } ?>/> No</label></p><br style="clear:both;" />
							
							<div class="submit">
								<input type="submit" name="update_sta_npc_plugin_settings" value="<?php _e('Update Settings', $this->plugin_domain); ?>" />
							</div>
						</form>
						<div style="float:left; margin-left:20px;">
							<h3>View other plugins created by <a href="http://sethalling.com/" title="Seth Alling">Seth Alling</a>:</h3>
							<ul>
								<li><a href="http://sethalling.com/plugins/wp-faqs-pro" title="WP FAQs Pro">WP FAQs Pro</a></li>
							</ul>
						</div>
					</div>
				<?php } // End sta_npc_print_admin_page function
		
				function sta_npc_settings_link($links, $file) {
					if ( basename($file) == $this->plugin_file ) {
						$settings_link = '<a href="' . admin_url('options-general.php?page=' . $this->plugin_file) . '">Settings</a>';
						array_unshift($links, $settings_link);
					}
					return $links;
				}
		
				function sta_npc_plugin_admin() {
					if ( function_exists('add_options_page') ) {
						add_options_page('No Page Comment Settings', 'No Page Comment', 'manage_options', $this->plugin_file, array( $this, 'sta_npc_print_admin_page' ));
					}
				}

				function sta_no_page_comment() {
					global $pagenow;
					global $post;
					$sta_npc_options = $this->sta_npc_get_admin_options();
					if ( (is_admin()) && ($pagenow=='post-new.php') && ($post->filter=='raw') && ($post->post_type=='page') ) {
						wp_enqueue_script('jquery'); ?>
						
						<script type="text/javascript">
						jQuery(document).ready(function() {
							<?php if ( $sta_npc_options['disable_comments'] == 'true' ) { ?>
								if ( jQuery('#comment_status').length ) {
									jQuery('#comment_status').attr('checked', false);
								}
							<?php }
							if ( $sta_npc_options['disable_trackbacks'] == 'true' ) { ?>
								if ( jQuery('#ping_status').length ) {
									jQuery('#ping_status').attr('checked', false);
								}
							<?php } ?>
						});
						</script>
				
					<?php }
				}

			}
		
		} // End Class STA_NPC_Plugin
		
		if ( class_exists('STA_NPC_Plugin') ) {
			global $sta_npc_plugin;
			$sta_npc_plugin = new STA_NPC_Plugin();
		}
		
		// Actions, Filters and Shortcodes
		if ( isset($sta_npc_plugin) ) {
			// Actions
			add_action('admin_menu', array( &$sta_npc_plugin, 'sta_npc_plugin_admin' )); // Activate admin settings page
			add_action('activate_no-page-comment/no-page-comment.php', array( &$sta_npc_plugin, 'sta_npc_init' )); // Activate admin options
			add_action( 'admin_head', array( &$sta_npc_plugin, 'sta_no_page_comment' ));
			
			// Filters
			add_filter('plugin_action_links', array( &$sta_npc_plugin, 'sta_npc_settings_link'), 10, 2 );
		}
	}
}

sta_npc_load();


