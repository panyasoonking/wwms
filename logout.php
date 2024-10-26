<?php
session_start();
session_unset(); // ล้างข้อมูลทั้งหมดใน session
session_destroy(); // ทำลาย session

// ป้องกันการย้อนกลับไปที่หน้าที่ล็อกอินแล้ว
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // ตั้งค่าให้หน้าเพจหมดอายุ
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="3;url=index.php"> <!-- รีไดเรกต์ไปยัง index.html หลังจาก 3 วินาที -->
    <title>Logging out...</title>
    <script>
        // ป้องกันการย้อนกลับ
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 2000);

        // เคลียร์แคชของเบราว์เซอร์เพื่อป้องกันการย้อนกลับไปที่หน้าเดิม
        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
            window.location.href = 'index.php';
        });
    </script>
</head>
    <style>
        p   {
            text-align: center; 
            }
    </style>
<body>
    <p>You have been logged out. Redirecting to login page in 2 seconds...</p>
</body>
</html>
