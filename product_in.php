<?php
include 'function/auth.php'; // ตรวจสอบการล็อกอิน

// ตั้งค่าเขตเวลาเป็นเวลาประเทศไทย
date_default_timezone_set('Asia/Bangkok');

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
$db->exec("CREATE TABLE IF NOT EXISTS product_in (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id TEXT UNIQUE,
    customer_name TEXT,
    product_name TEXT,
    outer_size TEXT,
    quantity INTEGER,
    checkin_date TEXT,
    storage_area TEXT,
    shelf_space TEXT,
    user_name TEXT,
    detailed_notes TEXT,
    product_status TEXT
)");

// ดึงข้อมูลจากตาราง add_data สำหรับใช้ในดรอปดาวน์ โดยกรองข้อมูลที่ว่างเปล่า
$add_data_results = $db->query("SELECT DISTINCT customer_name, product_name, outer_size, storage_area, shelf_space FROM add_data");

// เก็บค่าต่าง ๆ ไว้ใน array
$customer_names = $product_names = $outer_sizes = $storage_areas = $shelf_spaces = [];
while ($row = $add_data_results->fetchArray(SQLITE3_ASSOC)) {
    if (!empty($row['customer_name'])) $customer_names[] = $row['customer_name'];
    if (!empty($row['product_name'])) $product_names[] = $row['product_name'];
    if (!empty($row['outer_size'])) $outer_sizes[] = $row['outer_size'];
    if (!empty($row['storage_area'])) $storage_areas[] = $row['storage_area'];
    if (!empty($row['shelf_space'])) $shelf_spaces[] = $row['shelf_space'];
}

// ตรวจสอบ Product ID ซ้ำ
$message = ""; // ข้อความแจ้งเตือน
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $outer_size = $_POST['outer_size'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $checkin_date = date('Y-m-d H:i'); // ใช้วันที่และเวลาปัจจุบันในรูปแบบ YYYY-MM-DD HH:MM
    $storage_area = $_POST['storage_area'] ?? '';
    $shelf_space = $_POST['shelf_space'] ?? '';
    $user_name = $_SESSION['username']; // บันทึก user ที่ล็อกอินอยู่
    $detailed_notes = $_POST['detailed_notes'] ?? '';
    $product_status = 'รับเข้า'; // กำหนดค่าเริ่มต้นเป็น "รับเข้า"

    // ตรวจสอบว่ามี Product ID นี้ในฐานข้อมูลแล้วหรือยัง
    $check_stmt = $db->prepare("SELECT COUNT(*) as count FROM product_in WHERE product_id = :product_id");
    $check_stmt->bindValue(':product_id', $product_id);
    $result = $check_stmt->execute();
    $row = $result->fetchArray();

    if ($row['count'] > 0) {
        $message = "Product ID already exists!"; // แจ้งเตือนหาก Product ID ซ้ำ
    } else {
        // บันทึกข้อมูลลงในฐานข้อมูล
        $stmt = $db->prepare("INSERT INTO product_in (product_id, customer_name, product_name, outer_size, quantity, checkin_date, storage_area, shelf_space, user_name, detailed_notes, product_status) VALUES (:product_id, :customer_name, :product_name, :outer_size, :quantity, :checkin_date, :storage_area, :shelf_space, :user_name, :detailed_notes, :product_status)");
        $stmt->bindValue(':product_id', $product_id);
        $stmt->bindValue(':customer_name', $customer_name);
        $stmt->bindValue(':product_name', $product_name);
        $stmt->bindValue(':outer_size', $outer_size);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':checkin_date', $checkin_date); // บันทึกวันที่ในรูปแบบ YYYY-MM-DD HH:MM
        $stmt->bindValue(':storage_area', $storage_area);
        $stmt->bindValue(':shelf_space', $shelf_space);
        $stmt->bindValue(':user_name', $user_name);
        $stmt->bindValue(':detailed_notes', $detailed_notes);
        $stmt->bindValue(':product_status', $product_status);
        $stmt->execute();

        // รีไดเรกต์ไปยังหน้าเดิมเพื่อป้องกันการส่งข้อมูลซ้ำเมื่อกดรีเฟรช
        header("Location: product_in.php");
        exit();
    }
}

// ดึงข้อมูลรายการรับเข้าล่าสุด 10 รายการ
$recent_entries = $db->query("SELECT * FROM product_in ORDER BY id DESC LIMIT 10");

include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product In</title>
    <link rel="stylesheet" href="css/product_in.css?v=1.0"> <!-- เชื่อมต่อไฟล์ CSS -->
</head>
<body>
    <div class="container">
        <h2>Product In</h2>

        <!-- แสดงข้อความแจ้งเตือนหาก Product ID ซ้ำ -->
        <?php if ($message): ?>
            <p style="color: red; text-align: center;"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <!-- ส่วนฟอร์มกรอกข้อมูล -->
        <form method="POST" action="" class="form-container">
            <div class="form-group">
                <label>Product ID:</label>
                <input type="text" name="product_id" required>
            </div>

            <div class="form-group">
                <label>Customer Name:</label>
                <select name="customer_name" required>
                    <?php foreach (array_unique($customer_names) as $name): ?>
                        <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Product Name:</label>
                <select name="product_name" required>
                    <?php foreach (array_unique($product_names) as $name): ?>
                        <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Outer Size (mm):</label>
                <select name="outer_size" required>
                    <?php foreach (array_unique($outer_sizes) as $size): ?>
                        <option value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" required>
            </div>

            <div class="form-group">
                <label>Storage Area:</label>
                <select name="storage_area" required>
                    <?php foreach (array_unique($storage_areas) as $area): ?>
                        <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Shelf Space:</label>
                <select name="shelf_space" required>
                    <?php foreach (array_unique($shelf_spaces) as $space): ?>
                        <option value="<?php echo htmlspecialchars($space); ?>"><?php echo htmlspecialchars($space); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Detailed Notes:</label>
                <textarea name="detailed_notes"></textarea>
            </div>

            <button type="submit">Save</button>
        </form>

               <!-- ตารางแสดงรายการรับเข้าล่าสุด -->
            <h3>Recent Product In Entries</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Customer Name</th>
                        <th>Product Name</th>
                        <th>Outer Size (mm)</th>
                        <th>Quantity</th>
                        <th>Check-in Date</th>
                        <th>Storage Area</th>
                        <th>Shelf Space</th>
                        <th>User Name</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_entries->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr id="row-<?php echo $row['product_id']; ?>">
                            <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['outer_size']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['checkin_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['storage_area']); ?></td>
                            <td><?php echo htmlspecialchars($row['shelf_space']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['detailed_notes']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_status']); ?></td>
                            <td>
                                <button class="action-button print-button" onclick="printRow('row-<?php echo $row['product_id']; ?>')"><i class="fas fa-print"></i> Print</button>
                                <button class="action-button pdf-button" onclick="downloadPDF('row-<?php echo $row['product_id']; ?>')"><i class="fas fa-file-pdf"></i> PDF</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>
    <script src="/js/product_in.js"></script>
    <script src="js/product_out.js"></script>
</body>
</html>
