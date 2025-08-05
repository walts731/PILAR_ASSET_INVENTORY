<?php
require_once '../connect.php';

if (isset($_POST['search'])) {
    $search = $_POST['search'];

    $stmt = $conn->prepare("SELECT DISTINCT description FROM assets WHERE description LIKE ? ORDER BY description ASC LIMIT 10");
    $likeSearch = "%" . $search . "%";
    $stmt->bind_param("s", $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<div class="autocomplete-suggestion" style="cursor:pointer; padding:5px;">' . htmlspecialchars($row['description']) . '</div>';
    }
}
?>
