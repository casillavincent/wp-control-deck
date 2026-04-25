<?php
/**
 * Plugin Name: WP Control Deck
 * Description: A lightweight starter plugin for WP Control Deck.
 * Version: 1.0.0
 * Author: Vincent Casilla
 * Text Domain: wp-control-deck
 *
 * @package WP_Control_Deck
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_CONTROL_DECK_VERSION', '1.0.0' );
define( 'WP_CONTROL_DECK_FILE', __FILE__ );
define( 'WP_CONTROL_DECK_PATH', plugin_dir_path( WP_CONTROL_DECK_FILE ) );
define( 'WP_CONTROL_DECK_URL', plugin_dir_url( WP_CONTROL_DECK_FILE ) );
define( 'WP_CONTROL_DECK_DISABLE_COMMENTS_OPTION', 'wp_control_deck_disable_comments_globally' );
define( 'WP_CONTROL_DECK_DISABLE_GUTENBERG_OPTION', 'wp_control_deck_disable_gutenberg' );
define( 'WP_CONTROL_DECK_GUTENBERG_EXCLUSIONS_OPTION', 'wp_control_deck_gutenberg_excluded_post_types' );
define( 'WP_CONTROL_DECK_ADMIN_BAR_HOTLINKS_OPTION', 'wp_control_deck_admin_bar_hotlinks' );

/**
 * Runs when the plugin is activated.
 */
function wp_control_deck_activate() {
	// Placeholder for future activation tasks.
}
register_activation_hook( WP_CONTROL_DECK_FILE, 'wp_control_deck_activate' );

/**
 * Runs when the plugin is deactivated.
 */
function wp_control_deck_deactivate() {
	// Placeholder for future deactivation tasks.
}
register_deactivation_hook( WP_CONTROL_DECK_FILE, 'wp_control_deck_deactivate' );

/**
 * Boots the plugin.
 */
function wp_control_deck_bootstrap() {
	add_action( 'admin_menu', 'wp_control_deck_add_admin_menu' );
	add_action( 'admin_init', 'wp_control_deck_register_settings' );
	add_action( 'admin_post_wp_control_deck_delete_comments', 'wp_control_deck_handle_delete_comments' );
	add_action( 'admin_bar_menu', 'wp_control_deck_add_admin_bar_hotlinks', 100 );

	if ( wp_control_deck_comments_are_disabled() ) {
		wp_control_deck_disable_comments();
	}

	if ( wp_control_deck_gutenberg_is_disabled() ) {
		wp_control_deck_disable_gutenberg();
	}
}
add_action( 'plugins_loaded', 'wp_control_deck_bootstrap' );

/**
 * Adds the WP Control Deck admin menu.
 */
function wp_control_deck_add_admin_menu() {
	add_menu_page(
		__( 'WP Control Deck', 'wp-control-deck' ),
		__( 'WP Control Deck', 'wp-control-deck' ),
		'manage_options',
		'wp-control-deck',
		'wp_control_deck_render_admin_page',
		'dashicons-admin-generic',
		65
	);
}

/**
 * Registers plugin settings.
 */
function wp_control_deck_register_settings() {
	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_DISABLE_COMMENTS_OPTION,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'wp_validate_boolean',
			'default'           => false,
		)
	);

	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_DISABLE_GUTENBERG_OPTION,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'wp_validate_boolean',
			'default'           => false,
		)
	);

	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_GUTENBERG_EXCLUSIONS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wp_control_deck_sanitize_post_type_exclusions',
			'default'           => array(),
		)
	);

	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_ADMIN_BAR_HOTLINKS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wp_control_deck_sanitize_admin_bar_hotlinks',
			'default'           => array(),
		)
	);
}

/**
 * Renders the WP Control Deck admin page.
 */
