// Inside the port.on('data') event listener

// Fetch RFID code
const rfidCode = buffer.trim();

// Query to fetch student ID based on RFID code
const sql = `SELECT student_id FROM students WHERE rfid_code = '${rfidCode}'`;

// Execute the query
connection.query(sql, (err, result) => {
  if (err) {
    console.error('Error querying database:', err);
    return;
  }

  // Check if a student with the scanned RFID code exists
  if (result.length > 0) {
    const studentId = result[0].student_id;
    const datetime = new Date().toISOString().slice(0, 19).replace('T', ' '); // Get current date and time
    const classId = 123; // Replace with the actual class ID

    // Insert attendance record into the database
    const insertSql = `INSERT INTO attendance (student_id, datetime, class_id) VALUES (${studentId}, '${datetime}', ${classId})`;
    connection.query(insertSql, (err) => {
      if (err) {
        console.error('Error inserting attendance record:', err);
        return;
      }
      console.log('Attendance recorded for student:', studentId);
    });
  } else {
    console.log('Student not found');
  }
});
