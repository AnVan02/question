<?php 
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    }
    else {
        $action = "-1";
    }

    if (isset($_GET['query'])) {
        $query = $_GET['query'];
    }
    else {
        $query = "-1";
    }
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item <?php if ($action === 'add_khoahoc') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=add_khoahoc">
                <i class="mdi mdi mdi-code-not-equal menu-icon"></i>
                <span class="menu-title">Thêm khoá học</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'student') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=student">
                <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title">Thêm sinh viên </span>
            </a>
        </li>

        
        <li class="nav-item <?php if ($action === 'UI_admin') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=UI_admin">
                <i class="menu-icon mdi mdi-home"></i>
                <span class="menu-title">Tra cứu sinh viên</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'question') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=add_question">
                <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title">Thêm câu hỏi</span>
            </a>
        </li>
        
        <li class="nav-item <?php if ($action === 'khoahoc') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=khoahoc">
                    <i class="mdi mdi-image menu-icon"></i>
                    <span class="menu-title">Thêm bài test</span>
            </a>
        </li>

     
        <li class="nav-item <?php if ($action === 'them') {echo "active";} ?>">
            <a class="nav-link" href="index.php?action=them">
                <i class="menu-icon mdi mdi-icon"></i>
                <span class="manu-title">Thêm tài khoản </span>
            </a>
        </li>
        
        <li class="nav-item <?php if ($action === 'account') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=account&query=account_list">
                <i class="mdi mdi-account-multiple-outline menu-icon"></i>
                <span class="menu-title">Quản Lý Tài khoản</span>
            </a>
        </li>

        <?php if($_SESSION['account_type'] == 2): ?>
            <li class="nav-item <?php if ($action === 'settings') { echo "active"; } ?>">
                <a class="nav-link" href="index.php?action=settings&query=settings">
                    <i class="menu-icon mdi mdi-settings-box"></i>
                    <span class="menu-title">Cài đặt</span>
                </a>
            </li>
        <?php else: ?>
            <li></li>
        <?php endif; ?>
    </ul>
</nav>