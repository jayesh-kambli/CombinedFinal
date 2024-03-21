const express = require('express');
const app = express();
const { SerialPort } = require('serialport');
const cors = require('cors');
const path = require('path');
const mysql = require('mysql'); // Import the MySQL module

const portName = 'COM5'; // Change this to the port name your Arduino is connected to
const baudRate = 9600;   // Match this with the baud rate of your Arduino

const port = new SerialPort({ path: portName, baudRate: baudRate });

let buffer = '';

// MySQL database connection configuration
const dbConfig = {
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'attendance_tracker'
};

// Create a MySQL connection
const connection = mysql.createConnection(dbConfig);

// Connect to the MySQL database
connection.connect((err) => {
  if (err) {
    console.error('Error connecting to database:', err);
    return;
  }
  console.log('Connected to database');
});

app.use(cors());
app.use(express.static(path.join(__dirname, 'public')));

app.get('/startScan', (req, res) => {
  // Trigger the Arduino to start scanning the RFID tag
  startRFIDScan(); 
});

port.on('open', function () {
  console.log('Serial port is open');

  port.on('data', function (data) {
    buffer += data.toString();

    // Check if the buffer contains a complete line
    const newlineIndex = buffer.indexOf('\r\n');
    if (newlineIndex !== -1) {
      const line = buffer.substring(0, newlineIndex).trim();
      console.log(line);
      buffer = buffer.substring(newlineIndex + 2);

      // Fetch student ID from the database based on the RFID code
      const sql = `SELECT student_id FROM students WHERE rfid_code = '${line}'`;
      connection.query(sql, (err, result) => {
        if (err) {
          console.error('Error querying database:', err);
          return;
        }
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
            res.send('Attendance recorded');
          });
        } else {
          console.log('Student not found');
          res.send('Student not found');
        }
      });
    }
  });
});

port.on('error', function (err) {
  console.error('Error:', err.message);
});

app.listen(3000, () => {
  console.log('Server is running on port 3000');
});

function startRFIDScan() {
  port.write('START_SCAN'); 
}
