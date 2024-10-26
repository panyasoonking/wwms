<?php
// ตั้งค่าเขตเวลาเป็นเวลาประเทศไทย
date_default_timezone_set('Asia/Bangkok');

// ตั้งค่าเส้นทางไฟล์ฐานข้อมูล
$database_path = '../data/Database.db';

// เชื่อมต่อฐานข้อมูล
try {
    $db = new SQLite3($database_path);
} catch (Exception $e) {
    echo "Error: Unable to open database - " . $e->getMessage();
    exit();
}

// ดึงข้อมูลล่าสุด 10 รายการจาก product_out
$recent_exports = $db->query("SELECT * FROM product_out ORDER BY id DESC LIMIT 10");

// สร้างตารางสำหรับแสดงข้อมูล
$output = "";
while ($row = $recent_exports->fetchArray(SQLITE3_ASSOC)) {
    $output .= "<tr>
                    <td>" . htmlspecialchars($row['product_id']) . "</td>
                    <td>" . htmlspecialchars($row['customer_name']) . "</td>
                    <td>" . htmlspecialchars($row['product_name']) . "</td>
                    <td>" . htmlspecialchars($row['outer_size']) . "</td>
                    <td>" . htmlspecialchars($row['quantity']) . "</td>
                    <td>" . htmlspecialchars($row['export_date']) . "</td>
                    <td>" . htmlspecialchars($row['storage_area']) . "</td>
                    <td>" . htmlspecialchars($row['shelf_space']) . "</td>
                    <td>" . htmlspecialchars($row['user_name']) . "</td>
                    <td>" . htmlspecialchars($row['detailed_notes']) . "</td>
                    <td>" . htmlspecialchars($row['product_status']) . "</td>
                </tr>";
}
echo $output;
