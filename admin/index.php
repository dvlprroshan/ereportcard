<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$teachersData = array();

require_once "../config.php";

// print all teachers accounts
$sql = "SELECT username,info,id from users WHERE types='teachers'";

if ($result = $mysqli->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            array_push($teachersData, $row);
        }
        $result->free();
    } else {
        echo "no Records are found";
    }
} else {
    echo "Oops! Something went wrong. Please try again later.";
}


// delete teachers account
if (array_key_exists("deleteTeacherAcc", $_POST) && $_POST['deleteTeacherAcc'] == 'true' && isset($_SESSION["loggedin"]) && $_SESSION["accTypes"] == "mainAdmin") {
    echo "delete FROM users WHERE id = " . $_POST['deleteTeacherAccIndexId'];
    if ($mysqli->query("delete FROM users WHERE id = " . $_POST['deleteTeacherAccIndexId'])) {
        $sucessMessage = array("Account deleted successfully");
        header("location:index.php");
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
}


// add new elpolyees

$fullname = $username = $password = $confirm_password = $select_sub = "";
$fullname_err = $username_err = $password_err = $confirm_password_err = $select_sub_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" &&  array_key_exists("createNewTeachers", $_POST) && $_POST['createNewTeachers'] == "true") {

    // Validate FullName
    if (empty(trim($_POST["first_name"])) && empty(trim($_POST["last_name"]))) {
        $fullname_err = "Please enter your full name";
    } elseif (strlen($_POST["first_name"]) < 3) {
        $fullname_err = "Your first Name should be at least 3 characters.";
    } else {
        $fullname = array($_POST["first_name"], $_POST["last_name"]);
    }
    // Validate select_classes
    if (empty(trim($_POST["select_sub"]))) {
        $select_sub_err = "Please select your subject";
    } else {
        $select_sub = $_POST["select_sub"];
    }

    // validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // store result
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($fullname_err) && empty($select_sub_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password,types,info) VALUES (?, ?,?,?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $typesUser = "teachers";
            $stmt->bind_param("ssss", $param_username, $param_password, $typesUser, $param_info);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $info_data = json_encode(array("full_name" => $fullname, "subjects" => $select_sub));
            $param_info = $info_data;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {

                echo "<script>window.location.href = 'index.php';</script>";
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}
$mysqli->close();





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard eDetailsCard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Merienda+One">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script>
        function setValueInEditForm(value) {
            console.log(value)
        }
    </script>

    <style>
        body {
            background: #eeeeee;
        }

        .form-inline {
            display: inline-block;
        }

        .navbar-header.col {
            padding: 0 !important;
        }

        .navbar {
            background: #fff;
            padding-left: 16px;
            padding-right: 16px;
            border-bottom: 1px solid #d6d6d6;
            box-shadow: 0 0 4px rgba(0, 0, 0, .1);
        }

        .nav-link img {
            border-radius: 50%;
            width: 36px;
            height: 36px;
            margin: -8px 0;
            float: left;
            margin-right: 10px;
        }

        .navbar .navbar-brand {
            color: #555;
            padding-left: 0;
            padding-right: 50px;
            font-family: 'Merienda One', sans-serif;
        }

        .navbar .navbar-brand i {
            font-size: 20px;
            margin-right: 5px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            box-shadow: none;
            padding-right: 35px;
            border-radius: 3px !important;
        }

        .search-box .input-group-addon {
            min-width: 35px;
            border: none;
            background: transparent;
            position: absolute;
            right: 0;
            z-index: 9;
            padding: 7px;
            height: 100%;
        }

        .search-box i {
            color: #a0a5b1;
            font-size: 19px;
        }

        .navbar .nav-item i {
            font-size: 18px;
        }

        .navbar .dropdown-item i {
            font-size: 16px;
            min-width: 22px;
        }

        .navbar .nav-item.open>a {
            background: none !important;
        }

        .navbar .dropdown-menu {
            border-radius: 1px;
            border-color: #e5e5e5;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
        }

        .navbar .dropdown-menu a {
            color: #777;
            padding: 8px 20px;
            line-height: normal;
        }

        .navbar .dropdown-menu a:hover,
        .navbar .dropdown-menu a:active {
            color: #333;
        }

        .navbar .dropdown-item .material-icons {
            font-size: 21px;
            line-height: 16px;
            vertical-align: middle;
            margin-top: -2px;
        }

        .navbar .badge {
            color: #fff;
            background: #f44336;
            font-size: 11px;
            border-radius: 20px;
            position: absolute;
            min-width: 10px;
            padding: 4px 6px 0;
            min-height: 18px;
            top: 5px;
        }

        .navbar a.notifications,
        .navbar a.messages {
            position: relative;
            margin-right: 10px;
        }

        .navbar a.messages {
            margin-right: 20px;
        }

        .navbar a.notifications .badge {
            margin-left: -8px;
        }

        .navbar a.messages .badge {
            margin-left: -4px;
        }

        .navbar .active a,
        .navbar .active a:hover,
        .navbar .active a:focus {
            background: transparent !important;
        }

        @media (min-width: 1200px) {
            .form-inline .input-group {
                width: 300px;
                margin-left: 30px;
            }
        }

        @media (max-width: 1199px) {
            .form-inline {
                display: block;
                margin-bottom: 10px;
            }

            .input-group {
                width: 100%;
            }
        }

        .modal-confirm {
            color: #636363;
            width: 400px;
        }

        .modal-confirm .modal-content {
            padding: 20px;
            border-radius: 5px;
            border: none;
            text-align: center;
            font-size: 14px;
        }

        .modal-confirm .modal-header {
            border-bottom: none;
            position: relative;
        }

        .modal-confirm h4 {
            text-align: center;
            font-size: 26px;
            margin: 30px 0 -10px;
        }

        .modal-confirm .close {
            position: absolute;
            top: -5px;
            right: -2px;
        }

        .modal-confirm .modal-body {
            color: #999;
        }

        .modal-confirm .modal-footer {
            border: none;
            text-align: center;
            border-radius: 5px;
            font-size: 13px;
            padding: 10px 15px 25px;
        }

        .modal-confirm .modal-footer a {
            color: #999;
        }

        .modal-confirm .icon-box {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            z-index: 9;
            text-align: center;
            border: 3px solid #f15e5e;
        }

        .modal-confirm .icon-box i {
            color: #f15e5e;
            font-size: 46px;
            display: inline-block;
            margin-top: 13px;
        }

        .modal-confirm .btn,
        .modal-confirm .btn:active {
            color: #fff;
            border-radius: 4px;
            background: #60c7c1;
            text-decoration: none;
            transition: all 0.4s;
            line-height: normal;
            min-width: 120px;
            border: none;
            min-height: 40px;
            border-radius: 3px;
            margin: 0 5px;
        }

        .modal-confirm .btn-secondary {
            background: #c1c1c1;
        }

        .modal-confirm .btn-secondary:hover,
        .modal-confirm .btn-secondary:focus {
            background: #a8a8a8;
        }

        .modal-confirm .btn-danger {
            background: #f15e5e;
        }

        .modal-confirm .btn-danger:hover,
        .modal-confirm .btn-danger:focus {
            background: #ee3535;
        }

        .trigger-btn {
            display: inline-block;
            margin: 100px auto;
        }

        .table-responsive {
            margin: 30px 0;
        }

        .table-wrapper {
            background: #fff;
            padding: 20px 25px;
            border-radius: 3px;
            min-width: 1000px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
        }

        .table-title {
            padding-bottom: 15px;
            background: #435d7d;
            color: #fff;
            padding: 16px 30px;
            min-width: 100%;
            margin: -20px -25px 10px;
            border-radius: 3px 3px 0 0;
        }

        .table-title h2 {
            margin: 5px 0 0;
            font-size: 24px;
        }

        .table-title .btn-group {
            float: right;
        }

        .table-title .btn {
            color: #fff;
            float: right;
            font-size: 13px;
            border: none;
            min-width: 50px;
            border-radius: 2px;
            border: none;
            outline: none !important;
            margin-left: 10px;
        }

        .table-title .btn i {
            float: left;
            font-size: 21px;
            margin-right: 5px;
        }

        .table-title .btn span {
            float: left;
            margin-top: 2px;
        }

        table.table tr th,
        table.table tr td {
            border-color: #e9e9e9;
            padding: 12px 15px;
            vertical-align: middle;
        }

        table.table tr th:first-child {
            width: 60px;
        }

        table.table tr th:last-child {
            width: 100px;
        }

        table.table-striped tbody tr:nth-of-type(odd) {
            background-color: #fcfcfc;
        }

        table.table-striped.table-hover tbody tr:hover {
            background: #f5f5f5;
        }

        table.table th i {
            font-size: 13px;
            margin: 0 5px;
            cursor: pointer;
        }

        table.table td:last-child i {
            opacity: 0.9;
            font-size: 22px;
            margin: 0 5px;
        }

        table.table td a {
            font-weight: bold;
            color: #566787;
            display: inline-block;
            text-decoration: none;
            outline: none !important;
        }

        table.table td a:hover {
            color: #2196F3;
        }

        table.table td a.edit {
            color: #FFC107;
        }

        table.table td a.delete {
            color: #F44336;
        }

        table.table td i {
            font-size: 19px;
        }

        table.table .avatar {
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 10px;
        }

        .pagination {
            float: right;
            margin: 0 0 5px;
        }

        .pagination li a {
            border: none;
            font-size: 13px;
            min-width: 30px;
            min-height: 30px;
            color: #999;
            margin: 0 2px;
            line-height: 30px;
            border-radius: 2px !important;
            text-align: center;
            padding: 0 6px;
        }

        .pagination li a:hover {
            color: #666;
        }

        .pagination li.active a,
        .pagination li.active a.page-link {
            background: #03A9F4;
        }

        .pagination li.active a:hover {
            background: #0397d6;
        }

        .pagination li.disabled i {
            color: #ccc;
        }

        .pagination li i {
            font-size: 16px;
            padding-top: 6px
        }

        .hint-text {
            float: left;
            margin-top: 10px;
            font-size: 13px;
        }

        /* Custom checkbox */
        .custom-checkbox {
            position: relative;
        }

        .custom-checkbox input[type="checkbox"] {
            opacity: 0;
            position: absolute;
            margin: 5px 0 0 3px;
            z-index: 9;
        }

        .custom-checkbox label:before {
            width: 18px;
            height: 18px;
        }

        .custom-checkbox label:before {
            content: '';
            margin-right: 10px;
            display: inline-block;
            vertical-align: text-top;
            background: white;
            border: 1px solid #bbb;
            border-radius: 2px;
            box-sizing: border-box;
            z-index: 2;
        }

        .custom-checkbox input[type="checkbox"]:checked+label:after {
            content: '';
            position: absolute;
            left: 6px;
            top: 3px;
            width: 6px;
            height: 11px;
            border: solid #000;
            border-width: 0 3px 3px 0;
            transform: inherit;
            z-index: 3;
            transform: rotateZ(45deg);
        }

        .custom-checkbox input[type="checkbox"]:checked+label:before {
            border-color: #03A9F4;
            background: #03A9F4;
        }

        .custom-checkbox input[type="checkbox"]:checked+label:after {
            border-color: #fff;
        }

        .custom-checkbox input[type="checkbox"]:disabled+label:before {
            color: #b8b8b8;
            cursor: auto;
            box-shadow: none;
            background: #ddd;
        }

        /* Modal styles */
        .modal .modal-dialog {
            max-width: 400px;
        }

        .modal .modal-header,
        .modal .modal-body,
        .modal .modal-footer {
            padding: 20px 30px;
        }

        .modal .modal-content {
            border-radius: 3px;
            font-size: 14px;
        }

        .modal .modal-footer {
            background: #ecf0f1;
            border-radius: 0 0 3px 3px;
        }

        .modal .modal-title {
            display: inline-block;
        }

        .modal .form-control {
            border-radius: 2px;
            box-shadow: none;
            border-color: #dddddd;
        }

        .modal textarea.form-control {
            resize: vertical;
        }

        .modal .btn {
            border-radius: 2px;
            min-width: 100px;
        }

        .modal form label {
            font-weight: normal;
        }

        .form-control {
            height: 41px;
            background: #f2f2f2;
            box-shadow: none !important;
            border: none;
        }

        .form-control:focus {
            background: #e2e2e2;
        }

        .form-control,
        .btn {
            border-radius: 3px;
        }

        .create-teacher-form {
            width: 400px;
            margin: 50px auto;

        }

        .create-teacher-form form {
            color: #999;
            border-radius: 3px;
            background: #fff;
            box-shadow: 0px 3px 30px 3px rgba(0, 0, 0, 0.5);
            padding: 30px;
        }

        .create-teacher-form h2 {
            color: #333;
            font-weight: bold;
            margin-top: 0;
        }

        .create-teacher-form hr {
            margin: 0 -30px 20px;
        }

        .create-teacher-form .form-group {
            margin-bottom: 20px;
        }

        .create-teacher-form input[type="checkbox"] {
            margin-top: 3px;
        }

        .create-teacher-form .row div:first-child {
            padding-right: 10px;
        }

        .create-teacher-form .row div:last-child {
            padding-left: 10px;
        }

        .create-teacher-form .btn {
            font-size: 16px;
            font-weight: bold;
            background: #3598dc;
            border: none;
            min-width: 140px;
        }

        .create-teacher-form .btn:hover,
        .create-teacher-form .btn:focus {
            background: #2389cd !important;
            outline: none;
        }

        .create-teacher-form a {
            color: #fff;
            text-decoration: underline;
        }

        .create-teacher-form a:hover {
            text-decoration: none;
        }

        .create-teacher-form form a {
            color: #3598dc;
            text-decoration: none;
        }

        .create-teacher-form form a:hover {
            text-decoration: underline;
        }

        .create-teacher-form .hint-text {
            padding-bottom: 15px;
            text-align: center;
        }

        .filter-option-inner-inner {
            color: white !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            // Activate tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // Select/Deselect checkboxes
            var checkbox = $('table tbody input[type="checkbox"]');
            $("#selectAll").click(function() {
                if (this.checked) {
                    checkbox.each(function() {
                        this.checked = true;
                    });
                } else {
                    checkbox.each(function() {
                        this.checked = false;
                    });
                }
            });
            checkbox.click(function() {
                if (!this.checked) {
                    $("#selectAll").prop("checked", false);
                }
            });
        });
    </script>
</head>

<body>
    <!-- navbar -->
    <nav class="navbar navbar-expand-xl navbar-light bg-light">
        <a href="#" class="navbar-brand"><i class="fa fa-cubes text-danger"></i>e<b>DetailsCard</b></a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Collection of nav links, forms, and other content for toggling -->
        <div id="navbarCollapse" class="collapse navbar-collapse justify-content-start">

            <div class="navbar-nav ml-auto">
                <div class="nav-item dropdown">
                    <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle user-action"><img src="https://www.tutorialrepublic.com/examples/images/avatar/2.jpg" class="avatar" alt="Avatar"> <?php echo $_SESSION['username'] ?> <b class="caret"></b></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item"><i class="fa fa-user-o"></i> Profile</a></a>
                        <a href="#" class="dropdown-item"><i class="fa fa-sliders"></i> Settings</a></a>
                        <div class="dropdown-divider"></div>
                        <a href="#confirmLogout" class="dropdown-item" data-toggle="modal"><i class="material-icons">&#xE8AC;</i> Logout</a></a>
                    </div>
                </div>
            </div>
        </div>


        <!-- confirm model -->
        <div id="confirmLogout" class="modal fade">
            <div class="modal-dialog modal-confirm">
                <div class="modal-content">
                    <div class="modal-header flex-column">
                        <div class="icon-box">
                            <i class="material-icons">&#xE5CD;</i>
                        </div>
                        <h4 class="modal-title w-100">Are you sure?</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Do you really want to logout this account? After this process you may be need to enter user and password again!</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger"><a href="../logout.php " class="text-light">Logout</a> </button>
                    </div>
                </div>
            </div>
    </nav>
    <!-- manage teachers panel -->
    <?php if ($_SESSION['accTypes'] == "mainAdmin") : ?>
        <div class="container-xl">
            <div class="table-responsive">
                <div class="table-wrapper">
                    <div class="table-title">
                        <div class="row">
                            <div class="col-sm-6">
                                <h2>Manage <b>Teachers</b></h2>
                            </div>
                            <div class="col-sm-6">
                                <a href="#addTeachersModal" class="btn btn-success" data-toggle="modal"><i class="material-icons">&#xE147;</i> <span>Add New Employee</span></a>
                                <a href="#" class="btn btn-secondary" onclick="refreshData()" data-toggle="modal"><i class="material-icons refresh-data " style="margin-top:-2px;height:23px;width:23px;">&#x21bb;</i> <span>Refresh</span></a>
                                <script>
                                    let elm = document.querySelector(".refresh-data");
                                    elm.style.transition = "all .5s linear";

                                    function refreshData() {
                                        elm.style.transform += "rotate(360deg)";
                                        window.location.href = 'index.php';
                                    }
                                </script>
                            </div>
                        </div>
                    </div>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <span class="custom-checkbox">
                                        <input type="checkbox" id="selectAll">
                                        <label for="selectAll"></label>
                                    </span>
                                </th>
                                <th>Name</th>
                                <th>Classes</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachersData as $elm) : ?>
                                <tr>
                                    <td>
                                        <span class="custom-checkbox">
                                            <input type="checkbox" id="checkbox1" name="options[]" value="1">
                                            <label for="checkbox1"></label>
                                        </span>
                                    </td>
                                    <td><?php print_r(json_decode($elm['info'])->full_name[0] . " " . json_decode($elm['info'])->full_name[1]) ?></td>
                                    <td><?php print_r(json_decode($elm['info'])->subjects) ?></td>
                                    <td><?= $elm['username'] ?></td>
                                    <td>************</td>
                                    <td>
                                        <a href="#editTeachersModel" onclick='editTeacherData(`<?php echo $elm["id"] ?>`)' class="edit" data-toggle="modal"><i class="material-icons" data-toggle="tooltip" title="Edit">&#xE254;</i></a>
                                        <a href="#deleteTeacherAcc" onclick="deleteTeacherAccount(<?php echo $elm['id'] ?>)" class="delete" data-toggle="modal"><i class="material-icons" data-toggle="tooltip" title="Delete">&#xE872;</i></a>

                                    </td>
                                </tr>
                            <?php endforeach ?>
                            <script>
                                function deleteTeacherAccount(e) {
                                    document.getElementsByName("deleteTeacherAccIndexId")[0].value = e;

                                }
                            </script>

                        </tbody>
                    </table>
                    <div class="clearfix">
                        <div class="hint-text">Showing <b><?php echo sizeof($teachersData) ?></b> out of <b><?php echo sizeof($teachersData) ?></b> entries</div>
                        <ul class="pagination">
                            <li class="page-item disabled"><a href="#">Previous</a></li>
                            <li class="page-item active"><a href="#" class="page-link">1</a></li>
                            <li class="page-item  "><a href="#" class="page-link">2</a></li>
                            <li class="page-item " style="opacity:.8;"><a href="#" class="page-link">3</a></li>
                            <li class="page-item disabled" style="opacity:.6;"><a href="#" class="page-link">4</a></li>
                            <li class="page-item disabled" style="opacity:.4;"><a href="#" class="page-link">5</a></li>
                            <li class="page-item "><a href="#" class="page-link">Next</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Modal HTML -->
        <div id="addTeachersModal" class="modal fade">
            <div class="modal-dialog create-teacher-form modal-content">


                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="text" name="createNewTeachers" value="true" style="display: none;">
                    <h2>Add new Teachers</h2>
                    <p>Please fill in this form to create an account of teacher!</p>
                    <hr>
                    <div class="form-group">
                        <div class="row">
                            <div class="col"><input type="text" class="form-control" name="first_name" placeholder="First Name" required="required"></div>
                            <div class="col"><input type="text" class="form-control" name="last_name" placeholder="Last Name" required="required"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <script>
                            function upadateValueCreateNew(e) {
                                let tempData = [];
                                for (let i = 0; i < e.selectedOptions.length; i++) {
                                    tempData.push(e.selectedOptions[i].innerText.trim());
                                }
                                document.querySelector(".hiddenSelectedBox").value = tempData.toString();
                            }
                        </script>
                        <input type="text" name="select_sub" value="" class="hiddenSelectedBox" style="display:none;">
                        <select id="classes-sel-new" class="form-control selectpicker" onchange="upadateValueCreateNew(this)" placeholder="select classes" multiple>
                            <option value="HTML">HTML</option>
                            <option value="Jquery">Jquery</option>
                            <option value="CSS">CSS</option>
                            <option value="Bootstrap 3">Bootstrap 3</option>
                            <option value="Bootstrap 4">Bootstrap 4</option>
                            <option value="Java">Java</option>
                            <option value="Javascript">Javascript</option>
                            <option value="Angular">Angular</option>
                            <option value="Python">Python</option>
                            <option value="Hybris">Hybris</option>
                            <option value="SQL">SQL</option>
                            <option value="NOSQL">NOSQL</option>
                            <option value="NodeJS">NodeJS</option>
                        </select>
                        <script>
                            $('.my-select').selectpicker();
                        </script>


                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="username" required="required">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="Password" required="required">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required="required">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Create Teachers</button>
                    </div>
            </div>

            </form>


        </div>
        </div>
        <!-- Edit Modal HTML -->
        <script>
            window.teachersData = <?php echo  json_encode($teachersData) ?>;

            function editTeacherData(indexId) {
                let teacherFiled = teachersData.find(e => e.id == indexId);
                let info = JSON.parse(teacherFiled['info']);
                document.querySelector(".edit-first_name").value = info['full_name'][0];
                document.querySelector(".edit-last_name").value = info['full_name'][1];
            }
        </script>

        <div id="editTeachersModel" class="modal fade">
            <div class="modal-dialog create-teacher-form modal-content">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="text" name="createNewTeachers" value="true" style="display: none;">
                    <h2>Update new Teachers</h2>
                    <p>Please update this form to update data of teacher!</p>
                    <hr>
                    <div class="form-group">
                        <div class="row">
                            <div class="col"><input type="text" class="form-control edit-first_name" name="first_name" placeholder="First Name" required="required"></div>
                            <div class="col"><input type="text" class="form-control edit-last_name" name="last_name" placeholder="Last Name" required="required"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <select id="classes-sel-edit" class="form-control selectpicker" onchange="console.log('hi')" name="select_sub" placeholder="select classes" multiple>
                            <option value="HTML">HTML</option>
                            <option value="Jquery">Jquery</option>
                            <option value="CSS">CSS</option>
                            <option value="Bootstrap 3">Bootstrap 3</option>
                            <option value="Bootstrap 4">Bootstrap 4</option>
                            <option value="Java">Java</option>
                            <option value="Javascript">Javascript</option>
                            <option value="Angular">Angular</option>
                            <option value="Python">Python</option>
                            <option value="Hybris">Hybris</option>
                            <option value="SQL">SQL</option>
                            <option value="NOSQL">NOSQL</option>
                            <option value="NodeJS">NodeJS</option>
                        </select>
                        <script>
                            $('.my-select').selectpicker();
                        </script>


                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="username" required="required">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="Password" required="required">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required="required">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Create Teachers</button>
                    </div>
            </div>

            </form>


        </div>
        </div>
        <!-- Delete Modal HTML -->
        <div id="deleteTeacherAcc" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="index.php">
                        <div class="modal-header">
                            <h4 class="modal-title">Delete Teachers</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete these Records?</p>
                            <p class="text-danger">Type <b>DeleteThisAccount</b> to delete this account.</p>
                        </div>
                        <div class="form-group">
                            <input type="text" autocomplete="off" class="form-control confirm_input_check" oninput="confirmInputCheck(this)" onpaste=" return false;" name="confirm_input_check" style="transform:scaleX(.9)" placeholder="Read upper text ..." required="required">
                        </div>
                        <div class="modal-footer">
                            <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
                            <input type="submit" style="display:none;" class="btn btn-danger deleteTeacherAcc" value="Delete">
                            <input type="text" name="deleteTeacherAcc" value="true" style="display:none;">
                            <input type="text" name="deleteTeacherAccIndexId" value="" style="display:none;">

                            <input type="button" class="btn btn-danger deleteTeacherAccMainBtn" value="Delete" disabled>
                            <script>
                                document.querySelector(".deleteTeacherAccMainBtn").addEventListener("click", () => {
                                    setTimeout(() => {
                                        document.getElementsByClassName("deleteTeacherAcc")[0].click();
                                    }, 1000);
                                })

                                function confirmInputCheck(el) {
                                    if (el.value == "DeleteThisAccount") {
                                        document.getElementsByClassName("deleteTeacherAccMainBtn")[0].disabled = false;
                                    } else {
                                        document.getElementsByClassName("deleteTeacherAccMainBtn")[0].disabled = true;
                                    }
                                }
                            </script>
                        </div>
                    </form>
                </div>
            </div>
        </div>



    <?php endif; ?>



</body>

</html>