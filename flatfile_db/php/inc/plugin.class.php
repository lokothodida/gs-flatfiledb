<?php
/**
 * Plugin utility functions (used across various classes)
 *
 * @package FlatFileDB
 * @subpackage Plugin
 */
class FlatFileDBPlugin {
  /**
   * Gets internationalized strings
   *
   * @param string $hash Internationalized key string
   * @param array $replacements
   * @return string Language of the corresponding string
   */
  static function i18n_r($hash, $replacements = array()) {
    $string = i18n_r(FLATFILEDB . '/' . $hash);

    foreach ($replacements as $key => $value) {
      $string = str_replace($key, $value, $string);
    }

    return $string;
  }

  /**
   * Prints internationalized strings
   *
   * @param string $hash Internationalized key string
   * @param array $replacements
   */
  static function i18n($hash, $replacements = array()) {
    echo self::i18n_r($hash, $replacements);
  }
}