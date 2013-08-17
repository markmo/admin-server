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
 *   resourcePath="/markets",
 *   basePath="http://localhost/"
 * )
 *
 * @SWG\Model(
 *   id="MarketForm",
 *   @SWG\Properties(
 *     @SWG\Property(name="name", type="string", required="true"),
 *     @SWG\Property(name="externalId", type="string", required="true"),
 *     @SWG\Property(name="status", type="string", required="true"),
 *     @SWG\Property(name="eventId", type="int", required="true")
 *   )
 * )
 */
class Markets extends BaseController {
  /**
   *
   * @SWG\Api(
   *   path="/markets.{format}",
   *   @SWG\Operations(
   *     @SWG\Operation(
   *       httpMethod="GET",
   *       summary="Get a paged list of Markets.",
   *       responseClass="List[Market]",
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

  /**
   *
   * @SWG\Api(
   *   path="/markets/facets",
   *   @SWG\Operations(
   *     @SWG\Operation(
   *       httpMethod="GET",
   *       summary="Get the search facets for Markets."
   *     )
   *   )
   * )
   */
  public function facets(Application $app) {
    return parent::facets($app);
  }

  /**
   *
   * @SWG\Api(
   *   path="/markets",
   *   @SWG\Operations(
   *     @SWG\Operation(
   *       httpMethod="POST",
   *       summary="Create a new Market.",
   *       responseClass="int",
   *       notes="Returns the new Market ID.",
   *       @SWG\Parameters(
   *         @SWG\Parameter(
   *           name="form",
   *           description="JSON object of Market properties",
   *           paramType="body",
   *           required="true",
   *           allowMultiple="false",
   *           dataType="MarketForm"
   *         )
   *       ),
   *       @SWG\ErrorResponses(
   *         @SWG\ErrorResponse(
   *           code="400",
   *           reason="Market Name already exists"
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
   *   path="/markets/{id}",
   *   @SWG\Operations(
   *     @SWG\Operation(
   *       httpMethod="PUT",
   *       summary="Update a Market.",
   *       responseClass="int",
   *       notes="Returns the Market ID.",
   *       @SWG\Parameters(
   *         @SWG\Parameter(
   *           name="id",
   *           description="Market ID",
   *           paramType="path",
   *           required="true",
   *           allowMultiple="false",
   *           dataType="int"
   *         ),
   *         @SWG\Parameter(
   *           name="form",
   *           description="JSON object of Market properties",
   *           paramType="body",
   *           required="true",
   *           allowMultiple="false",
   *           dataType="MarketForm"
   *         )
   *       ),
   *       @SWG\ErrorResponses(
   *         @SWG\ErrorResponse(
   *           code="400",
   *           reason="Market Name already exists"
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
