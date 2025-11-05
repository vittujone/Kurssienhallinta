<?php
require 'yhteys.php';

$tyyppi = $_GET['tyyppi'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!$id || $tyyppi !== 'opiskelija') {
    $stmt = $conn->query("SELECT opiskelijat_ID, etunimi, sukunimi FROM opiskelijat");
    $opiskelijat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="fi">
    <head>
        <meta charset="UTF-8">
        <title>Valitse opiskelija</title>
        <link rel="stylesheet" href="opiskelija_näkymä.css">
    </head>
    <body>
        <h1>Valitse opiskelija</h1>
        <ul>
            <?php foreach ($opiskelijat as $opiskelija): ?>
                <li>
                    <a href="opiskelija_näkymä.php?tyyppi=opiskelija&id=<?= $opiskelija['opiskelijat_ID'] ?>">
                        <?= htmlspecialchars($opiskelija['etunimi'] . ' ' . $opiskelija['sukunimi']) ?>
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

$stmt = $conn->prepare("SELECT * FROM opiskelijat WHERE opiskelijat_ID = ?");
$stmt->execute([$id]);
$opiskelija = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$opiskelija) {
    echo "Opiskelijaa ei löytynyt.";
    exit;
}

$stmt2 = $conn->prepare("
    SELECT k.nimi, k.alkupaiva, k.loppupaiva, t.nimi AS tila_nimi
    FROM kurssikirjautumiset kk
    JOIN kurssit k ON kk.kurssi_ID = k.kurssi_ID
    LEFT JOIN tilat t ON k.tila_ID = t.tila_ID
    WHERE kk.opiskelija_ID = ?
");
$stmt2->execute([$id]);
$kurssit = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Opiskelijan näkymä</title>
    <link rel="stylesheet" href="opiskelija_näkymä.css">
</head>
<body>
    <h1>Opiskelija: <?= htmlspecialchars($opiskelija['etunimi'] . ' ' . $opiskelija['sukunimi']) ?></h1>

    <div class="student-info">
        <?php if (!empty($opiskelija['vuosikurssi'])): ?>
            <p><strong>Vuosikurssi:</strong> <?= htmlspecialchars($opiskelija['vuosikurssi']) ?></p>
        <?php endif; ?>
        <?php if (!empty($opiskelija['sähköposti'])): ?>
            <p><strong>Sähköposti:</strong> <?= htmlspecialchars($opiskelija['sähköposti']) ?></p>
        <?php endif; ?>
    </div>

    <h2>Kurssit</h2>
    <?php if (count($kurssit) > 0): ?>
        <table>
            <tr>
                <th>Kurssi</th>
                <th>Ajankohta</th>
                <th>Tila</th>
            </tr>
            <?php foreach ($kurssit as $kurssi): ?>
                <tr>
                    <td><?= htmlspecialchars($kurssi['nimi']) ?></td>
                    <td><?= htmlspecialchars($kurssi['alkupaiva']) ?> – <?= htmlspecialchars($kurssi['loppupaiva']) ?></td>
                    <td><?= htmlspecialchars($kurssi['tila_nimi'] ?? '–') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Ei kursseja.</p>
    <?php endif; ?>

    <p><a href="opiskelija_näkymä.php">← Takaisin opiskelijalistaan</a></p>
</body>
</html>
