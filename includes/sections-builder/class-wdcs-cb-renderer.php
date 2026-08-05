<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDCS_CB_Renderer {

	public static function render( int $post_id ): string {
		$sections = get_post_meta( $post_id, '_wdcs_cb_sections', true );
		if ( ! is_array( $sections ) || empty( $sections ) ) {
			return '';
		}

		self::enqueue_frontend_assets();

		$out = '';
		foreach ( $sections as $section ) {
			$out .= self::render_section( $section );
		}
		return $out;
	}

	public static function render_by_section_id( int $post_id, string $section_id ): string {
		$sections = get_post_meta( $post_id, '_wdcs_cb_sections', true );
		if ( ! is_array( $sections ) || empty( $sections ) ) {
			return '';
		}

		foreach ( $sections as $section ) {
			if ( ( $section['id'] ?? '' ) === $section_id ) {
				self::enqueue_frontend_assets();
				return self::render_section( $section );
			}
		}
		return '';
	}

	private static function enqueue_frontend_assets(): void {
		wp_enqueue_style( 'wdcs-cb-front', WDCS_PLUGIN_URL . 'assets/css/wdcs-sections-frontend.css', array(), WDCS_VERSION );
		wp_enqueue_script( 'wdcs-cb-front', WDCS_PLUGIN_URL . 'assets/js/wdcs-sections-frontend.js', array(), WDCS_VERSION, true );
	}

	/* ------------------------------------------------------------------ */
	/*  Section                                                             */
	/* ------------------------------------------------------------------ */

	private static function render_section( array $s ): string {
		$bg      = $s['background'] ?? array();
		$layout  = $s['layout']     ?? array();
		$spacing = $s['spacing']    ?? array();

		$section_style = self::build_section_style( $bg, $spacing['margin'] ?? array() );

		$container_class = 'wdcs-cb-container';
		if ( ( $layout['container_width'] ?? 'full' ) === 'boxed' ) {
			$container_class .= ' wdcs-cb-boxed';
		}
		$container_style = '';
		if ( ! empty( $layout['max_width'] ) ) {
			$container_style = ' style="max-width:' . esc_attr( self::unit( $layout['max_width'] ) ) . ';margin-left:auto;margin-right:auto;"';
		}

		$inner_style = '';
		$inner_parts = array();
		if ( ! empty( $layout['content_width'] ) ) {
			$inner_parts[] = 'max-width:' . self::unit( $layout['content_width'] );
			$inner_parts[] = 'margin-left:auto;margin-right:auto';
		}
		self::collect_spacing_css( $spacing['padding'] ?? array(), 'padding', $inner_parts );
		if ( $inner_parts ) {
			$inner_style = ' style="' . esc_attr( implode( ';', $inner_parts ) ) . '"';
		}

		$out  = '<section class="wdcs-cb-section"';
		if ( $section_style ) {
			$out .= ' style="' . esc_attr( $section_style ) . '"';
		}
		$out .= '>';
		$out .= '<div class="' . esc_attr( $container_class ) . '"' . $container_style . '>';
		$out .= '<div class="wdcs-cb-inner"' . $inner_style . '>';

		// Header group.
		if ( ! empty( $s['title']['text'] ) || ! empty( $s['subtitle']['text'] ) || ! empty( $s['description']['content'] ) ) {
			$out .= '<div class="wdcs-cb-header-group">';
			if ( ! empty( $s['title']['text'] ) )       $out .= self::render_title( $s['title'] );
			if ( ! empty( $s['subtitle']['text'] ) )    $out .= self::render_subtitle( $s['subtitle'] );
			if ( ! empty( $s['description']['content'] ) ) $out .= self::render_description( $s['description'] );
			$out .= '</div>';
		}

		// Columns.
		$left  = $s['columns']['left']  ?? array();
		$right = $s['columns']['right'] ?? array();
		if ( ! empty( $left ) || ! empty( $right ) ) {
			$out .= self::render_columns( $s['column_layout'] ?? '50-50', $left, $right, $layout['column_gap'] ?? '' );
		}

		$out .= '</div></div></section>';
		return $out;
	}

	private static function build_section_style( array $bg, array $margin ): string {
		$parts = array();

		if ( ( $bg['type'] ?? 'color' ) === 'color' ) {
			if ( ! empty( $bg['color'] ) ) $parts[] = 'background-color:' . $bg['color'];
		} else {
			if ( ! empty( $bg['image_url'] ) ) {
				$parts[] = 'background-image:url(' . esc_url( $bg['image_url'] ) . ')';
				$parts[] = 'background-position:' . ( $bg['position'] ?? 'center center' );
				$parts[] = 'background-size:'     . ( $bg['size']     ?? 'cover' );
				$parts[] = 'background-repeat:'   . ( $bg['repeat']   ?? 'no-repeat' );
			}
		}

		self::collect_spacing_css( $margin, 'margin', $parts );
		return implode( ';', $parts );
	}

	/* ------------------------------------------------------------------ */
	/*  Header elements                                                     */
	/* ------------------------------------------------------------------ */

	private static function render_title( array $t ): string {
		$style = self::text_style( $t );
		return '<h2 class="wdcs-cb-title"' . $style . '>' . esc_html( $t['text'] ) . '</h2>';
	}

	private static function render_subtitle( array $t ): string {
		$style = self::text_style( $t );
		return '<h3 class="wdcs-cb-subtitle"' . $style . '>' . esc_html( $t['text'] ) . '</h3>';
	}

	private static function render_description( array $d ): string {
		$parts = array();
		if ( ! empty( $d['color'] ) )       $parts[] = 'color:'       . $d['color'];
		if ( ! empty( $d['font_size'] ) )   $parts[] = 'font-size:'   . self::unit( $d['font_size'] );
		if ( ! empty( $d['font_weight'] ) ) $parts[] = 'font-weight:' . $d['font_weight'];
		if ( ! empty( $d['line_height'] ) ) $parts[] = 'line-height:' . $d['line_height'];
		if ( ! empty( $d['alignment'] ) )   $parts[] = 'text-align:'  . $d['alignment'];
		self::collect_spacing_css( $d['margin']  ?? array(), 'margin',  $parts );
		self::collect_spacing_css( $d['padding'] ?? array(), 'padding', $parts );
		$style = $parts ? ' style="' . esc_attr( implode( ';', $parts ) ) . '"' : '';
		return '<div class="wdcs-cb-description"' . $style . '>' . wp_kses_post( $d['content'] ) . '</div>';
	}

	private static function text_style( array $t ): string {
		$parts = array();
		if ( ! empty( $t['color'] ) )       $parts[] = 'color:'       . $t['color'];
		if ( ! empty( $t['font_size'] ) )   $parts[] = 'font-size:'   . self::unit( $t['font_size'] );
		if ( ! empty( $t['font_weight'] ) ) $parts[] = 'font-weight:' . $t['font_weight'];
		if ( ! empty( $t['line_height'] ) ) $parts[] = 'line-height:' . $t['line_height'];
		if ( ! empty( $t['alignment'] ) )   $parts[] = 'text-align:'  . $t['alignment'];
		self::collect_spacing_css( $t['margin']  ?? array(), 'margin',  $parts );
		self::collect_spacing_css( $t['padding'] ?? array(), 'padding', $parts );
		return $parts ? ' style="' . esc_attr( implode( ';', $parts ) ) . '"' : '';
	}

	/* ------------------------------------------------------------------ */
	/*  Columns                                                             */
	/* ------------------------------------------------------------------ */

	private static function render_columns( string $layout, array $left, array $right, string $gap ): string {
		$cols  = explode( '-', $layout );
		$lw    = ( (int) ( $cols[0] ?? 50 ) ) . '%';
		$rw    = ( (int) ( $cols[1] ?? 50 ) ) . '%';
		$gap_v = $gap ? self::unit( $gap ) : '30px';

		$out  = '<div class="wdcs-cb-columns" style="gap:' . esc_attr( $gap_v ) . '">';
		$out .= '<div class="wdcs-cb-col" style="flex:0 0 ' . esc_attr( $lw ) . ';max-width:' . esc_attr( $lw ) . '">';
		foreach ( $left as $block ) $out .= self::render_block( $block );
		$out .= '</div>';
		$out .= '<div class="wdcs-cb-col" style="flex:0 0 ' . esc_attr( $rw ) . ';max-width:' . esc_attr( $rw ) . '">';
		foreach ( $right as $block ) $out .= self::render_block( $block );
		$out .= '</div>';
		$out .= '</div>';
		return $out;
	}

	/* ------------------------------------------------------------------ */
	/*  Blocks                                                              */
	/* ------------------------------------------------------------------ */

	private static function render_block( array $block ): string {
		switch ( $block['type'] ?? '' ) {
			case 'text':   return self::render_text_block( $block );
			case 'image':  return self::render_image_block( $block );
			case 'button': return self::render_button_block( $block );
		}
		return '';
	}

	private static function render_text_block( array $b ): string {
		$parts = array();
		if ( ! empty( $b['color'] ) )       $parts[] = 'color:'       . $b['color'];
		if ( ! empty( $b['font_size'] ) )   $parts[] = 'font-size:'   . self::unit( $b['font_size'] );
		if ( ! empty( $b['font_weight'] ) ) $parts[] = 'font-weight:' . $b['font_weight'];
		if ( ! empty( $b['line_height'] ) ) $parts[] = 'line-height:' . $b['line_height'];
		if ( ! empty( $b['alignment'] ) )   $parts[] = 'text-align:'  . $b['alignment'];
		self::collect_spacing_css( $b['margin']  ?? array(), 'margin',  $parts );
		self::collect_spacing_css( $b['padding'] ?? array(), 'padding', $parts );
		$style = $parts ? ' style="' . esc_attr( implode( ';', $parts ) ) . '"' : '';
		return '<div class="wdcs-cb-block wdcs-cb-text"' . $style . '>' . wp_kses_post( $b['content'] ?? '' ) . '</div>';
	}

	private static function render_image_block( array $b ): string {
		if ( empty( $b['image_url'] ) ) return '';

		$img_parts = array();
		if ( ! empty( $b['width'] ) )         $img_parts[] = 'width:'         . self::unit( $b['width'] );
		if ( ! empty( $b['border_radius'] ) ) $img_parts[] = 'border-radius:' . self::unit( $b['border_radius'] );

		$wrap_parts = array();
		if ( ! empty( $b['alignment'] ) ) $wrap_parts[] = 'text-align:' . $b['alignment'];
		self::collect_spacing_css( $b['margin']  ?? array(), 'margin',  $wrap_parts );
		self::collect_spacing_css( $b['padding'] ?? array(), 'padding', $wrap_parts );

		$img_style  = $img_parts  ? ' style="' . esc_attr( implode( ';', $img_parts ) ) . '"'  : '';
		$wrap_style = $wrap_parts ? ' style="' . esc_attr( implode( ';', $wrap_parts ) ) . '"' : '';

		return '<div class="wdcs-cb-block wdcs-cb-image"' . $wrap_style . '>'
			. '<img src="' . esc_url( $b['image_url'] ) . '" alt="' . esc_attr( $b['alt'] ?? '' ) . '"' . $img_style . '>'
			. '</div>';
	}

	private static function render_button_block( array $b ): string {
		if ( empty( $b['text'] ) ) return '';

		$btn_parts = array();
		if ( ! empty( $b['bg_color'] ) )     $btn_parts[] = 'background-color:' . $b['bg_color'];
		if ( ! empty( $b['text_color'] ) )   $btn_parts[] = 'color:'            . $b['text_color'];
		if ( ! empty( $b['font_size'] ) )    $btn_parts[] = 'font-size:'        . self::unit( $b['font_size'] );
		if ( ! empty( $b['font_weight'] ) )  $btn_parts[] = 'font-weight:'      . $b['font_weight'];
		if ( ! empty( $b['border_radius'] ) ) $btn_parts[] = 'border-radius:'   . self::unit( $b['border_radius'] );
		self::collect_spacing_css( $b['padding'] ?? array(), 'padding', $btn_parts );

		$wrap_parts = array();
		if ( ! empty( $b['alignment'] ) ) $wrap_parts[] = 'text-align:' . $b['alignment'];
		self::collect_spacing_css( $b['margin'] ?? array(), 'margin', $wrap_parts );

		$btn_style  = $btn_parts  ? ' style="' . esc_attr( implode( ';', $btn_parts ) ) . '"'  : '';
		$wrap_style = $wrap_parts ? ' style="' . esc_attr( implode( ';', $wrap_parts ) ) . '"' : '';

		$hover_attrs = '';
		if ( ! empty( $b['hover_bg_color'] ) )   $hover_attrs .= ' data-hover-bg="' . esc_attr( $b['hover_bg_color'] ) . '"';
		if ( ! empty( $b['hover_text_color'] ) ) $hover_attrs .= ' data-hover-color="' . esc_attr( $b['hover_text_color'] ) . '"';

		return '<div class="wdcs-cb-block wdcs-cb-button"' . $wrap_style . '>'
			. '<a href="' . esc_url( $b['url'] ?? '#' ) . '" class="wdcs-cb-btn"' . $btn_style . $hover_attrs . '>'
			. esc_html( $b['text'] )
			. '</a></div>';
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                             */
	/* ------------------------------------------------------------------ */

	private static function unit( string $v ): string {
		if ( '' === $v ) return '';
		return is_numeric( $v ) ? $v . 'px' : $v;
	}

	private static function collect_spacing_css( array $s, string $prop, array &$parts ): void {
		if ( empty( $s['top'] ) && empty( $s['right'] ) && empty( $s['bottom'] ) && empty( $s['left'] ) ) return;
		$u = $s['unit'] ?? 'px';
		$t = ! empty( $s['top'] )    ? $s['top']    . $u : '0';
		$r = ! empty( $s['right'] )  ? $s['right']  . $u : '0';
		$b = ! empty( $s['bottom'] ) ? $s['bottom'] . $u : '0';
		$l = ! empty( $s['left'] )   ? $s['left']   . $u : '0';
		$parts[] = "$prop:$t $r $b $l";
	}
}
