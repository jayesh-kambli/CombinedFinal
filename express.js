const express = require('express');
const app = express();
const { SerialPort } = require('serialport');
const cors = require('cors');
const path = require('path');

const portName = 'COM3'; // Change this to the port name your Arduino is connected to
const baudRate = 9600;   // Match this with the baud rate of your Arduino

const port = new SerialPort({ path: portName, baudRate: baudRate });

let buffer = '';

app.use(cors());
app.use(express.static(path.join(__dirname, 'public')));

app.get('/startScan', (req, res) => {
    startRFIDScan(res); // Trigger the Arduino to start scanning the RFID tag
});

port.on('open', function () {
    console.log('Serial port is open');
});

port.on('data', function (data) {
    buffer += data.toString();

    // Check if the buffer contains a complete line
    const newlineIndex = buffer.indexOf('\r\n');
    if (newlineIndex !== -1) {
        const line = buffer.substring(0, newlineIndex).trim();
        console.log(line);
        buffer = buffer.substring(newlineIndex + 2);

        // Send the RFID code back as the response to the client's request
        if (lastRes) {
            lastRes.send(line);
            lastRes = null; // Reset the lastRes variable
        }
    }
});

port.on('error', function (err) {
    console.error('Error:', err.message);
});

app.listen(3000, () => {
    console.log('Server is running on port 3000');
});

let lastRes = null; // Variable to store the last response object

// Function to trigger the Arduino to start scanning the RFID tag
function startRFIDScan(res) {
    lastRes = res; // Store the response object for later use
    // Assuming 'port' is the SerialPort instance
    port.write('START_SCAN'); // Send a command to the Arduino to start scanning
}
