<?php
/**
 * Plugin Name: WP Control Deck
 * Description: A lightweight extension that enhances WordPress and helps reduce friction during development.
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
define( 'WP_CONTROL_DECK_TOGGLE_PAGE_INFO_OPTION', 'wp_control_deck_toggle_page_info' );
define( 'WP_CONTROL_DECK_PAGE_INFO_STYLE_OPTION', 'wp_control_deck_page_info_style' );
define( 'WP_CONTROL_DECK_SEO_CONSOLE_OPTION', 'wp_control_deck_seo_console_enabled' );
define( 'WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION', 'wp_control_deck_seo_console_fallbacks' );

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
	add_action( 'admin_enqueue_scripts', 'wp_control_deck_enqueue_admin_assets' );

	if ( wp_control_deck_comments_are_disabled() ) {
		wp_control_deck_disable_comments();
	}

	if ( wp_control_deck_gutenberg_is_disabled() ) {
		wp_control_deck_disable_gutenberg();
	}

	if ( wp_control_deck_page_info_is_enabled() ) {
		wp_control_deck_enable_page_info();
	}

	if ( wp_control_deck_seo_console_is_enabled() ) {
		wp_control_deck_enable_seo_console();
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
 * Enqueues shared WP Control Deck admin assets.
 *
 * @param string $hook_suffix Current admin hook.
 */
function wp_control_deck_enqueue_admin_assets( $hook_suffix ) {
	if ( 'toplevel_page_wp-control-deck' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style(
		'wpseo-console-admin',
		WP_CONTROL_DECK_URL . 'assets/wpseo-console-admin.css',
		array(),
		WP_CONTROL_DECK_VERSION
	);
	wp_enqueue_script(
		'wpseo-console-admin',
		WP_CONTROL_DECK_URL . 'assets/wpseo-console-admin.js',
		array(),
		WP_CONTROL_DECK_VERSION,
		true
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

	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_TOGGLE_PAGE_INFO_OPTION,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'wp_validate_boolean',
			'default'           => false,
		)
	);

	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_PAGE_INFO_STYLE_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_control_deck_sanitize_page_info_style',
			'default'           => 'default',
		)
	);

	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_SEO_CONSOLE_OPTION,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'wp_validate_boolean',
			'default'           => false,
		)
	);

	register_setting(
		'wp_control_deck_settings',
		WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wp_control_deck_sanitize_seo_console_fallbacks',
			'default'           => array(),
		)
	);
}

/**
 * Gets one-time notices for the WP Control Deck dashboard.
 *
 * @return array
 */
