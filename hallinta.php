<?php
$host = 'localhost';
$db   = 'kurssienhallinta';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Tietokantayhteys epäonnistui: " . $e->getMessage());
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

if (isset($_POST['delete'])) {
    $type = $_POST['type'];

    switch ($type) {
        case 'opiskelijat':
            $idName = 'opiskelijat_ID';
            break;
        case 'opettajat':
            $idName = 'opettaja_ID';
            break;
        case 'tilat':
            $idName = 'tila_ID';
            break;
        case 'kurssit':
            $idName = 'kurssi_ID';
            break;
        default:
            die("Tuntematon taulu");
    }

    $stmt = $pdo->prepare("DELETE FROM $type WHERE $idName = ?");
    $stmt->execute([$_POST[$idName]]);
    header("Location: hallinta.php");
    exit;
}

if (isset($_POST['save'])) {
    $type = $_POST['type'];

    switch ($type) {
        case 'kurssit':
            $idName = 'kurssi_ID';
            $data = [
                'nimi' => $_POST['nimi'],
                'kuvaus' => $_POST['kuvaus'],
                'alkupaiva' => $_POST['alkupaiva'],
                'loppupaiva' => $_POST['loppupaiva'],
                'opettaja_ID' => $_POST['opettaja_ID'],
                'tila_ID' => $_POST['tila_ID']
            ];
            break;
        case 'opettajat':
            $idName = 'opettaja_ID';
            $data = [
                'etunimi' => $_POST['etunimi'],
                'sukunimi' => $_POST['sukunimi'],
                'aine' => $_POST['aine']
            ];
            break;
        case 'tilat':
            $idName = 'tila_ID';
            $data = [
                'nimi' => $_POST['nimi'],
                'kapasiteetti' => $_POST['kapasiteetti']
            ];
            break;
        case 'opiskelijat':
            $idName = 'opiskelijat_ID';
            $data = [
                'etunimi' => $_POST['etunimi'],
                'sukunimi' => $_POST['sukunimi'],
                'syntymapaiva' => $_POST['Syntymapaiva'],
                'vuosikurssi' => $_POST['vuosikurssi']
            ];
            break;
        default:
            die("Tuntematon taulu");
    }

    if (!empty($_POST[$idName])) {
        $fields = [];
        foreach ($data as $k => $v) {
            $fields[] = "$k = :$k";
        }
        $sql = "UPDATE $type SET " . implode(',', $fields) . " WHERE $idName = :id";
        $stmt = $pdo->prepare($sql);
        $params = $data;
        $params['id'] = $_POST[$idName];
        $stmt->execute($params);
    } else {
        $keys = implode(',', array_keys($data));
        $placeholders = ':' . implode(',:', array_keys($data));
        $sql = "INSERT INTO $type ($keys) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
    }

    header("Location: hallinta.php");
    exit;
}

