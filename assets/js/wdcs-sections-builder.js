/**
 * WDCS Content Builder — Admin Meta Box JS
 *
 * Handles all interactions for the custom page builder meta box:
 * sortable sections/blocks, color pickers, media uploader,
 * column layout picker, background type toggle, and JSON serialization.
 */
(function ($) {
    'use strict';

    /* =========================================================
     * Utility helpers
     * ========================================================= */

    /**
     * Build a nested object path from dot-notation.
     * setNested(obj, 'margin.top', '10') → obj.margin.top = '10'
     *
     * @param {Object} obj
     * @param {string} path  Dot-separated key string
     * @param {*}      value
     */
    function setNested(obj, path, value) {
        var keys = path.split('.');
        var cur  = obj;
        for (var i = 0; i < keys.length - 1; i++) {
            if (!cur[keys[i]] || typeof cur[keys[i]] !== 'object') {
                cur[keys[i]] = {};
            }
            cur = cur[keys[i]];
        }
        cur[keys[keys.length - 1]] = value;
    }

    /**
     * Generate a unique section ID.
     * @returns {string}
     */
    function newSectionId() {
        return 'sec_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    }

    /**
     * Generate a unique block ID.
     * @returns {string}
     */
    function newBlockId() {
        return 'blk_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    }

    /* =========================================================
     * Init helpers
     * ========================================================= */

    /**
     * Initialize jQuery UI Sortable on a blocks or sections list.
     * @param {jQuery} $list
     */
    function initSortable($list) {
        if ($list.length && !$list.hasClass('ui-sortable')) {
            $list.sortable({
                handle:      '.wdcs-cb-drag',
                placeholder: 'wdcs-cb-sortable-placeholder',
                tolerance:   'pointer',
            });
        }
    }

    /**
     * Initialize wp-color-picker on all color inputs inside a context element.
     * Guards against double-initialization.
     *
     * @param {jQuery} $context
     */
    function initColorPickers($context) {
        $context.find('.wdcs-color-picker').each(function () {
            if (!$(this).hasClass('wp-color-picker')) {
                $(this).wpColorPicker();
            }
        });
    }

    /**
     * Initialize the background-type radio toggle for a section element.
     * Reads the current selection and shows/hides the appropriate row.
     *
     * @param {jQuery} $section
     */
    function initBgTypeToggle($section) {
        var $radios = $section.find('[data-field="background.type"]');
        var current = $radios.filter(':checked').val();
        applyBgTypeVisibility($section, current);
    }

    /**
     * Show/hide background color vs. image rows based on selected type.
     *
     * @param {jQuery} $section
     * @param {string} type  'color' | 'image'
     */
    function applyBgTypeVisibility($section, type) {
        var $colorRow = $section.find('.wdcs-cb-bg-color-row');
        var $imageRow = $section.find('.wdcs-cb-bg-image-row');
        if (type === 'image') {
            $colorRow.hide();
            $imageRow.show();
        } else {
            // Default to 'color'
            $colorRow.show();
            $imageRow.hide();
        }
    }

    /**
     * Initialize the column layout picker for a section:
     * marks the active option based on the hidden input value and updates visuals.
     *
     * @param {jQuery} $section
     */
    function initColLayoutPicker($section) {
        var currentVal = $section.find('[data-field="column_layout"]').val() || '50-50';
        $section.find('.wdcs-cb-col-option').each(function () {
            var $opt = $(this);
            if ($opt.data('value') === currentVal) {
                $opt.siblings().removeClass('active');
                $opt.addClass('active');
            }
        });
        updateColLayout($section, currentVal);
    }

    /**
     * Update the visual flex proportions of the two column areas.
     *
     * @param {jQuery} $section
     * @param {string} value  e.g. '50-50', '60-40', '30-70'
     */
    function updateColLayout($section, value) {
        var parts = (value || '50-50').split('-');
        var lw = (parts[0] || '50') + '%';
        var rw = (parts[1] || '50') + '%';
        $section.find('[data-col="left"]').css({ flex: '0 0 ' + lw, 'max-width': lw });
        $section.find('[data-col="right"]').css({ flex: '0 0 ' + rw, 'max-width': rw });
    }

    /**
     * Run all initialization routines on a freshly added or loaded section.
     *
     * @param {jQuery} $section
     */
    function initSection($section) {
        initColorPickers($section);
        $section.find('.wdcs-cb-blocks-list').each(function () {
            initSortable($(this));
        });
        initBgTypeToggle($section);
        initColLayoutPicker($section);
    }

    /**
     * Restore the collapsed/expanded UI state of sections and blocks based on the
     * `collapsed` flag stored during a previous save.  The PHP template is expected
     * to render sections with a `data-collapsed="1"` attribute when collapsed.
     */
    function restoreCollapsedState() {
        $('#wdcs-cb-sections-list').children('.wdcs-cb-section').each(function () {
            var $section = $(this);
            if ($section.data('collapsed') === 1 || $section.data('collapsed') === '1') {
                $section.find('.wdcs-cb-section-body').hide();
                $section.find('.wdcs-cb-section-toggle .dashicons')
                    .removeClass('dashicons-arrow-up-alt2')
                    .addClass('dashicons-arrow-down-alt2');
            }
            $section.find('.wdcs-cb-block').each(function () {
                var $block = $(this);
                if ($block.data('collapsed') === 1 || $block.data('collapsed') === '1') {
                    $block.find('.wdcs-cb-block-body').hide();
                    $block.find('.wdcs-cb-block-toggle .dashicons')
                        .removeClass('dashicons-arrow-up-alt2')
                        .addClass('dashicons-arrow-down-alt2');
                }
            });
        });
    }

    /* =========================================================
     * Data serialization helpers
     * ========================================================= */

    /**
     * Read a spacing group (top/right/bottom/left/unit) from a section,
     * excluding any inputs that live inside a block element.
     *
     * @param {jQuery} $section
     * @param {string} prefix  e.g. 'spacing.margin'
     * @returns {Object}
     */
    function readSpacing($section, prefix) {
        function sectionVal(field) {
            return $section
                .find('[data-field="' + field + '"]')
                .not($section.find('.wdcs-cb-block *'))
                .val();
        }
        return {
            top:    sectionVal(prefix + '.top'),
            right:  sectionVal(prefix + '.right'),
            bottom: sectionVal(prefix + '.bottom'),
            left:   sectionVal(prefix + '.left'),
            unit:   sectionVal(prefix + '.unit') || 'px',
        };
    }

    /**
     * Read a spacing group from within any arbitrary container
     * (used for title/subtitle margin & padding).
     *
     * @param {jQuery} $ctx
     * @param {string} prefix
     * @returns {Object}
     */
    function readSpacingFromBlock($ctx, prefix) {
        return {
            top:    $ctx.find('[data-field="' + prefix + '.top"]').val(),
            right:  $ctx.find('[data-field="' + prefix + '.right"]').val(),
            bottom: $ctx.find('[data-field="' + prefix + '.bottom"]').val(),
            left:   $ctx.find('[data-field="' + prefix + '.left"]').val(),
            unit:   $ctx.find('[data-field="' + prefix + '.unit"]').val() || 'px',
        };
    }

    /**
     * Read a text element (title / subtitle) from a section.
     *
     * @param {jQuery} $section
     * @param {string} key  'title' | 'subtitle'
     * @returns {Object}
     */
    function readTextElement($section, key) {
        return {
            text:        $section.find('[data-field="' + key + '.text"]').val(),
            color:       $section.find('[data-field="' + key + '.color"]').val(),
            font_size:   $section.find('[data-field="' + key + '.font_size"]').val(),
            font_weight: $section.find('[data-field="' + key + '.font_weight"]').val(),
            line_height: $section.find('[data-field="' + key + '.line_height"]').val(),
            alignment:   $section.find('[data-field="' + key + '.alignment"]:checked').val() || 'left',
            margin:      readSpacingFromBlock($section, key + '.margin'),
            padding:     readSpacingFromBlock($section, key + '.padding'),
        };
    }

    /**
     * Read the description field group from a section.
     *
     * @param {jQuery} $section
     * @returns {Object}
     */
    function readDescription($section) {
        return {
            content:     $section.find('[data-field="description.content"]').val(),
            color:       $section.find('[data-field="description.color"]').val(),
            font_size:   $section.find('[data-field="description.font_size"]').val(),
            font_weight: $section.find('[data-field="description.font_weight"]').val(),
            line_height: $section.find('[data-field="description.line_height"]').val(),
            alignment:   $section.find('[data-field="description.alignment"]:checked').val() || 'left',
            margin:      readSpacingFromBlock($section, 'description.margin'),
            padding:     readSpacingFromBlock($section, 'description.padding'),
        };
    }

    /**
     * Collect all blocks from a `.wdcs-cb-blocks-list` container in DOM order.
     *
     * @param {jQuery} $list
     * @returns {Array}
     */
    function collectBlocks($list) {
        var blocks = [];
        $list.children('.wdcs-cb-block').each(function () {
            var $block = $(this);
            var type   = $block.data('type');
            var block  = {
                id:        $block.data('id'),
                type:      type,
                collapsed: $block.find('.wdcs-cb-block-body').is(':hidden'),
            };

            // Read every data-field inside the block body
            $block.find('.wdcs-cb-block-body [data-field]').each(function () {
                var $input = $(this);
                var field  = $input.data('field');

                // Skip un-checked radio buttons
                if ($input.is(':radio') && !$input.is(':checked')) {
                    return;
                }

                var val = $input.val();
                setNested(block, field, val);
            });

            blocks.push(block);
        });
        return blocks;
    }

    /**
     * Serialize the entire section list into an array of section objects.
     *
     * @returns {Array}
     */
    function serializeSections() {
        var sections = [];

        $('#wdcs-cb-sections-list').children('.wdcs-cb-section').each(function () {
            var $section = $(this);

            var section = {
                id:       $section.data('id'),
                label:    $section.find('[data-field="label"]').val(),
                collapsed: $section.find('.wdcs-cb-section-body').is(':hidden'),

                background: {
                    type:      $section.find('[data-field="background.type"]:checked').val() || 'color',
                    color:     $section.find('[data-field="background.color"]').val(),
                    image_url: $section.find('[data-field="background.image_url"]').val(),
                    position:  $section.find('[data-field="background.position"]').val(),
                    size:      $section.find('[data-field="background.size"]').val(),
                    repeat:    $section.find('[data-field="background.repeat"]').val(),
                },

                layout: {
                    container_width: $section.find('[data-field="layout.container_width"]:checked').val() || 'full',
                    max_width:       $section.find('[data-field="layout.max_width"]').val(),
                    content_width:   $section.find('[data-field="layout.content_width"]').val(),
                    column_gap:      $section.find('[data-field="layout.column_gap"]').val(),
                },

                spacing: {
                    margin:  readSpacing($section, 'spacing.margin'),
                    padding: readSpacing($section, 'spacing.padding'),
                },

                title:       readTextElement($section, 'title'),
                subtitle:    readTextElement($section, 'subtitle'),
                description: readDescription($section),

                column_layout: $section.find('[data-field="column_layout"]').val() || '50-50',

                columns: {
                    left:  collectBlocks($section.find('[data-col="left"] .wdcs-cb-blocks-list')),
                    right: collectBlocks($section.find('[data-col="right"] .wdcs-cb-blocks-list')),
                },
            };

            sections.push(section);
        });

        return sections;
    }

    /* =========================================================
     * Media uploader
     * ========================================================= */

    /**
     * Open the WordPress media picker.
     * On selection, populate the URL input and update/show the preview image
     * within the same field group (.wdcs-cb-field-group).
     *
     * @param {jQuery} $btn  The clicked media button
     */
    function openMediaPicker($btn) {
        var frame = wp.media({
            title:    $btn.data('title') || 'Select Image',
            button:   { text: $btn.data('button') || 'Use Image' },
            multiple: false,
            library:  { type: 'image' },
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var url        = attachment.url;
            var $group     = $btn.closest('.wdcs-cb-field-group, .wdcs-cb-block-body, .wdcs-cb-section-body');

            // Try background image field first, then generic image field
            var $urlInput = $group.find('[data-field="background.image_url"]');
            if (!$urlInput.length) {
                $urlInput = $group.find('[data-field="image_url"]');
            }
            $urlInput.val(url);

            // Update preview — try background preview first
            var $preview = $group.find('.wdcs-cb-bg-preview');
            if (!$preview.length) {
                $preview = $group.find('.wdcs-cb-img-preview');
            }
            if ($preview.length) {
                $preview.attr('src', url).show();
            }
        });

        frame.open();
    }

    /* =========================================================
     * DOM ready
     * ========================================================= */

    $(function () {

        var $sectionsList = $('#wdcs-cb-sections-list');

        /* --- Initial setup ---------------------------------------- */

        // Sections sortable
        initSortable($sectionsList);

        // Initialize each existing section
        $sectionsList.children('.wdcs-cb-section').each(function () {
            initSection($(this));
        });

        // Restore collapsed state from saved data
        restoreCollapsedState();

        /* =============================================================
         * Section-level interactions (delegated to #wdcs-cb-sections-list)
         * ============================================================= */

        // New section triggered from the "Select section(s)" sidebar.
        $(document).on('wdcs:cb:new-section', function (e, sectionId) {
            if (typeof wdcsCBTemplates === 'undefined' || !wdcsCBTemplates.section) {
                console.warn('wdcsCBTemplates.section is not defined.');
                return;
            }

            var html     = wdcsCBTemplates.section.replace(/__SECID__/g, sectionId);
            var $section = $(html);
            $sectionsList.append($section);
            initSection($section);
            initSortable($sectionsList);

            // Scroll to the newly added section so the editor can start filling it in.
            $('html, body').animate({ scrollTop: $section.offset().top - 60 }, 300);
        });

        // Toggle section body
        $sectionsList.on('click', '.wdcs-cb-section-toggle', function () {
            var $btn     = $(this);
            var $section = $btn.closest('.wdcs-cb-section');
            var $body    = $section.find('.wdcs-cb-section-body');
            var $icon    = $btn.find('.dashicons');

            $body.toggle();

            if ($body.is(':visible')) {
                $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
            } else {
                $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
            }
        });

        // Delete section
        $sectionsList.on('click', '.wdcs-cb-delete-section', function () {
            if (!window.confirm('Are you sure?')) {
                return;
            }
            $(this).closest('.wdcs-cb-section').remove();
        });

        // Duplicate section
        $sectionsList.on('click', '.wdcs-cb-duplicate-section', function () {
            var $original = $(this).closest('.wdcs-cb-section');
            var $clone    = $original.clone(false); // false = don't copy event handlers

            // Assign a new ID to the cloned section itself
            $clone.attr('data-id', newSectionId());

            // Regenerate IDs for every block inside the clone
            $clone.find('[data-id]').each(function () {
                $(this).attr('data-id', newBlockId());
            });

            // Ensure section body is visible in the clone
            $clone.find('.wdcs-cb-section-body').show();
            $clone.find('.wdcs-cb-section-toggle .dashicons')
                .removeClass('dashicons-arrow-down-alt2')
                .addClass('dashicons-arrow-up-alt2');

            $original.after($clone);
            initSection($clone);
        });

        /* =============================================================
         * Block-level interactions (delegated to document for new sections)
         * ============================================================= */

        // Add block
        $(document).on('click', '.wdcs-cb-add-block', function () {
            var $btn         = $(this);
            var type         = $btn.data('type');
            var $colBuilder  = $btn.closest('.wdcs-cb-col-builder');
            var $blocksList  = $colBuilder.find('.wdcs-cb-blocks-list');

            if (typeof wdcsCBTemplates === 'undefined' ||
                !wdcsCBTemplates.blocks ||
                !wdcsCBTemplates.blocks[type]) {
                console.warn('wdcsCBTemplates.blocks["' + type + '"] is not defined.');
                return;
            }

            var id   = newBlockId();
            var html = wdcsCBTemplates.blocks[type].replace(/__BLKID__/g, id);
            var $block = $(html);
            $blocksList.append($block);
            initColorPickers($block);
            initSortable($blocksList);
        });

        // Toggle block body
        $(document).on('click', '.wdcs-cb-block-toggle', function () {
            var $btn   = $(this);
            var $block = $btn.closest('.wdcs-cb-block');
            var $body  = $block.find('.wdcs-cb-block-body');
            var $icon  = $btn.find('.dashicons');

            $body.toggle();

            if ($body.is(':visible')) {
                $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
            } else {
                $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
            }
        });

        // Delete block
        $(document).on('click', '.wdcs-cb-delete-block', function () {
            $(this).closest('.wdcs-cb-block').remove();
        });

        /* =============================================================
         * Tab switching (delegated)
         * ============================================================= */

        $(document).on('click', '.wdcs-cb-tab', function () {
            var $tab      = $(this);
            var $section  = $tab.closest('.wdcs-cb-section');
            var tabName   = $tab.data('tab');

            // Deactivate all tabs in this section
            $section.find('.wdcs-cb-tab').removeClass('active');
            // Deactivate all panes in this section
            $section.find('[data-pane]').removeClass('active').hide();

            // Activate clicked tab
            $tab.addClass('active');
            // Activate matching pane
            $section.find('[data-pane="' + tabName + '"]').addClass('active').show();
        });

        /* =============================================================
         * Column layout picker (delegated)
         * ============================================================= */

        $(document).on('click', '.wdcs-cb-col-option', function () {
            var $opt     = $(this);
            var $section = $opt.closest('.wdcs-cb-section');
            var value    = $opt.data('value');

            // Update active state
            $opt.siblings('.wdcs-cb-col-option').removeClass('active');
            $opt.addClass('active');

            // Update hidden field
            $section.find('[data-field="column_layout"]').val(value);

            // Update visual proportions
            updateColLayout($section, value);
        });

        /* =============================================================
         * Background type toggle (delegated)
         * ============================================================= */

        $(document).on('change', '[data-field="background.type"]', function () {
            var $radio   = $(this);
            var $section = $radio.closest('.wdcs-cb-section');
            applyBgTypeVisibility($section, $radio.val());
        });

        /* =============================================================
         * Media uploader (delegated)
         * ============================================================= */

        $(document).on('click', '.wdcs-cb-media-btn', function (e) {
            e.preventDefault();
            openMediaPicker($(this));
        });

        /* =============================================================
         * Form submit — serialize to JSON
         * ============================================================= */

        $('#post').on('submit', function () {
            try {
                var data = serializeSections();
                $('#wdcs-cb-data').val(JSON.stringify(data));
            } catch (err) {
                console.error('WDCS Content Builder: failed to serialize sections.', err);
            }
        });

    }); // end DOM ready

})(jQuery);
