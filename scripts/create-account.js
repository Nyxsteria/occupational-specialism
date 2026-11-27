document.addEventListener("DOMContentLoaded", () => {
    // Find the account form once the page is ready and attach its submit handler.
    const form =
        document.getElementById("create-account-form");

    // Send the entered account details to the server and display its response.
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        console.log("SUBMIT INTERCEPTED");

        const username =
            document.getElementById("username").value;

        const password =
            document.getElementById("password").value;

        try {
            const response = await fetch(
                "create-account.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        username,
                        password
                    })
                }
            );

            const result = await response.json();

            const popup =
                document.getElementById("popup");

            const title =
                document.getElementById("popup-title");

            const message =
                document.getElementById("popup-message");

            const button =
                document.getElementById("popup-button");

            if (result.success) {
                popup.classList.remove("hidden");

                title.textContent =
                    "Account Created";

                message.textContent =
                    "Welcome " +
                    result.username +
                    "! Your account has been created successfully.";

                button.style.display = "none";

                // Redirect to the signed-in page after showing the success message.
                setTimeout(() => {
                    window.location.href =
                        'main.php'
                }, 2000);
            } else {
                popup.classList.remove("hidden");

                title.textContent =
                    "Account Creation Failed";

                message.textContent =
                    result.message;

                button.style.display =
                    "block";

                button.textContent =
                    "Close";

                // Let the user dismiss the account-creation error popup.
                button.onclick = () => {
                    popup.classList.add("hidden");
                };
            }
        } catch (error) {
            console.error(error);
        }
    });
});
