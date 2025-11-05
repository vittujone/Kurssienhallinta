<?php
require 'yhteys.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    $stmt = $conn->query("SELECT opettaja_ID, etunimi, sukunimi FROM opettajat");
    $opettajat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="fi">
    <head>
        <meta charset="UTF-8">
        <title>Opettajat</title>
        <link rel="stylesheet" href="opettaja_näkymä.css">
    </head>
    <body>
        <h1>Valitse opettaja</h1>
        <ul>
            <?php foreach ($opettajat as $opettaja): ?>
                <li>
                    <a href="opettaja_näkymä.php?id=<?= $opettaja['opettaja_ID'] ?>">
                        <?= htmlspecialchars($opettaja['etunimi'] . ' ' . $opettaja['sukunimi']) ?>
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

$stmt = $conn->prepare("SELECT * FROM opettajat WHERE opettaja_ID = ?");
$stmt->execute([$id]);
$opettaja = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$opettaja) {
    echo "Opettajaa ei löytynyt.";
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        k.kurssi_ID,
        k.nimi AS kurssi_nimi,
        k.alkupaiva,
        k.loppupaiva,
        t.nimi AS tila_nimi,
        (
            SELECT COUNT(*) 
            FROM kurssikirjautumiset kk 
            WHERE kk.kurssi_ID = k.kurssi_ID
        ) AS osallistujat
    FROM kurssit k
    LEFT JOIN tilat t ON k.tila_ID = t.tila_ID
    WHERE k.opettaja_ID = ?
");
$stmt->execute([$id]);
$kurssit = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Opettaja: <?= htmlspecialchars($opettaja['etunimi'] . ' ' . $opettaja['sukunimi']) ?></title>
    <link rel="stylesheet" href="opettaja_näkymä.css">
</head>
<body>
    <h1>Opettaja: <?= htmlspecialchars($opettaja['etunimi'] . ' ' . $opettaja['sukunimi']) ?></h1>

    <div class="teacher-info">
        <p><strong>Sähköposti:</strong> <?= htmlspecialchars($opettaja['sähköposti'] ?? '–') ?></p>
        <p><strong>Osasto:</strong> <?= htmlspecialchars($opettaja['osasto'] ?? '–') ?></p>
    </div>

    <h2>Kurssit</h2>
    <?php if (count($kurssit) > 0): ?>
        <table>
            <tr>
                <th>Kurssi</th>
                <th>Ajankohta</th>
                <th>Tila</th>
                <th>Osallistujat</th>
            </tr>
            <?php foreach ($kurssit as $kurssi): ?>
                <tr>
                    <td><?= htmlspecialchars($kurssi['kurssi_nimi']) ?></td>
                    <td><?= htmlspecialchars($kurssi['alkupaiva']) ?> – <?= htmlspecialchars($kurssi['loppupaiva']) ?></td>
                    <td><?= htmlspecialchars($kurssi['tila_nimi'] ?? '–') ?></td>
                    <td><?= $kurssi['osallistujat'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Ei kursseja tällä opettajalla.</p>
    <?php endif; ?>

    <p><a href="opettaja_näkymä.php">← Takaisin opettajalistaan</a></p>
</body>
</html>
