<?php
/**
 * Main database class
 *
 * @package FlatFileDB
 */
class FlatFileDB {
  /**
   * Path to the database's directory
   * @var
   */
  protected $path;

  /**
   * Full path
   */
  protected $full_path;

  /**
   * File extension for entries
   * @var
   */
  protected $ext = '.json';

  /**
   * Enable cache
   * @var
   */
  protected $enable_query_cache = false;

  /**
   * Cache for a query
   * @var
   */
  private $query_cache = array();

  /**
   * Construct database instance
   *
   * @param string $path Path for database folder (relative to /data/other)
   * @param bool   $enable_cache
   */
  public function __construct($path, $enable_query_cache = false) {
    $this->path = $path;
    $this->full_path = GSDATAOTHERPATH . $path;
    $this->enable_query_cache = $enable_query_cache;
  }

  /**
   * Check if the database (or a record in the database) exists
   *
   * @param string $id (optional) ID of the record (or null for the whole database)
   * @return bool TRUE if the database (or record) exists, FALSE otherwise
   */
  public function exists($id = null) {
    if (is_null($id)) {
      return file_exists($this->full_path);
    } else {
      return file_exists($this->getFilename($id));
    }
  }

  /**
   * Initializes the database's directory (folder creation and htaccess)
   *
   * @param int $mode Creation mode for folder
   * @param string $perms HTACCESS file permissions
   * @return TRUE if directory initialized correctly, FALSE otherwise
   */
  public function init($mode = 0755, $perms = 'Deny from all') {
    // Make the directory
    $mkdir = $this->exists() || mkdir($this->full_path, $mode);

    // Set the access permissions for the directory (if it already existed)
    $mkdir = $mkdir || is_writable($this->full_path) || chmod($this->full_path, $mode);

    // Create htaccess file
    $htaccess = $this->full_path . '/.htaccess';

    if ($mkdir && !file_exists($htaccess)) {
      $mkdir = @file_put_contents($htaccess, $perms);
    }

    return $mkdir;
  }

  /**
   * Alter fields
   *
   * @param array|object $data
   * @return bool
   */
  public function alter($data) {
    $data = (object) $data;

    foreach ($data as $name => $options) {
      if (!is_array($options)) {
        $data[$name] = array('type' => (string) $options);
      } elseif (!isset($options['type'])) {
        $data[$name]['type'] = 'text';
      }
    }

    return $this->setData('_fields', $data);
  }

  /**
   * Get fields
   *
   * @return object
   */
  public function fields() {
    $fields = $this->get('_fields');
    unset($fields->_id);
    return $fields;
  }

  /**
   * Delete the table if there are no records (or force remove them)
   *
   * @return bool TRUE if deletion was successful, FALSE otherwise
   */
  public function drop($force = false) {
    $succ = true;

    // Purge files
    if ($force) {
      $files = glob($this->full_path . '/*' . $this->ext);

      foreach ($files as $file) {
        $succ = unlink($file);

        if (!$succ) break;
      }
    }

    // Remove htaccess file
    $htaccess = $this->full_path . '/.htaccess';
    if ($succ && file_exists($htaccess)) {
      $succ = unlink($htaccess);
    }

    // Finally delete the folder
    $succ = $succ && @rmdir($this->full_path);

    return $succ;
  }

  /**
   * Create a new record
   *
   * @param string $id
   * @param array|object $data
   * @return bool TRUE if record created successfully, FALSE if $id exists or creation failed
   */
  public function create($id, $data) {
    return !empty(trim($id)) && !$this->exists($id) && $this->setData($id, $data);
  }

  /**
   * Update a record
   *
   * @param string $id
   * @param array|object $data
   * @return bool TRUE if record updated successfully, FALSE if record doesn't exist or update failed
   */
  public function update($id, $data) {
    return $this->exists($id) && $this->setData($id, $data);
  }

  /**
   * Delete a record
   *
   * @param string $id
   * @return bool TRUE if record deleted correctly, FALSE if record doesn't exist or delete failed
   */
  public function delete($id) {
    // Delete the file
    $delete = $this->exists($id) && unlink($this->getFilename($id));

    // Update the cache
    if ($delete && $this->enable_query_cache && isset($this->query_cache[$id])) {
      unset($this->query_cache[$id]);
    }

    return $delete;
  }

