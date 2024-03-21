function startRFIDScan() {
    fetch('/startScan') // Send a GET request to the server
        .then(response => {
            if (response.ok) {
                return response.text();
            } else {
                throw new Error('Failed to start RFID scan');
            }
        })
        .then(data => {
            console.log('RFID scan started:', data);
        })
        .catch(error => {
            console.error('Error:', error.message);
        });
}

document.getElementById('startScanButton').addEventListener('click', startRFIDScan);
