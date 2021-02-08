<?php
$_GET['password'] == '12345' or die('access denied');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Excel Upload</title>
</head>
<body>
<?php
if (isset($_SESSION['message']) && $_SESSION['message']) {
    printf('<b>%s</b>', $_SESSION['message']);
    unset($_SESSION['message']);
}
?>
<form action="handler.php" method="post" enctype="multipart/form-data" style="
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    height: 100px;
    width: 300px;
    justify-content: space-around;">
    <input type="file" id="file" name="file" required>
    <button type="submit" name="submit" value="upload">Отправить</button>
</form>
</body>
</html>