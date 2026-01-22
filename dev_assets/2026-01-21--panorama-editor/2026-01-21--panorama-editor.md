# Branch: 2026-01-21--panorama-editor

## Overview
Adding save/load functionality to the Marzipano panorama editor tool so we can create and edit 360° virtual tours of Grand Illusion Cinema, with the ability to save work-in-progress and return to it later.

## 2026-01-13 14:30

### Context
- We have a working panorama viewer in `/panorama/` directory
- The Marzipano online tool creates tours but doesn't allow saving/loading projects
- Need to build tours from scratch with ability to save and continue later

### What We Did

**1. Set up local Marzipano Tool**
- Created `/panorama-editor/` directory
- Downloaded the Marzipano Tool source files from https://www.marzipano.net/tool/
- Created download script (`download_assets.sh`) to fetch all required assets:
  - JavaScript workers (zip.worker.js, cube.worker.js, equirect.worker.js)
  - UI images and SVG files
  - Logos and icons
- Tool is now running locally at `/panorama-editor/index.html`

**2. Fixed Git Repository Issues**
- Discovered panorama tiles (hundreds of MB per scene) were being committed to git
- GitLab rejected push due to files over 100MB limit
- Used BFG Repo Cleaner to remove large files from git history:
  ```bash
  bfg --delete-folders tiles --no-blob-protection
  bfg --delete-folders source_images --no-blob-protection
  bfg --delete-folders working --no-blob-protection
  bfg --delete-folders panorama_v1 --no-blob-protection
  ```
- Updated `.gitignore` to exclude:
  - `panorama/tiles/`
  - `panorama/source_images/`
  - `panorama/working/`
  - `panorama_v1/`
  - `panorama-editor/img/*.png`
  - `panorama-editor/img/*.svg`
- Result: Tiles stay local/on server, only small config files in git

**3. Created panorama-editor branch**
- Ready to add save/load functionality

### Next Steps
1. Add "Save Project" functionality to export project configuration as JSON
2. Add "Load Project" functionality to restore saved projects
3. Design project file format (lightweight JSON with panorama configs, hotspots, settings)
4. Add project naming with auto-generated unique IDs

### Architecture Notes

**What goes in Git:**
- `panorama/data.js` - Small config file
- `panorama/index.html`, `index.js`, `style.css` - Code files
- `panorama/vendor/` - Marzipano library
- `panorama-editor/` code files (excluding downloaded images)

**What stays OUT of Git:**
- Generated tiles (hundreds of MB)
- Source 360° images
- Working/temp files

**Project Save File Format:**
```json
{
  "projectName": "Grand Illusion Cinema Tour",
  "projectId": "gi-cinema-2026-01-13",
  "created": "2026-01-13T...",
  "lastModified": "2026-01-13T...",
  "settings": { ... },
  "scenes": [ ... ]
}
```

### Files Modified
- `.gitignore` - Added panorama tile exclusions
- Created `panorama-editor/download_assets.sh`
- Created `panorama-editor/index.html`, `js/app.js`, `css/app.css`
- Created `dev_assets/panorama-editor/` directory structure

### Commands to Run
None at this time - waiting for save/load implementation.

---

## 2026-01-13 15:00 - Architecture Discussion: Server-Based vs Client-Side

### Key Realization
The initial plan was to add save/load as a client-side feature (download/upload JSON files). However, **this needs to be a production tool on the server** where:
- Multiple users might create/edit tours
- Assets need to be managed on the server (not in version control)
- Tours need to be deployed to production
- Large image files should never touch git

### Server-Based Architecture (Under Discussion)

**Database Schema:**
```
projects
- id (primary key)
- name (human-readable, e.g., "Grand Illusion Cinema Tour")
- machine_name (unique, e.g., "gi-cinema-tour")
- created_at
- updated_at
- status (draft, published, archived)
- settings (JSON: mouseViewMode, autorotate, etc.)

scenes
- id (primary key)
- project_id (foreign key)
- scene_order (int)
- name (e.g., "Lobby", "Auditorium")
- tile_path (e.g., "/panorama_assets/projects/123/tiles/0-lobby/")
- face_size (int)
- initial_view (JSON: yaw, pitch, fov)
- levels (JSON: tile sizes)

hotspots
- id (primary key)
- scene_id (foreign key)
- hotspot_type (enum: 'link', 'info')
- yaw (float)
- pitch (float)
- rotation (float, for link hotspots)
- target_scene_id (foreign key, nullable, for link hotspots)
- title (text, nullable, for info hotspots)
- text (text, nullable, for info hotspots)
```

**File Storage Structure:**
```
/path/to/panorama_assets/  (outside git, server-managed)
  projects/
    {project-id}/
      source/
        original-image-1.jpg
        original-image-2.jpg
      tiles/
        {scene-id}/
          1/f/0/0.jpg
          1/b/0/0.jpg
          ...
      preview/
        scene-1-thumb.jpg
        scene-2-thumb.jpg
```

