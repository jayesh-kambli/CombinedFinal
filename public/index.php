<?php

//index.php

include ('header.php');

?>
<style>
  .list-group-item {
    min-width: 150rem !important;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }

  th, td {
    padding: 10px;
    border: 1px solid #ddd;
    position: relative;
    cursor: pointer;
  }

  th {
    background-color: #f2f2f2;
  }

  .present {
    background-color: #4CAF50; /* Green */
    color: white;
  }

  .absent {
    background-color: #f44336; /* Red */
    color: white;
  }

  .late {
    border: 2px solid red;
  }

  .yellow {
    background-color: yellow;
  }

  .sunday {
    background-color: #808080; /* Grey */
    color: white;
  }

  .calendar-title {
    font-size: 18px;
    margin-bottom: 10px;
  }

  .tooltip {
    visibility: hidden;
    width: 120px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 0.3s;
  }

  td:hover .tooltip {
    visibility: visible;
    opacity: 1;
  }

  .list-group-item {
    min-width: 15rem !important;
  }
</style>
<div class="container" style="margin-top:30px">
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-9">Overall Student Attendance Status</div>
        <div class="col-md-3" align="right">

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
        <table class="table table-striped table-bordered" id="student_table">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>rf id</th>
              <th>class</th>
              <th>Teacher</th>
              <th>Report</th>
              <!-- <th>Attendance Percentage</th> -->
            </tr>
          </thead>
          <tbody>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- model for reports -->
<div class="modal fade" id="ModalForReports" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Attendance Reports Of Student</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="ModalForReportsBody"></div>
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

<div class="modal" id="formModal">
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
          <select name="report_action" id="report_action" class="form-control">
            <option value="pdf_report">PDF Report</option>
            <option value="chart_report">Chart Report</option>
          </select>
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
        <input type="hidden" name="student_id" id="student_id" />
        <button type="button" name="create_report" id="create_report" class="btn btn-success btn-sm">Create
          Report</button>
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<script>
  $(document).ready(function () {

    var dataTable = $('#student_table').DataTable({
      "processing": true,
      "serverSide": true,
      "order": [],
      "ajax": {
        url: "attendance_action.php",
        type: "POST",
        data: function (d) {
          d.action = 'index_fetch';
          d.filter_class = $('#filter_class').val(); // Include the selected class filter
        }
      },
      "columnDefs": [
        {
          "targets": "_all",
          "defaultContent": "-"
        },
      ],
    });



    document.getElementById("student_table").addEventListener('click', (event) => {
      if (event.target.tagName == "BUTTONA") {
        stuAtData = JSON.parse(JSON.parse(event.target.getAttribute("data-attendance")));
        document.getElementById("ModalForReportsBody").innerHTML = `Name: ${event.target.getAttribute("data-student_id")}`;
        generateAttendanceCalendar(stuAtData, 'ModalForReportsBody')
      }
    });

    $('#filter_class').change(function () {
      dataTable.ajax.reload(); // Reload the DataTable when the class filter changes
    });

    $('.input-daterange').datepicker({
      todayBtn: "linked",
      format: 'yyyy-mm-dd',
      autoclose: true,
      container: '#formModal modal-body'
    });

    $(document).on('click', '.report_button', function () {
      var student_id = $(this).data('student_id');
      $('#student_id').val(student_id);
      $('#formModal').modal('show');
    });

    $('#create_report').click(function () {
      var student_id = $('#student_id').val();
      var from_date = $('#from_date').val();
      var to_date = $('#to_date').val();
      var error = 0;
      var action = $('#report_action').val();
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
        if (action == 'pdf_report') {
          window.open("report.php?action=student_report&student_id=" + student_id + "&from_date=" + from_date + "&to_date=" + to_date);
        }
        if (action == 'chart_report') {
          location.href = "chart.php?action=student_chart&student_id=" + student_id + "&from_date=" + from_date + "&to_date=" + to_date;
        }
      }

    });

  });

  function generateAttendanceCalendar(data, parentElement) {
    for (const calendarData of data.atData) {
      const [mm, yyyy] = calendarData.yearMonth.split("-");
      let date = new Date(`${yyyy}-${mm}-01`);
      let currentMonth = date.getMonth();

      const table = document.createElement('table');
      const titleRow = document.createElement('tr');
      const titleCell = document.createElement('th');
      titleCell.setAttribute('colspan', '7');
      titleCell.classList.add('calendar-title');
      titleCell.textContent = `${getMonthName(date.getMonth())} ${yyyy}`;
      titleRow.appendChild(titleCell);
      table.appendChild(titleRow);

      const headerRow = table.insertRow();
      const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

      // Create table header with days of the week
      for (const day of daysOfWeek) {
        const th = document.createElement('th');
        th.textContent = day;
        headerRow.appendChild(th);
      }

      // Create table rows and cells for each day
      while (date.getMonth() === currentMonth) {
        const row = table.insertRow();

        for (let i = 0; i < 7; i++) {
          const cell = row.insertCell();
          const tooltip = document.createElement('div');
          tooltip.classList.add('tooltip');

          if (date.getMonth() !== currentMonth) {
            // If the month changes, stop creating cells
            cell.innerHTML = '&nbsp;';
            continue;
          }

          if (date.getDay() === i) {
            const dayOfMonth = date.getDate();
            cell.textContent = dayOfMonth;

            // Check attendance for the day and apply appropriate class
            const attendance = calendarData.days[dayOfMonth - 1];
            if (attendance === 1) {
              cell.classList.add('present');
              tooltip.textContent = calendarData.times[dayOfMonth - 1] || '';
            } else if (attendance === 0) {
              cell.classList.add('absent');
              tooltip.textContent = 'Absent';
            } else if (attendance === 2) {
              cell.classList.add('yellow');
              tooltip.textContent = calendarData.times[dayOfMonth - 1] || '';
            }

            // Check if it's Sunday and apply the 'sunday' class
            if (i === 0 && attendance !== 2) {
              cell.classList.add('sunday');
            }

            // Check if the time is late and apply the 'late' class (excluding Sundays)
            const time = calendarData.times[dayOfMonth - 1];
            if (i !== 0 && time && time > "09:00") {
              cell.classList.add('late');
            }

            // Append the tooltip to the cell
            cell.appendChild(tooltip);

            date.setDate(dayOfMonth + 1);
          }
        }
      }

      document.getElementById(parentElement).appendChild(table);
    }
  }

  // Get month name from the month index
  function getMonthName(monthIndex) {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return months[monthIndex];
  }

  function calculatePer(db, dateBefore) {
    let total = 0;
    db.forEach((ele) => {
      let moye = ele.yearMonth;
      ele.days.forEach((at, i) => {
        if (
          isDateBeforeToday(`${i + 1}-${moye}`) &&
          at == 1 &&
          isDateAfter(dateBefore, `${i + 1}-${moye}`)
        )
          total += 1;
      });
    });

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
</script>