<?php
/**
 * Admin page wrapper for batch removal of superfluous ACF screenings.
 *
 * Loaded by gicinema.php and added to the GI Cinema admin menu by
 * inc/admin-nav.php. This is the all-films cleanup screen for removing ACF
 * showtimes that no longer match the custom table. It builds a
 * queue of Film posts and uses JavaScript to process them one at a time. Dry-run
 * mode previews deletions; live mode updates the ACF repeater rows. The log is
 * rendered as a WordPress admin table with per-film counts and the reason any
 * rows were considered superfluous. Films are processed by latest active
 * custom-table screening date first, so newer shows appear before older shows.
 */

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__delete_superfluous_screenings.php";

// Submenu registration is centralized in inc/admin-nav.php

function gicinema_get_delete_superfluous_film_ids() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'gi_screenings';
  $table_exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name)) === $table_name);

  if (!$table_exists) {
    $film_query = new WP_Query([
      'post_type'      => 'film',
      'posts_per_page' => -1,
      'fields'         => 'ids',
      'orderby'        => 'date',
      'order'          => 'DESC',
    ]);

    return is_array($film_query->posts) ? array_map('intval', $film_query->posts) : [];
  }

  $sql = "
    SELECT p.ID
    FROM {$wpdb->posts} p
    LEFT JOIN {$table_name} s
      ON s.post_id = p.ID
      AND s.status = 1
    WHERE p.post_type = 'film'
      AND p.post_status NOT IN ('trash', 'auto-draft')
    GROUP BY p.ID
    ORDER BY
      MAX(s.screening) IS NULL ASC,
      MAX(s.screening) DESC,
      p.post_date DESC,
      p.ID DESC
  ";

  $ids = $wpdb->get_col($sql);

  return is_array($ids) ? array_map('intval', $ids) : [];
}



