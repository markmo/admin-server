<?php
namespace Services;

use Doctrine\Common\Util\Inflector;
use Utils\Arr;

class DbService implements iRepository {

  // app context
  protected $app;

  // database connection
  protected $db;

  protected $converter;

  // logging utility
  protected $logger;

  // debug flag
  protected $debug;

  protected $defaultPageSize;

  private $_nextAlias = 'b';

  public function __construct($db,
                              $converter,
                              $logger,
                              $debug = false,
                              $defaultPageSize = 12
  ) {
    $this->db = $db;
    $this->converter = $converter;
    $this->logger = $logger;
    $this->debug = $debug;
    $this->defaultPageSize = $defaultPageSize;
  }

  public function createSelectQuery() {
    $converter = $this->converter;
    $qb = $this->db->createQueryBuilder()
          ->select($converter->columnList)
          ->from($converter->tableName, 'a');
    foreach ($converter->manyToOneAssociations as $a) {
      $refConverter = $this->converter->refConverter($a['ref']);
      $alias = $this->_nextAlias++;
      $idColumn = $refConverter->idColumn;
      $qb->addSelect($alias . '.' . $idColumn);
      $qb->addSelect($alias . '.' . $refConverter->nameColumn);
      $qb->leftJoin('a', $refConverter->tableName, $alias, $alias . '.' . $idColumn . '=a.' . $a['name']);
    }
    return $qb;
  }

  public function findFacets($rc) {
    $converter = $this->converter;
    if ($this->debug)
      $this->logger->addDebug($converter->qualifiedClassName . ':find' . $rc->className . 'Facets called');

    $idColumn = $rc->idColumn;
    $nameColumn = $rc->nameColumn;
    $fk = $this->converter->fk($rc->key);
    $sql = 'select distinct a.' . $rc->idColumn
        . ', a.' . $nameColumn
        . ' from ' . $rc->tableName . ' a inner join ' . $converter->tableName
        . ' b on b.' . $fk['name'] . '= a.' . $idColumn
        . ' order by ' . $nameColumn;
    $result = $this->db->fetchAll($sql);
    $facets = array_map(function ($row) use ($idColumn, $nameColumn) {
      return array(
        'value' => (int)$row[$idColumn],
        'label' => $row[$nameColumn],
      );
    }, $result);
    return $facets;
  }

  public function count($options) {
    // $sql = 'select * from '.$this->converter->tableName;
    // return count($this->db->fetchAll($sql));
    return count($this->findAll($options));
  }

  public function validateUniqueName($name) {
    $converter = $this->converter;
    $sql = 'select * from ' . $converter->tableName
        . ' a where a.' . $converter->nameColumn . '=:name';
    $result = $this->db->fetchAll($sql);
    return empty($result);
  }

