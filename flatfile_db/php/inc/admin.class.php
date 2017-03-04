<?php
/**
 * Administration panel and actions
 *
 * @package FlatFileDB
 * @subpackage Admin
 */
class FlatFileDBAdmin {
  /**
   * Database field types
   * @var array
   */
  private static $field_types = array(
    'text',
    'textlong',
    'textarea',
    'wysiwyg',
    'date',
    'dropdown',
    'checkbox',
  );

  /**
   * Default database field options
   * @var array
   */
  private static $field_defaults = array(
    'name' => null,
    'type' => null,
    'label' => null,
    'default' => null,
    'hidden' => null,
  );

  /**
   * Default global access permissions
   * @var array
   */
  private static $default_permissions = array(
    'create' => true,
    'drop'   => true,
  );

  /**
   * Default database-specific access permissions
   * @var array
   */
  private static $database_default_permissions = array(
    'view' => true,
    'fields' => true,
    'create' => true,
    'update' => true,
    'delete' => true,
  );

  /**
   * Main Administration Panel Hook
   */
  public static function main() {
    ?>
    <div id="flatfiledb">
    <?php
    // Initialize
    self::init();

    // Header hooks
    exec_action('flatfile-db-admin-header');

    // Global permissions
    $permissions = self::getPermissions();

    if (isset($_GET['createdb']) && $permissions->create) {
      // Create database page
      self::createDatabase();
    } elseif (
      isset($_GET['db']) &&
      ($name = $_GET['db']) &&
      ($db = new FlatFileDB(FLATFILEDB . '/' . $name)) &&
      $db->exists()
    ) {
      // Database-specific permissions
      $db_permissions = self::getDatabasePermissions($name);

      // Database-specific pages
      if (isset($_GET['fields']) && $db_permissions->fields) {
        // Update fields
        if (isset($_POST['update'])) {
          // Parse the data and update the fields
          $fields = self::processFieldsForm($_POST);
          $succ   = $db->alter($fields);

          if ($succ) {
            self::statusMessage('updated', FlatFileDBPlugin::i18n_r('UPDATE_FIELDS_SUCCESS'));
          } else {
            self::statusMessage('updated', FlatFileDBPlugin::i18n_r('UPDATE_FIELDS_ERROR'));
          }
        }

        // Display the Update Fields page
        self::updateFields($db, $name);
      } elseif (isset($_GET['drop']) && $permissions->drop) {
        // Display Delete Table page
        self::dropDatabase($db, $name);
      } elseif (isset($_GET['create']) && $db_permissions->create) {
        // Display Create Record page
        self::createRecord($db, $name);
      } elseif (isset($_GET['update']) && $db->exists($_GET['update']) && $db_permissions->update) {
        // Update record
        if (isset($_POST['update'])) {
          $id = $_GET['update'];
          unset($_POST['update']);

          // Execute plugin filters on the data
          $data = exec_filter('flatfile-db-admin-update-record-' . $name, (object) $_POST);
          $succ = $db->update($id, $data);

          if ($succ) {
            self::statusMessage('updated', FlatFileDBPlugin::i18n_r('UPDATE_RECORD_SUCCESS', array('%id%' => '<b>' . $id . '</b>')));
          } else {
            self::statusMessage('updated', FlatFileDBPlugin::i18n_r('UPDATE_RECORD_ERROR', array('%id%' => '<b>' . $id . '</b>')));
          }
        }

        // Display Update Record page
        self::updateRecord($db, $name, $_GET['update']);
      } elseif (isset($_GET['delete']) && $db->exists($_GET['delete']) && $db_permissions->delete) {
        // Display Delete Record page
        self::deleteRecord($db, $name, $_GET['delete']);
      } elseif ($db_permissions->view) {
        if (isset($_POST['create']) && $db_permissions->create) {
          unset($_POST['create']);

          // Ensure ID is a slug
          $id = clean_url($_POST['_id']);

          // Execute plugin filters on the data
          $data = exec_filter('flatfile-db-admin-create-record-' . $name, (object) $_POST);

          // Allow plugin to set the ID
          if ($data && @$data->_id) {
            $id = $data->_id;
          }

          $succ = $data && $db->create($id, $data);

          if ($succ) {
            self::statusMessage('updated', FlatFileDBPlugin::i18n_r('CREATE_RECORD_SUCCESS', array('%id%' => '<b>' . $id . '</b>')));
          } else {
            self::statusMessage('error', FlatFileDBPlugin::i18n_r('CREATE_RECORD_ERROR', array('%id%' => '<b>' . $id . '</b>')));
          }
        } elseif (isset($_POST['delete']) && $db_permissions->delete) {
          $id   = $_POST['_id'];
          $succ = $db->delete($id);

          if ($succ) {
            self::statusMessage('updated', FlatFileDBPlugin::i18n_r('DELETE_RECORD_SUCCESS', array('%id%' => '<b>' . $id . '</b>')));
          } else {
            self::statusMessage('error', FlatFileDBPlugin::i18n_r('DELETE_RECORD_ERROR', array('%id%' => '<b>' . $id . '</b>')));
          }
        }

        // Display View Database page
        self::viewDatabase($db, $name, $db_permissions);
      } else {
        // User not allowed - redirect to the main admin panel
        self::redirect(FLATFILEDB_ADMINURL);
      }
    } else {
      // Process forms for database creation/delection
      if (isset($_POST['create']) && $permissions->create) {
        // Create a database (no fields)
        $db_name = clean_url($_POST['name']);
        $db      = new FlatFileDB(FLATFILEDB . '/' . $db_name);

        if (!$db->exists() && $db->init() && $db->alter(array())) {
          self::statusMessage('updated', FlatFileDBPlugin::i18n_r('CREATE_DATABASE_SUCCESS', array('%name%' => '<b>' . $db_name . '</b>')));
        } else {
          self::statusMessage('error', FlatFileDBPlugin::i18n_r('CREATE_DATABASE_ERROR', array('%name%' => '<b>' . $db_name . '</b>')));
        }
      } elseif (isset($_POST['drop']) && $permissions->drop) {
        // Drop a database
        $db_name = clean_url($_POST['name']);
        $db      = new FlatFileDB(FLATFILEDB . '/' . $db_name);

        if ($db->drop(true)) {
          self::statusMessage('updated', FlatFileDBPlugin::i18n_r('DROP_DATABASE_SUCCESS', array('%name%' => '<b>' . $db_name . '</b>')));
        } else {
          self::statusMessage('error', FlatFileDBPlugin::i18n_r('DROP_DATABASE_ERROR', array('%name%' => '<b>' . $db_name . '</b>')));
        }
      }

      // View all databases
      self::viewDatabases($permissions);
    }

    self::loadCSS('admin.css');

    // Footer hooks
    exec_action('flatfile-db-admin-footer');
    ?>
    </div>
    <?php
  }

