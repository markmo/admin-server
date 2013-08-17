<?php
namespace Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Services\iRepository;
use Utils\Arr;

abstract class BaseController {

  const TODO = 'Not implemented';

  // repository manager
  protected $repo;

  protected $converter;

  // xml serializer
  protected $serializer;

  protected $validator;

  protected $logger;

  protected $debug;

  protected $pageSize = 12;

  public function __construct(iRepository $repo,
                              $converter,
                              $serializer,
                              $validator,
                              $logger,
                              $debug) {
    $this->repo = $repo;
    $this->converter = $converter;
    $this->serializer = $serializer;
    $this->validator = $validator;
    $this->logger = $logger;
    $this->debug = $debug;
  }

  private function parseOptions(Request $request) {
    $converter = $this->converter;
    $query = $request->query->all();
    $filters = Arr::blacklist($query, array('page', 'per_page', 'sort_by',
      'order', 'from', 'to', 'p', 'pp', 's', 'o', 'f', 't', 'total_pages', 'total_entries'));
    return array(
      'pageIndex' => $request->get('page', $request->get('p', 1)),
      'pageSize' => $request->get('per_page', $request->get('pp', $this->pageSize)),
      'sortKey' => $request->get('sort_by', $request->get('s', $converter->nameColumn)),
      'order' => $request->get('order', $request->get('o', 'asc')),
      'from' => $request->get('from', $request->get('f')),
      'to' => $request->get('to', $request->get('t')),
      'filters' => $filters,
      'shallow' => true,
    );
  }

  public function count(Request $request) {
    return $this->repo->count($this->parseOptions($request));
  }

  public function index($format = 'json', Request $request, Application $app) {
    $converter = $this->converter;
    if ($this->debug)
      $this->logger->addDebug($converter->qualifiedClassName . ':index called');

    $options = $this->parseOptions($request);
    $count = $this->repo->count(array('filters' => $options['filters']));
    $collection = $this->repo->findAll($options);

    switch ($format) {

      case 'xml':
        return $this->serializer->serialize($converter->page($collection, $count), 'xml');
        break;

      case 'xls':
        $data = $converter->xls($collection);
        return new Response($data, 200, array(
          'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
          'Content-length' => strlen($data),
          'Content-Disposition' => 'attachment; filename="'
          . $converter->tableName . '-' . date('Ymd-Hi') . '.xls"',
        ));
        break;

      default:
        return $app->json($converter->page($collection, $count));
    }
  }

  public function facets(Application $app) {
    $facets = array();
    foreach ($this->converter->columns as $column) {

      switch ($column['type']) {

        case 'boolean':
          $facets[$column['title']] = array(
            array('value' => 0, 'label' => 'No'),
            array('value' => 1, 'label' => 'Yes'),
          );
          break;

        case 'one':
          $ref = $column['ref'];
          $facets[$column['title']] = $this->repo->findFacets($app[$ref . '.converter']);
          break;

        case 'string':
          $facets[$column['title']] = array();
          break;
      }
    }
    return $app->json($facets);
  }

  private function validate($model) {
    $converter = $this->converter;
    $nameProperty = $converter->nameProperty;
    if ($model::$uniqueNameConstraint &&
        $this->repo->validateUniqueName($model->$nameProperty)
    ) {
      $nameTitle = $converter->columns[$nameProperty]['title'];
      return new Response($nameTitle . ' already exists', 400);
    }
    $errors = $this->validator->validate($model);
    if (count($errors) > 0) {
      return new Response($errors, 400);
    }
    return false;
  }

  public function create(Request $request) {
    $converter = $this->converter;
    if ($this->debug)
      $this->logger->addDebug('Controllers\BaseController:create called');

    $model = $converter->createModel(object_to_array(json_decode($request->getContent())));
    if ($err = $this->validate($model)) {
      return $err;
    }
    return new Response($this->repo->insert($model), 201);
  }

  public function update($id, Request $request) {
    $converter = $this->converter;
    if ($this->debug)
      $this->logger->addDebug('Controllers\BaseController:update called');

    $model = $converter->createModel(object_to_array(json_decode($request->getContent())));
    if ($err = $this->validate($model)) {
      return $err;
    }
    return $this->repo->update($id, $model);
  }
}
