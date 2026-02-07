<?php
// login page, use config.php to access database/*
/*
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>

<body>
    <form action="/action_page.php">
        <table>
            <tr>
                <th>
                    Dato
                </th>
                <th>
                    Inserimento
                </th>
            </tr>
            <tr>
                <td>
                    Sesso:
                </td>
                <td>
                    <input required type="radio" name="sesso" value="M">
                    <label>Maschio</label><br>
                    <input required type="radio" name="sesso" value="F">
                    <label>Femmina</label><br>
                </td>
            </tr>
            <tr>
                <td>
                    Data di nascita:
                </td>
                <td>
                    <input required type="date" name="dataDiNascita">
                </td>
            </tr>
            <tr>
                <td>
                    Altezza mani sollevamento:
                </td>
                <td>
                    <input required type="number" min="0" max="300" name="altezzaManiSoll">
                </td>
            </tr>
            <tr>
                <td>
                    Altezza mani verticale:
                </td>
                <td>
                    <input required type="number" min="0" max="300" name="altezzaManiVert">
                </td>
            </tr>
            <tr>
                <td>
                    Altezza mani orizzontale:
                </td>
                <td>
                    <input required type="number" min="0" max="100" name="altezzaManiOr">
                </td>
            </tr>
            <tr>
                <td>
                    Dislocazione angolare:
                </td>
                <td>
                    <input required type="number" min="0" max="360" name="DislocazioneAngolare">
                </td>
            </tr>
            <tr>
                <td>
                    Giudizio presa carico:
                </td>
                <td>
                    <input required type="radio" name="GiudizioPresa" value="B">
                    <label>Buono</label><br>
                    <input required type="radio" name="GiudizioPresa" value="S">
                    <label>Scarso</label><br>
                </td>
            </tr>
            <tr>
                <td>
                    Frequenza gesti al minuto:
                </td>
                <td>
                    <input required type="number" min="0.20" max="60" step="0.1" name="FrequenzaGesti">
                </td>
            </tr>
            <tr>
                <td>
                    Frequenza lavoro:
                </td>
                <td>
                    <input required type="radio" name="FrequenzaLavoro" value="1">
                    <label>Continuo < 1 ora </label><br>
                    <input required type="radio" name="FrequenzaLavoro" value="2">
                    <label>Continuo da 1 a 2 ore</label><br>
                    <input required type="radio" name="FrequenzaLavoro" value="3">
                    <label>Continuo da 2 a 8 ore</label><br>
                </td>
            </tr>
            <tr>
                <td>
                    Peso sollevato:
                </td>
                <td>
                    <input required type="number" min="0" max="350" name="PesoSollevato">
                </td>
            </tr>
            <?php
            if($IndiceSollevamento > 0):
            ?>
            <tr>
                <td>
                    Indice Sollevamento:
                </td>
                <td>
                    <?php echo $IndiceSollevamento; ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <input type = "submit">
    </form>
</body>

</html>