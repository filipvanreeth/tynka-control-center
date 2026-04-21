<!DOCTYPE html>
<html lang="<?= htmlspecialchars($htmlLang ?? 'en', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tynka's Control Center</title>
    <?php
    global $vite;
    echo $vite->assets('resources/js/app.js');
    ?>
</head>
<body>
