<?php

/**
 * Admin field: Title (Display) for Film CPT.
 *
 * Renders a small WYSIWYG directly below the main Title field
 * on film add/edit screens, and saves it to post meta `title_display`.
 */

// Render the field right below the Title on film edit screens.
add_action('edit_form_after_title', function ($post) {
    if (! $post || $post->post_type !== 'film') {
        return;
    }

    $meta_key = 'title_display';
    $value    = get_post_meta($post->ID, $meta_key, true);

    // Nonce for saving.
    wp_nonce_field('film_title_display_save', 'film_title_display_nonce');

    echo '<div id="film-title-display-wrap" class="film-title-display-wrap" style="margin:0;">';
    echo '<label for="title_display" style="display:block;font-weight:600;margin-bottom:6px;">Title (Display)</label>';

    // Compact WYSIWYG with fuller toolbar and code (Text) editing.
    $editor_settings = array(
        'textarea_name'  => $meta_key,
        'textarea_rows'  => 2,
        'teeny'          => false,
        'media_buttons'  => false,
        // Limit Quicktags and customize buttons (exclude default 'del')
        'quicktags'      => array(
            'buttons' => 'strong,em,code,blockquote,close'
        ),
        'wpautop'        => false,
        'editor_height'  => 60,
        'tinymce'        => array(
            'menubar'  => false,
            // No format selector or link buttons
            'toolbar1' => 'bold,italic,underline,strikethrough,removeformat,undo,redo',
            'toolbar2' => 'alignleft,aligncenter,alignright,bullist,numlist,blockquote',
            'resize'   => false,
            'statusbar'=> false,
            'wp_autoresize_on' => false,
        ),
    );

    wp_editor($value, 'title_display', $editor_settings);
    echo '</div>';
});

// Save the field.
add_action('save_post', function ($post_id) {
    // Basic checks.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }

    $post = get_post($post_id);
    if (! $post || $post->post_type !== 'film') {
        return;
    }

    // Capability check.
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    // Nonce check.
    if (! isset($_POST['film_title_display_nonce']) || ! wp_verify_nonce($_POST['film_title_display_nonce'], 'film_title_display_save')) {
        return;
    }

    $meta_key = 'title_display';
    $new_val  = isset($_POST[$meta_key]) ? wp_kses_post($_POST[$meta_key]) : '';

    if ($new_val === '') {
        delete_post_meta($post_id, $meta_key);
    } else {
        update_post_meta($post_id, $meta_key, $new_val);
    }
});

// Add a custom Quicktags 'del' button without datetime attribute for the 'title_display' editor only.
add_action('admin_print_footer_scripts', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (! $screen || $screen->base !== 'post' || $screen->post_type !== 'film') {
        return;
    }
    ?>
    <style>
    /* Film CPT: Adjust core title input spacing */
    body.post-type-film #titlediv #title { margin: 24px 0 6px !important; }
    body.post-type-film #titlewrap { margin-bottom: 12px !important; }
    /* Film CPT: Compact the Title (Display) editor iframe */
    body.post-type-film #wp-title_display-wrap .mce-edit-area iframe { height: 60px !important; }
    body.post-type-film #wp-title_display-wrap .wp-editor-area { min-height: 60px !important; }
    </style>
    <script>
    (function() {
        function setupTitleDisplayField() {
            // Add a custom 'del' button for the title_display instance only (no datetime).
            if (typeof window.QTags !== 'undefined' && window.QTags.addButton) {
                try { QTags.addButton('del_nodate', 'del', '<del>', '</del>', 'd', 'Deleted text', 1, 'title_display'); } catch (e) {}
            }

            // Move the field into #titlediv, after #titlewrap and before .inside (Permalink wrapper).
            try {
                var field = document.getElementById('film-title-display-wrap');
                var titleDiv = document.getElementById('titlediv');
                if (field && titleDiv) {
                    var titleWrap = document.getElementById('titlewrap');
                    var inside = titleDiv.querySelector('div.inside');
                    if (titleWrap && titleWrap.parentNode === titleDiv) {
                        titleWrap.insertAdjacentElement('afterend', field);
                    } else if (inside) {
                        titleDiv.insertBefore(field, inside);
                    } else {
                        titleDiv.appendChild(field);
                    }
                }
            } catch (e) {}

            // Ensure the editor wrapper has the requested id/classes/style.
            try {
                var wpWrap = document.getElementById('wp-title_display-wrap');
                if (wpWrap) {
                    wpWrap.className = 'wp-core-ui wp-editor-wrap tmce-active';
                    wpWrap.style.margin = '-33px 0 0';
                }
            } catch (e) {}

            // Restyle the core title prompt label to be visible and styled.
            try {
                var titlePrompt = document.getElementById('title-prompt-text');
                if (titlePrompt) {
                    // Remove the screen-reader-only class and apply inline styles as specified.
                    titlePrompt.removeAttribute('class');
                    titlePrompt.setAttribute('style', 'display:block;font-weight:600;margin: 0;color: #000;position: absolute;font-size: 1em;padding: 0;');
                    // Change label text from "Add title" to "Title".
                    titlePrompt.textContent = 'Title';
                }
            } catch (e) {}
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupTitleDisplayField);
        } else {
            setupTitleDisplayField();
        }
    })();
    </script>
    <?php
});
