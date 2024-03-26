<?php

//attendance.php

include ('header.php');

?>

<div class="container" style="margin-top:30px">
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-9">Attendance List</div>
        <div class="col-md-3" align="right">
          <!-- <button type="button" id="chart_button" class="btn btn-primary btn-sm">Chart</button> -->
          <button type="button" id="report_button" class="btn btn-danger btn-sm" data-bs-toggle='modal' data-bs-target='#ModalForOverAllReport'>Report</button>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <span id="message_operation"></span>
        <div class="form-group">
          <label for="filter_class">Filter by Class:</label>
          <select name="filter_class" id="filter_class" class="form-control">
            <option value="">All Classes</option>
            <?php
            echo load_class_list($connect); // Assuming load_class_list function is defined elsewhere
            ?>
          </select>
        </div>
        <table class="table table-striped table-bordered" id="attendance_table">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Rf id</th>
              <th>class</th>
              <th>Attendance data</th>
              <!-- <th>Attendance Date</th> -->

            </tr>
          </thead>
          <tbody id="attendanceTable">

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- modal for overall report -->
<div class="modal fade" id="ModalForOverAllReport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Attendance Reports Of Students</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="ModalForOverAllReportBody"></div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

</body>

</html>

<script type="text/javascript" src="../js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" href="../css/datepicker.css" />

<style>
  .datepicker {
    z-index: 1600 !important;
    /* has to be larger than 1050 */
  }
</style>

