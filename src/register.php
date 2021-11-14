<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = $admin_password = "";
$username_err = $password_err = $confirm_password_err = $admin_password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate username
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
    // Validate admin password
    if (empty(trim($_POST["admin_password"]))) {
        $admin_password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $admin_password_err = "Password must have atleast 6 characters.";
    } else {
        $admin_password = trim($_POST["admin_password"]);
    }

    if (empty($admin_password_err)) {
        $admin_password_err = $admin_password == "ThisIsAdminPass" ? "" : "Your Password is Worng";
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($admin_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password,types) VALUES (?, ?,?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $typesUser = "mainAdmin";
            $stmt->bind_param("sss", $param_username, $param_password, $typesUser);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("location: login.php");
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
        }

        .modal-login {
            color: #636363;
            width: 350px;
        }

        .modal-login .modal-content {
            padding: 20px;
            border-radius: 5px;
            border: none;
        }

        .modal-login .modal-header {
            border-bottom: none;
            position: relative;
            justify-content: center;
        }

        .modal-login h4 {
            text-align: center;
            font-size: 26px;
            margin: 30px 0 -15px;
        }

        .modal-login .form-control:focus {
            border-color: #70c5c0;
        }

        .modal-login .form-control,
        .modal-login .btn {
            min-height: 40px;
            border-radius: 3px;
        }

        .modal-login .close {
            position: absolute;
            top: -5px;
            right: -5px;
        }

        .modal-login .modal-footer {
            background: #ecf0f1;
            border-color: #dee4e7;
            text-align: center;
            justify-content: center;
            margin: 0 -20px -20px;
            border-radius: 5px;
            font-size: 13px;
        }

        .modal-login .modal-footer a {
            color: #999;
        }

        .modal-login .avatar {
            position: absolute;
            margin: 0 auto;
            left: 0;
            right: 0;
            top: -70px;
            width: 95px;
            height: 95px;
            border-radius: 50%;
            z-index: 9;
            background-color: #FFE53B;
            background-image: linear-gradient(147deg, #FFE53B 0%, #FF2525 74%);

            padding: 15px;
            box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.1);
        }

        .modal-login .avatar img {
            width: 100%;
        }

        .modal-login.modal-dialog {
            margin-top: 80px;
        }

        .modal-login .btn,
        .modal-login .btn:active {
            color: #fff;
            border-radius: 4px;
            background-color: #FFE53B !important;
            background-image: linear-gradient(147deg, #FFE53B 0%, #FF2525 74%) !important;

            text-decoration: none;
            transition: all 0.4s;
            line-height: normal;
            border: none;
        }

        .modal-login .btn:hover,
        .modal-login .btn:focus {
            background-color: #FFE57B !important;
            background-image: linear-gradient(147deg, #FFE57B 0%, #FF2555 74%) !important;
            outline: none;
        }

        .trigger-btn {
            display: inline-block;
            margin: 100px auto;
        }
    </style>
</head>

<body>
  
    <div class="text-center">
        <?php


        foreach (array($username_err, $admin_password_err, $password_err, $confirm_password_err) as $elm) {
            if (!empty($elm)) {
                echo '<div class="alert alert-danger container-md mt-md-3 alert-dismissible fade show" role="alert">
                        <strong>Error:</strong> '
                    . $elm .
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            }
        }

        ?>
        <h2>
            <a href="#register" class="trigger-btn badge badge-secondary signup-link" data-toggle="modal">Click to SignUp </a>
            <a href="login.php" class=" badge badge-secondary " >Back to Login </a>
        </h2>
    </div>

    <!-- Modal HTML -->
    <div id="register" class="modal fade">
        <div class="modal-dialog modal-login">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="avatar">
                        <img src="https://www.tutorialrepublic.com/examples/images/avatar.png" alt="Avatar">
                    </div>
                    <h4 class="modal-title">Main Admin SignUp</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" name="username" placeholder="Username" required="required" value="<?php echo $username; ?>">
                            <span class="invalid-feedback"><?php echo $username_err; ?></span>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control <?php echo (!empty($admin_password_err)) ? 'is-invalid' : ''; ?> " name="admin_password" placeholder="Main admin password" required="required" value="<?php echo $admin_password; ?>">
                            <span class=" invalid-feedback"><?php echo $admin_password_err; ?></span>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?> " name="password" placeholder="password" required="required" value="<?php echo $password; ?>">
                            <span class=" invalid-feedback"><?php echo $password_err; ?></span>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?> " name="confirm_password" placeholder="password" required="required" value="<?php echo $confirm_password; ?>">
                            <span class=" invalid-feedback"><?php echo $confirm_password_err; ?></span>
                        </div>
                        <div class="form-group">
                            <button type="reset" class="btn btn-secondary btn-lg btn-block " style="filter:grayscale(1.5)">Reset</button>
                            <button type="submit" class="btn btn-primary btn-lg btn-block login-btn">SignUp</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="login.php">Login now..</a>
                </div>
            </div>
        </div>
    </div>



    <?php echo "<script> document.querySelector('.signup-link').click();</script>"; ?>
</body>

</html>