function wp_control_deck_get_admin_page_notices() {
	$notices       = array();
	$transient_key = 'wp_control_deck_deleted_comments_' . get_current_user_id();
	$deleted_count = get_transient( $transient_key );

	if ( false !== $deleted_count ) {
		delete_transient( $transient_key );

		$notices[] = array(
			'type'    => 'success',
			'message' => sprintf(
				/* translators: %d: number of deleted comments. */
				_n( '%d comment deleted.', '%d comments deleted.', absint( $deleted_count ), 'wp-control-deck' ),
				absint( $deleted_count )
			),
		);
	}

	return $notices;
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
	$page_info_enabled    = wp_control_deck_page_info_is_enabled();
	$page_info_style      = wp_control_deck_get_page_info_style();
	$seo_console_enabled  = wp_control_deck_seo_console_is_enabled();
	$seo_fallbacks        = wp_control_deck_get_seo_console_fallbacks();
	$post_types           = wp_control_deck_get_editor_post_types();
	$notices              = wp_control_deck_get_admin_page_notices();
	$delete_confirm       = __( 'Are you sure you want to permanently delete all existing comments? This cannot be undone.', 'wp-control-deck' );
	?>
	<div class="wrap wp-control-deck-page">
		<div class="wp-control-deck-hero">
			<?php if ( ! empty( $notices ) ) : ?>
				<div class="wp-control-deck-notices">
					<?php foreach ( $notices as $notice ) : ?>
						<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
							<p><?php echo esc_html( $notice['message'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div>
				<span class="wp-control-deck-kicker"><?php esc_html_e( 'Control Console', 'wp-control-deck' ); ?></span>
				<p><?php esc_html_e( 'CONTROL_DECK', 'wp-control-deck' ); ?></p>
			</div>
			<div class="wp-control-deck-console-count">
				<span><?php esc_html_e( 'Modules', 'wp-control-deck' ); ?></span>
				<strong>06</strong>
			</div>
		</div>

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

				<section class="wp-control-deck-card">
					<div class="wp-control-deck-card-header">
						<h2><?php esc_html_e( 'Development Tools', 'wp-control-deck' ); ?></h2>
					</div>
					<div class="wp-control-deck-setting-row">
						<div>
							<h3><?php esc_html_e( 'Toggle Page Info', 'wp-control-deck' ); ?></h3>
							<p><?php esc_html_e( 'Shows a frontend diagnostics box with load speed, page size, image totals, meta title, and meta description.', 'wp-control-deck' ); ?></p>
							<p class="wp-control-deck-warning"><?php esc_html_e( 'Only use on a development environment.', 'wp-control-deck' ); ?></p>
						</div>
						<label class="wp-control-deck-switch">
							<input type="hidden" name="<?php echo esc_attr( WP_CONTROL_DECK_TOGGLE_PAGE_INFO_OPTION ); ?>" value="0" />
							<input
								type="checkbox"
								name="<?php echo esc_attr( WP_CONTROL_DECK_TOGGLE_PAGE_INFO_OPTION ); ?>"
								value="1"
								<?php checked( $page_info_enabled ); ?>
							/>
							<span class="wp-control-deck-slider" aria-hidden="true"></span>
						</label>
					</div>
					<div class="wp-control-deck-field-row">
						<label for="wp-control-deck-page-info-style"><?php esc_html_e( 'Style', 'wp-control-deck' ); ?></label>
						<select
							id="wp-control-deck-page-info-style"
							name="<?php echo esc_attr( WP_CONTROL_DECK_PAGE_INFO_STYLE_OPTION ); ?>"
						>
							<option value="default" <?php selected( $page_info_style, 'default' ); ?>><?php esc_html_e( 'Default', 'wp-control-deck' ); ?></option>
							<option value="glass" <?php selected( $page_info_style, 'glass' ); ?>><?php esc_html_e( 'Glass', 'wp-control-deck' ); ?></option>
						</select>
					</div>
					<?php submit_button( __( 'Save Settings', 'wp-control-deck' ), 'primary wp-control-deck-primary-button' ); ?>
				</section>

				<section class="wp-control-deck-card">
					<div class="wp-control-deck-card-header">
						<h2><?php esc_html_e( 'SEO Console', 'wp-control-deck' ); ?></h2>
					</div>
					<div class="wp-control-deck-setting-row">
						<div>
							<h3><?php esc_html_e( 'Enable WP SEO Console', 'wp-control-deck' ); ?></h3>
							<p><?php esc_html_e( 'Adds lightweight SEO fields to posts and pages, then outputs document title, description, robots, canonical, Open Graph, and Twitter tags from saved post meta.', 'wp-control-deck' ); ?></p>
						</div>
						<label class="wp-control-deck-switch">
							<input type="hidden" name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_OPTION ); ?>" value="0" />
							<input
								type="checkbox"
								name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_OPTION ); ?>"
								value="1"
								<?php checked( $seo_console_enabled ); ?>
							/>
							<span class="wp-control-deck-slider" aria-hidden="true"></span>
						</label>
					</div>
					<div class="wp-control-deck-fallback-fields">
						<h3><?php esc_html_e( 'Global Fallback Meta', 'wp-control-deck' ); ?></h3>
						<p><?php esc_html_e( 'Used only when an individual post or page has no SEO Console meta saved.', 'wp-control-deck' ); ?></p>
						<div class="wp-control-deck-field-stack">
							<label>
								<span><?php esc_html_e( 'Fallback SEO Title', 'wp-control-deck' ); ?></span>
								<input
									type="text"
									name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION ); ?>[_wpseo_console_title]"
									value="<?php echo esc_attr( $seo_fallbacks['_wpseo_console_title'] ); ?>"
								/>
							</label>
							<label>
								<span><?php esc_html_e( 'Fallback Meta Description', 'wp-control-deck' ); ?></span>
								<textarea
									name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION ); ?>[_wpseo_console_description]"
									rows="3"
								><?php echo esc_textarea( $seo_fallbacks['_wpseo_console_description'] ); ?></textarea>
							</label>
							<label>
								<span><?php esc_html_e( 'Fallback Robots Indexing', 'wp-control-deck' ); ?></span>
								<select name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION ); ?>[_wpseo_console_robots]">
									<?php foreach ( wpseo_console_get_field_definitions()['_wpseo_console_robots']['options'] as $option_value => $option_label ) : ?>
										<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $seo_fallbacks['_wpseo_console_robots'], $option_value ); ?>>
											<?php echo esc_html( $option_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>
							<label>
								<span><?php esc_html_e( 'Fallback Open Graph Title', 'wp-control-deck' ); ?></span>
								<input
									type="text"
									name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION ); ?>[_wpseo_console_og_title]"
									value="<?php echo esc_attr( $seo_fallbacks['_wpseo_console_og_title'] ); ?>"
								/>
							</label>
							<label>
								<span><?php esc_html_e( 'Fallback Open Graph Description', 'wp-control-deck' ); ?></span>
								<textarea
									name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION ); ?>[_wpseo_console_og_description]"
									rows="3"
								><?php echo esc_textarea( $seo_fallbacks['_wpseo_console_og_description'] ); ?></textarea>
							</label>
							<label>
								<span><?php esc_html_e( 'Fallback Open Graph Image', 'wp-control-deck' ); ?></span>
								<div class="wpseo-console-image-select" data-wpseo-console-image-select>
									<input
										type="hidden"
										name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION ); ?>[_wpseo_console_og_image]"
										value="<?php echo esc_url( $seo_fallbacks['_wpseo_console_og_image'] ); ?>"
										data-wpseo-console-image-input
									/>
									<div class="wpseo-console-image-preview" data-wpseo-console-image-preview>
										<?php if ( $seo_fallbacks['_wpseo_console_og_image'] ) : ?>
											<img src="<?php echo esc_url( $seo_fallbacks['_wpseo_console_og_image'] ); ?>" alt="" />
										<?php endif; ?>
									</div>
									<button type="button" class="button" data-wpseo-console-image-button><?php esc_html_e( 'Select Image', 'wp-control-deck' ); ?></button>
									<button type="button" class="button" data-wpseo-console-image-remove><?php esc_html_e( 'Remove', 'wp-control-deck' ); ?></button>
								</div>
							</label>
							<label>
								<span><?php esc_html_e( 'Fallback Twitter Card Type', 'wp-control-deck' ); ?></span>
								<select name="<?php echo esc_attr( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION ); ?>[_wpseo_console_twitter_card]">
									<?php foreach ( wpseo_console_get_field_definitions()['_wpseo_console_twitter_card']['options'] as $option_value => $option_label ) : ?>
										<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $seo_fallbacks['_wpseo_console_twitter_card'], $option_value ); ?>>
											<?php echo esc_html( $option_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>
						</div>
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
						true,
						array( 'onclick' => sprintf( 'return confirm(%s);', wp_json_encode( $delete_confirm ) ) )
					);
					?>
				</form>
			</section>
		</div>
	</div>

	<style>
		.wp-control-deck-page {
			color: #101010;
			font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
			max-width: 1180px;
		}

		.wp-control-deck-kicker,
		.wp-control-deck-console-count span,
		.wp-control-deck-card-header h2,
		.wp-control-deck-field-row label,
		.wp-control-deck-hotlink-row span,
		.wp-control-deck-field-stack span {
			font-family: ui-monospace, "SFMono-Regular", Consolas, monospace;
			letter-spacing: 0;
			text-transform: uppercase;
		}

		.wp-control-deck-console-count {
			align-items: flex-end;
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.wp-control-deck-console-count span {
			color: #5f5f59;
			font-size: 10px;
		}

		.wp-control-deck-console-count strong {
			color: #101010;
			font-family: ui-monospace, "SFMono-Regular", Consolas, monospace;
			font-size: 16px;
			line-height: 1;
		}

		.wp-control-deck-hero {
			align-items: end;
			background:
				linear-gradient(to right, rgba(16, 16, 16, .08) 1px, transparent 1px),
				linear-gradient(to bottom, rgba(16, 16, 16, .08) 1px, transparent 1px),
				#efefed;
			background-size: 25% 100%, 100% 72px;
			border: 1px solid #c9c9c4;
			border-radius: 10px;
			display: grid;
			gap: 24px;
			grid-template-columns: minmax(0, 1fr) auto;
			margin: 24px 0 16px;
			min-height: 190px;
			padding: 30px 34px;
			position: relative;
		}

		.wp-control-deck-notices {
			position: absolute;
			right: 18px;
			top: 18px;
			width: min(420px, calc(100% - 36px));
			z-index: 2;
		}

		.wp-control-deck-notices .notice {
			margin: 0 0 8px;
		}

		.wp-control-deck-kicker {
			background: #101010;
			color: #fff;
			display: inline-block;
			font-size: 11px;
			line-height: 1;
			margin-bottom: 16px;
			padding: 6px 8px;
		}

		.wp-control-deck-hero p {
			color: #101010;
			font-size: clamp(28px, 5vw, 64px);
			font-weight: 800;
			line-height: .95;
			margin: 0;
			max-width: 850px;
		}

		.wp-control-deck-grid {
			display: grid;
			gap: 14px;
			grid-template-columns: minmax(0, 1fr);
		}

		.wp-control-deck-settings-form {
			display: grid;
			gap: 14px;
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.wp-control-deck-card {
			background: #f7f7f5;
			border: 1px solid #c9c9c4;
			border-radius: 10px;
			box-shadow: none;
			overflow: hidden;
			padding: 0;
		}

		.wp-control-deck-settings-form .wp-control-deck-card:nth-of-type(5) {
			grid-column: 1 / -1;
		}

		.wp-control-deck-card-header {
			align-items: center;
			background: #efefed;
			border-bottom: 1px solid #c9c9c4;
			display: flex;
			justify-content: space-between;
			margin: 0;
			min-height: 42px;
			padding: 0 14px;
		}

		.wp-control-deck-card-header h2 {
			color: #101010;
			font-size: 11px;
			font-weight: 500;
			margin: 0;
		}

		.wp-control-deck-card h2,
		.wp-control-deck-card h3 {
			color: #101010;
			font-weight: 700;
			margin: 0;
		}

		.wp-control-deck-setting-row h3 {
			font-size: 22px;
			line-height: 1.15;
		}

		.wp-control-deck-card p {
			color: #4c4c46;
			font-size: 13px;
			line-height: 1.55;
			margin: 8px 0 0;
			max-width: 620px;
		}

		.wp-control-deck-card .wp-control-deck-warning {
			color: #a53a16;
			font-weight: 600;
		}

		.wp-control-deck-card > p {
			margin: 0;
			padding: 20px 20px 0;
		}

		.wp-control-deck-setting-row {
			align-items: center;
			display: flex;
			gap: 24px;
			justify-content: space-between;
			padding: 20px;
		}

		.wp-control-deck-field-row {
			align-items: center;
			background: #efefed;
			border: 1px solid #d8d8d4;
			border-radius: 8px;
			display: flex;
			gap: 16px;
			justify-content: space-between;
			margin: 0 20px 20px;
			padding: 14px;
		}

		.wp-control-deck-field-row label {
			color: #101010;
			font-size: 11px;
			font-weight: 500;
			line-height: 1.3;
		}

		.wp-control-deck-field-row select,
		.wp-control-deck-hotlink-row input,
		.wp-control-deck-field-stack input,
		.wp-control-deck-field-stack textarea,
		.wp-control-deck-field-stack select {
			background-color: #fff;
			border: 1px solid #b7b7b0;
			border-radius: 6px;
			min-height: 36px;
		}

		.wp-control-deck-field-row select {
			min-width: 180px;
		}

		.wp-control-deck-card .submit {
			border-top: 1px solid #d8d8d4;
			margin: 0;
			padding: 14px 20px;
		}

		.wp-control-deck-exclusions {
			background: #efefed;
			border: 1px solid #d8d8d4;
			border-radius: 8px;
			display: none;
			margin: 0 20px 20px;
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
			border: 1px solid #c9c9c4;
			border-radius: 6px;
			display: flex;
			gap: 8px;
			min-height: 38px;
			padding: 8px 10px;
		}

		.wp-control-deck-checkbox span {
			color: #101010;
			font-size: 13px;
			font-weight: 500;
			line-height: 1.3;
		}

		.wp-control-deck-hotlinks {
			display: grid;
			gap: 14px;
			padding: 20px;
		}

		.wp-control-deck-hotlink-row {
			background: #efefed;
			border: 1px solid #d8d8d4;
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
			color: #101010;
			font-size: 11px;
			font-weight: 500;
			line-height: 1.3;
		}

		.wp-control-deck-hotlink-row input {
			width: 100%;
		}

		.wp-control-deck-fallback-fields {
			background: #efefed;
			border: 1px solid #d8d8d4;
			border-radius: 8px;
			margin: 0 20px 20px;
			padding: 16px;
		}

		.wp-control-deck-field-stack {
			display: grid;
			gap: 14px;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			margin-top: 16px;
		}

		.wp-control-deck-field-stack label {
			display: grid;
			gap: 6px;
		}

		.wp-control-deck-field-stack label:has(textarea),
		.wp-control-deck-field-stack label:has(.wpseo-console-image-select) {
			grid-column: 1 / -1;
		}

		.wp-control-deck-field-stack input,
		.wp-control-deck-field-stack textarea,
		.wp-control-deck-field-stack select {
			width: 100%;
		}

		.wpseo-console-image-select {
			align-items: center;
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
		}

		.wpseo-console-image-preview {
			background: #fff;
			border: 1px dashed #b7b7b0;
			border-radius: 6px;
			min-height: 72px;
			width: 100%;
		}

		.wpseo-console-image-preview img {
			border-radius: 6px;
			display: block;
			height: auto;
			max-width: 180px;
		}

		.wpseo-console-image-preview:has(img) {
			background: transparent;
			border: 0;
			min-height: 0;
			width: auto;
		}

		.wp-control-deck-primary-button,
		.wp-control-deck-delete-button {
			border-radius: 6px !important;
			font-family: ui-monospace, "SFMono-Regular", Consolas, monospace !important;
			font-size: 11px !important;
			font-weight: 700 !important;
			min-height: 36px;
			padding-left: 16px !important;
			padding-right: 16px !important;
			text-transform: uppercase;
		}

		.wp-control-deck-primary-button {
			background: #101010 !important;
			border-color: #101010 !important;
		}

		.wp-control-deck-delete-button {
			background: #b32d2e !important;
			border-color: #b32d2e !important;
			color: #fff !important;
		}

		.wp-control-deck-danger-card {
			border-color: #e4a891;
			grid-column: 1 / -1;
		}

		.wp-control-deck-danger-card .wp-control-deck-card-header {
			background: #f4ebe7;
			border-bottom-color: #e4a891;
		}

		.wp-control-deck-switch {
			display: inline-block;
			flex: 0 0 auto;
			height: 28px;
			position: relative;
			width: 52px;
		}

		.wp-control-deck-switch input {
			height: 0;
			opacity: 0;
			width: 0;
		}

		.wp-control-deck-slider {
			background-color: #b7b7b0;
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
			bottom: 4px;
			content: "";
			height: 20px;
			left: 4px;
			position: absolute;
			transition: .15s;
			width: 20px;
		}

		.wp-control-deck-switch input:checked + .wp-control-deck-slider {
			background-color: #ff6a2a;
		}

		.wp-control-deck-switch input:focus + .wp-control-deck-slider {
			box-shadow: 0 0 0 2px #fff, 0 0 0 4px #ff6a2a;
		}

		.wp-control-deck-switch input:checked + .wp-control-deck-slider::before {
			transform: translateX(24px);
		}

		@media (max-width: 782px) {
			.wp-control-deck-settings-form,
			.wp-control-deck-hero {
				grid-template-columns: minmax(0, 1fr);
			}

			.wp-control-deck-card {
				border-radius: 8px;
			}

			.wp-control-deck-hero {
				min-height: 0;
				padding: 22px;
			}

			.wp-control-deck-notices {
				margin-bottom: 18px;
				position: static;
				width: 100%;
			}

			.wp-control-deck-hero p {
				font-size: 32px;
			}

			.wp-control-deck-setting-row {
				align-items: flex-start;
				flex-direction: column;
				gap: 14px;
			}

			.wp-control-deck-field-row {
				align-items: flex-start;
				flex-direction: column;
				gap: 8px;
			}

			.wp-control-deck-hotlink-row {
				grid-template-columns: minmax(0, 1fr);
			}

			.wp-control-deck-field-stack {
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
 * Sanitizes the page info display style.
 *
 * @param string $style Selected style.
 * @return string
 */
function wp_control_deck_sanitize_page_info_style( $style ) {
	$style = sanitize_key( $style );

	if ( in_array( $style, array( 'default', 'glass' ), true ) ) {
		return $style;
	}

	return 'default';
}

/**
 * Sanitizes global SEO Console fallback values.
 *
 * @param array $fallbacks Submitted fallback values.
 * @return array
 */
function wp_control_deck_sanitize_seo_console_fallbacks( $fallbacks ) {
	if ( ! is_array( $fallbacks ) ) {
		return array();
	}

	$allowed_keys = array(
		'_wpseo_console_title',
		'_wpseo_console_description',
		'_wpseo_console_robots',
		'_wpseo_console_og_title',
		'_wpseo_console_og_description',
		'_wpseo_console_og_image',
		'_wpseo_console_twitter_card',
	);
	$sanitized    = array();
	$definitions  = wpseo_console_get_field_definitions();

	foreach ( $allowed_keys as $key ) {
		if ( ! isset( $definitions[ $key ] ) ) {
			continue;
		}

		$value = isset( $fallbacks[ $key ] ) ? $fallbacks[ $key ] : '';
		$value = wpseo_console_sanitize_field( $value, $definitions[ $key ] );

		if ( '' === $value || ( isset( $definitions[ $key ]['default'] ) && $value === $definitions[ $key ]['default'] ) ) {
			continue;
		}

		$sanitized[ $key ] = $value;
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
 * Checks whether frontend page info diagnostics are enabled.
 *
 * @return bool
 */
function wp_control_deck_page_info_is_enabled() {
	return (bool) get_option( WP_CONTROL_DECK_TOGGLE_PAGE_INFO_OPTION, false );
}

/**
 * Checks whether the SEO Console module is enabled.
 *
 * @return bool
 */
function wp_control_deck_seo_console_is_enabled() {
	return (bool) get_option( WP_CONTROL_DECK_SEO_CONSOLE_OPTION, false );
}

/**
 * Gets the selected frontend page info display style.
 *
 * @return string
 */
function wp_control_deck_get_page_info_style() {
	return wp_control_deck_sanitize_page_info_style( get_option( WP_CONTROL_DECK_PAGE_INFO_STYLE_OPTION, 'default' ) );
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
 * Gets global SEO Console fallback values.
 *
 * @return array
 */
function wp_control_deck_get_seo_console_fallbacks() {
	$fallbacks   = get_option( WP_CONTROL_DECK_SEO_CONSOLE_FALLBACKS_OPTION, array() );
	$definitions = wpseo_console_get_field_definitions();
	$values      = array();

	if ( ! is_array( $fallbacks ) ) {
		$fallbacks = array();
	}

	foreach ( $definitions as $key => $field ) {
		if ( '_wpseo_console_canonical' === $key ) {
			continue;
		}

		$value = isset( $fallbacks[ $key ] ) ? $fallbacks[ $key ] : '';

		if ( '' === $value && isset( $field['default'] ) ) {
			$value = $field['default'];
		}

		$values[ $key ] = wpseo_console_sanitize_field( $value, $field );
	}

	return $values;
}

/**
 * Enables the opt-in WP SEO Console module.
 */
function wp_control_deck_enable_seo_console() {
	require_once WP_CONTROL_DECK_PATH . 'includes/class-wpseo-console-sanitizer.php';
	require_once WP_CONTROL_DECK_PATH . 'includes/class-wpseo-console-admin-fields.php';
	require_once WP_CONTROL_DECK_PATH . 'includes/class-wpseo-console-meta-output.php';

	if ( ! class_exists( 'WPSEO_Console_Admin_Fields' ) || ! class_exists( 'WPSEO_Console_Meta_Output' ) ) {
		return;
	}

	$admin_fields = new WPSEO_Console_Admin_Fields();
	$meta_output  = new WPSEO_Console_Meta_Output();

	if ( ! method_exists( $admin_fields, 'register' ) || ! method_exists( $meta_output, 'register' ) ) {
		return;
	}

	$admin_fields->register();
	$meta_output->register();
}

/**
 * Gets post types supported by WP SEO Console.
 *
 * @return array
 */
function wpseo_console_get_supported_post_types() {
	return array( 'post', 'page' );
}

/**
 * Gets the SEO Console field map.
 *
 * @return array
 */
function wpseo_console_get_field_definitions() {
	return array(
		'_wpseo_console_title'           => array(
			'label'   => __( 'SEO Title', 'wp-control-deck' ),
			'type'    => 'text',
			'counter' => 'title',
			'help'    => __( 'Recommended length: 50-60 characters.', 'wp-control-deck' ),
		),
		'_wpseo_console_description'     => array(
			'label'   => __( 'Meta Description', 'wp-control-deck' ),
			'type'    => 'textarea',
			'counter' => 'description',
			'help'    => __( 'Recommended length: 120-160 characters.', 'wp-control-deck' ),
		),
		'_wpseo_console_robots'          => array(
			'label'   => __( 'Robots Indexing', 'wp-control-deck' ),
			'type'    => 'select',
			'default' => 'default',
			'options' => array(
				'default'          => __( 'Default', 'wp-control-deck' ),
				'index'            => __( 'index', 'wp-control-deck' ),
				'noindex'          => __( 'noindex', 'wp-control-deck' ),
				'nofollow'         => __( 'nofollow', 'wp-control-deck' ),
				'noindex,nofollow' => __( 'noindex,nofollow', 'wp-control-deck' ),
			),
		),
		'_wpseo_console_canonical'       => array(
			'label' => __( 'Canonical URL', 'wp-control-deck' ),
			'type'  => 'url',
		),
		'_wpseo_console_og_title'        => array(
			'label' => __( 'Open Graph Title', 'wp-control-deck' ),
			'type'  => 'text',
		),
		'_wpseo_console_og_description'  => array(
			'label' => __( 'Open Graph Description', 'wp-control-deck' ),
			'type'  => 'textarea',
		),
		'_wpseo_console_og_image'        => array(
			'label' => __( 'Open Graph Image', 'wp-control-deck' ),
			'type'  => 'image',
		),
		'_wpseo_console_twitter_card'    => array(
			'label'   => __( 'Twitter Card Type', 'wp-control-deck' ),
			'type'    => 'select',
			'default' => 'summary',
			'options' => array(
				'summary'             => __( 'summary', 'wp-control-deck' ),
				'summary_large_image' => __( 'summary_large_image', 'wp-control-deck' ),
			),
		),
	);
}

/**
 * Sanitizes a SEO Console field according to its configured type.
 *
 * @param mixed $value Raw value.
 * @param array $field Field definition.
 * @return string
 */
function wpseo_console_sanitize_field( $value, $field ) {
	if ( ! class_exists( 'WPSEO_Console_Sanitizer' ) ) {
		require_once WP_CONTROL_DECK_PATH . 'includes/class-wpseo-console-sanitizer.php';
	}

	if ( 'textarea' === $field['type'] ) {
		return WPSEO_Console_Sanitizer::textarea( $value );
	}

	if ( 'url' === $field['type'] || 'image' === $field['type'] ) {
		return WPSEO_Console_Sanitizer::url( $value );
	}

	if ( 'select' === $field['type'] ) {
		return WPSEO_Console_Sanitizer::choice( $value, array_keys( $field['options'] ), $field['default'] );
	}

	return WPSEO_Console_Sanitizer::text( $value );
}

/**
 * Gets sanitized SEO Console values for a post.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function wpseo_console_get_post_meta_values( $post_id ) {
	$values = array();

	foreach ( wpseo_console_get_field_definitions() as $key => $field ) {
		$value = get_post_meta( $post_id, $key, true );

		if ( '' === $value && isset( $field['default'] ) ) {
			$value = $field['default'];
		}

		$values[ $key ] = wpseo_console_sanitize_field( $value, $field );
	}

	return $values;
}

/**
 * Checks whether a post has any non-default SEO Console metadata saved.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function wpseo_console_post_has_meta_values( $post_id ) {
	foreach ( wpseo_console_get_field_definitions() as $key => $field ) {
		$value = get_post_meta( $post_id, $key, true );

		if ( '' === $value ) {
			continue;
		}

		if ( isset( $field['default'] ) && $value === $field['default'] ) {
			continue;
		}

		return true;
	}

	return false;
}

/**
 * Gets SEO values, using global fallbacks only when no post meta is present.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function wpseo_console_get_effective_meta_values( $post_id ) {
	if ( wpseo_console_post_has_meta_values( $post_id ) ) {
		return wpseo_console_get_post_meta_values( $post_id );
	}

	$values    = wpseo_console_get_post_meta_values( $post_id );
	$fallbacks = wp_control_deck_get_seo_console_fallbacks();

	foreach ( $fallbacks as $key => $value ) {
		if ( isset( $values[ $key ] ) && '' !== $value ) {
			$values[ $key ] = $value;
		}
	}

	return $values;
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
 * Enables frontend page diagnostics for administrators.
 */
function wp_control_deck_enable_page_info() {
	if ( is_admin() ) {
		return;
	}

	add_action( 'wp_footer', 'wp_control_deck_render_page_info', 999 );
}

/**
 * Renders the frontend page diagnostics box.
 */
function wp_control_deck_render_page_info() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$page_info_style = wp_control_deck_get_page_info_style();
	?>
	<style>
		#wp-control-deck-page-info {
			background: #111827;
			border: 1px solid rgba(255, 255, 255, .14);
			border-radius: 8px;
			bottom: 16px;
			box-sizing: border-box;
			box-shadow: 0 12px 30px rgba(0, 0, 0, .24);
			color: #f9fafb;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
			font-size: 12px;
			left: 16px;
			line-height: 1.4;
			max-width: min(320px, calc(100vw - 32px));
			padding: 12px 14px;
			position: fixed;
			text-align: left;
			transition: opacity .18s ease, transform .18s ease;
			z-index: 999999;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass {
			-webkit-backdrop-filter: blur(20px) saturate(210%) contrast(108%);
			backdrop-filter: blur(20px) saturate(210%) contrast(108%);
			align-items: start;
			background:
				linear-gradient(135deg, rgba(255, 255, 255, .2), rgba(255, 255, 255, .035) 42%, rgba(255, 255, 255, .12)),
				rgba(255, 255, 255, .045);
			border-color: rgba(255, 255, 255, .38);
			border-radius: 10px;
			box-shadow:
				inset 0 1px 0 rgba(255, 255, 255, .46),
				inset 0 0 0 1px rgba(255, 255, 255, .08),
				0 18px 44px rgba(15, 23, 42, .16);
			color: #0f172a;
			display: grid;
			gap: 7px;
			grid-template-columns: minmax(0, 1fr);
			max-width: min(760px, calc(100vw - 32px));
			overflow: hidden;
			padding: 10px 16px;
			text-align: left;
			text-shadow: 0 1px 0 rgba(255, 255, 255, .35);
			width: min(760px, calc(100vw - 32px));
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass::before {
			background:
				linear-gradient(115deg, rgba(255, 255, 255, .32), rgba(255, 255, 255, 0) 26%),
				linear-gradient(290deg, rgba(255, 255, 255, .18), rgba(255, 255, 255, 0) 34%),
				radial-gradient(circle at 18% 0%, rgba(255, 255, 255, .28), transparent 24%);
			content: "";
			inset: 0;
			pointer-events: none;
			position: absolute;
		}

		#wp-control-deck-page-info.is-scrolling {
			opacity: .4;
			transform: translateX(calc(-100% - 24px));
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass.is-scrolling {
			transform: translateY(calc(100% + 24px));
		}

		#wp-control-deck-page-info * {
			box-sizing: border-box;
		}

		#wp-control-deck-page-info strong {
			color: #fff;
			display: block;
			font-size: 13px;
			margin-bottom: 8px;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass strong {
			color: #0f172a;
			font-size: 12px;
			margin: 0;
			position: relative;
			text-align: left;
			white-space: nowrap;
			z-index: 1;
		}

		#wp-control-deck-page-info dl {
			display: grid;
			gap: 6px 10px;
			grid-template-columns: max-content minmax(0, 1fr);
			margin: 0;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dl {
			gap: 4px 8px;
			grid-template-columns: repeat(3, max-content minmax(58px, 1fr));
			position: relative;
			text-align: left;
			z-index: 1;
		}

		#wp-control-deck-page-info dt {
			color: #cbd5e1;
			font-weight: 600;
			margin: 0;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dt {
			color: rgba(15, 23, 42, .72);
			font-size: 10px;
			white-space: nowrap;
		}

		#wp-control-deck-page-info dd {
			color: #f9fafb;
			margin: 0;
			min-width: 0;
			overflow-wrap: anywhere;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dd {
			color: #0f172a;
			font-size: 10px;
			font-weight: 700;
			line-height: 1.3;
			max-height: 2.6em;
			overflow: hidden;
		}

		#wp-control-deck-page-info dd.is-good {
			color: #86efac;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dd.is-good {
			color: #047857;
		}

		#wp-control-deck-page-info dd.is-medium {
			color: #fdba74;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dd.is-medium {
			color: #b45309;
		}

		#wp-control-deck-page-info dd.is-high {
			color: #fca5a5;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dd.is-high {
			color: #b91c1c;
		}

		#wp-control-deck-page-info dd.is-unavailable {
			color: #cbd5e1;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dd.is-unavailable {
			color: rgba(15, 23, 42, .58);
		}

		#wp-control-deck-page-info .wp-control-deck-page-info-disclaimer {
			border-top: 1px solid rgba(255, 255, 255, .12);
			color: #cbd5e1;
			font-size: 10px;
			line-height: 1.35;
			margin: 10px 0 0;
			padding-top: 8px;
		}

		#wp-control-deck-page-info.wp-control-deck-page-info-style-glass .wp-control-deck-page-info-disclaimer {
			border-top: 1px solid rgba(15, 23, 42, .1);
			color: rgba(15, 23, 42, .62);
			font-size: 9px;
			line-height: 1.2;
			margin: 10px 0 0;
			padding-top: 5px;
			position: relative;
			z-index: 1;
		}

		@media (max-width: 782px) {
			#wp-control-deck-page-info.wp-control-deck-page-info-style-glass {
				bottom: 20px;
				grid-template-columns: minmax(0, 1fr);
				left: 20px;
				max-width: none;
				right: 20px;
				width: auto;
			}

			#wp-control-deck-page-info.wp-control-deck-page-info-style-glass dl {
				grid-template-columns: max-content minmax(0, 1fr);
			}

			#wp-control-deck-page-info.wp-control-deck-page-info-style-glass .wp-control-deck-page-info-disclaimer {
				grid-column: auto;
			}
		}
	</style>
	<script>
		(function () {
			var unavailable = <?php echo wp_json_encode( __( 'Unavailable', 'wp-control-deck' ) ); ?>;
			var pageInfoStyle = <?php echo wp_json_encode( $page_info_style ); ?>;

			function getEntrySize(entry) {
				return entry.transferSize || entry.encodedBodySize || entry.decodedBodySize || 0;
			}

			function getLoadSpeed(navigationEntry, timing) {
				if (navigationEntry && navigationEntry.duration) {
					return Math.round(navigationEntry.duration);
				}

				if (timing && timing.loadEventEnd && timing.navigationStart) {
					return Math.max(0, timing.loadEventEnd - timing.navigationStart);
				}

				if (timing && timing.navigationStart) {
					return Math.max(0, Math.round(Date.now() - timing.navigationStart));
				}

				return null;
			}

			function formatBytes(bytes) {
				if (!bytes) {
					return unavailable;
				}

				if (bytes < 1024) {
					return bytes + ' B';
				}

				if (bytes < 1048576) {
					return (bytes / 1024).toFixed(1) + ' KB';
				}

				return (bytes / 1048576).toFixed(2) + ' MB';
			}

			function getLoadSpeedStatus(milliseconds) {
				if (null === milliseconds) {
					return 'is-unavailable';
				}

				if (milliseconds <= 1500) {
					return 'is-good';
				}

				if (milliseconds <= 3000) {
					return 'is-medium';
				}

				return 'is-high';
			}

			function getPageSizeStatus(bytes) {
				if (!bytes) {
					return 'is-unavailable';
				}

				if (bytes <= 1048576) {
					return 'is-good';
				}

				if (bytes <= 3145728) {
					return 'is-medium';
				}

				return 'is-high';
			}

			function getImageCountStatus(count) {
				if (count <= 10) {
					return 'is-good';
				}

				if (count <= 25) {
					return 'is-medium';
				}

				return 'is-high';
			}

			function getImageSizeStatus(bytes) {
				if (!bytes) {
					return 'is-unavailable';
				}

				if (bytes <= 512000) {
					return 'is-good';
				}

				if (bytes <= 1572864) {
					return 'is-medium';
				}

				return 'is-high';
			}

			function getMetaTitleStatus(value) {
				var length = value ? value.length : 0;

				if (length >= 30 && length <= 60) {
					return 'is-good';
				}

				if ((length > 0 && length < 30) || (length > 60 && length <= 70)) {
					return 'is-medium';
				}

				return 'is-high';
			}

			function getMetaDescriptionStatus(value) {
				var length = value ? value.length : 0;

				if (length >= 120 && length <= 160) {
					return 'is-good';
				}

				if ((length > 0 && length < 120) || (length > 160 && length <= 180)) {
					return 'is-medium';
				}

				return 'is-high';
			}

			function addRow(list, label, value, status) {
				var term = document.createElement('dt');
				var description = document.createElement('dd');

				term.textContent = label;
				description.textContent = value || unavailable;
				description.className = status || 'is-unavailable';
				list.appendChild(term);
				list.appendChild(description);
			}

			function renderPageInfo() {
				var box = document.createElement('aside');
				var title = document.createElement('strong');
				var list = document.createElement('dl');
				var disclaimer = document.createElement('p');
				var metaDescription = document.querySelector('meta[name="description"]');
				var resources = window.performance && typeof window.performance.getEntriesByType === 'function'
					? window.performance.getEntriesByType('resource')
					: [];
				var navigationEntry = window.performance && typeof window.performance.getEntriesByType === 'function'
					? window.performance.getEntriesByType('navigation')[0]
					: null;
				var timing = window.performance && window.performance.timing ? window.performance.timing : null;
				var pageSize = 0;
				var imageSize = 0;
				var hasPageSize = false;
				var hasImageSize = false;
				var loadSpeed = getLoadSpeed(navigationEntry, timing);
				var metaDescriptionValue = metaDescription ? metaDescription.getAttribute('content') : '';
				var metaTitle = document.title || '';

				box.id = 'wp-control-deck-page-info';
				box.className = 'wp-control-deck-page-info-style-' + pageInfoStyle;
				box.setAttribute('aria-label', <?php echo wp_json_encode( __( 'WP Control Deck Page Info', 'wp-control-deck' ) ); ?>);
				title.textContent = <?php echo wp_json_encode( __( 'Page Info', 'wp-control-deck' ) ); ?>;
				disclaimer.className = 'wp-control-deck-page-info-disclaimer';
				disclaimer.textContent = <?php echo wp_json_encode( __( 'These values are estimations only. Use Google Lighthouse or PageSpeed Insights for an accurate reading.', 'wp-control-deck' ) ); ?>;

				resources.forEach(function (entry) {
					var size = getEntrySize(entry);

					if (size) {
						pageSize += size;
						hasPageSize = true;
					}

					if ('img' === entry.initiatorType) {
						if (size) {
							imageSize += size;
							hasImageSize = true;
						}
					}
				});

				if (navigationEntry) {
					var documentSize = getEntrySize(navigationEntry);

					if (documentSize) {
						pageSize += documentSize;
						hasPageSize = true;
					}
				}

				addRow(list, <?php echo wp_json_encode( __( 'Approx. Load Speed', 'wp-control-deck' ) ); ?>, null === loadSpeed ? unavailable : loadSpeed + ' ms', getLoadSpeedStatus(loadSpeed));
				addRow(list, <?php echo wp_json_encode( __( 'Page Size', 'wp-control-deck' ) ); ?>, hasPageSize ? formatBytes(pageSize) : unavailable, hasPageSize ? getPageSizeStatus(pageSize) : 'is-unavailable');
				addRow(list, <?php echo wp_json_encode( __( 'Images', 'wp-control-deck' ) ); ?>, String(document.images.length), getImageCountStatus(document.images.length));
				addRow(list, <?php echo wp_json_encode( __( 'Image Size', 'wp-control-deck' ) ); ?>, hasImageSize ? formatBytes(imageSize) : unavailable, hasImageSize ? getImageSizeStatus(imageSize) : 'is-unavailable');
				addRow(list, <?php echo wp_json_encode( __( 'Meta Title', 'wp-control-deck' ) ); ?>, metaTitle || unavailable, getMetaTitleStatus(metaTitle));
				addRow(list, <?php echo wp_json_encode( __( 'Meta Description', 'wp-control-deck' ) ); ?>, metaDescriptionValue || unavailable, getMetaDescriptionStatus(metaDescriptionValue));

				box.appendChild(title);
				box.appendChild(list);
				box.appendChild(disclaimer);
				document.body.appendChild(box);
				enableScrollOpacity(box);
			}

			function enableScrollOpacity(box) {
				var scrollTimeout;

				window.addEventListener(
					'scroll',
					function () {
						box.classList.add('is-scrolling');
						window.clearTimeout(scrollTimeout);
						scrollTimeout = window.setTimeout(function () {
							box.classList.remove('is-scrolling');
						}, 700);
					},
					{ passive: true }
				);
			}

			function schedulePageInfo() {
				window.setTimeout(renderPageInfo, 0);
			}

			if ('complete' === document.readyState) {
				schedulePageInfo();
			} else {
				window.addEventListener('load', schedulePageInfo);
			}
		})();
	</script>
	<?php
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
	set_transient( 'wp_control_deck_deleted_comments_' . get_current_user_id(), $deleted_count, MINUTE_IN_SECONDS );

	$redirect_url = add_query_arg( 'page', 'wp-control-deck', admin_url( 'admin.php' ) );

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
