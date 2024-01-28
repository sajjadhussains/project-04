
<?php 
    ob_start();
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
    include '../include/connection.php';
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
                  <div class="pull-left">Enter Session Information</div>
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
                                    <option>L-2-S-1</option>
                                    <option>L-2-S-2</option>
                                    <option>L-3-S-1</option>
                                    <option>L-3-S-2</option>
                                    <option>L-4-S-1</option>
                                    <option>L-4-S-2</option>
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
                           <input type="submit" class="btn btn-primary" value="submit" name="submit">
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
    <script>
      $(document).ready(function(){
        $('form').submit(function(){
          alert("Session has been created");
        });
      });
    </script>
    <?php include '../include/script.php' ?>
    </body>
  </html>

  <?php
if (isset($_POST['submit'])) {
  // Get form data
  $date = $_POST['date'];
  $time = $_POST['time'];
  $department = $_POST['department'];
  $level = $_POST['semester'];
  $batch = $_POST['batch'];

  // Define extra holidays
  $extra_holiday = array(
      array('id' => 1, 'date' => '2024-01-15'),
      array('id' => 2, 'date' => '2023-08-23'),
      array('id' => 3, 'date' => '2023-09-03')
  );

  // Fetch courses from the database based on department and level
  $courseResult = mysqli_query($conn, "SELECT * FROM `all_courses` WHERE program_name='$department' AND semester='$level'");
  $courses = array();

  while ($row = mysqli_fetch_assoc($courseResult)) {
      $courses[] = $row;
  }

  // Generate exam routine
  $examSchedule = generateExamRoutine($date, $extra_holiday, $courses, $time, $level, $department);

  // Store the generated schedule in the database
  foreach ($examSchedule as $schedule) {
      $course_name = $schedule['course_name'];
      $schedule_date = $schedule['date'];
      $schedule_day = $schedule['day'];
      $schedule_time = $schedule['time'];
      $schedule_level = $schedule['level'];
      $schedule_department = $schedule['department'];

      // Insert the schedule into the database
      $query = "INSERT INTO `examUsers` (course_name, date, day, time, level, department, batch) 
      VALUES ('$course_name', '$schedule_date', '$schedule_day', '$schedule_time', '$schedule_level', '$schedule_department', '$batch')";
mysqli_query($conn, $query);
  }

  // Store the generated schedule in session for later display
  $_SESSION['generated_schedule'] = $examSchedule;

  // Redirect to another page to display the routine
  header("Location: show_routine.php?batch=$batch&level=$level&department=$department");
  exit();
}

// Function to generate exam routine
function generateExamRoutine($date, $extra_holiday, $courses, $time, $level, $department) {
  $examSchedule = array();
  $given_date = $date;

  foreach ($courses as $course) {
      $duration = ($time == "10") ? ($course['credit'] == 3 ? "10am-1pm" : "10am-12am") : ($course['credit'] == 3 ? "2pm-5pm" : "2pm-4pm");
      $dayOfTheDate = getDayOfTheDate($given_date);

      if ($dayOfTheDate != 'Friday' && $dayOfTheDate != 'Saturday' && !holiday_check($given_date, $extra_holiday)) {
          $examSchedule[] = array(
              'date' => $given_date,
              'day' => $dayOfTheDate,
              'course_name' => $course['course_name'],
              'time' => $duration,
              'level' => $level,
              'department' => $department
          );
      } else {
          // Skip to the next working day
          while ($dayOfTheDate == 'Friday' || $dayOfTheDate == 'Saturday' || holiday_check($given_date, $extra_holiday)) {
              $given_date = getNextDate($given_date, 1);
              $dayOfTheDate = getDayOfTheDate($given_date);
          }

          $examSchedule[] = array(
              'date' => $given_date,
              'day' => $dayOfTheDate,
              'course_name' => $course['course_name'],
              'time' => $duration,
              'level' => $level,
              'department' => $department
          );
      }

      $gap = ($course['credit'] == 3) ? 3 : 2;
      $given_date = getNextDate($given_date, $gap);
  }

  return $examSchedule;
}

// Function to get the day of the date
function getDayOfTheDate($start_date) {
  $date = new DateTime($start_date);
  return $date->format('l');
}

// Function to get the next date
function getNextDate($current_date, $increase_day) {
  $dateObject = new DateTime($current_date);
  $dateObject->add(new DateInterval("P{$increase_day}D"));
  return $dateObject->format('Y-m-d');
}

// Function to check if a date is a holiday
function holiday_check($date, $extra_holiday) {
  foreach ($extra_holiday as $holiday) {
      if ($date == $holiday['date']) {
          return true;
      }
  }
  return false;
}

?>

