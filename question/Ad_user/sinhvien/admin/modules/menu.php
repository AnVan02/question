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
                <span class="menu-title"><i style=font-size:17px>📚 </i>Thêm khoá học</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'student') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=student">
                <span class="menu-title"><i style=font-size:17px>👨‍🎓 </i>Thêm sinh viên </span>
            </a>
        </li>

        
        <li class="nav-item <?php if ($action === 'UI_admin') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=UI_admin">
                <span class="menu-title"><i style=font-size:17px>🏠 </i>Tra cứu sinh viên</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'question') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=add_question">
                <span class="menu-title"><i style=font-size:17px>❓ </i> Thêm câu hỏi</span>
            </a>
        </li>
        
        <!-- <li class="nav-item <?php if ($action === 'khoahoc') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=khoahoc">
                    <span class="menu-title"><i>📝</i> Thêm bài test</span>
            </a>
        </li> -->

        <li class="nav-item <?php if ($action === 'add_taikhoan') { echo "activi"; } ?>">
            <a class="nav-link" href ="index.php?action=add_taikhoan">
                <span class="menu-title"><i></i> Quản lý tài khoản </span>
            </a>
        </li>
        
        <li class="nav-item <?php if ($action === 'them') {echo "active";} ?>">
            <a class="nav-link" href="index.php?action=them">
                <span class="manu-title"><i style=font-size:17px>➕ </i>Thêm tài khoản </span>
            </a>
        </li>
        
        
        <?php if($_SESSION['account_type'] == 2): ?>
            <li class="nav-item <?php if ($action === 'settings') { echo "active"; } ?>">
                <a class="nav-link" href="index.php?action=settings">
                    <span class="menu-title"><i style=font-size:17px>⚙️ </i>Cài đặt</span>
                </a>
            </li>
        <?php else: ?>
            <li></li>
        <?php endif; ?>
    </ul>
</nav>
