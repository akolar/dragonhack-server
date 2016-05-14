<?php

$db['host'] = 'localhost';
$db['user'] = 'root';
$db['pass'] = '';
$db['db']	= 'dragonhack';

try {
    $PDO = new PDO('mysql:dbname='.$db['db'].';host='.$db['host'], 
        $db['user'], $db['pass'], 
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
}
catch (PDOException $e) {
    die ($e->getMessage());
}

//GET ALL SUBJECTS
$classQuery = $PDO->prepare("SELECT `id` FROM `subject`");
$classQuery->execute();
$class_ = $classQuery->fetchAll();
//var_dump($class_);
	
foreach($class_ as $predmet)
{
	//var_dump($predmet);
	//posamezen predmet
	$data = array();

	//potegnemo vse swape za ta predmet
	$swapsSql = $PDO->prepare("SELECT `student_id`, `termin_id` FROM `swap` INNER JOIN `termin` ON termin.id = swap.termin_id WHERE termin.subject_id = ".$predmet['id']);
	$swapsSql->execute();
	$swaps = $swapsSql->fetchAll();
	
	foreach($swaps as $swap)
	{
		$termin_id = $swap['termin_id'];
		$student_id = $swap['student_id'];
		
		//echo $termin_id.' -> '.$student_id."\r\n";
		
		if(isset($data[$student_id]))
		{
			$data[$student_id][] = $termin_id;
		}
		else
		{
			$data[$student_id] = array();
			$data[$student_id][] = $termin_id;
		}
	}
	//echo "Data:\r\n";
	//print_r($data);
	
	$command = count($data)." ";
	foreach($data as $id => $termins) //za vsakega studenta
	{
		//get user termin
		//echo $id."\r\n";
		//print_r($person);
		$userTerminSql = $PDO->prepare("SELECT `term_id` FROM `student` WHERE `id` = ".$id);
		$userTerminSql->execute();
		$termin = $userTerminSql->fetchAll();
		
		$termin = $termin[0]['term_id']; //uporabnikov termin
		$stWanted = count($termins); //stevilo zelja
		//$termins //zeljeni termini
		
		//sestavimo niz
		$command .= $id.' '.$termin.' '.$stWanted;
		foreach($termins as $t)
		{
			$command .= ' '.$t;
		}
		$command .= ' ';
	}	
	//$solved  = exec('java Main ' + trim($command));
	
	$comand = 'java Production '.trim($command);
	exec ($comand , $output , $return_value  ) ;
	if(strlen($output[0] == 0))
	{
		continue;
	}		
	var_dump($output);
	$output = explode(" ", $output[0]);
	for($i = 0; $i < count($output); $i +=2)
	{
		$person_id = $output[$i];
		$newTermin_id = $output[$i+1];
		echo "$person_id -> $newTermin_id\r\n";
		
		$update = $PDO->prepare("UPDATE `student` SET `term_id`= $newTermin_id WHERE `id` = $person_id");
		$update->execute();
	}
	//break;
}



?>
