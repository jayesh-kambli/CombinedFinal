<?php include('header.php'); ?>

<div class="container" style="margin-top:30px">
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-9">Subject List</div>
        <div class="col-md-3" align="right">
          <button type="button" id="add_button" class="btn btn-info btn-sm">Add</button>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <span id="message_operation"></span>
        <table class="table table-striped table-bordered" id="subject_table">
          <thead>
            <tr>
              <th>Subject Name</th>
              <th>Teacher</th>
              <th>Class</th>
              <th>View</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Subject Modal -->
<div class="modal" id="formModal">
  <div class="modal-dialog">
    <form method="post" id="subject_form">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="modal_title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="name">Subject Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name">
            <span id="error_name" class="text-danger"></span>
          </div>
          <div class="form-group">
            <label for="teacher_id">Teacher <span class="text-danger">*</span></label>
            <select class="form-control" id="teacher_id" name="teacher_id">
              <!-- Teachers will be populated dynamically using AJAX -->
              <option value="">select teacher</option>
              <?php
                echo load_teacher_list($connect);
              ?>
            </select>
            <span id="error_teacher_id" class="text-danger"></span>
          </div>
          <div class="form-group">
            <label for="class">Class <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="class" name="class">
            <span id="error_class" class="text-danger"></span>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="subject_id" id="subject_id" />
          <input type="hidden" name="action" id="action" value="Add" />
          <button type="submit" class="btn btn-success" id="button_action">Add</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- View Subject Modal -->
<div class="modal" id="viewModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Subject Details</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="subject_details">
        <!-- Content for viewing subject details will be populated here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Delete Confirmation</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <h3 align="center">Are you sure you want to remove this?</h3>
      </div>
      <div class="modal-footer">
        <button type="button" name="ok_button" id="ok_button" class="btn btn-primary btn-sm">OK</button>
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  var dataTable = $('#subject_table').DataTable({
    "processing": true,
    "serverSide": true,
    "order": [],
    "ajax": {
      url: "subject_action.php",
      type: "POST",
      data: { action: 'fetch' }
    },
    "columnDefs":[
      {
        "targets": "_all",
        "defaultContent": "-"
      },
    ]
  });

  function clearField() {
    $('#subject_form')[0].reset();
    $('#error_name').text('');
    $('#error_teacher_id').text('');
    $('#error_class').text('');
  }

  $('#add_button').click(function(){
    $('#modal_title').text("Add Subject");
    $('#button_action').val('Add');
    $('#action').val('Add');
    $('#formModal').modal('show');
    clearField();
  });

  $('#subject_form').on('submit', function(event){
    event.preventDefault();
    $.ajax({
      url:"subject_action.php",
      method:"POST",
      data:$(this).serialize(),
      dataType:"json",
      beforeSend:function() {
        $('#button_action').val('Validate...');
        $('#button_action').attr('disabled', 'disabled');
      },
      success:function(data){
        $('#button_action').attr('disabled', false);
        $('#button_action').val($('#action').val());
        if(data.success) {
          $('#message_operation').html('<div class="alert alert-success">'+data.success+'</div>');
          clearField();
          $('#formModal').modal('hide');
          dataTable.ajax.reload();
        }
        if(data.error) { 
          if(data.error_name != '') {
            $('#error_name').text(data.error_name);
          } else {
            $('#error_name').text('');
          }
          if(data.error_teacher_id != '') {
            $('#error_teacher_id').text(data.error_teacher_id);
          } else {
            $('#error_teacher_id').text('');
          }
          if(data.error_class != '') {
            $('#error_class').text(data.error_class);
          } else {
            $('#error_class').text('');
          }
        }
      }
    });
  });

  var subject_id = '';

  $(document).on('click', '.view_subject', function(){
    subject_id = $(this).attr('id');
    $.ajax({
      url: "subject_action.php",
      method: "POST",
      data: { action: 'single_fetch', subject_id: subject_id },
      success: function(data) {
        $('#viewModal').modal('show');
        $('#subject_details').html(data);
      }
    });
  });

  $(document).on('click', '.edit_subject', function(){
    subject_id = $(this).attr('id');
    clearField();
    $.ajax({
      url: "subject_action.php",
      method: "POST",
      data: { action: 'edit_fetch', subject_id: subject_id },
      dataType: "json",
      success: function(data) {
        console.log(data);
        $('#name').val(data.name);
        $('#teacher_id').val(data.teacher_id);
        $('#class').val(data.class);
        $('#subject_id').val(data.subject_id);
        $('#modal_title').text("Edit Subject");
        $('#button_action').val('Edit');
        $('#action').val('Edit');
        $('#formModal').modal('show');
      }
    });
  });

  $(document).on('click', '.delete_subject', function(){
    subject_id = $(this).attr('id');
    $('#deleteModal').modal('show');
  });

  $('#ok_button').click(function(){
    $.ajax({
      url: "subject_action.php",
      method: "POST",
      data: { subject_id: subject_id, action: 'delete' },
      success: function(data) {
        $('#message_operation').html('<div class="alert alert-success">'+data+'</div>');
        $('#deleteModal').modal('hide');
        dataTable.ajax.reload();
      }
    });
  });
});
</script>
