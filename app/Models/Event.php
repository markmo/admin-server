<?php
namespace Models;

use Swagger\Annotations as SWG;

/**
 * @SWG\Model(id="Event")
 */
class Event extends BaseModel {
  /**
   * @SWG\Property(name="id", type="int")
   */
  public $id;

  /**
   * @SWG\Property(name="name", type="string")
   */
  public $name;

  /**
   * @SWG\Property(name="externalId", type="int")
   */
  public $externalId;

  /**
   * @SWG\Property(name="status", type="string")
   */
  public $status;

  /**
   * @SWG\Property(name="typeId", type="int")
   */
  public $typeId;

  /**
   * @SWG\Property(name="classId", type="int")
   */
  public $classId;

  public static function loadValidatorMetadata(ClassMetadata $metadata) {
    $metadata
    ->addPropertyConstraint('name', new Assert\NotBlank())
    ->addPropertyConstraint('name', new Assert\Length(array('max' => 100)))
    ->addPropertyConstraint('status', new Assert\Choice(array('choices' => array('A', 'S'))));
  }

  public static $uniqueNameConstraint = true;

  static $mapping = array(
    'table' => 'event',
    'columns' => array(
      'id' => array(
        'title' => 'Event ID',
        'name' => 'event_id',
        'type' => 'int',
      ),
      'name' => array(
        'title' => 'Name',
        'name' => 'event_name',
        'type' => 'string',
      ),
      'externalId' => array(
        'title' => 'External ID',
        'name' => 'external_id',
        'type' => 'int',
      ),
      'status' => array(
        'title' => 'Status',
        'name' => 'status',
        'type' => 'string',
      ),
      'typeId' => array(
        'title' => 'Type ID',
        'name' => 'type_id',
        'type' => 'int',
      ),
      'classId' => array(
        'title' => 'Class ID',
        'name' => 'class_id',
        'type' => 'int',
      ),
    ),
    'id' => 'id',
    'name' => 'name',
  );
}
