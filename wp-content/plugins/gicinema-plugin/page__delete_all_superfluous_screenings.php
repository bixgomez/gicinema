<?php

// If this file is called directly, abort!
if (!defined('ABSPATH')) {
  exit;
}

require_once "function__delete_superfluous_screenings.php";

function gicinema_page_add__delete_all_superfluous_screenings() {
  // Add sub-menu page under the main plugin menu
  add_submenu_page(
    'gicinema--admin',
    'Delete All Superfluous Screenings',
    'Delete Superfluous (All Films)',
    'manage_options',
    'gicinema--delete-all-superfluous-screenings',
    'gicinema_page_display__delete_all_superfluous_screenings'
  );
}
add_action('admin_menu', 'gicinema_page_add__delete_all_superfluous_screenings');

function gicinema_page_display__delete_all_superfluous_screenings() {
  echo '<div class="wrap wrap--gicinema">';
  echo '<h2>Delete Superfluous Screenings (All Films)</h2>';

  // Build a list of Film IDs up-front for the JS runner.
  $film_query = new WP_Query([
    'post_type'      => 'film',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    // Process newest-to-oldest by post date (reverse chronological)
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);
  $film_ids = is_array($film_query->posts) ? array_map('intval', $film_query->posts) : [];
  $ajax_url = admin_url('admin-ajax.php');
  $nonce    = wp_create_nonce('gicinema_delete_all_superfluous');

  ?>
  <div class="info">
    <p>
      This tool processes films one at a time and removes ACF “Screenings” entries
      that do not match active screenings in the custom table, using the same logic
      as the per‑film red button. You’ll see a running log below.
    </p>
  </div>
  <div style="margin:12px 0;">
    <label style="margin-right:12px;">
      <input type="checkbox" id="gicinema-dry-run" checked>
      Dry run (no changes)
    </label>
    <button id="gicinema-start-delete" class="button button-primary">Start</button>
    <button id="gicinema-stop-delete" class="button" style="margin-left:6px;">Stop</button>
    <span id="gicinema-progress" style="margin-left:10px; color:#555;">Idle</span>
  </div>
  <div id="gicinema-summary" style="margin:8px 0; color:#1d2327;"></div>
  <div class="function-info" style="max-height:360px; overflow:auto; border:1px solid #ccd0d4; padding:8px; background:#fff;">
    <ul id="gicinema-delete-log" style="margin:0 0 0 18px; list-style:disc;"></ul>
  </div>

  <script>
  (function() {
    // Configuration and state
    const filmIds = <?php echo wp_json_encode($film_ids); ?>;
    const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
    const nonce   = <?php echo wp_json_encode($nonce); ?>;
    let idx = 0;
    let stopRequested = false;
    let totals = { original: 0, kept: 0, deleted: 0 };

    const $start = document.getElementById('gicinema-start-delete');
    const $stop  = document.getElementById('gicinema-stop-delete');
    const $log   = document.getElementById('gicinema-delete-log');
    const $prog  = document.getElementById('gicinema-progress');
    const $sum   = document.getElementById('gicinema-summary');

    function updateSummary() {
      const dryRun = document.getElementById('gicinema-dry-run').checked;
      const delLabel = dryRun ? 'Would delete' : 'Deleted';
      const keepLabel = dryRun ? 'Would keep' : 'Kept';
      $sum.innerHTML = 'Processed ' + idx + ' / ' + filmIds.length + ' films — '
        + delLabel + ' <strong>' + totals.deleted + '</strong>; '
        + keepLabel + ' <strong>' + totals.kept + '</strong> of '
        + '<strong>' + totals.original + '</strong> total.';
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
        if (dryRun) { formData.append('dry_run', '1'); }

        const res = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: formData });
        const data = await res.json();
        if (!data || !data.success) {
          const li = document.createElement('li');
          li.style.color = '#b32d2e';
          li.textContent = 'Film #' + postId + ': error processing.';
          $log.appendChild(li);
        } else {
          const p = data.data || {};
          totals.original += p.original || 0;
          totals.kept     += p.kept || 0;
          totals.deleted  += p.deleted || 0;
          // Do not list films that have zero ACF screenings
          if ((p.original || 0) > 0) {
            const li = document.createElement('li');
            const prefix = p.dry_run ? '<em>[dry]</em> ' : '';
            const base = (p.edit_link ? ('<a href="' + p.edit_link + '" target="_blank" rel="noopener">' + (p.title || ('Film #' + postId)) + '</a>') : (p.title || ('Film #' + postId)));
            const keepCount = Math.max((p.original || 0) - (p.deleted || 0), 0);
            const actionText = p.dry_run ? 'would delete ' : 'deleted ';
            const keepText = p.dry_run ? 'keeping ' : 'kept ';
            const status = actionText + (p.deleted || 0) + ' of ' + (p.original || 0) + ' (' + keepText + keepCount + ')';
            const statusHtml = (p.deleted || 0) > 0
              ? '<strong style="color:#b32d2e">' + status + '</strong>'
              : status;
            li.innerHTML = prefix + base + ' — ' + statusHtml + '.';
            $log.appendChild(li);
          }
          updateSummary();
        }
      } catch (e) {
        const li = document.createElement('li');
        li.style.color = '#b32d2e';
        li.textContent = 'Film #' + postId + ': exception ' + e;
        $log.appendChild(li);
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
      totals = { original: 0, kept: 0, deleted: 0 };
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
