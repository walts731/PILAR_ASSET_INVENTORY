<?php
session_start();
require_once '../connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

function redirectWithFlash(string $type, string $message, string $location = 'borrow.php'): void
{
    $_SESSION[$type] = $message;
    header('Location: ' . $location);
    exit();
}

function isValidDate(?string $value): bool
{
    if (empty($value)) {
        return false;
    }
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function generateSubmissionNumber(mysqli $conn): string
{
    $datePrefix = date('Ymd');
    $pattern = "BFS-{$datePrefix}-%";

    $stmt = $conn->prepare('SELECT submission_number FROM borrow_form_submissions WHERE submission_number LIKE ? ORDER BY submission_number DESC LIMIT 1');
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $stmt->bind_result($latestNumber);

    $nextCounter = 1;
    if ($stmt->fetch()) {
        $parts = explode('-', (string) $latestNumber);
        if (count($parts) === 3) {
            $counterPart = ltrim($parts[2], '0');
            $nextCounter = ((int) $counterPart) + 1;
        }
    }

    $stmt->close();

    return sprintf('BFS-%s-%03d', $datePrefix, $nextCounter);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithFlash('error', 'Invalid request method.');
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    redirectWithFlash('error', 'Security token mismatch. Please try again.');
}

$guestName = trim($_POST['guest_name'] ?? '');
$barangay = trim($_POST['barangay'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$dateBorrowed = $_POST['date_borrowed'] ?? '';
$scheduleReturn = $_POST['schedule_return'] ?? '';
$releasingOfficer = trim($_POST['releasing_officer'] ?? '');
$approvedBy = trim($_POST['approved_by'] ?? '');

$errors = [];

if ($guestName === '') {
    $errors[] = 'Borrower name is required.';
}
if ($barangay === '') {
    $errors[] = 'Barangay is required.';
}
if ($contact === '') {
    $errors[] = 'Contact number is required.';
} elseif (!preg_match('/^09\d{9}$/', $contact)) {
    $errors[] = 'Contact number must be an 11-digit number starting with 09.';
}
if (!isValidDate($dateBorrowed)) {
    $errors[] = 'Please provide a valid date borrowed.';
}
if (!isValidDate($scheduleReturn)) {
    $errors[] = 'Please provide a valid schedule of return.';
}
if (isValidDate($dateBorrowed) && isValidDate($scheduleReturn) && $scheduleReturn < $dateBorrowed) {
    $errors[] = 'Schedule of return cannot be earlier than the date borrowed.';
}
if ($releasingOfficer === '') {
    $errors[] = 'Releasing officer is required.';
}
if ($approvedBy === '') {
    $errors[] = 'Approving authority is required.';
}

$itemsInput = $_POST['items'] ?? [];
$rawItems = [];
$assetIds = [];

if (!is_array($itemsInput) || empty($itemsInput)) {
    $errors[] = 'Please add at least one asset to borrow.';
} else {
    foreach ($itemsInput as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        $assetId = isset($item['asset_id']) ? (int) $item['asset_id'] : 0;
        $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
        $remarks = trim($item['remarks'] ?? '');
        $customDescription = trim($item['description'] ?? '');
        $rowNumber = $index + 1;

        if ($assetId <= 0) {
            $errors[] = "Please choose a valid asset for item #{$rowNumber}.";
            continue;
        }

        if ($quantity <= 0) {
            $errors[] = "Quantity must be at least 1 for item #{$rowNumber}.";
        }

        if (in_array($assetId, $assetIds, true)) {
            $errors[] = 'Each asset can only be requested once per submission.';
        }

        $assetIds[] = $assetId;
        $rawItems[] = [
            'asset_id' => $assetId,
            'quantity' => $quantity,
            'remarks' => $remarks,
            'description' => $customDescription,
            'row' => $rowNumber,
        ];
    }
}

if (empty($rawItems)) {
    $errors[] = 'Please add at least one valid asset to borrow.';
}

if (!empty($errors)) {
    redirectWithFlash('error', 'Unable to submit borrow request. ' . implode(' ', $errors));
}

try {
    $placeholders = implode(',', array_fill(0, count($assetIds), '?'));
    $assetSql = "SELECT a.id, a.asset_name, a.description, a.property_no, a.inventory_tag, a.quantity, a.status, c.category_name
                 FROM assets a
                 LEFT JOIN categories c ON a.category = c.id
                 WHERE a.id IN ({$placeholders})";
    $stmt = $conn->prepare($assetSql);
    $types = str_repeat('i', count($assetIds));
    $stmt->bind_param($types, ...$assetIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $assetsData = [];
    while ($row = $result->fetch_assoc()) {
        $assetsData[(int) $row['id']] = $row;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    redirectWithFlash('error', 'Failed to fetch asset information. Please try again.');
}

if (count($assetsData) !== count($assetIds)) {
    redirectWithFlash('error', 'One or more selected assets could not be found. Please refresh the page and try again.');
}

$preparedItems = [];
$validationErrors = [];

foreach ($rawItems as $item) {
    $asset = $assetsData[$item['asset_id']] ?? null;
    if (!$asset) {
        $validationErrors[] = 'A selected asset could not be validated.';
        continue;
    }

    $status = strtolower($asset['status'] ?? '');
    if (!in_array($status, ['serviceable', 'available'], true)) {
        $validationErrors[] = sprintf('Asset "%s" is not available for borrowing.', $asset['asset_name'] ?? ('#' . $asset['id']));
    }

    $availableQty = (int) ($asset['quantity'] ?? 0);
    if ($availableQty <= 0) {
        $validationErrors[] = sprintf('Asset "%s" has no available quantity left.', $asset['asset_name'] ?? ('#' . $asset['id']));
    } elseif ($item['quantity'] > $availableQty) {
        $validationErrors[] = sprintf(
            'Requested quantity for "%s" exceeds available stock (%d available).',
            $asset['asset_name'] ?? ('#' . $asset['id']),
            $availableQty
        );
    }

    $thing = $item['description'] !== ''
        ? $item['description']
        : ($asset['asset_name'] ?: ($asset['description'] ?: 'Asset #' . $asset['id']));

    $preparedItems[] = [
        'asset_id' => (int) $asset['id'],
        'thing' => $thing,
        'inventory_tag' => $asset['inventory_tag'] ?? '',
        'property_no' => $asset['property_no'] ?? '',
        'category' => $asset['category_name'] ?? '',
        'qty' => (string) $item['quantity'],
        'remarks' => $item['remarks'],
    ];
}

if (!empty($validationErrors)) {
    redirectWithFlash('error', 'Unable to submit borrow request. ' . implode(' ', $validationErrors));
}

$itemsJson = json_encode($preparedItems, JSON_UNESCAPED_UNICODE);

try {
    $columnsResult = $conn->query('SHOW COLUMNS FROM borrow_form_submissions');
    $columnNames = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $columnNames[$column['Field']] = true;
    }
    $columnsResult->free();

    $columns = [];
    $placeholders = [];
    $types = '';
    $values = [];

    $submissionNumber = null;
    if (!empty($columnNames['submission_number'])) {
        $submissionNumber = generateSubmissionNumber($conn);
        $columns[] = 'submission_number';
        $placeholders[] = '?';
        $types .= 's';
        $values[] = $submissionNumber;
    }

    if (!empty($columnNames['guest_session_id'])) {
        $columns[] = 'guest_session_id';
        $placeholders[] = '?';
        $types .= 's';
        $values[] = session_id();
    }

    if (!empty($columnNames['guest_email'])) {
        $columns[] = 'guest_email';
        $placeholders[] = '?';
        $types .= 's';
        $values[] = $_SESSION['email'] ?? $_SESSION['guest_email'] ?? null;
    }

    if (!empty($columnNames['guest_id']) && isset($_SESSION['guest_id'])) {
        $columns[] = 'guest_id';
        $placeholders[] = '?';
        $types .= 'i';
        $values[] = (int) $_SESSION['guest_id'];
    }

    if (!empty($columnNames['user_id'])) {
        $columns[] = 'user_id';
        $placeholders[] = '?';
        $types .= 'i';
        $values[] = (int) $_SESSION['user_id'];
    }

    $columns = array_merge($columns, [
        'guest_name',
        'date_borrowed',
        'schedule_return',
        'barangay',
        'contact',
        'releasing_officer',
        'approved_by',
        'items',
    ]);

    $placeholders = array_merge($placeholders, array_fill(0, 8, '?'));
    $types .= 'ssssssss';
    $values[] = $guestName;
    $values[] = $dateBorrowed;
    $values[] = $scheduleReturn;
    $values[] = $barangay;
    $values[] = $contact;
    $values[] = $releasingOfficer;
    $values[] = $approvedBy;
    $values[] = $itemsJson;

    if (!empty($columnNames['status'])) {
        $columns[] = 'status';
        $placeholders[] = '?';
        $types .= 's';
        $values[] = 'approved';
    }

    $sql = 'INSERT INTO borrow_form_submissions (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();

    $insertedId = $conn->insert_id;
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    redirectWithFlash('error', 'Failed to save borrow request. Please try again.');
}

$displayNumber = $submissionNumber ?? sprintf('BFS-%05d', $insertedId);
$_SESSION['success'] = 'Borrow request submitted successfully and marked as approved. Submission #' . $displayNumber . '.';

header('Location: borrowing.php');
exit();
