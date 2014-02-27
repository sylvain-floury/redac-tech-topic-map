<?php

use RedacTech\Csv;
use RedacTech\QueryBuilder;
use RedacTech\Query;
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
    
    $query = new Query();
    $query->init($request->get('topic'));
    
    //Caractéristiques
    $cpu = $request->get('CPU');
    $ram = $request->get('RAM');
    
    $queryBuilder = new QueryBuilder('$Reference');
    $queryBuilder->select('$ID', '$Reference', '$Fabricant', '$Prix');
    $queryBuilder->where($queryBuilder->addOr('a08', 'r08a', 'r08b', $cpu));
    $queryBuilder->addAnd($queryBuilder->addOr('a07', 'r07a', 'r07b', $ram));
    //$queryBuilder->addAnd($Prix.'<500');
    $queryBuilder->join('o01', '$Prix');
    $queryBuilder->join('a01', '$Fabricant', 'r01b', 'r01a');
    
    $query->setQuery($queryBuilder->build());
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $result = $csvManager->export();
    
    $test = $request->get('query');
    
    return $app['twig']->render('index.html.twig', array(
        'url' => $query->getQueryUrl(),
        'query' => $queryBuilder->build(),
        'topic' => $request->get('topic'),
        'fileContent' => $csvManager->getCsvString(),
        'headers' => array_shift($result),
        'entities' => $result,
    ));
})
->method('GET|POST');

$app->run();