<?php
/**
 * Admin settings screen.
 *
 * @package MultipleFloatingWA
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the settings page and stores the option.
 */
class MFW_Admin {

	/**
	 * Screen hook suffix of the settings page.
	 *
	 * @var string
	 */
	private $hook = '';

	/**
	 * Register hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MFW_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Add the settings page.
	 */
	public function menu() {
		$this->hook = add_options_page(
			__( 'Floating WhatsApp', 'multiple-floating-wa' ),
			__( 'Floating WhatsApp', 'multiple-floating-wa' ),
			'manage_options',
			'multiple-floating-wa',
			array( $this, 'page' )
		);
	}

	/**
	 * Settings link on the plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=multiple-floating-wa' );

		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'multiple-floating-wa' ) . '</a>' );

		return $links;
	}

	/**
	 * Register the option and its sanitizer.
	 */
	public function register() {
		register_setting(
			'mfw_settings_group',
			MFW_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => MFW_Plugin::defaults(),
			)
		);
	}

	/**
	 * Load admin assets on the plugin screen only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function assets( $hook ) {
		if ( ! $this->is_plugin_screen( $hook ) ) {
			return;
		}

		mfw()->frontend->register_assets();

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'mfw-frontend' );
		wp_enqueue_style( 'mfw-admin', MFW_URL . 'assets/css/admin.css', array( 'mfw-frontend' ), MFW_VERSION );
		wp_enqueue_script( 'mfw-frontend' );

		wp_enqueue_script(
			'mfw-admin',
			MFW_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
			MFW_VERSION,
			true
		);

		$settings = MFW_Plugin::settings();

		wp_localize_script(
			'mfw-admin',
			'mfwAdmin',
			array(
				'nextIndex'   => count( $settings['items'] ) + 1,
				'defaultIcon' => MFW_Icon::whatsapp(),
				'i18n'        => array(
					'button' => __( 'Button', 'multiple-floating-wa' ),
				),
			)
		);
	}

	/**
	 * Is the current admin screen this plugin settings page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return bool
	 */
	private function is_plugin_screen( $hook ) {
		$hook = (string) $hook;

		if ( $this->hook && $hook === $this->hook ) {
			return true;
		}

		return false !== strpos( $hook, 'multiple-floating-wa' );
	}

	/**
	 * Sanitize submitted settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$defaults = MFW_Plugin::defaults();
		$input    = is_array( $input ) ? $input : array();
		$output   = $defaults;

		$output['enabled']     = empty( $input['enabled'] ) ? 0 : 1;
		$output['open_new_tab'] = empty( $input['open_new_tab'] ) ? 0 : 1;

		$positions           = array( 'bottom-right', 'bottom-left' );
		$output['position']  = isset( $input['position'] ) && in_array( $input['position'], $positions, true ) ? $input['position'] : $defaults['position'];

		$sizes          = array( 'small', 'medium', 'large' );
		$output['size'] = isset( $input['size'] ) && in_array( $input['size'], $sizes, true ) ? $input['size'] : $defaults['size'];

		foreach ( array( 'button_color', 'icon_color', 'header_color', 'panel_color', 'text_color' ) as $color_key ) {
			$raw   = isset( $input[ $color_key ] ) ? trim( (string) $input[ $color_key ] ) : '';
			$color = $raw ? sanitize_hex_color( $raw ) : '';

			if ( $color ) {
				$output[ $color_key ] = $color;
				continue;
			}

			$output[ $color_key ] = ( 'header_color' === $color_key ) ? '' : $defaults[ $color_key ];
		}

		$output['header_title'] = isset( $input['header_title'] ) ? MFW_Plugin::sanitize_text( $input['header_title'] ) : $defaults['header_title'];
		$output['panel_intro']  = isset( $input['panel_intro'] ) ? sanitize_textarea_field( $input['panel_intro'] ) : '';
		$output['icon_url']     = isset( $input['icon_url'] ) ? esc_url_raw( trim( $input['icon_url'] ) ) : '';

		foreach ( array( 'auto_open' ) as $number_key ) {
			$output[ $number_key ] = isset( $input[ $number_key ] ) ? min( 120, absint( $input[ $number_key ] ) ) : 0;
		}

		$output['hide_desktop'] = empty( $input['hide_desktop'] ) ? 0 : 1;
		$output['hide_tablet']  = empty( $input['hide_tablet'] ) ? 0 : 1;
		$output['hide_mobile']  = empty( $input['hide_mobile'] ) ? 0 : 1;

		$exclude_ids = array();

		if ( isset( $input['exclude_ids'] ) ) {
			foreach ( preg_split( '/[\s,]+/', (string) $input['exclude_ids'] ) as $chunk ) {
				$chunk = absint( $chunk );

				if ( $chunk ) {
					$exclude_ids[] = $chunk;
				}
			}
		}

		$output['exclude_ids'] = array_values( array_unique( $exclude_ids ) );

		$exclude_types = array();

		if ( isset( $input['exclude_post_types'] ) && is_array( $input['exclude_post_types'] ) ) {
			foreach ( $input['exclude_post_types'] as $type ) {
				$type = sanitize_key( $type );

				if ( $type && post_type_exists( $type ) ) {
					$exclude_types[] = $type;
				}
			}
		}

		$output['exclude_post_types'] = array_values( array_unique( $exclude_types ) );
		$output['exclude_front']      = empty( $input['exclude_front'] ) ? 0 : 1;
		$output['exclude_blog']       = empty( $input['exclude_blog'] ) ? 0 : 1;

		$items = array();

		if ( isset( $input['items'] ) && is_array( $input['items'] ) ) {
			foreach ( $input['items'] as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}

				$items[] = array(
					'label'   => MFW_Plugin::sanitize_text( isset( $item['label'] ) ? $item['label'] : '' ),
					'number'  => MFW_Plugin::sanitize_text( isset( $item['number'] ) ? $item['number'] : '' ),
					'message' => sanitize_textarea_field( isset( $item['message'] ) ? $item['message'] : '' ),
				);

				if ( count( $items ) >= 30 ) {
					break;
				}
			}
		}

		if ( empty( $items ) ) {
			$items = $defaults['items'];
		}

		$output['items'] = $items;

		return $output;
	}

	/**
	 * Render the settings page.
	 */
	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = MFW_Plugin::settings();
		?>
		<div class="wrap mfw-wrap">
			<h1><?php esc_html_e( 'Multiple Floating WhatsApp', 'multiple-floating-wa' ); ?></h1>
			<p class="mfw-lead"><?php esc_html_e( 'The floating widget shows on every page. Each row below becomes a button inside the widget.', 'multiple-floating-wa' ); ?></p>

			<form method="post" action="options.php" id="mfw-form">
				<?php settings_fields( 'mfw_settings_group' ); ?>

				<div class="mfw-layout">
					<div class="mfw-column">

						<div class="mfw-card">
							<h2><?php esc_html_e( 'Buttons', 'multiple-floating-wa' ); ?></h2>
							<p class="description"><?php esc_html_e( 'Leave a label empty to get a numbered placeholder. Leave the number empty and the button follows the # fallback link.', 'multiple-floating-wa' ); ?></p>

							<div class="mfw-row-head" aria-hidden="true">
								<span class="mfw-col-drag"></span>
								<span><?php esc_html_e( 'Label', 'multiple-floating-wa' ); ?></span>
								<span><?php esc_html_e( 'Number or URL', 'multiple-floating-wa' ); ?></span>
								<span><?php esc_html_e( 'Prefilled message', 'multiple-floating-wa' ); ?></span>
								<span class="mfw-col-action"></span>
							</div>

							<div class="mfw-rows" id="mfw-rows">
								<?php
								$index = 0;

								foreach ( $settings['items'] as $item ) {
									$index++;
									$this->row( $index, $item );
								}
								?>
							</div>

							<p><button type="button" class="button" id="mfw-add-row"><?php esc_html_e( 'Add button', 'multiple-floating-wa' ); ?></button></p>
						</div>

						<div class="mfw-card">
							<h2><?php esc_html_e( 'Appearance', 'multiple-floating-wa' ); ?></h2>

							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><?php esc_html_e( 'Button color', 'multiple-floating-wa' ); ?></th>
									<td><?php $this->color_field( 'button_color', $settings ); ?></td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Icon color', 'multiple-floating-wa' ); ?></th>
									<td>
										<?php $this->color_field( 'icon_color', $settings ); ?>
										<p class="description"><?php esc_html_e( 'Color of the launcher icon and the icon in the panel header.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Header background', 'multiple-floating-wa' ); ?></th>
									<td>
										<?php $this->color_field( 'header_color', $settings ); ?>
										<p class="description"><?php esc_html_e( 'Leave empty to follow the button color.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Panel background', 'multiple-floating-wa' ); ?></th>
									<td><?php $this->color_field( 'panel_color', $settings ); ?></td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Panel text color', 'multiple-floating-wa' ); ?></th>
									<td><?php $this->color_field( 'text_color', $settings ); ?></td>
								</tr>
								<tr>
									<th scope="row"><label for="mfw-header-title"><?php esc_html_e( 'Title', 'multiple-floating-wa' ); ?></label></th>
									<td>
										<input type="text" class="regular-text" id="mfw-header-title" name="<?php echo esc_attr( MFW_OPTION ); ?>[header_title]" value="<?php echo esc_attr( $settings['header_title'] ); ?>" />
										<p class="description"><?php esc_html_e( 'Shown in the widget header, for example Chat with us or Need help.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfw-panel-intro"><?php esc_html_e( 'Intro text', 'multiple-floating-wa' ); ?></label></th>
									<td>
										<textarea id="mfw-panel-intro" class="regular-text" rows="2" name="<?php echo esc_attr( MFW_OPTION ); ?>[panel_intro]"><?php echo esc_textarea( $settings['panel_intro'] ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Optional line above the buttons.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Launcher size', 'multiple-floating-wa' ); ?></th>
									<td>
										<select name="<?php echo esc_attr( MFW_OPTION ); ?>[size]" id="mfw-size">
											<option value="small" <?php selected( $settings['size'], 'small' ); ?>><?php esc_html_e( 'Small', 'multiple-floating-wa' ); ?></option>
											<option value="medium" <?php selected( $settings['size'], 'medium' ); ?>><?php esc_html_e( 'Medium', 'multiple-floating-wa' ); ?></option>
											<option value="large" <?php selected( $settings['size'], 'large' ); ?>><?php esc_html_e( 'Large', 'multiple-floating-wa' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Position', 'multiple-floating-wa' ); ?></th>
									<td>
										<select name="<?php echo esc_attr( MFW_OPTION ); ?>[position]" id="mfw-position">
											<option value="bottom-right" <?php selected( $settings['position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom right', 'multiple-floating-wa' ); ?></option>
											<option value="bottom-left" <?php selected( $settings['position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom left', 'multiple-floating-wa' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfw-icon-url"><?php esc_html_e( 'Custom icon URL', 'multiple-floating-wa' ); ?></label></th>
									<td>
										<input type="url" class="regular-text" id="mfw-icon-url" name="<?php echo esc_attr( MFW_OPTION ); ?>[icon_url]" value="<?php echo esc_attr( $settings['icon_url'] ); ?>" placeholder="https://" />
										<p class="description"><?php esc_html_e( 'The WhatsApp icon is built in. Fill this only to replace it with your own image.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
							</table>
						</div>

						<div class="mfw-card">
							<h2><?php esc_html_e( 'Behavior', 'multiple-floating-wa' ); ?></h2>

							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><?php esc_html_e( 'Widget', 'multiple-floating-wa' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="<?php echo esc_attr( MFW_OPTION ); ?>[enabled]" value="1" <?php checked( $settings['enabled'], 1 ); ?> />
											<?php esc_html_e( 'Load the floating widget on the whole site', 'multiple-floating-wa' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Links', 'multiple-floating-wa' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="<?php echo esc_attr( MFW_OPTION ); ?>[open_new_tab]" value="1" <?php checked( $settings['open_new_tab'], 1 ); ?> />
											<?php esc_html_e( 'Open chats in a new tab', 'multiple-floating-wa' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="mfw-auto-open"><?php esc_html_e( 'Auto open', 'multiple-floating-wa' ); ?></label></th>
									<td>
										<input type="number" min="0" max="120" step="1" id="mfw-auto-open" name="<?php echo esc_attr( MFW_OPTION ); ?>[auto_open]" value="<?php echo esc_attr( $settings['auto_open'] ); ?>" class="small-text" />
										<p class="description"><?php esc_html_e( 'Seconds before the panel opens by itself. Use 0 to keep it closed until a visitor interacts. Opens once per session.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Visibility', 'multiple-floating-wa' ); ?></th>
									<td>
										<label><input type="checkbox" name="<?php echo esc_attr( MFW_OPTION ); ?>[hide_desktop]" value="1" <?php checked( $settings['hide_desktop'], 1 ); ?> /> <?php esc_html_e( 'Hide on desktop', 'multiple-floating-wa' ); ?></label><br />
										<label><input type="checkbox" name="<?php echo esc_attr( MFW_OPTION ); ?>[hide_tablet]" value="1" <?php checked( $settings['hide_tablet'], 1 ); ?> /> <?php esc_html_e( 'Hide on tablet', 'multiple-floating-wa' ); ?></label><br />
										<label><input type="checkbox" name="<?php echo esc_attr( MFW_OPTION ); ?>[hide_mobile]" value="1" <?php checked( $settings['hide_mobile'], 1 ); ?> /> <?php esc_html_e( 'Hide on mobile', 'multiple-floating-wa' ); ?></label>
									</td>
								</tr>
							</table>
						</div>

						<div class="mfw-card">
							<h2><?php esc_html_e( 'Exclusions', 'multiple-floating-wa' ); ?></h2>
							<p class="description"><?php esc_html_e( 'The widget shows on every page by default. Anything listed here is skipped. Pages that carry the shortcode still render the widget.', 'multiple-floating-wa' ); ?></p>

							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="mfw-exclude-ids"><?php esc_html_e( 'Exclude by ID', 'multiple-floating-wa' ); ?></label></th>
									<td>
										<input type="text" class="regular-text" id="mfw-exclude-ids" name="<?php echo esc_attr( MFW_OPTION ); ?>[exclude_ids]" value="<?php echo esc_attr( implode( ', ', array_map( 'absint', (array) $settings['exclude_ids'] ) ) ); ?>" placeholder="12, 45, 301" />
										<p class="description"><?php esc_html_e( 'Comma separated IDs. Works for posts, pages and any custom post type item. The ID is in the URL of the editor screen, for example post=301.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Exclude post types', 'multiple-floating-wa' ); ?></th>
									<td>
										<?php $this->post_type_checkboxes( $settings ); ?>
										<p class="description"><?php esc_html_e( 'Every single view of a checked post type is skipped.', 'multiple-floating-wa' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Exclude special pages', 'multiple-floating-wa' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="<?php echo esc_attr( MFW_OPTION ); ?>[exclude_front]" value="1" <?php checked( $settings['exclude_front'], 1 ); ?> />
											<?php esc_html_e( 'Front page', 'multiple-floating-wa' ); ?>
										</label><br />
										<label>
											<input type="checkbox" name="<?php echo esc_attr( MFW_OPTION ); ?>[exclude_blog]" value="1" <?php checked( $settings['exclude_blog'], 1 ); ?> />
											<?php esc_html_e( 'Blog page (posts page)', 'multiple-floating-wa' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>

					</div>

					<div class="mfw-column mfw-column-side">
						<div class="mfw-card">
							<h2><?php esc_html_e( 'Preview', 'multiple-floating-wa' ); ?></h2>
							<div class="mfw-preview-stage">
								<?php echo mfw()->frontend->render( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>

						<div class="mfw-card">
							<h2><?php esc_html_e( 'Shortcode', 'multiple-floating-wa' ); ?></h2>
							<p><?php esc_html_e( 'Place the widget manually with:', 'multiple-floating-wa' ); ?></p>
							<p><code>[multiple_floating_wa]</code></p>
							<p><?php esc_html_e( 'Turn the widget off above to use it on selected pages only. Attributes override the saved values:', 'multiple-floating-wa' ); ?></p>
							<p><code>[multiple_floating_wa position="bottom-left" size="large" button_color="#128c7e" auto_open="5"]</code></p>
						</div>
					</div>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
		$this->template();
	}

	/**
	 * Hidden row template used by the repeater script.
	 */
	private function template() {
		?>
		<script type="text/html" id="tmpl-mfw-row">
			<?php $this->row( '__INDEX__', array() ); ?>
		</script>
		<?php
	}

	/**
	 * Render one repeater row.
	 *
	 * @param int|string $index Row index or placeholder.
	 * @param array      $item  Row values.
	 */
	public function row( $index, $item ) {
		$item = wp_parse_args( (array) $item, array( 'label' => '', 'number' => '', 'message' => '' ) );
		$name = MFW_OPTION . '[items][' . $index . ']';
		?>
		<div class="mfw-row">
			<span class="mfw-drag" aria-hidden="true"></span>
			<input type="text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $item['label'] ); ?>" placeholder="<?php esc_attr_e( 'Button', 'multiple-floating-wa' ); ?>" />
			<input type="text" name="<?php echo esc_attr( $name ); ?>[number]" value="<?php echo esc_attr( $item['number'] ); ?>" placeholder="6281234567890" />
			<input type="text" name="<?php echo esc_attr( $name ); ?>[message]" value="<?php echo esc_attr( $item['message'] ); ?>" placeholder="<?php esc_attr_e( 'Hello, I need help', 'multiple-floating-wa' ); ?>" />
			<button type="button" class="button-link mfw-remove"><?php esc_html_e( 'Remove', 'multiple-floating-wa' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Checkboxes for every public post type.
	 *
	 * @param array $settings Saved settings.
	 */
	private function post_type_checkboxes( $settings ) {
		$types    = get_post_types( array( 'public' => true ), 'objects' );
		$selected = array_map( 'strval', (array) $settings['exclude_post_types'] );

		if ( empty( $types ) ) {
			esc_html_e( 'No public post types found.', 'multiple-floating-wa' );

			return;
		}

		echo '<div class="mfw-type-list">';

		foreach ( $types as $type ) {
			if ( 'attachment' === $type->name ) {
				continue;
			}

			printf(
				'<label><input type="checkbox" name="%1$s[exclude_post_types][]" value="%2$s" %3$s /> %4$s <code>%2$s</code></label>',
				esc_attr( MFW_OPTION ),
				esc_attr( $type->name ),
				checked( in_array( (string) $type->name, $selected, true ), true, false ),
				esc_html( $type->labels->singular_name )
			);
		}

		echo '</div>';
	}

	/**
	 * Render a color input.
	 *
	 * @param string $key      Setting key.
	 * @param array  $settings Saved settings.
	 */
	private function color_field( $key, $settings ) {
		printf(
			'<input type="text" class="mfw-color" name="%1$s[%2$s]" id="mfw-%2$s" value="%3$s" data-default-color="%4$s" />',
			esc_attr( MFW_OPTION ),
			esc_attr( $key ),
			esc_attr( $settings[ $key ] ),
			esc_attr( MFW_Plugin::defaults()[ $key ] )
		);
	}
}
