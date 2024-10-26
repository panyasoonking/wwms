<?php
$database_path = '../data/Database.db';
$db = new SQLite3($database_path);

// ดึงข้อมูลจากตาราง Product In
$results = $db->query("SELECT product_id, customer_name, product_name, quantity, checkin_date, 'รับเข้า' as status FROM product_in WHERE quantity > 0 ORDER BY id DESC");

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
