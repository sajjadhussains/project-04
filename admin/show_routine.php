<?php
session_start();
//authorization
if (!$_SESSION['username']) {
    session_destroy();
    header('Location: ../index.php');
} else if ($_SESSION['username'] && $_SESSION['role'] != 'admin') {
    session_destroy();
    header('Location: ../unauthorised_user.php');
}
include '../include/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Generated Exam Routine by Batch</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body class="container" style="height: 100vh;">

    <div class="text-center">
        <h2 class="my-4">Generated Exam Routine</h2>

        <?php
        if (isset($_GET['batch']) && isset($_GET['level']) && isset($_GET['department'])) {
            $batch = $_GET['batch'];
            $level = $_GET['level'];
            $department = $_GET['department'];

            // Fetch and display routine based on the batch and level
            $query = "SELECT * FROM `examUsers` WHERE batch = '$batch' AND level = '$level' AND department = '$department'";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                echo '<table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Course Name</th>
                                <th>Time</th>
                                <th>Level</th>
                                <th>Department</th>
                                <th>Batch</th>
                            </tr>
                        </thead>
                        <tbody>';
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td>' . $row['date'] . '</td>';
                    echo '<td>' . $row['day'] . '</td>';
                    echo '<td>' . $row['course_name'] . '</td>';
                    echo '<td>' . $row['time'] . '</td>';
                    echo '<td>' . $row['level'] . '</td>';
                    echo '<td>' . $row['department'] . '</td>';
                    echo '<td>' . $row['batch'] . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-danger">No data found for the batch: ' . $batch . ' and level: ' . $level . '</p>';
            }
        } else {
            echo '<p class="text-danger">Invalid batch or level parameters</p>';
        }
        ?>

    </div>

    
</body>
</html>
