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
    $query->init('tablette.xtm');
    
    //Caractéristiques
    $prix = $request->get('prix');
    $constructeur = $request->get('constructeur');
    $os = $request->get('os');
    $stockage = $request->get('stockage');
    $ram = $request->get('ram');
    $tailleEcran = $request->get('taille-ecran');
    $connectique = $request->get('connectique');
    $reseau = $request->get('reseau');

    
    $queryBuilder = new QueryBuilder('$tablette');
    $queryBuilder->select('$ID', '$tablette', '$Prix');

    $queryBuilder->where($queryBuilder->addOr('Marque', 'apourmarque', 'estlamarquede', $constructeur));
    $queryBuilder->addAnd($queryBuilder->addOr('SE', 'utilisecommeSE', 'estleSEutilisepar', $os));
    $queryBuilder->addAnd($queryBuilder->addOr('Taille', 'acommetaille', 'estlataillede', $tailleEcran));
    $queryBuilder->addAnd($queryBuilder->addOr('Technologie', 'utilisecommemoyendetransmission', 'estlemoyendetransmissionutilisepar', $reseau));
    $queryBuilder->addAnd($queryBuilder->addOr('Capacitedestockage', 'acommecapacite', 'estlacapacitede', $stockage));
    $queryBuilder->addAnd($queryBuilder->addOr('Memoirevive', 'acommememoirevive', 'estlamemoirevivede', $ram));
    $queryBuilder->addAnd($queryBuilder->addOr('Connectique', 'acommeconnectique', 'estlaconnectiquede', $connectique));
    
    //$queryBuilder->addAnd('{$Prix = "99e"| $Prix = "300e" | $Prix = "149e"}');
    
    $queryBuilder->join('Prix', '$Prix');
    
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
    $query->init('tablette.xtm');
    
    $queryString = 'using o for i"http://psi.ontopedia.net/"
o:Autonomie(o:'.$id.': o:auneautonomiede, $AUTONOMIE: o:estlautonomiede),
o:Technologie(o:'.$id.': o:utilisecommemoyendetransmission, $RESEAU: o:estlemoyendetransmissionutilisepar),
o:SE(o:'.$id.': o:utilisecommeSE, $SYSTEME: o:estleSEutilisepar),
o:Taille(o:'.$id.': o:acommetaille, $TAILLE: o:estlataillede),
o:Resolutiondecamera(o:'.$id.': o:auneresolutiondecamerade, $CAMERA: o:estlaresolutiondecamerade),
o:Connectique(o:'.$id.': o:acommeconnectique, $CONNECTIQUE: o:estlaconnectiquede),
o:Capacitedestockage(o:'.$id.': o:acommecapacite, $STOCKAGE: o:estlacapacitede),
o:Marque(o:'.$id.': o:apourmarque, $MARQUE: o:estlamarquede),
o:Memoirevive(o:'.$id.': o:acommememoirevive, $MEMOIREVIVE: o:estlamemoirevivede),
o:Resolutiondecran(o:'.$id.': o:acommedefinitiondecran, $RESOLUTION: o:estladefinitiondecrande),
o:Prix(o:'.$id.', $PRIX),
o:Poids(o:'.$id.', $POIDS),
o:Dimensions(o:'.$id.', $DIMENSIONS),
o:Coeurs(o:'.$id.', $NOMBREDECOEURS) ?';
    
    $query->setQuery($queryString);
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $fiche = $csvManager->export();
    
    return $app['twig']->render('fiche.html.twig', array(
        'query' => $queryString,
        'fiche' => $fiche
    ));
});

$app->run();