<?php

use RedacTech\Csv;

$loader = require __DIR__.'/../vendor/autoload.php';

$loader->add('RedacTech', __DIR__.'/../src');

$app = new Silex\Application();

$csv = new Csv();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app['debug'] = true;

$app->get('/', function (Silex\Application $app){
    
    $url  = "http://localhost:8080/omnigator/plugins/query/csv.jsp?processor=tolog";
    $url .= '&query='. urlencode('using o for i"http://psi.ontopedia.net/" instance-of ($machine, o:machineadosespreemballees)?');
    $url .= "&tm=machine___boissons_chaudes.xtm";
    //$url .= '&query='. urlencode('using o for i"http://psi.ontopedia.net/" select $LAPTOP, $MARQUE, $RAM, $MAXIMUM_RAM from {o:a07($LAPTOP  : o:r07a, o:ref17 : o:r07b),o:a01($LAPTOP : o:r01b, $MARQUE: o:r01a),o:a07($LAPTOP  : o:r07a, $RAM: o:r07b),o:a13($LAPTOP  : o:r13b, $MAXIMUM_RAM : o:r13a)}?');
    //$url .= "&tm=laptopssylvain.xtm";
    
     
    $fileContent = utf8_encode(file_get_contents($url));
    
    $csvManager = new Csv();
    $csvManager->import($fileContent);
    
    return $app['twig']->render('index.html.twig', array(
        'url' => $url,
        'fileContent' => $csvManager->getCsvString(),
        'arrayContent' => $csvManager->export(),
    ));
});

$app->run();