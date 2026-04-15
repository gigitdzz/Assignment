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

$user_id = (int)$_SESSION['user_id'];
$message = "";
$message_class = "";
$userAppointments = [];
$selectedAppointment = null;
$availableSlots = [];

$appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
$new_date = trim($_POST['new_date'] ?? '');

// Load user's active future appointments
$listSql = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.appointment_type, a.status,
           d.doctor_name, d.specialty, d.clinic_name, d.doctor_id, d.available_days,
           d.shift_start, d.shift_end, d.break_start, d.break_end, d.appointment_duration
    FROM appointments a
    INNER JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.user_id = ?
      AND a.status <> 'Cancelled'
      AND TIMESTAMP(a.appointment_date, a.appointment_time) >= NOW()
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
";
$listStmt = $conn->prepare($listSql);
if ($listStmt) {
    $listStmt->bind_param("i", $user_id);
    $listStmt->execute();
    $listResult = $listStmt->get_result();

    while ($row = $listResult->fetch_assoc()) {
        $userAppointments[] = $row;
    }

    $listStmt->close();
}

if ($appointment_id > 0) {
    foreach ($userAppointments as $appt) {
        if ((int)$appt['appointment_id'] === $appointment_id) {
            $selectedAppointment = $appt;
            break;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['preview_slots'])) {
    if ($appointment_id <= 0 || empty($new_date)) {
        $message = "Please choose an appointment and a new date.";
        $message_class = "message message-error";
    } elseif ($new_date < date('Y-m-d')) {
        $message = "You cannot reschedule to a past date.";
        $message_class = "message message-error";
    } elseif (!$selectedAppointment) {
        $message = "Selected appointment was not found.";
        $message_class = "message message-error";
    } else {
        if (!staffWorksOnDay($selectedAppointment['available_days'], $new_date)) {
            $message = "This clinician does not work on the selected day.";
            $message_class = "message message-error";
        } else {
            $allSlots = generateSlots(
                $selectedAppointment['shift_start'],
                $selectedAppointment['shift_end'],
                $selectedAppointment['break_start'],
                $selectedAppointment['break_end'],
                (int)$selectedAppointment['appointment_duration']
            );

            $bookedSlots = [];
            $bookedStmt = $conn->prepare("
                SELECT appointment_time
                FROM appointments
                WHERE doctor_id = ?
                  AND appointment_date = ?
                  AND status <> 'Cancelled'
                  AND appointment_id <> ?
            ");
            $bookedStmt->bind_param("isi", $selectedAppointment['doctor_id'], $new_date, $appointment_id);
            $bookedStmt->execute();
            $bookedResult = $bookedStmt->get_result();

            while ($row = $bookedResult->fetch_assoc()) {
                $bookedSlots[] = $row['appointment_time'];
            }
            $bookedStmt->close();

            $availableSlots = array_values(array_filter($allSlots, function ($slot) use ($bookedSlots) {
                return !in_array($slot, $bookedSlots, true);
            }));

            if (empty($availableSlots)) {
                $message = "No available 15-minute slots remain for this clinician on the selected date.";
                $message_class = "message message-info";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_reschedule'])) {
    $new_time = trim($_POST['new_time'] ?? '');

    if ($appointment_id <= 0 || empty($new_date) || empty($new_time)) {
        $message = "Please choose an appointment, a date, and a valid time slot.";
        $message_class = "message message-error";
    } elseif (!$selectedAppointment) {
        $message = "Selected appointment was not found.";
        $message_class = "message message-error";
    } elseif (!staffWorksOnDay($selectedAppointment['available_days'], $new_date)) {
        $message = "This clinician does not work on the selected day.";
        $message_class = "message message-error";
    } else {
        $allSlots = generateSlots(
            $selectedAppointment['shift_start'],
            $selectedAppointment['shift_end'],
            $selectedAppointment['break_start'],
            $selectedAppointment['break_end'],
            (int)$selectedAppointment['appointment_duration']
        );

        if (!in_array($new_time, $allSlots, true)) {
            $message = "That time falls outside the clinician's working hours or break rules.";
            $message_class = "message message-error";
        } else {
            $checkStmt = $conn->prepare("
                SELECT appointment_id
                FROM appointments
                WHERE doctor_id = ?
                  AND appointment_date = ?
                  AND appointment_time = ?
                  AND status <> 'Cancelled'
                  AND appointment_id <> ?
                LIMIT 1
            ");
            $checkStmt->bind_param("issi", $selectedAppointment['doctor_id'], $new_date, $new_time, $appointment_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $taken = $checkResult->num_rows > 0;
            $checkStmt->close();

            if ($taken) {
                $message = "That slot is no longer available.";
                $message_class = "message message-error";
            } else {
                $update = $conn->prepare("
                    UPDATE appointments
                    SET appointment_date = ?, appointment_time = ?, status = 'Rescheduled'
                    WHERE appointment_id = ? AND user_id = ?
                ");
                $update->bind_param("ssii", $new_date, $new_time, $appointment_id, $user_id);

                if ($update->execute()) {
                    $message = "Appointment rescheduled successfully.";
                    $message_class = "message message-success";
                } else {
                    $message = "Error rescheduling appointment.";
                    $message_class = "message message-error";
                }
                $update->close();
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
    <title>Reschedule Appointment</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 14px;
        }
        .slot-option {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f9fbfd;
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Reschedule Appointment
</div>

<header class="navbar">
    <div class="logo-section">
        <h1>NHS Booking</h1>
        <p>Reschedule using valid clinician availability only</p>
    </div>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="appointment_history.php">Appointment History</a>
        <a href="book_appointment.php">Book Appointment</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="page-container">
    <div class="page-card booking-card">
        <h2 class="page-title">Reschedule Appointment</h2>
        <p class="page-intro">
            Choose one of your active appointments, select a new date, and the system will show only valid 15-minute slots inside the clinician's working hours and outside break periods.
        </p>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($userAppointments)): ?>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="appointment_id">Select Appointment</label>
                        <select name="appointment_id" id="appointment_id" required>
                            <option value="">Choose an appointment</option>
                            <?php foreach ($userAppointments as $appt): ?>
                                <option value="<?php echo (int)$appt['appointment_id']; ?>" <?php echo ($appointment_id === (int)$appt['appointment_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(
                                        '#' . $appt['appointment_id'] . ' - ' .
                                        $appt['doctor_name'] . ' - ' .
                                        $appt['clinic_name'] . ' - ' .
                                        $appt['appointment_date'] . ' ' . substr($appt['appointment_time'], 0, 5) . ' - ' .
                                        $appt['appointment_type']
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="new_date">New Appointment Date</label>
                        <input type="date" name="new_date" id="new_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($new_date); ?>" required>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" name="preview_slots" class="btn-primary">Show Available Slots</button>
                    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
                </div>
            </form>

            <?php if ($selectedAppointment): ?>
                <div class="message message-info" style="margin-top:20px;">
                    <strong>Clinician:</strong> <?php echo htmlspecialchars($selectedAppointment['doctor_name']); ?><br>
                    <strong>Clinic:</strong> <?php echo htmlspecialchars($selectedAppointment['clinic_name']); ?><br>
                    <strong>Type:</strong> <?php echo htmlspecialchars($selectedAppointment['appointment_type']); ?><br>
                    <strong>Current Time:</strong> <?php echo htmlspecialchars($selectedAppointment['appointment_date'] . ' ' . substr($selectedAppointment['appointment_time'], 0, 5)); ?><br>
                    <strong>Working Hours:</strong> <?php echo date('g:ia', strtotime($selectedAppointment['shift_start'])); ?> - <?php echo date('g:ia', strtotime($selectedAppointment['shift_end'])); ?><br>
                    <strong>Break:</strong> <?php echo date('g:ia', strtotime($selectedAppointment['break_start'])); ?> - <?php echo date('g:ia', strtotime($selectedAppointment['break_end'])); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($availableSlots)): ?>
                <form method="POST" style="margin-top:20px;">
                    <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment_id; ?>">
                    <input type="hidden" name="new_date" value="<?php echo htmlspecialchars($new_date); ?>">

                    <div class="form-group full-width">
                        <label>Select a New Time Slot</label>
                        <div class="slot-grid">
                            <?php foreach ($availableSlots as $slot): ?>
                                <label class="slot-option">
                                    <input type="radio" name="new_time" value="<?php echo htmlspecialchars($slot); ?>" required>
                                    <span><?php echo date('g:ia', strtotime($slot)); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="button-row">
                        <button type="submit" name="confirm_reschedule" class="btn-primary">Confirm Reschedule</button>
                    </div>
                </form>
            <?php endif; ?>

        <?php else: ?>
            <div class="message message-info">
                You do not have any active future appointments available to reschedule.
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>