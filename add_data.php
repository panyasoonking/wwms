<?php
// ตั้งค่าเส้นทางไฟล์ฐานข้อมูล
$database_path = 'data/Database.db';

// เชื่อมต่อฐานข้อมูลและจัดการข้อผิดพลาด
try {
    $db = new SQLite3($database_path);
} catch (Exception $e) {
    echo "Error: Unable to open database - " . $e->getMessage();
    exit();
}

// สร้างตารางหากยังไม่มี
$db->exec("CREATE TABLE IF NOT EXISTS add_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_name TEXT,
    customer_name TEXT,
    outer_size TEXT,
    storage_area TEXT,
    shelf_space TEXT
)");

// ข้อความแจ้งเตือนเมื่อมีข้อมูลซ้ำ
$message = "";

// บันทึกข้อมูลเมื่อฟอร์มถูกส่ง
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $outer_size = $_POST['outer_size'] ?? '';
    $storage_area = $_POST['storage_area'] ?? '';
    $shelf_space = $_POST['shelf_space'] ?? '';

    // ตรวจสอบว่ามีข้อมูลซ้ำหรือไม่
    $check_stmt = $db->prepare("SELECT COUNT(*) as count FROM add_data WHERE product_name = :product_name AND customer_name = :customer_name");
    $check_stmt->bindValue(':product_name', $product_name);
    $check_stmt->bindValue(':customer_name', $customer_name);
    $result = $check_stmt->execute();
    $row = $result->fetchArray();

    if ($row['count'] > 0) {
        $message = "Data already exists!"; // ข้อมูลซ้ำ
    } else {
        // บันทึกข้อมูลลงในฐานข้อมูล
        $stmt = $db->prepare("INSERT INTO add_data (product_name, customer_name, outer_size, storage_area, shelf_space) VALUES (:product_name, :customer_name, :outer_size, :storage_area, :shelf_space)");
        $stmt->bindValue(':product_name', $product_name);
        $stmt->bindValue(':customer_name', $customer_name);
        $stmt->bindValue(':outer_size', $outer_size);
        $stmt->bindValue(':storage_area', $storage_area);
        $stmt->bindValue(':shelf_space', $shelf_space);
        $stmt->execute();

        // รีไดเรกต์ไปยังหน้าเดิมเพื่อป้องกันการส่งข้อมูลซ้ำเมื่อกดรีเฟรช
        header("Location: add_data.php");
        exit();
    }
}

// ดึงข้อมูลทั้งหมดเพื่อแสดงในตาราง
$results = $db->query("SELECT * FROM add_data");

// เพิ่มเมนู
include 'menu.php';
include 'function/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Data</title>
    <link rel="stylesheet" href="css/add_data.css"> <!-- เชื่อมต่อไฟล์ CSS -->
</head>
<body>
    <div class="container">
        <h2>Add Data</h2>
        
        <!-- แสดงข้อความแจ้งเตือนเมื่อข้อมูลซ้ำ -->
        <?php if ($message): ?>
            <p style="color: red;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Product Name:</label>
            <input type="text" name="product_name">

            <label>Customer Name:</label>
            <input type="text" name="customer_name">

            <label>Outer Size (mm):</label>
            <input type="text" name="outer_size">

            <label>Storage Area:</label>
            <input type="text" name="storage_area">

            <label>Shelf Space:</label>
            <input type="text" name="shelf_space">

            <button type="submit">Save</button>
        </form>

        <h2>Stored Data</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Customer Name</th>
                    <th>Outer Size (mm)</th>
                    <th>Storage Area</th>
                    <th>Shelf Space</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $results->fetchArray()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['outer_size']); ?></td>
                        <td><?php echo htmlspecialchars($row['storage_area']); ?></td>
                        <td><?php echo htmlspecialchars($row['shelf_space']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
