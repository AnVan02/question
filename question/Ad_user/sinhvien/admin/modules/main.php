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

        elseif ($action == 'student' ){
            include ("./modules/UI_admin.php");
        }

        elseif ($action == 'student' ){
            include ("./modules/student.php");
        }
        
        elseif ($action == 'add_khoahoc'){
            include ("./modules/add_khoahoc.php");
        }
      
        elseif ($action == 'khoahoc') {
            include ("./modules/khoahoc.php");
        }

        elseif ($action == 'question' ) {
            include ("./modules/question.php");
        }

        elseif ($action == 'them' ) {
            include ("./modules/them.php");
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