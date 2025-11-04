<?php

namespace NoPageComment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_View {
	private static function get_excluded_posttypes() {
		return [
			'revision',
			'nav_menu_item',
		];
	}

	public static function print_admin_page() {
		$options = Settings::get_admin_options();

		if ( isset( $_POST['update_sta_npc_plugin_settings'] ) && isset( $_POST['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );

			if ( wp_verify_nonce( $nonce, 'sta_npc_csrf_nonce' ) ) {
				$admin_options = $options;

				foreach ( get_post_types( '', 'objects' ) as $posttype ) {
					if ( in_array( $posttype->name, self::get_excluded_posttypes(), true ) ) {
						continue;
					}

					$admin_options[ 'disable_comments_' . $posttype->name ]   = isset( $_POST[ 'sta_npc_disable_comments_' . $posttype->name ] ) ? 'true' : 'false';
					$admin_options[ 'disable_trackbacks_' . $posttype->name ] = isset( $_POST[ 'sta_npc_disable_trackbacks_' . $posttype->name ] ) ? 'true' : 'false';
				}

				update_option( 'sta_npc_options', $admin_options );
				echo '<div class="updated"><p><strong>' . esc_html__( 'Settings Updated.', 'no-page-comment' ) . '</strong></p></div>';
				$options = $admin_options;
			}
		}

		$sta_npc_nonce = wp_create_nonce( 'sta_npc_nonce' );
		?>

		<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=' . Settings::get_admin_slug() ) ); ?>" class="wrap npc-settings">
			<?php wp_nonce_field( 'sta_npc_csrf_nonce' ); ?>

			<div id="icon-options-general" class="icon32"></div>
			<h2><?php echo esc_html__( 'No Page Comment Settings', 'no-page-comment' ); ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">

					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">
								<h3 style="cursor:default;"><span><?php echo esc_html__( 'Checked boxes will disable comments or trackbacks on new:', 'no-page-comment' ); ?></span></h3>
								<div class="inside">
									<?php
									foreach ( get_post_types( '', 'objects' ) as $posttype ) {
										if ( in_array( $posttype->name, self::get_excluded_posttypes(), true ) ) {
											continue;
										}

										$disable_comments_key   = 'disable_comments_' . $posttype->name;
										$disable_trackbacks_key = 'disable_trackbacks_' . $posttype->name;

										$comments_disabled   = isset( $options[ $disable_comments_key ] ) && 'true' === $options[ $disable_comments_key ];
										$trackbacks_disabled = isset( $options[ $disable_trackbacks_key ] ) && 'true' === $options[ $disable_trackbacks_key ];
										?>
										<div>
											<strong class="post-type"><?php echo esc_html( $posttype->label ); ?></strong>
											<div class="inner">
												<label for="sta_npc_disable_comments_<?php echo esc_attr( $posttype->name ); ?>">
													<input type="checkbox" id="sta_npc_disable_comments_<?php echo esc_attr( $posttype->name ); ?>" name="sta_npc_disable_comments_<?php echo esc_attr( $posttype->name ); ?>" value="true" <?php checked( $comments_disabled ); ?> /> <?php echo esc_html__( 'Comments', 'no-page-comment' ); ?></label>
												<label for="sta_npc_disable_trackbacks_<?php echo esc_attr( $posttype->name ); ?>">
													<input type="checkbox" id="sta_npc_disable_trackbacks_<?php echo esc_attr( $posttype->name ); ?>" name="sta_npc_disable_trackbacks_<?php echo esc_attr( $posttype->name ); ?>" value="true" <?php checked( $trackbacks_disabled ); ?> /> <?php echo esc_html__( 'Trackbacks', 'no-page-comment' ); ?></label>
											</div>
										</div>
										<br class="clear">
									<?php } ?>
								</div>
							</div>
							<p class="submit">
								<input type="submit" name="update_sta_npc_plugin_settings" id="submit" class="button-primary" value="<?php echo esc_attr_x( 'Update Settings', 'button', 'no-page-comment' ); ?>">
							</p>

						</div>
						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">
								<h3 style="cursor:default;"><span><?php echo esc_html__( 'Modify all current:', 'no-page-comment' ); ?></span></h3>
								<div class="inside buttons">
									<?php
									foreach ( get_post_types( '', 'objects' ) as $posttype ) {
										if ( in_array( $posttype->name, self::get_excluded_posttypes(), true ) ) {
											continue;
										}
										?>
										<div>
											<strong class="post-type"><?php echo esc_html( $posttype->label ); ?></strong>
											<div class="inner">
												<div>
													<input type="submit" name="disable_all_<?php echo esc_attr( $posttype->name ); ?>_comments" class="button-primary sta_ajax_modify" data-nonce="<?php echo esc_attr( $sta_npc_nonce ); ?>" data-post_type="<?php echo esc_attr( $posttype->name ); ?>" data-post_label="<?php echo esc_attr( $posttype->label ); ?>" data-comment_status="open" data-comment_type="comment" value="<?php echo esc_attr__( 'Disable All Comments', 'no-page-comment' ); ?>">
													<input type="submit" name="enable_all_<?php echo esc_attr( $posttype->name ); ?>_comments" class="button-primary sta_ajax_modify" data-nonce="<?php echo esc_attr( $sta_npc_nonce ); ?>" data-post_type="<?php echo esc_attr( $posttype->name ); ?>" data-post_label="<?php echo esc_attr( $posttype->label ); ?>" data-comment_status="closed" data-comment_type="comment" value="<?php echo esc_attr__( 'Enable All Comments', 'no-page-comment' ); ?>">
												</div>
												<div>
													<input type="submit" name="disable_all_<?php echo esc_attr( $posttype->name ); ?>_trackbacks" class="button-primary sta_ajax_modify" data-nonce="<?php echo esc_attr( $sta_npc_nonce ); ?>" data-post_type="<?php echo esc_attr( $posttype->name ); ?>" data-post_label="<?php echo esc_attr( $posttype->label ); ?>" data-comment_status="open" data-comment_type="ping" value="<?php echo esc_attr__( 'Disable All Trackbacks', 'no-page-comment' ); ?>">
													<input type="submit" name="enable_all_<?php echo esc_attr( $posttype->name ); ?>_trackbacks" class="button-primary sta_ajax_modify" data-nonce="<?php echo esc_attr( $sta_npc_nonce ); ?>" data-post_type="<?php echo esc_attr( $posttype->name ); ?>" data-post_label="<?php echo esc_attr( $posttype->label ); ?>" data-comment_status="closed" data-comment_type="ping" value="<?php echo esc_attr__( 'Enable All Trackbacks', 'no-page-comment' ); ?>">
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

							<div class="postbox">
								<h3 style="cursor:default;"><span><?php echo esc_html__( 'Support No Page Comment:', 'no-page-comment' ); ?></span></h3>
								<div class="inside">
									<ul>
										<li style="padding:5px 0;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4" title="<?php echo esc_attr__( 'Donate to support the No Page Comment plugin development', 'no-page-comment' ); ?>" target="_blank"><?php echo esc_html__( 'Donate', 'no-page-comment' ); ?></a></li>
										<li style="padding:5px 0;"><a href="http://wordpress.org/support/view/plugin-reviews/no-page-comment#postform" title="<?php echo esc_attr__( 'Write a Review about No Page Comment', 'no-page-comment' ); ?>" target="_blank"><?php echo esc_html__( 'Write a Review', 'no-page-comment' ); ?></a></li>
										<li style="padding:5px 0;"><a href="https://github.com/sethta/no-page-comment" title="<?php echo esc_attr__( 'Fork No Page Comment on Github', 'no-page-comment' ); ?>" target="_blank"><?php echo esc_html__( 'Fork No Page Comment', 'no-page-comment' ); ?></a></li>
										<li style="padding:5px 0;"><a href="https://github.com/sethta/no-page-comment/issues" title="<?php echo esc_attr__( 'Report an Issue on Github', 'no-page-comment' ); ?>" target="_blank"><?php echo esc_html__( 'Report an Issue about No Page Comment', 'no-page-comment' ); ?></a></li>
									</ul>
								</div>
							</div>

							<div class="postbox">
								<h3 style="cursor:default;"><span><?php echo esc_html__( 'Translation Thanks:', 'no-page-comment' ); ?></span></h3>
								<div class="inside">
									<ul>
										<li style="padding:5px 0;"><?php echo esc_html__( 'Dutch and Italian:', 'no-page-comment' ); ?> Fravaco</li>
										<li style="padding:5px 0;"><?php echo esc_html__( 'Serbian:', 'no-page-comment' ); ?> firstsiteguide.com</li>
										<li style="padding:5px 0;"><?php echo esc_html__( 'Spanish:', 'no-page-comment' ); ?> Maria Ramos, WebHostingHub</li>
										<li style="padding:5px 0;"><?php echo esc_html__( 'Swedish:', 'no-page-comment' ); ?> Andréas Lundgren</li>
									</ul>
								</div>
							</div>

						</div>
					</div>

				</div>
				<br class="clear">
			</div>
		</form>

		<?php
	}
}
