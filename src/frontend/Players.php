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
	echo "<tr><th>In-game Name</th><th>Number of Kills</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><tr>";
    }
	echo "</table>";

}

function sameMatchTable($result) { //prints results from a select statement
	echo "<center><h2>Here are your results!</h2></center>";
	echo "<table>";
	echo "<tr><th>Match ID</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row[0] . "</td><tr>";
    }
	echo "</table>";

}

if ($db_conn) {
    # using aggregation to group by player and find the player's '
    # average damage per round across all weapons
    if (array_key_exists('dmg_allweapons', $_GET)) {
        $result = executePlainSQL("SELECT TMC.in_game_name, AVG(UW.average_damage_per_round) 
        FROM TeamMemberContract TMC 
        JOIN UsesWeapon UW ON TMC.tm_id = UW.tm_id 
        GROUP BY TMC.in_game_name");
        showDmgTable($result);
        OCICommit($db_conn);
    }

    # using join, selection, projection, aggregation with having
    # Finds player in-game name with the highest kills in stats so far
    if (array_key_exists('highest combat score player', $_POST)) {
        $result = executePlainSQL("
        SELECT in_game_name, MAX(sum_kills) as kills
        FROM (
            SELECT tm_id, weapon_name, SUM(kills) as sum_kills
            FROM Player AS p
            INNER JOIN UsesWeapon AS uw
            ON p.tm_id = uw.tm_id
            GROUP BY p.tm_id, uw.weapon_name
            HAVING SUM(kills) > 0 
            ) AS player_kills
        INNER JOIN TeamMemberContract AS tmc 
        ON player_kills.tm_id = tmc.tm_id
        GROUP BY tmr.in_game_name;");
        showTable($result);
        OCICommit($db_conn);
    }

    # Using division
    # Checks for matches that have both indicated players participating in
    if (array_key_exists('Same Match for Two Players', $_POST)) {
        $player1 = $_POST['Player 1 (In-game name)'];
        $player2 = $_POST['Player 2 (In-game name)'];
        $result1 = executePlainSQL("
        SELECT DISTINCT m_id
        FROM (
            SELECT ap1.tm_id AS player1_tm_id, ap2.tm_id AS player2_tm_id, ap1.m_id
            FROM AgentPlayed AS ap1
            INNER JOIN AgentPlayed AS ap2 
            ON ap1.m_id = ap2.m_id),
        TeamMemberContract AS tmc1,
        TeamMemberContract AS tmc2
        WHERE tmc1.in_game_name ='" . $player1 . "' AND tmc2.in_game_name='" . $player2 . "' 
        AND tmc1.tm_id = player1_tm_id
        AND tmc2.tm_id = player2_tm_id");
        sameMatchTable($result1);
        OCICommit($db_conn);
    }

    if (array_key_exists("log off")) {
        OCILogoff($db_conn);
    }
}