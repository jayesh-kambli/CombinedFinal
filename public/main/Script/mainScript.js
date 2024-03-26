document.addEventListener("DOMContentLoaded", function () {
  // console.log(JSON.parse(sessionStorage.getItem('data')).student_id);
  function CallAll() {
    fetch("./php/mainpage.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: JSON.stringify({
        data: JSON.parse(sessionStorage.getItem("data")).student_id,
        user: sessionStorage.getItem("user"),
        pass: sessionStorage.getItem("pass"),
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          let storeHtml;
          if (data.student.ppass == "") {
            // document.getElementById("currentPassSection").style.display = 'block';
            storeHtml = document.getElementById("currentPassSection").innerHTML;
            document.getElementById("currentPassSection").innerHTML = "";
            $('#exampleModalforResetParentPass').modal('show');
          }
          console.log(data.student)
          document.getElementById("nameOfStudent").innerHTML = `Hello, ${data.student.name}`
          const allAttendanceData = JSON.parse(
            data.attendance.attendance_data
          ).atData;
          // console.log(allAttendanceData);
          fetch("./php/assign.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: JSON.stringify({
              info: "classInfo",
              classId: data.student.clss_id,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              let [yyyy, mm, dd] = data.data[0].start_id.split("-");
              const userDate = `${mm}-${dd}-${yyyy}`;
              const mainChij = calculatePer(allAttendanceData, userDate);
              document.getElementById("catten").innerHTML = ` Classes Attended ${mainChij}%`
              document.getElementById("cnotstten").innerHTML = ` Classes Not Attended ${100 - mainChij}%`
              // console.log(mainChij);
              new Chart(document.getElementById("pie-chart"), {
                type: "pie",
                data: {
                  // labels: ['Present', 'Not Present'],
                  datasets: [
                    {
                      label: "Attendance (millions)",
                      backgroundColor: ["#0d0c22", "#ffae42 "],
                      data: [100 - mainChij, mainChij],
                    },
                  ],
                },
                options: {
                  title: {
                    display: false,
                    text: "Predicted world population (millions) in 2050",
                  },
                  tooltips: {
                    enabled: false
                  }
                },
              });
              document.getElementById("pie-chart").style.height = "30em";
              document.getElementById("pie-chart").style.width = "30em";
              // document.getElementById("pie-chart").style.fontSize = '1em';
            })
            .catch((error) => {
              console.error("Error:", error);
            });

          // console.log(JSON.parse(data.student.leave_request).requests);
          sessionStorage.setItem(
            "leaveReqdata",
            JSON.stringify(JSON.parse(data.student.leave_request).requests)
          );
          var atData = JSON.parse(data.attendance.attendance_data).atData;

          function getDaysDifference(targetDateStr) {
            const [day, month, year] = targetDateStr.split("-");
            const targetDate = new Date(year, month - 1, day);
            const today = new Date();
            const timeDifference = today.getTime() - targetDate.getTime();
            const daysDifference = Math.ceil(
              timeDifference / (1000 * 60 * 60 * 24)
            );
            return daysDifference + 1;
          }

          //late arrivals
          function compareTime(timeString) {
            // console.log(timeString);
            const [hours, minutes] = timeString.split(":").map(Number);
            const inputTimeInMinutes = hours * 60 + minutes;
            const referenceTimeInMinutes = 9 * 60;
            if (inputTimeInMinutes > referenceTimeInMinutes) {
              return 0; // Input time is later than 9:00 AM
            } else {
              return 1; // Input time is 9:00 AM or earlier
            }
          }

          let lateParent = document.getElementById("lateArrGroup");
          lateParent.innerHTML = "";
          atData.forEach((ele) => {
            // console.log(ele);
            ele["days"].forEach((elem, i) => {
              // console.log(i+1 + "-" + ele.yearMonth+ "==>" +elem + "==>" + ele["times"][i]);
              if (!compareTime(ele["times"][i])) {
                let diffDays = getDaysDifference(`${i + 1}-${ele.yearMonth}`);
                lateParent.innerHTML += `<a href="#" class="list-group-item list-group-item-action">
                      <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Date : ${i + 1}-${ele.yearMonth}</h5>
                        <small class="text-body-secondary">${diffDays} ${diffDays > 1 ? "Days" : "Day"
                  } ago</small>
                      </div>
                      <p class="mb-1">Arrived at : ${ele["times"][i]}</p>
                    </a>
                  `;
              }
            });
          });

          //leave reaquests
          function dateDiffInDays(dateString1, dateString2) {
            const date1 = new Date(dateString1.split("-").reverse().join("-"));
            const date2 = new Date(dateString2.split("-").reverse().join("-"));
            const timeDifference = date2 - date1;
            const daysDifference = timeDifference / (1000 * 60 * 60 * 24);
            return Math.floor(daysDifference) + 1;
          }
          // console.log(data.student);
          let leaveReqParent = document.getElementById("leaveReqGroup");
          leaveReqParent.innerHTML = "";
          let leaveReqData = JSON.parse(data.student.leave_request);

          if (
            leaveReqData &&
            leaveReqData.requests &&
            Array.isArray(leaveReqData.requests)
          ) {
            // console.log(leaveReqData.requests);
            leaveReqData.requests.forEach((request) => {
              let classNm =
                request.status == 101
                  ? "bg-warning"
                  : request.status == 102
                    ? "bg-danger"
                    : "bg-success";
              let statusMessage =
                request.status == 101
                  ? "Pending"
                  : request.status == 102
                    ? "Rejected"
                    : "Accepted";
              // Perform operations with each leave request
              // console.log(request);
              let dayOrDays = dateDiffInDays(request.from, request.to) > 1 ? "Days" : "Day";
              leaveReqParent.innerHTML += `<a href="#" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                  <p class="mb-1">${request.reason}</p>
                    <small class="text-body-secondary">${request.requestedAt
                }</small>
                  </div>
                  Interval: <small>${request.from} - ${request.to
                } / ${dateDiffInDays(request.from, request.to)} ${dayOrDays}</small><br>
                  Status: <small class="${classNm} p-1 rounded">${statusMessage}</small>
                </a>`;
            });
          } else {
            console.error("Invalid leave request data format");
          }

          //additional abset work
          function getStatusForDate(dateToCheck, rangeArray) {
            const [checkDay, checkMonth, checkYear] = dateToCheck.split("-");
            const checkDate = new Date(
              `${checkMonth}-${checkDay}-${checkYear}`
            );

            for (const request of rangeArray) {
              const [fromDay, fromMonth, fromYear] = request.from.split("-");
              const [toDay, toMonth, toYear] = request.to.split("-");
              const fromDate = new Date(`${fromMonth}-${fromDay}-${fromYear}`);
              const toDate = new Date(`${toMonth}-${toDay}-${toYear}`);
              if (checkDate >= fromDate && checkDate <= toDate) {
                return [request.status, true];
              }
            }
            return [null, false];
          }

          //absents ====>
          let parent = document.getElementById("ablist");
          parent.innerHTML = "";
          atData.forEach((ele) => {
            let dateOfToday = ele.yearMonth; //as per data
            // console.log(ele.yearMonth);

            ele["days"].forEach((elem, i) => {
              // start =====
              // console.log(`${i+1}-`+dateOfToday);
              if (isDateBeforeToday(`${i+1}-`+dateOfToday)) {
                if (leaveReqData && leaveReqData.requests && Array.isArray(leaveReqData.requests)) {
                  // console.log(leaveReqData.requests);
                  // leaveReqData.requests.forEach((request) => {
                  // });
                  const givenDate = `${i + 1}-${ele.yearMonth}`; // Format: "dd-MM-yyyy"
                  const [status, isDatePresent] = getStatusForDate(
                    givenDate,
                    leaveReqData.requests
                  );
                  if (isDatePresent) {
                    var clsNm =
                      status == 101
                        ? "bg-warning"
                        : status == 102
                          ? "bg-danger"
                          : "bg-success";
                    var msg =
                      status == 101
                        ? "Request Pending"
                        : status == 102
                          ? "Requested But Rejected"
                          : "Requested And Approved";
                  } else {
                    var clsNm = "bg-info";
                    var msg = "Not Requested";
                  }
                  //subcode =====
                  if (elem == 0) {
                    let diffDays = getDaysDifference(`${i + 1}-${ele.yearMonth}`);
                    parent.innerHTML += `<a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                          <h5 class="mb-1">Date : ${i + 1}-${ele.yearMonth}</h5>
                          <small class="text-body-secondary">${diffDays} ${diffDays > 1 ? "Days" : "Day"
                      } ago</small>
                        </div>
                        <small class="${clsNm} p-1 rounded">${msg}</small>
                      </a>`;
                  }
                  //subcode =====
                } else {
                  console.error("Invalid leave request data format");
                }
              }
              //end =====
            });
          });
          let sortedAbsentList = "";
          Array.from(document.getElementById("ablist").children).reverse().forEach((ele) => {
            sortedAbsentList+=`<a href="#" class="list-group-item list-group-item-action">`+ele.innerHTML+`</a>`;
            // console.log(ele.innerHTML);
          })
          document.getElementById("ablist").innerHTML = sortedAbsentList;
          //absents end ====>

          //profile
          const sData = JSON.parse(sessionStorage.getItem("data"));
          const proParent = document.getElementById("modal-body-profile");
          document.getElementById("CPemail").value = sData.email;
          // console.log(JSON.parse(sessionStorage.getItem("data")));
          proParent.innerHTML = `<div class="d-flex align-items-center"><ion-icon name="person-circle-outline"></ion-icon><small class="mx-1"> Name: ${sData.name}</small><br/><br/></div>
            <div class="d-flex justify-content-start align-items-center"><ion-icon name="school-outline"></ion-icon> <small class="mx-1"> Class: ${sData.clss_id}</small><br/><br/></div>
            <div class="d-flex justify-content-start align-items-center"><ion-icon name="id-card-outline"></ion-icon> <small class="mx-1"> Student id : ${sData.student_id}</small><br/><br/></div>
            <div class="d-flex justify-content-start align-items-center"><ion-icon name="mail-open-outline"></ion-icon> <small class="mx-1"> email: ${sData.email}</small><br/><br/></div>
            <div class="d-flex justify-content-start align-items-center"><ion-icon name="call-outline"> </ion-icon><small class="mx-1"> Contact : ${sData.phone_no}</small><br/><br/></div>`;

          var unlockButton = document.getElementById('unlockButton');
          var lockButton = document.getElementById('lockButton');
          var lockedSection = document.getElementById('lockedSection');
          var lockedOverlay = document.getElementById('lockedOverlay');

          document.getElementById("buttonForParentPassCheck").addEventListener("click", (event) => {
            console.log(document.getElementById("btnGroupAddOnforPar").value, data.student.ppass)
            if (data.student.ppass == document.getElementById("btnGroupAddOnforPar").value) {
              document.getElementById("btnGroupAddOnforPar").value = "";
              iziToast.success({
                title: "Unlocked",
                message: "",
                position: "topLeft",
              });
              lockedSection.classList.remove('locked-section');
              lockedOverlay.style.display = 'none';
              $('#exampleModalforParentPass').modal('hide');
            } else {
              iziToast.error({
                title: "Wrong Password",
                message: "",
                position: "topLeft",
              });
            }
          });

          lockButton.addEventListener('click', function () {
            lockedSection.classList.add('locked-section');
            lockedOverlay.style.display = 'flex';
          });

          document.getElementById("buttonForParentPassUpdate").addEventListener('click', (event) => {
            validSym("btnGroupAddOnforCurrPass");
            validSym("btnGroupAddOnforNewPass");
            validSym("btnGroupAddOnforNewPass2");
            const currPass = document.getElementById("btnGroupAddOnforCurrPass") ? document.getElementById("btnGroupAddOnforCurrPass").value : "";
            const newPass = document.getElementById("btnGroupAddOnforNewPass").value;
            const newPass2 = document.getElementById("btnGroupAddOnforNewPass2").value;
            function clearValue(id) {
              if (document.getElementById(id)) {
                document.getElementById(id).value = "";
              }
            }
            if (currPass == data.student.ppass) {
              if (newPass == newPass2) {
                if (newPass != currPass) {
                  async function updateStudentPass(studentId, newPass) {
                    try {
                      const response = await fetch('./php/chParPass.php', {
                        method: 'POST',
                        headers: {
                          'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                          student_id: studentId,
                          ppass: newPass
                        })
                      });

                      const responseData = await response.json(); // Parse response as JSON
                      if (responseData.success) {
                        iziToast.success({
                          title: "Password Changed",
                          message: "",
                          position: "topLeft",
                        });
                        clearValue("btnGroupAddOnforCurrPass");
                        clearValue("btnGroupAddOnforNewPass");
                        clearValue("btnGroupAddOnforNewPass2");
                        data.student.ppass = newPass2;
                        // document.getElementById("currentPassSection").style.display = 'visible';
                        if (storeHtml) {
                          document.getElementById("currentPassSection").innerHTML = storeHtml;
                        }
                        $('#exampleModalforResetParentPass').modal('hide');
                        // console.log('Password updated successfully');
                      } else {
                        console.error('Error:', responseData.error);
                      }
                    } catch (error) {
                      console.error('Error:', error);
                    }
                  }
                  updateStudentPass(data.student.student_id, newPass);
                } else {
                  iziToast.warning({
                    title: "Old & New password can't be same",
                    message: "",
                    position: "topLeft",
                  });
                  InvalidSym("btnGroupAddOnforCurrPass");
                  InvalidSym("btnGroupAddOnforNewPass");
                  InvalidSym("btnGroupAddOnforNewPass2");
                }
              } else {
                iziToast.error({
                  title: "New password dosent match",
                  message: "",
                  position: "topLeft",
                });
                InvalidSym("btnGroupAddOnforNewPass");
                InvalidSym("btnGroupAddOnforNewPass2");
              }
            } else {
              iziToast.error({
                title: "Incorrect Current Password",
                message: "",
                position: "topLeft",
              });
              InvalidSym("btnGroupAddOnforCurrPass");
            }
          });

        } else {
          console.log("Login Again");
        }


        //parents teacher communication ====>
        console.log(JSON.stringify({ class_id: data.student.clss_id }));
        // Make a POST request to the PHP script
        fetch('./php/chat.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ "class_id": data.student.clss_id })
        })
          .then(response => response.json())
          .then(trsData => {
            // console.log("needed");
            // console.log(trsData);

            const parentSec = document.getElementById("parsec");
            parentSec.innerHTML = ``;
            trsData.teachers.forEach((trsEle) => {
              let unread = 0;
              const allChats = JSON.parse(data.student.comm);
              allChats.forEach((chat) => {
                if (!chat.read && (chat.sender == "teacher")) {
                  unread++;
                }
              })
              if (unread > 0) {
                parentSec.innerHTML += `<a href="#" id="${trsEle.teacher_id}-inlist" class="list-group-item list-group-item-action">${trsEle.name} <span class="badge rounded-pill text-bg-warning">${unread} New Message</span></a>`;
              } else {
                parentSec.innerHTML += `<a href="#" id="${trsEle.teacher_id}-inlist" class="list-group-item list-group-item-action">${trsEle.name}</a>`;
              }
            });
            //start ==>
            document.getElementById("parsec").addEventListener("click", (event) => {
              if (event.target.tagName == "A") {
                const [teacherId, btnName] = event.target.id.split("-");
                // console.log(teacherId);
                document.getElementById("msgHandle").innerHTML = `<input type="text" id="message-input" placeholder="Type your message...">
                  <button id="msg-send-button"><span id="myElement" class="spinner-border spinner-border-sm" aria-hidden="true"></span> Send</button>`;
                document.getElementById("myElement").style.visibility = "hidden";
                let messages = '';
                async function fetchComm(studentId) {
                  try {
                    const data = {
                      type: "call",
                      student_id: studentId,
                      sender: "student"
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
                    // console.log(result);
                    // console.log(JSON.parse(JSON.stringify(result)));
                    const allComm = JSON.parse(JSON.stringify(result));

                    //filter for this teacher
                    const res = JSON.parse(allComm.commData) ? JSON.parse(allComm.commData).filter(ele => ele.teacher_id == teacherId) : [];

                    if (allComm.success) {
                      messages = res;


                      displayMessages(messages);
                      document.getElementById("msg-send-button").addEventListener("click", (event) => {
                        document.getElementById("myElement").style.visibility = "visible";
                        const messageInput = document.getElementById('message-input');
                        const newMessage = {
                          sender: 'student',
                          content: messageInput.value,
                          timestamp: new Date().toISOString(),
                          read: false,
                          teacher_id: teacherId
                        };

                        //message
                        const msgdata = {
                          type: "add",
                          student_id: data.student_id,
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
                console.log(data.student.student_id);
                fetchComm(data.student.student_id);
                $('#exampleModal-ptc').modal('show');

                //live chat check call
                function checkForChanges() {
                  fetch('php/comm/comm.php', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                      type: "checkStu",
                      student_id: data.student.student_id,
                      sender: "student",
                      teacher: teacherId
                    }),
                  })
                    .then(response => response.json())
                    .then(msgData => {
                      // console.log(msgData);
                      if (msgData.changed) {
                        const allComm = JSON.parse((msgData.message));
                        // console.log(allComm, teacherId)
                        const res = allComm ? allComm.filter(ele => ele.teacher_id == teacherId) : [];
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
                // checkForChanges();
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
            //end ==>
          })
          .catch(error => {
            console.error('Error:', error);
          });

      })
      .catch((error) => {
        console.error("Error:", error);
      });
  } //fetch end

  CallAll();

  //chage Pass
  const passForm = document.getElementById("passForm");
  passForm.addEventListener("submit", (event) => {
    event.preventDefault();

    if (passForm.cpass1.value == passForm.cpass2.value) {
      if (!(passForm.pass.value == passForm.cpass1.value)) {
        // fetch change pass
        fetch("./php/chPass.php", {
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
          .then((response) => {
            if (!response.ok) {
              iziToast.error({
                title: "Failed",
                message: "Error1 !",
                position: "topLeft",
              });
            }
            return response.json();
          })
          .then((data) => {
            if (data.success) {
              iziToast.success({
                title: "Success",
                message: data.message,
                position: "topLeft",
              });
              setTimeout(function () {
                window.location.href = "index.html";
              }, 1500);
            } else {
              iziToast.error({
                title: "Failed",
                message: data.message,
                position: "topLeft",
              });
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            iziToast.error({
              title: "Failed",
              message: "Error !",
              position: "topLeft",
            });
          }); //fetch end change pass
      } else {
        iziToast.error({
          title: "Failed",
          message: "Password cant be same as earlier!",
          position: "topLeft",
        });
      }
    } else {
      iziToast.error({
        title: "Failed",
        message: "New password does not match !",
        position: "topLeft",
      });
    }
  });

  //leave
  const form = document.getElementById("leaveApprovalForm");
  form.addEventListener("submit", function (event) {
    event.preventDefault();
    function isDate1GreaterThanDate2(dateString1, dateString2) {
      const [day1, month1, year1] = dateString1.split("-").map(Number);
      const [day2, month2, year2] = dateString2.split("-").map(Number);

      console.log(dateString1);

      if (
        year1 > year2 ||
        (year1 === year2 &&
          (month1 > month2 || (month1 === month2 && day1 > day2)))
      ) {
        return true;
      }

      return false;
    }
    function reformatDate(inputDate) {
      return inputDate ? inputDate.split("-").reverse().join("-") : null;
    }

    if (
      isDate1GreaterThanDate2(
        reformatDate(
          document.getElementById("exampleDropdownFormEmail1").value
        ),
        reformatDate(document.getElementById("exampleDropdownFormEmail2").value)
      )
    ) {
      iziToast.error({
        title: "Failed",
        message: "Check Date Input ",
        position: "topLeft",
      });
    } else {
      if (document.getElementById("exampleFormControlTextarea1").value == "") {
        iziToast.error({
          title: "Failed",
          message: "Enter Message/Reason",
          position: "topLeft",
        });
      } else {
        // Retrieve existing data from sessionStorage
        let existingData = sessionStorage.getItem("leaveReqdata");
        let leaveReqDataArray = existingData ? JSON.parse(existingData) : [];

        //todays date
        const today = new Date();
        // Get day, month, and year
        let day = today.getDate();
        let month = today.getMonth() + 1; // Months are zero-based
        const year = today.getFullYear();

        // Add leading zero if day or month is a single digit
        day = day < 10 ? "0" + day : day;
        month = month < 10 ? "0" + month : month;

        // Format as "dd-mm-yyyy"
        const formattedDate = `${day}-${month}-${year}`;

        // Create a new object
        let newObject = {
          requestedAt: formattedDate,
          from: reformatDate(
            document.getElementById("exampleDropdownFormEmail1").value
          ),
          to: reformatDate(
            document.getElementById("exampleDropdownFormEmail2").value
          ),
          reason: document.getElementById("exampleFormControlTextarea1").value,
          status: 101,
        };

        // Push the new object to the array
        leaveReqDataArray.push(newObject);

        // Save the updated array back to sessionStorage
        sessionStorage.setItem(
          "leaveReqdata",
          JSON.stringify(leaveReqDataArray)
        );

        // Send data to server using fetch
        let serverUrl = "./php/leaveReq.php";
        let userId = JSON.parse(sessionStorage.getItem("data")).student_id;

        fetch(serverUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            userId: userId,
            data: leaveReqDataArray,
          }),
        })
          .then((response) => {
            if (!response.ok) {
              iziToast.error({
                title: "Failed",
                message: "Error1 !",
                position: "topLeft",
              });
            }
            return response.json();
          })
          .then((data) => {
            if (data.success) {
              iziToast.success({
                title: "Success",
                message: data.message,
                position: "topLeft",
              });
              CallAll(); // Fetch All Data Again
            } else {
              iziToast.error({
                title: "Failed",
                message: data.message,
                position: "topLeft",
              });
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            iziToast.error({
              title: "Failed",
              message: "Error !",
              position: "topLeft",
            });
          });
      }
    }
  });

  //logout
  document.getElementById("logout").addEventListener("click", () => {
    sessionStorage.clear();
    iziToast.success({
      title: "Logout Successfull",
      message: "",
      position: "topCenter",
    });
    setTimeout(function () {
      window.location.href = "index.html";
    }, 1000);
  });

  const subjectParentEle = document.getElementById("subList");

  document.getElementById("assi").addEventListener("click", () => {
    const userClassId = JSON.parse(sessionStorage.getItem("data")).clss_id;
    // console.log(userClassId);
    fetch("./php/assign.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        info: "sub",
        classId: userClassId,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          subjectParentEle.innerHTML = "";
          data.data.forEach((ele) => {
            // console.log(ele);
            subjectParentEle.innerHTML += `<a class="list-group-item list-group-item-action" id="${ele.subject_id}" data-bs-toggle="modal" data-bs-target="#assSubMod">${ele.name}</a>`;
          });
        } else {
          iziToast.error({
            title: "Failed",
            message: "Failes to get subject data",
            position: "topLeft",
          });
        }
      })
      .catch((error) => {
        console.error("Error ==>", error);
      });
  });

  //on subject in list clicked
  subjectParentEle.addEventListener("click", function (event) {
    if (event.target.tagName === "A") {
      var clickedId = event.target.id;
      // console.log('Clicked link ID: ' + clickedId);

      fetch("./php/assign.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          info: "assign",
          subid: clickedId,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          // console.log(data.data);
          const AssPar = document.getElementById("assinOfSub");
          AssPar.innerHTML = ``;
          const myStId = JSON.parse(sessionStorage.getItem("data")).student_id;
          data.data.forEach((ele) => {

            // sub code start =========================>
            fetch("./php/assign.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                info: "submit",
                stuid: myStId,
                assingId: ele.assignment_id,
              }),
            })
              .then((res1) => res1.json())
              .then((data1) => {
                let clsNm = data1.success
                  ? "badge bg-success rounded-pill p-2"
                  : "badge bg-warning rounded-pill p-2 text-dark";
                let message = data1.success ? "submitted" : "not submitted";

                let fdata = ele.assignment_information;
                let lines = fdata.split("\n");
                let msg = "";
                lines.forEach((line) => {
                  msg += `<small>${line}</small><br/>`;
                });
                let colorPill = isOfferValid(ele.due_date) ? "primary" : "danger"
                AssPar.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
                  <div class="ms-2 me-auto">
                    <div class="fw-bold">Assignment No. <small id="assId-${ele.assignment_id}">${ele.assignment_id}</small> <small class="badge bg-${colorPill} rounded-pill p-2 mb-2">Last date : ${convertDateFormat(ele.due_date)}</small></div>
                    <small>${msg}</small>
                    <small>Status : </small><small class="${clsNm}">${message}</small><br/>
                    <div class="d-flex justify-content-center align-items-center mt-2">
                      <button id="${ele.assignment_id}" expdate="${ele.due_date}" type="button" style="width: 200px;" class="btn btn-warning me-2 p-0">Submit</button>
                      <input class="form-control form-control-sm" id="file-${ele.assignment_id}" type="file">
                    </div>
                  </div>
                </li>`;

                function convertDateFormat(inputDate) {
                  const [year, month, day] = inputDate.split("-");
                  const newDate = new Date(year, month - 1, day);
                  const monthName = newDate.toLocaleString("en-US", {
                    month: "long",
                  });
                  const formattedDate = `${newDate.getDate()} ${monthName} ${newDate.getFullYear()}`;
                  return formattedDate;
                }
              })
              .catch((error) => {
                console.error("Error ==>", error);
              });
            // sub code end =========================>
          });

          //upload assignment files
          const asbody = document.getElementById("assSubModBody");
          // console.log(asbody);
          asbody.addEventListener("click", function (event) {
            if (event.target.tagName === "BUTTON") {
              var clickedId = event.target.id;
              // console.log("Clicked ==> " + clickedId);
              // console.log(document.getElementById(`file-${clickedId}`));

              const studentId = JSON.parse(sessionStorage.getItem("data")).student_id;
              const fileInput = document.getElementById(`file-${clickedId}`);
              const file = fileInput.files[0];
              // console.log(clickedId, studentId)

              if (file) {
                // if (clickedId && studentId && file) {
                const formData = new FormData();
                formData.append('assignment_id', clickedId);
                formData.append('student_id', studentId);
                formData.append('file', file);

                //check if date is crossed
                //console.log(isOfferValid(document.getElementById(clickedId).getAttribute("expdate")));

                if (isOfferValid(document.getElementById(clickedId).getAttribute("expdate"))) {
                  fetch('./php/upload.php', {
                    method: 'POST',
                    body: formData
                  })
                    .then(response => response.json())
                    .then(data => {
                      if (data.success) {
                        jQuery('#assSubMod').modal('hide');
                        iziToast.success({
                          title: "Success",
                          message: data.message,
                          position: "topLeft",
                        });
                        setLayout();
                      } else {
                        iziToast.error({
                          title: "Failed",
                          message: "Failes to upload",
                          position: "topLeft",
                        });
                      }

                    })
                    .catch(error => {
                      console.error('Error:', error);
                      iziToast.error({
                        title: "Failed",
                        message: "Time Up !!",
                        position: "topLeft",
                      });
                    });
                } else {
                  iziToast.error({
                    title: "Failed",
                    message: "Assignment submission deadline has passed",
                    position: "topLeft",
                  });
                }
              } else {
                iziToast.error({
                  title: "Failed",
                  message: "Select File",
                  position: "topLeft",
                });
              }
            }
          });
        })
        .catch((error) => {
          console.error("Error ==>", error);
        });
    }
  });


  //disable previous date in leave request calender
  validateCalender("exampleDropdownFormEmail1");
  validateCalender("exampleDropdownFormEmail2");

  var myModal2 = new bootstrap.Modal(document.getElementById('exampleModal-ptc'));
  myModal2._element.addEventListener('hidden.bs.modal', function () {
    location.reload();
  });

});
function validateCalender(calId) {
  var today = new Date().toISOString().split('T')[0];
  document.getElementById(calId).setAttribute("min", today);
}

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
  countSuns -= remainingSundaysInMonth();
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

function formatTimestamp(timestamp) {
  const date = new Date(timestamp);
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  const hours = date.getHours().toString().padStart(2, '0');
  const minutes = date.getMinutes().toString().padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function isOfferValid(lastDate) {
  var offerLastDate = new Date(lastDate);
  var today = new Date();

  today.setHours(0, 0, 0, 0);
  offerLastDate.setHours(0, 0, 0, 0);

  return today <= offerLastDate ? true : false;
}

function unlockSection(button) {
  var lockedSection = button.parentElement.parentElement.parentElement;
  lockedSection.classList.remove('locked-section');
  lockedSection.querySelector('.locked-overlay').style.display = 'none';
}

function lockSection(button) {
  var lockedSection = button.parentElement.parentElement.parentElement;
  lockedSection.classList.add('locked-section');
  lockedSection.querySelector('.locked-overlay').style.display = 'flex';
}

function InvalidSym(id) {
  if (document.getElementById(id)) {
    document.getElementById(id).classList.add('is-invalid');
  }
}

function validSym(id) {
  if (document.getElementById(id)) {
    document.getElementById(id).classList.remove('is-invalid');
  }
}

function setLayout() {
  document.getElementById("modelBodyOfResetParentPass").innerHTML = `<div class="input-group" id="currentPassSection">
  <div class="input-group-text" style="width: 200px;">Current Password</div>
  <input type="password" class="form-control" placeholder="old password" aria-label="Input group example" aria-describedby="btnGroupAddon" id="btnGroupAddOnforCurrPass">
</div>
<div class="input-group mt-2">
  <div class="input-group-text" style="width: 200px;">New Password*</div>
  <input type="password" class="form-control" placeholder="new password" aria-label="Input group example" aria-describedby="btnGroupAddon" id="btnGroupAddOnforNewPass">
</div>
<div class="input-group mt-2">
  <div class="input-group-text" style="width: 200px;">Confirm Password*</div>
  <input type="password" class="form-control" placeholder="new password" aria-label="Input group example" aria-describedby="btnGroupAddon" id="btnGroupAddOnforNewPass2">
</div>  `;
}

function isDateBeforeToday(dateString) {
  const [day, month, year] = dateString.split("-").map(Number);
  const today = new Date();
  const providedDate = new Date(year, month - 1, day);
  today.setHours(0, 0, 0, 0);
  providedDate.setHours(0, 0, 0, 0);
  return providedDate < today;
}