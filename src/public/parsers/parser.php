<?php

$db['host'] = 'localhost';
$db['user'] = 'root';
$db['pass'] = 'toor';
$db['db']	= 'dragonhack';
try {
    $PDO = new PDO('mysql:dbname='.$db['db'].';host='.$db['host'], 
        $db['user'], $db['pass'], 
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
}
catch (PDOException $e) {
    die ($e->getMessage());
}


$dayToId = [
    'ponedeljek' => 1,
    'torek' => 2,
    'sreda' => 3,
    'četrtek' => 4,
    'petek' => 5
];

$source = file_get_contents('https://urnik.fri.uni-lj.si/timetable/2015_2016_letni/allocations?type=LV&type=AV&type=LAB');

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
        $class_id = preg_split("/(=|&)/", $as->item(0)->getAttribute('href'), 3)[1];
        $class_name = explode("_", $as->item(0)->nodeValue, 2)[0];
        $classroom = $as->item(1)->nodeValue;
        storeClass($PDO, $class_id, $class_name, $classroom, $day, $hour);
    }
}

function storeClass($dbh, $classId, $className, $classroom, $day, $hour) {
    $classQuery = $dbh->prepare("SELECT * FROM subject WHERE id_fri=$classId LIMIT 1");
    $classQuery->execute();
    $class_ = $classQuery->fetchAll();
    if (count($class_) == 0) {
        $insert = $dbh->prepare("INSERT INTO subject(name, id_fri) VALUES (:name, :id_fri)");
        $insert->bindParam(":name", $className);
        $insert->bindParam(":id_fri", $classId);
        $insert->execute();
        return storeClass($dbh, $classId, $className, $classroom, $day, $hour);
    }

    $insert = $dbh->prepare("INSERT INTO termin(subject_id, day, hour, room) VALUES (:subj_id, :day, :hour, :room)");
    $insert->bindParam(":subj_id", $class_[0]["id"]);
    $insert->bindParam(":day", $day);
    $insert->bindParam(":hour", $hour);
    $insert->bindParam(":room", $classroom);
    $insert->execute();
}
?>
