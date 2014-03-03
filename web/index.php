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
    $query->init('armouredvehicules.xtm');
    
    
    //Caractéristiques
    $country = $request->get('country');
    $type = $request->get('type');
    $date = $request->get('date');
    
    $queryBuilder = new QueryBuilder('$blinde');
    $queryBuilder->select('$ID', '$blinde');
    $queryBuilder->where($queryBuilder->addOr('origin', 'whosecountryis', 'isthecountryof', $country));
    $queryBuilder->where($queryBuilder->addOr('category', 'whoseroleis', 'istheroleof', $type));
    $queryBuilder->where($queryBuilder->addOr('activity', 'whosefirstserv', 'isthefirstof', $date));
    
    $query->setQuery($queryBuilder->build());
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $result = $csvManager->export();
    
    $test = $request->get('query');
    
    return $app['twig']->render('index.html.twig', array(
        'url' => $query->getQueryUrl(),
        'query' => $queryBuilder->build(),
        'fileContent' => $csvManager->getCsvString(),
        'headers' => array_shift($result),
        'entities' => $result,
    ));
})
->method('GET|POST');

$app->get('/fiche/{id}', function (Application $app, $id){
    $query = new Query();
    $query->init('armouredvehicules.xtm');
    
    $stringQuery = 'using o for i"http://psi.ontopedia.net/"
select $Country,$Role,$Crew,$Activity,$Gear,$Weight,$Power,$Autonomy,$Height,$Length,$Width from
{
o:origin(o:'.$id.': o:whosecountryis, $Country: o:isthecountryof),
o:category(o:'.$id.': o:whoseroleis, $Role: o:istheroleof),
o:mouvement(o:'.$id.': o:whoserunning, $Gear: o:istherunninggear),
o:activity(o:'.$id.': o:whosefirstserv, $Activity: o:isthefirstof),
o:crew(o:'.$id.': o:whosenumberis, $Crew: o:isnumbof)
},


o:power(o:'.$id.',$Power),
o:autonomy(o:'.$id.',$Autonomy),
o:length(o:'.$id.',$Length),
o:weight(o:'.$id.',$Weight),
o:height(o:'.$id.',$Height),
o:width(o:'.$id.',$Width)?';
    
    $query->setQuery($stringQuery);
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $result = $csvManager->export();
    
    array_shift($result);
   
    return $app['twig']->render('detail.html.twig', array(
        'query' => $stringQuery,
        'result' => $query->execute(),
        'fiche' => array_shift($result)
    ));
});

$app->run();