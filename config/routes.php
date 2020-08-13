<?php
/**
 * Routes configuration.
 */
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->setRouteClass(DashedRoute::class);

$routes->scope('/', function (RouteBuilder $builder) {

    # Users
    $builder->connect('/edit/:id', ['controller' => 'Users', 'action' => 'edit'])->setPass(['id']);

    $builder->fallbacks();
});