  /**
   * Rename the database/rename the record
   *
   * @param string $oldname
   * @param string $newname
   */
  public function rename($oldname, $newname = null) {
    if (is_null($newname)) {
      // Rename Database
      $oldpath = $this->full_path;
      $newpath = GSDATAOTHERPATH . $oldname;

      $succ = rename($oldpath, $newpath);

      if ($succ) {
        $this->full_path = $newpath;
      }
    } else {
      // Rename record id
      $oldfile = $this->getFilename($oldname);
      $newfile = $this->getFilename($newname);

      $succ = rename($oldfile, $newfile);
    }

    return $succ;
  }

  /**
   * Get a record
   *
   * @param string $id
   * @return object|bool Record data if the record exists, FALSE if record doesn't exist or getting fails
   */
  public function get($id) {
    if ($this->exists($id)) {
      $filename = $this->getFilename($id);
      return $this->getFileData($filename, $id);
    } else {
      return false;
    }
  }

  /**
   * Query the database records
   *
   * @param array $params Query parameters {
   *   @param callable $where Filter elements by this criteria
   *   @param callable $sortby Sort elements by this criteria
   *   @param int      $max Maximum number of elements to check in the query
   * }
   *
   * @return array Results
   */
  public function query($params = array()) {
    // Merge default paramaters
    $params = array_merge(array(
      'where'   => null,
      'orderby' => null,
      'limit'   => 0,
      'offset'  => 0,
      '_id'     => null,
    ), (array) $params);

    // Alias the parameters
    $where   = $params['where'];
    $orderby = $params['orderby'];
    $limit   = abs((int) $params['limit']);
    $offset  = abs((int) $params['offset']);
    $_id     = $params['_id'];

    // Set counters for limit and offset
    $count_limit  = 0;
    $count_offset = 0;

    // Prepare results array and file listing
    $results = array();
    $files   = glob($this->full_path . '/*' . $this->ext);

    foreach ($files as $file) {
      $id = basename($file, $this->ext);

      // Skip "private" records and records with _id failing to match
      if (strpos($id, '_') === 0 || ($_id && is_callable($_id) && !$_id($id))) {
        continue;
      }

      $result = $this->getFileData($file, $id);

      if (!$where || (is_callable($where) && $where($result))) {
        /*
        // Skip this record if we still need to offset
        if ($count_offset < $offset) {
          $count_offset++;
          continue;
        }

        // Break out of the loop if we've hit the limit
        if ($limit && $count_limit >= $limit) {
          break;
        }*/

        // Add the record to the results
        $results[] = $result;
        //$count_limit++;
      }
    }

    // Sort
    if ($orderby && is_callable($orderby)) {
      uasort($results, $orderby);
    }

    if ($offset || $limit) {
      $results = array_slice($results, $offset, $limit);
    }

    return array(
      'results' => $results,
      'total'   => count($results),
    );
  }

  /**
   * Get file name for record
   */
  private function getFilename($id) {
    return $this->full_path . '/' . $id . $this->ext;
  }

  /**
   * Set record data
   */
  private function setData($id, $data) {
    $data = (object) $data;

    if (property_exists($data, '_id')) {
      unset($data->_id);
    }

    $contents = json_encode($data);
    $put_contents = @file_put_contents($this->getFilename($id), $contents);

    if ($put_contents && $this->enable_query_cache) {
      // Update the cache
      $data->_id = $id;
      $this->query_cache[$id] = $data;
    }

    return $put_contents;
  }

  /**
   * Get record data
   */
  private function getFileData($filename, $id = null) {
    if ($this->enable_query_cache && !is_null($id) && isset($this->query_cache[$id])) {
      $data = $this->query_cache[$id];
    } else {
      $contents = file_get_contents($filename);
      $data     = @json_decode($contents);

      if ($data) {
        $data = (object) $data;
        $data->_id = basename($filename, $this->ext);

        if ($this->enable_query_cache && !is_null($id)) {
          $this->query_cache[$id] = $data;
        }
      }
    }

    return $data;
  }
}