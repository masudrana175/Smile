<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sanitizes and validates the raw decoded JSON from the builder
 * before it is stored as post meta.
 */
class WDCS_CB_Save {

	private static $allowed_units       = array( 'px', '%', 'em', 'rem', 'vw', 'vh' );
	private static $allowed_alignments  = array( 'left', 'center', 'right', 'justify' );
	private static $allowed_font_weights = array( '100','200','300','400','500','600','700','800','900','normal','bold' );

	public static function sanitize_sections( array $raw ): array {
		$out = array();
		foreach ( $raw as $section ) {
			if ( is_array( $section ) ) {
				$out[] = self::sanitize_section( $section );
			}
		}
		return $out;
	}

	private static function sanitize_section( array $s ): array {
		return array(
			'id'            => sanitize_key( $s['id'] ?? '' ),
			'label'         => sanitize_text_field( $s['label'] ?? '' ),
			'collapsed'     => ! empty( $s['collapsed'] ),
			'background'    => self::sanitize_background( $s['background'] ?? array() ),
			'layout'        => self::sanitize_layout( $s['layout'] ?? array() ),
			'spacing'       => array(
				'margin'  => self::sanitize_spacing( $s['spacing']['margin']  ?? array() ),
				'padding' => self::sanitize_spacing( $s['spacing']['padding'] ?? array() ),
			),
			'title'         => self::sanitize_text_element( $s['title']    ?? array() ),
			'subtitle'      => self::sanitize_text_element( $s['subtitle'] ?? array() ),
			'description'   => self::sanitize_description( $s['description'] ?? array() ),
			'column_layout' => self::sanitize_column_layout( $s['column_layout'] ?? '50-50' ),
			'columns'       => array(
				'left'  => self::sanitize_blocks( $s['columns']['left']  ?? array() ),
				'right' => self::sanitize_blocks( $s['columns']['right'] ?? array() ),
			),
		);
	}

	private static function sanitize_background( array $b ): array {
		static $positions = array( 'top left','top center','top right','center left','center center','center right','bottom left','bottom center','bottom right' );
		static $sizes     = array( 'cover','contain','auto' );
		static $repeats   = array( 'no-repeat','repeat','repeat-x','repeat-y' );
		return array(
			'type'     => in_array( $b['type'] ?? 'color', array( 'color', 'image' ), true ) ? $b['type'] : 'color',
			'color'    => sanitize_hex_color( $b['color'] ?? '' ) ?? '',
			'image_url'=> esc_url_raw( $b['image_url'] ?? '' ),
			'position' => in_array( $b['position'] ?? 'center center', $positions, true ) ? $b['position'] : 'center center',
			'size'     => in_array( $b['size'] ?? 'cover', $sizes, true ) ? $b['size'] : 'cover',
			'repeat'   => in_array( $b['repeat'] ?? 'no-repeat', $repeats, true ) ? $b['repeat'] : 'no-repeat',
		);
	}

	private static function sanitize_layout( array $l ): array {
		return array(
			'container_width' => in_array( $l['container_width'] ?? 'full', array( 'full', 'boxed' ), true ) ? $l['container_width'] : 'full',
			'max_width'       => self::sanitize_css_value( $l['max_width']     ?? '' ),
			'content_width'   => self::sanitize_css_value( $l['content_width'] ?? '' ),
			'column_gap'      => self::sanitize_css_value( $l['column_gap']    ?? '' ),
		);
	}

	private static function sanitize_spacing( array $s ): array {
		return array(
			'top'    => self::sanitize_number( $s['top']    ?? '' ),
			'right'  => self::sanitize_number( $s['right']  ?? '' ),
			'bottom' => self::sanitize_number( $s['bottom'] ?? '' ),
			'left'   => self::sanitize_number( $s['left']   ?? '' ),
			'unit'   => in_array( $s['unit'] ?? 'px', self::$allowed_units, true ) ? $s['unit'] : 'px',
		);
	}

	private static function sanitize_text_element( array $e ): array {
		return array(
			'text'        => sanitize_textarea_field( $e['text'] ?? '' ),
			'color'       => sanitize_hex_color( $e['color'] ?? '' ) ?? '',
			'font_size'   => self::sanitize_css_value( $e['font_size']   ?? '' ),
			'font_weight' => in_array( $e['font_weight'] ?? '', self::$allowed_font_weights, true ) ? $e['font_weight'] : '',
			'line_height' => self::sanitize_css_value( $e['line_height'] ?? '' ),
			'alignment'   => in_array( $e['alignment'] ?? 'left', self::$allowed_alignments, true ) ? $e['alignment'] : 'left',
			'margin'      => self::sanitize_spacing( $e['margin']  ?? array() ),
			'padding'     => self::sanitize_spacing( $e['padding'] ?? array() ),
		);
	}

