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

            const popup =
                document.getElementById('popup');

            const title =
                document.getElementById('popup-title');

            const message =
                document.getElementById('popup-message');

            const button =
                document.getElementById('popup-button');

            if (result.success) {

                popup.classList.remove('hidden');

                popup.querySelector('.popup-content')
                    .style.borderLeftColor = '#16a34a';

                title.textContent =
                    'Login Successful';

                message.textContent =
                    `Welcome back ${result.username}! Redirecting you to the main page...`;

                button.style.display =
                    'none';

                setTimeout(() => {

                    window.location.href =
                        `main.html?username=${encodeURIComponent(result.username)}`;

                }, 1500);

            } else {

                popup.classList.remove('hidden');

                popup.querySelector('.popup-content')
                    .style.borderLeftColor = '#dc2626';

                title.textContent =
                    'Login Failed';

                message.textContent =
                    result.message ||
                    'The username or password entered is incorrect.';

                button.style.display =
                    'block';

                button.textContent =
                    'Try Again';

                button.onclick = () => {

                    popup.classList.add('hidden');

                };

            }

        } catch (error) {

            console.error('Login Error:', error);

            const popup =
                document.getElementById('popup');

            const title =
                document.getElementById('popup-title');

            const message =
                document.getElementById('popup-message');

            const button =
                document.getElementById('popup-button');

            popup.classList.remove('hidden');

            popup.querySelector('.popup-content')
                .style.borderLeftColor = '#dc2626';

            title.textContent =
                'Server Error';

            message.textContent =
                'Unable to contact the server. Please try again later.';

            button.style.display =
                'block';

            button.textContent =
                'Close';

            button.onclick = () => {

                popup.classList.add('hidden');

            };

        }

    });

});