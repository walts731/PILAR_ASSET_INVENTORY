<?php
require_once '../connect.php';
$category = $_GET['category'] ?? '';
$stmt = $conn->prepare("SELECT * FROM forms WHERE category = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
?>

<h3>Forms under "<?= htmlspecialchars($category) ?>"</h3>
<ul>
    <?php while ($row = $result->fetch_assoc()): ?>
        <li>
            <a href="<?= $row['file_path'] ?>" target="_blank"><?= htmlspecialchars($row['form_title']) ?></a>
        </li>
    <?php endwhile; ?>
</ul>