	private static function sanitize_description( array $d ): array {
		return array(
			'content'     => wp_kses_post( $d['content'] ?? '' ),
			'color'       => sanitize_hex_color( $d['color'] ?? '' ) ?? '',
			'font_size'   => self::sanitize_css_value( $d['font_size']   ?? '' ),
			'font_weight' => in_array( $d['font_weight'] ?? '', self::$allowed_font_weights, true ) ? $d['font_weight'] : '',
			'line_height' => self::sanitize_css_value( $d['line_height'] ?? '' ),
			'alignment'   => in_array( $d['alignment'] ?? 'left', self::$allowed_alignments, true ) ? $d['alignment'] : 'left',
			'margin'      => self::sanitize_spacing( $d['margin']  ?? array() ),
			'padding'     => self::sanitize_spacing( $d['padding'] ?? array() ),
		);
	}

	private static function sanitize_column_layout( string $v ): string {
		$allowed = array( '50-50', '60-40', '40-60', '70-30', '30-70' );
		return in_array( $v, $allowed, true ) ? $v : '50-50';
	}

	private static function sanitize_blocks( array $blocks ): array {
		$out = array();
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) continue;
			$type = sanitize_key( $block['type'] ?? '' );
			switch ( $type ) {
				case 'text':   $out[] = self::sanitize_text_block( $block );   break;
				case 'image':  $out[] = self::sanitize_image_block( $block );  break;
				case 'button': $out[] = self::sanitize_button_block( $block ); break;
			}
		}
		return $out;
	}

	private static function sanitize_text_block( array $b ): array {
		return array(
			'type'        => 'text',
			'id'          => sanitize_key( $b['id'] ?? '' ),
			'content'     => wp_kses_post( $b['content'] ?? '' ),
			'color'       => sanitize_hex_color( $b['color'] ?? '' ) ?? '',
			'font_size'   => self::sanitize_css_value( $b['font_size']   ?? '' ),
			'font_weight' => in_array( $b['font_weight'] ?? '', self::$allowed_font_weights, true ) ? $b['font_weight'] : '',
			'line_height' => self::sanitize_css_value( $b['line_height'] ?? '' ),
			'alignment'   => in_array( $b['alignment'] ?? 'left', self::$allowed_alignments, true ) ? $b['alignment'] : 'left',
			'margin'      => self::sanitize_spacing( $b['margin']  ?? array() ),
			'padding'     => self::sanitize_spacing( $b['padding'] ?? array() ),
			'collapsed'   => ! empty( $b['collapsed'] ),
		);
	}

	private static function sanitize_image_block( array $b ): array {
		return array(
			'type'          => 'image',
			'id'            => sanitize_key( $b['id'] ?? '' ),
			'image_url'     => esc_url_raw( $b['image_url'] ?? '' ),
			'alt'           => sanitize_text_field( $b['alt'] ?? '' ),
			'width'         => self::sanitize_css_value( $b['width'] ?? '' ),
			'border_radius' => self::sanitize_css_value( $b['border_radius'] ?? '' ),
			'alignment'     => in_array( $b['alignment'] ?? 'left', array( 'left', 'center', 'right' ), true ) ? $b['alignment'] : 'left',
			'margin'        => self::sanitize_spacing( $b['margin']  ?? array() ),
			'padding'       => self::sanitize_spacing( $b['padding'] ?? array() ),
			'collapsed'     => ! empty( $b['collapsed'] ),
		);
	}

	private static function sanitize_button_block( array $b ): array {
		return array(
			'type'             => 'button',
			'id'               => sanitize_key( $b['id'] ?? '' ),
			'text'             => sanitize_text_field( $b['text'] ?? '' ),
			'url'              => esc_url_raw( $b['url'] ?? '' ),
			'bg_color'         => sanitize_hex_color( $b['bg_color']         ?? '' ) ?? '',
			'hover_bg_color'   => sanitize_hex_color( $b['hover_bg_color']   ?? '' ) ?? '',
			'text_color'       => sanitize_hex_color( $b['text_color']       ?? '' ) ?? '',
			'hover_text_color' => sanitize_hex_color( $b['hover_text_color'] ?? '' ) ?? '',
			'font_size'        => self::sanitize_css_value( $b['font_size']    ?? '' ),
			'font_weight'      => in_array( $b['font_weight'] ?? '', self::$allowed_font_weights, true ) ? $b['font_weight'] : '',
			'border_radius'    => self::sanitize_css_value( $b['border_radius'] ?? '' ),
			'alignment'        => in_array( $b['alignment'] ?? 'left', array( 'left', 'center', 'right' ), true ) ? $b['alignment'] : 'left',
			'margin'           => self::sanitize_spacing( $b['margin']  ?? array() ),
			'padding'          => self::sanitize_spacing( $b['padding'] ?? array() ),
			'collapsed'        => ! empty( $b['collapsed'] ),
		);
	}

	// Sanitize a bare number (no unit) — returns empty string if not a valid number.
	private static function sanitize_number( $v ): string {
		if ( '' === $v || null === $v ) return '';
		$f = (float) $v;
		return is_nan( $f ) ? '' : (string) $f;
	}

	// Sanitize a CSS dimension value: number optionally followed by a unit.
	private static function sanitize_css_value( string $v ): string {
		$v = trim( $v );
		if ( '' === $v ) return '';
		if ( preg_match( '/^-?\d*\.?\d+(px|%|em|rem|vw|vh|pt)?$/', $v ) ) {
			return $v;
		}
		return '';
	}
}
