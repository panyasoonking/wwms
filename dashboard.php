<?php
include 'function/auth.php'; // ตรวจสอบการล็อกอิน
include 'menu.php';
// ตั้งค่าเขตเวลาเป็นเวลาประเทศไทย
date_default_timezone_set('Asia/Bangkok');

// เชื่อมต่อฐานข้อมูล
$database_path = 'data/Database.db';
$db = new SQLite3($database_path);

// ดึงข้อมูลสินค้ารับเข้าแยกตามเดือน
$checkin_data = $db->query("SELECT strftime('%Y-%m', checkin_date) AS month, SUM(quantity) AS total FROM product_in GROUP BY month ORDER BY month");
$checkin_months = [];
$checkin_totals = [];
while ($row = $checkin_data->fetchArray(SQLITE3_ASSOC)) {
    $checkin_months[] = $row['month'];
    $checkin_totals[] = $row['total'];
}

// ดึงข้อมูลสินค้าส่งออกแยกตามเดือน
$checkout_data = $db->query("SELECT strftime('%Y-%m', export_date) AS month, SUM(quantity) AS total FROM product_out GROUP BY month ORDER BY month");
$checkout_totals = [];
while ($row = $checkout_data->fetchArray(SQLITE3_ASSOC)) {
    $checkout_totals[] = $row['total'];
}

// คำนวณจำนวนสินค้ารับเข้า ส่งออก และสินค้าคงคลัง
$checkin_count = $db->querySingle("SELECT SUM(quantity) FROM product_in WHERE quantity > 0");
$checkout_count = $db->querySingle("SELECT SUM(quantity) FROM product_out WHERE product_status = 'ส่งออก'");
$inventory_count = $checkin_count - $checkout_count;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/dashboard.js" defer></script>
</head>
<body>
<div class="container">
    <h2>Warehouse Dashboard</h2>

    <!-- กราฟแท่ง -->
    <div class="chart-container">
        <canvas id="productChart"></canvas>
    </div>

    <!-- ส่งข้อมูลสินค้ารายเดือนไปยัง JavaScript -->
    <script>
        const checkinMonths = <?php echo json_encode($checkin_months); ?>;
        const checkinTotals = <?php echo json_encode($checkin_totals); ?>;
        const checkoutTotals = <?php echo json_encode($checkout_totals); ?>;
    </script>

     <!-- แสดงข้อมูลตัวเลข -->
     <div class="summary-container">
        <div class="summary-box">
            <i class="fas fa-box-open icon"></i>
            <span class="label">Received Products</span>
            <span class="count" id="checkin-count"><?php echo $checkin_count; ?></span>
        </div>
        <div class="summary-box">
            <i class="fas fa-dolly icon"></i>
            <span class="label">Exported Products</span>
            <span class="count" id="checkout-count"><?php echo $checkout_count; ?></span>
        </div>
        <div class="summary-box">
            <i class="fas fa-warehouse icon"></i>
            <span class="label">Current Inventory</span>
            <span class="count" id="inventory-count"><?php echo $inventory_count; ?></span>
        </div>
    </div>

    <!-- ตารางสินค้า -->
    <div class="table-container">
        <div class="table-selection">
            <button onclick="showTable('product_in')">Product In</button>
            <button onclick="showTable('product_out')">Product Out</button>
            <button onclick="showTable('inventory')">Inventory</button>
        </div>
        <input type="text" id="searchInput" placeholder="Search by Product ID, Customer Name, or Product Name" onkeyup="searchTable()">
        <table id="productTable">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Customer Name</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- ข้อมูลจะแสดงที่นี่ด้วย AJAX -->
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
