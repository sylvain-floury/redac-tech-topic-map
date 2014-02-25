<?php

use RedacTech\Csv;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../vendor/autoload.php';

$loader->add('RedacTech', __DIR__.'/../src');

$app = new Silex\Application();

$csv = new Csv();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app['debug'] = true;

$app->match('/', function (Request $request, Application $app){
    
    $query = new \RedacTech\Query();
    $query->init($request->get('topic'));
    $query->setQuery($request->get('query'));
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $test = $request->get('query');
    
    return $app['twig']->render('index.html.twig', array(
        'url' => $query->getQueryUrl(),
        'query' => $request->get('query'),
        'topic' => $request->get('topic'),
        'fileContent' => $csvManager->getCsvString(),
        'arrayContent' => $csvManager->export(),
    ));
})
->method('GET|POST');

$app->run();