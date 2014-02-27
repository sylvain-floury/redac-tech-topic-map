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
    
    $queryBuilder = new QueryBuilder();
    $queryBuilder->where($queryBuilder->addOr('a08', 'r08a', 'r08b', $cpu));
    
    $query->setQuery($queryBuilder->build());
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $test = $request->get('query');
    
    return $app['twig']->render('index.html.twig', array(
        'url' => $query->getQueryUrl(),
        'query' => $queryBuilder->build(),
        'topic' => $request->get('topic'),
        'fileContent' => $csvManager->getCsvString(),
        'arrayContent' => $csvManager->export(),
    ));
})
->method('GET|POST');

$app->get('/detail', function (Request $request, Application $app){
    $query = new Query();
    $query->init('ordinateursportables2602.xtm'); //$request->get('topic')
    $ref = 'o:03';
    
    $stringQuery = 'using o for i"http://psi.ontopedia.net/"
o:a08($CPU: o:r08b, '.$ref.' : o:r08a),
o:a17($FREQUENCE_DU_PROCESSEUR: o:r17a, '.$ref.' : o:r17b),
o:a07($CAPACITE_RAM: o:r07b, '.$ref.' : o:r07a),
o:a13($CAPACITE_MAX_RAM: o:r13a, '.$ref.' : o:r13b),
o:a14($CAPACITE_DU_DD: o:r14b, '.$ref.' : o:r14a),
o:a09($TECHNOLOGIE_DU_DD: o:r09b, '.$ref.' : o:r09a),
o:a18($RESOLUTION_ECRAN: o:r18a, '.$ref.' : o:r18b),
o:a19($CAMERA_RESOLUTION: o:r19a, '.$ref.' : o:r19b),
o:a06($COULEUR: o:r06b, '.$ref.' : o:r06a),
o:a01($FABRICANT: o:r01a, '.$ref.' : o:r01b),
o:a03($OS_INSTALLE: o:r03b, '.$ref.' : o:r03a),
{o:o02('.$ref.', $WEIGHT),
o:o01('.$ref.', $PRICE)}?';
    
    $query->setQuery($stringQuery);
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    return $app['twig']->render('detail.html.twig', array(
        'query' => $stringQuery,
        'result' => $query->execute(),
        'fiche' => $csvManager->export()
    ));
});

$app->run();