$editData = null;
$idName = '';
if (isset($_GET['action'], $_GET['type'], $_GET['id']) && $_GET['action'] === 'edit') {
    $type = $_GET['type'];
    $id = $_GET['id'];

    switch ($type) {
        case 'opiskelijat':
            $idName = 'opiskelijat_ID';
            break;
        case 'opettajat':
            $idName = 'opettaja_ID';
            break;
        case 'tilat':
            $idName = 'tila_ID';
            break;
        case 'kurssit':
            $idName = 'kurssi_ID';
            break;
        default:
            die("Tuntematon taulu");
    }

    $stmt = $pdo->prepare("SELECT * FROM $type WHERE $idName = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

$kurssit = $pdo->query("
SELECT k.*, t.nimi AS tila_nimi, o.etunimi, o.sukunimi 
FROM kurssit k 
LEFT JOIN tilat t ON k.tila_ID = t.tila_ID
LEFT JOIN opettajat o ON k.opettaja_ID = o.opettaja_ID
")->fetchAll();

$opettajat = $pdo->query("SELECT * FROM opettajat")->fetchAll();
$tilat = $pdo->query("SELECT * FROM tilat")->fetchAll();
$opiskelijat = $pdo->query("SELECT * FROM opiskelijat")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Kurssienhallinta</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
th { background-color: #f0f0f0; }
form { display: inline; }
input, select, button { padding: 5px; margin: 3px 0; }
button { cursor: pointer; }
h1,h2 { color: #333; }
a { text-decoration: none; color: #0066cc; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>

<h1>Kurssienhallinta</h1>

<h2>Opiskelijat</h2>
<table>
<tr><th>ID</th><th>Etunimi</th><th>Sukunimi</th><th>Syntymäpäivä</th><th>Vuosikurssi</th><th>Toiminnot</th></tr>
<?php foreach($opiskelijat as $s): ?>
<tr>
    <td><?=h($s['opiskelijat_ID'])?></td>
    <td><?=h($s['etunimi'])?></td>
    <td><?=h($s['sukunimi'])?></td>
    <td><?=h($s['syntymapaiva'])?></td>
    <td><?=h($s['vuosikurssi'])?></td>
    <td>
        <a href="?action=edit&type=opiskelijat&id=<?=h($s['opiskelijat_ID'])?>">Muokkaa</a> |
        <form method="post">
            <input type="hidden" name="type" value="opiskelijat">
            <input type="hidden" name="opiskelijat_ID" value="<?=h($s['opiskelijat_ID'])?>">
            <button type="submit" name="delete">Poista</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="?action=add&type=opiskelijat">Lisää opiskelija</a>

<h2>Opettajat</h2>
<table>
<tr><th>ID</th><th>Etunimi</th><th>Sukunimi</th><th>Aine</th><th>Toiminnot</th></tr>
<?php foreach($opettajat as $o): ?>
<tr>
    <td><?=h($o['opettaja_ID'])?></td>
    <td><?=h($o['etunimi'])?></td>
    <td><?=h($o['sukunimi'])?></td>
    <td><?=h($o['aine'])?></td>
    <td>
        <a href="?action=edit&type=opettajat&id=<?=h($o['opettaja_ID'])?>">Muokkaa</a> |
        <form method="post">
            <input type="hidden" name="type" value="opettajat">
            <input type="hidden" name="opettaja_ID" value="<?=h($o['opettaja_ID'])?>">
            <button type="submit" name="delete">Poista</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="?action=add&type=opettajat">Lisää opettaja</a>

<h2>Tilat</h2>
<table>
<tr><th>ID</th><th>Nimi</th><th>Kapasiteetti</th><th>Toiminnot</th></tr>
<?php foreach($tilat as $t): ?>
<tr>
    <td><?=h($t['tila_ID'])?></td>
    <td><?=h($t['nimi'])?></td>
    <td><?=h($t['kapasiteetti'])?></td>
    <td>
        <a href="?action=edit&type=tilat&id=<?=h($t['tila_ID'])?>">Muokkaa</a> |
        <form method="post">
            <input type="hidden" name="type" value="tilat">
            <input type="hidden" name="tila_ID" value="<?=h($t['tila_ID'])?>">
            <button type="submit" name="delete">Poista</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="?action=add&type=tilat">Lisää tila</a>

<h2>Kurssit</h2>
<table>
<tr><th>ID</th><th>Nimi</th><th>Kuvaus</th><th>Alku</th><th>Loppu</th><th>Opettaja</th><th>Tila</th><th>Toiminnot</th></tr>
<?php foreach($kurssit as $k): ?>
<tr>
    <td><?=h($k['kurssi_ID'])?></td>
    <td><?=h($k['nimi'])?></td>
    <td><?=h($k['kuvaus'])?></td>
    <td><?=h($k['alkupaiva'])?></td>
    <td><?=h($k['loppupaiva'])?></td>
    <td><?=h($k['etunimi'].' '.$k['sukunimi'])?></td>
    <td><?=h($k['tila_nimi'])?></td>
    <td>
        <a href="?action=edit&type=kurssit&id=<?=h($k['kurssi_ID'])?>">Muokkaa</a> |
        <form method="post">
            <input type="hidden" name="type" value="kurssit">
            <input type="hidden" name="kurssi_ID" value="<?=h($k['kurssi_ID'])?>">
            <button type="submit" name="delete">Poista</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="?action=add&type=kurssit">Lisää kurssi</a>

<?php
if (isset($_GET['action'], $_GET['type']) && in_array($_GET['action'], ['add', 'edit'])):
    $type = $_GET['type'];
    $data = $editData ?? [];
    $isEdit = isset($editData);
?>
<h2><?= $isEdit ? "Muokkaa" : "Lisää" ?> <?=h($type)?></h2>
<form method="post">
    <input type="hidden" name="type" value="<?=h($type)?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="<?=h($idName)?>" value="<?=h($editData[$idName])?>">
    <?php endif; ?>

    <?php if ($type === 'opiskelijat'): ?>
        Etunimi: <input name="etunimi" value="<?=h($data['etunimi'] ?? '')?>"><br>
        Sukunimi: <input name="sukunimi" value="<?=h($data['sukunimi'] ?? '')?>"><br>
        Syntymäpäivä: <input type="date" name="Syntymapaiva" value="<?=h($data['syntymapaiva'] ?? '')?>"><br>
        Vuosikurssi: <input type="number" name="vuosikurssi" value="<?=h($data['vuosikurssi'] ?? '')?>"><br>
    <?php elseif ($type === 'opettajat'): ?>
        Etunimi: <input name="etunimi" value="<?=h($data['etunimi'] ?? '')?>"><br>
        Sukunimi: <input name="sukunimi" value="<?=h($data['sukunimi'] ?? '')?>"><br>
        Aine: <input name="aine" value="<?=h($data['aine'] ?? '')?>"><br>
    <?php elseif ($type === 'tilat'): ?>
        Nimi: <input name="nimi" value="<?=h($data['nimi'] ?? '')?>"><br>
        Kapasiteetti: <input type="number" name="kapasiteetti" value="<?=h($data['kapasiteetti'] ?? '')?>"><br>
    <?php elseif ($type === 'kurssit'): ?>
        Nimi: <input name="nimi" value="<?=h($data['nimi'] ?? '')?>"><br>
        Kuvaus: <input name="kuvaus" value="<?=h($data['kuvaus'] ?? '')?>"><br>
        Alku: <input type="date" name="alkupaiva" value="<?=h($data['alkupaiva'] ?? '')?>"><br>
        Loppu: <input type="date" name="loppupaiva" value="<?=h($data['loppupaiva'] ?? '')?>"><br>
        Opettaja:
        <select name="opettaja_ID">
            <?php foreach($opettajat as $o): ?>
                <option value="<?=h($o['opettaja_ID'])?>" <?= (isset($data['opettaja_ID']) && $data['opettaja_ID']==$o['opettaja_ID'])?'selected':'' ?>><?=h($o['etunimi'].' '.$o['sukunimi'])?></option>
            <?php endforeach; ?>
        </select><br>
        Tila:
        <select name="tila_ID">
            <?php foreach($tilat as $t): ?>
                <option value="<?=h($t['tila_ID'])?>" <?= (isset($data['tila_ID']) && $data['tila_ID']==$t['tila_ID'])?'selected':'' ?>><?=h($t['nimi'])?></option>
            <?php endforeach; ?>
        </select><br>
    <?php endif; ?>

    <button type="submit" name="save"><?= $isEdit ? "Tallenna" : "Lisää" ?></button>
</form>
<?php endif; ?>

</body>
</html>
