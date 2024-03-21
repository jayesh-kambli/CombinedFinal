// const php = require('node-php');


// Add event listener to the button
let scanInitiated = false; // Flag to track if RFID scan has already been initiated
document.getElementById('startScanButton').addEventListener('click', startRFIDScan);
// Function to handle button click event
function startRFIDScan() {
    fetch('http://localhost:3000/startScan') // Send a GET request to the server
        .then(response => {
            if (response.ok) {
                return response.text();
            } else {
                throw new Error('Failed to start RFID scan');
            }
        })
        .then(data => {
            alert('RFID scan started: ' + data); // Display RFID code as an alert
            let date = new Date();
            console.log(date);
            scanInitiated = false; // Reset the flag to allow subsequent scans
        })
        .catch(error => {
            console.error('Error:', error.message);
        });
}