function wp_control_deck_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$comments_disabled    = wp_control_deck_comments_are_disabled();
	$gutenberg_disabled   = wp_control_deck_gutenberg_is_disabled();
	$gutenberg_exclusions = wp_control_deck_get_gutenberg_exclusions();
	$admin_bar_hotlinks   = wp_control_deck_get_admin_bar_hotlinks();
	$post_types           = wp_control_deck_get_editor_post_types();
	$deleted_count        = isset( $_GET['wp_control_deck_deleted_comments'] ) ? absint( $_GET['wp_control_deck_deleted_comments'] ) : null;
	$delete_confirm       = __( 'Are you sure you want to permanently delete all existing comments? This cannot be undone.', 'wp-control-deck' );
	?>
	<div class="wrap wp-control-deck-page">
		<div class="wp-control-deck-header">
			<h1><?php esc_html_e( 'WP Control Deck', 'wp-control-deck' ); ?></h1>
		</div>

		<?php if ( null !== $deleted_count ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %d: number of deleted comments. */
						esc_html( _n( '%d comment deleted.', '%d comments deleted.', $deleted_count, 'wp-control-deck' ) ),
						absint( $deleted_count )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<div class="wp-control-deck-grid">
			<form class="wp-control-deck-settings-form" method="post" action="options.php">
				<?php settings_fields( 'wp_control_deck_settings' ); ?>
				<section class="wp-control-deck-card">
					<div class="wp-control-deck-card-header">
						<h2><?php esc_html_e( 'Editor Controls', 'wp-control-deck' ); ?></h2>
					</div>
					<div class="wp-control-deck-setting-row">
						<div>
							<h3><?php esc_html_e( 'Disable Gutenberg', 'wp-control-deck' ); ?></h3>
							<p><?php esc_html_e( 'Switches WordPress back to the classic editor for all post types unless excluded below.', 'wp-control-deck' ); ?></p>
						</div>
						<label class="wp-control-deck-switch">
							<input type="hidden" name="<?php echo esc_attr( WP_CONTROL_DECK_DISABLE_GUTENBERG_OPTION ); ?>" value="0" />
							<input
								type="checkbox"
								name="<?php echo esc_attr( WP_CONTROL_DECK_DISABLE_GUTENBERG_OPTION ); ?>"
								value="1"
								data-wp-control-deck-toggle="gutenberg-exclusions"
								<?php checked( $gutenberg_disabled ); ?>
							/>
							<span class="wp-control-deck-slider" aria-hidden="true"></span>
						</label>
					</div>
					<div
						class="wp-control-deck-exclusions <?php echo esc_attr( $gutenberg_disabled ? 'is-visible' : '' ); ?>"
						data-wp-control-deck-panel="gutenberg-exclusions"
					>
						<h3><?php esc_html_e( 'Exclude Post Types', 'wp-control-deck' ); ?></h3>
						<p><?php esc_html_e( 'Selected post types will keep Gutenberg enabled.', 'wp-control-deck' ); ?></p>
						<input type="hidden" name="<?php echo esc_attr( WP_CONTROL_DECK_GUTENBERG_EXCLUSIONS_OPTION ); ?>[]" value="" />
						<div class="wp-control-deck-checkbox-grid">
							<?php foreach ( $post_types as $post_type ) : ?>
								<label class="wp-control-deck-checkbox">
									<input
										type="checkbox"
										name="<?php echo esc_attr( WP_CONTROL_DECK_GUTENBERG_EXCLUSIONS_OPTION ); ?>[]"
										value="<?php echo esc_attr( $post_type->name ); ?>"
										<?php checked( in_array( $post_type->name, $gutenberg_exclusions, true ) ); ?>
									/>
									<span><?php echo esc_html( $post_type->labels->singular_name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
					<?php submit_button( __( 'Save Settings', 'wp-control-deck' ), 'primary wp-control-deck-primary-button' ); ?>
				</section>

				<section class="wp-control-deck-card">
					<div class="wp-control-deck-card-header">
						<h2><?php esc_html_e( 'Comment Controls', 'wp-control-deck' ); ?></h2>
					</div>
					<div class="wp-control-deck-setting-row">
						<div>
							<h3><?php esc_html_e( 'Disable Comments Globally', 'wp-control-deck' ); ?></h3>
							<p><?php esc_html_e( 'Removes comment support, closes comments and pings, and hides WordPress comment screens while enabled.', 'wp-control-deck' ); ?></p>
						</div>
						<label class="wp-control-deck-switch">
							<input type="hidden" name="<?php echo esc_attr( WP_CONTROL_DECK_DISABLE_COMMENTS_OPTION ); ?>" value="0" />
							<input
								type="checkbox"
								name="<?php echo esc_attr( WP_CONTROL_DECK_DISABLE_COMMENTS_OPTION ); ?>"
								value="1"
								<?php checked( $comments_disabled ); ?>
							/>
							<span class="wp-control-deck-slider" aria-hidden="true"></span>
						</label>
					</div>
					<?php submit_button( __( 'Save Settings', 'wp-control-deck' ), 'primary wp-control-deck-primary-button' ); ?>
				</section>

				<section class="wp-control-deck-card">
					<div class="wp-control-deck-card-header">
						<h2><?php esc_html_e( 'Admin Bar Hotlinks', 'wp-control-deck' ); ?></h2>
					</div>
					<p><?php esc_html_e( 'Add up to three custom links for quick access from the WordPress admin bar.', 'wp-control-deck' ); ?></p>
					<div class="wp-control-deck-hotlinks">
						<?php for ( $index = 0; $index < 3; $index++ ) : ?>
							<?php
							$hotlink = isset( $admin_bar_hotlinks[ $index ] ) ? $admin_bar_hotlinks[ $index ] : array(
								'url'  => '',
								'text' => '',
							);
							?>
							<div class="wp-control-deck-hotlink-row">
								<label>
									<span><?php esc_html_e( 'Link', 'wp-control-deck' ); ?></span>
									<input
										type="url"
										name="<?php echo esc_attr( WP_CONTROL_DECK_ADMIN_BAR_HOTLINKS_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][url]"
										value="<?php echo esc_url( $hotlink['url'] ); ?>"
										placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>"
									/>
								</label>
								<label>
									<span><?php esc_html_e( 'Link Text', 'wp-control-deck' ); ?></span>
									<input
										type="text"
										name="<?php echo esc_attr( WP_CONTROL_DECK_ADMIN_BAR_HOTLINKS_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][text]"
										value="<?php echo esc_attr( $hotlink['text'] ); ?>"
										placeholder="<?php esc_attr_e( 'Dashboard', 'wp-control-deck' ); ?>"
									/>
								</label>
							</div>
						<?php endfor; ?>
					</div>
					<?php submit_button( __( 'Save Settings', 'wp-control-deck' ), 'primary wp-control-deck-primary-button' ); ?>
				</section>
			</form>

			<section class="wp-control-deck-card wp-control-deck-danger-card">
				<div class="wp-control-deck-card-header">
					<h2><?php esc_html_e( 'Delete Existing Comments', 'wp-control-deck' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Permanently delete all existing WordPress comments. This cannot be undone.', 'wp-control-deck' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="wp_control_deck_delete_comments" />
					<?php wp_nonce_field( 'wp_control_deck_delete_comments' ); ?>
					<?php
					submit_button(
						__( 'Delete Existing Comments', 'wp-control-deck' ),
						'delete wp-control-deck-delete-button',
						'submit',
						false,
						array( 'onclick' => sprintf( 'return confirm(%s);', wp_json_encode( $delete_confirm ) ) )
					);
					?>
				</form>
			</section>
		</div>
	</div>

	<style>
		.wp-control-deck-page {
			max-width: 980px;
		}

		.wp-control-deck-header {
			align-items: center;
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 8px;
			box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
			display: flex;
			justify-content: space-between;
			margin: 24px 0 18px;
			padding: 22px 24px;
		}

		.wp-control-deck-header h1 {
			color: #1d2327;
			font-size: 26px;
			font-weight: 600;
			line-height: 1.2;
			margin: 0;
		}

		.wp-control-deck-grid {
			display: grid;
			gap: 16px;
			grid-template-columns: minmax(0, 1fr);
		}

		.wp-control-deck-settings-form {
			display: grid;
			gap: 16px;
		}

		.wp-control-deck-card {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 8px;
			box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
			padding: 22px 24px;
		}

		.wp-control-deck-card-header {
			border-bottom: 1px solid #f0f0f1;
			margin: -2px 0 18px;
			padding-bottom: 14px;
		}

		.wp-control-deck-card h2,
		.wp-control-deck-card h3 {
			color: #1d2327;
			margin: 0;
		}

		.wp-control-deck-card h2 {
			font-size: 18px;
			line-height: 1.3;
		}

		.wp-control-deck-card h3 {
			font-size: 15px;
			line-height: 1.4;
		}

		.wp-control-deck-card p {
			color: #50575e;
			font-size: 13px;
			line-height: 1.55;
			margin: 8px 0 0;
			max-width: 620px;
		}

		.wp-control-deck-setting-row {
			align-items: center;
			display: flex;
			gap: 24px;
			justify-content: space-between;
		}

		.wp-control-deck-card .submit {
			margin: 20px 0 0;
			padding: 0;
		}

		.wp-control-deck-exclusions {
			background: #f6f7f7;
			border: 1px solid #e5e5e5;
			border-radius: 8px;
			display: none;
			margin-top: 18px;
			padding: 16px;
		}

		.wp-control-deck-exclusions.is-visible {
			display: block;
		}

		.wp-control-deck-checkbox-grid {
			display: grid;
			gap: 10px;
			grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
			margin-top: 14px;
		}

		.wp-control-deck-checkbox {
			align-items: center;
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 6px;
			display: flex;
			gap: 8px;
			min-height: 38px;
			padding: 8px 10px;
		}

		.wp-control-deck-checkbox span {
			color: #1d2327;
			font-size: 13px;
			font-weight: 500;
			line-height: 1.3;
		}

		.wp-control-deck-hotlinks {
			display: grid;
			gap: 14px;
			margin-top: 16px;
		}

		.wp-control-deck-hotlink-row {
			background: #f6f7f7;
			border: 1px solid #e5e5e5;
			border-radius: 8px;
			display: grid;
			gap: 12px;
			grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
			padding: 14px;
		}

		.wp-control-deck-hotlink-row label {
			display: grid;
			gap: 6px;
		}

		.wp-control-deck-hotlink-row span {
			color: #1d2327;
			font-size: 13px;
			font-weight: 600;
			line-height: 1.3;
		}

		.wp-control-deck-hotlink-row input {
			border: 1px solid #8c8f94;
			border-radius: 6px;
			min-height: 36px;
			width: 100%;
		}

		.wp-control-deck-primary-button,
		.wp-control-deck-delete-button {
			border-radius: 6px !important;
			min-height: 36px;
			padding-left: 16px !important;
			padding-right: 16px !important;
		}

		.wp-control-deck-danger-card {
			border-color: #f0c2c2;
		}

		.wp-control-deck-switch {
			display: inline-block;
			flex: 0 0 auto;
			height: 24px;
			position: relative;
			width: 44px;
		}

		.wp-control-deck-switch input {
			height: 0;
			opacity: 0;
			width: 0;
		}

		.wp-control-deck-slider {
			background-color: #8c8f94;
			border-radius: 999px;
			bottom: 0;
			cursor: pointer;
			left: 0;
			position: absolute;
			right: 0;
			top: 0;
			transition: .15s;
		}

		.wp-control-deck-slider::before {
			background-color: #fff;
			border-radius: 50%;
			bottom: 3px;
			content: "";
			height: 18px;
			left: 3px;
			position: absolute;
			transition: .15s;
			width: 18px;
		}

		.wp-control-deck-switch input:checked + .wp-control-deck-slider {
			background-color: #2271b1;
		}

		.wp-control-deck-switch input:focus + .wp-control-deck-slider {
			box-shadow: 0 0 0 2px #fff, 0 0 0 4px #2271b1;
		}

		.wp-control-deck-switch input:checked + .wp-control-deck-slider::before {
			transform: translateX(20px);
		}

		@media (max-width: 782px) {
			.wp-control-deck-header,
			.wp-control-deck-card {
				padding: 18px;
			}

			.wp-control-deck-setting-row {
				align-items: flex-start;
				flex-direction: column;
				gap: 14px;
			}

			.wp-control-deck-hotlink-row {
				grid-template-columns: minmax(0, 1fr);
			}
		}
	</style>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var toggle = document.querySelector('[data-wp-control-deck-toggle="gutenberg-exclusions"]');
			var panel = document.querySelector('[data-wp-control-deck-panel="gutenberg-exclusions"]');

			if (!toggle || !panel) {
				return;
			}

			function updatePanel() {
				panel.classList.toggle('is-visible', toggle.checked);
			}

			toggle.addEventListener('change', updatePanel);
			updatePanel();
		});
	</script>
	<?php
}

