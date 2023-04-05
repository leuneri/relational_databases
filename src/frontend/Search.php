<html>
    <head>
        <title>VCT STATS - Search</title>
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
            <h1 class="pageTitle">Search</h1>
            <div class="viewRelation">
                <h3>Query relations in database</h3>
                <div class="viewRelationFilter">
                    <form method="POST" action="Search.php">
                        <select name="selectRelation" id="selectRelation">
                            <option value="" selected>-- Select a table --</option>
                            <?php createRelationsDropdown(); ?>
                        </select>
                        <input type="submit" value="Select Table">
                    </form>                   
                </div>
                <div class="viewRelationDisplay">

                </div>
            </div>
        </div>
	</body>
    <?php
        $success = True;
        $db_conn = NULL;
        $show_debug_alert_messages = False;

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

        function createRelationsDropdown() {
            if (connectToDB()) {
                $result = executePlainSQL("SELECT table_name
                    FROM user_tables");

                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<option value='" . $row["TABLE_NAME"] . "'>" . $row["TABLE_NAME"] . "</option>";
                }

                disconnectFromDB();
            }
        }

        function createAttributeCheckboxes($tableName) {
            if (connectToDB() && $tableName != "") {
                $result = executePlainSQL("SELECT * FROM " . $tableName);
                $nCols = oci_num_fields($result);

                echo "
                    <div class='attributeBoxes viewRelationFilter pageContent'>
                        Select attributes for <b>" . $tableName . "</b> relation
                        <form method='POST' action='Search.php'>
                            <input type='hidden' id='selectAttributesRequest' name='selectAttributesRequest'>
                            <input type='hidden' id='relationName' name='relationName' value='" . $tableName . "'>";

                for ($i = 1; $i <= $nCols; $i++) {
                    $colName = oci_field_name($result, $i);
                    echo "
                        <span class='checkBox'>
                            <input type='checkbox' id='att_" . $colName . "' name='att_" . $colName . "' value='" . $colName . "     '>
                            <label for='att_" . $colName . "'>" . $colName . "</label>
                        </span>";
                }
                echo "
                            <br><br>
                            <input type='submit' value='Select Attributes'>
                        </form>
                    </div>";

                disconnectFromDB();
            }
        }

        function showSearchResults() {
            if (connectToDB()) {
                $table = $_POST['relationName'];
                $attributes = array();

                $getCols = executePlainSQL("SELECT * FROM " . $table);
                $nCols = oci_num_fields($getCols);

                for ($i = 1; $i <= $nCols; $i++) {
                    $colName = oci_field_name($getCols, $i);

                    if ($_POST['att_' . $colName]) {
                        array_push($attributes, $colName);
                    }
                }

                if (empty($attributes)) {
                    echo "<span id='noAttributesMsg'><i>No attributes selected</i></span>";
                    disconnectFromDB();
                    return;
                }

                $query = "SELECT";

                for ($i = 0; $i < sizeof($attributes); $i++) {
                    $current = $attributes[$i];
                    $query .= " " . $current;

                    if ($i != sizeof($attributes) - 1) {
                        $query .= ",";
                    }
                }

                $query .= " FROM " . $table;

                $result = executePlainSQL($query);

                echo "<table class='searchResults'>";

                echo "<tr>";
                for ($i = 0; $i < sizeof($attributes); $i++) {
                    echo "<th>" . $attributes[$i] . "</th>";
                }                
                echo "</tr>";

                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr>";
                    for ($i = 0; $i < sizeof($attributes); $i++) {
                        echo "<td>" . $row[$attributes[$i]] . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</table>";

                disconnectFromDB();
            } 
        }

        if (isset($_POST['selectRelation'])) {
            createAttributeCheckboxes($_POST['selectRelation']);
        }

        if (isset($_POST['selectAttributesRequest'])) {
            showSearchResults();
        }
    ?>
</html>
