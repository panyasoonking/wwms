<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['status'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$db = new SQLite3('data/Database.db');

// ฟังก์ชันเพิ่มผู้ใช้ใหม่
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $status = $_POST['status'];
    $dateAdded = date('Y-m-d H:i:s');

    $stmt = $db->prepare("INSERT INTO users (username, password, email, status, date_added) VALUES (:username, :password, :email, :status, :date_added)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':date_added', $dateAdded, SQLITE3_TEXT);

    $message = $stmt->execute() ? "เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว!" : "เกิดข้อผิดพลาด ไม่สามารถเพิ่มผู้ใช้ได้";
}

// ฟังก์ชันแก้ไขข้อมูลผู้ใช้
if (isset($_POST['update_user'])) {
    $id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    $query = "UPDATE users SET username = :username, email = :email, status = :status";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $query .= ", password = :password";
    }
    $query .= " WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    if (!empty($_POST['password'])) {
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    }
    $message = $stmt->execute() ? "แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว!" : "เกิดข้อผิดพลาด ไม่สามารถแก้ไขข้อมูลได้";
}

include 'menu.php';
include 'function/auth.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มผู้ใช้ใหม่และแก้ไขข้อมูล</title>
    <link rel="stylesheet" href="css/add_user.css"> <!-- เชื่อมต่อไฟล์ CSS -->
</head>
<body>
    <div class="content">
        <div class="container">
            <h1>เพิ่มผู้ใช้ใหม่</h1>
            <form method="post" action="">
                <label>ชื่อผู้ใช้:</label>
                <input type="text" name="username" required>

                <label>รหัสผ่าน:</label>
                <input type="password" name="password" required>

                <label>อีเมล:</label>
                <input type="email" name="email" required>

                <label>สถานะ:</label>
                <select name="status" required>
                    <option value="admin">admin</option>
                    <option value="user">user</option>
                </select>

                <input type="submit" name="add_user" value="เพิ่มผู้ใช้">
            </form>
            <?php if (isset($message)): ?>
                <p class="message"><?php echo $message; ?></p>
            <?php endif; ?>
        </div>

        <!-- ตารางข้อมูลผู้ใช้ทั้งหมด -->
        <h2>ข้อมูลผู้ใช้ทั้งหมด</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Date Added</th>
                <th>Action</th>
            </tr>
            <?php
            $result = $db->query("SELECT * FROM users");
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['email']}</td><td>{$row['status']}</td><td>{$row['date_added']}</td>";
                echo "<td><a href='#' onclick='openModal({$row['id']}, \"{$row['username']}\", \"{$row['email']}\", \"{$row['status']}\")'>Edit</a></td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Modal for Edit User -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>แก้ไขข้อมูลผู้ใช้</h2>
            <form method="post" action="">
                <input type="hidden" name="user_id" id="edit_user_id">
                <label>ชื่อผู้ใช้:</label>
                <input type="text" name="username" id="edit_username" required>

                <label>รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน):</label>
                <input type="password" name="password">

                <label>อีเมล:</label>
                <input type="email" name="email" id="edit_email" required>

                <label>สถานะ:</label>
                <select name="status" id="edit_status" required>
                    <option value="admin">admin</option>
                    <option value="user">user</option>
                </select>

                <input type="submit" name="update_user" value="บันทึกการแก้ไข">
            </form>
        </div>
    </div>

    <script>
        function openModal(id, username, email, status) {
            document.getElementById("edit_user_id").value = id;
            document.getElementById("edit_username").value = username;
            document.getElementById("edit_email").value = email;
            document.getElementById("edit_status").value = status;
            document.getElementById("editModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }
    </script>
</body>
</html>