  public function findAll($options = array('shallow' => false)) {
    $converter = $this->converter;
    $qualifiedClassName = $converter->qualifiedClassName;
    if ($this->debug)
      $this->logger->addDebug($qualifiedClassName . 'DbService:findAll called');

    $qb = $this->createSelectQuery();
    $params = array();

    // add filter parameters
    if (array_key_exists('filters', $options) and
        $filters = $options['filters']
    ) {
      $manyToManyAssociations = $converter->manyToManyAssociations;
      foreach ($filters as $key => $val) {
        if (strpos($key, '/') !== false) {
          $names = split('/', $key);
          $refModel = $names[0];
          if ($manyToManyAssociations &&
              array_key_exists($refModel, $manyToManyAssociations)
          ) {
            $assoc = $manyToManyAssociations[$names[0]];
            $refConverter = $converter->refConverter($assoc['ref']);
            $table = $refConverter->tableName;
            $prop = $names[1];
            $column = $refConverter->columnNameLookup[$prop];
            $alias1 = $this->_nextAlias++;
            $joinTable = $assoc['joinTable'];
            $fk = $refConverter->idColumn;
            $pk = $converter->idColumn;
            $alias2 = $this->_nextAlias++;
            $qb
            ->join('a', $joinTable, $alias2, $alias2 . '.' . $pk . '=a.' . $pk)
            ->join($alias2, $table, $alias1, $alias1 . '.' . $fk . '=' . $alias2 . '.' . $fk);
            if (strpos($val, '%') !== false) {
              $qb->andWhere($alias1 . '.' . $column . ' like :' . $prop);
            } else {
              $qb->andWhere($alias1 . '.' . $column . '=:' . $prop);
            }
            $params[':' . $prop] = $val;
          } else {
            $assoc = $converter->manyToOneAssociations[$names[0]];
            $refConverter = $converter->refConverter($assoc['ref']);
            $table = $refConverter->tableName;
            $prop = $names[1];
            $column = $refConverter->columnNameLookup[$prop];
            $alias = $this->_nextAlias++;
            $qb->join('a', $table, $alias, $alias . '.' . $refConverter->idColumn . '=a.' . $assoc['name']);
            if (strpos($val, '%') !== false) {
              $qb->andWhere($alias . '.' . $column . ' like :' . $prop);
            } else {
              $qb->andWhere($alias . '.' . $column . '=:' . $prop);
            }
            $params[':' . $prop] = $val;

          }
        } else {
          $column = $converter->columnNameLookup[$key];
          if (strpos($val, '%') !== false) {
            $qb->andWhere('a.' . $column . ' like :' . $key);
          } else {
            $qb->andWhere('a.' . $column . '=:' . $key);
          }
          $params[':' . $key] = $val;
        }
      }
    }

    // add date range parameters
    $dateFilterColumn = $converter->filterDateColumn;
    if ($dateFilterColumn and
        array_key_exists('from', $options) and
        $f = $options['from']
    ) {
      $from = $f;
      $to = date('Y-m-d H:i:s');
      if (array_key_exists('to', $options) and
          $t = $options['to']
      ) {
        $to = $t;
      }
      $qb->andWhere(
        'a.' . $dateFilterColumn . ' >= :startDate and a.'
        . $dateFilterColumn . ' <= :endDate'
      );
      $params[':startDate'] = $from;
      $params[':endDate'] = $to;
    }

    // add order parameters
    if (array_key_exists('sortKey', $options) and
        $s = $options['sortKey']
    ) {
      $sortKey = Inflector::tableize($s);
      $order = 'asc';
      if (array_key_exists('order', $options) and
          $o = $options['order']
      ) {
        $order = $o;
      }
      $qb->orderBy('a.' . $sortKey, $order);
    }

    // add paging parameters
    if (array_key_exists('pageIndex', $options) and
        $p = $options['pageIndex']
    ) {
      $pageSize = $this->defaultPageSize;
      if (array_key_exists('pageSize', $options) and
          $z = $options['pageSize']
      ) {
        $pageSize = $z;
      }
      $startIndex = $p * $pageSize - $pageSize;
      $qb->setFirstResult($startIndex)->setMaxResults($pageSize);
    }

    $sql = $qb->getSQL();
    if ($this->debug) {
      $this->logger->addDebug($sql);
      $this->logger->addDebug(print_r($params, true));
    }

    $result = $this->db->fetchAll($sql, $params);
    $fn = array($converter, 'set');
    return array_map(function ($row) use ($qualifiedClassName, $fn, $options) {
      $model = new $qualifiedClassName;
      return call_user_func($fn, $model, $row, true);
    }, $result);
  }

  protected function persist($object, $id, $sql) {
    $qualifiedClassName = $this->converter->qualifiedClassName;
    if ($object instanceof $qualifiedClassName) {
      $params = Arr::params($object->array(array('fetchAssociations' => false)));
    } else {
      $params = Arr::params($object);
    }
    $params[':id'] = $id;
    $this->db->executeUpdate($sql, $params);
    //$id = PDO::lastInsertId;

    return $id;
  }

  public function insert($object) {
    if ($this->debug)
      $this->logger->addDebug('Services\DbService:insert called');

    $converter = $this->converter;
    $tableName = $converter->tableName;

    // generate the id as a convenience until the db schema settles - to
    // easily enable data to be loaded in bulk
    $id = $this->db->fetchColumn('select max('
                                 . $converter->idColumn . ') + 1 from ' . $tableName);

    $sql = 'insert into ' . $tableName . '(' . $converter->columnList
        . ') values(' . $converter->paramKeys . ')';

    return $this->persist($object, $id, $sql);
  }

  public function update($id, $object) {
    if ($this->debug)
      $this->logger->addDebug('Services\DbService:update called');

    $converter = $this->converter;
    $sql = 'update ' . $converter->tableName . ' set '
        . $converter->sqlUpdateList
        . ' where ' . $converter->idColumn . '=:' . $converter->idProperty;

    return $this->persist($object, $id, $sql);
  }
}
