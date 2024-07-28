<?php
$servername = "localhost";
$username = "pagemaster";
$password = "";
$dbname = "quibreria";

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to insert data into the table
$sql = "INSERT INTO your_table_name (column1, column2) VALUES ('Value1', 'Value2')";

// Execute the query and check if it was successful
if ($conn->query($sql) === TRUE) {
    echo "Record inserted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>
