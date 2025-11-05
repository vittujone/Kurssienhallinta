<?php
require 'yhteys.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    $stmt = $conn->query("SELECT kurssi_ID, nimi FROM kurssit");
    $kurssit = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="fi">
    <head>
        <meta charset="UTF-8">
        <title>Valitse kurssi</title>
        <link rel="stylesheet" href="kurssi_näkymä.css">
    </head>
    <body>
        <h1>Valitse kurssi</h1>
        <ul>
            <?php foreach ($kurssit as $kurssi): ?>
                <li>
                    <a href="kurssi_näkymä.php?id=<?= $kurssi['kurssi_ID'] ?>">
                        <?= htmlspecialchars($kurssi['nimi']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><a href="index.php">← Takaisin etusivulle</a></p>
    </body>
    </html>
    <?php
    exit;
}

$stmt = $conn->prepare("SELECT k.*, t.nimi AS tila_nimi, o.etunimi AS opettaja_etunimi, o.sukunimi AS opettaja_sukunimi
                        FROM kurssit k
                        LEFT JOIN tilat t ON k.tila_ID = t.tila_ID
                        LEFT JOIN opettajat o ON k.opettaja_ID = o.opettaja_ID
                        WHERE k.kurssi_ID = ?");
$stmt->execute([$id]);
$kurssi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kurssi) {
    echo "Kurssia ei löytynyt.";
    exit;
}

$stmt2 = $conn->prepare("
    SELECT o.etunimi, o.sukunimi
    FROM kurssikirjautumiset kk
    JOIN opiskelijat o ON kk.opiskelija_ID = o.opiskelijat_ID
    WHERE kk.kurssi_ID = ?
");
$stmt2->execute([$id]);
$osallistujat = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Kurssinäkymä: <?= htmlspecialchars($kurssi['nimi']) ?></title>
    <link rel="stylesheet" href="kurssi_näkymä.css">
</head>
<body>
    <h1>Kurssi: <?= htmlspecialchars($kurssi['nimi']) ?></h1>

    <div class="course-info">
        <p><strong>Ajankohta:</strong> <?= htmlspecialchars($kurssi['alkupaiva']) ?> – <?= htmlspecialchars($kurssi['loppupaiva']) ?></p>
        <p><strong>Opettaja:</strong> <?= htmlspecialchars($kurssi['opettaja_etunimi'] . ' ' . $kurssi['opettaja_sukunimi']) ?></p>
        <p><strong>Tila:</strong> <?= htmlspecialchars($kurssi['tila_nimi'] ?? '–') ?></p>
    </div>

    <h2>Osallistujat</h2>
    <?php if (count($osallistujat) > 0): ?>
        <ul>
            <?php foreach ($osallistujat as $osallistuja): ?>
                <li><?= htmlspecialchars($osallistuja['etunimi'] . ' ' . $osallistuja['sukunimi']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Ei osallistujia.</p>
    <?php endif; ?>

    <p><a href="kurssi_näkymä.php">← Takaisin kurssilistaan</a></p>
</body>
</html>
