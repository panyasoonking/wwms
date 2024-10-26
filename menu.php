<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- menu.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="css/menu.css"> <!-- เชื่อมต่อไฟล์ CSS -->
<!-- ปุ่มเปิด-ปิดเมนู -->
<button class="menu-toggle" onclick="toggleMenu()">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <h2>Menus</h2>
    <p>User: <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?></p>
    <p>Status: <?php echo isset($_SESSION['status']) ? $_SESSION['status'] : 'Unknown'; ?></p>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="product_in.php"><i class="fas fa-box"></i> Product In</a></li>
        <li><a href="product_out.php"><i class="fas fa-box-open"></i> Product Out</a></li>
        <li><a href="add_data.php"><i class="fas fa-plus-circle"></i> Add Data</a></li>
        <?php if (isset($_SESSION['status']) && $_SESSION['status'] === 'admin'): ?>
            <li><a href="add_user.php"><i class="fas fa-user-plus"></i> Add User</a></li>
        <?php endif; ?>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>


<script>
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    }
</script>
