<?php
// {{{ Slim config
$db['host'] = 'localhost';
$db['user'] = 'root';
$db['pass'] = 'toor';
$db['db']   = 'dragonhack';
$database = new PDO('mysql:dbname='.$db['db'].';host='.$db['host'], 
    $db['user'], $db['pass'], 
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

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

$app->post('/picker', function ($request, $response, $args) {
    if (!isLoggedIn()) {
        return $response->withStatus(302)->withHeader('Location', '/login');
    }

    global $database;

    $student_id = $_COOKIE['student_id'];

    $data = $request->getParsedBody();
    $success = addStudent($student_id, $data['subject'], $data['termin']);

    if ($success == true) {
        return $response->withStatus(302)->withHeader('Location', '/success');
    } else {
        return $response->withStatus(302)->withHeader('Location', '/error');
    }
})->setName('picker-post');

$app->get('/api/termin/{id}', function ($request, $response, $args) {
    global $database;
    
    $cl_id = $args['id'];
    $classQuery = $database->prepare("SELECT t.day, t.id, t.hour, t.room FROM termin t INNER JOIN subject s ON t.subject_id=s.id WHERE s.id_fri=$cl_id");
    $classQuery->execute();
    $class_ = $classQuery->fetchAll();

    $newResponse = $response->withHeader('Content-type', 'application/json');
    $body = $newResponse->getBody();
    $body->write(json_encode($class_));

    return $newResponse;
})->setName('termin-info');

$app->get('/success', function ($request, $response, $args) {
    return $this->view->render($response, 'success.html', [
        'title' => 'WBDG Team :: Uspešno',
        'auth' => isLoggedIn()
    ]);
})->setName('success');

$app->get('/error', function ($request, $response, $args) {
    return $this->view->render($response, 'error.html', [
        'title' => 'WBDG Team :: Napaka',
        'auth' => isLoggedIn()
    ]);
})->setName('success');
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

function addStudent($id, $subject, $wants) {
    $dayToId = [
        'ponedeljek' => 1,
        'torek' => 2,
        'sreda' => 3,
        'četrtek' => 4,
        'petek' => 5
    ];

    $source = file_get_contents("https://urnik.fri.uni-lj.si/timetable/2015_2016_letni/allocations?activity=$subject&student=$id");

    $doc = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $doc->loadHTML($source);
    $trs = $doc->getElementsByTagName('tr');

    foreach ($trs as $tr) {
        $tds = $tr->getElementsByTagName('td');
        foreach ($tds as $td) {
            $spans = $td->getElementsByTagName('span');
            if ($spans->length == 0) {
                continue;
            }

            $arr = explode(" ", trim($spans->item(0)->nodeValue), 3);
            $day = $dayToId[$arr[0]];
            $hour = explode(":", $arr[1])[0];

            $as = $td->getElementsByTagName('a');
            $classroom = $as->item(1)->nodeValue;

            insertStudent($id, $subject, $day, $hour, $classroom, $wants);
            return true;
        }
    }

    return false;
}

function insertStudent($id, $subject, $day, $hour, $room, $wants) {
    global $database;

    $classQuery = $database->prepare("SELECT * FROM termin INNER JOIN subject ON termin.subject_id=subject.id WHERE id_fri=$subject AND day=$day AND hour=$hour AND room='$room' LIMIT 1");
    $classQuery->execute();
    $class_ = $classQuery->fetchAll();

    $insertStud = $database->prepare("INSERT INTO student(term_id, student_id) VALUES (:tid, :sid)");
    $insertStud->bindParam(":tid", $class_[0][0]);
    $insertStud->bindParam(":sid", $id);
    $insertStud->execute();

    $c = $class_[0][0];
    $selectStud = $database->prepare("SELECT * FROM student WHERE student_id=$id AND term_id=$c LIMIT 1");
    $selectStud->execute();
    $student = $selectStud->fetchAll();

    foreach ($wants as $w) {
        $insert = $database->prepare("INSERT INTO swap(student_id, termin_id) VALUES (:sid, :tid)");
        $insert->bindParam(":sid", $student[0]["id"]);
        $insert->bindParam(":tid", $w);
        $insert->execute();
    }
}

function isLoggedIn() {
    return isset($_COOKIE['student_id']);
}

// }}}
