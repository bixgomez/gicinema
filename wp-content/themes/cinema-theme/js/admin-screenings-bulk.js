// Admin enhancement: Bulk-select and delete rows in ACF 'screenings' repeater
(function($){
  function injectStyles() {
    if (document.getElementById('screenings-bulk-css')) return;
    var css = document.createElement('style');
    css.id = 'screenings-bulk-css';
    css.textContent = '\
      .screenings-bulk-toolbar{margin:6px 0;display:flex;align-items:center;gap:8px;flex-wrap:wrap}\
      .screenings-bulk-toolbar .actions{display:flex;gap:8px;align-items:center}\
      .screenings-bulk-toolbar .actions button{margin:0}\
      .acf-field-repeater[data-name="screenings"] .acf-row-handle.order{display:flex;align-items:center;gap:6px}\
      .acf-field-repeater[data-name="screenings"] .bulk-delete-checkbox{margin:0}\
    ';
    document.head.appendChild(css);
  }

  function enhance($field){
    if (!$field || !$field.length) return;
    if ($field.data('bulk-enhanced')) return;

    // Insert toolbar before the repeater table
    var $input = $field.children('.acf-input');
    var $repeater = $input.find('> .acf-repeater').first();
    if ($input.length) {
      if ($input.find('> .screenings-bulk-toolbar').length === 0) {
        var $toolbar = $('<div class="screenings-bulk-toolbar">'
        + '<strong>Screenings:</strong>'
        + '<div class="actions">'
        + '<button type="button" class="button button-small js-select-all">Select all</button>'
        + '<button type="button" class="button button-small js-select-none">Select none</button>'
        + '<span>With selected:</span>'
        + '<button type="button" class="button button-small button-link-delete js-delete-selected">Delete</button>'
        + '</div>'
        + '</div>');
        if ($repeater.length) {
          $repeater.before($toolbar);
        } else {
          // Fallback: insert after label
          var $label = $field.children('.acf-label');
          if ($label.length) {
            $label.after($toolbar);
          } else {
            $input.prepend($toolbar);
          }
        }

        // Event bindings
        $toolbar.on('click', '.js-select-all', function(){
          $field.find('tbody tr.acf-row:not(.acf-clone) td.acf-row-handle.order .bulk-delete-checkbox').prop('checked', true);
        });
        $toolbar.on('click', '.js-select-none', function(){
          $field.find('tbody tr.acf-row:not(.acf-clone) td.acf-row-handle.order .bulk-delete-checkbox').prop('checked', false);
        });
        $toolbar.on('click', '.js-delete-selected', function(){
          var $rows = $field.find('tbody tr.acf-row:not(.acf-clone) td.acf-row-handle.order .bulk-delete-checkbox:checked').closest('tr.acf-row');
          if (!$rows.length) return;
          if (!window.confirm('Delete ' + $rows.length + ' screening(s)?')) return;

          // Remove rows directly without triggering ACF's confirmation
          $($rows.get().reverse()).each(function(){
            var $row = $(this);

            // Use ACF's repeater remove method if available
            var repeater = acf.getField($field.data('key'));
            if (repeater && typeof repeater.remove === 'function') {
              repeater.remove($row);
            } else {
              // Fallback: manually remove the row and trigger ACF events
              $row.addClass('-collapsed');
              var $tr = $row.addClass('acf-remove');
              $tr.animate({'left': '50px', 'opacity': 0}, 250, function(){
                $tr.remove();
              });
            }
          });
        });
      }
    }

    // Add a checkbox to each existing row handle
    $field.find('tbody tr.acf-row:not(.acf-clone)').each(function(){
      var $row = $(this);
      if ($row.data('bulk-cb')) return;
      var $handle = $row.find('td.acf-row-handle.order').first();
      if ($handle.length) {
        var $cb = $('<input type="checkbox" class="bulk-delete-checkbox" title="Select screening" />');
        $handle.prepend($cb);
        $row.data('bulk-cb', true);
      }
    });

    $field.data('bulk-enhanced', true);
  }

  function enhanceIn($el){
    // Try the explicit field key first (reliable), then name
    var $fields = $el.find('.acf-field[data-key="field_617b2f8e4b8c4"]');
    if (!$fields.length) {
      $fields = $el.find('.acf-field-repeater[data-name="screenings"]');
    }
    if (!$fields.length) {
      var $hidden = $el.find('input.acf-repeater-hidden-input[name="acf[field_617b2f8e4b8c4]"]');
      if ($hidden.length) $fields = $hidden.closest('.acf-field-repeater');
    }
    if (!$fields.length) {
      $fields = $('.acf-field[data-key="field_617b2f8e4b8c4"], .acf-field-repeater[data-name="screenings"]').first();
    }
    $fields.each(function(){ enhance($(this)); });
  }

  // Initialize with ACF lifecycle
  if (window.acf && acf.add_action) {
    acf.add_action('ready', enhanceIn);
    acf.add_action('append', enhanceIn);
    acf.add_action('show', enhanceIn); // groups/tabs opening
  }

  // Fallback init in case ACF hooks miss
  $(function(){ injectStyles(); enhanceIn($(document)); setTimeout(function(){ enhanceIn($(document)); }, 250); setTimeout(function(){ enhanceIn($(document)); }, 750); setTimeout(function(){ enhanceIn($(document)); }, 1500); });
  $(window).on('load', function(){ enhanceIn($(document)); });
})(jQuery);
