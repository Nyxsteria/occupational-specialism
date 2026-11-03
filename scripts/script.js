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

function setCookie(name, value, days) {

    const date = new Date();

    date.setTime(
        date.getTime() +
        (days * 24 * 60 * 60 * 1000)
    );

    document.cookie =
        `${name}=${value}; expires=${date.toUTCString()}; path=/`;

}

function getCookie(name) {

    const cookieName = name + "=";

    const decodedCookie =
        decodeURIComponent(document.cookie);

    const cookieArray =
        decodedCookie.split(";");

    for (let cookie of cookieArray) {

        cookie = cookie.trim();

        if (cookie.indexOf(cookieName) === 0) {

            return cookie.substring(
                cookieName.length
            );

        }

    }

    return null;

}


// ==========================================
// Popup Functions
// ==========================================

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

            message.actions.forEach(action => {

                const button =
                    document.createElement("button");

                button.textContent =
                    action.text;

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

    constructor(messageData) {

        this.messageData =
            messageData;

    }

    display() {

        openPopup(
            this.messageData
        );

    }

}

class AlertMessage extends Message {

    display() {

        super.display();

        alert("HELP, HELP!");

    }

}

class Data {

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