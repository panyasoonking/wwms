<?php
session_start();
$db = new SQLite3('data/Database.db');

// ตรวจสอบการล็อกอินเมื่อกดปุ่มเข้าสู่ระบบ
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // ตรวจสอบรหัสผ่านที่เข้ารหัส
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['status'] = $user['status'];

        // นำทางไปยังหน้า dashboard.php หลังจากล็อกอินสำเร็จ
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; color: #333; }
        h1 { text-align: center; }
        form { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label, input { display: block; width: 100%; margin-top: 10px; }
        input[type="text"], input[type="password"] {
            padding: 8px; border: 1px solid #ddd; border-radius: 4px;
        }
        input[type="submit"] {
            width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; margin-top: 15px;
            cursor: pointer;
        }
        input[type="submit"]:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>เข้าสู่ระบบ</h1>
    <form method="post" action="">
        <label>ชื่อผู้ใช้:</label>
        <input type="text" name="username" required>

        <label>รหัสผ่าน:</label>
        <input type="password" name="password" required>

        <input type="submit" name="login" value="เข้าสู่ระบบ">
    </form>
    <?php if (isset($error)): ?>
        <p style="text-align:center;color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
</body>
</html>
