<?php


session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
} else {

    $empid = $_SESSION['eid'];
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <!-- Title -->

        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta charset="UTF-8">
        <meta name="description" content="Responsive Admin Dashboard Template" />
        <meta name="keywords" content="admin,dashboard" />
        <meta name="author" content="
        " />

        <!-- Styles -->
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css" />
        <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/metrojs/MetroJs.min.css" rel="stylesheet">
        <link href="assets/plugins/weather-icons-master/css/weather-icons.min.css" rel="stylesheet">


        <!-- Theme Styles -->
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />

    </head>

    <body>

        <?php include('includes/header.php'); ?>

        <?php include('includes/sidebar.php'); ?>
        <main class="mn-inner">
            <div class="">
                <div class="row no-m-t no-m-b">



                    <h3>WELCOME TO EMPLOYEE LEAVE MANAGEMENT SYSTEM</h3>


                    <!-- <div class="col s12 m12 l4">
                        <div class="card stats-card">
                            <div class="card-content">

                                <span class="card-title">Totle Leaves This Year</span>
                                <span class="stats-counter">
                                    <?php
                                    $sql = "SELECT * from tblleaves where empid=$empid";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $empcount = $query->rowCount();
                                    ?>

                                    <span class="counter"><?php echo htmlentities($empcount); ?></span></span>
                            </div>
                            <div class="progress stats-card-progress">
                                <div class="determinate" style="width: 70%"></div>
                            </div>
                        </div>
                    </div> -->


                    <div class="col s12 m12 l4">
                        <div class="card stats-card">
                            <div class="card-content">

                                <span class="card-title">Balance Leaves This Year</span>
                                <span class="stats-counter">
                                    <?php
                                    $sql = "SELECT leave_count FROM leavecount WHERE empid = :empid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':empid', $empid, PDO::PARAM_INT);
                                    $query->execute();
                                    $results = $query->fetch(PDO::FETCH_OBJ); // Fetch a single object instead of fetchAll()

                                    // Check if results exist before displaying
                                    $leave_count = isset($results->leave_count) ? $results->leave_count : 24;
                                    ?>

                                    <span class="counter"><?php echo htmlentities($leave_count); ?></span>

                            </div>
                            <div class="progress stats-card-progress">
                                <div class="determinate" style="width: 70%"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Javascripts -->
            <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
            <script src="assets/plugins/materialize/js/materialize.min.js"></script>
            <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
            <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
            <script src="assets/plugins/waypoints/jquery.waypoints.min.js"></script>
            <script src="assets/plugins/counter-up-master/jquery.counterup.min.js"></script>
            <script src="assets/plugins/jquery-sparkline/jquery.sparkline.min.js"></script>
            <script src="assets/plugins/chart.js/chart.min.js"></script>
            <script src="assets/plugins/flot/jquery.flot.min.js"></script>
            <script src="assets/plugins/flot/jquery.flot.time.min.js"></script>
            <script src="assets/plugins/flot/jquery.flot.symbol.min.js"></script>
            <script src="assets/plugins/flot/jquery.flot.resize.min.js"></script>
            <script src="assets/plugins/flot/jquery.flot.tooltip.min.js"></script>
            <script src="assets/plugins/curvedlines/curvedLines.js"></script>
            <script src="assets/plugins/peity/jquery.peity.min.js"></script>
            <script src="assets/js/alpha.min.js"></script>
            <script src="assets/js/pages/dashboard.js"></script>

    </body>

    </html>
<?php } ?>