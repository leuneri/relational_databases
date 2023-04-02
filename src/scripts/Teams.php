<?php

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_leung147", "a47358213", "dbhost.ugrad.cs.ubc.ca:1522/ug");

function executePlainSQL($cmdstr) { 
    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr); 
    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn); // For OCIParse errors pass the       
        // connection handle
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = OCIExecute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement);
        echo htmlentities($e['message']);
        $success = False;
    } else {

    }
    return $statement;
}

function executeBoundSQL($cmdstr, $list) {
    /* Sometimes the same statement will be executed for several times ... only
    the value of variables need to be changed.
    In this case, you don't need to create the statement several times; 
    using bind variables can make the statement be shared and just parsed once.
    This is also very useful in protecting against SQL injection.  
    See the sample code below for how this functions is used */

    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }

    foreach ($list as $tuple) {
        foreach ($tuple as $bind => $val) {
            //echo $val;
            //echo "<br>".$bind."<br>";
            OCIBindByName($statement, $bind, $val);
            unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

        }
        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($statement); // For OCIExecute errors pass the statement handle
            echo htmlentities($e['message']);
            echo "<br>";
            $success = False;
        }
                
    }

}

function showTable($result) { //prints results from a select statement
	echo "<center><h2>Here are your results!</h2></center>";
	echo "<table>";
	echo "<tr><th>Organization ID</th><th>Event ID</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><tr>";
    }
	echo "</table>";

}

if ($db_conn) {
    # using update
    # updates the organization or the event in which the organization is participating in
    if (array_key_exists('Update', $_POST)) {
        $current_oid = $_POST['current_oid'];
        $current_eid = $_POST['current_eid'];
        $new_oid = $_POST['new_oid'];
        $new_eid = $_POST['new_eid'];
        $result = executePlainSQL("
        UPDATE ParticipatingIn
        SET o_id ='" . $new_oid . "', e_id ='" . $new_eid . "'
        WHERE od_id ='" . $current_oid . "' , e_id ='" . $current_eid . "';    
	");
        showTable($result);
        OCICommit($db_conn);
    }
    if (array_key_exists("log off")) {
        OCILogoff($db_conn);
    }
}