  /**
   * Initialize the directories (i.e. ensure flatfile_db folder exists)
   */
  private static function init() {
    $db = new FlatFileDB(FLATFILEDB);
    return $db->exists() || $db->init();
  }

  /**
   * View all databases
   *
   * @param object $permissions
   */
  private static function viewDatabases($permissions) {
    $page_title = FlatFileDBPlugin::i18n_r('VIEW_DATABASES');

    $nav = array();
    $nav[] = array(
      'title' => i18n_r('SIDE_DOCUMENTATION'),
      'url'   => 'https://github.com/lokothodida/gs-flatfiledb',
    );

    if ($permissions->create) {
      $nav[] = array(
        'title' => FlatFileDBPlugin::i18n_r('CREATE'),
        'url'   => FLATFILEDB_ADMINURL . '&createdb',
      );
    }

    $nav = self::formatNavigationItems($nav);

    $databases = glob(FLATFILEDB_DATAPATH . '/*/');
    $databases = array_map('basename', $databases);

    // Header hook
    exec_action('flatfile-db-admin-header-view');

    $page_data = (object) array(
      'page_title' => $page_title,
      'nav'        => $nav,
      'databases'  => $databases,
      'can_drop'   => $permissions->drop,
    );

    $page_data = exec_filter('flatfile-db-admin-view-page', $page_data);

    self::template('view_databases', $page_data);

    // Footer hook
    exec_action('flatfile-db-admin-footer-view');
  }

  /**
   * View Database page
   *
   * @param FlatFileDB $db Database instance
   * @param string $db_name Database name
   * @param object $permissions Database permissions
   */
  private static function viewDatabase(FlatFileDB $db, $db_name, $permissions) {
    $fields  = self::getFieldsWithDefaults($db);
    $records = $db->query();

    // Filter the records
    $records['results'] = exec_filter('flatfile-db-admin-view-' . $db_name, $records['results']);

    $page_title = FlatFileDBPlugin::i18n_r('VIEW_RECORDS') . ': ' . $db_name;

    $nav = array();

    if ($permissions->fields) {
      $nav[] = array(
        'title' => FlatFileDBPlugin::i18n_r('FIELDS'),
        'url'   => FLATFILEDB_ADMINURL . '&db=' . $db_name . '&fields',
      );
    }

    if ($permissions->create) {
      $nav[] = array(
        'title' => FlatFileDBPlugin::i18n_r('CREATE'),
        'url'   => FLATFILEDB_ADMINURL . '&db=' . $db_name . '&create',
        'classes' => 'add-record',
      );
    }

    $nav = self::formatNavigationItems($nav);

    // Header hook
    exec_action('flatfile-db-admin-header-view-' . $db_name);

    // Filter the page data
    $page_data = (object) array(
      'page_title' => $page_title,
      'fields'     => $fields,
      'records'    => $records['results'],
      'db_name'    => $db_name,
      'nav'        => $nav,
      'can_update' => $permissions->update,
      'can_delete' => $permissions->delete,
    );

    $page_data = exec_filter('flatfile-db-admin-view-page-' . $db_name, $page_data);

    $page_data->page_title = str_replace('%db_name%', $db_name, $page_data->page_title);

    self::template('view_database', $page_data);

    // Footer hook
    exec_action('flatfile-db-admin-footer-view-' . $db_name);
  }

