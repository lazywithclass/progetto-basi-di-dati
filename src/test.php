<?php
// Database connection parameters
$host = 'localhost'; // or your PostgreSQL server address
$port = '5432';      // default PostgreSQL port
$dbname = 'quibreria';
$user = 'pagemaster';
$password = '';

// Create a connection string
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";

// Connect to PostgreSQL
$dbconn = pg_connect($conn_string);

// Check connection
if (!$dbconn) {
    die("Connection failed: " . pg_last_error());
}

// Query to get all tables in the public schema
$query = "SELECT tablename FROM pg_tables WHERE schemaname = 'public'";

// Execute the query
$result = pg_query($dbconn, $query);

// Check if the query was successful
if (!$result) {
    die("Error in SQL query: " . pg_last_error());
}

// Fetch and display results
echo "<h2>Tables in the 'public' schema:</h2>";
echo "<ul>";

while ($row = pg_fetch_assoc($result)) {
    echo "<li>" . htmlspecialchars($row['tablename']) . "</li>";
}

echo "</ul>";

// Free result resource
pg_free_result($result);

// Close the connection
pg_close($dbconn);
?>
