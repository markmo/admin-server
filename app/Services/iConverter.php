<?php
namespace Services;

interface iConverter {

  public function refConverter($key);

  public function fk($key);

  public function xls($collection);

  public function arr($collection, $options);

  public function page($collection, $totalEntries, $options);

  public function get($model, $options);

  public function createModel($array, $isRow);

  public function set($model, $array, $isRow);

  public function toString($model);

}