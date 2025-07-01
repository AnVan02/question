
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


        // elseif ($action == 'hochoc') {
        //     include ("./modules/cauhoi/hoahoc.php");
        // }

        // elseif ($action == '') {
        //     include (".modules/cauhoi/python_cb.php");
        // }

        // elseif ($action == '') {
        //     include (".modules/cauhoi/python_nc.php");
        // }

        // elseif ($action = '') {
        //     include (".modules/cauhoi/sinhhoc.php");

        // }

        // elseif ($action = '') {
        //     include (".modules/cauhoi/sinhhoc.php"); 
            
        // }

        // elseif ($action ='') {
        //     include (".modules/cauhoi/yolo");
        // }

        // elseif ($action = ''){
        //     include (".modules/cauhoi/tienganh");
        // }

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