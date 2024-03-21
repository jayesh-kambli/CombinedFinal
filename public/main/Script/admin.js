document.addEventListener("DOMContentLoaded", function () {
  fetchClasses();
});

function addEntity(data) {
  fetch('./php/admin/add.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
    .then(response => response.json())
    .then(result => {
      sToast(result.status, result.message);
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

function fetchClasses() {
  fetch('./php/admin/admin.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Process the fetched data
      console.log(data);
      if (data.status) {
        //class
        data.classes.forEach(clsEle => {
          document.getElementById("accordionExample").innerHTML += `<div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${clsEle.class_id}" aria-expanded="false" aria-controls="collapseThree">
                        ${clsEle.class_name}
                      </button>
                    </h2>
                    <div id="collapse-${clsEle.class_id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                      <div class="accordion-body" id="classBody">
                      <div class="d-flex justify-content-center align-items-center">
                        <button type="button" id="${clsEle.class_id}-stu" class="btn btn-warning container-fluid d-flex justify-content-center align-items-center mx-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><ion-icon name="add-circle-outline" class="mx-1"></ion-icon>Add Student</button>
                        <button type="button" id="${clsEle.class_id}-sub" class="btn btn-warning container-fluid d-flex justify-content-center align-items-center mx-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><ion-icon name="add-circle-outline" class="mx-1"></ion-icon>Add Subject</button>
                      </div>
                      <table class="table table-striped">
                            <thead>
                                <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Subject Name</th>
                            </thead>
                            <tbody id="subs-${clsEle.class_id}">
                                
                            </tbody>
                        </table>
                      <table class="table table-striped">
                            <thead>
                                <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Edit</th>
                                </tr>
                            </thead>
                            <tbody id="students-${clsEle.class_id}">
                                
                            </tbody>
                        </table>
                      </div>
                    </div>
                  </div>`;
          clsEle.subjects.forEach((subEle) => {
            document.getElementById(`subs-${clsEle.class_id}`).innerHTML += `<tr>
                        <th scope="row">${subEle.subject_id}</th>
                        <td>${subEle.name}</td>
                    </tr>`;
          })
          clsEle.students.forEach((stuEle) => {
            document.getElementById(`students-${clsEle.class_id}`).innerHTML += `<tr>
                        <th scope="row">${stuEle.student_id}</th>
                        <td>${stuEle.name}</td>
                        <td>${stuEle.email}</td>
                        <td>${stuEle.phone_no}</td>
                        <td></td>
                      </tr>`;
          });
        });

        document.getElementById("classBody").addEventListener("click", (event) => {
          if (event.target.tagName == "BUTTON") {
            const [id, type] = event.target.id.split("-");
            if (type == "stu") {
              // data.classes.find(cls => cls.class_id === id)
              document.getElementById("allModBody").innerHTML = `<form id="studentForm">
                      <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1" style="width: 120px;">Student ID</span>
                        <input type="number" class="form-control" placeholder="Enter Student ID" aria-label="Student ID" aria-describedby="basic-addon1" name="student_id">
                      </div>
                    
                      <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1" style="width: 120px;">Name</span>
                        <input type="text" class="form-control" placeholder="Enter Name" aria-label="Name" aria-describedby="basic-addon1" name="name">
                      </div>
                    
                      <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1" style="width: 120px;">Email</span>
                        <input type="email" class="form-control" placeholder="Enter Email" aria-label="Email" aria-describedby="basic-addon1" name="email">
                      </div>
                    
                      <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1" style="width: 120px;">Password</span>
                        <input type="password" class="form-control" placeholder="Enter Password" aria-label="Password" aria-describedby="basic-addon1" value="Default123" name="password">
                      </div>
                    
                      <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1" style="width: 120px;">Phone No.</span>
                        <input type="number" class="form-control" placeholder="Enter Phone Number" aria-label="Phone Number" aria-describedby="basic-addon1" name="phone_no">
                      </div>
                    
                      <button type="submit" class="btn btn-primary">Submit</button>
                    </form>`;

              const myForm = document.getElementById("studentForm");
              myForm.addEventListener("submit", (event) => {
                event.preventDefault();
                const studentData = {
                  addType: 'student',
                  name: myForm.name.value,
                  student_id: myForm.student_id.value,
                  email: myForm.email.value,
                  password: myForm.password.value,
                  phone_no: myForm.phone_no.value,
                  clss_id: id  // Adjust the class ID accordingly
                }
                addEntity(studentData);

              })
            } else if (type == "sub") {

            }
          }
        })

        //teachers
        data.teachers.forEach((tEle) => {
          document.getElementById("accordionTeacher").innerHTML += `<div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${tEle.teacher_id}" aria-expanded="false" aria-controls="collapse-${tEle.teacher_id}">
                        ${tEle.name}
                      </button>
                    </h2>
                    <div id="collapse-${tEle.teacher_id}" class="accordion-collapse collapse p-2" data-bs-parent="#accordionTeacher">
                      <div class="accordion-body">
                      <div class="p-2 rounded border border-dark-subtle">
                        Id: ${tEle.teacher_id}<br/>
                        Email: ${tEle.email}<br/>
                        Join Date: ${tEle.join_date}
                      </div>
                      <table class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">ID</th>
                          <th scope="col">Subject Name</th>
                        </tr>
                      </thead>
                      <tbody id="subs-${tEle.teacher_id}">
                        
                      </tbody>
                    </table>
                      </div>
                    </div>
                  </div>`;
          tEle.subjects.forEach((subEle) => {
            document.getElementById(`subs-${tEle.teacher_id}`).innerHTML += `<tr>
                    <th scope="row">${subEle.subject_id}</th>
                    <td>${subEle.name}</td>
                  </tr>`;
          })
        })


      } else {

      }
    })
    .catch(error => {
      console.error('Error:', error.message);
    });
}

function sToast(sta, title, message = "") {
  if (sta) {
    iziToast.success({
      title: title,
      message: message,
      position: "topLeft",
    });
  } else {
    iziToast.error({
      title: title,
      message: message,
      position: "topLeft",
    });
  }

}