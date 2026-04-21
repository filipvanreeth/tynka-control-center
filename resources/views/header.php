<!DOCTYPE html>
<html lang="<?= htmlspecialchars($htmlLang ?? 'en', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tynka's Control Center</title>
    <link href="<?= htmlspecialchars($this->appConfig->appUrl, ENT_QUOTES, 'UTF-8'); ?>/assets/css/style.css?v=<?= $this->appConfig->appVersion ?>" rel="stylesheet">
</head>
<body>
