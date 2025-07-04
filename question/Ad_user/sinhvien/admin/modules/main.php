
<div class="main-panel">
    <div class="content-wrapper" >
        <?php
        if (isset($_GET['action'])) {
            $action = $_GET['action'];

        } else {
            $action = '';
            // $query = '';
        }
        if ($action == 'dashboard' && $query == 'dashboard') {
            include("./modules/dashboard.php");
        }
       
        elseif ($action == 'add_khoahoc'){
            include ("./modules/cauhoi/add_khoahoc.php");
        }
        
        elseif ($action == 'UI_admin') {
            include ("./modules/cauhoi/UI_admin.php");
        }
    
        elseif ($action == 'them') {
            include ("./modules/cauhoi/them.php");
        }

        elseif ($action == 'add_question') {
            include ("./modules/cauhoi/add_question.php");
        }

        elseif ($action == 'student' ){
            include ("./modules/cauhoi/student.php");
        }

        elseif ($action == 'khoahoc') {
            include ("./modules/cauhoi/khoahoc.php");
        }

        elseif ($action == 'question' ) {
            include ("./modules/cauhoi/question.php");
        }

        elseif ($action == 'them' ) {
            include ("./modules/cauhoi/them.php");
        }

        elseif ($action == 'add_taikhoan.php') {
            include ("./modules/add_taikhoan.php");
        }

        elseif ($action == 'add_taikhon.php') {
            include (".");
        }
        elseif($action =='account' ) {
            include("./modules/account/my_account.php");
        }

        elseif($action =='account' ) {
            include("./modules/account/password_change.php");
        }

        elseif($action =='account' && $query == 'account_list') {
            include("./modules/account/lietke.php");
        }
        
        elseif($action == 'account' && $query == 'account_them'){
            include("./modules/account/them.php");
        }

        elseif($action =='account' && $query == 'account_edit') {
            include("./modules/account/sua.php");
        }
        
        elseif($action =='dashboard' && $query == 'dashboard') {
            include("./modules/dashboard.php");
        } 

        elseif($action =='settings' && $query == 'settings') {
            include("./modules/settings/main.php");
        }

        else {
            include("./modules/home.php");
        }
        
        ?>
    </div>
</div>