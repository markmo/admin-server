<?php
namespace Models;

abstract class BaseModel {

  const DATE_FORMAT = 'Y-m-d H:i:s';

  public static $uniqueNameConstraint = false;

  protected $columns;

  protected $propertyNames;

  public function __construct() {
    $this->columns = $columns = static::$mapping['columns'];
    $this->propertyNames = array_keys($columns);
  }

  public function __get($key) {
    if (in_array($key, $this->propertyNames)) {
      $type = static::$mapping['columns'][$key]['type'];
      $getter = 'boolean' === $type ? $key : 'get' . ucfirst($key);
      if (method_exists($this, $getter)) {
        return $this->$getter();
      }
    }
    throw new Exception('Non-existent or inaccessible property: ' . $key);
  }

  public function __set($key, $val) {
    if (in_array($key, $this->propertyNames)) {
      $setter = 'set' . ucfirst($key);
      if (method_exists($this, $setter)) {
        $this->$setter($val);
        return;
      }
    }
    throw new Exception('Non-existent or inaccessible property: ' . $key);
  }

  public function __toString() {
    $format = get_class($this) . '(';
    $fields = array();
    $params = array();
    foreach ($this->columns as $key => $column) {
      array_push($fields, $key . ' => %s');
      $val = $this->$key;
      switch ($column['type']) {
        case 'boolean':
          $val = $val ? : 'false';
          break;
        case 'Date':
          $val = $val ? $val->format(self::DATE_FORMAT) : 'NULL';
          break;
        case 'one':
          $Ref = $column['ref'];
          $idProperty = $Ref::$idProperty;
          $val = $val ? $val->$idProperty : 'NULL';
          break;
        default:
          $val = $val ? : 'NULL';
      }
      array_push($params, $val);
    }
    $format .= join(', ', $fields) . ')';
    return sprintf($format, $params);
  }
}
