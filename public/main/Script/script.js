sessionStorage.clear();
localStorage.clear();
document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById('loginForm');

    loginForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        let typeInfo = '';

        if (username.includes("@")) {
            const [a, b] = username.split("@");
            console.log(b);
            if (b === 'tr.com') {
                typeInfo = 'tr';
            } else {
                typeInfo = 'st';
            }
        }

        // Fetch API to send the login data to the server
        fetch('./php/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'type': typeInfo,
                'username': username,
                'password': password
            }),
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const redirectPage = typeInfo === 'tr' ? 'teachers.html' : 'mainPage.html';

                    iziToast.success({
                        title: 'Success',
                        message: data.message,
                        position: 'topLeft'
                    });
                    console.log(data.user);
                    sessionStorage.setItem('data', JSON.stringify(data.user));
                    sessionStorage.setItem('user', username);
                    sessionStorage.setItem('pass', password);
                    window.location.href = redirectPage;
                } else {
                    iziToast.error({
                        title: 'Failed',
                        message: data.message,
                        position: 'topLeft'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});
