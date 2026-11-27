// ==========================================
// Popup Elements
// ==========================================

const openButton = document.getElementById("openPopup");
const popupOverlay = document.getElementById("popupOverlay");
const closeButton = document.getElementById("popupClose");

const popupTitle =
    document.getElementById("popup-title");

const popupDescription =
    document.getElementById("popup-description");

const popupActions =
    document.getElementById("popup-actions");

let popupTimer;


// ==========================================
// Cookie Functions
// ==========================================

// Save a browser cookie with the given value and expiry period.
function setCookie(name, value, days) {
    const maxAge =
        days * 24 * 60 * 60;

    document.cookie =
        `${encodeURIComponent(name)}=${encodeURIComponent(value)}; ` +
        `Max-Age=${maxAge}; ` +
        `Path=/; ` +
        `SameSite=Lax`;
}

// Find a named cookie and return its decoded value, or null if absent.
function getCookie(name) {
    const cookieName =
        encodeURIComponent(name) + "=";

    const cookies =
        document.cookie.split(";");

    for (let cookie of cookies) {
        cookie = cookie.trim();

        if (
            cookie.startsWith(cookieName)
        ) {
            return decodeURIComponent(
                cookie.substring(
                    cookieName.length
                )
            );
        }
    }

    return null;
}

// Remove a cookie by setting its expiry time in the past.
function deleteCookie(name) {
    document.cookie =
        `${encodeURIComponent(name)}=; ` +
        `Max-Age=0; ` +
        `Path=/; ` +
        `SameSite=Lax`;
}


// ==========================================
// Popup Functions
// ==========================================

// Fill and display a popup, adding action buttons and an auto-close timer if needed.
function openPopup(message) {
    clearTimeout(popupTimer);

    if (popupTitle) {
        popupTitle.textContent =
            message.title || "Message";
    }

    if (popupDescription) {
        popupDescription.textContent =
            message.content || "";
    }

    if (popupActions) {
        popupActions.innerHTML = "";

        if (message.actions) {
            // Create a button for every action supplied with this message.
            message.actions.forEach(action => {
                const button =
                    document.createElement("button");

                button.textContent =
                    action.text;

                // Route this button's click to the action handler below.
                button.addEventListener(
                    "click",
                    () => handleAction(action.action)
                );

                popupActions.appendChild(button);
            });
        }
    }

    popupOverlay.hidden = false;

    if (!message.persistent) {
        popupTimer = setTimeout(
            closePopup,
            10000
        );
    }
}

// Hide the popup, cancel its timer, and remove any temporary action buttons.
function closePopup() {
    clearTimeout(popupTimer);

    popupOverlay.hidden = true;

    if (popupActions) {
        popupActions.innerHTML = "";
    }
}


// ==========================================
// Action Router
// ==========================================

// Perform the selected popup action, such as saving cookie consent or navigating.
function handleAction(action) {
    switch (action) {
        case "close":
            closePopup();
            break;

        case "acceptCookies":
            setCookie(
                "cookieConsent",
                "accepted",
                365
            );

            closePopup();
            break;

        case "denyCookies":
            setCookie(
                "cookieConsent",
                "denied",
                365
            );

            closePopup();
            break;

        case "readMoreCookies":
            window.location.href =
                "cookie-policy.html";
            break;

        default:
            closePopup();
    }
}


// ==========================================
// Message Classes
// ==========================================

class Message {
    // Store the message data so the instance can display it later.
    constructor(messageData) {
        this.messageData =
            messageData;
    }

    // Show this message using the shared popup display function.
    display() {
        openPopup(
            this.messageData
        );
    }
}

class AlertMessage extends Message {
    // Display the shared popup, then show an additional browser alert.
    display() {
        super.display();

        alert("HELP, HELP!");
    }
}

class Data {
    // Parse message JSON and choose a message class based on its type.
    constructor(name, jsonData) {
        this.name = name;

        this.data =
            JSON.parse(jsonData);

        const message =
            this.data.message;

        switch (message.type) {
            case "alert":
                new AlertMessage(
                    message
                ).display();
                break;

            default:
                new Message(
                    message
                ).display();
        }
    }
}


// ==========================================
// Cookie Consent JSON
// ==========================================

const cookieMessage = JSON.stringify({
    message: {
        id: "cookie-consent",
        type: "cookie",
        title: "Cookie Preferences",
        content:
            "This website uses cookies to remember your preferences and improve your experience.",
        persistent: true,
        actions: [
            {
                text: "Accept",
                action: "acceptCookies"
            },
            {
                text: "Deny",
                action: "denyCookies"
            },
            {
                text: "Read More",
                action: "readMoreCookies"
            }
        ]
    }
});


// ==========================================
// About Popup JSON
// ==========================================

const aboutMessage = JSON.stringify({
    message: {
        type: "info",
        title: "Occupational Specialism",
        content:
            "This content is now being delivered from JSON rather than hard-coded HTML.",
        persistent: false,
        actions: [
            {
                text: "Close",
                action: "close"
            }
        ]
    }
});


// ==========================================
// Cookie Consent Check
// ==========================================

// Show the consent popup only when no choice has already been saved.
function checkCookieConsent() {
    const consent =
        getCookie("cookieConsent");

    if (!consent) {
        new Data(
            "cookie-consent",
            cookieMessage
        );
    }
}


// ==========================================
// Event Listeners
// ==========================================

if (openButton) {
    // Open the informational popup when its page button is clicked.
    openButton.addEventListener(
        "click",
        () => {
            new Data(
                "about",
                aboutMessage
            );
        }
    );
}

if (closeButton) {
    closeButton.addEventListener(
        "click",
        closePopup
    );
}

// Let the Escape key close the popup while it is visible.
document.addEventListener(
    "keydown",
    (event) => {
        if (
            event.key === "Escape" &&
            !popupOverlay.hidden
        ) {
            closePopup();
        }
    }
);

if (popupOverlay) {
    // Close the popup when the user clicks its backdrop, not its contents.
    popupOverlay.addEventListener(
        "click",
        (event) => {
            if (
                event.target ===
                popupOverlay
            ) {
                closePopup();
            }
        }
    );
}

document.addEventListener(
    "DOMContentLoaded",
    checkCookieConsent
);
