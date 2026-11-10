document.addEventListener('DOMContentLoaded', () => {

    const loginForm = document.getElementById('login-form');

    if (!loginForm) {
        console.error('login-form not found');
        return;
    }

    loginForm.addEventListener('submit', async (event) => {

        event.preventDefault();

        const formData = new FormData(loginForm);

        try {

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            console.log('Server Response:', result);

            if (result.success) {

                const username = formData.get('username');

                window.location.href =
                    `main.html?username=${encodeURIComponent(username)}`;

            } else {

                alert(result.message || 'Invalid username or password.');

            }

        } catch (error) {

            console.error('Login Error:', error);

            alert('Login failed. Check the console for details.');

        }

    });

});