document.addEventListener("DOMContentLoaded", () => {

    console.log("JS LOADED");

    const form = document.getElementById("create-account-form");

    console.log(form);

    form.addEventListener("submit", async (e) => {

        e.preventDefault();

        console.log("FORM SUBMITTED");

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        const response = await fetch("create-account.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                username,
                password
            })
        });

        const data = await response.json();

        console.log(data);
    });

});