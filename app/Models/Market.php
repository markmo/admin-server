<?php
namespace Models;

use Swagger\Annotations as SWG;

/**
 * @SWG\Model(id="Market")
 */
class Market extends BaseModel {
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
   * @SWG\Property(name="event", type="Event")
   */
  public $event;

  public static function loadValidatorMetadata(ClassMetadata $metadata) {
    $metadata
    ->addPropertyConstraint('name', new Assert\NotBlank())
    ->addPropertyConstraint('name', new Assert\Length(array('max' => 100)))
    ->addPropertyConstraint('status', new Assert\Choice(array('choices' => array('A', 'S'))))
    ->addPropertyConstraint('event', new Assert\NotNull());
  }

  public static $uniqueNameConstraint = true;

  static $mapping = array(
    'table' => 'market',
    'columns' => array(
      'id' => array(
        'title' => 'Market ID',
        'name' => 'market_id',
        'type' => 'int',
      ),
      'name' => array(
        'title' => 'Name',
        'name' => 'market_name',
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
      'event' => array(
        'title' => 'Event',
        'name' => 'event_id',
        'type' => 'one',
        'ref' => 'event',
      ),
    ),
    'id' => 'id',
    'name' => 'name',
  );
}
