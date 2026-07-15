<?php
session_start();
error_reporting(0);
include('includes/config.php');

require './vendor/phpmailer/phpmailer/src/Exception.php';
require './vendor/phpmailer/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['emplogin'])) {
    header('location:index.php');
    exit;
}

if (isset($_POST['apply'])) {
    $empid = $_SESSION['eid'];
    $name = trim($_POST['name']);
    $leavetype = trim($_POST['leavetype']);
    $fromdate = trim($_POST['fromdate']);
    $todate = trim($_POST['todate']);
    $description = trim($_POST['description']);

    // Validate date input
    if (strtotime($fromdate) > strtotime($todate)) {
        echo "<script>alert('To Date should be greater than From Date');</script>";
        exit;
    }

    $from = new DateTime($fromdate);
    $to = new DateTime($todate);
    $interval = $from->diff($to);
    $leave_days = $interval->days + 1;

    // Check existing leave balance
    $check = $dbh->prepare("SELECT leave_count FROM leavecount WHERE EmpId = :empid");
    $check->bindParam(':empid', $empid, PDO::PARAM_INT);
    $check->execute();
    $result = $check->fetch(PDO::FETCH_ASSOC);

    // $leave_count = ($result) ? max(0, $result['leave_count'] - $leave_days) : max(0, 24 - $leave_days);
    

    $status = 0;
    $isread = 0;

    // Insert leave application
    $sql = "INSERT INTO tblleaves (name, LeaveType, FromDate, ToDate, Description, Status, IsRead, EmpId) 
            VALUES (:name, :leavetype, :fromdate, :todate, :description, :status, :isread, :empid)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':name', $name, PDO::PARAM_STR);
    $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
    $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
    $query->bindParam(':todate', $todate, PDO::PARAM_STR);
    $query->bindParam(':description', $description, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_INT);
    $query->bindParam(':isread', $isread, PDO::PARAM_INT);
    $query->bindParam(':empid', $empid, PDO::PARAM_INT);

    if ($query->execute()) {
        // Update leave balance
        // if ($result) {
        //     $leaveSQL = "UPDATE leavecount SET leave_count = :leave_count WHERE EmpId = :empid";
        // } else {
        //     $leaveSQL = "INSERT INTO leavecount (EmpId, leave_count) VALUES (:empid, :leave_count)";
        // }
        if (!$result) { // If no existing record, insert new leave balance
            $leave_count = max(0, 24); // Assuming 24 is the max allowed leaves
            $leaveSQL = "INSERT INTO leavecount (EmpId, leave_count) VALUES (:empid, :leave_count)";
            
            $query2 = $dbh->prepare($leaveSQL);
            $query2->bindParam(':empid', $empid, PDO::PARAM_INT);
            $query2->bindParam(':leave_count', $leave_count, PDO::PARAM_INT);
            $query2->execute();
        }
        

        // Send email notification
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'symposiumevent2025@gmail.com';
            $mail->Password = 'pzojxrpdejoltxay';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('loghupoonga@gmail.com', 'Leave Management System');
            $mail->addAddress('loghupoonga@example.com');

            $mail->isHTML(true);
            $mail->Subject = "Leave Application Submitted";
            $mail->Body = "<b>Employee Id:</b> $empid<br>
                           <b>Name:</b> $name<br>
                           <b>Leave Type:</b> $leavetype<br>
                           <b>From Date:</b> $fromdate<br>
                           <b>To Date:</b> $todate<br>
                           <b>Description:</b> $description<br>
                           <b>Balance Leave:</b> $leave_count";

            $mail->send();
            echo "<script>
            alert('Leave applied successfully. Email sent to management.');
            window.location.href = 'apply-leave.php';
          </script>";
    
        } catch (Exception $e) {
            echo "<script>alert('Leave applied but email could not be sent. Error: " . $mail->ErrorInfo . "');</script>";
        }
    } else {
        echo "<script>alert('Something went wrong. Please try again.');</script>";
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Employee | Apply Leave</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta charset="UTF-8">
    <meta name="description" content="Responsive Admin Dashboard Template" />
    <meta name="keywords" content="admin,dashboard" />
    <meta name="author" content="Steelcoders" />

    <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css" />
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
    <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
    <style>
        .errorWrap {
            padding: 10px;
            background: #fff;
            border-left: 4px solid #dd3d36;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
        }

        .succWrap {
            padding: 10px;
            background: #fff;
            border-left: 4px solid #5cb85c;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
        }
    </style>
</head>

<body>
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <main class="mn-inner">
        <div class="row">
            <div class="col s12">
                <div class="page-title">Apply for Leave</div>
            </div>
            <div class="col s12 m12 l8">
                <div class="card">
                    <div class="card-content">
                        <form id="example-form" method="post" name="addemp">
                            <h3>Apply for Leave</h3>

                            <?php
                            $empid = $_SESSION['eid'];

                            $user_id_query = "SELECT EmpId FROM tblemployees WHERE id = :empid";
                            $query = $dbh->prepare($user_id_query);
                            $query->bindParam(':empid', $empid, PDO::PARAM_INT);
                            $query->execute();
                            $user_list = $query->fetch(PDO::FETCH_ASSOC);
                            ?>

                            <p>Employee ID : <?php echo htmlentities($user_list['EmpId']); ?></p>

                            <div class="row">
                                <div class="col m12">
                                    <?php if ($error) { ?>
                                        <div class="errorWrap"><strong>ERROR</strong>: <?php echo htmlentities($error); ?> </div>
                                    <?php } elseif ($msg) { ?>
                                        <div class="succWrap"><strong>SUCCESS</strong>: <?php echo htmlentities($msg); ?> </div>
                                    <?php } ?>

                                    <input name="emp_id" type="hidden" value="<?php echo htmlentities($empid); ?>">

                                    <div class="input-field col m6 s12">
                                        <label for="name">Name</label>
                                        <input id="name" name="name" type="text" required>
                                    </div>

                                    <div class="input-field col m6 s12">
                                        <select name="leavetype" required>
                                            <option value="">Select leave type...</option>
                                            <?php
                                            $sql = "SELECT LeaveType FROM tblleavetype";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $result) { ?>
                                                    <option value="<?php echo htmlentities($result->LeaveType); ?>">
                                                        <?php echo htmlentities($result->LeaveType); ?>
                                                    </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>

                                    <div class="input-field col m6 s12">
                                        <label for="fromdate">From Date</label><br>
                                        <input id="fromdate" name="fromdate" type="date" required>
                                    </div>
                                    <div class="input-field col m6 s12">
                                        <label for="todate">To Date</label><br>
                                        <input id="todate" name="todate" type="date" required>
                                    </div>

                                    <div class="input-field col m12 s12">
                                        <label for="description">Description</label>
                                        <textarea id="description" name="description" class="materialize-textarea" length="500" required></textarea>
                                    </div>

                                    <button type="submit" name="apply" class="waves-effect waves-light btn indigo m-b-xs">Apply</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="left-sidebar-hover"></div>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="assets/plugins/materialize/js/materialize.min.js"></script>
    <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
    <script src="assets/js/alpha.min.js"></script>
</body>

</html>