function gicinema_page_display__delete_all_superfluous_screenings() {
  echo '<div class="wrap wrap--gicinema wrap--gicinema-delete-superfluous">';
  gicinema_render_page_info('gicinema--delete-all-superfluous-screenings');

  // Build a list of Film IDs up-front for the JS runner.
  $film_ids = gicinema_get_delete_superfluous_film_ids();
  $ajax_url = admin_url('admin-ajax.php');
  $nonce    = wp_create_nonce('gicinema_delete_all_superfluous');

?>

  <div class="gicinema-batch-controls">
    <label class="gicinema-control-label">
      <input type="checkbox" id="gicinema-dry-run" checked>
      Dry run (no changes)
    </label>
    <button id="gicinema-start-delete" class="button button-primary">Start</button>
    <button id="gicinema-stop-delete" class="button gicinema-button-spaced">Stop</button>
    <span id="gicinema-progress" class="gicinema-progress">Idle</span>
  </div>
  <div id="gicinema-summary" class="gicinema-summary"></div>
  <div class="function-info gicinema-log-box">
    <table class="widefat striped gicinema-table gicinema-table--delete-log">
      <thead>
        <tr>
          <th scope="col">Mode</th>
          <th scope="col">Film</th>
          <th scope="col">Screen Dates</th>
          <th scope="col">Entries</th>
          <th scope="col">Superfluous</th>
          <th scope="col">Keeping</th>
          <th scope="col">Rationale</th>
        </tr>
      </thead>
      <tbody id="gicinema-delete-log"></tbody>
    </table>
  </div>

  <script>
    (function() {
      // Configuration and state
      const filmIds = <?php echo wp_json_encode($film_ids); ?>;
      const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
      const nonce = <?php echo wp_json_encode($nonce); ?>;
      let idx = 0;
      let stopRequested = false;
      let totals = {
        original: 0,
        kept: 0,
        deleted: 0
      };

      const $start = document.getElementById('gicinema-start-delete');
      const $stop = document.getElementById('gicinema-stop-delete');
      const $log = document.getElementById('gicinema-delete-log');
      const $prog = document.getElementById('gicinema-progress');
      const $sum = document.getElementById('gicinema-summary');

      function appendLogCell(row, text) {
        const cell = document.createElement('td');
        cell.textContent = text;
        row.appendChild(cell);
        return cell;
      }

      function appendFilmCell(row, title, editLink) {
        const cell = document.createElement('td');

        if (editLink) {
          const link = document.createElement('a');
          link.href = editLink;
          link.target = '_blank';
          link.rel = 'noopener';
          link.textContent = title;
          cell.appendChild(link);
        } else {
          cell.textContent = title;
        }

        row.appendChild(cell);
        return cell;
      }

      function updateSummary() {
        const dryRun = document.getElementById('gicinema-dry-run').checked;
        const delLabel = dryRun ? 'Would delete' : 'Deleted';
        const keepLabel = dryRun ? 'Would keep' : 'Kept';
        $sum.innerHTML = 'Processed ' + idx + ' / ' + filmIds.length + ' films — ' +
          delLabel + ' <strong>' + totals.deleted + '</strong>; ' +
          keepLabel + ' <strong>' + totals.kept + '</strong> of ' +
          '<strong>' + totals.original + '</strong> total.';
      }

      async function processNext() {
        if (stopRequested) {
          $prog.textContent = 'Stopped at ' + idx + ' / ' + filmIds.length + ' films.';
          return;
        }
        if (idx >= filmIds.length) {
          $prog.textContent = 'Done. Processed all ' + filmIds.length + ' films.';
          updateSummary();
          return;
        }

        const postId = filmIds[idx];
        $prog.textContent = 'Processing film ' + (idx + 1) + ' of ' + filmIds.length + ' (ID ' + postId + ')…';

        try {
          const formData = new FormData();
          formData.append('action', 'gicinema_delete_superfluous_batch');
          formData.append('post_id', String(postId));
          formData.append('_ajax_nonce', nonce);
          const dryRun = document.getElementById('gicinema-dry-run').checked;
          if (dryRun) {
            formData.append('dry_run', '1');
          }

          const res = await fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
          });
          const data = await res.json();
          if (!data || !data.success) {
            const row = document.createElement('tr');
            row.classList.add('gicinema-error');
            appendLogCell(row, document.getElementById('gicinema-dry-run').checked ? 'dry' : 'live');
            appendLogCell(row, 'Film #' + postId);
            appendLogCell(row, '—');
            appendLogCell(row, '0');
            appendLogCell(row, '0');
            appendLogCell(row, '0');
            appendLogCell(row, 'Error processing this film.');
            $log.appendChild(row);
          } else {
            const p = data.data || {};
            totals.original += p.original || 0;
            totals.kept += p.kept || 0;
            totals.deleted += p.deleted || 0;
            // Do not list films that have zero ACF screenings
            if ((p.original || 0) > 0) {
              const row = document.createElement('tr');
              const deleted = p.deleted || 0;
              const kept = p.kept || 0;
              const superfluousCellClass = deleted > 0 ? 'gicinema-danger-text' : '';
              if (deleted > 0) {
                row.classList.add('gicinema-delete-log__row--has-discrepancy');
              }

              appendLogCell(row, p.dry_run ? 'dry' : 'live');
              appendFilmCell(row, p.title || ('Film #' + postId), p.edit_link || '');
              appendLogCell(row, p.screen_date_range || '—');
              appendLogCell(row, String(p.original || 0));
              const superfluousCell = appendLogCell(row, String(deleted));
              if (superfluousCellClass) {
                superfluousCell.classList.add(superfluousCellClass);
              }
              appendLogCell(row, String(kept));
              appendLogCell(row, p.rationale || '');

              $log.appendChild(row);
            }
            updateSummary();
          }
        } catch (e) {
          const row = document.createElement('tr');
          row.classList.add('gicinema-error');
          appendLogCell(row, document.getElementById('gicinema-dry-run').checked ? 'dry' : 'live');
          appendLogCell(row, 'Film #' + postId);
          appendLogCell(row, '—');
          appendLogCell(row, '0');
          appendLogCell(row, '0');
          appendLogCell(row, '0');
          appendLogCell(row, 'Exception: ' + e);
          $log.appendChild(row);
        }

        idx++;
        // Schedule next tick to keep UI responsive
        setTimeout(processNext, 50);
      }

      $start.addEventListener('click', function(ev) {
        ev.preventDefault();
        if (!filmIds || filmIds.length === 0) {
          $prog.textContent = 'No films found.';
          return;
        }
        // Reset state
        stopRequested = false;
        idx = 0;
        totals = {
          original: 0,
          kept: 0,
          deleted: 0
        };
        $log.innerHTML = '';
        $prog.textContent = 'Starting…';
        updateSummary();
        processNext();
      });

      $stop.addEventListener('click', function(ev) {
        ev.preventDefault();
        stopRequested = true;
      });
    })();
  </script>
<?php
  echo '</div>';
}
