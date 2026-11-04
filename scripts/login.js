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

            console.log(result);

            if (result.success) {

                window.location.href = 'main.html';

            } else {

                alert(result.message);

            }

        } catch (error) {

            console.error('Login Error:', error);
            alert('Login failed.');

        }

    });

});