  /**
   * Create Database page
   */
  private static function createDatabase() {
    // Header hook
    exec_action('flatfile-db-admin-header-create');

    self::template('create_database');

    // Header hook
    exec_action('flatfile-db-admin-header-create');
  }

  /**
   * Update a database's fields
   *
   * @param FlatFileDB $db Database instance
   * @param string $db_name Database name
   */
  private static function updateFields(FlatFileDB $db, $db_name) {
    $fields     = self::getFieldsWithDefaults($db);
    $page_title = FlatFileDBPlugin::i18n_r('UPDATE_FIELDS') . ': %db_name%';

    // Header hook
    exec_action('flatfile-db-admin-header-fields-' . $db_name);

    $page_data = (object) array(
      'page_title'     => $page_title,
      'fields'         => $fields,
      'db_name'        => $db_name,
      'field_types'    => self::$field_types,
      'field_defaults' => (object) self::$field_defaults,
    );

    $page_data = exec_filter('flatfile-db-admin-fields-page-' . $db_name, $page_data);
    $page_data->page_title = str_replace('%db_name%', $db_name, $page_data->page_title);

    self::template('update_fields', $page_data);

    // Footer hook
    exec_action('flatfile-db-admin-footer-fields-' . $db_name);
  }

  /**
   * Drop Database page
   *
   * @param FlatFileDB $db Database instance
   * @param string $name Database name
   */
  private static function dropDatabase(FlatFileDB $db, $db_name) {
    // Header hook
    exec_action('flatfile-db-admin-header-drop-' . $db_name);

    self::template('drop_database', array(
      'db_name' => $db_name,
    ));

    // Footer hook
    exec_action('flatfile-db-admin-footer-drop-' . $db_name);
  }

  /**
   * Create Record page
   *
   * @param FlatFileDB $db Database instance
   * @param string $db_name Database name
   */
  private static function createRecord(FlatFileDB $db, $db_name) {
    $fields = self::getFieldsWithDefaults($db);
    $page_title = FlatFileDBPlugin::i18n_r('CREATE_RECORD') . ': ' . $db_name;

    exec_action('flatfile-db-admin-header-create-record-' . $db_name);

    $page_data = (object) array(
      'page_title' => $page_title,
      'fields' => $fields,
      'db_name' => $db_name,
    );

    $page_data = exec_filter('flatfile-db-admin-create-record-page-' . $db_name, $page_data);
    $page_data->page_title = str_replace('%db_name%', $db_name, $page_data->page_title);

    self::template('create_record', $page_data);

    exec_action('flatfile-db-admin-footer-create-record-' . $db_name);
  }

  /**
   * Update Record page
   *
   * @param FlatFileDB $db Database instance
   * @param string $db_name Database name
   * @param string $record_id Record ID
   */
  private static function updateRecord($db, $db_name, $record_id) {
    // Get the fields and record
    $fields = self::getFieldsWithDefaults($db);
    $record = $db->get($record_id);

    // Merge default record values
    foreach ($fields as $name => $field) {
      if (!property_exists($record, $name)) {
        $record->{$name} = $field->default;
      }
    }

    $page_title = FlatFileDBPlugin::i18n_r('UPDATE_RECORD') . ': %record_id% [%db_name%]';

    // Header hook
    exec_action('flatfile-db-admin-header-update-record-' . $db_name);

    // Page data
    $page_data = (object) array(
      'page_title' => $page_title,
      'fields'     => $fields,
      'db_name'    => $db_name,
      'record'     => $record,
    );

    $page_data = exec_filter('flatfile-db-admin-update-record-page-' . $db_name, $page_data);
    $page_data->page_title = str_replace(array('%db_name%', '%record_id%'), array($db_name, $record_id), $page_data->page_title);

    self::template('update_record', $page_data);

    // Footer hook
    exec_action('flatfile-db-admin-footer-update-record-' . $db_name);
  }

