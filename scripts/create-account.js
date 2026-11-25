document.addEventListener("DOMContentLoaded", () => {

    const form =
        document.getElementById("create-account-form");

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

                setTimeout(() => {

                    window.location.href =
                        `main.html?username=${encodeURIComponent(result.username)}`;

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

                button.onclick = () => {

                    popup.classList.add("hidden");

                };

            }

        } catch (error) {

            console.error(error);

        }

    });

});