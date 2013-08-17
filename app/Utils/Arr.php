<?php
namespace Utils;

class Arr {

  public static function get($key, $array, $default = null) {
    if (array_key_exists($key, $array) and !empty($array[$key])) {
      return $array[$key];
    } else {
      return $default;
    }
  }

  public static function blacklist($array, $blacklist = array()) {
    if ($array) {
      return array_flip(array_filter(array_flip($array), function ($key) use ($blacklist) {
        return !in_array($key, $blacklist);
      }));
    } else {
      return array();
    }
  }

  public static function whitelist($array, $whitelist = array()) {
    if ($array) {
      return array_flip(array_filter(array_flip($array), function ($key) use ($whitelist) {
        return in_array($key, $whitelist);
      }));
    } else {
      return array();
    }
  }

  public static function params($array) {
    $params = array();
    foreach ($array as $key => $val) {
      $params[':' . $key] = $val;
    }
    return $params;
  }

}
