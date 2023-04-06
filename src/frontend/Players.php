<html>
    <head>
        <title>VCT STATS - Players</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <div class="navBar">
            <h1>VCT STATS</h1>
            <form method="GET" action="Search.php">
                <input type="submit" value="Search">
            </form>
            <form method="GET" action="Organizations.php">
                <input type="submit" value="Organizations">
            </form>
            <form method="GET" action="Players.php">
                <input type="submit" value="Players">
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

        <h2>Finds player in-game name with kills above a threshold</h2>
        <form method="POST" action="Players.php"> <!--refresh page when submitted-->
            <input type="hidden" id="PlayerAboveKillsThresholdRequest" name="PlayerAboveKillsThresholdRequest">
            Threshold value: <input type="text" name="kills_above"> <br /><br />
            <input type="submit" name="playerAboveKillsThresholdSubmit"></p>
        </form>
        <h2>Look up all Match ID if two players have played in the same match</h2>
        <form method="POST" action="Players.php"> <!--refresh page when submitted-->
            <input type="hidden" id="checkPlayersSameMatchRequest" name="checkPlayersSameMatchRequest">
            First Player: <input type="text" name="player1"> <br /><br />
            Second Player: <input type="text" name="player2"> <br /><br />            
            <input type="submit" value="Submit" name="checkPlayersSubmit"></p>
        </form>
        <h2>See headshot percentage by weapon of a player</h2>
        <form method="POST" action="Players.php"> <!--refresh page when submitted-->
            <input type="hidden" id="checkHeadshotPercentage" name="checkHeadshotPercentage">
            Player Name: <input type="text" name="player1"> <br /><br />           
            <input type="submit" value="Submit" name="checkHeadshotSubmit"></p>
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

        function handlePlayerAboveKillsThreshold(){
            global $db_conn;

            $kills_above = $_POST['kills_above'];
        
            executePlainSQL("
                CREATE VIEW player_kills 
                AS SELECT p.tm_id, SUM(uw.kills) as sum_kills 
                FROM Player p 
                INNER JOIN UsesWeapon uw ON p.tm_id = uw.tm_id 
                GROUP BY p.tm_id
                HAVING SUM(kills) >" . $kills_above);

            $result = executePlainSQL("
                SELECT tmc.in_game_name, pk.sum_kills 
                FROM player_kills pk
                INNER JOIN TeamMemberContract tmc 
                ON pk.tm_id = tmc.tm_id");

            executePlainSQL("DROP VIEW player_kills");

            showPlayerHighestKills($result);
            OCICommit($db_conn);
            
        }


        function showPlayerHighestKills($result){
            echo "<center><h2>Here are your results!</h2></center>";
            echo "<table>";
            echo "<tr><th>In-game Name</th><th>Number of Kills</th></tr>";

            while ($row = oci_fetch_row($result)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><tr>";
            }
            echo "</table>";
        }

        function handleCheckPlayersSameMatch(){
            global $db_conn;
            $player1 = $_POST['player1'];
	        $player2 = $_POST['player2'];
		    $result = executePlainSQL("SELECT DISTINCT ap1.m_id
                FROM AgentPlayed ap1
                INNER JOIN TeamMemberContract tmc1 ON tmc1.tm_id = ap1.tm_id
                WHERE tmc1.in_game_name = '" . $player1 . "'
                AND NOT EXISTS (
                    SELECT *
                    FROM TeamMemberContract tmc2
                    WHERE tmc2.in_game_name = '" . $player2 . "'
                    AND NOT EXISTS (
                        SELECT *
                        FROM AgentPlayed ap2
                        WHERE ap2.m_id = ap1.m_id
                        AND ap2.tm_id = tmc2.tm_id
                    )
                )");
            showSameMatchTable($player1, $player2, $result);
        }

        function showSameMatchTable($player1, $player2, $result) {
            echo "<h2>Matches ".$player1." and ".$player2." have played together</h2>";
            echo "<table>";
            echo "
                <tr>
                    <th>Match ID</th>
                </tr>";

            while ($row = OCI_Fetch_Array($result)) {
                echo "
                <tr>
                    <td>".$row[0]."</td>
                </tr";
            }
            echo "</table>";
        }

        function handleHeadShotPercentage(){
            global $db_conn;
            $player1 = $_POST['player1'];
		    $result = executePlainSQL("SELECT in_game_name, UsesWeapon.weapon_name, headshot_percentage
            FROM TeamMemberContract
            INNER JOIN Player
            ON TeamMemberContract.tm_id = Player.tm_id
            INNER JOIN UsesWeapon
            ON UsesWeapon.tm_id = TeamMemberContract.tm_id
            WHERE in_game_name = '" . $player1 . "'");
            headShotPercentageTable($player1, $result);
        }

        function headShotPercentageTable($player1, $result) {
            echo "<h2>Headshot Percentage by Weapon of ".$player1."</h2>";
            echo "<table>";
            echo "
                <tr>
                    <th>Player Name</th>
                    <th>Weapon Name</th>
                    <th>Headshot Percentage</th>
                </tr>";

            while ($row = OCI_Fetch_Array($result)) {
                echo "
                <tr>
                    <td>".$row[0]."</td>
                    <td>".$row[1]."</td>
                    <td>".$row[2]."</td>
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
                } else if (array_key_exists('PlayerAboveKillsThresholdRequest', $_POST)) {
                    handlePlayerAboveKillsThreshold();
                } else if (array_key_exists('checkHeadshotPercentage', $_POST)) {
                    handleHeadShotPercentage();
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
                } 
                disconnectFromDB();
            }
        }

		if (isset($_POST['checkPlayersSubmit']) || isset($_POST['playerAboveKillsThresholdSubmit']) || isset($_POST['checkHeadshotSubmit'])) {
            handlePOSTRequest();
        } else {
            handleGETRequest();
        }
		?>
	</body>
</html>
