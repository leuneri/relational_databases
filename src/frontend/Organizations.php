<html>    
	<head>
        <title>VCT STATS - Organizations</title>
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
        <div class="pageContent">
            <div class="editOrgsMenu">
                <h2>Add new Organization</h2>
                <form method="POST" action="Organizations.php"> <!--refresh page when submitted-->
                    <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
                    Name: <input type="text" name="ins_name"> <br /><br />
                    Ranking: <input type="text" name="ins_ranking"> <br /><br />
                    Region: <input type="text" name="ins_region"> <br /><br />
                    Winrate: <input type="text" name="ins_winrate"> <br /><br />
                    <input type="submit" value="Insert" name="insertSubmit"></p>
                </form>
                <h2>Delete an Organization</h2>
                <form method="POST" action="Organizations.php"> <!--refresh page when submitted-->
                    <input type="hidden" id="deleteQueryRequest" name="deleteQueryRequest">
                    Organization to Delete Name: <input type="text" name="del_name"> <br /><br />
                    <input type="submit" value="Delete" name="deleteSubmit"></p>
                </form>
                <h2>Update Organization Ranking</h2>
                <form method="POST" action="Organizations.php"> <!--refresh page when submitted-->
                    <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
                    Organization: <input type="text" name="org_name"> <br /><br />
                    New Winrate: <input type="text" name="upd_wr"> <br /><br />            
                    <input type="submit" value="Update" name="updateSubmit"></p>
                </form>
            </div>
            <h1 class="pageTitle">Organizations</h1>
            <div class="orgsDisplay">
                <div class="topRegion">
                    <h2>Leading Region</h2>
                    <?php
                        handleRegionAvgWinRate();
                    ?>
                </div>
                <div class="orgsList">
                    <h2>List of Teams</h2>
                    <?php
                        showOrgList();
                    ?>
                </div>
            </div>
        </div>
        <?php	//this tells the system that it's no longer just parsing html; it's now parsing PHP

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
                // echo "<br>running ".$cmdstr."<br>";
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

            //TODO CHRIS: Show list of teams on page load (instead of after button is pressed)
            function showOrgList() {
                if (connectToDB()) {
                    $result = executePlainSQL("SELECT * FROM Organization");
            
                    echo "<table class='orgsListTable'>";
                    echo "
                        <tr>
                            <th>Name</th>
                            <th>Ranking</th>
                            <th>Region</th>
                            <th>Win Rate</th>
                        </tr>";
                    
                    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                        echo "
                            <tr>
                                <td>" . $row["NAME"] . "</td>
                                <td>" . $row["RANKING"] . "</td>
                                <td>" . $row["REGION"] . "</td>
                                <td>" . $row["WIN_RATE"] * 100 . "%</td>
                            </tr>";
                    }
                    echo "</table>";

                    disconnectFromDB();
                }
            }

            function showOrganizationTable($result) { //prints results from a select statement
                echo "<center><h2>Here are your results!</h2></center>";
                echo "<table>";
                echo "<tr><th>Organization ID</th><th>Win Rate</th></tr>";
            
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>" . $row[0] . "</td><td>" . $row[3] . "</td><tr>";
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
                $org_name = $_POST['org_name'];
                $upd_wr = $_POST['upd_wr'];
                $result = executePlainSQL("UPDATE Organization SET win_rate='".$upd_wr."' WHERE name='".$org_name."'");
                showOrganizationTable($result);
                OCICommit($db_conn);
            }

            function handleDeleteRequest() {
                global $db_conn;
            
                //Getting the values from user
                $tuple = array (
                    ":bind1" => $_POST['del_name'],
                );
            
                $alltuples = array (
                    $tuple
                );
            
                $result = executePlainSQL("SELECT OID.o_id 
                    FROM OrganizationID OID, Organization O
                    WHERE OID.name = O.name AND O.name = '" . $_POST['del_name'] ."'" );

                $row = oci_fetch_row($result);
                $oid = $row[0];
                executePlainSQL("DELETE FROM Organization WHERE name = '" . $_POST['del_name'] ."'" );
                executePlainSQL("DELETE FROM OrganizationID WHERE o_id = '" . $oid ."'" );
            
                OCICommit($db_conn);
            }

            function handleInsertRequest() {
                global $db_conn;
            
                //Getting the values from user and insert data into the table
                $tuple = array (
                    ":bind1" => $_POST['ins_name'],
                    ":bind2" => $_POST['ins_ranking'],
                    ":bind3" => $_POST['ins_region'],
                    ":bind4" => $_POST['ins_winrate']
                );
            
                $alltuples = array (
                    $tuple
                );
            
                executeBoundSQL("insert into Organization values (:bind1, :bind2, :bind3, :bind4)", $alltuples);

                //now create an org ID and insert into OrganizationID
                $randomNumber = rand(10, 100000);
            
                $tuple2 = array (
                    ":bind1" => $randomNumber,
                    ":bind2" => $_POST['ins_name'],
                );
            
                $alltuples2 = array (
                    $tuple2
                );
                executeBoundSQL("insert into OrganizationID values (:bind1, :bind2)", $alltuples2);

            
                OCICommit($db_conn);
            }

            function handleCountRequest() {
                global $db_conn;
            
                $result = executePlainSQL("SELECT Count(*) FROM demoTable");
            
                if (($row = oci_fetch_row($result)) != false) {
                    echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
                }
            }

            function handleRegionAvgWinRate(){ 
                if (connectToDB()) {
                    executePlainSQL("CREATE VIEW RegionAvgWinRate AS SELECT O.region, AVG(O.win_rate) AS avgwr FROM Organization O GROUP BY O.region");
                    $result = executePlainSQL("SELECT region, avgwr FROM RegionAvgWinRate WHERE avgwr = (SELECT MAX(avgwr) FROM RegionAvgWinRate)");
                    executePlainSQL("DROP VIEW RegionAvgWinRate");
                    
                    if (($row = oci_fetch_row($result)) != false) {
                        echo "<p>The top region is <b><i>" . $row[0] . "</i></b></p>";
                    }

                    disconnectFromDB();
                }
            }

            // HANDLE ALL POST ROUTES
            // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
            function handlePOSTRequest() {
                if (connectToDB()) {
                    if (array_key_exists('deleteQueryRequest', $_POST)) {
                        handleDeleteRequest();
                    } else if (array_key_exists('updateQueryRequest', $_POST)) {
                        handleUpdateRequest();
                    } else if (array_key_exists('insertQueryRequest', $_POST)) {
                        handleInsertRequest();
                    }
                
                    disconnectFromDB();
                }
            }

            // HANDLE ALL GET ROUTES
            // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
            function handleGETRequest() {
                if (connectToDB()) {
                    if (array_key_exists('countTuples', $_GET)) {
                        handleCountRequest();
                    }
                
                    disconnectFromDB();
                }
            }

            if (isset($_POST['deleteSubmit']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
                handlePOSTRequest();
            } else {
                handleGETRequest();
            }
        ?>
	</body>
</html>
