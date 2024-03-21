const mysql = require('mysql');

// MySQL database connection configuration
const dbConfig = {
  host: 'localhost', // Change this to your database host
  user: 'root', // Change this to your database username
  password: '', // Change this to your database password
  database: 'attendance_tracker' // Change this to your database name
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

// Your application logic goes here

// Don't forget to close the database connection when your application exits
process.on('SIGINT', () => {
  connection.end();
  process.exit();
});
