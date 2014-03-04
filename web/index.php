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
    $query->init('machine___boissons_chaudes.xtm');
    
    
    //Caractéristiques
    $consommable = $request->get('consommable');
    $preparation = $request->get('preparation');
    
    
    $queryBuilder = new QueryBuilder('$machine');
    $queryBuilder->select('$ID', '$machine', '$prix');
    $queryBuilder->where($queryBuilder->addOr('consommable', 'apourconsommable', 'estleconsommablede', $consommable));
    $queryBuilder->addAnd($queryBuilder->addOr('preparation', 'apourpreparation', 'estlapreparationde', $preparation));
    $queryBuilder->join('prix', '$prix');
    
    $query->setQuery($queryBuilder->build());
    
    $csvManager = new Csv();
    $csvManager->import($query->execute());
    
    $result = $csvManager->export();
    
    $test = $request->get('query');
    
    return $app['twig']->render('index.html.twig', array(
        'url' => $query->getQueryUrl(),
        'query' => $queryBuilder->build(),
        'topic' => $request->get('topic'),
        'headers' => array_shift($result),
        'entities' => $result,
    ));
})
->method('GET|POST');

$app->get('/detail', function (Request $request, Application $app){
    $query = new Query();
    $query->init('machine___boissons_chaudes.xtm'); //$request->get('topic')
    $ref = 'o:03';
    
    $stringQuery = 'using o for i"http://psi.ontopedia.net/"
o:prix (o:YY1204FDPIXIE, $prix)
o:consommable ($consommable, o:apourconsommable, o:YY1204FDPIXIE, o:estleconsommablede)
o:preparation ($preparation, o:estlapreparationde, o:YY1204FDPIXIE, o:apourpreparation)
o:pression ($pression, o:apourpression, o:YY1204FDPIXIE, o:estlapressionde)
o:garantie ($garantie, o:apourgarantie, o:YY1204FDPIXIE, o:estlagarantiede)
o:puissance ($puissance, o:apourpuissance, o:YY1204FDPIXIE, o:estlapuissancede)
o:marque ($marque, o:estlamarquede, o:YY1204FDPIXIE, o:apourmarque)
o:couleur (o:YY1204FDPIXIE, $couleur)
o:reference (o:YY1204FDPIXIE, $reference)?';
    
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