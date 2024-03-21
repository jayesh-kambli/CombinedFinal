<?php

include('header.php');

?>

<div class="container" style="margin-top:30px">
  <div class="card">
   <div class="card-header">
      <div class="row">
        <div class="col-md-9">class List</div>
        <div class="col-md-3" align="right">
          <button type="button" id="add_button" class="btn btn-info btn-sm">Add</button>
        </div>
      </div>
    </div>
   <div class="card-body">
    <div class="table-responsive">
        <span id="message_operation"></span>
     <table class="table table-striped table-bordered" id="grade_table">
      <thead>
       <tr>
        <th>class Name</th>
		<th>teacher </th>
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
<div class="modal" id="formModal">
  <div class="modal-dialog">
    <form method="post" id="grade_form">
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
              <label class="col-md-4 text-right">Class Name <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <select name="name" id="name" class="form-control">
                  <option value="">Select Class Name</option>
                  <?php
                  echo load_class_names($connect);
                  ?>
                </select>
                <span id="error_name" class="text-danger"></span>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-md-4 text-right">Teacher <span class="text-danger">*</span></label>
              <div class="col-md-8">
                <select name="teacher" id="teacher" class="form-control">
                  <option value="">Select Teacher</option>
                  <?php
                  echo load_teacher_list($connect);
                  ?>
                </select>
                <span id="error_teacher" class="text-danger"></span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Modal footer -->
        <div class="modal-footer">
          <input type="hidden" name="class_id" id="class_id" />
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
$(document).ready(function(){
 var dataTable = $('#grade_table').DataTable({
  "processing":true,
  "serverSide":true,
  "order":[],
  "ajax":{
   url:"grade_action.php",
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

  $('#add_button').click(function(){
    $('#modal_title').text("Add Grade");
    $('#button_action').val('Add');
    $('#action').val('Add');
    $('#formModal').modal('show');
    clear_field();
  });

  function clear_field()
  {
    $('#grade_form')[0].reset();
    $('#error_name').text('');
	  $('#error_teacher').text('');
  }

  $('#grade_form').on('submit', function(event){
    event.preventDefault();
    $.ajax({
      url:"grade_action.php",
      method:"POST",
      data:$(this).serialize(),
      dataType:"json",
      beforeSend:function()
      {
        $('#button_action').attr('disabled', 'disabled');
        $('#button_action').val('Validate...');
      },
      success:function(data){
        $('#button_action').attr('disabled', false);
        $('#button_action').val($('#action').val());
        if(data.success)
        {
          $('#message_operation').html('<div class="alert alert-success">'+data.success+'</div>');
          clear_field();
          dataTable.ajax.reload();
          $('#formModal').modal('hide');
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
		
          if(data.error_teacher != '')
          {
            $('#error_teacher').text(data.error_teacher);
          }
          else
          {
            $('#error_teacher').text('');
          }
        }
      }
    });
  });

  var class_id = '';

  $(document).on('click', '.edit_grade', function(){
   class_id = $(this).attr('id');
    clear_field();
    $.ajax({
      url:"grade_action.php",
      method:"POST",
      data:{action:'edit_fetch', class_id:class_id},
      dataType:"json",
      success:function(data)
      {
        $('#name').val(data.name);
		$('#teacher').val(data.teacher);
        $('#class_id').val(data.class_id);
        $('#modal_title').text("Edit Grade");
        $('#button_action').val('Edit');
        $('#action').val('Edit');
        $('#formModal').modal('show');
      }
    });
  });

  $(document).on('click', '.delete_grade', function(){
    class_id = $(this).attr('id');
    $('#deleteModal').modal('show');
  });

  $('#ok_button').click(function(){
    $.ajax({
      url:"grade_action.php",
      method:"POST",
      data:{class_id:class_id, action:'delete'},
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
