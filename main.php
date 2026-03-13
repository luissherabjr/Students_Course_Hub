<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

$programmes = getProgrammes();
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['errors'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Interest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="form-box">
        <h2>Register Your Interest</h2>

        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <ul class="error">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="process_registration.php" method="POST">
            <label for="student_name">Student Name</label>
            <input type="text" id="student_name" name="student_name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="programme_id">Select Programme</label>
            <select id="programme_id" name="programme_id" required>
                <option value="">-- Select Programme --</option>
                <?php foreach ($programmes as $programme): ?>
                    <option value="<?php echo $programme['ProgrammeID']; ?>">
                        <?php echo htmlspecialchars($programme['ProgrammeName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Register</button>
        </form>
    </div>
</div>

</body>
</html>