<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Services\SongService;
use App\Services\DatabaseService;

require __DIR__ . "/bootstrap.php";


$container = new Container();
AppFactory::setContainer($container);


$container->set('view', function () {
    return Twig::create(__DIR__ . "/app/templates");
});


$app = AppFactory::create();


$app->add(TwigMiddleware::createFromContainer($app));

$app->addErrorMiddleware(true, true, false);

$app->addRoutingMiddleware();


//landing page
$app->get('/', function (Request $request, Response $response) {
    return $this->get('view')->render($response, 'front_page.twig', [
        'right' => '/list'
    ]);
});

//Song list
$app->get('/list', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();
    $SS = new SongService();
    $DS = new DatabaseService();
    $DS->sync();
    $songs = $SS->getSongList($params['sortBy']);
    return $this->get('view')->render($response, 'song_list.twig', [
        'left' => '/',
        'right' => '/song?id=1',
        'songs' => $songs
    ]);
});

//Song
$app->get('/song', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();
    $SS = new SongService();
    $navi = $SS->getAdjacentSongs($params['id']);
    $song = $SS->getSong($params['id']);

    if (key_exists('next', $navi)) {
        return $this->get('view')->render($response, 'song.twig', [
            'left' => $navi['prev'],
            'right' => $navi['next'],
            'id' => $params['id'],
            'name' => $song['SongName'],
            'author' => $song['SongAuthor'],
            'song' => $song['Song'],
            'capo' => $song['Capo'],
            'made' => $song['MadeBy'],
            'made_gender' => $song['MadeGender'],
            'revision' => $song['Revision'],
            'revision_gender' => $song['RevisionGender']
        ]);
    } else {
        return $this->get('view')->render($response, 'song.twig', [
            'left' => $navi['prev'],
            'id' => $params['id'],
            'name' => $song['SongName'],
            'author' => $song['SongAuthor'],
            'song' => $song['Song'],
            'capo' => $song['Capo'],
            'made' => $song['MadeBy'],
            'made_gender' => $song['MadeGender'],
            'revision' => $song['Revision'],
            'revision_gender' => $song['RevisionGender']
        ]);
    }
});

$app->get('/about', function (Request $request, Response $response) {
    return $this->get('view')->render($response, 'about.twig');
});


$app->run();