<?php

function db_connect (){
		
	$connection = mysql_connect("sql.njit.edu", "ejw3_proj", "ozw6OBAO");
	if (!$connection){
		return("Could not connect to MySQL database: ".mysql_error());
	}
	mysql_select_db("ejw3_proj", $connection);
	
}	
	
function query ($query_str){
	db_connect();
	
	$result=mysql_query($query_str) or die ( mysql_error());
	
	mysql_close();
	
	return $result;
}

function associative($result){
	
	$rows = array();

	while ($row = mysql_fetch_array($result)){
		array_push($rows, $row);	
	}
	
	mysql_free_result($result);
	
	return $rows;
	
}


/* QUERIES */	
function DeleteEvent($id, $user){ 	// DONE
	$result=query("DELETE FROM event WHERE username='$user' AND ID='$id'");	// Return true or false if attempt to delete event that doesn't exist?
	return ($result) ? true : false;	
}	
	
function GetEvents($user){		// DONE
	$result = query("SELECT id, event_name, start_time, end_time, day FROM event WHERE username='$user'");
	
	return associative($result);  
}	

function GetClassTimes($department, $course_number, $semester){		// DONE
	$result = query("SELECT * FROM course_times T INNER JOIN courses C on T.crn=C.crn ".
			" WHERE C.dept = '$department' AND C.number = '$course_number' AND C.semester='$semester' ORDER BY T.crn,T.day;");
	
	return associative($result);
}

function GetAllCourseNumbers($department, $semester){			// DONE
	return associative(query("SELECT DISTINCT C.number, D.name, D.description FROM courses C INNER JOIN ".
			"course_description D ON D.dept = C.dept AND D.number = C.number WHERE C.dept = '$department' and C.semester='$semester'"));
}

function GetSchedules($username, $semester){	//DONE
	$id = query("SELECT schedule_id FROM schedule WHERE user='$username'");
	$id = mysql_fetch_array($id);
	$id = $id{"schedule_id"};
	
	$events = associative(query("SELECT * FROM schedule_event_view WHERE schedule_id='$id'"));
	$courses = associative(query("SELECT * FROM schedule_course_view WHERE schedule_id='$id'"));
	
	$result=array();
	$result{"id"}{"events"}=$events;
	$result{"id"}{"courses"}=$courses;
	
	return $result;
}

function GetDepartments($semester){	//DONE
	$result = query("SELECT DISTINCT dept FROM courses ORDER BY dept");
	return associative($result);
}

function CheckCredentials($username, $password){
    $u = mysql_real_escape_string($username);
    $p = mysql_real_escape_string($password);
	$result = query("SELECT * FROM user WHERE username='$u' AND password='$p'");
	return ($result) ? true : false;
}

function RegisterUser($username, $password){	//DONE
	$result = query("INSERT INTO user VALUES ('$username', '$password')");
   	return ($result) ? true : false;
}

function SaveEvent($event_name, $start, $end, $day, $username){	//DONE
	$result = query("INSERT INTO event(event_name, start_time, end_time, day, username) VALUES ".
					"('$event_name', '$start', '$end', '$day', '$username')");
    return ($result) ? true : false;
}

function SaveSchedule($semester, $user, $schedule_name, $courses, $events){	//DONE
	
	query("INSERT INTO schedule (user, schedule_name) VALUES ('$user', '$schedule_name')");
	$id = query("SELECT MAX(schedule_id) AS schedule_id FROM schedule WHERE user='$user' AND schedule_name='$schedule_name'");
	$id = mysql_fetch_array($id);
	$id = $id{"schedule_id"};
		
	while ($course = array_shift($courses)){
		$result=query("INSERT INTO schedule_course VALUES ('$semester', '$id', '$course')");
		if ($result) { continue; } else { return false; }
	}
	
	while ($event = array_shift($events)){
		$result=query("INSERT INTO schedule_event VALUES ('$semester','$id', '$event')");
		if ($result) { continue; } else { return false; }
	}
	
	return true;
}

function get_event_names ($username){		// DONE
	return associative(query("SELECT event_name FROM event WHERE username='$username'"));
}

function get_all_crn($semester){		// DONE
	return associative(query("SELECT crn FROM courses WHERE semester='$semester'"));
}

function get_dates($semester){			// DONE
	$start = associative(query("SELECT month, day, description FROM dates WHERE semester='$semester' AND type=\"start\""));
	$last = associative(query("SELECT month, day, description FROM dates WHERE semester='$semester' AND type=\"last\""));
	$closed = associative(query("SELECT month, day, description FROM dates WHERE semester='$semester' AND type=\"closed\""));
	
	$dates = array();
	$dates{"start"} = $start;
	$dates{"end"} = $last;
	$dates{"closed"} = $closed;
	
	return $dates;

}

?>