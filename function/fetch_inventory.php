<?php
$database_path = '../data/Database.db';
$db = new SQLite3($database_path);

// ดึงข้อมูลสินค้าคงคลังโดยคำนวณจาก Product In และ Product Out
$results = $db->query("SELECT product_in.product_id, product_in.customer_name, product_in.product_name, 
                      (product_in.quantity - COALESCE((SELECT SUM(quantity) FROM product_out WHERE product_out.product_id = product_in.product_id), 0)) AS quantity, 
                      product_in.checkin_date, 'คงคลัง' as status
                      FROM product_in 
                      GROUP BY product_in.product_id, product_in.customer_name, product_in.product_name, product_in.checkin_date
                      HAVING quantity > 0 
                      ORDER BY product_in.id DESC");

$output = "";
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $output .= "<tr>
                    <td>" . htmlspecialchars($row['product_id']) . "</td>
                    <td>" . htmlspecialchars($row['customer_name']) . "</td>
                    <td>" . htmlspecialchars($row['product_name']) . "</td>
                    <td>" . htmlspecialchars($row['quantity']) . "</td>
                    <td>" . htmlspecialchars($row['checkin_date']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                </tr>";
}
echo $output;
?>
