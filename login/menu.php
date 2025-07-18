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
        <li class="nav-item <?php if ($action === 'home') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=home&query">
                <i class="menu-icon mdi mdi-home"></i>
                <span class="menu-title">Trang chủ </span>
                <!-- <?php echo $_SESSION['account_type'] ?> -->
            </a>
        </li>
        <li class="nav-item <?php if ($action === 'product' or $action === 'category') { echo "active"; } ?>">
            <a class="nav-link" data-bs-toggle="collapse" href="#products" aria-expanded="<?php if ($action === 'product' or $action === 'category') { echo "true"; } else { echo "false"; } ?>" aria-controls="products">
                <i class="menu-icon mdi mdi-flask"></i>
                <span class="menu-title">Sản phẩm </span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?php if ($action === 'product' or $action === 'category' or $action === 'collection' or $action === 'brand') { echo "show"; } ?>" id="products">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item <?php if ($query === 'product_list') { echo "active"; } ?>"> <a class="nav-link" href="index.php?action=product&query=product_list">Danh sách sản phẩm</a></li>
                    <li class="nav-item <?php if ($query === 'category_list') { echo "active"; } ?>"> <a class="nav-link" href="index.php?action=category&query=category_list">Danh sách nhãn hàng</a></li>
                    <li class="nav-item <?php if ($query === 'brand_list') { echo "active"; } ?>"> <a class="nav-link" href="index.php?action=brand&query=brand_list">Nhãn hàng</a></li>
                    <li class="nav-item <?php if ($query === 'product_inventory') { echo "active"; } ?>"> <a class="nav-link" href="index.php?action=product&query=product_inventory">Hàng tồn kho</a></li>
                </ul>
            </div>
        </li>
   
        <li class="nav-item <?php if ($action === 'article') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=article&query=article_list">
                <i class="mdi mdi mdi-code-not-equal menu-icon"></i>
                <span class="menu-title">Bài viết</span>
            </a>
        </li>
        
        <li class="nav-item <?php if ($action === 'banner') { echo "active"; } ?>">
        <a class="nav-link" href="index.php?action=banner&query=banner">
                <i class="mdi mdi-image menu-icon"></i>
                <span class="menu-title">Banner</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'baohanh') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=baohanh&query=baohanh">
                <i class="mdi mdi-import menu-icon"></i>
                <span class="menu-title">Upload bảo hành</span>
            </a>
        </li>

         <li class="nav-item <?php if ($action === 'check') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=check&query=check">
                <i class="mdi mdi-file-excel menu-icon"></i>
                <span class="menu-title">Xuất file excel</span>
            </a>
        </li>
                
        <li class="nav-item <?php if ($action === 'user') { echo "active"; } ?>">
        <a class="nav-link" href="index.php?action=user&query=user">
                <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title">User</span>
            </a>
        </li>
        <!-- <li class="nav-item <?php if ($action === 'quanly') { echo "active"; } ?>">
        <a class="nav-link" href="index.php?action=quanly&query=quanly">
                <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title">Quản lý user</span>
            </a>
        </li> -->
        <!-- <li class="nav-item <?php if ($action === 'customer') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=customer&query=customer_list">
                <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title">Khách hàng</span>
            </a>
        </li> -->
        

        <?php if($_SESSION['account_type'] == 2): ?>
            <li class="nav-item <?php if ($action === 'account') { echo "active"; } ?>">
                <a class="nav-link" href="index.php?action=account&query=account_list">
                    <i class="mdi mdi-account-multiple-outline menu-icon"></i>
                    <span class="menu-title">Quản Lý Tài khoản</span>
                </a>
            </li>
        <?php else: ?>
            <li></li>
        <?php endif; ?>
     
        <!-- <li class="nav-item <?php if ($action === 'order') { echo "active"; } ?>">
            <a class="nav-link" data-bs-toggle="collapse" href="#orders" aria-expanded="<?php if ($action === 'order') { echo "true"; } else { echo "false"; } ?>" aria-controls="orders">
                <i class="menu-icon mdi mdi-table"></i>
                <span class="menu-title">Đơn hàng</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?php if ($action === 'order') { echo "show"; } ?>" id="orders">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item <?php if ($query === 'order_live') { echo "active"; } ?>"> <a class="nav-link" href="index.php?action=order&query=order_live">Đơn hàng tại quầy</a></li>
                    <li class="nav-item <?php if ($query === 'order_list') { echo "active"; } ?>"> <a class="nav-link" href="index.php?action=order&query=order_list">Đơn hàng trực tuyến</a></li>
                    <li class="nav-item <?php if ($query === 'order_payment') { echo "active"; } ?>"> <a class="nav-link" href="index.php?action=order&query=order_payment">Lịch sửa thanh toán</a></li>
                </ul>
            </div>
        </li> -->
        <!-- <li class="nav-item <?php if ($action === 'inventory') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=inventory&query=inventory_list">
                <i class="mdi mdi-clipboard-text menu-icon"></i>
                <span class="menu-title">Phiếu nhập kho</span>
            </a>
        </li> -->
        
        <?php if($_SESSION['account_type'] == 2): ?>
            <!-- <li class="nav-item <?php if ($action === 'dashboard') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=dashboard&query=dashboard">
                <i class="menu-icon mdi mdi-chart-line"></i>
                <span class="menu-title">Thống Kê</span>
            </a>
            </li> -->
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