<div class="modal" id="reportModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Make Report</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <div class="form-group">
          <select name="class_id" id="class_id" class="form-control">
            <option value="">Select class</option>
            <?php
            echo load_class_list($connect);
            ?>
          </select>
          <span id="error_class_id" class="text-danger"></span>
        </div>
        <div class="form-group">
          <div class="input-daterange">
            <input type="text" name="from_date" id="from_date" class="form-control" placeholder="From Date" readonly />
            <span id="error_from_date" class="text-danger"></span>
            <br />
            <input type="text" name="to_date" id="to_date" class="form-control" placeholder="To Date" readonly />
            <span id="error_to_date" class="text-danger"></span>
          </div>
        </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" name="create_report" id="create_report" class="btn btn-success btn-sm">Create
          Report</button>
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<div class="modal" id="chartModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Create class Attandance Chart</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <div class="form-group">
          <select name="chart_class_id" id="chart_class_id" class="form-control">
            <option value="">Select class</option>
            <?php
            echo load_class_list($connect);
            ?>
          </select>
          <span id="error_chart_class_id" class="text-danger"></span>
        </div>
        <div class="form-group">
          <div class="input-daterange">
            <input type="text" name="attendance_date_time" id="attendance_date_time" class="form-control"
              placeholder="Select Date" readonly />
            <span id="error_attendance_date_time" class="text-danger"></span>
          </div>
        </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" name="create_chart" id="create_chart" class="btn btn-success btn-sm">Create Chart</button>
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    function recId() {
      let scanInitiated = false; // Flag to track if RFID scan has already been initiated
      fetch('http://localhost:3000/startScan') // Send a GET request to the server
        .then(response => {
          if (response.ok) {
            return response.text();
          } else {
            throw new Error('Failed to start RFID scan');
          }
        })
        .then(data => {
          console.log('RFID scan started: ' + data);

          let rf = data;
          // Fetch attendance data using the Fetch API
          fetch('./php/write.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              rfid: data,
              type: 'getStuData',
            }),
          })
            .then(response => response.json())
            .then(data => {
              console.log(data);
              // Check if data is not empty and has the expected structure
              if (Array.isArray(data) && data.length > 0 && data[0].attendance_data) {
                // Assuming the first element in the array contains the attendance data
                const attendanceData = data[0];

                // Parse the stringified JSON
                const parsedAttendanceData = JSON.parse(attendanceData.attendance_data);

                // Get today's date and time
                const today = new Date();
                const currentDate = today.getDate();
                const currentMonth = today.getMonth() + 1; // Months are zero-indexed
                const currentYear = today.getFullYear();
                var currentTime = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2);
                // console.log(time);
                // const currentTime = today.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });

                // Find the corresponding month and year in the parsed attendance data
                const monthYearKey = `${String(currentMonth).padStart(2, '0')}-${currentYear}`;
                const monthData = parsedAttendanceData.atData.find(entry => entry.yearMonth === monthYearKey);

                // Check if the month data is found
                if (monthData) {
                  // Find today's date in the days array and mark present
                  const todayIndex = currentDate - 1;
                  if (monthData.days && monthData.days[todayIndex] !== undefined) {
                    if (!monthData.days[todayIndex]) {//condition
                      monthData.days[todayIndex] = 1;
                      // Add current time to the times array
                      if (monthData.times && monthData.times[todayIndex] !== undefined) {
                        monthData.times[todayIndex] = currentTime;
                      }
                      console.log(currentTime);

                      // Print the modified attendance data
                      let callBody = JSON.stringify({
                        "rfid": rf,
                        "type": 'putStuData',
                        "data": parsedAttendanceData,
                      });

                      console.log(callBody);

                      fetch('./php/write.php', {
                        method: 'POST',
                        headers: {
                          'Content-Type': 'application/json',
                        },
                        body: callBody,
                      })
                        .then(response => response.json())
                        .then(data => {
                          // console.log(data);
                          var currentDate = new Date();
                          var day = currentDate.getDate();
                          var monthIndex = currentDate.getMonth();
                          var hours = currentDate.getHours();
                          var minutes = currentDate.getMinutes();
                          var monthNames = ["January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December"
                          ];
                          var formattedDateTime = day + " " + monthNames[monthIndex] + " " + (hours < 10 ? '0' : '') + hours + ":" + (minutes < 10 ? '0' : '') + minutes;

                          // Print the formatted date and time
                          // console.log(formattedDateTime);
                          const parent = Array.from(document.getElementById("attendanceTable").children);
                          console.log(parent);
                          parent.forEach((ele) => {
                            if (Array.from(ele.children)[1].innerText == rf) {
                              Array.from(ele.children)[3].innerHTML = `<label class="badge badge-success">Present</label>`;
                            }
                          });
                          iziToast.success({
                            title: data.stData[0].name,
                            message: formattedDateTime,
                            timeout: 3000,
                            position: 'bottomLeft'
                          });
                        })
                        .catch(error => {
                          console.error('Error:', error);
                        });
                    } else {
                      iziToast.warning({
                        title: "Already marked",
                        message: "",
                        timeout: 3000,
                        position: 'bottomLeft'
                      });
                    }
                  }

                }
              }
            })
            .catch(error => {
              console.error('Error:', error);
            });

          recId();
          scanInitiated = false; // Reset the flag to allow subsequent scans
        })
        .catch(error => {
          console.error('Error:', error.message);
        });
    }

    recId();
    onstart();
  });


  function callData() {
    var dataTable = null;
    var dataTable = $('#attendance_table').DataTable({
      "processing": true,
      "serverSide": true,
      "order": [],
      "ajax": {
        url: "attendance_action.php",
        type: "POST",
        data: function (data) {
          data.action = 'fetch';
          data.filter_class = $('#filter_class').val(); // Pass the selected class for filtering
          console.log('Selected Class:', data.filter_class); // Log selected class for debugging
        }
      },
      "columnDefs": [
        {
          "targets": "_all",
          "defaultContent": "-"
        },
      ],
    });
    $('#filter_class').change(function () {
      dataTable.ajax.reload(); // Reload the DataTable when the class filter changes
    });
  }

  document.getElementById("report_button").addEventListener('click', (event) => {
    console.log(Array.from(document.getElementById("attendanceTable").children))
    let values = [];
    let colorValues = [];
    let nameValues = [];
    let graphNameValues = [];

    Array.from(document.getElementById("attendanceTable").children).forEach((ele) => {
      // console.log(JSON.parse(ele.children[3].children[0].getAttribute("data-att")));
      // console.log((ele.children[3].children[0].getAttribute("end-id")));
      let [yyyy, mm, dd] = ele.children[3].children[0].getAttribute("start-id").split("-");
      const repUserDate = `${mm}-${dd}-${yyyy}`;
      let calPer = calculatePer(JSON.parse(ele.children[3].children[0].getAttribute("data-att")).atData, repUserDate);
      values.push(calPer);
      colorValues.push(getColor(calPer));
      graphNameValues.push(calPer + "%");
      nameValues.push(ele.children[3].children[0].getAttribute("stname"));
    })
    console.log(values);

    const bodyForReport = document.getElementById("ModalForOverAllReportBody");
    bodyForReport.innerHTML = `<canvas id="barChart"></canvas>`;
    var ctx = document.getElementById('barChart').getContext('2d');
    var myBarChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: nameValues,
        datasets: [{
          label: '',
          data: values,
          backgroundColor: colorValues,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        legend: {
          display: false
        },
        title: {
          display: true,
          text: 'Attendance Report'
        },
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true,
              max: 100
            }
          }]
        },
        tooltips: {
          enabled: false // Disable tooltips for all bars
        }
      }
    });
    myBarChart.update();
  });

  // $(document).ready(function onstart() {
  function onstart() {
    callData();
    $('.input-daterange').datepicker({
      todayBtn: "linked",
      format: "yyyy-mm-dd",
      autoclose: true,
      container: '#formModal modal-body'
    });

    // $(document).on('click', '#report_button', function () {
    //   $('#reportModal').modal('show');
    // });

    $('#create_report').click(function () {
      var class_id = $('#class_id').val();
      var from_date = $('#from_date').val();
      var to_date = $('#to_date').val();
      var error = 0;

      if (class_id == '') {
        $('#error_class_id').text('class is Required');
        error++;
      }
      else {
        $('#error_class_id').text('');
      }

      if (from_date == '') {
        $('#error_from_date').text('From Date is Required');
        error++;
      }
      else {
        $('#error_from_date').text('');
      }

      if (to_date == '') {
        $('#error_to_date').text("To Date is Required");
        error++;
      }
      else {
        $('#error_to_date').text('');
      }

      if (error == 0) {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#formModal').modal('hide');
        window.open("report.php?action=attendance_report&class_id=" + class_id + "&from_date=" + from_date + "&to_date=" + to_date);
      }

    });
    /*
      $('#chart_button').click(function(){
        $('#chart_class_id').val('');
        $('#attendance_date_time').val('');
        $('#chartModal').modal('show');
      });
    
      $('#create_chart').click(function(){
        var grade_id = $('#chart_class_id').val();
        var attendance_date = $('#attendance_date_time').val();
        var error = 0;
        if(grade_id == '')
        {
          $('#error_chart_class_id').text('class is Required');
          error++;
        }
        else
        {
          $('#error_chart_class_id').text('');
        }
        if(attendance_date == '')
        {
          $('#error_attendance_date_time').text('Date is Required');
          $error++;
        }
        else
        {
          $('#error_attendance_date_time').text('');
        }
    
        if(error == 0)
        {
          $('#attendance_date_time').val('');
          $('#chart_class_id').val('');
          $('#chartModal').modal('show');
          window.open("chart1.php?action=attendance_report&class_id="+class_id+"&date="+attendance_date_time);
        }
    
      });
    */

  };

  function calculatePer(db, dateBefore) {
  let total = 0;
  let countSuns = 0;
  let detector;
  db.forEach((ele) => {
    let moye = ele.yearMonth;
    ele.days.forEach((at, i) => {
      if (isDateBeforeToday(`${i + 1}-${moye}`) && at == 1 && isDateAfter(dateBefore, `${i + 1}-${moye}`)) {
        total += 1;
        if (detector != ele.yearMonth) {
          countSuns += countSundays(ele.yearMonth);
          detector = ele.yearMonth;
        }
      }
    });
  });
  countSuns-=remainingSundaysInMonth();
  total -= countSuns;

  function remainingSundaysInMonth() {
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const currentDay = today.getDate();
    let count = 0;

    // Check remaining days in the month starting from today
    for (let day = currentDay; day <= daysInMonth; day++) {
      const date = new Date(year, month, day);
      if (date.getDay() === 0) count++; // Sunday is represented by 0 in JavaScript
    }

    return count;
  }

  function countSundays(inputDate) {
    const [month, year] = inputDate.split("-").map(Number);
    let count = 0;
    for (let day = 1; day <= 31; day++) {
      const date = new Date(year, month - 1, day); // Months are 0-based in JavaScript
      if (date.getMonth() !== month - 1) break; // Check if we've moved to the next month
      if (date.getDay() === 0) count++; // Sunday is represented by 0 in JavaScript
    }
    return count;
  }

  function isDateBeforeToday(userDateString) {
    let [dd, mm, yyyy] = userDateString.split("-");
    const userDate = new Date(`${mm}/${dd}/${yyyy}`);
    const today = new Date();
    return userDate < today;
  }

  function isDateAfter(fixedDate, checkdate) {
    let [dd, mm, yyyy] = fixedDate.split("-");
    const userDate1 = new Date(`${mm}/${dd}/${yyyy}`);
    let [dd1, mm1, yyyy1] = checkdate.split("-");
    const userDate2 = new Date(`${mm1}/${dd1}/${yyyy1}`);
    return userDate1 < userDate2;
  }

  function getTotalDaysExcludingSundays(startDate) {
    let [dd, mm, yyyy] = startDate.split("-");
    const start = new Date(`${mm}/${dd}/${yyyy}`);
    const today = new Date();
    const timeDifference = today - start;
    const totalDays = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
    const numberOfSundays = Math.floor((totalDays + start.getDay()) / 7);
    const result = totalDays - numberOfSundays;
    console.log(result);
    return result;
  }

  function calculatePercentage(score, totalMarks) {
    if (
      typeof score !== "number" ||
      typeof totalMarks !== "number" ||
      totalMarks <= 0
    ) {
      return "Invalid input. Please provide valid numeric values for score and totalMarks.";
    }
    const percentage = (score / totalMarks) * 100;
    return percentage.toFixed(2);
  }

  return Math.ceil(calculatePercentage(total, getTotalDaysExcludingSundays(dateBefore)));
}

function getColor(number) {
    var green = [0, 255, 0]; // RGB values for green
    var red = [255, 0, 0];   // RGB values for red

    var ratio = (number - 1) / 99; // Adjusting the range to start from 0

    var color = [
        Math.round((1 - ratio) * red[0] + ratio * green[0]), // Red component
        Math.round((1 - ratio) * red[1] + ratio * green[1]), // Green component
        Math.round((1 - ratio) * red[2] + ratio * green[2])  // Blue component
    ];

    var cssColor = 'rgb(' + color.join(',') + ')';

    return number >= 75 ? 'rgb(0,255,0)' : cssColor;
}
</script>