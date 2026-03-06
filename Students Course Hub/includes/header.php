<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/csss/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <h1><a href="index.php">Student Course Hub</a></h1>
            </div>
            <?php include '../includes/navigation.php'; ?>
        </div>
    </header>
    <main>