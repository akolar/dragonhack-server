<?php

function addStudent($id, $subject, $wants) {
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

            insertStudent($PDO, $id, $subject, $day, $hour, $classroom, $wants);
            return true;
        }
    }

    return false;
}

function insertStudent($dbh, $id, $subject, $day, $hour, $room, $wants) {
    $classQuery = $dbh->prepare("SELECT * FROM termin INNER JOIN subject ON termin.subject_id=subject.id WHERE id_fri=$subject AND day=$day AND hour=$hour AND room='$room' LIMIT 1");
    var_dump($classQuery);
    $classQuery->execute();
    $class_ = $classQuery->fetchAll();

    $insertStud = $dbh->prepare("INSERT INTO student(term_id, student_id) VALUES (:tid, :sid)");
    $insertStud->bindParam(":tid", $class_[0][0]);
    $insertStud->bindParam(":sid", $id);
    $insertStud->execute();

    $c = $class_[0][0];
    $selectStud = $dbh->prepare("SELECT * FROM student WHERE student_id=$id AND term_id=$c LIMIT 1");
    $selectStud->execute();
    $student = $selectStud->fetchAll();

    foreach ($wants as $w) {
        $insert = $dbh->prepare("INSERT INTO swap(student_id, termin_id) VALUES (:sid, :tid)");
        $insert->bindParam(":sid", $student[0]["id"]);
        $insert->bindParam(":tid", $w);
        $insert->execute();
    }
}
?>
