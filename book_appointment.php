<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Europe/London');

function staffWorksOnDay(string $availableDays, string $date): bool
{
    $dayName = date('l', strtotime($date));
    $days = array_map('trim', explode(',', $availableDays));
    return in_array($dayName, $days, true);
}

function generateSlots(string $shiftStart, string $shiftEnd, ?string $breakStart, ?string $breakEnd, int $durationMinutes = 15): array
{
    $slots = [];

    $current = strtotime($shiftStart);
    $end = strtotime($shiftEnd);

    $breakStartTs = $breakStart ? strtotime($breakStart) : null;
    $breakEndTs = $breakEnd ? strtotime($breakEnd) : null;

    while (($current + ($durationMinutes * 60)) <= $end) {
        $slotStartTs = $current;
        $slotEndTs = $current + ($durationMinutes * 60);

        $overlapsBreak = false;
        if ($breakStartTs !== null && $breakEndTs !== null) {
            $overlapsBreak = ($slotStartTs < $breakEndTs && $slotEndTs > $breakStartTs);
        }

        if (!$overlapsBreak) {
            $slots[] = date('H:i:s', $slotStartTs);
        }

        $current += ($durationMinutes * 60);
    }

    return $slots;
}

function getSelectedIssues(mysqli $conn, array $issueIds): array
{
    $issueIds = array_values(array_unique(array_map('intval', $issueIds)));
    $issueIds = array_filter($issueIds, fn($id) => $id > 0);

    if (empty($issueIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($issueIds), '?'));
    $types = str_repeat('i', count($issueIds));

    $sql = "SELECT issue_id, issue_name, symptom_group, recommended_staff_type, appointment_type, priority_level
            FROM medical_issues
            WHERE issue_id IN ($placeholders) AND is_active = 1
            ORDER BY priority_level DESC, issue_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$issueIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $issues = [];
    while ($row = $result->fetch_assoc()) {
        $issues[] = $row;
    }

    $stmt->close();
    return $issues;
}

function determineRecommendation(array $selectedIssues): array
{
    $needsGp = false;
    $needsNurse = false;
    $appointmentType = 'Routine Assessment';
    $highestPriority = 0;

    foreach ($selectedIssues as $issue) {
        if ($issue['recommended_staff_type'] === 'GP') {
            $needsGp = true;
        } elseif ($issue['recommended_staff_type'] === 'Nurse') {
            $needsNurse = true;
        } else {
            $needsGp = true;
            $needsNurse = true;
        }

        if ((int)$issue['priority_level'] >= $highestPriority) {
            $highestPriority = (int)$issue['priority_level'];
            $appointmentType = $issue['appointment_type'];
        }
    }

    if ($needsGp && !$needsNurse) {
        $staffTypes = ['GP', 'Specialist'];
        $label = 'GP / Specialist recommended';
    } elseif ($needsNurse && !$needsGp) {
        $staffTypes = ['Nurse'];
        $label = 'Nurse recommended';
    } else {
        $staffTypes = ['GP', 'Nurse', 'Specialist'];
        $label = 'Multiple suitable staff types';
    }

    return [
        'staff_types' => $staffTypes,
        'label' => $label,
        'appointment_type' => $appointmentType
    ];
}

$message = "";
$message_class = "";
$availability = [];
$clinics = [];
$medicalIssues = [];

$selected_date = $_POST['appointment_date'] ?? $_GET['appointment_date'] ?? date('Y-m-d');
$selected_clinic = trim($_POST['clinic_name'] ?? $_GET['clinic_name'] ?? '');
$selected_staff_type = trim($_POST['staff_type'] ?? $_GET['staff_type'] ?? '');
$symptom_search = trim($_POST['symptom_search'] ?? '');
$selected_issue_ids = isset($_POST['issues']) ? array_map('intval', $_POST['issues']) : [];
$selected_doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : (isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0);

if ($selected_date < date('Y-m-d')) {
    $selected_date = date('Y-m-d');
}

