<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This plugin requires WordPress' );
}
?>

<form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" class="wrap npc-settings">

<?php
// Add nonce to prevent CSRF
wp_nonce_field( 'sta_npc_csrf_nonce' );
$nonce = ( isset( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : false;
?>

<?php
// Prints out the admin settings page
$sta_npc_nonce = wp_create_nonce( 'sta_npc_nonce' );
$sta_npc_options = $this->sta_npc_get_admin_options();

if ( wp_verify_nonce( $nonce, 'sta_npc_csrf_nonce' ) && isset( $_POST['update_sta_npc_plugin_settings'] ) ) {

	foreach ( get_post_types( '', 'objects' ) as $posttype ) {
		if ( in_array( $posttype->name, $this->excluded_posttypes ) )
			continue;

		if ( isset( $_POST['sta_npc_disable_comments_' . $posttype->name] ) ) {
			$sta_npc_options['disable_comments_' . $posttype->name] = $_POST['sta_npc_disable_comments_' . $posttype->name];
		} else {
			$sta_npc_options['disable_comments_' . $posttype->name] = 'false';
		}

		if ( isset( $_POST['sta_npc_disable_trackbacks_' . $posttype->name] ) ) {
			$sta_npc_options['disable_trackbacks_' . $posttype->name] = $_POST['sta_npc_disable_trackbacks_' . $posttype->name];
		} else {
			$sta_npc_options['disable_trackbacks_' . $posttype->name] = 'false';
		}

	} // end foreach post types

	update_option($this->admin_options_name, $sta_npc_options);
	?>
	<div class="updated"><p><strong><?php _e('Settings Updated.', $this->plugin_domain ); ?></strong></p></div>
<?php } ?>

	<div id="icon-options-general" class="icon32"></div>
	<h2><?php _e( 'No Page Comment Settings', $this->plugin_domain ); ?></h2>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">
						<h3 style="cursor:default;"><span><?php _e('Disable comments on new:', $this->plugin_domain ); ?></span></h3>
						<div class="inside">
							<?php foreach ( get_post_types('','objects') as $posttype ) {
								if ( in_array( $posttype->name, $this->excluded_posttypes ) )
									continue; ?>
								<div>
									<strong class="post-type"><?php echo $posttype->label; ?></strong>
									<div class="inner">
										<label for="sta_npc_disable_comments_<?php echo $posttype->name; ?>">
											<input type="checkbox" id="sta_npc_disable_comments_<?php echo $posttype->name; ?>" name="sta_npc_disable_comments_<?php echo $posttype->name; ?>" value="true" <?php if ( $sta_npc_options['disable_comments_' . $posttype->name] == 'true' ) { echo 'checked="checked"'; } ?>> <?php _e('Comments', $this->plugin_domain ); ?></label>
										<label for="sta_npc_disable_trackbacks_<?php echo $posttype->name; ?>">
											<input type="checkbox" id="sta_npc_disable_trackbacks_<?php echo $posttype->name; ?>" name="sta_npc_disable_trackbacks_<?php echo $posttype->name; ?>" value="true" <?php if ( $sta_npc_options['disable_trackbacks_' . $posttype->name] == 'true' ) { echo 'checked="checked"'; } ?>> <?php _e('Trackbacks', $this->plugin_domain ); ?></label>
									</div>
								</div>
								<br class="clear">
							<?php } ?>
						</div>
					</div>
					<p class="submit">
						<input type="submit" name="update_sta_npc_plugin_settings" id="submit" class="button-primary" value="<?php _e('Update Settings', $this->plugin_domain ); ?>">
					</p>

				</div>
				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">
						<h3 style="cursor:default;"><span><?php _e('Modify all current:', $this->plugin_domain ); ?></span></h3>
						<div class="inside buttons">
							<?php foreach ( get_post_types('','objects') as $posttype ) {
								if ( in_array( $posttype->name, $this->excluded_posttypes ) )
									continue; ?>
							<div>
								<strong class="post-type"><?php echo $posttype->label; ?></strong>
								<div class="inner">
									<div>
										<input type="submit" name="disable_all_<?php echo $posttype->name; ?>_comments" class="button-primary sta_ajax_modify" data-nonce="<?php echo $sta_npc_nonce; ?>" data-post_type="<?php echo $posttype->name; ?>" data-post_label="<?php echo $posttype->label; ?>" data-comment_status="open" data-comment_type="comment" value="<?php _e('Disable All Comments', $this->plugin_domain ); ?>">
										<input type="submit" name="enable_all_<?php echo $posttype->name; ?>_comments" class="button-primary sta_ajax_modify" data-nonce="<?php echo $sta_npc_nonce; ?>" data-post_type="<?php echo $posttype->name; ?>" data-post_label="<?php echo $posttype->label; ?>" data-comment_status="closed" data-comment_type="comment" value="<?php _e('Enable All Comments', $this->plugin_domain ); ?>">
									</div>
									<div>
										<input type="submit" name="disable_all_<?php echo $posttype->name; ?>_trackbacks" class="button-primary sta_ajax_modify" data-nonce="<?php echo $sta_npc_nonce; ?>" data-post_type="<?php echo $posttype->name; ?>" data-post_label="<?php echo $posttype->label; ?>" data-comment_status="open" data-comment_type="ping" value="<?php _e('Disable All Trackbacks', $this->plugin_domain ); ?>">
										<input type="submit" name="enable_all_<?php echo $posttype->name; ?>_trackbacks" class="button-primary sta_ajax_modify" data-nonce="<?php echo $sta_npc_nonce; ?>" data-post_type="<?php echo $posttype->name; ?>" data-post_label="<?php echo $posttype->label; ?>" data-comment_status="closed" data-comment_type="ping" value="<?php _e('Enable All Trackbacks', $this->plugin_domain ); ?>">
									</div>
								</div>
							</div>
							<br class="clear">
					<?php } ?>
						</div>

					</div>

				</div>
			</div>

			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">

					<?php /*<div class="postbox">
						<h3 style="cursor:default;"><span><?php _e('Other plugins by', $this->plugin_domain ); ?> <a href="http://sethalling.com/" title="Seth Alling" style="font-size:15px;">Seth Alling</a>:</span></h3>
						<div class="inside">
							<ul>
								<li style="padding:5px 0;"><a href="http://sethalling.com/plugins/wordpress/wp-faqs-pro" title="WP FAQs Pro" target="_blank">WP FAQs Pro</a></li>
							</ul>
						</div>
					</div>*/ ?>

					<div class="postbox">
						<h3 style="cursor:default;"><span><?php _e('Support No Page Comment:', $this->plugin_domain ); ?></span></h3>
						<div class="inside">
							<ul>
								<li style="padding:5px 0;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4" title="<?php _e('Donate to support the No Page Comment plugin development', $this->plugin_domain ); ?>" target="_blank"><?php _e('Donate', $this->plugin_domain ); ?></a></li>
								<li style="padding:5px 0;"><a href="http://wordpress.org/support/view/plugin-reviews/no-page-comment#postform" title="<?php _e('Write a Review about No Page Comment', $this->plugin_domain ); ?>" target="_blank"><?php _e('Write a Review', $this->plugin_domain ); ?></a></li>
								<li style="padding:5px 0;"><a href="https://github.com/sethta/no-page-comment" title="<?php _e('Fork No Page Comment on Github', $this->plugin_domain ); ?>" target="_blank"><?php _e('Fork No Page Comment', $this->plugin_domain ); ?></a></li>
								<li style="padding:5px 0;"><a href="https://github.com/sethta/no-page-comment/issues" title="<?php _e('Report an Issue on Github', $this->plugin_domain ); ?>" target="_blank"><?php _e('Report an Issue about No Page Comment', $this->plugin_domain ); ?></a></li>
								<li style="padding:5px 0;"><a href="http://sethalling.com/contact/" title="<?php _e('Help Translate No Page Comment', $this->plugin_domain ); ?>" target="_blank"><?php _e('Offer to Translate No Page Comment', $this->plugin_domain ); ?></a></li>
							</ul>
						</div>
					</div>

					<div class="postbox">
						<h3 style="cursor:default;"><span><?php _e('Translation Thanks:', $this->plugin_domain ); ?></span></h3>
						<div class="inside">
							<ul>
								<li style="padding:5px 0;"><?php _e('Dutch and Italian:', $this->plugin_domain ); ?> <a href="http://www.fravaco.be/" title="Fravaco" target="_blank">Fravaco</a></li>
								<li style="padding:5px 0;"><?php _e('Serbian:', $this->plugin_domain ); ?> <a href="http://firstsiteguide.com/" title="firstsiteguide.com" target="_blank">firstsiteguide.com</a></li>
								<li style="padding:5px 0;"><?php _e('Spanish:', $this->plugin_domain ); ?> <a href="http://www.webhostinghub.com/" title="Maria Ramos, WebHostingHub" target="_blank">Maria Ramos, WebHostingHub</a></li>
								<li style="padding:5px 0;"><?php _e('Swedish:', $this->plugin_domain ); ?> <a href="http://adevade.com/" title="Andréas Lundgren" target="_blank">Andréas Lundgren</a></li>
							</ul>
						</div>
					</div>

				</div>
			</div>

		</div>
		<br class="clear">
	</div>

</form>
