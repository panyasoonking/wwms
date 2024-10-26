<?php
// ตั้งค่าเชื่อมต่อฐานข้อมูล
$database_path = '../data/Database.db';

try {
    $db = new SQLite3($database_path);
} catch (Exception $e) {
    echo json_encode(['error' => 'Unable to connect to database']);
    exit();
}

// ดึงข้อมูลล่าสุด 10 รายการจากตาราง product_in
$results = $db->query("SELECT * FROM product_in ORDER BY id DESC LIMIT 10");

// สร้าง array สำหรับเก็บผลลัพธ์
$data = [];
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row;
}

// ส่งข้อมูลกลับในรูปแบบ JSON
header('Content-Type: application/json');
echo json_encode($data);
