<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Abstract base: all HTML-rendering methods for the Content Builder admin UI.
 * Loaded before WDCS_CB_Admin_UI so that class can extend it and inherit
 * section_html(), block_html(), and every private helper.
 */
abstract class WDCS_CB_Admin_UI_Base {

	// -------------------------------------------------------------------------
	// Public API (called from outside: meta-box render, JS template generation)
	// -------------------------------------------------------------------------

	public static function section_html( $data = array() ) {
		$id        = self::v( $data, 'id' ) ?: uniqid( 'sec_' );
		$label     = self::v( $data, 'label' );
		$collapsed = ! empty( $data['collapsed'] );

		$bg           = is_array( $data['background'] ?? null )  ? $data['background']  : array();
		$layout       = is_array( $data['layout']     ?? null )  ? $data['layout']      : array();
		$spacing      = is_array( $data['spacing']    ?? null )  ? $data['spacing']     : array();
		$title        = is_array( $data['title']      ?? null )  ? $data['title']       : array();
		$subtitle     = is_array( $data['subtitle']   ?? null )  ? $data['subtitle']    : array();
		$description  = is_array( $data['description']?? null )  ? $data['description'] : array();
		$col_layout   = self::v( $data, 'column_layout', '50-50' );
		$columns      = is_array( $data['columns']    ?? null )  ? $data['columns']     : array();

		$body_style = $collapsed ? 'display:none' : 'display:block';

		ob_start();
		?>
		<div class="wdcs-cb-section" data-id="<?php echo esc_attr( $id ); ?>">

			<div class="wdcs-cb-section-header">
				<span class="wdcs-cb-drag dashicons dashicons-menu"></span>
				<span class="wdcs-cb-section-toggle dashicons <?php echo $collapsed ? 'dashicons-arrow-down-alt2' : 'dashicons-arrow-up-alt2'; ?>"></span>
				<input type="text"
					class="wdcs-cb-section-label"
					data-field="label"
					value="<?php echo esc_attr( $label ); ?>"
					placeholder="Section label...">
				<div class="wdcs-cb-section-actions">
					<button type="button" class="wdcs-cb-duplicate-section button button-small">Duplicate</button>
					<button type="button" class="wdcs-cb-delete-section">&times;</button>
				</div>
			</div>

			<div class="wdcs-cb-section-body" style="<?php echo esc_attr( $body_style ); ?>">

				<div class="wdcs-cb-tabs">
					<button type="button" class="wdcs-cb-tab active" data-tab="background">Background</button>
					<button type="button" class="wdcs-cb-tab" data-tab="layout">Layout</button>
					<button type="button" class="wdcs-cb-tab" data-tab="spacing">Spacing</button>
					<button type="button" class="wdcs-cb-tab" data-tab="title">Title</button>
					<button type="button" class="wdcs-cb-tab" data-tab="subtitle">Subtitle</button>
					<button type="button" class="wdcs-cb-tab" data-tab="description">Description</button>
					<button type="button" class="wdcs-cb-tab" data-tab="columns">Columns</button>
				</div>

				<div class="wdcs-cb-tab-pane active" data-pane="background">
					<?php echo self::tab_background( $bg, $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="wdcs-cb-tab-pane" data-pane="layout">
					<?php echo self::tab_layout( $layout ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="wdcs-cb-tab-pane" data-pane="spacing">
					<?php echo self::tab_spacing( $spacing ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="wdcs-cb-tab-pane" data-pane="title">
					<?php echo self::tab_title( $title, 'title', $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="wdcs-cb-tab-pane" data-pane="subtitle">
					<?php echo self::tab_title( $subtitle, 'subtitle', $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="wdcs-cb-tab-pane" data-pane="description">
					<?php echo self::tab_description( $description, $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="wdcs-cb-tab-pane" data-pane="columns">
					<?php echo self::tab_columns( $col_layout, $columns ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

			</div><!-- /.wdcs-cb-section-body -->

		</div><!-- /.wdcs-cb-section -->
		<?php
		return ob_get_clean();
	}

	public static function block_html( $data = array() ) {
		$type      = self::v( $data, 'type', 'text' );
		$id        = self::v( $data, 'id' ) ?: uniqid( 'blk_' );
		$collapsed = ! empty( $data['collapsed'] );

		$type_labels = array(
			'text'   => 'Text Block',
			'image'  => 'Image Block',
			'button' => 'Button Block',
		);
		$type_label = $type_labels[ $type ] ?? ucfirst( $type ) . ' Block';
		$body_style = $collapsed ? 'display:none' : 'display:block';

		ob_start();
		?>
		<div class="wdcs-cb-block" data-id="<?php echo esc_attr( $id ); ?>" data-type="<?php echo esc_attr( $type ); ?>">

			<div class="wdcs-cb-block-header">
				<span class="wdcs-cb-drag dashicons dashicons-menu"></span>
				<span class="wdcs-cb-block-toggle dashicons dashicons-arrow-down-alt2"></span>
				<span class="wdcs-cb-block-type-label"><?php echo esc_html( $type_label ); ?></span>
				<button type="button" class="wdcs-cb-delete-block">&times;</button>
			</div>

			<div class="wdcs-cb-block-body" style="<?php echo esc_attr( $body_style ); ?>">
				<?php
				switch ( $type ) {
					case 'image':
						echo self::block_image_fields( $data ); // phpcs:ignore WordPress.Security.EscapeOutput
						break;
					case 'button':
						echo self::block_button_fields( $data ); // phpcs:ignore WordPress.Security.EscapeOutput
						break;
					case 'text':
					default:
						echo self::block_text_fields( $data ); // phpcs:ignore WordPress.Security.EscapeOutput
						break;
				}
				?>
			</div><!-- /.wdcs-cb-block-body -->

		</div><!-- /.wdcs-cb-block -->
		<?php
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Tab renderers
	// -------------------------------------------------------------------------

	protected static function tab_background( $bg, $section_id = '' ) {
		$type      = self::v( $bg, 'type', 'color' );
		$color     = self::v( $bg, 'color' );
		$image_url = self::v( $bg, 'image_url' );
		$position  = self::v( $bg, 'position', 'center center' );
		$size      = self::v( $bg, 'size', 'cover' );
		$repeat    = self::v( $bg, 'repeat', 'no-repeat' );

		$is_image = ( 'image' === $type );

		$color_row_style = $is_image ? 'display:none' : 'display:block';
		$image_row_style = $is_image ? 'display:block' : 'display:none';
		$preview_style   = ( $is_image && '' !== $image_url ) ? 'display:block' : 'display:none';

		$positions = array(
			'top left'      => 'Top Left',    'top center'    => 'Top Center',
			'top right'     => 'Top Right',   'center left'   => 'Center Left',
			'center center' => 'Center',      'center right'  => 'Center Right',
			'bottom left'   => 'Bottom Left', 'bottom center' => 'Bottom Center',
			'bottom right'  => 'Bottom Right',
		);

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Background Type', 'smile' ); ?></label>
			<label>
				<input type="radio" data-field="background.type"
					name="wdcs_bg_type_<?php echo esc_attr( $section_id ); ?>"
					value="color" <?php checked( $type, 'color' ); ?>>
				<?php esc_html_e( 'Color', 'smile' ); ?>
			</label>
			<label>
				<input type="radio" data-field="background.type"
					name="wdcs_bg_type_<?php echo esc_attr( $section_id ); ?>"
					value="image" <?php checked( $type, 'image' ); ?>>
				<?php esc_html_e( 'Image', 'smile' ); ?>
			</label>
		</div>

		<div class="wdcs-cb-field-row wdcs-cb-bg-color-row" style="<?php echo esc_attr( $color_row_style ); ?>">
			<label><?php esc_html_e( 'Background Color', 'smile' ); ?></label>
			<input type="text" class="wdcs-color-picker" data-field="background.color" value="<?php echo esc_attr( $color ); ?>">
		</div>

		<div class="wdcs-cb-field-row wdcs-cb-bg-image-row" style="<?php echo esc_attr( $image_row_style ); ?>">
			<label><?php esc_html_e( 'Background Image', 'smile' ); ?></label>
			<div class="wdcs-cb-media-row">
				<input type="text" data-field="background.image_url" value="<?php echo esc_attr( $image_url ); ?>"
					class="regular-text" placeholder="<?php esc_attr_e( 'Image URL', 'smile' ); ?>">
				<button type="button" class="button wdcs-cb-media-btn"><?php esc_html_e( 'Choose Image', 'smile' ); ?></button>
			</div>
			<div class="wdcs-cb-bg-preview" style="<?php echo esc_attr( $preview_style ); ?>">
				<?php if ( '' !== $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" style="max-width:100%;max-height:80px;" alt="">
				<?php endif; ?>
			</div>
		</div>

		<div class="wdcs-cb-field-row wdcs-cb-bg-image-row" style="<?php echo esc_attr( $image_row_style ); ?>">
			<label><?php esc_html_e( 'Position', 'smile' ); ?></label>
			<select data-field="background.position">
				<?php foreach ( $positions as $val => $lbl ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $position, $val ); ?>><?php echo esc_html( $lbl ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="wdcs-cb-field-row wdcs-cb-bg-image-row" style="<?php echo esc_attr( $image_row_style ); ?>">
			<label><?php esc_html_e( 'Size', 'smile' ); ?></label>
			<select data-field="background.size">
				<option value="cover"   <?php selected( $size, 'cover' ); ?>><?php esc_html_e( 'Cover', 'smile' ); ?></option>
				<option value="contain" <?php selected( $size, 'contain' ); ?>><?php esc_html_e( 'Contain', 'smile' ); ?></option>
				<option value="auto"    <?php selected( $size, 'auto' ); ?>><?php esc_html_e( 'Auto', 'smile' ); ?></option>
			</select>
		</div>

		<div class="wdcs-cb-field-row wdcs-cb-bg-image-row" style="<?php echo esc_attr( $image_row_style ); ?>">
			<label><?php esc_html_e( 'Repeat', 'smile' ); ?></label>
			<select data-field="background.repeat">
				<option value="no-repeat" <?php selected( $repeat, 'no-repeat' ); ?>><?php esc_html_e( 'No Repeat', 'smile' ); ?></option>
				<option value="repeat"    <?php selected( $repeat, 'repeat' ); ?>><?php esc_html_e( 'Repeat', 'smile' ); ?></option>
				<option value="repeat-x"  <?php selected( $repeat, 'repeat-x' ); ?>><?php esc_html_e( 'Repeat X', 'smile' ); ?></option>
				<option value="repeat-y"  <?php selected( $repeat, 'repeat-y' ); ?>><?php esc_html_e( 'Repeat Y', 'smile' ); ?></option>
			</select>
		</div>
		<?php
		return ob_get_clean();
	}

	protected static function tab_layout( $layout ) {
		$max_width  = self::v( $layout, 'max_width' );
		$column_gap = self::v( $layout, 'column_gap' );

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Max Width', 'smile' ); ?></label>
			<input type="text" data-field="layout.max_width" value="<?php echo esc_attr( $max_width ); ?>"
				class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 1200px', 'smile' ); ?>">
		</div>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Column Gap', 'smile' ); ?></label>
			<input type="text" data-field="layout.column_gap" value="<?php echo esc_attr( $column_gap ); ?>"
				class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 30px', 'smile' ); ?>">
		</div>
		<?php
		return ob_get_clean();
	}

	protected static function tab_spacing( $spacing ) {
		$margin  = is_array( $spacing['margin']  ?? null ) ? $spacing['margin']  : array();
		$padding = is_array( $spacing['padding'] ?? null ) ? $spacing['padding'] : array();

		ob_start();
		echo self::spacing_row( 'spacing.margin', $margin );   // phpcs:ignore WordPress.Security.EscapeOutput
		echo self::spacing_row( 'spacing.padding', $padding ); // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}

	protected static function tab_title( $title, $key, $section_id = '' ) {
		$text      = self::v( $title, 'text' );
		$alignment = self::v( $title, 'alignment', 'left' );
		$margin    = is_array( $title['margin']  ?? null ) ? $title['margin']  : array();
		$padding   = is_array( $title['padding'] ?? null ) ? $title['padding'] : array();

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Text', 'smile' ); ?></label>
			<input type="text" data-field="<?php echo esc_attr( $key . '.text' ); ?>"
				value="<?php echo esc_attr( $text ); ?>" class="large-text"
				placeholder="<?php esc_attr_e( 'Enter text...', 'smile' ); ?>">
		</div>

		<?php echo self::color_field( $key . '.color', self::v( $title, 'color' ), __( 'Color', 'smile' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Font Size', 'smile' ); ?></label>
			<input type="text" data-field="<?php echo esc_attr( $key . '.font_size' ); ?>"
				value="<?php echo esc_attr( self::v( $title, 'font_size' ) ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. 36px', 'smile' ); ?>">
		</div>

		<?php echo self::font_weight_field( $key . '.font_weight', self::v( $title, 'font_weight' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Line Height', 'smile' ); ?></label>
			<input type="text" data-field="<?php echo esc_attr( $key . '.line_height' ); ?>"
				value="<?php echo esc_attr( self::v( $title, 'line_height' ) ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. 1.4', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Alignment', 'smile' ); ?></label>
			<?php foreach ( array( 'left', 'center', 'right', 'justify' ) as $align ) : ?>
				<label>
					<input type="radio" data-field="<?php echo esc_attr( $key . '.alignment' ); ?>"
						name="wdcs_<?php echo esc_attr( $key ); ?>_align_<?php echo esc_attr( $section_id ); ?>"
						value="<?php echo esc_attr( $align ); ?>" <?php checked( $alignment, $align ); ?>>
					<?php echo esc_html( ucfirst( $align ) ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<?php
		echo self::spacing_row( $key . '.margin', $margin );   // phpcs:ignore WordPress.Security.EscapeOutput
		echo self::spacing_row( $key . '.padding', $padding ); // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}

	protected static function tab_description( $desc, $section_id = '' ) {
		$content   = self::v( $desc, 'content' );
		$alignment = self::v( $desc, 'alignment', 'left' );
		$margin    = is_array( $desc['margin']  ?? null ) ? $desc['margin']  : array();
		$padding   = is_array( $desc['padding'] ?? null ) ? $desc['padding'] : array();
		$editor_id = 'wdcs_desc_' . $section_id;

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Content', 'smile' ); ?></label>
			<textarea id="<?php echo esc_attr( $editor_id ); ?>" data-field="description.content"
				rows="5" class="large-text wdcs-wysiwyg"><?php echo esc_textarea( $content ); ?></textarea>
		</div>

		<?php echo self::color_field( 'description.color', self::v( $desc, 'color' ), __( 'Color', 'smile' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Font Size', 'smile' ); ?></label>
			<input type="text" data-field="description.font_size"
				value="<?php echo esc_attr( self::v( $desc, 'font_size' ) ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. 16px', 'smile' ); ?>">
		</div>

		<?php echo self::font_weight_field( 'description.font_weight', self::v( $desc, 'font_weight' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Line Height', 'smile' ); ?></label>
			<input type="text" data-field="description.line_height"
				value="<?php echo esc_attr( self::v( $desc, 'line_height' ) ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. 1.6', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Alignment', 'smile' ); ?></label>
			<?php foreach ( array( 'left', 'center', 'right', 'justify' ) as $align ) : ?>
				<label>
					<input type="radio" data-field="description.alignment"
						name="wdcs_desc_align_<?php echo esc_attr( $section_id ); ?>"
						value="<?php echo esc_attr( $align ); ?>" <?php checked( $alignment, $align ); ?>>
					<?php echo esc_html( ucfirst( $align ) ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<?php
		echo self::spacing_row( 'description.margin', $margin );   // phpcs:ignore WordPress.Security.EscapeOutput
		echo self::spacing_row( 'description.padding', $padding ); // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}

	protected static function tab_columns( $column_layout, $columns ) {
		$col_layout   = $column_layout ?: '50-50';
		$left_blocks  = is_array( $columns['left']  ?? null ) ? $columns['left']  : array();
		$right_blocks = is_array( $columns['right'] ?? null ) ? $columns['right'] : array();

		$layout_options = array( '50-50', '60-40', '40-60', '70-30', '30-70' );

		ob_start();
		?>
		<div class="wdcs-cb-col-layout-picker">
			<label><?php esc_html_e( 'Column Layout', 'smile' ); ?></label>
			<div class="wdcs-cb-col-layouts">
				<?php foreach ( $layout_options as $opt ) : ?>
					<button type="button"
						class="wdcs-cb-col-option<?php echo ( $col_layout === $opt ) ? ' active' : ''; ?>"
						data-value="<?php echo esc_attr( $opt ); ?>">
						<?php echo esc_html( str_replace( '-', ' / ', $opt ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<input type="hidden" data-field="column_layout" value="<?php echo esc_attr( $col_layout ); ?>">
		</div>

		<div class="wdcs-cb-two-cols">

			<div class="wdcs-cb-col-builder" data-col="left">
				<div class="wdcs-cb-col-header"><?php esc_html_e( 'Left Column', 'smile' ); ?></div>
				<div class="wdcs-cb-blocks-list">
					<?php foreach ( $left_blocks as $block ) : ?>
						<?php echo self::block_html( $block ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endforeach; ?>
				</div>
				<div class="wdcs-cb-add-block-bar">
					<button type="button" class="wdcs-cb-add-block button button-small" data-type="text">+ Text</button>
					<button type="button" class="wdcs-cb-add-block button button-small" data-type="image">+ Image</button>
					<button type="button" class="wdcs-cb-add-block button button-small" data-type="button">+ Button</button>
				</div>
			</div>

			<div class="wdcs-cb-col-builder" data-col="right">
				<div class="wdcs-cb-col-header"><?php esc_html_e( 'Right Column', 'smile' ); ?></div>
				<div class="wdcs-cb-blocks-list">
					<?php foreach ( $right_blocks as $block ) : ?>
						<?php echo self::block_html( $block ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endforeach; ?>
				</div>
				<div class="wdcs-cb-add-block-bar">
					<button type="button" class="wdcs-cb-add-block button button-small" data-type="text">+ Text</button>
					<button type="button" class="wdcs-cb-add-block button button-small" data-type="image">+ Image</button>
					<button type="button" class="wdcs-cb-add-block button button-small" data-type="button">+ Button</button>
				</div>
			</div>

		</div><!-- /.wdcs-cb-two-cols -->
		<?php
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Block field sets
	// -------------------------------------------------------------------------

	protected static function block_text_fields( $b ) {
		$content   = self::v( $b, 'content' );
		$alignment = self::v( $b, 'alignment', 'left' );
		$margin    = is_array( $b['margin']  ?? null ) ? $b['margin']  : array();
		$padding   = is_array( $b['padding'] ?? null ) ? $b['padding'] : array();
		$editor_id = 'wdcs_text_' . self::v( $b, 'id' );

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Content', 'smile' ); ?></label>
			<textarea id="<?php echo esc_attr( $editor_id ); ?>" data-field="content"
				rows="5" class="large-text wdcs-wysiwyg"><?php echo esc_textarea( $content ); ?></textarea>
		</div>

		<?php echo self::color_field( 'color', self::v( $b, 'color' ), __( 'Color', 'smile' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Font Size', 'smile' ); ?></label>
			<input type="text" data-field="font_size"
				value="<?php echo esc_attr( self::v( $b, 'font_size' ) ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. 16px', 'smile' ); ?>">
		</div>

		<?php echo self::font_weight_field( 'font_weight', self::v( $b, 'font_weight' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Line Height', 'smile' ); ?></label>
			<input type="text" data-field="line_height"
				value="<?php echo esc_attr( self::v( $b, 'line_height' ) ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. 1.6', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Alignment', 'smile' ); ?></label>
			<?php foreach ( array( 'left', 'center', 'right', 'justify' ) as $align ) : ?>
				<label>
					<input type="radio" data-field="alignment"
						name="wdcs_txt_align_<?php echo esc_attr( self::v( $b, 'id' ) ); ?>"
						value="<?php echo esc_attr( $align ); ?>" <?php checked( $alignment, $align ); ?>>
					<?php echo esc_html( ucfirst( $align ) ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<?php
		echo self::spacing_row( 'margin', $margin );   // phpcs:ignore WordPress.Security.EscapeOutput
		echo self::spacing_row( 'padding', $padding ); // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}

	protected static function block_image_fields( $b ) {
		$image_url     = self::v( $b, 'image_url' );
		$alt           = self::v( $b, 'alt' );
		$width         = self::v( $b, 'width' );
		$border_radius = self::v( $b, 'border_radius' );
		$alignment     = self::v( $b, 'alignment', 'left' );
		$margin        = is_array( $b['margin']  ?? null ) ? $b['margin']  : array();
		$padding       = is_array( $b['padding'] ?? null ) ? $b['padding'] : array();
		$preview_style = ( '' !== $image_url ) ? 'display:block' : 'display:none';

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Image', 'smile' ); ?></label>
			<div class="wdcs-cb-media-row">
				<input type="text" data-field="image_url" value="<?php echo esc_attr( $image_url ); ?>"
					class="regular-text" placeholder="<?php esc_attr_e( 'Image URL', 'smile' ); ?>">
				<button type="button" class="button wdcs-cb-media-btn"><?php esc_html_e( 'Choose Image', 'smile' ); ?></button>
			</div>
			<div class="wdcs-cb-image-preview" style="<?php echo esc_attr( $preview_style ); ?>">
				<?php if ( '' !== $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" style="max-width:100%;max-height:120px;" alt="">
				<?php endif; ?>
			</div>
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Alt Text', 'smile' ); ?></label>
			<input type="text" data-field="alt" value="<?php echo esc_attr( $alt ); ?>"
				class="regular-text" placeholder="<?php esc_attr_e( 'Alt text...', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Width', 'smile' ); ?></label>
			<input type="text" data-field="width" value="<?php echo esc_attr( $width ); ?>"
				class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 100%', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Border Radius', 'smile' ); ?></label>
			<input type="text" data-field="border_radius" value="<?php echo esc_attr( $border_radius ); ?>"
				class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 8px', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Alignment', 'smile' ); ?></label>
			<?php foreach ( array( 'left', 'center', 'right' ) as $align ) : ?>
				<label>
					<input type="radio" data-field="alignment"
						name="wdcs_img_align_<?php echo esc_attr( self::v( $b, 'id' ) ); ?>"
						value="<?php echo esc_attr( $align ); ?>" <?php checked( $alignment, $align ); ?>>
					<?php echo esc_html( ucfirst( $align ) ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<?php
		echo self::spacing_row( 'margin', $margin );   // phpcs:ignore WordPress.Security.EscapeOutput
		echo self::spacing_row( 'padding', $padding ); // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}

	protected static function block_button_fields( $b ) {
		$text          = self::v( $b, 'text' );
		$url           = self::v( $b, 'url' );
		$border_radius = self::v( $b, 'border_radius' );
		$alignment     = self::v( $b, 'alignment', 'left' );
		$margin        = is_array( $b['margin']  ?? null ) ? $b['margin']  : array();
		$padding       = is_array( $b['padding'] ?? null ) ? $b['padding'] : array();

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Button Text', 'smile' ); ?></label>
			<input type="text" data-field="text" value="<?php echo esc_attr( $text ); ?>"
				class="regular-text" placeholder="<?php esc_attr_e( 'Click here...', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'URL', 'smile' ); ?></label>
			<input type="url" data-field="url" value="<?php echo esc_attr( $url ); ?>"
				class="large-text" placeholder="<?php esc_attr_e( 'https://...', 'smile' ); ?>">
		</div>

		<?php echo self::color_field( 'bg_color', self::v( $b, 'bg_color' ), __( 'Background Color', 'smile' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php echo self::color_field( 'hover_bg_color', self::v( $b, 'hover_bg_color' ), __( 'Hover Background Color', 'smile' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php echo self::color_field( 'text_color', self::v( $b, 'text_color' ), __( 'Text Color', 'smile' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php echo self::color_field( 'hover_text_color', self::v( $b, 'hover_text_color' ), __( 'Hover Text Color', 'smile' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Font Size', 'smile' ); ?></label>
			<input type="text" data-field="font_size"
				value="<?php echo esc_attr( self::v( $b, 'font_size' ) ); ?>" class="regular-text"
				placeholder="<?php esc_attr_e( 'e.g. 16px', 'smile' ); ?>">
		</div>

		<?php echo self::font_weight_field( 'font_weight', self::v( $b, 'font_weight' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Border Radius', 'smile' ); ?></label>
			<input type="text" data-field="border_radius" value="<?php echo esc_attr( $border_radius ); ?>"
				class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 4px', 'smile' ); ?>">
		</div>

		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Alignment', 'smile' ); ?></label>
			<?php foreach ( array( 'left', 'center', 'right' ) as $align ) : ?>
				<label>
					<input type="radio" data-field="alignment"
						name="wdcs_btn_align_<?php echo esc_attr( self::v( $b, 'id' ) ); ?>"
						value="<?php echo esc_attr( $align ); ?>" <?php checked( $alignment, $align ); ?>>
					<?php echo esc_html( ucfirst( $align ) ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<?php
		echo self::spacing_row( 'margin', $margin );   // phpcs:ignore WordPress.Security.EscapeOutput
		echo self::spacing_row( 'padding', $padding ); // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Shared UI helpers
	// -------------------------------------------------------------------------

	protected static function spacing_row( $prefix, $val ) {
		$label  = ( false !== strpos( $prefix, 'padding' ) ) ? __( 'Padding', 'smile' ) : __( 'Margin', 'smile' );
		$sides  = array( 'top' => 'T', 'right' => 'R', 'bottom' => 'B', 'left' => 'L' );
		$values = array(
			'top'    => self::v( $val, 'top' ),
			'right'  => self::v( $val, 'right' ),
			'bottom' => self::v( $val, 'bottom' ),
			'left'   => self::v( $val, 'left' ),
		);
		$unit  = self::v( $val, 'unit', 'px' );
		$units = array( 'px', '%', 'em', 'rem' );

		ob_start();
		?>
		<div class="wdcs-cb-spacing-group">
			<label class="wdcs-cb-spacing-label"><?php echo esc_html( $label ); ?></label>
			<div class="wdcs-cb-spacing-inputs">
				<?php foreach ( $sides as $side => $side_label ) : ?>
					<div class="wdcs-cb-spacing-item">
						<input type="number" data-field="<?php echo esc_attr( $prefix . '.' . $side ); ?>"
							value="<?php echo esc_attr( $values[ $side ] ); ?>" placeholder="0">
						<span><?php echo esc_html( $side_label ); ?></span>
					</div>
				<?php endforeach; ?>
				<select data-field="<?php echo esc_attr( $prefix . '.unit' ); ?>">
					<?php foreach ( $units as $u ) : ?>
						<option value="<?php echo esc_attr( $u ); ?>" <?php selected( $unit, $u ); ?>><?php echo esc_html( $u ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	protected static function color_field( $field, $val, $label ) {
		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php echo esc_html( $label ); ?></label>
			<input type="text" class="wdcs-color-picker"
				data-field="<?php echo esc_attr( $field ); ?>"
				value="<?php echo esc_attr( $val ); ?>">
		</div>
		<?php
		return ob_get_clean();
	}

	protected static function font_weight_field( $field, $val ) {
		$weights = array(
			''       => __( '— Default —', 'smile' ),
			'100'    => '100 (Thin)',    '200'    => '200 (Extra Light)',
			'300'    => '300 (Light)',   '400'    => '400 (Regular)',
			'500'    => '500 (Medium)',  '600'    => '600 (Semi Bold)',
			'700'    => '700 (Bold)',    '800'    => '800 (Extra Bold)',
			'900'    => '900 (Black)',   'normal' => 'Normal',
			'bold'   => 'Bold',
		);

		ob_start();
		?>
		<div class="wdcs-cb-field-row">
			<label><?php esc_html_e( 'Font Weight', 'smile' ); ?></label>
			<select data-field="<?php echo esc_attr( $field ); ?>">
				<?php foreach ( $weights as $wv => $wl ) : ?>
					<option value="<?php echo esc_attr( $wv ); ?>" <?php selected( $val, $wv ); ?>><?php echo esc_html( $wl ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Utility
	// -------------------------------------------------------------------------

	protected static function v( $data, $key, $default = '' ) {
		if ( ! is_array( $data ) ) {
			return $default;
		}
		if ( false === strpos( $key, '.' ) ) {
			return $data[ $key ] ?? $default;
		}
		$parts   = explode( '.', $key, 2 );
		$segment = $data[ $parts[0] ] ?? null;
		if ( ! is_array( $segment ) ) {
			return $default;
		}
		return self::v( $segment, $parts[1], $default );
	}

}
