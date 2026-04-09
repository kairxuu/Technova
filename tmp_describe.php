<?php
require 'db/db.php';
$query = "DESCRIBE produit;";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . "\n";
}
?>
