<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values

  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the
  OCILogon below to be your ORACLE username and password -->

  <html>
    <head>
        <title>Players</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="navBar">
            <h2>Navigation</h2>
            <form method="GET" action="Organizations.php">
                <input type="submit" name="navigateToOrganizations" value="Organizations">
            </form>
            <form method="GET" action="Players.php">
                <input type="submit" name="navigateToPlayers" value="Players">
            </form>
            <br /><hr />
        </div>
        <h1 class="pageTitle">Players</h1>
        <div class="dmgAllWeaponsList">
            <h2>Average damage per round (ADR) for all players</h2>
            <!-- <form method="GET" action="Players.php">
                <input type="hidden" id="dmg_allWeapons" name="dmg_allWeapons">
                <input type="submit" name="dmg_allWeapons"></p>
            </form> -->
            <?php
                handleAvgDmgAllWeapons();
            ?>
        </div>

        <hr />

        <h2>Finds player in-game name with the highest kills in stats so far</h2>
        <form method="GET" action="Players.php"> <!--refresh page when submitted-->
            <input type="hidden" id="highestkills" name="highestkills">
            <input type="submit" name="highestkills"></p>
        </form>
        <h2>Checks if two players have played in the same match</h2>
        <form method="POST" action="Players.php"> <!--refresh page when submitted-->
            <input type="hidden" id="checkPlayersSameMatchRequest" name="checkPlayersSameMatchRequest">
            First Player: <input type="text" name="player1"> <br /><br />
            Second Player: <input type="text" name="player2"> <br /><br />            
            <input type="submit" value="Submit" name="checkPlayersSubmit"></p>
        </form>

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr);
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

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
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
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

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_annaw245", "a59754044", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        function handleUpdateRequest() {
            global $db_conn;

            $old_name = $_POST['oldName'];
            $new_name = $_POST['newName'];

            // you need the wrap the old name and new name values with single quotations
            executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
            OCICommit($db_conn);
        }

        function handleResetRequest() {
            global $db_conn;
            // Drop old table
            executePlainSQL("DROP TABLE demoTable");

            // Create new table
            echo "<br> creating new table <br>";
            executePlainSQL("CREATE TABLE demoTable (id int PRIMARY KEY, name char(30))");
            OCICommit($db_conn);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['insNo'],
                ":bind2" => $_POST['insName']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into demoTable values (:bind1, :bind2)", $alltuples);
            OCICommit($db_conn);
        }

        function handleAvgDmgAllWeapons() {
            if (connectToDB()) {
                $result = executePlainSQL("SELECT TMC.in_game_name, AVG(UW.average_damage_per_round) 
                FROM TeamMemberContract TMC 
                JOIN UsesWeapon UW ON TMC.tm_id = UW.tm_id 
                GROUP BY TMC.in_game_name");
                showDmgTable($result);
            }
        }

        //TODO CHRIS
        function showDmgTable($result){
            echo "
                <table class='playerDmgTable'>
                    <tr>
                        <th>Player</th>
                        <th>ADR</th>
                    </tr>";

            while (($row = oci_fetch_row($result)) != false) {
                echo "
                    <tr>
                        <td><i>".$row[0]."</i></td>
                        <td>".round($row[1], 1)."</td>
                    </tr>";
            }
            echo "</table>";
        }

        function handleHighestKills(){
            global $db_conn;
            if (array_key_exists('highestkills', $_GET)) {
                //TODO ERIC: check this query pls
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
                showPlayerHighestKills($result);
                OCICommit($db_conn);
            }
        }

        //TODO CRHIS
        function showPlayerHighestKills(){
            echo "<center><h2>Here are your results!</h2></center>";
            echo "<table>";
            echo "<tr><th>In-game Name</th><th>Number of Kills</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[3] . "</td><tr>";
            }
            echo "</table>";
        }

        function handleCheckPlayersSameMatch(){
            global $db_conn;
            $player1 = $_POST['player1'];
			$player2 = $_POST['player2'];

            executePlainSQL("CREATE VIEW ap1 AS SELECT tm_id, m_id
                FROM AgentPlayed");
            executePlainSQL("CREATE VIEW ap2 AS SELECT tm_id, m_id
                FROM AgentPlayed");
            executePlainSQL("CREATE VIEW pair AS SELECT ap1.tm_id AS player1_tm_id, ap2.tm_id AS player2_tm_id, ap1.m_id
                FROM ap1
                INNER JOIN ap2 
                ON ap1.m_id=ap2.m_id");

            executePlainSQL("CREATE VIEW join1 AS SELECT player1_tm_id, player2_tm_id, in_game_name AS player1_name, m_id
                FROM pair
                INNER JOIN TeamMemberContract
                ON player1_tm_id=tm_id
                WHERE player1_tm_id<>player2_tm_id");

            executePlainSQL("CREATE VIEW join2 AS SELECT player1_name, in_game_name AS player2_name, m_id
                FROM join1
                INNER JOIN TeamMemberContract
                ON player2_tm_id=TeamMemberContract.tm_id");

            executePlainSQL("CREATE VIEW join3 AS SELECT player1_name, player2_name, join2.m_id, s_id
                FROM join2
                INNER JOIN MatchInSeries
                ON join2.m_id=MatchInSeries.m_id
                WHERE player1_name='".$player1."' AND player2_name='".$player2."'");

            executePlainSQL("CREATE VIEW join4 AS SELECT m_id, join3.s_id, e_id, game_date, winning_organization
                FROM join3
                INNER JOIN SeriesInEvent
                ON join3.s_id=SeriesInEvent.s_id");
            
            executePlainSQL("CREATE VIEW join5 AS SELECT m_id, s_id, join4.e_id, game_date, join4.winning_organization, name AS event_name
                FROM join4
                INNER JOIN Event
                ON join4.e_id=Event.e_id");

            executePlainSQL("CREATE VIEW join6 AS SELECT join5.m_id, s_id, e_id, game_date, winning_organization, event_name, map_name
                FROM join5
                INNER JOIN MapPlayed
                ON join5.m_id=MapPlayed.m_id");
            
            $result = executePlainSQL("SELECT *
                FROM join6
                INNER JOIN OrganizationID
                ON winning_organization=o_id");

            executePlainSQL("DROP VIEW ap1");
            executePlainSQL("DROP VIEW ap2");
            executePlainSQL("DROP VIEW pair");
            executePlainSQL("DROP VIEW join1");
            executePlainSQL("DROP VIEW join2");
            executePlainSQL("DROP VIEW join3");
            executePlainSQL("DROP VIEW join4");
            executePlainSQL("DROP VIEW join5");
            executePlainSQL("DROP VIEW join6");

            showSameMatchTable($player1, $player2, $result);
        }

        //TODO CHRIS: 
        function showSameMatchTable($player1, $player2, $result) {
            echo "<h2>Matches ".$player1." and ".$player2." have played together</h2>";
            echo "<table class='sameMatchTable'>";
            echo "
                <tr>
                    <th>Date</th>
                    <th>Event</th>
                    <th>Winning Team</th>
                    <th>Map</th>
                </tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "
                <tr>
                    <td>".$row["GAME_DATE"]."</td>
                    <td>".$row["EVENT_NAME"]."</td>
                    <td>".$row["NAME"]."</td>
                    <td>".$row["MAP_NAME"]."</td>
                </tr";
            }

            echo "</table>";
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('checkPlayersSameMatchRequest', $_POST)) {
                    handleCheckPlayersSameMatch();
                } 
                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('dmg_allWeapons', $_GET)) {
                    handleAvgDmgAllWeapons();
                } else if (array_key_exists('highestkills', $_GET)) {
                    handleHighestKills();
                } 
                disconnectFromDB();
            }
        }

		if (isset($_POST['checkPlayersSubmit'])) {
            handlePOSTRequest();
        } else {
            handleGETRequest();
        }
		?>
	</body>
</html>
