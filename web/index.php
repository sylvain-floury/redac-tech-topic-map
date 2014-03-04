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
    $rom = $request->get('ROM');
    $hdd = $request->get('HDD');
    $typeMemoire = $request->get('type-memoire');
    $tailleEcran = $request->get('taille-ecran');
    $technoEcran = $request->get('techno-ecran');
    $resolutionEcran = $request->get('resolution-ecran');
    $couleur = $request->get('Couleurs');
    
    $queryBuilder = new QueryBuilder('$Reference');
    $queryBuilder->select('$ID', '$Reference', '$Fabricant', '$Prix');
    $queryBuilder->where($queryBuilder->addOr('a08', 'r08a', 'r08b', $cpu));
    $queryBuilder->addAnd($queryBuilder->addOr('a07', 'r07a', 'r07b', $ram));
    $queryBuilder->addAnd($queryBuilder->addOr('a14', 'r14a', 'r14b', $rom));
    $queryBuilder->addAnd($queryBuilder->addOr('a09', 'r09a', 'r09b', $hdd));
    $queryBuilder->addAnd($queryBuilder->addOr('a16', 'r16a', 'r16b', $typeMemoire));
    $queryBuilder->addAnd($queryBuilder->addOr('a05', 'r05b', 'r05a', $tailleEcran));
    //$queryBuilder->addAnd($queryBuilder->addOr('a07', 'r07a', 'r07b', $technoEcran));
    $queryBuilder->addAnd($queryBuilder->addOr('a19', 'r19a', 'r19b', $resolutionEcran));
    $queryBuilder->addAnd($queryBuilder->addOr('a06', 'r06a', 'r06b', $couleur));
    
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

$app->get('/fiche/{id}', function (Application $app, $id){
    $query = new Query();
    $query->init('ordinateursportables2602.xtm');
    
    $queryString = 'using o for i"http://psi.ontopedia.net/"
o:a08($CPU: o:r08b, o:'.$id.' : o:r08a),
o:a17($FREQUENCE_DU_PROCESSEUR: o:r17a, o:'.$id.' : o:r17b),
o:a07($CAPACITE_RAM: o:r07b, o:'.$id.' : o:r07a),
o:a13($CAPACITE_MAX_RAM: o:r13a, o:'.$id.' : o:r13b),
o:a14($CAPACITE_DU_DD: o:r14b, o:'.$id.' : o:r14a),
o:a09($TECHNOLOGIE_DU_DD: o:r09b, o:'.$id.' : o:r09a),
o:a18($RESOLUTION_ECRAN: o:r18a, o:'.$id.' : o:r18b),
o:a19($CAMERA_RESOLUTION: o:r19a, o:'.$id.' : o:r19b),
o:a06($COULEUR: o:r06b, o:'.$id.' : o:r06a),
o:a01($FABRICANT: o:r01a, o:'.$id.' : o:r01b),
o:a03($OS_INSTALLE: o:r03b, o:'.$id.' : o:r03a),
{o:o02(o:'.$id.', $WEIGHT),
o:o01(o:'.$id.', $PRICE)}
?';
    
    $query->setQuery($queryString);
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $fiche = $csvManager->export();
    
    return $app['twig']->render('fiche.html.twig', array(
        'query' => $queryString,
        'fiche' => $fiche[1]
    ));
});

$app->run();