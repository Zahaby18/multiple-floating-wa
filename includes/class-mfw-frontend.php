<?php
/**
 * Frontend output.
 *
 * @package MultipleFloatingWA
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the floating widget and its assets.
 */
class MFW_Frontend {

	/**
	 * Whether the widget markup was already printed on this request.
	 *
	 * @var bool
	 */
	private $rendered = false;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'wp_footer', array( $this, 'auto_output' ), 99 );
	}

	/**
	 * Load styles and scripts when the widget can appear on the page.
	 */
	public function assets() {
		wp_register_style( 'mfw-frontend', MFW_URL . 'assets/css/frontend.css', array(), MFW_VERSION );
		wp_register_script( 'mfw-frontend', MFW_URL . 'assets/js/frontend.js', array(), MFW_VERSION, true );

		if ( $this->is_needed() ) {
			wp_enqueue_style( 'mfw-frontend' );
			wp_enqueue_script( 'mfw-frontend' );
		}
	}

	/**
	 * Print the widget on every front end page when enabled.
	 */
	public function auto_output() {
		$settings = MFW_Plugin::settings();

		if ( $this->rendered || empty( $settings['enabled'] ) ) {
			return;
		}

		if ( ! wp_style_is( 'mfw-frontend', 'enqueued' ) ) {
			wp_enqueue_style( 'mfw-frontend' );
		}

		if ( ! wp_script_is( 'mfw-frontend', 'enqueued' ) ) {
			wp_enqueue_script( 'mfw-frontend' );
			wp_print_scripts( 'mfw-frontend' );
		}

		echo $this->render( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$this->rendered = true;
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode( $atts = array() ) {
		$settings = MFW_Plugin::settings();

		if ( ! wp_style_is( 'mfw-frontend', 'enqueued' ) ) {
			wp_enqueue_style( 'mfw-frontend' );
		}

		if ( ! wp_script_is( 'mfw-frontend', 'enqueued' ) ) {
			wp_enqueue_script( 'mfw-frontend' );
		}

		$overrides = shortcode_atts(
			array(
				'position'     => '',
				'size'         => '',
				'title'        => '',
				'button_color' => '',
				'header_color' => '',
				'panel_color'  => '',
				'text_color'   => '',
				'auto_open'    => '',
				'intro'        => '',
			),
			is_array( $atts ) ? $atts : array(),
			'multiple_floating_wa'
		);

		$map = array(
			'title' => 'header_title',
			'intro' => 'panel_intro',
		);

		foreach ( $overrides as $key => $value ) {
			if ( '' === $value || null === $value ) {
				continue;
			}

			$target = isset( $map[ $key ] ) ? $map[ $key ] : $key;

			if ( in_array( $target, array( 'auto_open' ), true ) ) {
				$settings[ $target ] = absint( $value );
				continue;
			}

			if ( in_array( $target, array( 'button_color', 'header_color', 'panel_color', 'text_color' ), true ) ) {
				$color = sanitize_hex_color( $value );
				if ( $color ) {
					$settings[ $target ] = $color;
				}
				continue;
			}

			$settings[ $target ] = MFW_Plugin::sanitize_text( $value );
		}

		$this->rendered = true;

		return $this->render( $settings );
	}

	/**
	 * Does the current request need the widget assets.
	 *
	 * @return bool
	 */
	private function is_needed() {
		$settings = MFW_Plugin::settings();

		if ( ! empty( $settings['enabled'] ) ) {
			return true;
		}

		$post = get_post();

		if ( $post && is_string( $post->post_content ) ) {
			if ( has_shortcode( $post->post_content, 'multiple_floating_wa' ) || has_shortcode( $post->post_content, 'show_wa' ) ) {
				return true;
			}
		}

		return (bool) apply_filters( 'mfw_needs_assets', false );
	}

	/**
	 * Build a wa.me style link from a number, a full URL or an anchor.
	 *
	 * @param string $number  Phone number, full URL or anchor.
	 * @param string $message Prefilled message.
	 * @return string
	 */
	public static function build_link( $number, $message = '' ) {
		$number  = trim( (string) $number );
		$message = trim( (string) $message );

		if ( '' === $number ) {
			return '#';
		}

		if ( 0 === strpos( $number, '#' ) ) {
			return $number;
		}

		if ( preg_match( '#^https?://#i', $number ) ) {
			if ( '' !== $message && false === strpos( $number, 'text=' ) ) {
				$number = add_query_arg( 'text', rawurlencode( $message ), $number );
			}

			return $number;
		}

		$digits = preg_replace( '/[^0-9]/', '', $number );

		if ( '' === $digits ) {
			return '#';
		}

		$link = 'https://api.whatsapp.com/send?phone=' . $digits;

		if ( '' !== $message ) {
			$link .= '&text=' . rawurlencode( $message );
		}

		return $link;
	}

	/**
	 * Resolve the visible label of a button.
	 *
	 * @param string $label Row label.
	 * @param int    $index Row position, starting at one.
	 * @return string
	 */
	public static function item_label( $label, $index ) {
		$label = trim( (string) $label );

		if ( '' === $label ) {
			/* translators: %d: button position. */
			return sprintf( __( 'Button %d', 'multiple-floating-wa' ), (int) $index );
		}

		return $label;
	}

	/**
	 * Render the widget markup.
	 *
	 * @param array $settings Settings to render.
	 * @return string
	 */
	public function render( $settings ) {
		$settings = wp_parse_args( $settings, MFW_Plugin::defaults() );

		$items = array();

		foreach ( (array) $settings['items'] as $index => $item ) {
			$item   = wp_parse_args( (array) $item, array( 'label' => '', 'number' => '', 'message' => '' ) );
			$number = trim( (string) $item['number'] );

			$items[] = array(
				'label' => self::item_label( $item['label'], $index + 1 ),
				'href'  => self::build_link( $number, $item['message'] ),
			);
		}

		$items = apply_filters( 'mfw_items', $items, $settings );

		if ( empty( $items ) ) {
			return '';
		}

		$position = in_array( $settings['position'], array( 'bottom-right', 'bottom-left' ), true ) ? $settings['position'] : 'bottom-right';
		$size     = in_array( $settings['size'], array( 'small', 'medium', 'large' ), true ) ? $settings['size'] : 'medium';

		$header_color = $settings['header_color'] ? $settings['header_color'] : ( $settings['button_color'] ? $settings['button_color'] : '#25d366' );

		$classes = array(
			'mfw-widget',
			'mfw-position-' . $position,
			'mfw-size-' . $size,
		);

		if ( ! empty( $settings['hide_desktop'] ) ) {
			$classes[] = 'mfw-hide-desktop';
		}

		if ( ! empty( $settings['hide_tablet'] ) ) {
			$classes[] = 'mfw-hide-tablet';
		}

		if ( ! empty( $settings['hide_mobile'] ) ) {
			$classes[] = 'mfw-hide-mobile';
		}

		$style = sprintf(
			'--mfw-button:%1$s;--mfw-header:%2$s;--mfw-panel:%3$s;--mfw-text:%4$s;',
			esc_attr( $settings['button_color'] ),
			esc_attr( $header_color ),
			esc_attr( $settings['panel_color'] ),
			esc_attr( $settings['text_color'] )
		);

		$title    = '' !== trim( (string) $settings['header_title'] ) ? $settings['header_title'] : __( 'Chat with us', 'multiple-floating-wa' );
		$new_tab  = ! empty( $settings['open_new_tab'] );
		$target   = $new_tab ? ' target="_blank"' : '';
		$rel      = $new_tab ? ' rel="noopener noreferrer"' : '';
		$auto     = absint( $settings['auto_open'] );
		$intro    = trim( (string) $settings['panel_intro'] );
		$icon_url = $settings['icon_url'];

		ob_start();
		?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style ); ?>" data-mfw data-auto-open="<?php echo esc_attr( $auto ); ?>">
	<div class="mfw-panel" role="dialog" aria-modal="false" aria-label="<?php echo esc_attr( $title ); ?>" aria-hidden="true">
		<div class="mfw-panel-head">
			<span class="mfw-panel-icon"><?php echo MFW_Icon::whatsapp( 'mfw-svg mfw-svg-head' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="mfw-panel-title"><?php echo esc_html( $title ); ?></span>
			<button type="button" class="mfw-close" aria-label="<?php echo esc_attr__( 'Close', 'multiple-floating-wa' ); ?>"><?php echo MFW_Icon::close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>
		<div class="mfw-panel-body">
			<?php if ( '' !== $intro ) : ?>
			<p class="mfw-intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
			<?php foreach ( $items as $item ) : ?>
			<a class="mfw-item" href="<?php echo esc_url( $item['href'] ? $item['href'] : '#' ); ?>"<?php echo $target . $rel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<span class="mfw-item-icon"><?php echo MFW_Icon::whatsapp( 'mfw-svg mfw-svg-item' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mfw-item-label"><?php echo esc_html( $item['label'] ); ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
	<button type="button" class="mfw-launcher" aria-expanded="false" aria-label="<?php echo esc_attr__( 'Show WhatsApp numbers', 'multiple-floating-wa' ); ?>">
		<span class="mfw-launcher-icon"><?php echo MFW_Icon::launcher( $icon_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</button>
</div>
		<?php

		return trim( (string) preg_replace( '/>\s+</', '><', (string) ob_get_clean() ) );
	}
}
