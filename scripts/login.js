document.addEventListener('DOMContentLoaded', () => {

    const loginForm = document.getElementById('login-form');

    if (!loginForm) {
        console.error('Form with id="login-form" not found.');
        return;
    }

    loginForm.addEventListener('submit', async (event) => {

        event.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        const formData = new URLSearchParams();
        formData.append('username', username);
        formData.append('password', password);

        try {

            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Server Error: ${response.status}`);
            }

            const result = await response.json();

            console.log(result);
            alert(result.message);

        } catch (error) {

            console.error('Request failed:', error);
            alert('Login request failed.');

        }

    });

});