/**
 * Sanitizes Gutenberg post type exclusions.
 *
 * @param array|string $post_types Selected post type names.
 * @return array
 */
function wp_control_deck_sanitize_post_type_exclusions( $post_types ) {
	if ( ! is_array( $post_types ) ) {
		$post_types = array( $post_types );
	}

	$allowed_post_types = wp_list_pluck( wp_control_deck_get_editor_post_types(), 'name' );
	$post_types         = array_map( 'sanitize_key', $post_types );
	$post_types         = array_filter( $post_types );

	return array_values( array_intersect( $post_types, $allowed_post_types ) );
}

/**
 * Sanitizes admin bar hotlinks.
 *
 * @param array $hotlinks Submitted hotlink rows.
 * @return array
 */
function wp_control_deck_sanitize_admin_bar_hotlinks( $hotlinks ) {
	if ( ! is_array( $hotlinks ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( array_slice( $hotlinks, 0, 3 ) as $hotlink ) {
		if ( ! is_array( $hotlink ) ) {
			continue;
		}

		$url  = isset( $hotlink['url'] ) ? esc_url_raw( $hotlink['url'] ) : '';
		$text = isset( $hotlink['text'] ) ? sanitize_text_field( $hotlink['text'] ) : '';

		if ( '' === $url || '' === $text ) {
			continue;
		}

		$sanitized[] = array(
			'url'  => $url,
			'text' => $text,
		);
	}

	return $sanitized;
}

/**
 * Gets post types that can reasonably use an editor screen.
 *
 * @return WP_Post_Type[]
 */
function wp_control_deck_get_editor_post_types() {
	$post_types = get_post_types(
		array(
			'show_ui' => true,
		),
		'objects'
	);

	return array_filter(
		$post_types,
		function ( $post_type ) {
			return post_type_supports( $post_type->name, 'editor' );
		}
	);
}

/**
 * Checks whether global comments are disabled.
 *
 * @return bool
 */
function wp_control_deck_comments_are_disabled() {
	return (bool) get_option( WP_CONTROL_DECK_DISABLE_COMMENTS_OPTION, false );
}

/**
 * Checks whether Gutenberg is disabled.
 *
 * @return bool
 */
function wp_control_deck_gutenberg_is_disabled() {
	return (bool) get_option( WP_CONTROL_DECK_DISABLE_GUTENBERG_OPTION, false );
}

/**
 * Gets post types that should keep Gutenberg enabled.
 *
 * @return array
 */
function wp_control_deck_get_gutenberg_exclusions() {
	$exclusions = get_option( WP_CONTROL_DECK_GUTENBERG_EXCLUSIONS_OPTION, array() );

	if ( ! is_array( $exclusions ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'sanitize_key', $exclusions ) ) );
}

/**
 * Gets configured admin bar hotlinks.
 *
 * @return array
 */
function wp_control_deck_get_admin_bar_hotlinks() {
	$hotlinks = get_option( WP_CONTROL_DECK_ADMIN_BAR_HOTLINKS_OPTION, array() );

	return wp_control_deck_sanitize_admin_bar_hotlinks( $hotlinks );
}

/**
 * Adds configured hotlinks to the WordPress admin bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function wp_control_deck_add_admin_bar_hotlinks( $wp_admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	foreach ( wp_control_deck_get_admin_bar_hotlinks() as $index => $hotlink ) {
		$wp_admin_bar->add_node(
			array(
				'id'    => 'wp-control-deck-hotlink-' . ( $index + 1 ),
				'title' => esc_html( $hotlink['text'] ),
				'href'  => esc_url( $hotlink['url'] ),
				'meta'  => array(
					'title' => esc_attr( $hotlink['text'] ),
				),
			)
		);
	}
}

/**
 * Switches WordPress to the classic editor unless the post type is excluded.
 */
function wp_control_deck_disable_gutenberg() {
	add_filter( 'use_block_editor_for_post_type', 'wp_control_deck_filter_block_editor_for_post_type', 20, 2 );
	add_filter( 'use_block_editor_for_post', 'wp_control_deck_filter_block_editor_for_post', 20, 2 );
}

/**
 * Disables the block editor for non-excluded post types.
 *
 * @param bool   $use_block_editor Whether the block editor should be used.
 * @param string $post_type Post type name.
 * @return bool
 */
function wp_control_deck_filter_block_editor_for_post_type( $use_block_editor, $post_type ) {
	if ( in_array( $post_type, wp_control_deck_get_gutenberg_exclusions(), true ) ) {
		return $use_block_editor;
	}

	return false;
}

/**
 * Disables the block editor for non-excluded posts.
 *
 * @param bool    $use_block_editor Whether the block editor should be used.
 * @param WP_Post $post Post object.
 * @return bool
 */
function wp_control_deck_filter_block_editor_for_post( $use_block_editor, $post ) {
	if ( $post instanceof WP_Post && in_array( $post->post_type, wp_control_deck_get_gutenberg_exclusions(), true ) ) {
		return $use_block_editor;
	}

	return false;
}

/**
 * Disables WordPress comments globally.
 */
function wp_control_deck_disable_comments() {
	add_action( 'admin_init', 'wp_control_deck_remove_comment_support' );
	add_action( 'admin_init', 'wp_control_deck_redirect_comment_admin_pages' );
	add_action( 'init', 'wp_control_deck_remove_comment_support', 100 );
	add_action( 'admin_menu', 'wp_control_deck_remove_comment_admin_menu', 999 );
	add_action( 'wp_dashboard_setup', 'wp_control_deck_remove_dashboard_comment_widgets' );
	add_action( 'wp_before_admin_bar_render', 'wp_control_deck_remove_comment_admin_bar_menu' );
	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open', '__return_false', 20 );
	add_filter( 'comments_array', '__return_empty_array', 20 );
	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	add_filter( 'wp_headers', 'wp_control_deck_remove_comment_headers' );
	add_action( 'template_redirect', 'wp_control_deck_disable_comment_feeds' );
}

/**
 * Removes comment and trackback support from all post types.
 */
function wp_control_deck_remove_comment_support() {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
		}

		if ( post_type_supports( $post_type, 'trackbacks' ) ) {
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}

/**
 * Removes comments from the admin menu.
 */
function wp_control_deck_remove_comment_admin_menu() {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}

/**
 * Redirects direct requests to comment admin pages.
 */
function wp_control_deck_redirect_comment_admin_pages() {
	global $pagenow;

	if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'options-discussion.php' ), true ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wp-control-deck' ) );
		exit;
	}
}