$clinicSql = "SELECT DISTINCT clinic_name FROM doctors WHERE is_active = 1 ORDER BY clinic_name ASC";
$clinicResult = $conn->query($clinicSql);
if ($clinicResult) {
    while ($row = $clinicResult->fetch_assoc()) {
        $clinics[] = $row['clinic_name'];
    }
}

$issueSql = "SELECT issue_id, issue_name, symptom_group, recommended_staff_type, appointment_type
             FROM medical_issues
             WHERE is_active = 1";
$params = [];
$types = "";

if ($symptom_search !== '') {
    $issueSql .= " AND (issue_name LIKE ? OR symptom_group LIKE ? OR appointment_type LIKE ?)";
    $searchLike = "%" . $symptom_search . "%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= "sss";
}

$issueSql .= " ORDER BY symptom_group ASC, issue_name ASC";
$issueStmt = $conn->prepare($issueSql);
if ($issueStmt) {
    if (!empty($params)) {
        $issueStmt->bind_param($types, ...$params);
    }
    $issueStmt->execute();
    $issueResult = $issueStmt->get_result();
    while ($row = $issueResult->fetch_assoc()) {
        $medicalIssues[] = $row;
    }
    $issueStmt->close();
}

$action = $_POST['action'] ?? '';
$recommendation = [
    'staff_types' => ['GP', 'Nurse', 'Specialist'],
    'label' => 'Select symptoms to receive a recommended appointment type',
    'appointment_type' => 'Routine Assessment'
];
$selectedIssues = [];

if (!empty($selected_issue_ids)) {
    $selectedIssues = getSelectedIssues($conn, $selected_issue_ids);
    if (!empty($selectedIssues)) {
        $recommendation = determineRecommendation($selectedIssues);
    }
}

function buildAvailability(
    mysqli $conn,
    string $selectedDate,
    string $selectedClinic,
    string $selectedStaffType,
    int $selectedDoctorId,
    array $recommendedTypes
): array {
    $sql = "SELECT * FROM doctors WHERE is_active = 1";
    $params = [];
    $types = "";

    if ($selectedClinic !== '') {
        $sql .= " AND clinic_name = ?";
        $params[] = $selectedClinic;
        $types .= "s";
    }

    if ($selectedStaffType !== '') {
        $sql .= " AND staff_type = ?";
        $params[] = $selectedStaffType;
        $types .= "s";
    }

    if ($selectedDoctorId > 0) {
        $sql .= " AND doctor_id = ?";
        $params[] = $selectedDoctorId;
        $types .= "i";
    }

    $sql .= " ORDER BY clinic_name ASC, staff_type ASC, doctor_name ASC";

    $stmt = $conn->prepare($sql);
    $staffRows = [];

    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $staffRows[] = $row;
        }

        $stmt->close();
    }

    $booked = [];
    $bookedStmt = $conn->prepare("
        SELECT doctor_id, appointment_time
        FROM appointments
        WHERE appointment_date = ?
          AND status <> 'Cancelled'
    ");
    $bookedStmt->bind_param("s", $selectedDate);
    $bookedStmt->execute();
    $bookedResult = $bookedStmt->get_result();

    while ($row = $bookedResult->fetch_assoc()) {
        $booked[(int)$row['doctor_id']][] = $row['appointment_time'];
    }
    $bookedStmt->close();

    $availability = [];
    foreach ($staffRows as $staff) {
        if (!in_array($staff['staff_type'], $recommendedTypes, true)) {
            continue;
        }

        if (!staffWorksOnDay($staff['available_days'], $selectedDate)) {
            continue;
        }

        $allSlots = generateSlots(
            $staff['shift_start'],
            $staff['shift_end'],
            $staff['break_start'],
            $staff['break_end'],
            (int)$staff['appointment_duration']
        );

        $bookedSlots = $booked[(int)$staff['doctor_id']] ?? [];
        $freeSlots = array_values(array_filter($allSlots, function ($slot) use ($bookedSlots) {
            return !in_array($slot, $bookedSlots, true);
        }));

        $availability[] = [
            'staff' => $staff,
            'slots' => $freeSlots
        ];
    }

    return $availability;
}

