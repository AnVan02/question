<?php 
    // Gán giá trị mặc định nếu không có tham số
    $action = $_GET['action'] ?? '-1';
    $query = $_GET['query'] ?? '-1';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sidebar Navigation</title>
    <!-- Link đến Bootstrap & Icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet">
</head>
<body>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item <?php if ($action === 'home') echo 'active'; ?>">
            <a class="nav-link" href="index.php?action=home&query=-1">
                <i class="menu-icon mdi mdi-home"></i>
                <span class="menu-title">Trang chủ</span>
            </a>
        </li>

        <li class="nav-item <?php if ($action === 'product' || $action === 'category') echo 'active'; ?>">
            <a class="nav-link" data-bs-toggle="collapse" href="#products" aria-expanded="<?php echo ($action === 'product' || $action === 'category') ? 'true' : 'false'; ?>" aria-controls="products">
                <i class="menu-icon mdi mdi-flask"></i>
                <span class="menu-title" >Khoá học</span>
            </a>
            <div class="collapse <?php if (in_array($action, ['aad_khoahoc', 'khoahoc', 'question'])) echo 'show'; ?>" id="products">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item <?php if ($query === 'product_list') echo 'active'; ?>">
                        <a class="nav-link" href="./add_khoahoc.php">Thêm khoá học</a>
                    </li>
                    
                    <li class="nav-item <?php if ($query === 'product_list') echo 'active'; ?>">
                        <a class="nav-link" href="./khoahoc.php">Thêm bài kiểm tra</a>
                    </li>

                    <li class="nav-item <?php if ($query === 'questio') echo 'active';?>">
                         <a class="nav-link" href="./baihoc.php">Thêm bài hoc</a>
                    </li>
                    
                    <li class="nav-item <?php if ($query === 'category_list') echo 'active'; ?>">
                        <a class="nav-link" href="./add_question.php">Thêm câu hỏi</a>
                    </li>

                </ul>
            </div>
        </li>
        </li>

        
    
        <li class="nav-item <?php if ($action === 'quanly') { echo "active"; } ?>">
            <a class="nav-link" href="student.php">
                    <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title">Thêm mới tài khoản </span>
            </a>
        </li> 

        <!-- <li class="nav-item <?php if ($action === 'customer') { echo "active"; } ?>">
            <a class="nav-link" href="index.php?action=customer&query=customer_list">
                <i class="mdi mdi-account-box-outline menu-icon"></i>
                <span class="menu-title"></span>
            </a>
        </li> -->

        <?php if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 2): ?>
            <li class="nav-item <?php if ($action === 'account') echo 'active'; ?>">
                <a class="nav-link" href="./student.php">
                    <i class="mdi mdi-account-multiple-outline menu-icon"></i>
                    <span class="menu-title">Thêm mới Id</span>
                </a>
            </li>
            <li class="nav>

            </li>

            <li class="nav-item <?php if ($action === 'settings') echo 'active'; ?>">
                <a class="nav-link" href="Thêm ">
                    <i class="menu-icon mdi mdi-settings-box"></i>
                    <span class="menu-title">Thêm mới tài khoản </span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<style>
        .nav-item.active > .nav-link {
            background-color: #0d6efd;
            color: #fff;
        }

        .nav-item .nav-link {
            color: #333;
        }

        .nav-item .nav-link:hover {
            background-color: #e9ecef;
        }

        .menu-icon {
            margin-right: 10px;
        }

        .sidebar {
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
            min-height: 100vh;
        }
    </style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

