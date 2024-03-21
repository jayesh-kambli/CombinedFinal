document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('registerForm');
    const resultDiv = document.getElementById('result');

    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Get form data
        const formData = new FormData(form);

        // Fetch API to send the form data to the server
        fetch('./reg.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // Parse the JSON response
        })
        .then(data => {
            // Display the result
            resultDiv.innerHTML = data.message;
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
