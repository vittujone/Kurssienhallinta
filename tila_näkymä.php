<?php
require 'yhteys.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    $stmt = $conn->query("SELECT tila_ID, nimi FROM tilat");
    $tilat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="fi">
    <head>
        <meta charset="UTF-8">
        <title>Tilat</title>
        <link rel="stylesheet" href="tila_näkymä.css">
    </head>
    <body>
        <h1>Valitse tila</h1>
        <ul>
            <?php foreach ($tilat as $tila): ?>
                <li>
                    <a href="tila_näkymä.php?id=<?= $tila['tila_ID'] ?>">
                        <?= htmlspecialchars($tila['nimi']) ?>
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

$stmt = $conn->prepare("SELECT * FROM tilat WHERE tila_ID = ?");
$stmt->execute([$id]);
$tila = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tila) {
    echo "Tila ei löytynyt.";
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        k.kurssi_ID,
        k.nimi AS kurssi_nimi,
        k.alkupaiva,
        k.loppupaiva,
        o.etunimi AS opettaja_etunimi,
        o.sukunimi AS opettaja_sukunimi,
        (
            SELECT COUNT(*) 
            FROM kurssikirjautumiset kk 
            WHERE kk.kurssi_ID = k.kurssi_ID
        ) AS osallistujat
    FROM kurssit k
    LEFT JOIN opettajat o ON k.opettaja_ID = o.opettaja_ID
    WHERE k.tila_ID = ?
");
$stmt->execute([$id]);
$kurssit = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Tilanäkymä: <?= htmlspecialchars($tila['nimi']) ?></title>
    <link rel="stylesheet" href="tila_näkymä.css">
</head>
<body>
    <h1>Tila: <?= htmlspecialchars($tila['nimi']) ?></h1>
    <p><strong>Kapasiteetti:</strong> <?= htmlspecialchars($tila['kapasiteetti']) ?> opiskelijaa</p>

    <h2>Kurssit tässä tilassa</h2>
    <?php if (count($kurssit) > 0): ?>
        <table>
            <tr>
                <th>Kurssi</th>
                <th>Ajankohta</th>
                <th>Opettaja</th>
                <th>Osallistujat</th>
            </tr>
            <?php foreach ($kurssit as $kurssi): ?>
                <tr>
                    <td><?= htmlspecialchars($kurssi['kurssi_nimi']) ?></td>
                    <td><?= htmlspecialchars($kurssi['alkupaiva']) ?> – <?= htmlspecialchars($kurssi['loppupaiva']) ?></td>
                    <td><?= htmlspecialchars($kurssi['opettaja_etunimi'] . ' ' . $kurssi['opettaja_sukunimi']) ?></td>
                    <td>
                        <?= $kurssi['osallistujat'] ?>
                        <?php if ($kurssi['osallistujat'] > $tila['kapasiteetti']): ?>
                            <span class="warning">⚠ Ylikuormitus!</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Ei kursseja tässä tilassa.</p>
    <?php endif; ?>

    <p><a href="tila_näkymä.php">← Takaisin tilalistaan</a></p>
</body>
</html>
