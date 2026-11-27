document.addEventListener('DOMContentLoaded', async () => {
    // Fetch the signed-in user's profile after the page has loaded.
    try {
        const response = await fetch('profile.php', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            cache: 'no-store'
        });

        if (response.status === 401) {
            window.location.replace('login.html');
            return;
        }

        if (!response.ok) {
            throw new Error(`Profile request failed: HTTP ${response.status}`);
        }

        const result = await response.json();

        if (
            result.status !== 'success' ||
            typeof result.data?.username !== 'string'
        ) {
            throw new Error('Unexpected profile response');
        }

        const usernameDisplay = document.getElementById('username');

        if (!usernameDisplay) {
            throw new Error('Profile page is missing the username element');
        }

        usernameDisplay.textContent = result.data.username;
    } catch (error) {
        console.error('Error loading profile:', error);
    }
});
