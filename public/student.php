<?php
//student.php
include('header.php');

?>

<div class="container" style="margin-top:30px">
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-9">Student List</div>
        <div class="col-md-3" align="right">
          <button type="button" id="add_button" class="btn btn-info btn-sm">Add</button>
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
              <th>rf_id</th>
              <th>email</th>
              <th>class</th>
              <th>phone no</th>
              <!-- <th>leave_request</th> -->
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>

</html>

<script type="text/javascript" src="https://www.eyecon.ro/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" href="https://www.eyecon.ro/bootstrap-datepicker/css/datepicker.css" />

<style>
  .datepicker {
    z-index: 1600 !important;
    /* has to be larger than 1050 */
  }
</style>

<div class="modal" id="formModal">
  <div class="modal-dialog">
    <form method="post" id="student_form">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title" id="modal_title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">Student Name <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="name" id="name" class="form-control" />
                <span id="error_name" class="text-danger"></span>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">rf id<span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="rf_id" id="rf_id" class="form-control" />
                <span id="error_rf_id" class="text-danger"></span>
                <button type="button" class="btn btn-primary container-fluid mt-1" id="startScanButton">Read Id</button>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">Email Address <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="email" id="email" class="form-control" />
                <span id="error_email" class="text-danger"></span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">class <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <select name="clss_id" id="clss_id" class="form-control">
                  <option value="">Select class</option>
                  <?php
                  echo load_class_list($connect);
                  ?>
                </select>
                <span id="error_clss_id" class="text-danger"></span>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <div class="row">
            <label class="col-md-4 text-right">Password <span class="text-danger">*</span></label>
            <div class="col-md-8">
              <input type="password" name="password" id="password" class="form-control" />
              <span id="error_password" class="text-danger"></span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <label class="col-md-4 text-right">phone_number <span class="text-danger">*</span></label>
            <div class="col-md-8">
              <input type="bigint" name="phone_no" id="phone_no" class="form-control" />
              <span id="error_phone_no" class="text-danger"></span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <label class="col-md-4 text-right">leave request <span class="text-danger">*</span></label>
            <div class="col-md-8">
              <textarea name="leave_request" id="leave_request" class="form-control"></textarea>
              <span id="error_leave_request" class="text-danger"></span>
            </div>
          </div>
        </div>



        <!-- Modal footer -->
        <div class="modal-footer">
          <input type="hidden" name="student_id" id="student_id" />
          <input type="hidden" name="action" id="action" value="Add" />
          <input type="submit" name="button_action" id="button_action" class="btn btn-success btn-sm" value="Add" />
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
        </div>

      </div>
    </form>
  </div>
</div>

<div class="modal" id="deleteModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Delete Confirmation</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <h3 align="center">Are you sure you want to remove this?</h3>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" name="ok_button" id="ok_button" class="btn btn-primary btn-sm">OK</button>
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


