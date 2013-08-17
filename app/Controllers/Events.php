<?php
namespace Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;

/**
 *
 * @SWG\Resource(
 *   apiVersion="0.1",
 *   swaggerVersion="1.1",
 *   resourcePath="/events",
 *   basePath="http://localhost/"
 * )
 *
 * @SWG\Model(
 *   id="EventForm",
 *   @SWG\Properties(
 *     @SWG\Property(name="name", type="string", required="true"),
 *     @SWG\Property(name="externalId", type="string", required="true"),
 *     @SWG\Property(name="status", type="string", required="true"),
 *     @SWG\Property(name="typeId", type="int", required="true"),
 *     @SWG\Property(name="classId", type="int", required="true")
 *   )
 * )
 */
class Events extends BaseController {
  /**
   *
   * @SWG\Api(
   *   path="/events.{format}",
   *   @SWG\Operations(
   *     @SWG\Operation(
   *       httpMethod="GET",
   *       summary="Get a paged list of Events.",
   *       responseClass="List[Event]",
   *       @SWG\Parameters(
   *         @SWG\Parameter(
   *           name="format",
   *           description="Format of response. Defaults to `json`.",
   *           paramType="path",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="string",
   *           @SWG\AllowableValues(
   *             valueType="LIST",
   *             values="['json', 'xml']"
   *           )
   *         ),
   *         @SWG\Parameter(
   *           name="fields",
   *           description="Comma separated list of fields to project.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="string"
   *         ),
   *         @SWG\Parameter(
   *           name="page",
   *           description="Page index.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="int"
   *         ),
   *         @SWG\Parameter(
   *           name="per_page",
   *           description="Page size.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="int"
   *         ),
   *         @SWG\Parameter(
   *           name="sort_by",
   *           description="Column used to sort results.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="string"
   *         ),
   *         @SWG\Parameter(
   *           name="order",
   *           description="Sort direction.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="string",
   *           @SWG\AllowableValues(
   *             valueType="LIST",
   *             values="['asc', 'desc']"
   *           )
   *         ),
   *         @SWG\Parameter(
   *           name="filter",
   *           description="Filter params.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="json"
   *         ),
   *         @SWG\Parameter(
   *           name="from",
   *           description="Start of date range filter.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="Date"
   *         ),
   *         @SWG\Parameter(
   *           name="to",
   *           description="End of date range filter.",
   *           paramType="query",
   *           required="false",
   *           allowMultiple="false",
   *           dataType="Date"
   *         )
   *       )
   *     )
   *   )
   * )
   */
  public function index($format = 'json', Request $request, Application $app) {
    return parent::index($format, $request, $app);
  }

  public function markets($eventId, Request $request, Application $app) {
    if ($this->debug)
      $this->logger->addDebug('Controllers\Events:markets called');

    $query = $request->query->all();
    $query['event/id'] = $eventId;
    return $app['market.controller']->index('json', $request->duplicate($query), $app);
  }

  /**
   *
   * @SWG\Api(
   *   path="/events",
   *   @SWG\Operations(
   *     @SWG\Operation(
   *       httpMethod="POST",
   *       summary="Create a new Event.",
   *       responseClass="int",
   *       notes="Returns the new Event ID.",
   *       @SWG\Parameters(
   *         @SWG\Parameter(
   *           name="form",
   *           description="JSON object of Event properties",
   *           paramType="body",
   *           required="true",
   *           allowMultiple="false",
   *           dataType="EventForm"
   *         )
   *       ),
   *       @SWG\ErrorResponses(
   *         @SWG\ErrorResponse(
   *           code="400",
   *           reason="Event Name already exists"
   *         ),
   *         @SWG\ErrorResponse(
   *           code="400",
   *           reason="Validation failed"
   *         )
   *       )
   *     )
   *   )
   * )
   */
  public function create(Request $request) {
    return parent::create($request);
  }

  /**
   *
   * @SWG\Api(
   *   path="/events/{id}",
   *   @SWG\Operations(
   *     @SWG\Operation(
   *       httpMethod="PUT",
   *       summary="Update an Event.",
   *       responseClass="int",
   *       notes="Returns the Event ID.",
   *       @SWG\Parameters(
   *         @SWG\Parameter(
   *           name="id",
   *           description="Event ID",
   *           paramType="path",
   *           required="true",
   *           allowMultiple="false",
   *           dataType="int"
   *         ),
   *         @SWG\Parameter(
   *           name="form",
   *           description="JSON object of Event properties",
   *           paramType="body",
   *           required="true",
   *           allowMultiple="false",
   *           dataType="EventForm"
   *         )
   *       ),
   *       @SWG\ErrorResponses(
   *         @SWG\ErrorResponse(
   *           code="400",
   *           reason="Event Name already exists"
   *         ),
   *         @SWG\ErrorResponse(
   *           code="400",
   *           reason="Validation failed"
   *         )
   *       )
   *     )
   *   )
   * )
   */
  public function update($id, Request $request) {
    return parent::update($id, $request);
  }
}
