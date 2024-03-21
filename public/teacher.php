<?php

include('header.php');

?>

<div class="container" style="margin-top:30px">
  <div class="card">
   <div class="card-header">
      <div class="row">
        <div class="col-md-9">Teacher List</div>
        <div class="col-md-3" align="right">
          <button type="button" id="add_button" class="btn btn-info btn-sm">Add</button>
        </div>
      </div>
    </div>
   <div class="card-body">
    <div class="table-responsive">
        <span id="message_operation"></span>
     <table class="table table-striped table-bordered" id="teacher_table">
      <thead>
       <tr>
    
        <th>Teacher Name</th>
        <th>Email Address</th>

        <th>View</th>
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
      z-index: 1600 !important; /* has to be larger than 1050 */
    }
</style>

<div class="modal" id="formModal">
  <div class="modal-dialog">
    <form method="post" id="teacher_form" enctype="multipart/form-data">
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
              <label class="col-md-4 text-right">Teacher Name <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="name" id="name" class="form-control" />
                <span id="error_name" class="text-danger"></span>
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
              <label class="col-md-4 text-right">Email Address <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="email" id="email" class="form-control" />
                <span id="error_email" class="text-danger"></span>
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
              <label class="col-md-4 text-right">subject <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="subject" id="subject" class="form-control" />
                <span id="error_subject" class="text-danger"></span>
              </div>
            </div>
          </div>
        
          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">Date of Joining <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <input type="text" name="join_date" id="join_date" class="form-control" />
                <span id="error_join_date" class="text-danger"></span>
              </div>
            </div>
          </div>
     
        </div>

       <!-- Modal footer -->
        <div class="modal-footer">
 
          <input type="hidden" name="teacher_id" id="teacher_id" />
          <input type="hidden" name="action" id="action" value="Add" />
          <input type="submit" name="button_action" id="button_action" class="btn btn-success btn-sm" value="Add" />
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
        </div>

      </div>
    </form>
  </div>
</div>

<div class="modal" id="viewModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Teacher Details</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body" id="teacher_details">

      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
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
$(document).ready(function(){
 var dataTable = $('#teacher_table').DataTable({
  "processing":true,
  "serverSide":true,
  "order":[],
  "ajax":{
   url:"teacher_action.php",
   type:"POST",
   data:{action:'fetch'}
  },
  "columnDefs":[
   {
    "targets": "_all",
    "defaultContent": "-"
   },
  ],

 });

  $('#join_date').datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        container: '#formModal modal-body'
    });

  function clear_field()
  {
    $('#teacher_form')[0].reset();
    $('#error_name').text('');
    $('#error_phone_no').text('');
    $('#error_email').text('');
    $('#error_password').text('');
    $('#error_subject').text('');
    $('#error_join_date').text('');

  }

  $('#add_button').click(function(){
        $('#modal_title').text("Add Teacher");
        $('#button_action').val('Add');
        $('#action').val('Add');
        $('#formModal').modal('show');
        clear_field();
        // Show email and password fields for adding
        $('#email').closest('.form-group').show();
        $('#password').closest('.form-group').show();
    });

    $('#teacher_form').on('submit', function(event){
        event.preventDefault();
        $.ajax({
            url:"teacher_action.php",
            method:"POST",
            data:new FormData(this),
            dataType:"json",
            contentType:false,
            processData:false,
            beforeSend:function()
            {
                $('#button_action').val('Validate...');
                $('#button_action').attr('disabled', 'disabled');
            },
            success:function(data){
                $('#button_action').attr('disabled', false);
                $('#button_action').val($('#action').val());
                if(data.success)
                {
                    $('#message_operation').html('<div class="alert alert-success">'+data.success+'</div>');
                    clear_field();
                    $('#formModal').modal('hide');
                    dataTable.ajax.reload();
                }
        if(data.error)
        { 
          if(data.error_name != '')
          {
            $('#error_name').text(data.error_name);
          }
          else
          {
            $('#error_name').text('');
          }
          if(data.error_phone_no != '')
          {
            $('#error_phone_no').text(data.error_phone_no);
          }
          else
          {
            $('#error_phone_no').text('');
          }
          if(data.error_email != '')
          {
            $('#error_email').text(data.error_email);
          }
          else
          {
            $('#error_email').text('');
          }
          if(data.error_password != '')
          {
            $('#error_password').text(data.error_password);
          }
          else
          {
            $('#error_password').text('');
          }
        
          if(data.error_subject != '')
          {
            $('#error_subject').text(data.error_subject);
          }
          else
          {
            $('#error_subject').text('');
          }
          if(data.error_join_date != '')
          {
            $('#error_join_date').text(data.error_join_date);
          }
          else
          {
            $('#error_join_date').text('');
          }
         
        }
      }
    });
  });

  var teacher_id = '';

  $(document).on('click', '.view_teacher', function(){
    teacher_id = $(this).attr('id');
    $.ajax({
      url:"teacher_action.php",
      method:"POST",
      data:{action:'single_fetch', teacher_id:teacher_id},
      success:function(data)
      {
        $('#viewModal').modal('show');
        $('#teacher_details').html(data);
      }
    });
  });

  $(document).on('click', '.edit_teacher', function(){
    teacher_id = $(this).attr('id');
    clear_field();
    $.ajax({
        url:"teacher_action.php",
        method:"POST",
        data:{action:'edit_fetch', teacher_id:teacher_id},
        dataType:"json",
        success:function(data)
        {
            $('#name').val(data.name);
            $('#phone_no').val(data.phone_no);
            $('#subject').val(data.subject);
            $('#join_date').val(data.join_date);
            $('#teacher_id').val(data.teacher_id);
            $('#modal_title').text("Edit Teacher");
            $('#button_action').val('Edit');
            $('#action').val('Edit');
            $('#formModal').modal('show');
            // Hide email and password fields for editing
            $('#formModal #email').closest('.form-group').hide();
            $('#formModal #password').closest('.form-group').hide();
        }
    });
});


  $(document).on('click', '.delete_teacher', function(){
    teacher_id = $(this).attr('id');
    $('#deleteModal').modal('show');
  });

  $('#ok_button').click(function(){
    $.ajax({
      url:"teacher_action.php",
      method:"POST",
      data:{teacher_id:teacher_id, action:'delete'},
      success:function(data)
      {
        $('#message_operation').html('<div class="alert alert-success">'+data+'</div>');
        $('#deleteModal').modal('hide');
        dataTable.ajax.reload();
      }
    });
  });

});
</script>