<script>

  let scanInitiated = false; // Flag to track if RFID scan has already been initiated
  document.getElementById('startScanButton').addEventListener('click', startRFIDScan);
  // Function to handle button click event
  function startRFIDScan() {
    console.log("Done")
    fetch('http://localhost:3000/startScan') // Send a GET request to the server
      .then(response => {
        if (response.ok) {
          return response.text();
        } else {
          throw new Error('Failed to start RFID scan');
        }
      })
      .then(data => {
        // alert('RFID scan started: ' + data);
        document.getElementById("rf_id").value = data;
        scanInitiated = false; // Reset the flag to allow subsequent scans
      })
      .catch(error => {
        console.error('Error:', error.message);
      });
  }

  $(document).ready(function () {
    var dataTable = $('#student_table').DataTable({
      "processing": true,
      "serverSide": true,
      "order": [],
      "ajax": {
        url: "student_action.php",
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

    //  $('#student_dob').datepicker({
    //  format: "yyyy-mm-dd",
    // autoclose: true,
    //   container: '#formModal modal-body'
    // });

    function clear_field() {
      $('#student_form')[0].reset();
      $('#error_name').text('');
      $('#error_rf_id').text('');
      $('#error_email').text('');
      $('#error_password').text('');
      $('#error_phone_no').text('');
      $('#error_leave_request').text('');
      $('#error_clss_id_id').text('');
    }

    $('#add_button').click(function () {
      $('#modal_title').text("Add Student");
      $('#button_action').val('Add');
      $('#action').val('Add');
      $('#formModal').modal('show');
      clear_field();
    });



    $('#student_form').on('submit', function (event) {
      event.preventDefault();
      $.ajax({
        url: "student_action.php",
        method: "POST",
        data: $(this).serialize(),
        dataType: "json",
        beforeSend: function () {
          $('#button_action').attr('disabled', 'disabled');
          $('#button_action').val('Validate...');
        },
        success: function (data) {
          $('#button_action').attr('disabled', false);
          $('#button_action').val($('#action').val());
          if (data.success) {
            $('#message_operation').html('<div class="alert alert-success">' + data.success + '</div>');
            clear_field();
            $('#formModal').modal('hide');
            dataTable.ajax.reload();
          }
          if (data.error) {
            if (data.error_student_name != '') {
              $('#errort_name').text(data.error_name);
            }
            else {
              $('#error_name').text('');
            }
            if (data.error_rf_id != '') {
              $('#error_rf_id').text(data.error_rf_id);
            }
            else {
              $('#error_rf_id').text('');
            }

            if (data.error_email != '') {
              $('#error_email').text(data.error_email);
            }
            else {
              $('#error_email').text('');
            }
            if (data.error_password != '') {
              $('#error_password').text(data.error_password);
            }
            else {
              $('#error_password').text('');
            }
            if (data.error_phone_no != '') {
              $('#error_phone_no').text(data.error_phone_no);
            }
            else {
              $('#error_phone_no').text('');
            }
            if (data.error_leave_request != '') {
              $('#error_leave_request').text(data.error_leave_request);
            }
            else {
              $('#error_leave_request').text('');
            }
            if (data.error_clss_id != '') {
              $('#error_clss_id').text(data.error_clss_id);
            }
            else {
              $('#error_clss_id').text('');
            }
          }
        }
      });
    });

    var student_id = '';

    $(document).on('click', '.edit_student', function () {
      student_id = $(this).attr('id');
      clear_field();
      $.ajax({
        url: "student_action.php",
        method: "POST",
        data: { action: 'edit_fetch', student_id: student_id },
        dataType: "json",
        success: function (data) {
          $('#name').val(data.name);
          $('#rf_id').val(data.rf_id);

          //$('#teacher_emailid').val(data.teacher_emailid);
          $('#phone_no').val(data.phone_no);
          $('#leave_request').val(data.leave_request);
          //$('#teacher_qualification').val(data.teacher_qualification);
          //$('#teacher_doj').val(data.teacher_doj);
          $('#clss_id').val(data.clss_id);
          $('#student_id').val(data.student_id);
          $('#modal_title').text("Edit Student");
          $('#button_action').val('Edit');
          $('#action').val('Edit');
          $('#formModal').modal('show');
        }
      });
    });

    $(document).on('click', '.delete_student', function () {
      student_id = $(this).attr('id');
      $('#deleteModal').modal('show');
    });

    $('#ok_button').click(function () {
      $.ajax({
        url: "student_action.php",
        method: "POST",
        data: { student_id: student_id, action: 'delete' },
        success: function (data) {
          $('#message_operation').html('<div class="alert alert-success">' + data + '</div>');
          $('#deleteModal').modal('hide');
          dataTable.ajax.reload();
        }
      });
    });

  let scanInitiated = false; // Flag to track if RFID scan has already been initiated
  document.getElementById('startScanButton').addEventListener('click', startRFIDScan);
  // Function to handle button click event
  function startRFIDScan() {
    console.log("Done")
    fetch('http://localhost:3000/startScan') // Send a GET request to the server
      .then(response => {
        if (response.ok) {
          return response.text();
        } else {
          throw new Error('Failed to start RFID scan');
        }
      })
      .then(data => {
        // alert('RFID scan started: ' + data);
        document.getElementById("rf_id").value = data;
        scanInitiated = false; // Reset the flag to allow subsequent scans
      })
      .catch(error => {
        console.error('Error:', error.message);
      });
  }

  });
</script>