document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("logout").addEventListener("click", () => {
        sessionStorage.clear();
        sToast(1, "Logout Successfull", "Loading......");
        setTimeout(function () {
            window.location.href = "index.html";
        }, 1000);
    });

    fetch('./php/Tr/teacher.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: JSON.stringify({
            user: sessionStorage.getItem("user"),
            pass: sessionStorage.getItem("pass"),
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data);
                const TeacherData = data.dataTr;

                //set up profile ====>
                document.getElementById("modal-body-profile").innerHTML = `<div class="d-flex align-items-center"><ion-icon name="person-circle-outline"></ion-icon><small class="mx-1">
                        Name: ${data.dataTr.name}</small><br /><br /></div>
                    <div class="d-flex justify-content-start align-items-center"><ion-icon name="school-outline"></ion-icon>
                    <small class="mx-1"> Join Date: ${data.dataTr.join_date}</small><br /><br />
                    </div>
                    <div class="d-flex justify-content-start align-items-center"><ion-icon name="id-card-outline"></ion-icon>
                    <small class="mx-1"> Teacher id : ${data.dataTr.teacher_id}</small><br /><br />
                    </div>
                    <div class="d-flex justify-content-start align-items-center"><ion-icon name="mail-open-outline"></ion-icon>
                    <small class="mx-1"> email: ${data.dataTr.email}</small><br /><br />
                    </div>
                    <div class="d-flex justify-content-start align-items-center"><ion-icon name="call-outline"> </ion-icon><small
                        class="mx-1"> Contact : ${data.dataTr.phone_no}</small><br /><br /></div>`;
                document.getElementById("CPemail").value = data.dataTr.email;

                //change password button in profile section
                const passForm = document.getElementById("passForm");
                passForm.addEventListener("submit", (event) => {
                    event.preventDefault();

                    if (passForm.cpass1.value == passForm.cpass2.value) {
                        if (!(passForm.pass.value == passForm.cpass1.value)) {
                            // fetch change pass
                            fetch("./php/Tr/chPass.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify({
                                    mail: passForm.mail.value,
                                    pass: passForm.pass.value,
                                    npass: passForm.cpass1.value,
                                }),
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    if (data.success) {
                                        sToast(data.success, "Success", data.message);
                                        setTimeout(function () {
                                            window.location.href = "index.html";
                                        }, 1500);
                                    } else {
                                        sToast(data.success, "Failed", data.message);
                                    }
                                })
                                .catch((error) => {
                                    console.error("Error:", error);
                                    sToast(0, "Failed", "Error !");
                                }); //fetch end change pass

                        } else {
                            sToast(0, "Password cant be same as earlier!");
                        }
                    } else {
                        sToast(0, "New password does not match each other");
                    }
                });

                //Class Main Div ====>
                const parent = document.getElementById("accordionExampleForTeachers");
                parent.innerHTML = ``;
                data.classes.forEach((classEle) => {
                    parent.innerHTML += `<div class="accordion-item">
                        <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" style="width: 100%;"
                        data-bs-target="#collapse${classEle.class_id}" aria-expanded="false" aria-controls="collapse${classEle.class_id}">
                        ${classEle.name}
                        </button>
                        </h2>
                        <div id="collapse${classEle.class_id}" class="accordion-collapse collapse" data-bs-parent="#accordionExampleForTeachers">
                        <div class="accordion-body">
                        <div class="yellowBorder d-flex flex-column justify-content-start align-items-start p-3 mb-3">
                            <p class="card-text m-0">Class Id : ${classEle.class_id}</p>
                            <p class="card-text m-0">Start date: ${classEle.start_id}</p>
                            <p class="card-text m-0">End date: ${classEle.end_id}</p>
                        </div>
                        <buttona type="button" id="${classEle.class_id}" class="btn btn-success container-fluid mB-3" data-bs-toggle="modal" data-bs-target="#exampleModal">View Students Details</buttona>
                        <buttonReport type="button" id="${classEle.class_id}" class="btn btn-warning container-fluid mB-3" data-bs-toggle="modal" data-bs-target="#exampleModalforClassReport">View Full Class Attendance Report</buttonReport>
                        <div class="yellowBorder p-3">
                        <div class="mb-2 p-1 d-flex justify-content-start align-items-start"><h5>My Subjects</h5></div>
                        <div class="row" id="allSubjectsOfTeacher${classEle.class_id}"> <!-- all Subject in this --></div>
                        </div>
                        </div>
                        </div>
                    </div>`;

                    //subject-block list in class section
                    const subPar = document.getElementById(`allSubjectsOfTeacher${classEle.class_id}`)
                    data.subjects.forEach((subEle) => {
                        const newAssignParEle = document.getElementById(`assignForParticularSubject-${subEle.subject_id}`);
                        const thisSubject = subEle.subject_id;
                        const gotThis = data.subjects.find((subs) => subs.subject_id == thisSubject)
                        let msg = "", main = "";
                        gotThis.assignments.forEach((asEle) => {
                            msg = "";
                            let lines = asEle.assignment_information.split("\n");
                            lines.forEach((line) => {
                                msg += `<small>${line}</small><br/>`;
                            });
                            main += `<li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                            <div class="fw-bold">Assignment No. <small id="assId-${asEle.assignment_id}">${asEle.assignment_id}</small> <small class="badge bg-primary rounded-pill p-2 mb-2">Due date : ${asEle.due_date}</small></div>
                            <small>${msg}</small>
                            <div class="d-flex justify-content-start align-items-center my-2">
                            <buttonB id="${gotThis.subject_id}-${gotThis.name}-subm-${asEle.assignment_id}-${gotThis.class}" type="button" style="width: 250px;" class="btn btn-warning me-2 p-0 px-2 d-flex justify-contect-center align-items-center" data-bs-toggle="modal" data-bs-target="#staticBackdropMain"><ion-icon name="analytics-outline" class="mx-2"></ion-icon>Submission Data</buttonB>
                            <buttonB id="${gotThis.subject_id}-${gotThis.name}-del-${asEle.assignment_id}-${gotThis.class}" type="button" style="width: 250px;" class="btn btn-danger me-2 p-0 px-2 d-flex justify-contect-center align-items-center"><ion-icon name="trash-outline" class="mx-2"></ion-icon>Delete Assignment</buttonB>
                            <buttonB id="${gotThis.subject_id}-${gotThis.name}-edit-${asEle.assignment_id}-${gotThis.class}" type="button" style="width: 250px;" class="btn btn-primary me-2 p-0 px-2 d-flex justify-contect-center align-items-center" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><ion-icon name="create-outline" class="mx-2"></ion-icon>Edit Details</buttonB>
                            </div>
                            </div>
                            </li>`;
                        });

                        if (subEle.class == classEle.class_id) {
                            subPar.innerHTML += `<div class="col-12  d-flex justify-content-center align-items-center mt-2">
                            <div class="card text-center container-fluid">
                            <div class="card-body d-flex justify-content-between cardMod cardMod2 text-start">
                            <h6 class="card-title">${subEle.name}</h6>
                            <div>
                            <small class="card-text m-0">Subject Id : ${subEle.subject_id}</small><br/>
                            <small class="card-text m-0">For Class : ${subEle.class}</small>
                            </div>

                            <div class="accordion" id="accordionExample">
                            <div class="accordion-item" >
                                <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${gotThis.subject_id}" aria-expanded="false" aria-controls="collapseTwo">
                                    Assignments
                                </button>
                                </h2>
                                <div id="collapse${gotThis.subject_id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body" id="${gotThis.subject_id}-body">
                                <buttonB id="${gotThis.subject_id}-${gotThis.name}-add" type="button" class="btn btn-success container-fluid my-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Add New Assignment</buttonB>
                                    <ol class="list-group list-group-numbered" id="assinOfSub-${gotThis.subject_id}">${main}</ol>
                                </div>
                                </div>
                            </div>
                            </div>


                            <!-- <a href="#" class="btn btn-primary">Details</a> -->
                            <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Details
                            </button> -->
                            </div>
                            </div>
                            </div>`
                        }
                    })
                });


                classParentEle = document.getElementById("allClassOfTeacher");
                const studentDataBlock = document.getElementById("studentsData");

                //on click "view students details"
                classParentEle.addEventListener("click", function (event) {
                    if (event.target.tagName === "BUTTONA") {
                        studentDataBlock.innerHTML = ``;
                        var clickedId = event.target.id;
                        data.classes.forEach((clsEle) => {
                            if (clsEle.class_id == clickedId) {
                                clsEle.students.forEach((stuEle) => {
                                    if (stuEle.clss_id == clickedId) {
                                        // console.log(JSON.parse(stuEle.attendance[0]));
                                        const allAttendanceData = JSON.parse(stuEle.attendance[0].attendance_data).atData;
                                        let [yyyy, mm, dd] = clsEle.start_id.split("-");
                                        const userDate = `${mm}-${dd}-${yyyy}`;
                                        const percent = calculatePer(allAttendanceData, userDate);
                                        const colorCls = percent >= 75 ? "green-circle" : "orange-circle";
                                        studentDataBlock.innerHTML += `<tr>
                                        <th scope="row">${stuEle.student_id}</th>
                                        <td>${stuEle.name}</td>
                                        <td>${stuEle.clss_id}</td>
                                        <td>+91 ${stuEle.phone_no}</td>
                                        <td ><a class="link-offset-2 link-underline link-underline-opacity-100 d-flex align-items-center" href="#" id="${stuEle.student_id}-ad" data-bs-toggle="modal" data-bs-target="#staticBackdropMain"><span class="circle ${colorCls} me-1"></span> ${percent}% (view)</a></td>
                                        <td><a class="link-offset-2 link-underline link-underline-opacity-100" href="#" id="${stuEle.student_id}-lr" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Leave Requests</a></td>
                                        <!-- <td><a class="link-offset-2 link-underline link-underline-opacity-100" href="#" id="${stuEle.student_id}-ed" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><ion-icon name="create"></ion-icon> Edit</a></td> -->
                                    </tr>`;
                                    }
                                })
                            }
                        })
                    } else if (event.target.tagName === "BUTTONREPORT") {
                        let values = [];
                        let colorValues = [];
                        let nameValues = [];
                        let graphNameValues = [];
                        data.classes.forEach((classEle) => {
                            if (classEle.class_id == event.target.id) {
                                classEle.students.forEach((child) => {
                                    // console.log(JSON.parse(child.attendance[0].attendance_data).atData);
                                    // console.log(classEle.start_id);
                                    let [yyyy, mm, dd] = classEle.start_id.split("-");
                                    const repUserDate = `${mm}-${dd}-${yyyy}`;
                                    let calPer = calculatePer(JSON.parse(child.attendance[0].attendance_data).atData, repUserDate);
                                    values.push(calPer);
                                    colorValues.push(getColor(calPer));
                                    graphNameValues.push(calPer + "%");
                                    nameValues.push(child.name);
                                })
                            }
                        })

                        const bodyForReport = document.getElementById("bodyForReport");
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
                        // document.getElementById("barChart").style.height = "10em";
                        // document.getElementById("barChart").style.width = "auto";
                    }
                });

                //to handle events of "view students details" table (Attendance, Leave Reaquests, Edit)
                const stuListData = document.getElementById("studentsData");
                stuListData.addEventListener("click", function (event) {
                    if (event.target.tagName === "A") {
                        var [clickedId, adData] = event.target.id.split("-");
                        const attendanceCalender = document.getElementById("attendanceCalenderMain");
                        attendanceCalender.innerHTML = "Loading..........";
                        if (adData == "ad") {
                            data.classes.forEach(data1 => {
                                data1.students.forEach((sData) => {
                                    if (sData.student_id == clickedId) {
                                        dataAttendance = JSON.parse(sData.attendance[0].attendance_data);
                                        const allAttendanceData = JSON.parse(sData.attendance[0].attendance_data).atData;
                                        let [yyyy, mm, dd] = data1.start_id.split("-");
                                        const userDate = `${mm}-${dd}-${yyyy}`;
                                        attendanceCalender.innerHTML = `<div class="container m-1">
                                        <h6 class="m-1">Name: ${sData.name}</h6>
                                        <h6 class="m-1">Attendance Percentage: ${calculatePer(allAttendanceData, userDate)}%</h6>
                                        <div id="attendanceCalenderAll" class="row"></div>
                                    </div>`;
                                        generateAttendanceCalendar(dataAttendance, 'attendanceCalenderMain');
                                    }
                                })
                            });
                        } else if (adData == "lr") {
                            let flag = false;
                            const forNameleavereaquestsdataList = document.getElementById("attendanceCalender");
                            let gotOne;
                            data.classes.forEach((classes) => {
                                console.log(classes);
                                if (gotOne) {
                                    return;
                                }
                                gotOne = classes.students.find((student) => student.student_id == clickedId);
                            })
                            forNameleavereaquestsdataList.innerHTML = `<div>Name : ${gotOne.name}</div>
                            <div>Student Id : ${gotOne.student_id}</div><hr>
                            <div id="listOfLeaverequests"></div>`;
                            const leavereaquestsdataList = document.getElementById("listOfLeaverequests");
                            leavereaquestsdataList.innerHTML = "Loading........";
                            data.classes.forEach(data1 => {
                                data1.students.forEach((sData) => {
                                    if (sData.student_id == clickedId) {
                                        localStorage.setItem("listLeaveReaquests", JSON.stringify(sData));
                                        flag = true;
                                        leavereaquestsdataList.innerHTML = `<ol class="list-group list-group-numbered" id="listOfLeaveReaquests"></ol>`;
                                        JSON.parse(sData.leave_request).requests.forEach((ele, i) => {
                                            let cls, msg;
                                            if (ele.status == 100) {
                                                msg = "Accepted";
                                                cls = "bg-success";
                                            } else if (ele.status == 101) {
                                                msg = "Pending";
                                                cls = "bg-warning";
                                            } else if (ele.status == 102) {
                                                msg = "Rejected";
                                                cls = "bg-danger";
                                            }
                                            let dayOrDays = dateDifferenceInDays(ele.from, ele.to) > 1 ? "Days" : "Day";
                                            document.getElementById("listOfLeaveReaquests").innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">   
                                            <small>${ele.reason}</small><br/>
                                            <p>Duration : ${ele.from} - ${ele.to} / ${dateDifferenceInDays(ele.from, ele.to)} ${dayOrDays}</p>
                                            <p style="width: 6rem;" id="${i}-status" class="${cls} rounded d-flex justify-content-center align-items-center mx-2" >${msg}</p>
                                            <small></small>
                                            <div class="d-flex justify-content-start align-items-center">
                                            <button type="button" id="${i}-100" class="btn btn-success d-flex justify-content-center align-items-center mx-2"><ion-icon name="checkmark-outline"></ion-icon>Accept</button>
                                            <button type="button" id="${i}-102" class="btn btn-danger d-flex justify-content-center align-items-center"><ion-icon name="close-outline"></ion-icon>Reject</button>
                                            </div>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">Requested at : ${convertDateFormat(ele.requestedAt)}</span>
                                        </li>`;
                                        })
                                    }
                                })
                            });
                            if (flag) {
                                const LRListData = document.getElementById("listOfLeaveReaquests");
                                LRListData.addEventListener("click", function (event) {
                                    if (event.target.tagName === "BUTTON") {
                                        var [clickedId, adData] = event.target.id.split("-");
                                        const id = JSON.parse(localStorage.getItem("listLeaveReaquests")).student_id; // replace with actual student ID
                                        const requestBody = Object.assign({}, JSON.parse(JSON.parse(localStorage.getItem("listLeaveReaquests")).leave_request).requests[clickedId], { "newStatus": parseInt(adData), "id": id });

                                        fetch('./php/Tr/leaverequest.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify(requestBody),
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    let newclsNm, newMsg;
                                                    if (adData == 100) {
                                                        newMsg = "Accepted";
                                                        newclsNm = "bg-success rounded d-flex justify-content-center align-items-center mx-2";
                                                    } else if (adData == 102) {
                                                        newMsg = "Rejected";
                                                        newclsNm = "bg-danger rounded d-flex justify-content-center align-items-center mx-2";
                                                    }
                                                    document.getElementById(`${clickedId}-status`).className = newclsNm;
                                                    document.getElementById(`${clickedId}-status`).innerText = newMsg;
                                                    sToast(data.success, `Request ${newMsg}`)
                                                } else {
                                                    sToast(data.success, "Failed")
                                                }
                                            })
                                            .catch(error => console.error('Error:', error));

                                    }
                                })
                            }
                        } else if (adData == "ed") {
                            const leavereaquestsdataList = document.getElementById("attendanceCalender");
                            leavereaquestsdataList.innerHTML = "Loading..........";
                            let flag2 = false;
                            data.classes.forEach(data1 => {
                                data1.students.forEach((sData) => {
                                    if (sData.student_id == clickedId) {
                                        sessionStorage.setItem("targetedChild", JSON.stringify(sData));
                                        flag2 = true;
                                        console.log(sData);
                                        leavereaquestsdataList.innerHTML = `<form id="changeStdData">
                                    <div class="input-group">
                                        <span class="input-group-text" id="id-addon">Id</span>
                                        <input name="id" type="text" class="form-control" placeholder="ID" aria-label="ID" aria-describedby="id-addon" value="${sData.student_id}" readonly>
                                    </div>
                                    <div class="form-text text-danger mb-3" id="basic-addon4">Id is not editable*</div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="name-addon">Name</span>
                                        <input name="name" type="text" class="form-control" placeholder="Name" aria-label="Name" aria-describedby="name-addon" value="${sData.name}">
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="mobile-addon">Mobile</span>
                                        <input name="number" type="text" class="form-control" placeholder="Mobile Number" aria-label="Mobile Number" aria-describedby="mobile-addon" value="${sData.phone_no}">
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="class-addon">Class</span>
                                        <input style="width: 1rem;"  name="class" type="text" class="form-control" placeholder="Class" aria-label="Class" aria-describedby="class-addon" value="${sData.clss_id}" readonly>
                                        <select class="form-select form-select-sm" aria-label="Small select example" id="classesListForStudentedit"></select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>`;
                                        let html = '';
                                        data.allClassInDb.forEach((ele) => {
                                            if (ele.class_id == sData.clss_id) {
                                                html += `<option value="${ele.class_id}" selected>${ele.name} (ID:${ele.class_id})</option>`;
                                            } else {
                                                html += `<option value="${ele.class_id}">${ele.name} (ID:${ele.class_id})</option>`;
                                            }
                                        });
                                        document.getElementById("classesListForStudentedit").innerHTML = html;
                                    }
                                })
                            });

                            if (flag2) {
                                const myform = document.getElementById("changeStdData");
                                myform.addEventListener("submit", (event) => {
                                    event.preventDefault();
                                    const getChild = JSON.parse(sessionStorage.getItem("targetedChild"));
                                    // console.log(getChild);
                                    // console.log(data.allClassInDb);
                                    const newClass = document.getElementById("classesListForStudentedit").value;

                                    if (myform.name.value == getChild.name && myform.number.value == getChild.phone_no & myform.class.value == newClass) {
                                        console.log("Not Changed");
                                    } else {
                                        if (!isNaN(myform.number.value)) {
                                            if (myform.number.value.length == 10) {
                                                fetch("./php/Tr/updateStu.php", {
                                                    method: "POST",
                                                    headers: {
                                                        "Content-Type": "application/json",
                                                    },
                                                    body: JSON.stringify({
                                                        id: myform.id.value,
                                                        name: myform.name.value,
                                                        phone: myform.number.value,
                                                        class: newClass
                                                    })
                                                })
                                                    .then((response) => response.json())
                                                    .then((data) => {
                                                        sToast(data.success, data.message);
                                                    })
                                                    .catch((error) => { });
                                            } else {
                                                // console.log("Mobile Number Must be 10 digit");
                                                sToast(0, "Mobile Number Must be 10 digit");
                                            }
                                        } else {
                                            // console.log("Mobile number is not numeric");
                                            sToast(0, "Mobile number is not numeric");
                                        }
                                    }
                                })
                            }
                        }
                    }
                });

                //Assignment Details section ====> old
                // const assignmentMainparent = document.getElementById("allAssign");
                // data.subjects.forEach((ele) => {
                //     let msg = "", main = "";
                //     //loop for all assignments in subject
                //     ele.assignments.forEach((asEle) => {
                //         msg = "";
                //         let lines = asEle.assignment_information.split("\n");
                //         lines.forEach((line) => {
                //             msg += `<small>${line}</small><br/>`;
                //         });
                //         main += `<li class="list-group-item d-flex justify-content-between align-items-start">
                //     <div class="ms-2 me-auto">
                //       <div class="fw-bold">Assignment No. <small id="assId-${asEle.assignment_id}">${asEle.assignment_id}</small> <small class="badge bg-primary rounded-pill p-2 mb-2">Due date : ${asEle.due_date}</small></div>
                //       <small>${msg}</small>
                //       <div class="d-flex justify-content-start align-items-center my-2">
                //         <button id="${ele.subject_id}-${ele.name}-subm-${asEle.assignment_id}-${ele.class}" type="button" style="width: 250px;" class="btn btn-warning me-2 p-0 px-2 d-flex justify-contect-center align-items-center" data-bs-toggle="modal" data-bs-target="#staticBackdropMain"><ion-icon name="analytics-outline" class="mx-2"></ion-icon>Submission Data</button>
                //         <button id="${ele.subject_id}-${ele.name}-del-${asEle.assignment_id}-${ele.class}" type="button" style="width: 250px;" class="btn btn-danger me-2 p-0 px-2 d-flex justify-contect-center align-items-center"><ion-icon name="trash-outline" class="mx-2"></ion-icon>Delete Assignment</button>
                //         <button id="${ele.subject_id}-${ele.name}-edit-${asEle.assignment_id}-${ele.class}" type="button" style="width: 250px;" class="btn btn-primary me-2 p-0 px-2 d-flex justify-contect-center align-items-center" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><ion-icon name="create-outline" class="mx-2"></ion-icon>Edit Details</button>
                //       </div>
                //     </div>
                //   </li>`;
                //     });

                //     //for each subject
                //     assignmentMainparent.innerHTML += `<div class="accordion-item">
                //         <h2 class="accordion-header">
                //         <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${ele.subject_id}" aria-expanded="false" aria-controls="collapseTwo">
                //             ${ele.name}
                //         </button>
                //         </h2>
                //         <div id="collapse${ele.subject_id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                //         <div class="accordion-body" id="${ele.subject_id}-body">
                //         <button id="${ele.subject_id}-${ele.name}-add" type="button" class="btn btn-success container-fluid my-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Add New Assignment</button>
                //             <ol class="list-group list-group-numbered" id="assinOfSub-${ele.subject_id}">${main}</ol>
                //         </div>
                //         </div>
                //      </div>`;
                //     })

                //Assignments event handlers (submision data, delete assignents, edit details)
                // const allAssign = document.getElementById("allAssign");
                const allAssign = document.getElementById("classAssignChat");
                allAssign.addEventListener("click", function (event) {
                    if (event.target.tagName === "BUTTONB") {
                        var [clickedId, name, type, aId, cId] = event.target.id.split("-");

                        // console.log(clickedId, name, type, aId, cId);
                        //if add assignment
                        if (type == "add") {
                            let flag3 = false;
                            data.subjects.forEach((Sele) => {
                                if (clickedId == Sele.subject_id) {
                                    flag3 = true;
                                    let html = '<option value="0" selected>Select Subject</option>';
                                    data.subjects.forEach((ele) => {
                                        html += `<option value="${ele.subject_id}">${ele.name} - ${ele.subject_id}</option>`;
                                    });
                                    const main = document.getElementById("attendanceCalender");
                                    main.innerHTML = `<form id="addAssignForm">
                                        <div class="input-group mb-3">
                                        <span class="input-group-text" id="subject-addon">Subject Id</span>
                                        <input type="text" class="form-control" placeholder="Enter Assignment ID" aria-label="Assignment ID" aria-describedby="assignment-addon" name="subId" value="${name} - ${clickedId}" readonly>
                                        </div>
                                    
                                        <div class="input-group mb-3">
                                        <span class="input-group-text" id="assignment-addon">Assignment ID (Random)</span>
                                        <input type="text" class="form-control" placeholder="Enter Assignment ID" aria-label="Assignment ID" aria-describedby="assignment-addon" name="assId">
                                        </div>
                                    
                                        <div class="input-group mb-3">
                                        <span class="input-group-text d-flex justify-contect-center align-items-start" id="description-addon">Assignment Description</span>
                                        <textarea style="height: 10rem" class="form-control" placeholder="Enter Assignment Description" aria-label="With textarea" name="des"></textarea>
                                        </div>
                                    
                                        <div class="input-group mb-3">
                                        <span class="input-group-text" id="date-addon">Due Date</span>
                                        <input type="date" class="form-control" aria-label="Due Date" aria-describedby="date-addon" name="dueDate">
                                        </div>
                                    
                                        <button type="submit" id="assignAdd" class="btn btn-primary">Submit</button>
                                    </form>`;
                                }
                            });

                            if (flag3) {
                                document.getElementById("assignAdd").addEventListener("click", (event) => {
                                    event.preventDefault();
                                    const form = document.getElementById("addAssignForm");
                                    if (!isNaN(form.assId.value)) {
                                        const data = {
                                            type: "add",
                                            assignment_id: form.assId.value,
                                            due_date: form.dueDate.value,
                                            assignment_information: form.des.value,
                                            sub_id: clickedId
                                        };

                                        fetch('./php/Tr/assign.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify(data),
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                sToast(data.success, data.message);
                                                $('#staticBackdrop').modal('hide');
                                            })
                                            .catch((error) => {
                                                sToast(0, "Failed !");
                                                console.error('Error:', error);
                                            });
                                    } else {
                                        sToast(0, "Assignment Id should be numeric");
                                    }
                                })
                            }

                        } else if (type == "del") {
                            // console.log(clickedId);
                            showConfirmation()
                                .then((confirmation) => {
                                    if (confirmation) {
                                        const data = {
                                            type: "del",
                                            assignment_id: aId
                                        }
                                        fetch('./php/Tr/assign.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify(data),
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    sToast(data.success, data.message, "Refreshing.....");
                                                    setTimeout(function () {
                                                        location.reload();
                                                    }, 1500);
                                                } else {
                                                    sToast(data.success, data.message);
                                                }
                                            })
                                            .catch((error) => {
                                                sToast(0, "Failed !");
                                                console.error('Error:', error);
                                            });
                                    }
                                });


                        } else if (type == "subm") {
                            document.getElementById("attendanceCalenderMain").innerHTML = "";
                            const dataBody = {
                                type: "subInfo",
                                assignment_id: aId
                            }
                            fetch('./php/Tr/assign.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(dataBody),
                            })
                                .then(response => response.json())
                                .then(dataSub => {
                                    if (dataSub.success) {
                                        // console.log("submition data:", dataSub.data);
                                        document.getElementById("attendanceCalenderMain").innerHTML = `
                                        <div id="assignmentStuCount"></div>
                                        <hr>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Id</th>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">File</th>
                                                </tr>
                                            </thead>
                                            <tbody id="subDetails"></tbody>
                                        </table>`
                                        let submitedCount = 0;
                                        let notSubmitedCount = 0;
                                        let participantsAssignments = 0;
                                        console.log(dataSub);
                                        const bodyAbtSub = document.getElementById("subDetails");
                                        let flag4 = false;
                                        data.classes.forEach((clsEle) => {
                                            if (clsEle.class_id == cId) {
                                                participantsAssignments = clsEle.students.length;
                                                clsEle.students.forEach((stuEle) => {
                                                    // console.log("student : ", stuEle);
                                                    let status = "Not Submited", date = "--", file = "--", rowCls = "table-danger";
                                                    dataSub.data.forEach((ele) => {
                                                        if (ele.stu_id == stuEle.student_id) {
                                                            submitedCount++;
                                                            flag4 = true;
                                                            status = "Submited";
                                                            date = ele.date;
                                                            file = `<a id="link:${stuEle.student_id}:${ele.fileName}" class="link-offset-2 link-underline link-underline-opacity-100" href="#"><ion-icon name="document-outline"></ion-icon> ${ele.fileName}</a>`;
                                                            rowCls = "table-success"
                                                        } else {
                                                            notSubmitedCount++;
                                                        }
                                                    });
                                                    bodyAbtSub.innerHTML += `<tr class="${rowCls}">
                                                <th scope="row">${stuEle.student_id}</th>
                                                <td>${stuEle.name}</td>
                                                <td>${status}</td>
                                                <td>${date}</td>
                                                <td>${file}</td>
                                            </tr>`;

                                                })
                                            }
                                        });
                                        document.getElementById("assignmentStuCount").innerHTML = `<div>Participants : ${participantsAssignments}</div>
                                        <div>Submitted : ${submitedCount}</div>
                                        <div>Not Submitted : ${notSubmitedCount}</div>`

                                        bodyAbtSub.addEventListener("click", (event) => {
                                            event.preventDefault();
                                            if (event.target.tagName == "A") {
                                                const [type, stId, fname] = event.target.id.split(":");
                                                // console.log(type, stId, fname);
                                                const formData = new FormData();
                                                formData.append('file_name', fname);
                                                formData.append('student_id', stId);

                                                // Fetch the file using the download.php script with POST method
                                                fetch('./php/Tr/getFile.php', {
                                                    method: 'POST',
                                                    body: formData,
                                                })
                                                    .then(response => {
                                                        if (!response.ok) {
                                                            // Handle HTTP errors
                                                            throw new Error(`HTTP error! Status: ${response.status}`);
                                                        }
                                                        return response.blob();
                                                    })
                                                    .then(blob => {
                                                        // Check if the response is valid
                                                        if (blob.size > 0) {
                                                            // Create a link element and trigger a click event to start the download
                                                            const link = document.createElement('a');
                                                            link.href = window.URL.createObjectURL(blob);
                                                            link.download = fname;
                                                            document.body.appendChild(link); // Append the link to the document
                                                            link.click();
                                                            document.body.removeChild(link); // Remove the link from the document after click
                                                        } else {
                                                            // Handle the case where the file is not found or is empty
                                                            console.error('File not found or empty.');
                                                        }
                                                    })
                                                    .catch(error => console.error('Fetch error:', error));
                                            }
                                        });
                                        // console.log(clickedId, name, type, aId, cId);

                                    } else {
                                        sToast(dataSub.success, dataSub.message);
                                    }
                                })
                                .catch((error) => {
                                    sToast(0, "Failed !");
                                    console.error('Error:', error);
                                });


                        } else if (type == "edit") {
                            // console.log(clickedId, name, type, aId, cId);
                            let myAssign = "";
                            data.subjects.forEach((sEle) => {
                                if (sEle.subject_id == clickedId) {
                                    sEle.assignments.forEach((aEle) => {
                                        if (aEle.assignment_id == aId) {
                                            myAssign = aEle;
                                        }
                                    })
                                }
                            })
                            // console.log(myAssign);
                            document.getElementById("attendanceCalender").innerHTML = `<form id="assignmentEditForm">
                        <div class="input-group">
                          <span class="input-group-text" id="basic-addon1">Assignment ID</span>
                          <input name="id" type="text" class="form-control" placeholder="Enter Assignment ID" aria-label="Assignment ID" aria-describedby="basic-addon1" value="${myAssign.assignment_id}" readonly>
                        </div>
                        <div class="form-text text-danger mb-3" id="basic-addon4">Id is not editable*</div>
                        <div class="input-group mb-3">
                          <span class="input-group-text d-flex justify-contect-center align-items-start" id="basic-addon2">Assignment Info</span>
                          <textarea name="des" class="form-control" style="height: 10rem" placeholder="Enter Assignment Info" aria-label="Assignment Info" aria-describedby="basic-addon2">${myAssign.assignment_information}</textarea>
                        </div>
                        <div class="input-group mb-3">
                          <span class="input-group-text" id="basic-addon3">Due Date</span>
                          <input name="date" type="date" class="form-control" aria-label="Due Date" aria-describedby="basic-addon3" value="${myAssign.due_date}">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </form>`;

                            const form = document.getElementById("assignmentEditForm");
                            form.addEventListener("submit", (event) => {
                                event.preventDefault();
                                const data = {
                                    type: "update",
                                    assignment_id: form.id.value,
                                    due_date: form.date.value,
                                    assignment_information: form.des.value
                                };

                                fetch('./php/Tr/assign.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify(data),
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        sToast(data.success, data.message);
                                        $('#staticBackdrop').modal('hide');
                                    })
                                    .catch((error) => {
                                        sToast(0, "Failed !");
                                        console.error('Error:', error);
                                    });
                            })

                        }

                    }
                })

                //Parents teachers communication section ====>
                const trId = data.dataTr.teacher_id;
                const parentSec = document.getElementById("parsec");
                parentSec.innerHTML = ``;
                data.classes.forEach((classEle) => {
                    parentSec.innerHTML += `<div class="accordion-item">
                    <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" style="width: 100%;"
                    data-bs-target="#collapse-${classEle.class_id}" aria-expanded="false"
                    aria-controls="collapse${classEle.class_id}">
                    ${classEle.name} <span id="showDotForMessage-${classEle.class_id}" class=""></span>
                    </button>
                    </h2>
                    <div id="collapse-${classEle.class_id}" class="accordion-collapse collapse" data-bs-parent="#parsec">
                    <div class="accordion-body">
                    <div class="list-group" id="stuData-${classEle.class_id}"></div>
                    </div>
                    </div>
                    </div>`;
                    let checkUnreadinClass = false;
                    classEle.students.forEach((stuEle) => {
                        let unread = 0;
                        let notiHtml;
                        const commData = JSON.parse(stuEle.comm);
                        if (commData.length > 0) {
                            commData.forEach((chat) => {
                                // console.log(data.dataTr.teacher_id);
                                if (!chat.read && (chat.sender == "student") && (chat.teacher_id == data.dataTr.teacher_id)) {
                                    unread++;
                                }
                            })
                            notiHtml = unread > 0 ? notiHtml = `<span class="badge rounded-pill text-bg-warning">${unread} New Message</span>` : "";
                        }

                        if (notiHtml) {
                            checkUnreadinClass = true;
                            document.getElementById(`stuData-${classEle.class_id}`).innerHTML += `<a href="#" id="${stuEle.student_id}-inlist" class="list-group-item list-group-item-action">${stuEle.name} ${notiHtml}</a>`;
                        } else {
                            document.getElementById(`stuData-${classEle.class_id}`).innerHTML += `<a href="#" id="${stuEle.student_id}-inlist" class="list-group-item list-group-item-action">${stuEle.name}</a>`;
                        }
                    });
                    if (checkUnreadinClass) {
                        document.getElementById(`showDotForMessage-${classEle.class_id}`).className = "badge rounded-pill text-bg-warning mx-1";
                        document.getElementById(`showDotForMessage-${classEle.class_id}`).innerHTML = "New Message";
                    }
                });

                document.getElementById("parsec").addEventListener("click", (event) => {
                    if (event.target.tagName == "A") {
                        let messages = '';
                        const [stuId, btnName] = event.target.id.split("-");
                        // console.log(stuId);
                        document.getElementById("msgHandle").innerHTML = `<input type="text" id="message-input" placeholder="Type your message...">
                        <button id="msg-send-button"><span id="myElement" class="spinner-border spinner-border-sm" aria-hidden="true"></span> Send</button>`;
                        document.getElementById("myElement").style.visibility = "hidden";
                        async function fetchComm(studentId) {
                            try {
                                const data = {
                                    type: "call",
                                    student_id: studentId,
                                    sender: "teacher"
                                };

                                const response = await fetch('./php/comm/comm.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify(data),
                                });

                                if (!response.ok) {
                                    console.error('Error:', response.statusText);
                                    return;
                                }

                                //all communication(of all teachers)
                                const result = await response.json();
                                // console.log(JSON.parse(JSON.stringify(result)));
                                const allComm = JSON.parse(JSON.stringify(result));
                                console.log(allComm);

                                //filter for this teacher
                                // const res = JSON.parse(allComm.commData).commData.filter(ele => ele.teacher_id == trId);
                                const res = JSON.parse(allComm.commData) ? JSON.parse(allComm.commData).filter(ele => ele.teacher_id == trId) : [];
                                // console.log(JSON.parse(allComm.commData));
                                if (allComm.success) {
                                    messages = res;

                                    // function displayMessages() {
                                    //     const chatBox = document.getElementById('chat-box');
                                    //     chatBox.innerHTML = '';

                                    //     messages.slice().reverse().forEach(message => { // Reverse the order of messages before rendering
                                    //         const messageDiv = document.createElement('div');
                                    //         messageDiv.classList.add('message', message.sender);

                                    //         const contentDiv = document.createElement('div');
                                    //         contentDiv.classList.add('message-content');
                                    //         contentDiv.textContent = message.content;

                                    //         const timestampDiv = document.createElement('div');
                                    //         timestampDiv.classList.add('message-timestamp');
                                    //         timestampDiv.textContent = formatTimestamp(message.timestamp);

                                    //         messageDiv.appendChild(contentDiv);
                                    //         messageDiv.appendChild(timestampDiv);

                                    //         chatBox.appendChild(messageDiv);
                                    //     });
                                    // }

                                    displayMessages(messages);
                                    document.getElementById("msg-send-button").addEventListener("click", (event) => {
                                        document.getElementById("myElement").style.visibility = "visible";
                                        const messageInput = document.getElementById('message-input');
                                        const newMessage = {
                                            sender: 'teacher',
                                            content: messageInput.value,
                                            timestamp: new Date().toISOString(),
                                            read: false,
                                            teacher_id: trId
                                        };

                                        //message
                                        const msgdata = {
                                            type: "add",
                                            student_id: stuId,
                                            "newMessage": newMessage
                                        };

                                        // console.log(msgdata);

                                        fetch('php/comm/comm.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify(msgdata),
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                messages.push(newMessage);
                                                displayMessages(messages);
                                                messageInput.value = '';
                                                // console.log(data);
                                                document.getElementById("myElement").style.visibility = "hidden";
                                            })
                                            .catch(error => {
                                                document.getElementById("myElement").style.visibility = "hidden";
                                                console.error('Error:', error);
                                            });
                                    })

                                }

                            } catch (error) {
                                console.error('Error:', error);
                            }
                        }
                        fetchComm(stuId);
                        $('#exampleModal-ptc').modal('show');

                        // console.log("needed");
                        // console.log(data.dataTr.teacher_id);
                        // console.log(stuId);

                        //live chat check call
                        function checkForChanges() {
                            fetch('php/comm/comm.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    type: "check",
                                    student_id: stuId,
                                    sender: "teacher",
                                    teacher: data.dataTr.teacher_id
                                }),
                            })
                                .then(response => response.json())
                                .then(msgData => {
                                    // console.log(msgData);
                                    if (msgData.changed) {
                                        const allComm = JSON.parse((msgData.message));
                                        // console.log(allComm, teacherId)
                                        const res = allComm ? allComm.filter(ele => ele.teacher_id == data.dataTr.teacher_id) : [];
                                        displayMessages(res);
                                        messages = res;
                                        console.log(msgData);
                                    } else {
                                        console.log(msgData);
                                    }
                                })
                                .catch(error => {
                                    // document.getElementById("myElement").style.visibility = "hidden";
                                    console.error('Error:', error);
                                });
                        }
                        setInterval(checkForChanges, 2500);

                        function displayMessages(msg) {
                            const chatBox = document.getElementById('chat-box');
                            chatBox.innerHTML = '';

                            msg.slice().reverse().forEach(message => { // Reverse the order of messages before rendering
                                const messageDiv = document.createElement('div');
                                messageDiv.classList.add('message', message.sender);

                                const contentDiv = document.createElement('div');
                                contentDiv.classList.add('message-content');
                                contentDiv.textContent = message.content;

                                const timestampDiv = document.createElement('div');
                                timestampDiv.classList.add('message-timestamp');
                                timestampDiv.textContent = formatTimestamp(message.timestamp);

                                messageDiv.appendChild(contentDiv);
                                messageDiv.appendChild(timestampDiv);

                                chatBox.appendChild(messageDiv);
                            });
                        }
                    }
                })

                //If any new leave request comes
                document.getElementById("newLR").addEventListener("click", (event) => {
                    $('#staticBackdrop').modal('show');
                    const leavereaquestsdataList = document.getElementById("attendanceCalender");
                    leavereaquestsdataList.innerHTML = `<ol class="list-group list-group-numbered" id="listOfLeaveReaquests"></ol>`;
                    let flag5 = false;
                    data.classes.forEach((clsEle) => {
                        clsEle.students.forEach((stuEle) => {
                            JSON.parse(stuEle.leave_request).requests.forEach((ele, i) => {
                                if (ele.status == 101) {
                                    // console.log(stuEle.name, "==>", reqs);
                                    //inner
                                    flag5 = true;
                                    let cls, msg;
                                    if (ele.status == 100) {
                                        msg = "Accepted";
                                        cls = "bg-success";
                                    } else if (ele.status == 101) {
                                        msg = "Pending";
                                        cls = "bg-warning";
                                    } else if (ele.status == 102) {
                                        msg = "Rejected";
                                        cls = "bg-danger";
                                    }
                                    let dayOrDays = dateDifferenceInDays(ele.from, ele.to) > 1 ? "Days" : "Day";
                                    document.getElementById("listOfLeaveReaquests").innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">   
                                            <p class="m-0 p-0">Name: ${stuEle.name}</p>
                                            <p class="m-0 p-0">Class: ${stuEle.clss_id}</p>
                                            <p class="m-0 p-0">Duration : ${ele.from} - ${ele.to} / ${dateDifferenceInDays(ele.from, ele.to)} ${dayOrDays}</p>
                                            <small>Reason: ${ele.reason}</small><br/>
                                            <p style="width: 6rem;" id="${i}-status" class="${cls} rounded d-flex justify-content-center align-items-center m-2" >${msg}</p>
                                            <small></small>
                                            <div class="d-flex justify-content-start align-items-center">
                                            <button type="button" id="${i}-100-${stuEle.student_id}-${stuEle.clss_id}" class="btn btn-success d-flex justify-content-center align-items-center mx-2"><ion-icon name="checkmark-outline"></ion-icon>Accept</button>
                                            <button type="button" id="${i}-102-${stuEle.student_id}-${stuEle.clss_id}" class="btn btn-danger d-flex justify-content-center align-items-center"><ion-icon name="close-outline"></ion-icon>Reject</button>
                                            </div>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">Requested at : ${convertDateFormat(ele.requestedAt)}</span>
                                        </li>`;
                                    //inner
                                }
                            });
                        })
                    })
                    if (flag5) {
                        const LRListData = document.getElementById("listOfLeaveReaquests");
                        LRListData.addEventListener("click", function (event) {
                            if (event.target.tagName === "BUTTON") {
                                let requestBodyPending = {};
                                var [clickedId, adData, stuId, clsId] = event.target.id.split("-");
                                let getStu = data.classes.find(cls => cls.class_id === clsId).students.find(stu => stu.student_id == stuId);
                                if (requestBodyPending = JSON.parse(getStu.leave_request).requests[clickedId]) {
                                    // console.log('Found Class:', getStu.student_id);
                                    let id = getStu.student_id; // replace with actual student ID
                                    requestBodyPending = Object.assign({}, requestBodyPending, { "newStatus": parseInt(adData), "id": id });
                                    fetch('./php/Tr/leaverequest.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify(requestBodyPending),
                                    })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                let newclsNm, newMsg;
                                                if (adData == 100) {
                                                    newMsg = "Accepted";
                                                    newclsNm = "bg-success rounded d-flex justify-content-center align-items-center mx-2";
                                                } else if (adData == 102) {
                                                    newMsg = "Rejected";
                                                    newclsNm = "bg-danger rounded d-flex justify-content-center align-items-center mx-2";
                                                }
                                                document.getElementById(`${clickedId}-status`).className = newclsNm;
                                                document.getElementById(`${clickedId}-status`).innerText = newMsg;
                                                sToast(data.success, `Request ${newMsg}`)
                                            } else {
                                                sToast(data.success, "Failed")
                                            }
                                        })
                                        .catch(error => console.error('Error:', error));
                                }

                            }
                        })
                    }
                })

                //check new requests (to display)
                document.getElementById("newLR").innerHTML = ``;
                data.classes.forEach((clsEle) => {
                    clsEle.students.forEach((stuEle) => {
                        JSON.parse(stuEle.leave_request).requests.forEach((ele, i) => {
                            if (ele.status == 101) {
                                document.getElementById("newLR").innerHTML = `Requests 
                            <span class="badge text-bg-warning">NEW</span>`;
                                return;
                            }
                        })
                    })
                })

                //end successful responce
            } else {
                console.log("Teachers data Not available")
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });

    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal._element.addEventListener('hidden.bs.modal', function () {
        localStorage.clear("listLeaveReaquests");
        localStorage.removeItem("listLeaveReaquests");
        location.reload();
    });

    var myModal2 = new bootstrap.Modal(document.getElementById('exampleModal-ptc'));
    myModal2._element.addEventListener('hidden.bs.modal', function () {
        location.reload();
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

function dateDifferenceInDays(date1, date2) {
    const [day1, month1, year1] = date1.split('-').map(Number);
    const [day2, month2, year2] = date2.split('-').map(Number);
    const firstDate = new Date(year1, month1 - 1, day1);
    const secondDate = new Date(year2, month2 - 1, day2);
    const timeDifference = secondDate - firstDate;
    const daysDifference = Math.floor(timeDifference / (24 * 60 * 60 * 1000));
    return daysDifference + 1;
}

function convertDateFormat(inputDate) {
    const [day, month, year] = inputDate.split("-");
    const newDate = new Date(year, month - 1, day);
    const monthName = newDate.toLocaleString("en-US", {
        month: "long",
    });
    const formattedDate = `${newDate.getDate()} ${monthName} ${newDate.getFullYear()}`;
    return formattedDate;
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

function showConfirmation(msg = "This action cannot be undone") {
    return new Promise((resolve) => {
        Swal.fire({
            title: 'Are you sure?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(true);
            } else {
                resolve(false);
            }
        });
    });
}

function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
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