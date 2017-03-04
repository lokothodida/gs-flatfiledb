<?php
/**
 * Plugin registration
 *
 * @package FlatFileDB
 */
function register_plugin_flatfile_db() {
  // Constants
  define('FLATFILEDB', basename(__FILE__, '.php'));
  define('FLATFILEDB_VERSION', '0.1.0');
  define('FLATFILEDB_PLUGINPATH', GSPLUGINPATH . FLATFILEDB . '/');
  define('FLATFILEDB_PHPPATH', FLATFILEDB_PLUGINPATH . 'php/');

  define('FLATFILEDB_DATAPATH', GSDATAOTHERPATH . FLATFILEDB . '/');
  define('FLATFILEDB_DATAHTACCESSFILE', FLATFILEDB_DATAPATH . '.htaccess');
  define('FLATFILEDB_DATAROUTESFILE', FLATFILEDB_DATAPATH . 'routes.json');

  define('FLATFILEDB_ADMINURL', 'load.php?id=' . FLATFILEDB . '&action=');
  define('FLATFILEDB_PLUGINURL', $GLOBALS['SITEURL'] . '/plugins/' . FLATFILEDB . '/');
  define('FLATFILEDB_IMGURL', FLATFILEDB_PLUGINURL . 'img/');
  define('FLATFILEDB_JSURL', FLATFILEDB_PLUGINURL . 'js/');
  define('FLATFILEDB_CSSURL', FLATFILEDB_PLUGINURL . 'css/');
  define('FLATFILEDB_PHPURL', FLATFILEDB_PLUGINURL . 'php/');

  // Language registration
  i18n_merge(FLATFILEDB) || i18n_merge(FLATFILEDB, 'en_US');

  // Class dependencies
  require_once(FLATFILEDB_PHPPATH . 'inc/db.class.php');
  require_once(FLATFILEDB_PHPPATH . 'inc/plugin.class.php');
  require_once(FLATFILEDB_PHPPATH . 'inc/admin.class.php');

  // Register plugin
  call_user_func_array('register_plugin', array(
    'id'      => FLATFILEDB,
    'name'    => FlatFileDBPlugin::i18n_r('PLUGIN_NAME'),
    'version' => FLATFILEDB_VERSION,
    'author'  => 'Lawrence Okoth-Odida',
    'url'     => 'https://github.com/lokothodida',
    'desc'    => FlatFileDBPlugin::i18n_r('PLUGIN_DESC'),
    'tab'     => 'plugins',
    'admin'   => 'FlatFileDBAdmin::main'
  ));

  // Sidebar link
  add_action('plugins-sidebar', 'createSideMenu', array(FLATFILEDB, FlatFileDBPlugin::i18n_r('PLUGIN_SIDEBAR')));
}

register_plugin_flatfile_db();

