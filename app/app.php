<?php

$app = require __DIR__ . '/bootstrap.php';

$ok = function () {
  return 'OK';
};

// routes

$app->get('/events.{format}', 'event.controller:index');

$app->get('/events', 'event.controller:index')
    ->value('format', 'json');

$app->get('/events/{eventId}/markets', 'event.controller:markets');

$app->get('/events/facets', 'event.controller:facets');

$app->post('/events/new', 'event.controller:create');

$app->put('/events/{id}', 'event.controller:update');

$app->get('/markets.{format}', 'market.controller:index');

$app->get('/markets', 'market.controller:index')
    ->value('format', 'json');

$app->get('/markets/facets', 'market.controller:facets');

$app->post('/markets', 'market.controller:create');

return $app;
