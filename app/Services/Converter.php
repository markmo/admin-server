<?php
namespace Services;

use DateTime;
use Utils\Arr;

class Converter implements iConverter {

  const DATE_FORMAT = 'Y-m-d H:i:s';

  public $key;

  public $qualifiedClassName;

  public $className;

  public $mapping;

  public $columns;

  public $tableName;

  public $propertyNames;

  public $paramKeys;

  public $columnNames;

  public $columnList;

  public $idProperty;

  public $idColumn;

  public $nameProperty;

  public $nameColumn;

  public $filterDateColumn;

  public $sqlUpdateList;

  public $propertyNameLookup;

  public $columnNameLookup;

  public $manyToOneAssociations;

  public $manyToManyAssociations;

  private $app;

  public function __construct($app, $key, $qualifiedClassName) {
    $this->app = $app;

    $this->key = $key;

    $this->qualifiedClassName = $qualifiedClassName;

    $this->className = substr($qualifiedClassName, strpos($qualifiedClassName, '/') + 1);

    $this->mapping = $mapping = $qualifiedClassName::$mapping;

    $this->columns = $columns = $mapping['columns'];

    $this->tableName = $mapping['table'];

    $this->propertyNames = $propertyNames = array_keys($columns);

    $this->paramKeys = $paramKeys = array_map(function ($name) {
      return ':' . $name;
    }, $propertyNames);

    $this->columnNames = $columnNames = array_map(function ($column) {
      return $column['name'];
    }, $columns);

    $this->columnList = join(',', array_map(function ($name) {
      return 'a.' . $name;
    }, $columnNames));

    $this->idProperty = $idProperty = $mapping['id'];

    $this->idColumn = $columns[$idProperty]['name'];

    $this->nameProperty = $mapping['name'];

    $this->nameColumn = $columns[$mapping['name']]['name'];

    if (array_key_exists('date', $columns)) {
      $this->filterDateColumn = $columns[$mapping['date']]['name'];
    }

    $this->sqlUpdateList = join(',', array_filter(array_map(function ($pair) use ($idProperty) {
      return $pair[1] === $idProperty ? null : $pair[0] . '=' . $pair[1];
    }, array_map(null, $columnNames, $paramKeys))));

    $propertyNameLookup = array();
    $columnNameLookup = array();
    $pairs = array_map(null, $columnNames, $propertyNames);
    foreach ($pairs as $pair) {
      $propertyNameLookup[$pair[0]] = $pair[1];
      $columnNameLookup[$pair[1]] = $pair[0];
    }
    $this->propertyNameLookup = $propertyNameLookup;
    $this->columnNameLookup = $columnNameLookup;

    $this->manyToOneAssociations = array_filter($columns, function ($column) {
      return $column['type'] === 'one';
    });

    if (array_key_exists('many', $mapping)) {
      $this->manyToManyAssociations = $mapping['many'];
    }
  }

  public function refConverter($key) {
    return $this->app[$key . '.converter'];
  }

  public function fk($key) {
    $filtered = array_filter($this->columns, function ($column) use ($key) {
      return array_key_exists('ref', $column) && $column['ref'] === $key;
    });
    return $filtered;
  }

  public function xls($collection) {
    if ($collection) {
      $rows = array(implode("\t", $this->columnNames));
      foreach ($collection as $model) {
        array_push($rows, implode("\t", array_values($this->get($model, array('skipNulls' => false)))));
      }
      return implode("\r\n", $rows) . "\r\n";
    }
    return null;
  }

  public function arr($collection, $options = array()) {
    if ($collection) {
      // defaults
      $options = array_merge(array(
                               'shallow' => true,
                             ), $options);
      $fn = array($this, 'get');
      return array_map(function ($model) use ($fn, $options) {
        return call_user_func($fn, $model);
      }, $collection);
    }
    return array();
  }

  public function page($collection, $totalEntries, $options = array()) {
    if ($collection) {
      // defaults
      $options = array_merge(array(
                               'shallow' => true,
                             ), $options);
      return array(
        array('total_entries' => $totalEntries),
        $this->arr($collection, $options),
      );
    }
    return array();
  }

  public function get($model, $options = array()) {
    // defaults
    $options = array_merge(array(
                             'skipNulls' => true,
                           ), $options);
    $columns = $this->columns;
    $array = array();
    foreach ($this->propertyNames as $key) {
      $val = $model->$key;
      if (!is_null($val) or !$options['skipNulls']) {
        $column = $columns[$key];

        switch ($column['type']) {

          case 'Date':
            $val = $val->format(self::DATE_FORMAT);
            break;

          case 'one':
            $refConverter = $this->app[$column['ref'] . '.converter'];
            $val = $refConverter->get($val);
            break;
        }
        $array[$key] = $val;
      }
    }
    return $array;
  }

  public function createModel($array, $isRow = false) {
    $qualifiedClassName = $this->qualifiedClassName;
    $model = new $qualifiedClassName;
    return $this->set($model, $array, $isRow);
  }

  public function set($model, $array, $isRow = false) {
    if ($array) {
      $propertyNameLookup = $this->propertyNameLookup;
      $propertyNames = $this->propertyNames;
      $columns = $this->columns;
      $app = $this->app;
      foreach ($array as $key => $val) {
        $prop = $isRow ? Arr::get($key, $propertyNameLookup) : $key;
        if (in_array($prop, $propertyNames)) {
          if (is_null($val)) {
            $model->$prop = null;
          } else {
            $type = $columns[$prop]['type'];

            switch ($type) {

              case 'boolean':
                if (!is_bool($val)) {
                  $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }
                break;

              case 'Date':
                if (!($val instanceof DateTime)) {
                  if (is_array($val) and array_key_exists('date', $val)) {
                    $val = $val['date'];
                  } else if (is_string($val)) {
                    $val = new DateTime($val);
                  }
                }
                break;

              case 'int':
                if (!is_int($val)) {
                  $val = (int)$val;
                }
                break;

              case 'one':
                $refConverter = $app[$columns[$prop]['ref'] . '.converter'];
                $qualifiedClassName = $refConverter->qualifiedClassName;
                if (!($val instanceof $qualifiedClassName)) {
                  $ref = new $qualifiedClassName;
                  // $idColumns = Arr::whitelist($array, array(
                  //   $refConverter->idColumn,
                  //   $refConverter->nameColumn,
                  //   ));
                  $idColumn = $refConverter->idColumn;
                  $nameColumn = $refConverter->nameColumn;
                  $idColumns = array(
                    $idColumn => $array[$idColumn],
                    $nameColumn => $array[$nameColumn],
                  );
                  $val = $refConverter->set($ref, $idColumns, $isRow);
                }
                break;

              case 'string':
                $val = trim($val);
                break;
            }
            $model->$prop = $val;
          }
        }
      }
    }
    return $model;
  }

  public function toString($model) {
    $app = $this->app;
    $format = $this->qualifiedName . '(';
    $fields = array();
    $params = array();
    foreach ($this->columns as $key => $column) {
      array_push($fields, $key . ' => %s');
      $val = $model->$key;

      switch ($column['type']) {

        case 'boolean':
          $val = $val ? : 'false';
          break;

        case 'Date':
          $val = $val ? $val->format(self::DATE_FORMAT) : 'NULL';
          break;

        case 'one':
          $refConverter = $app[$column['ref'] . '.converter'];
          $idProperty = $refConverter->idProperty;
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