if ($action === 'search') {
    if (empty($selected_issue_ids)) {
        $message = "Please choose one or more symptoms before searching for appointments.";
        $message_class = "message message-error";
    } else {
        $availability = buildAvailability(
            $conn,
            $selected_date,
            $selected_clinic,
            $selected_staff_type,
            $selected_doctor_id,
            $recommendation['staff_types']
        );

        if (empty($availability)) {
            $message = "No available appointments matched your symptoms and filters on that date. Try another day or clinic.";
            $message_class = "message message-info";
        } else {
            $message = "Available appointments were found based on your selected symptoms and the recommended appointment type.";
            $message_class = "message message-success";
        }
    }
}

if ($action === 'book') {
    $appointment_time = $_POST['appointment_time'] ?? '';
    $doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;

    if (empty($selected_issue_ids)) {
        $message = "Please choose one or more symptoms before booking.";
        $message_class = "message message-error";
    } elseif ($doctor_id <= 0 || $appointment_time === '') {
        $message = "Please select a valid appointment slot.";
        $message_class = "message message-error";
    } else {
        $availability = buildAvailability(
            $conn,
            $selected_date,
            $selected_clinic,
            $selected_staff_type,
            $doctor_id,
            $recommendation['staff_types']
        );

        $validSlot = false;
        $selectedStaffName = '';

        foreach ($availability as $item) {
            if ((int)$item['staff']['doctor_id'] === $doctor_id) {
                $selectedStaffName = $item['staff']['doctor_name'];
                if (in_array($appointment_time, $item['slots'], true)) {
                    $validSlot = true;
                }
                break;
            }
        }

        if (!$validSlot) {
            $message = "That slot is no longer available or falls outside the clinician's working schedule.";
            $message_class = "message message-error";
        } else {
            $checkStmt = $conn->prepare("
                SELECT appointment_id
                FROM appointments
                WHERE doctor_id = ?
                  AND appointment_date = ?
                  AND appointment_time = ?
                  AND status <> 'Cancelled'
                LIMIT 1
            ");
            $checkStmt->bind_param("iss", $doctor_id, $selected_date, $appointment_time);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $alreadyBooked = $checkResult->num_rows > 0;
            $checkStmt->close();

            if ($alreadyBooked) {
                $message = "That slot has just been taken. Please choose another available time.";
                $message_class = "message message-error";
            } else {
                $user_id = (int)$_SESSION['user_id'];
                $appointmentType = $recommendation['appointment_type'];

                $conn->begin_transaction();

                try {
                    $insertStmt = $conn->prepare("
                        INSERT INTO appointments
                        (user_id, doctor_id, appointment_date, appointment_time, duration_minutes, appointment_type, status)
                        VALUES (?, ?, ?, ?, 15, ?, 'Booked')
                    ");
                    $insertStmt->bind_param("iisss", $user_id, $doctor_id, $selected_date, $appointment_time, $appointmentType);
                    $insertStmt->execute();

                    $appointmentId = $insertStmt->insert_id;
                    $insertStmt->close();

                    $symptomStmt = $conn->prepare("
                        INSERT INTO appointment_symptoms (appointment_id, issue_id)
                        VALUES (?, ?)
                    ");

                    foreach ($selected_issue_ids as $issueId) {
                        $issueId = (int)$issueId;
                        if ($issueId > 0) {
                            $symptomStmt->bind_param("ii", $appointmentId, $issueId);
                            $symptomStmt->execute();
                        }
                    }

                    $symptomStmt->close();
                    $conn->commit();

                    $message = "Appointment booked successfully with " . $selectedStaffName . " on " . htmlspecialchars($selected_date) . " at " . date('g:ia', strtotime($appointment_time)) . ".";
                    $message_class = "message message-success";

                    $availability = buildAvailability(
                        $conn,
                        $selected_date,
                        $selected_clinic,
                        $selected_staff_type,
                        $selected_doctor_id,
                        $recommendation['staff_types']
                    );
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Error booking appointment. Please try again.";
                    $message_class = "message message-error";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .filter-panel,
        .symptom-panel,
        .availability-panel {
            background: #f9fbfd;
            border: 1px solid #dbe4ee;
            border-radius: 12px;
            padding: 22px;
        }

        .symptom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .symptom-option {
            background: #fff;
            border: 1px solid #dbe4ee;
            border-radius: 10px;
            padding: 12px;
        }

        .symptom-option label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 0;
            font-weight: 600;
            cursor: pointer;
        }

        .symptom-option small {
            display: block;
            margin-top: 6px;
            color: #6b7280;
            font-weight: 400;
        }

        .recommendation-box {
            background: #eef5ff;
            border-left: 4px solid #005eb8;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .availability-grid {
            display: grid;
            gap: 18px;
        }

        .staff-card {
            background: #fff;
            border: 1px solid #dbe4ee;
            border-radius: 12px;
            padding: 18px;
        }

        .staff-header {
            margin-bottom: 12px;
        }

        .staff-header h3 {
            color: #005eb8;
            margin-bottom: 6px;
        }

        .staff-meta {
            display: grid;
            gap: 6px;
            font-size: 14px;
            color: #374151;
            margin-bottom: 14px;
        }

        .slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .slot-button {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #005eb8;
            background: #fff;
            color: #005eb8;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        .slot-button:hover {
            background: #eef5ff;
        }

        .empty-note {
            color: #6b7280;
            font-size: 14px;
        }

        .pill {
            display: inline-block;
            background: #e8f1fb;
            color: #005eb8;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            margin-right: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Symptom-Based Booking
</div>

<header class="navbar">
    <div class="logo-section">
        <h1>NHS Booking</h1>
        <p>Book only valid 15-minute appointments inside staff working hours</p>
    </div>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="doctors.php">Clinics</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="appointment_history.php">Appointment History</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="page-container">
    <div class="page-card">
        <h2 class="page-title">Book an Appointment</h2>
        <p class="page-intro">
            Complete the medical form below. The system will recommend the correct staff type, show clinics with availability, and offer only valid 15-minute slots during working hours and outside staff breaks.
        </p>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_class; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="booking-layout">
            <form method="POST">
                <input type="hidden" name="action" value="search">

                <div class="filter-panel">
                    <h3 style="color:#005eb8; margin-bottom: 12px;">Appointment Filters</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="appointment_date">Appointment Date</label>
                            <input type="date" name="appointment_date" id="appointment_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($selected_date); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="clinic_name">Preferred Clinic</label>
                            <select name="clinic_name" id="clinic_name">
                                <option value="">Any clinic</option>
                                <?php foreach ($clinics as $clinic): ?>
                                    <option value="<?php echo htmlspecialchars($clinic); ?>" <?php echo ($selected_clinic === $clinic) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($clinic); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="staff_type">Preferred Staff Type</label>
                            <select name="staff_type" id="staff_type">
                                <option value="">System decides</option>
                                <option value="GP" <?php echo ($selected_staff_type === 'GP') ? 'selected' : ''; ?>>GP</option>
                                <option value="Nurse" <?php echo ($selected_staff_type === 'Nurse') ? 'selected' : ''; ?>>Nurse</option>
                                <option value="Specialist" <?php echo ($selected_staff_type === 'Specialist') ? 'selected' : ''; ?>>Specialist</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="symptom_search">Search Symptoms</label>
                            <input type="text" name="symptom_search" id="symptom_search" value="<?php echo htmlspecialchars($symptom_search); ?>" placeholder="Search issues like fever, wound, chest pain">
                        </div>
                    </div>
                </div>

                <div class="symptom-panel">
                    <h3 style="color:#005eb8; margin-bottom: 12px;">Medical Form</h3>
                    <p class="page-intro" style="margin-bottom: 10px;">
                        Choose one or more symptoms that best describe your current issue.
                    </p>

                    <div class="symptom-grid">
                        <?php foreach ($medicalIssues as $issue): ?>
                            <div class="symptom-option">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="issues[]"
                                        value="<?php echo (int)$issue['issue_id']; ?>"
                                        <?php echo in_array((int)$issue['issue_id'], $selected_issue_ids, true) ? 'checked' : ''; ?>
                                    >
                                    <span>
                                        <?php echo htmlspecialchars($issue['issue_name']); ?>
                                        <small>
                                            <?php echo htmlspecialchars($issue['symptom_group']); ?> ·
                                            <?php echo htmlspecialchars($issue['recommended_staff_type']); ?> ·
                                            <?php echo htmlspecialchars($issue['appointment_type']); ?>
                                        </small>
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="button-row">
                        <button type="submit" class="btn-primary">Find Available Appointments</button>
                        <a href="book_appointment.php" class="btn-secondary">Reset Booking Form</a>
                    </div>
                </div>
            </form>

            <div class="availability-panel">
                <h3 style="color:#005eb8; margin-bottom: 12px;">Appointment Recommendation</h3>

                <div class="recommendation-box">
                    <strong>Recommended pathway:</strong> <?php echo htmlspecialchars($recommendation['label']); ?><br>
                    <strong>Suggested appointment type:</strong> <?php echo htmlspecialchars($recommendation['appointment_type']); ?><br>
                    <strong>Slot length:</strong> 15 minutes<br>
                    <strong>Booking rules:</strong> Only between 8:00am and 8:00pm, excluding staff breaks.
                </div>

                <?php if (!empty($selectedIssues)): ?>
                    <div style="margin-bottom: 16px;">
                        <?php foreach ($selectedIssues as $issue): ?>
                            <span class="pill"><?php echo htmlspecialchars($issue['issue_name']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h3 style="color:#005eb8; margin-bottom: 12px;">Clinic Availability</h3>

                <?php if (!empty($availability)): ?>
                    <div class="availability-grid">
                        <?php foreach ($availability as $item): ?>
                            <div class="staff-card">
                                <div class="staff-header">
                                    <h3><?php echo htmlspecialchars($item['staff']['doctor_name']); ?></h3>
                                    <div class="doctor-badge"><?php echo htmlspecialchars($item['staff']['staff_type'] . ' - ' . $item['staff']['specialty']); ?></div>
                                </div>

                                <div class="staff-meta">
                                    <div><strong>Clinic:</strong> <?php echo htmlspecialchars($item['staff']['clinic_name']); ?></div>
                                    <div><strong>Room:</strong> <?php echo htmlspecialchars($item['staff']['room_number']); ?></div>
                                    <div><strong>Working Hours:</strong> <?php echo date('g:ia', strtotime($item['staff']['shift_start'])); ?> - <?php echo date('g:ia', strtotime($item['staff']['shift_end'])); ?></div>
                                    <div><strong>Break:</strong> <?php echo date('g:ia', strtotime($item['staff']['break_start'])); ?> - <?php echo date('g:ia', strtotime($item['staff']['break_end'])); ?></div>
                                    <div><strong>Available Times on <?php echo htmlspecialchars($selected_date); ?>:</strong></div>
                                </div>

                                <?php if (!empty($item['slots'])): ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="book">
                                        <input type="hidden" name="doctor_id" value="<?php echo (int)$item['staff']['doctor_id']; ?>">
                                        <input type="hidden" name="appointment_date" value="<?php echo htmlspecialchars($selected_date); ?>">
                                        <input type="hidden" name="clinic_name" value="<?php echo htmlspecialchars($selected_clinic); ?>">
                                        <input type="hidden" name="staff_type" value="<?php echo htmlspecialchars($selected_staff_type); ?>">
                                        <input type="hidden" name="symptom_search" value="<?php echo htmlspecialchars($symptom_search); ?>">

                                        <?php foreach ($selected_issue_ids as $issueId): ?>
                                            <input type="hidden" name="issues[]" value="<?php echo (int)$issueId; ?>">
                                        <?php endforeach; ?>

                                        <div class="slot-grid">
                                            <?php foreach ($item['slots'] as $slot): ?>
                                                <button type="submit" name="appointment_time" value="<?php echo htmlspecialchars($slot); ?>" class="slot-button">
                                                    <?php echo date('g:ia', strtotime($slot)); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="empty-note">No free slots remain for this clinician on the selected date.</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="message message-info">
                        Choose symptoms and search for appointments to see clinic availability.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>