**Backend API (PHP):**
- `POST /api/panorama/project/create` - Create new project
- `GET /api/panorama/project/{id}` - Load project
- `PUT /api/panorama/project/{id}` - Update project settings
- `POST /api/panorama/scene/upload` - Upload panorama image, trigger tile processing
- `GET /api/panorama/scene/{id}` - Get scene details
- `PUT /api/panorama/scene/{id}` - Update scene (name, initial view)
- `DELETE /api/panorama/scene/{id}` - Delete scene and its tiles
- `POST /api/panorama/hotspot/create` - Create hotspot
- `PUT /api/panorama/hotspot/{id}` - Update hotspot
- `DELETE /api/panorama/hotspot/{id}` - Delete hotspot
- `GET /api/panorama/project/{id}/export` - Export as standalone tour (zip)
- `POST /api/panorama/project/{id}/publish` - Deploy to production location

**Frontend Editor:**
- Modified Marzipano Tool interface
- Ajax calls to backend API instead of client-side only
- Authentication/authorization
- Real-time preview of changes
- Multi-user support (lock editing, show who's editing)

**Image Processing:**
- Server-side tile generation (PHP exec to call ImageMagick/VIPS, or Node.js script)
- Queue system for heavy processing (avoid timeouts)
- Progress tracking for tile generation
- Could use existing Marzipano tile generation code (JavaScript via Node.js)

### Open Questions to Resolve

1. **Platform Integration:**
   - Is this a WordPress site, Drupal site, or custom PHP?
   - Current repo shows WordPress structure (`wp-content/`, `wp-config.php`)
   - Should this be a WordPress plugin or standalone app?

2. **Access Control:**
   - Who needs to create/edit tours? Just you? Staff? Multiple users?
   - Does it need WordPress user integration or separate auth?

3. **Database:**
   - Use existing WordPress database with custom tables?
   - Separate database for panorama data?

4. **Deployment Workflow:**
   - How do tours move from "editing" to "published" state?
   - Should published tours be static exports (current `panorama/` folder model)?
   - Or dynamic database-driven viewers?

5. **Tile Processing:**
   - Process tiles on upload (blocking/queued)?
   - Or keep Marzipano's client-side processing and just save results to server?
   - Could use Node.js on server to run the existing tile generation code

6. **Hosting Assets:**
   - Where on server should `panorama_assets/` live?
   - Outside web root for source images?
   - Public path for tiles?
   - CDN considerations?

### Two Possible Approaches

**Approach A: Hybrid (Simpler, Faster to Build)**
- Keep Marzipano Tool's client-side tile processing
- Add backend just for:
  - Saving project configs to database
  - Uploading processed tiles to server storage
  - Loading saved projects
- Pro: Less backend complexity, reuse existing tool
- Con: Still requires browser-based processing

**Approach B: Full Server Solution (More Robust)**
- Complete backend rewrite
- Server processes images → tiles
- API-driven editor interface
- Pro: True production tool, multi-user ready, queued processing
- Con: Significant development effort, need server-side tile generation

### Current Status
- Local Marzipano Tool is working and tested
- Client-side approach was initially planned but **needs reconsideration**
- Architecture decision needed before continuing with save/load implementation
- Git repo cleaned up and ready for proper asset management

### Next Decision Point
**Richard needs to decide:**
1. WordPress plugin vs standalone app?
2. Approach A (hybrid) vs Approach B (full server)?
3. Single user (just Richard) vs multi-user?
4. Timeline/urgency for this feature?

Once these are decided, we can design the specific implementation.

---

## 2026-01-21 21:05

### Session Start
Richard returned to continue work on the panorama editor. Reviewed previous session notes and summarized current status:
- Local Marzipano Tool working
- Git repo cleaned
- Architecture decision pending (Hybrid vs Full Server approach)
- Awaiting Richard's decisions on platform integration, user scope, and approach

Richard asked if the panorama editor was discussed in other notes/logs. Searched `dev_assets/dev_notes.md` and confirmed: No, all panorama editor discussion is contained only in this branch-specific file. The main dev notes cover Agile imports, timezone normalization, and admin tools - no panorama content.

Renamed folder and file to match branch naming convention:
- `dev_assets/panorama-editor/` -> `dev_assets/2026-01-21--panorama-editor/`
- `panorama-editor.md` -> `2026-01-21--panorama-editor.md`

Richard asked to explore `./panorama-editor/` directory. Found a complete local Marzipano Tool installation:
- `index.html` - Full Marzipano Tool interface
- `js/app.js` (412KB) - Main application logic
- `js/cube.worker.js`, `js/equirect.worker.js` - Web workers for panorama processing
- `js/zip.worker.js` - ZIP export worker
- `css/app.css` - Styling
- `img/` - UI images and icons
- `download_assets.sh` - Asset download script

Corrected all previous references from `panorama_tool/` to `panorama-editor/` to match the actual directory name.

Updated `.gitignore` to use correct directory name:
- `panorama_tool/img/*.png` -> `panorama-editor/img/*.png`
- `panorama_tool/img/*.svg` -> `panorama-editor/img/*.svg`

---

## 2026-01-21 21:45 - WordPress Plugin Architecture (Brain Dump)

Richard decided to build this as a WordPress plugin. This makes sense because:
- Site is already WordPress with established patterns (gicinema-plugin uses custom tables)
- Built-in authentication
- Familiar admin interface
- Can leverage existing `$wpdb` patterns

### Plugin Name
`panorama-editor` or `gicinema-panorama` (to match existing `gicinema-plugin`)

### High-Level Architecture

**Hybrid Approach:**
- Keep Marzipano's client-side tile processing (browser does the heavy work)
- WordPress plugin handles persistence, project management, and file storage
- Modified Marzipano Tool interface talks to WP via AJAX

### Database Schema

```sql
-- Projects table
CREATE TABLE {$wpdb->prefix}panorama_projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    settings JSON,
    -- Settings includes: mouseViewMode, autorotateEnabled, viewControlButtons, fullscreenButton
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    INDEX idx_status (status),
    INDEX idx_slug (slug)
);

-- Scenes table
CREATE TABLE {$wpdb->prefix}panorama_scenes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    scene_order INT UNSIGNED DEFAULT 0,
    tile_path VARCHAR(500),
    -- e.g., "panorama-projects/123/scenes/1/" relative to uploads
    face_size INT UNSIGNED,
    initial_view JSON,
    -- {yaw, pitch, fov}
    levels JSON,
    -- [{tileSize, size, fallbackOnly}]
    source_filename VARCHAR(255),
    processing_status ENUM('pending', 'processing', 'complete', 'failed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES {$wpdb->prefix}panorama_projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_order (project_id, scene_order)
);

-- Hotspots table
CREATE TABLE {$wpdb->prefix}panorama_hotspots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scene_id BIGINT UNSIGNED NOT NULL,
    hotspot_type ENUM('link', 'info') NOT NULL,
    yaw DECIMAL(10,6) NOT NULL,
    pitch DECIMAL(10,6) NOT NULL,
    rotation DECIMAL(10,6) DEFAULT 0,
    -- For link hotspots
    target_scene_id BIGINT UNSIGNED NULL,
    -- For info hotspots
    title VARCHAR(255),
    text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (scene_id) REFERENCES {$wpdb->prefix}panorama_scenes(id) ON DELETE CASCADE,
    FOREIGN KEY (target_scene_id) REFERENCES {$wpdb->prefix}panorama_scenes(id) ON DELETE SET NULL,
    INDEX idx_scene (scene_id)
);
```

### File Storage Structure

```
wp-content/uploads/panorama-projects/
  {project-id}/
    scenes/
      {scene-id}/
        tiles/
          1/        (level 1)
            f/      (front face)
              0/
                0.jpg
            b/      (back)
            d/      (down)
            l/      (left)
            r/      (right)
            u/      (up)
          2/        (level 2)
          ...
        preview.jpg (thumbnail for scene list)
    exports/
      tour.zip    (exported standalone tour)
```

### Plugin File Structure

```
wp-content/plugins/gicinema-panorama/
  gicinema-panorama.php           (main plugin file)

  inc/
    activate.php                  (create tables on activation)
    deactivate.php
    admin-menu.php                (register admin pages)
    ajax-handlers.php             (AJAX endpoints)
    api/
      projects.php                (CRUD for projects)
      scenes.php                  (CRUD for scenes)
      hotspots.php                (CRUD for hotspots)
      export.php                  (export to standalone tour)

  admin/
    pages/
      projects-list.php           (list all projects)
      project-edit.php            (edit project settings)
      project-editor.php          (launches Marzipano editor)
    assets/
      css/admin.css
      js/admin.js

  editor/
    (modified Marzipano Tool files)
    index.php                     (WP-wrapped editor page)
    js/
      app.js                      (original Marzipano app)
      wp-integration.js           (our additions for save/load)
    css/
      app.css
    ...
```

### Admin Pages

1. **Projects List** (`/wp-admin/admin.php?page=panorama-projects`)
   - Table of all projects with status, scene count, last modified
   - Actions: Edit Settings, Open Editor, Duplicate, Delete, Export
   - "Add New Project" button

2. **Project Settings** (`/wp-admin/admin.php?page=panorama-project-edit&id=X`)
   - Project name, slug
   - Default settings (mouseViewMode, autorotate, etc.)
   - Status (draft/published/archived)
   - "Open in Editor" button

3. **Panorama Editor** (`/wp-admin/admin.php?page=panorama-editor&project=X`)
   - Full-page Marzipano Tool interface
   - Loads project data from database
   - Auto-saves or manual save button
   - Back to project list link

### AJAX Endpoints

```php
// Project operations
wp_ajax_panorama_get_project        // Load project with all scenes/hotspots
wp_ajax_panorama_save_project       // Save project settings
wp_ajax_panorama_duplicate_project  // Clone a project

// Scene operations
wp_ajax_panorama_add_scene          // Create new scene record
wp_ajax_panorama_update_scene       // Update scene (name, initial view, etc.)
wp_ajax_panorama_delete_scene       // Delete scene and its tiles
wp_ajax_panorama_reorder_scenes     // Update scene_order values
wp_ajax_panorama_upload_tiles       // Receive processed tiles from browser

// Hotspot operations
wp_ajax_panorama_save_hotspots      // Bulk save all hotspots for a scene
wp_ajax_panorama_add_hotspot
wp_ajax_panorama_update_hotspot
wp_ajax_panorama_delete_hotspot

// Export
wp_ajax_panorama_export_tour        // Generate standalone tour ZIP
wp_ajax_panorama_publish_tour       // Copy to /panorama/ for live viewing
```

### Marzipano Integration Points

The existing Marzipano Tool (`panorama-editor/js/app.js`) needs modifications:

1. **On Load:**
   - Instead of starting blank, fetch project data via AJAX
   - Populate panorama list from database
   - Load tile URLs from server storage
   - Restore hotspots

2. **On Add Panorama:**
   - Still process tiles client-side (this is the expensive part)
   - After processing, upload tiles to server via chunked AJAX
   - Create scene record in database
   - Store tile path reference

3. **On Save/Auto-save:**
   - Send current state to server:
     - Project settings
     - Scene order, names, initial views
     - All hotspot positions and data

4. **On Export:**
   - Option A: Use existing client-side ZIP (current behavior)
   - Option B: Server generates ZIP from stored tiles + config

5. **Remove/Modify:**
   - Remove intro screen (we know they have a project)
   - Add "Back to WordPress" link
   - Add project name in header (from database)
   - Add explicit "Save" button (in addition to auto-save)

### Workflow: Creating a New Tour

1. Admin clicks "Add New Project" in WP admin
2. Enters project name, selects settings
3. Project record created in database with status=draft
4. Redirected to Panorama Editor page
5. Editor loads with empty project
6. User drags panorama images into editor
7. Browser processes images into tiles (client-side, ~30-60 sec per image)
8. Tiles uploaded to `wp-content/uploads/panorama-projects/{id}/scenes/{scene-id}/`
9. User sets initial views, adds hotspots, names scenes
10. Changes auto-save (or manual save) to database
11. User clicks "Back to Projects"
12. Later: User returns, opens editor, project loads from database, resumes editing

### Workflow: Publishing a Tour

1. In Projects list, click "Publish" on a project
2. Plugin generates standalone tour files:
   - `index.html` (viewer)
   - `data.js` (configuration)
   - Copies tiles to `/panorama/` or specified location
3. Updates project status to "published"
4. Tour is now live at `/panorama/`

### Workflow: Duplicating a Project

1. Click "Duplicate" on existing project
2. Plugin creates new project record with "(Copy)" suffix
3. Copies all scene records with new IDs
4. Copies all hotspot records (updating scene_id references)
5. Copies tile directories to new project folder
6. User can now modify the copy independently

### Open Questions

1. **Tile upload strategy:**
   - Upload as single large request? (may hit PHP limits)
   - Chunked upload? (more complex but handles large files)
   - Upload as ZIP then extract server-side?

2. **Auto-save frequency:**
   - Every change? (lots of requests)
   - Debounced (e.g., 5 seconds after last change)?
   - Manual only with "unsaved changes" warning?

3. **Concurrent editing:**
   - For now: single user (Richard), no locking needed
   - Future: could add "currently being edited by X" indicator

4. **Tile storage size:**
   - Each panorama generates ~50-200MB of tiles
   - Need to consider disk space on hosting
   - Could add "delete unused projects" cleanup tool

5. **Preview thumbnails:**
   - Marzipano generates preview images
   - Capture and store for project list display?

### Next Steps

1. Create basic plugin structure with table creation on activation
2. Build Projects list admin page (CRUD)
3. Modify Marzipano Tool to load/save via AJAX
4. Implement tile upload endpoint
5. Test full workflow: create -> edit -> save -> reload
6. Add duplicate and export functionality
7. Add publish-to-live functionality
