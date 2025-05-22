<div class="main-panel">
    <div class="content-wrapper" >
        <?php
        if (isset($_GET['action']) && $_GET['query']) {
            $action = $_GET['action'];
            $query = $_GET['query'];

        } else {
            $action = '';
            $query = '';
        }
        if ($action == 'dashboard' && $query == 'dashboard') {
            include("./UI/dashboard.php");
        }

        elseif ($action == './admin/add_khoahoc,php'){
            include ("admin/add_khoahoc.php");
        }

        elseif ($action == './admin/add_question.php') {
            include ("admin/add_question.php");
        }

        elseif ($action == './admin/baihoc.php') {
            include ("admin/khaohoc.php");   
        }
        elseif ($action == './admin/question.php') {
            include ("admin/khoahoc.php");
        }
        elseif ($action == 'admin/FAQ.php') {
            include ("admin/FAQ.php");
        }

        elseif ($action == './admin/ketqua.php'){
            include ("admin/ketqua.php");
        }

        elseif ($action == 'setting' && $query == 'settings') {
            include ("./admin/main/php");
        }
        else {
            include("./modules/home.php");
        }

        ?>
    </div>
</div>