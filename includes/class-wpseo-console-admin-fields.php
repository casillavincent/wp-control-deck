<?php
/**
 * Admin fields for the WP SEO Console module.
 *
 * @package WP_Control_Deck
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds SEO controls to post and page edit screens and saves them as post meta.
 */
if ( ! class_exists( 'WPSEO_Console_Admin_Fields', false ) ) :
class WPSEO_Console_Admin_Fields {

	const NONCE_ACTION = 'wpseo_console_save_meta';
	const NONCE_NAME   = 'wpseo_console_nonce';

	/**
	 * Registers editor hooks.
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Adds the SEO Console meta box to posts and pages.
	 */
	public function add_meta_boxes() {
		foreach ( wpseo_console_get_supported_post_types() as $post_type ) {
			add_meta_box(
				'wpseo-console-fields',
				__( 'WP SEO Console', 'wp-control-deck' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Enqueues admin assets only on supported editor screens.
	 *
	 * @param string $hook_suffix Current admin hook.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->post_type, wpseo_console_get_supported_post_types(), true ) ) {
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
	 * Renders all SEO fields and the Google-style preview.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$values      = wpseo_console_get_post_meta_values( $post->ID );
		$permalink   = get_permalink( $post );
		$preview_url = $permalink ? $permalink : home_url( '/' );
		?>
		<div class="wpseo-console-fields" data-wpseo-console-preview>
			<?php foreach ( wpseo_console_get_field_definitions() as $key => $field ) : ?>
				<?php $value = isset( $values[ $key ] ) ? $values[ $key ] : ''; ?>
				<div class="wpseo-console-field">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea
							id="<?php echo esc_attr( $key ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							rows="3"
							<?php echo ! empty( $field['counter'] ) ? 'data-wpseo-console-counter="' . esc_attr( $field['counter'] ) . '"' : ''; ?>
						><?php echo esc_textarea( $value ); ?></textarea>
					<?php elseif ( 'select' === $field['type'] ) : ?>
						<select id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>">
							<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
								<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
									<?php echo esc_html( $option_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'image' === $field['type'] ) : ?>
						<div class="wpseo-console-image-select" data-wpseo-console-image-select>
							<input
								id="<?php echo esc_attr( $key ); ?>"
								name="<?php echo esc_attr( $key ); ?>"
								type="hidden"
								value="<?php echo esc_url( $value ); ?>"
								data-wpseo-console-image-input
							/>
							<div class="wpseo-console-image-preview" data-wpseo-console-image-preview>
								<?php if ( $value ) : ?>
									<img src="<?php echo esc_url( $value ); ?>" alt="" />
								<?php endif; ?>
							</div>
							<button type="button" class="button" data-wpseo-console-image-button><?php esc_html_e( 'Select Image', 'wp-control-deck' ); ?></button>
							<button type="button" class="button" data-wpseo-console-image-remove><?php esc_html_e( 'Remove', 'wp-control-deck' ); ?></button>
						</div>
					<?php else : ?>
						<input
							id="<?php echo esc_attr( $key ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							type="<?php echo esc_attr( $field['type'] ); ?>"
							value="<?php echo 'url' === $field['type'] ? esc_url( $value ) : esc_attr( $value ); ?>"
							<?php echo ! empty( $field['counter'] ) ? 'data-wpseo-console-counter="' . esc_attr( $field['counter'] ) . '"' : ''; ?>
						/>
					<?php endif; ?>
					<?php if ( ! empty( $field['help'] ) ) : ?>
						<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $field['counter'] ) ) : ?>
						<p class="wpseo-console-counter" data-wpseo-console-counter-output="<?php echo esc_attr( $field['counter'] ); ?>"></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<div class="wpseo-console-preview" aria-live="polite">
				<h3><?php esc_html_e( 'Search Preview', 'wp-control-deck' ); ?></h3>
				<div class="wpseo-console-preview-title" data-wpseo-console-preview-title>
					<?php echo esc_html( $values['_wpseo_console_title'] ? $values['_wpseo_console_title'] : get_the_title( $post ) ); ?>
				</div>
				<div class="wpseo-console-preview-url" data-wpseo-console-preview-url>
					<?php echo esc_html( untrailingslashit( $preview_url ) ); ?>
				</div>
				<div class="wpseo-console-preview-description" data-wpseo-console-preview-description>
					<?php echo esc_html( $values['_wpseo_console_description'] ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Saves SEO metadata securely.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! in_array( get_post_type( $post_id ), wpseo_console_get_supported_post_types(), true ) ) {
			return;
		}

		foreach ( wpseo_console_get_field_definitions() as $key => $field ) {
			$raw_value = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
			$value     = wpseo_console_sanitize_field( $raw_value, $field );

			if ( '' === $value || ( isset( $field['default'] ) && $value === $field['default'] ) ) {
				delete_post_meta( $post_id, $key );
				continue;
			}

			update_post_meta( $post_id, $key, $value );
		}
	}
}
endif;
