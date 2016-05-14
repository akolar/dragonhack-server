<?php
// {{{ Slim config
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';

$config['displayErrorDetails'] = true;

$app = new \Slim\App([
    "settings" => $config,
]);

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates', [
        'cache' => false
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    return $view;
};
// }}}

function isLoggedIn() {
    return isset($_COOKIE['student_id']);
}

// {{{ Main
$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'landing.html', [
        'title' => 'WBDG Team',
        'auth' => isLoggedIn()
    ]);
})->setName('landing');

$app->get('/login', function ($request, $response, $args) {
    if (isLoggedIn()) {
        return $response->withStatus(302)->withHeader('Location', '/picker');
    }
    return $this->view->render($response, 'login.html', [
        'title' => 'WBDG Team :: Prijava'
    ]);
})->setName('login');

$app->post('/login', function ($request, $response, $args) {
    setcookie('student_id', $_POST['student_id']);
    return $response->withStatus(302)->withHeader('Location', '/picker');
})->setName('process_login');

$app->get('/logout', function ($request, $response, $args) {
    unset($_COOKIE['student_id']);
    setcookie('student_id', null, -1, '/');
    return $response->withStatus(302)->withHeader('Location', '/');
})->setName('logout');

$app->get('/picker', function ($request, $response, $args) {
    if (!isLoggedIn()) {
        return $response->withStatus(302)->withHeader('Location', '/login');
    }

    $student_id = $_COOKIE['student_id'];

    return $this->view->render($response, 'picker.html', [
        'title' => 'WBDG Team :: Izberi zamenjavo',
        'class_info' => getClassesByStudent($student_id),
        'auth' => isLoggedIn()
    ]);
})->setName('picker');
// }}}

$app->run();


// {{{ Helper methods
function getClassesByStudent($id) {
    $source = file_get_contents("https://urnik.fri.uni-lj.si/timetable/2015_2016_letni/allocations?student=$id");

    $doc = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $doc->loadHTML($source);

    $subjects = array();
    $trs = $doc->getElementsByTagName('tr');
    foreach ($trs as $tr) {
        $tds = $tr->getElementsByTagName('td');
        foreach ($tds as $td) {
            $spans = $td->getElementsByTagName('span');
            if ($spans->length == 0) {
                continue;
            }

            $as = $td->getElementsByTagName('a');
            $class_id = preg_split("/(=|&)/", $as->item(0)->getAttribute('href'), 3)[1];
            $class_name = $as->item(0)->nodeValue;

            array_push($subjects, ['id' => $class_id, 'name' => $class_name]);
        }
    }

    return $subjects;
}
// }}}