/**
 * Removes dashboard widgets related to comments.
 */
function wp_control_deck_remove_dashboard_comment_widgets() {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}

/**
 * Removes comments from the admin bar.
 */
function wp_control_deck_remove_comment_admin_bar_menu() {
	global $wp_admin_bar;

	if ( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'comments' );
	}
}

/**
 * Removes comment-related HTTP headers.
 *
 * @param array $headers HTTP headers.
 * @return array
 */
function wp_control_deck_remove_comment_headers( $headers ) {
	unset( $headers['X-Pingback'] );

	return $headers;
}

/**
 * Blocks comment feeds when comments are disabled.
 */
function wp_control_deck_disable_comment_feeds() {
	if ( is_comment_feed() ) {
		wp_die(
			esc_html__( 'Comments are disabled.', 'wp-control-deck' ),
			esc_html__( 'Comments Disabled', 'wp-control-deck' ),
			array( 'response' => 404 )
		);
	}
}

/**
 * Handles the delete existing comments action.
 */
function wp_control_deck_handle_delete_comments() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to delete comments.', 'wp-control-deck' ) );
	}

	check_admin_referer( 'wp_control_deck_delete_comments' );

	$deleted_count = wp_control_deck_delete_all_comments();
	$redirect_url  = add_query_arg(
		array(
			'page'                             => 'wp-control-deck',
			'wp_control_deck_deleted_comments' => $deleted_count,
		),
		admin_url( 'admin.php' )
	);

	wp_safe_redirect( $redirect_url );
	exit;
}

/**
 * Deletes all existing WordPress comments.
 *
 * @return int
 */
function wp_control_deck_delete_all_comments() {
	$deleted_count = 0;

	do {
		$comment_ids = get_comments(
			array(
				'fields' => 'ids',
				'number' => 100,
				'status' => 'all',
			)
		);

		foreach ( $comment_ids as $comment_id ) {
			if ( wp_delete_comment( $comment_id, true ) ) {
				++$deleted_count;
			}
		}
	} while ( ! empty( $comment_ids ) );

	return $deleted_count;
}
