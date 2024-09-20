<?php 

// If this file is called directly, abort!
defined('ABSPATH') or die('Unauthorized Access');

function gicinema_add_admin_page() {
  add_menu_page(
      'GI Cinema Plugin',            // title text
      'GI Cinema',                   // menu text
      'manage_options',              // capability for menu item to be displayed
      'gicinema--admin',             // slug name for menu
      'gicinema_admin_page_display', // function to output the content for this page
      'dashicons-admin-generic',     // menu icon
      6                              // position in the menu
  );
}

add_action('admin_menu', 'gicinema_add_admin_page');

function gicinema_admin_page_display() {
  ?>
  <div class="wrap wrap--gicinema">
      <h2>GI Cinema Plugin</h2>
      <p>
        This plugin contains all the code necessary to import films from Agile, create new film posts
        and update existing film posts as needed.
      </p>
      <p>
        It consists of the following (use the links in the sidebar):
      </p>
      <ul>

        <li>
          <h3>All Film Posts</h3>
          <p>
            This simply displays all the WordPress films posts in reverse order of date posted.
            It will check the custom table to see if we have an Agile ID associated with the film,
            and let you know if we find one... or not!
          </p>
          <p>
            <b>Please note:</b> For films posted prior to 10/20/2022, it is 
            unlikely that we have a matching record in the custom table.
          </p>
        </li>

        <li>
          <h3>Import from Agile</h3>
          <p>
            This is the first of our two main cron jobs, which you can run manually if needed.
          </p>
        </li>

        <li>
          <h3>Sync All Screenings</h3>
          <p>
            This is the second of our two main cron jobs, which you can run manually if needed.
          </p>
        </li>

        <li>
          <h3>Delete Overnight Screenings</h3>
          <p>
            Occasionally, due to some weirdness regarding time zones, we end up with  
            screenings being imported in their UTC time equivalents rather than local time.  
            So, we end up with duplicate screenings that appear to occur 7-8 hours later 
            than they actually do.  This function seeks to take care of most of these
            occurrences, by deleting any screenings that appear to start between 10pm and 10am.
          </p>
          <p>
            <i>Yes, it's kludgy, and yes, it must be a bug in the system.</i>  But, for now,
            it works...  For the most part.
          </p>
        </li>

        <li>
          <h3>Dedupe Screenings</h3>
          <p>
            Every so often (usually locally, during development and testing) we end up with 
            duplicate records
            -- not in our WordPress film posts, but in the custom screenings table.  
            This procedure finds and 
            deletes dupes.
          </p>
        </li>

        <li>
          <h3>Backup Database</h3>
          <p>
            This creates a backup of the database, and sticks it in a directory outside the 
            web root (gicinema_dbs).
            It also backs up any database backup older than one week.
            This runs as a cron job once every 24 hours.
            Currently not working locally for some reason; more research is needed.
          </p>
        </li>

        <li>
          <h3>Delete All Films</h3>
          <p>
            This one should almost never be used, especially in production.  This will, as 
            it implies, <i>permanently delete all film posts</i>.  This should only be used locally. 
            In fact, it's not even available on the live site!  So there ya go.
          </p>
        </li>

        <li>
          <h3>Truncate Screenings Table</h3>
          <p>
            This one should also never be used in production.  This will, as 
            it implies, <i>permanently truncate the custom screenings table</i>.  
            This should only be used locally. This too is not available on the live site anyway.
          </p>
        </li>

      </ul>
      <!-- Add more HTML content here -->
  </div>
  <?php
}
