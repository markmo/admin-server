<?php
namespace Services;

interface iRepository {

  public function createSelectQuery();

  public function findFacets($rc);

  public function count($options);

  public function findAll($options);

  public function insert($object);

  public function update($id, $object);

}
