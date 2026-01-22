<?php
defined('ABSPATH') || exit;

if(!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class iPanorama_List_Table_Items extends WP_List_Table {
	private $count_all = 0;
	private $count_mine = 0;
	private $count_trash = 0;
	private $url_preview_base = '';
	
	function __construct() {
		parent::__construct(array(
			'singular'=> 'ipanorama_item',
			'plural' => 'ipanorama_items',
			'ajax' => false
		));
		
		$this->url_preview_base = rtrim((string) parse_url(home_url(), PHP_URL_PATH ), '/') . '/ipanorama/virtualtour/';
	}
	
	function handle_row_actions( $post, $column_name, $primary ) {
		return '';
	}

	function joinPaths() {
		$paths = array();
		
		foreach(func_get_args() as $arg) {
			if($arg !== '') {
				$paths[] = $arg;
			}
		}
		
		return preg_replace('#/+#','/',join('/', $paths));
	}
	
	function filesystem_method() {
		return 'direct';
	}
	
	function request_filesystem_credentials() {
		return true;
	}
	
	function getFileSystem() {
		global $wp_filesystem;
		$result = true;
		
		if(!$wp_filesystem) {
			require_once(ABSPATH . '/wp-admin/includes/file.php');
			
			add_filter('filesystem_method', array( $this, 'filesystem_method'));
			add_filter('request_filesystem_credentials', array( $this, 'request_filesystem_credentials'));
			
			$credentials = request_filesystem_credentials(site_url(), '', true, false, null );
			
			$result = WP_Filesystem($credentials);
			
			remove_filter('filesystem_method', array( $this, 'filesystem_method'));
			remove_filter('request_filesystem_credentials', array( $this, 'request_filesystem_credentials'));
		}
		
		if($result)
			return $wp_filesystem;
		return null;
	}
	
	function hasUserPermissions($user_id) {
		return true;
	}
	
	function getUserActiveRole($user_id) {
		$roles = array();
		
		$settings_key = 'ipanorama_settings';
		$settings_value = get_option($settings_key);
		if($settings_value) {
			$settings = unserialize($settings_value);
			if(is_array($settings->roles)) $roles = $settings->roles;
		}
		
		$user = get_user_by('id', (int)$user_id);
		if($user) {
			foreach($user->roles as $userRoleKey => $userRole) {
				foreach($roles as $roleKey => $role) {
					if($role->id == $userRole && $role->active) {
						return array('id' => $role->id, 'state' => $role->state);
					}
				}
			}
		}
		
		return null;
	}
	
	function getRoleUsers($role) {
		$args = array('role' => $role);
		$users = get_users($args);
		$result = array();
		if($users) {
			foreach($users as $user) {
				array_push($result, $user->ID);
			}
		}
		return $result;
	}
	
	function column_default($item, $column_name){
		switch($column_name){
			case 'title':
			case 'active':
			case 'shortcode':
			case 'author':
			case 'editor':
			case 'created':
			case 'modified':
			case 'id':
				return $item[$column_name];
		}
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s">',
			esc_attr($this->_args['singular']),
            esc_attr($item['id']) // the value of the checkbox should be the record's ID
		);
	}
	
	function column_title($item) {
		$page = sanitize_key(filter_input(INPUT_GET, 'page', FILTER_DEFAULT));
		$item_status = sanitize_key(filter_input(INPUT_GET, 'item_status', FILTER_DEFAULT));
		
		if($this->hasUserPermissions($item['author'])) {
			$actions = array();
			
			switch($item_status) {
				case 'trash': {
					$args = array(
						'page' => $page,
						'item_status' => $item_status,
						'action' => 'restore',
						'id'  => $item['id']
					);
					$url = add_query_arg($args, 'admin.php');
					
					$actions['restore'] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url(wp_nonce_url($url, 'restore_' . $item['id'])),
						esc_html__('Restore', 'ipanorama')
					);
					
					$args = array(
						'page' => $page,
						'item_status' => $item_status,
						'action' => 'delete',
						'id'  => $item['id']
					);
					$url = add_query_arg($args, 'admin.php');
					
					$actions['delete'] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url(wp_nonce_url($url, 'delete_' . $item['id'])),
						esc_html__('Delete Permanently', 'ipanorama')
					);
				} break;
				default: {
					$args = array(
						'page' => 'ipanorama_item',
						'item_status' => $item_status,
						'id'  => $item['id']
					);
					$url = add_query_arg($args, 'admin.php');
					
					$actions['edit'] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url($url),
						esc_html__('Edit', 'ipanorama')
					);

                    if ( IPANORAMA_PLUGIN_PLAN !== 'lite' ) {
                        $args = array(
                            'page' => $page,
                            'item_status' => $item_status,
                            'action' => 'copy',
                            'id' => $item['id']
                        );
                        $url = add_query_arg($args, 'admin.php');

                        $actions['copy'] = sprintf(
                            '<a href="%1$s">%2$s</a>',
                            esc_url(wp_nonce_url($url, 'copy_' . $item['id'])),
                            esc_html__('Duplicate', 'ipanorama')
                        );
                    }
					
					$args = array(
						'page' => $page,
						'item_status' => $item_status,
						'action' => 'trash',
						'id'  => $item['id']
					);
					$url = add_query_arg($args, 'admin.php');
					
					$actions['trash'] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url(wp_nonce_url($url, 'trash_' . $item['id'])),
						esc_html__('Move to Trash', 'ipanorama')
					);
					
					$args = array(
						'mode' => 'preview'
					);
					$url = add_query_arg($args, $this->url_preview_base . $item['id']);
					
					$actions['view'] = sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						esc_url($url),
						esc_html__('Preview', 'ipanorama')
					);
				} break;
			}
			
			return sprintf('<a href="%1$s" class="row-title">%2$s</a> %3$s',
                esc_url(sprintf('?page=ipanorama_item&id=%1$s', $item['id'])),
				esc_html($item['title']),
				$this->row_actions($actions)
			);
		} else {
			return sprintf('<strong>%1$s</strong>', esc_html($item['title']));
		}
	}
	
	function column_active($item) {
		if($this->hasUserPermissions($item['author'])) {
			return sprintf('<div class="ipanorama-toggle ipanorama-%1$s" data-id="%2$s">&nbsp;</div>',
				esc_attr($item['active'] ? 'checked' : 'unchecked'),
                esc_attr($item['id'])
			);
		} else {
			return sprintf('<div class="ipanorama-toggle ipanorama-readonly ipanorama-%1$s" data-id="%2$s">&nbsp;</div>',
                esc_attr($item['active'] ? 'checked' : 'unchecked'),
                esc_attr($item['id'])
			);
		}
	}
	
	function column_shortcode($item) {
		return sprintf('<code>[ipano id="%1$s"]</code>', esc_attr($item['id']));
	}
	
	function column_author($item) {
		$page = sanitize_key(filter_input(INPUT_GET, 'page', FILTER_DEFAULT));
		$args = array(
			'page' => $page,
			'author' => $item['author']
		);
		$url = add_query_arg($args, 'admin.php');
		
		return sprintf('<a href="%1$s">%2$s</a>',
			esc_url($url),
			get_the_author_meta('display_name', $item['author'])
		);
	}
	
	function column_editor($item) {
		$page = sanitize_key(filter_input(INPUT_GET, 'page', FILTER_DEFAULT));
		$args = array(
			'page'   => $page,
			'editor' => $item['editor']
		);
		$url = add_query_arg($args, 'admin.php');
		
		return sprintf('<a href="%1$s">%2$s</a>',
			esc_url($url),
			get_the_author_meta('display_name', $item['editor'])
		);
	}
	
	function column_created($item) {
		$m_time = mysql2date('Y/m/d g:i:s a', $item['created'] );
		$h_time = mysql2date('Y/m/d', $item['created'] );
		
		return sprintf('<abbr title="%1$s">%2$s</abbr>', esc_attr($m_time), esc_attr($h_time));
	}
	
	function column_modified($item) {
		$m_time = mysql2date('Y/m/d g:i:s a', $item['modified'] );
		$h_time = mysql2date('Y/m/d', $item['modified'] );
		
		return sprintf('<abbr title="%1$s">%2$s</abbr>', esc_attr($m_time), esc_attr($h_time));
	}
	
	function get_views() {
		$page = sanitize_key(filter_input(INPUT_GET, 'page', FILTER_DEFAULT));
		$item_status = sanitize_key(filter_input(INPUT_GET, 'item_status', FILTER_DEFAULT));
		
		$args_all   = array('page' => $page);
		$args_mine  = array('page' => $page, 'item_status' => 'mine');
		$args_trash = array('page' => $page, 'item_status' => 'trash');
		
		$url_all   = add_query_arg($args_all, 'admin.php');
		$url_mine  = add_query_arg($args_mine, 'admin.php');
		$url_trash = add_query_arg($args_trash, 'admin.php');
		
		$status_links = array(
			'all'   => sprintf('<a href="%1$s" %2$s>%3$s <span class="count">(%4$d)</span></a>', esc_url($url_all),   ($item_status            ? '' : 'class="current"'), esc_html__('All','ipanorama'), $this->count_all),
			'mine'  => sprintf('<a href="%1$s" %2$s>%3$s <span class="count">(%4$d)</span></a>', esc_url($url_mine),  ($item_status == 'mine'  ? 'class="current"' : ''), esc_html__('Mine','ipanorama'), $this->count_mine),
			'trash' => sprintf('<a href="%1$s" %2$s>%3$s <span class="count">(%4$d)</span></a>', esc_url($url_trash), ($item_status == 'trash' ? 'class="current"' : ''), esc_html__('Trash','ipanorama'), $this->count_trash)
		);
		
		return $status_links;
	}
	
	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox">',
			'title'     => esc_html__('Title', 'ipanorama'),
			'active'    => esc_html__('Active', 'ipanorama'),
			'shortcode' => esc_html__('Shortcode', 'ipanorama'),
			'author'    => esc_html__('Author', 'ipanorama'),
			'editor'    => esc_html__('Editor', 'ipanorama'),
			'created'   => esc_html__('Created', 'ipanorama'),
			'modified'  => esc_html__('Modified', 'ipanorama')
		);
		return $columns;
	}
	
	function get_sortable_columns() {
		$columns = array(
			'title'     => array('title', false),
			'active'    => array('active', false),
			'author'    => array('author', false),
			'editor'    => array('editor', false),
			'created'   => array('created', false),
			'modified'  => array('modified', false)
		);
		return $columns;
	}
	
	function get_bulk_actions() {
		$item_status = sanitize_key(filter_input(INPUT_GET, 'item_status', FILTER_DEFAULT));
		$actions = array();
		
		switch($item_status) {
			case 'trash': {
				$actions = array(
					'restore' => esc_html__('Restore', 'ipanorama'),
					'delete'  => esc_html__('Delete Permanently', 'ipanorama')
				);
			} break;
			default: {
                if ( IPANORAMA_PLUGIN_PLAN !== 'lite' ) {
                    $actions['copy'] = esc_html__('Duplicate', 'ipanorama');
                }
				$actions['trash'] = esc_html__('Move to Trash', 'ipanorama');
			} break;
		}
		
		return $actions;
	}
	
	function process_trash_action($id, $flag) {
		global $wpdb;
		$table = $wpdb->prefix . IPANORAMA_PLUGIN_NAME;

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id);
		$item = $wpdb->get_row($sql, OBJECT);
        // phpcs:enable
		
		if($item && $this->hasUserPermissions($item->author)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update($table, array('deleted'=> $flag), array('id'=>$id));
		}
	}
	
	function process_copy_action($id) {
		global $wpdb;
		$table = $wpdb->prefix . IPANORAMA_PLUGIN_NAME;

        if ( IPANORAMA_PLUGIN_PLAN == 'lite' ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

            if ($count >= 1) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>iPanorama 360: ' . esc_html__('You can create only 1 virtual tour. If you need more, upgrade to the pro version.', 'ipanorama') . '</p>';
                echo '</div>';
                return;
            }
        }

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$sql = $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id);
		$item = $wpdb->get_row($sql, OBJECT);
        // phpcs:enable
		
		if($item && $this->hasUserPermissions($item->author)) {
			$author = get_current_user_id();
			$itemData = unserialize($item->data);
			$itemData->slug = sanitize_title(($itemData->slug ? $itemData->slug : $itemData->title));
			$itemData->title = esc_html__('[Duplicate] ', 'ipanorama') . $itemData->title;
			$itemConfig = unserialize($item->config);

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$result = $wpdb->insert(
				$table,
				array(
					'title' => $itemData->title,
					'slug' => $itemData->slug,
					'active' => $itemData->active,
					'data' => serialize($itemData),
					'config' => serialize($itemConfig),
					'author' => $author,
					'editor' => $author,
					'created' => current_time('mysql', false),
					'modified' => current_time('mysql', false)
				)
			);

			// [filemanager] create an external file
			if($result) {
				if(wp_is_writable(IPANORAMA_PLUGIN_UPLOAD_DIR)) {
					$dir_src_root = $this->joinPaths(IPANORAMA_PLUGIN_UPLOAD_DIR, $id);
					$dir_dst_root = $this->joinPaths(IPANORAMA_PLUGIN_UPLOAD_DIR, $wpdb->insert_id);
					
					$wp_filesystem = $this->getFileSystem();
					
					if($wp_filesystem) {
						if(!$wp_filesystem->is_dir($dir_dst_root)) {
							$wp_filesystem->mkdir($dir_dst_root);
						}
						copy_dir($dir_src_root, $dir_dst_root);
					}
				}
			}
		}
	}
	
	function process_delete_action($id) {
		global $wpdb;
		$table = $wpdb->prefix . IPANORAMA_PLUGIN_NAME;

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id);
		$item = $wpdb->get_row($sql, OBJECT);
        // phpcs:enable
		
		if($item && $this->hasUserPermissions($item->author)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->delete($table, ['id'=>$id], ['%d']);

			// [filemanager] delete file
			if($result) {
				if(wp_is_writable(IPANORAMA_PLUGIN_UPLOAD_DIR)) {
					$dir_root = $this->joinPaths(IPANORAMA_PLUGIN_UPLOAD_DIR, $id);
					$wp_filesystem = $this->getFileSystem();
					if($wp_filesystem) {
						if($wp_filesystem->is_dir($dir_root)) {
							$wp_filesystem->rmdir($dir_root, true);
						}
					}
				}
			}
		}
	}
	
	function process_actions() {
		switch($this->current_action()) {
			case 'trash': {
				if(isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
					$nonce = sanitize_key(filter_input(INPUT_POST, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'bulk-' . $this->_args['plural'];
					
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$items = filter_input(INPUT_POST, $this->_args['singular'], FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
						
						foreach($items as $id) {
							$this->process_trash_action($id, true);
						}
					}
				} else if(isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
					$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
					$nonce = sanitize_key(filter_input(INPUT_GET, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'trash_' . $id;
					
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$this->process_trash_action($id, true);
					}
				}
			} break;
			case 'restore': {
				if(isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
					$nonce = sanitize_key(filter_input(INPUT_POST, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'bulk-' . $this->_args['plural'];
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$items = filter_input(INPUT_POST, $this->_args['singular'], FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
						
						foreach($items as $id) {
							$this->process_trash_action($id, false);
						}
					}
				} else if(isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
					$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
					$nonce = sanitize_key(filter_input(INPUT_GET, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'restore_' . $id;
					
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$this->process_trash_action($id, false);
					}
				}
			} break;
			case 'copy': {
				if(isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
					$nonce = sanitize_key(filter_input(INPUT_POST, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'bulk-' . $this->_args['plural'];
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$items = filter_input(INPUT_POST, $this->_args['singular'], FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
						
						foreach($items as $id) {
							$this->process_copy_action($id);
						}
					}
				} else if(isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
					$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
					$nonce = sanitize_key(filter_input(INPUT_GET, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'copy_' . $id;
					
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$this->process_copy_action($id);
					}
				}
			} break;
			case 'delete': {
				if(isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
					$nonce = sanitize_key(filter_input(INPUT_POST, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'bulk-' . $this->_args['plural'];
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$items = filter_input(INPUT_POST, $this->_args['singular'], FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
						
						foreach($items as $id) {
							$this->process_delete_action($id);
						}
					}
				} else if(isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
					$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
					$nonce = sanitize_key(filter_input(INPUT_GET, '_wpnonce', FILTER_DEFAULT));
					$nonce_key = 'delete_' . $id;
					
					if(wp_verify_nonce($nonce, $nonce_key)) {
						$this->process_delete_action($id);
					}
				}
			} break;
		}
	}
	
	function prepare_items() {
		$this->process_actions();
		
		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$hidden = array();
		
		$itemsPerPage = 25;
		$currentPage = ($this->get_pagenum()-1) * $itemsPerPage;
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		// make sql query
		global $wpdb;
		$table = $wpdb->prefix . IPANORAMA_PLUGIN_NAME;

        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		$item_status = sanitize_key(filter_input(INPUT_GET, 'item_status', FILTER_DEFAULT));
		$orderby = (isset($_GET['orderby']) ? strtolower(sanitize_key(filter_input(INPUT_GET, 'orderby', FILTER_DEFAULT))) : 'id');
		$order   = (isset($_GET['order']) ? strtolower(sanitize_key(filter_input(INPUT_GET, 'order', FILTER_DEFAULT))) : 'desc');
		$author  = (isset($_GET['author']) ? filter_input(INPUT_GET, 'author', FILTER_SANITIZE_NUMBER_INT) : NULL);
		$editor  = (isset($_GET['editor']) ? filter_input(INPUT_GET, 'editor', FILTER_SANITIZE_NUMBER_INT) : NULL);
		$search  = (isset($_POST['s']) ? htmlspecialchars(filter_input(INPUT_POST, 's', FILTER_DEFAULT)) : NULL);
        // phpcs:enable

		$current_user = get_current_user_id();

        // check user input
        $sortable_columns = array_keys($sortable);
        $orderby = in_array($orderby, $sortable_columns) ? $orderby : 'id';

        $order_values = ['asc', 'desc'];
        $order = in_array($order, $order_values) ? $order : 'desc';

		$sql = '';
		$sql_total_items = '';
		$sql_search = ($search ? sprintf(' AND title LIKE "%%%s%%"', $search) : '');
		
		$role = $this->getUserActiveRole($current_user);
		if(current_user_can('manage_options') || ($role && $role['state'] == 'all')) { // by default, the manage_options permission is only given to 'Super Users' and 'Administrators'
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $this->count_all   = $wpdb->query("SELECT id FROM {$table} WHERE NOT deleted");
			$this->count_mine  = $wpdb->query($wpdb->prepare("SELECT id FROM {$table} WHERE NOT deleted AND author=%s", $current_user));
			$this->count_trash = $wpdb->query("SELECT id FROM {$table} WHERE deleted");
            // phpcs:enable

			switch($item_status) {
				case 'trash': {
					if($author) {
						$sql = sprintf('SELECT * FROM %1$s WHERE author=%2$s AND deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $author, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author=%2$s AND deleted %3$s', $table, $author, $sql_search);
					} else if($editor) {
						$sql = sprintf('SELECT * FROM %1$s WHERE editor=%2$s AND deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $editor, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE editor=%2$s AND deleted %3$s', $table, $editor, $sql_search);
					} else {
						$sql = sprintf('SELECT * FROM %1$s WHERE deleted %2$s ORDER BY %3$s %4$s LIMIT %5$d, %6$d', $table, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE deleted %2$s', $table, $sql_search);
					}
				} break;
				case 'mine': {
					if($author) {
						$sql = sprintf('SELECT * FROM %1$s WHERE author=%2$s AND NOT deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $current_user, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author=%2$s AND NOT deleted %3$s', $table, $current_user, $sql_search);
					} else if($editor) {
						$sql = sprintf('SELECT * FROM %1$s WHERE author=%2$s AND editor=%3$s AND NOT deleted %4$s ORDER BY %5$s %6$s LIMIT %7$d, %8$d', $table, $current_user, $editor, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author=%2$s AND editor=%3$s AND NOT deleted %4$s', $table, $current_user, $editor, $sql_search);
					} else {
						$sql = sprintf('SELECT * FROM %1$s WHERE author=%2$s AND NOT deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $current_user, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author=%2$s AND NOT deleted %3$s', $table, $current_user, $sql_search);
					}
				} break;
				default: {
					if($author) {
						$sql = sprintf('SELECT * FROM %1$s WHERE author=%2$s AND NOT deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $author, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author=%2$s AND NOT deleted %3$s', $table, $author, $sql_search);
					} else if($editor) {
						$sql = sprintf('SELECT * FROM %1$s WHERE editor=%2$s AND NOT deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $editor, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE editor=%2$s AND NOT deleted %3$s', $table, $editor, $sql_search);
					} else {
						$sql = sprintf('SELECT * FROM %1$s WHERE NOT deleted %2$s ORDER BY %3$s %4$s LIMIT %5$d, %6$d', $table, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
						$sql_total_items = sprintf('SELECT id FROM %1$s WHERE NOT deleted %2$s', $table, $sql_search);
					}
				} break;
			}
		} else { // the current user view
			if($role) {
				$users = array();
				if($role['state'] == 'private') {
					$users = array($current_user);
				} else if($role['state'] == 'group') {
					$users = $this->getRoleUsers($role['id']);
				}
				
				if(count($users) > 0) {
					$users = implode(',', $users);

                    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$this->count_all = $wpdb->query($wpdb->prepare("SELECT id FROM {$table} WHERE author IN (%s) AND NOT deleted", $users));
					$this->count_mine  = $wpdb->query($wpdb->prepare("SELECT id FROM {$table} WHERE author=%s AND NOT deleted", $current_user));
					$this->count_trash = $wpdb->query($wpdb->prepare("SELECT id FROM {$table} WHERE author IN (%s) AND deleted", $users));
                    // phpcs:enable

					switch($item_status) {
						case 'trash': {
							$sql = sprintf('SELECT * FROM %1$s WHERE author IN (%2$s) AND deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $users, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
							$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author IN (%2$s) AND deleted %3$s', $table, $users, $sql_search);
						} break;
						case 'mine': {
							$sql = sprintf('SELECT * FROM %1$s WHERE author IN (%2$s) AND NOT deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $users, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
							$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author IN (%2$s) AND NOT deleted %3$s', $table, $users, $sql_search);
						} break;
						default: {
							$sql = sprintf('SELECT * FROM %1$s WHERE author IN (%2$s) AND NOT deleted %3$s ORDER BY %4$s %5$s LIMIT %6$d, %7$d', $table, $users, $sql_search, $orderby, $order, $currentPage, $itemsPerPage);
							$sql_total_items = sprintf('SELECT id FROM %1$s WHERE author IN (%2$s) AND NOT deleted %3$s', $table, $users, $sql_search);
						} break;
					}
				}
			}
		}

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $this->items = $wpdb->get_results($sql, 'ARRAY_A');
		$total_items = $wpdb->query($sql_total_items);
        // phpcs:enable
		
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => ceil($total_items / $itemsPerPage)
		));
	}
}
?>