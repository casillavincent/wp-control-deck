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

	if ( wp_control_deck_comments_are_disabled() ) {
		wp_control_deck_disable_comments();
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
}

/**
 * Renders the WP Control Deck admin page.
 */
function wp_control_deck_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$comments_disabled = wp_control_deck_comments_are_disabled();
	$deleted_count     = isset( $_GET['wp_control_deck_deleted_comments'] ) ? absint( $_GET['wp_control_deck_deleted_comments'] ) : null;
	$delete_confirm    = __( 'Are you sure you want to permanently delete all existing comments? This cannot be undone.', 'wp-control-deck' );
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
			<section class="wp-control-deck-card">
				<div class="wp-control-deck-card-header">
					<h2><?php esc_html_e( 'Comment Controls', 'wp-control-deck' ); ?></h2>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'wp_control_deck_settings' ); ?>
					<div class="wp-control-deck-setting-row">
						<div>
							<h3><?php esc_html_e( 'Disable Comments Globally', 'wp-control-deck' ); ?></h3>
							<p><?php esc_html_e( 'Removes comment support, closes comments and pings, and hides WordPress comment screens while enabled.', 'wp-control-deck' ); ?></p>
						</div>
						<label class="wp-control-deck-switch">
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
				</form>
			</section>

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
		}
	</style>
	<?php
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
