document.addEventListener('DOMContentLoaded', () => {

    const loginForm = document.getElementById('login-form');

    if (!loginForm) {
        console.error('login-form not found');
        return;
    }

    loginForm.addEventListener('submit', async (event) => {

        event.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        try {

            const response = await fetch('./login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    username,
                    password
                })
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = 'main.html';
            } else {
                alert(result.message);
            }

        } catch (error) {
            console.error(error);
            alert('Login failed.');
        }

    });

});