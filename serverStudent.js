const { SerialPort } = require('serialport');

const portName = 'COM5'; // Change this to the port name your Arduino is connected to
const baudRate = 9600;   // Match this with the baud rate of your Arduino

const port = new SerialPort({ path: portName, baudRate: baudRate });

let buffer = '';

port.on('open', function () {
  console.log('Serial port is open');

  port.on('data', function (data) {
    buffer += data.toString();

    // Check if the buffer contains a complete line
    const newlineIndex = buffer.indexOf('\r\n');
    if (newlineIndex !== -1) {
      const line = buffer.substring(0, newlineIndex).trim();

      // Check if the line contains RFID data
      if (line.startsWith('RFID:')) {
        // Extract RFID data
        const rfidCode = line.substring(5); // Assuming RFID code follows 'RFID:' prefix
        console.log('RFID:', rfidCode);

        // Handle the RFID data here, such as sending it to the client-side application
        // You may also want to reset `buffer` after processing the RFID code
        buffer = '';
      }

      buffer = buffer.substring(newlineIndex + 2);
    }
  });
});

port.on('error', function (err) {
  console.error('Error:', err.message);
});