  /**
   * Delete Record page
   *
   * @param FlatFileDB $db Database instance
   * @param string $db_name Database name
   * @param string $record_id Record ID
   */
  private static function deleteRecord(FlatFileDB $db, $db_name, $record_id) {
    $page_title = FlatFileDBPlugin::i18n_r('DELETE_RECORD') . ': %record_id% [%db_name%]';

    // Header hook
    exec_action('flatfile-db-admin-header-delete-record-' . $db_name);

    // Page data
    $page_data = (object) array(
      'page_title' => $page_title,
      'db_name'    => $db_name,
      'record_id'  => $record_id,
    );

    $page_data = exec_filter('flatfile-db-admin-delete-record-page-' . $db_name, $page_data);
    $page_data->page_title = str_replace(array('%db_name%', '%record_id%'), array($db_name, $record_id), $page_data->page_title);

    self::template('delete_record', $page_data);

    // Footer hook
    exec_action('flatfile-db-admin-footer-delete-record-' . $db_name);
  }

  /**
   * Get excerpt of text (used to display text from large fields)
   *
   * @param string $text
   * @param int $length
   */
  private static function getExcerpt($text, $length = 140) {
    $text = htmlentities((string) $text);

    if (strlen($text) > $length) {
      $text = substr($text, $length) . '...';
    }

    return $text;
  }

  /**
   * Show status message
   *
   * @param string $status updated/error
   * @param string $message
   */
  private static function statusMessage($status, $message) {
    $status_encoded  = json_encode($status);
    $message_encoded = json_encode($message);

    self::template('status_message', array(
      'status'  => $status_encoded,
      'message' => $message_encoded,
    ));
  }

  /**
   * Get global permissions
   */
  private static function getPermissions() {
    $permissions = (object) self::$default_permissions;

    // Filter the permissions
    $permissions = exec_filter('flatfile-db-admin-permissions', $permissions);

    return $permissions;
  }

  /**
   * Get database-specific permissions
   *
   * @param string $name Database name
   */
  private static function getDatabasePermissions($name) {
    $permissions = (object) self::$database_default_permissions;

    // Filter the permissions
    $permissions = exec_filter('flatfile-db-admin-permissions-' . $name, $permissions);

    return $permissions;
  }

  /**
   * Load CSS
   *
   * @param string $url
   * @param bool $local
   */
  private static function loadCSS($url, $local = true) {
    if ($local) $url = FLATFILEDB_CSSURL . $url;
    ?>
    <link rel="stylesheet" href="<?php echo $url; ?>" />
    <?php
  }

  /**
   * Load JavaScript
   *
   * @param string $url
   * @param bool $local
   */
  private static function loadJS($url, $local = true) {
    if ($local) $url = FLATFILEDB_JSURL . $url;
    ?>
    <script type="text/javascript" src="<?php echo $url; ?>"></script>
    <?php
  }

  /**
   * Load a template file and set the variables
   *
   * @param string $_name Template name
   * @param array $_vars Variables
   */
  private static function template($_name, $_vars = array()) {
    extract((array) $_vars);
    include(FLATFILEDB_PHPPATH . $_name . '.php');
  }

  /**
   * Process data for fields
   * @uses clean_url
   *
   * @param array $post Data
   * @return array Formatted field data
   */
  private static function processFieldsForm(array $post) {
    $fields = array();

    foreach ($post['name'] as $i => $name) {
      $name = clean_url($name);
      $fields[$name] = array();
      $fields[$name]['name']    = $name;
      $fields[$name]['type']    = $post['type'][$i];
      $fields[$name]['label']   = $post['label'][$i];
      $fields[$name]['default'] = $post['default'][$i];
      $fields[$name]['hidden']  = $post['hidden'][$i];
    }

    return $fields;
  }

  /**
   * Format navigation menu (set properties and order)
   *
   * @param array $nav Navigation items
   * @return array Formatted items
   */
  private static function formatNavigationItems(array $nav = array()) {
    $nav = array_reverse($nav);
    $defaults = array(
      'classes' => null,
      'target'  => '_self',
      'title'   => null,
      'url'     => null
    );

    foreach ($nav as $i => $item) {
      $nav[$i] = (object) array_merge($defaults, $item);
    }

    return $nav;
  }

  /**
   * Merge field defaults
   *
   * @param FlatFileDB $db Database instance
   * @return object Fields (with defaults set)
   */
  private static function getFieldsWithDefaults(FlatFileDB $db) {
    $fields = $db->fields();

    if (!$fields) $fields = (object) array();

        // Merge in defaults
    foreach ($fields as $fname => $field) {
      $fields->{$fname} = (object) array_merge(self::$field_defaults, (array) $field);
    }

    return $fields;
  }

  /**
   * Redirect the page (with JavaScript)
   *
   * @param string $url Destination URL
   */
  private static function redirect($url) {
    ?><script>window.location.href = <?php echo json_encode($url); ?>;</script><?php
  }
}