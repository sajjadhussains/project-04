
<?php 
    session_start();
    //authorization
    if(!$_SESSION['username']){
      session_destroy();
      header('Location: ../index.php');
    }
    else if($_SESSION['username'] && $_SESSION['role'] != 'admin'){
      session_destroy();
      header('Location: ../unauthorised_user.php');
    }
?>

<!DOCTYPE html>
  <html lang="en">
  <head>
    <title>Admin control panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../include/link.php' ?>
  </head>
  <body>
    <section id="container" class="">
      <?php include '../include/navbar.php' ?>
      <?php include '../include/sidebar.php' ?>
      <section id="main-content">
        <section class="wrapper">
          <div class="row">
            <div class="col-lg-12">
              <h3 class="page-header"><i class="fa fa-laptop"></i>Session Creation</h3>
              <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="dashboard.php">Home</a></li>
                <li><i class="fa fa-laptop"></i>Session</li>
              </ol>
            </div>
          </div>
            <div class="col-md-6 portlets">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <div class="pull-left">Enter Routine Information</div>
                  <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                  <div class="padd">
                    <div class="form quick-post">
                      <form class="form-horizontal" method="post" action="" >
                        <div class="form-group">
                          <label class="control-label col-lg-2" for="date">pick date</label>
                            <div class="col-lg-10">
                                <input type="date" name = "date" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                          <label class="control-label col-lg-2" for="time">Start time</label>
                            <div class="col-lg-10">
                                <select class="form-control" name="time" id="time">
                                    <option>10</option>
                                    <option>2</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                          <label class="control-label col-lg-2" for="department">Department</label>
                            <div class="col-lg-10">
                                <select class="form-control" name="department" id="department">
                                    <option>CSE</option>
                                    <option>ECE</option>
                                    <option>EEE</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                          <label class="control-label col-lg-2" for="semester">Semester</label>
                            <div class="col-lg-10">
                                <select class="form-control" name="semester" id="semester">
                                    <option>L-1-S-1</option>
                                    <option>L-1-S-2</option>
                                    <option>L-2-S1</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                          <label class="control-label col-lg-2" for="Batch">Batch</label>
                            <div class="col-lg-10">
                                <input type="text" name = "batch" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-9">
                            <button type="submit" name = "submit" class="btn btn-primary">Create</button>
                            </div>
                        </div>
                    </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </section>
    </section>
    <?php include '../include/script.php' ?>
    </body>
  </html>

  
  <?php 
    include '../include/connection.php';
    if(isset($_POST['submit']))
    {
        //recvd data from input/control
        $date = $_POST['date'];
        $time = $_POST['time'];
        $department =$_POST['department'];
        $semester = $_POST['semester'];
        $batch = $_POST['batch'];
        
    }

    // Make a query based on department and semester
    $sql = "SELECT * FROM all_courses WHERE program_name = :department AND semester = :semester";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':semester', $semester);
    $stmt->execute();

    // Process the query results
    while ($row = $stmt->fetch()) {
        $courses[] = $row;
    }
    $extra_holiday = [
        ['date' => '2024-01-25'],  // Example holiday 1
        ['date' => '2024-02-10'],  // Example holiday 2
        // ... more holidays
    ];
    $examSchedule = generateExamRoutine($date, $extra_holiday, $courses);
    
    // Display the examSchedule table
    ?>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Course Name</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($examSchedule as $exam) { ?>
            <tr>
                <td><?php echo $exam['date']; ?></td>
                <td><?php echo $exam['day']; ?></td>
                <td><?php echo $exam['course_name']; ?></td>
                <td><?php echo $exam['time']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>


<?php
function generateExamRoutine($date, $extra_holiday, $courses) {
    $examSchedule = array(); // Use array() for PHP 5.6 compatibility

    $given_date = $date;

    for ($i = 0; $i < count($courses); $i++) {
        $duration = $courses[$i]['credit'] === 3 ? "10am-1pm" : "10am-12am";
        $dayOfTheDate = getDayOfTheDate($given_date);

        if ($dayOfTheDate !== 'friday' && $dayOfTheDate !== 'saturday' && !holiday_check($given_date, $extra_holiday)) {
            $examSchedule[] = array( // Use array() for PHP 5.6 compatibility
                'date' => $given_date,
                'day' => $dayOfTheDate,
                'course_name' => $courses[$i]['name'],
                'time' => $duration
            );
        } else {
            while ($dayOfTheDate === 'friday' || $dayOfTheDate === 'saturday' || holiday_check($given_date, $extra_holiday)) {
                $given_date = getNextDate($given_date, 1);
                $dayOfTheDate = getDayOfTheDate($given_date);
            }
            $examSchedule[] = array( // Use array() for PHP 5.6 compatibility
                'date' => $given_date,
                'day' => $dayOfTheDate,
                'course_name' => $courses[$i]['name'],
                'time' => $duration
            );
        }

        $gap = $courses[$i]['credit'] === 3 ? 3 : 2;
        $given_date = getNextDate($given_date, $gap);
    }

    return $examSchedule;
}

function getDayOfTheDate($start_date) {
    $date = new DateTime($start_date);
    $dayNames = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'); // Use array()
    $dayIndex = $date->format('w'); // 0 for Sunday, 6 for Saturday
    $dayName = $dayNames[$dayIndex];
    return strtolower($dayName);
}

function getNextDate($current_date, $increase_day) {
    $inputDate = $current_date;
    $dateObject = new DateTime($inputDate);
    $dateObject->modify("+$increase_day day");
    $year = $dateObject->format('Y');
    $month = $dateObject->format('m');
    $day = $dateObject->format('d');
    return "$year-$month-$day";
}
?>
 