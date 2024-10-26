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

// สร้างตาราง product_out หากยังไม่มี
$db->exec("CREATE TABLE IF NOT EXISTS product_out (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id TEXT,
    customer_name TEXT,
    product_name TEXT,
    outer_size TEXT,
    quantity INTEGER,
    export_date TEXT,
    storage_area TEXT,
    shelf_space TEXT,
    user_name TEXT,
    detailed_notes TEXT,
    product_status TEXT
)");

// ค้นหาข้อมูลจากตาราง product_in ตาม product_id, customer_name หรือ product_name โดยดึงเฉพาะข้อมูลที่ quantity > 0
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_query = $_POST['search_query'] ?? '';
    $stmt = $db->prepare("SELECT * FROM product_in WHERE (product_id LIKE :query OR customer_name LIKE :query OR product_name LIKE :query) AND quantity > 0");
    $stmt->bindValue(':query', '%' . $search_query . '%');
    $results = $stmt->execute();

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $search_results[] = $row;
    }
}

// เมื่อกดปุ่ม Export ให้บันทึกข้อมูลในตาราง product_out
$error_message = ""; // ข้อความแจ้งเตือน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $product_id = $_POST['product_id'];
    $customer_name = $_POST['customer_name'];
    $product_name = $_POST['product_name'];
    $outer_size = $_POST['outer_size'];
    $quantity_export = $_POST['quantity'];
    $export_date = date('Y-m-d H:i:s'); // ใช้วันที่และเวลาประเทศไทย
    $storage_area = $_POST['storage_area'];
    $shelf_space = $_POST['shelf_space'];
    $user_name = $_SESSION['username']; // บันทึก user ที่ล็อกอินอยู่
    $detailed_notes = $_POST['detailed_notes'];
    $product_status = 'ส่งออก';

    // ตรวจสอบว่า Quantity ที่ส่งออกไม่เกินจากจำนวนที่มีอยู่
    $stmt_check = $db->prepare("SELECT quantity FROM product_in WHERE product_id = :product_id");
    $stmt_check->bindValue(':product_id', $product_id);
    $result_check = $stmt_check->execute();
    $row_check = $result_check->fetchArray(SQLITE3_ASSOC);
    $quantity_in_stock = $row_check['quantity'] ?? 0;

    // เมื่อกดปุ่ม Export ให้บันทึกข้อมูลในตาราง product_out
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
        // ... ส่วนของการเตรียมข้อมูลอื่น ๆ ...

        // ตั้งค่า export_date เป็นวันที่และเวลาปัจจุบันในรูปแบบ YYYY-MM-DD HH:MM
        $export_date = date('Y-m-d H:i');

        // บันทึกข้อมูลใน product_out
        $stmt = $db->prepare("INSERT INTO product_out (product_id, customer_name, product_name, outer_size, quantity, export_date, storage_area, shelf_space, user_name, detailed_notes, product_status) VALUES (:product_id, :customer_name, :product_name, :outer_size, :quantity, :export_date, :storage_area, :shelf_space, :user_name, :detailed_notes, :product_status)");
        $stmt->bindValue(':product_id', $product_id);
        $stmt->bindValue(':customer_name', $customer_name);
        $stmt->bindValue(':product_name', $product_name);
        $stmt->bindValue(':outer_size', $outer_size);
        $stmt->bindValue(':quantity', $quantity_export);
        $stmt->bindValue(':export_date', $export_date); // บันทึกในรูปแบบ YYYY-MM-DD HH:MM
        $stmt->bindValue(':storage_area', $storage_area);
        $stmt->bindValue(':shelf_space', $shelf_space);
        $stmt->bindValue(':user_name', $user_name);
        $stmt->bindValue(':detailed_notes', $detailed_notes);
        $stmt->bindValue(':product_status', $product_status);
        $stmt->execute();

        // อัปเดตจำนวนสินค้าใน product_in หลังจาก Export
        $stmt_update = $db->prepare("UPDATE product_in SET quantity = quantity - :quantity_export WHERE product_id = :product_id");
        $stmt_update->bindValue(':quantity_export', $quantity_export);
        $stmt_update->bindValue(':product_id', $product_id);
        $stmt_update->execute();

        // รีไดเรกต์ไปหน้าเดิมเพื่อป้องกันการส่งข้อมูลซ้ำเมื่อกดรีเฟรช
        header("Location: product_out.php");
        exit();
    }
}

// ดึงข้อมูลล่าสุด 10 รายการจาก product_out
$recent_exports = $db->query("SELECT * FROM product_out ORDER BY id DESC LIMIT 10");

include 'menu.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Out</title>
    <link rel="stylesheet" href="css/product_out.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container">
    <h2>Product Out</h2>

    <!-- แสดงข้อความแจ้งเตือนหากมีข้อผิดพลาด -->
    <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- ฟอร์มค้นหา -->
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Search by Product ID, Customer Name, or Product Name" required>
        <button type="submit" name="search">Search</button>
    </form>

    <!-- แสดงผลการค้นหา -->
<?php if (!empty($search_results)): ?>
    <h3>Search Results</h3>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Customer Name</th>
                <th>Product Name</th>
                <th>Outer Size (mm)</th>
                <th>Quantity in Stock</th>
                <th>Quantity to Export</th>
                <th>Storage Area</th>
                <th>Shelf Space</th>
                <th>Notes</th>
                <th>Export</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($search_results as $row): ?>
                <tr>
                    <form method="POST" action="">
                        <td><?php echo htmlspecialchars($row['product_id']); ?><input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?><input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($row['customer_name']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?><input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['product_name']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['outer_size']); ?><input type="hidden" name="outer_size" value="<?php echo htmlspecialchars($row['outer_size']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?> <!-- แสดงจำนวนที่มีในสต็อก --></td>
                        <td>
                            <!-- ตั้งค่าค่า max ของ input ตาม Quantity in Stock -->
                            <input type="number" name="quantity" placeholder="Enter Quantity" max="<?php echo htmlspecialchars($row['quantity']); ?>" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </td>
                        <td><?php echo htmlspecialchars($row['storage_area']); ?><input type="hidden" name="storage_area" value="<?php echo htmlspecialchars($row['storage_area']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['shelf_space']); ?><input type="hidden" name="shelf_space" value="<?php echo htmlspecialchars($row['shelf_space']); ?>"></td>
                        <td><input type="text" name="detailed_notes" value="<?php echo htmlspecialchars($row['detailed_notes']); ?>"></td>
                        <td><button type="submit" name="export">Export</button></td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

        
    <!-- ตารางแสดงรายการส่งออกล่าสุด -->
    <h3>Recent Exports</h3>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Customer Name</th>
                <th>Product Name</th>
                <th>Outer Size (mm)</th>
                <th>Quantity</th>
                <th>Export Date</th>
                <th>Storage Area</th>
                <th>Shelf Space</th>
                <th>User Name</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $recent_exports->fetchArray(SQLITE3_ASSOC)): ?>
                <tr id="row-<?php echo $row['product_id']; ?>">
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['outer_size']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['export_date']); ?></td>
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

<script src="js/product_out.js"></script>

</body>
</html>

