/*
| DAILY BAZAAR - GLOBAL JAVASCRIPT
| File:/js/script.js
|
| Works with:
|     Login
|     Register
|     Products
|     Admin Dashboard
|     Product Management
|     Price Management
|     Search / Filter
|     Alerts
|     Forms
|     Mobile UI
|     Image Preview
|     Password Toggle
*/

"use strict";
document.addEventListener("DOMContentLoaded", function () {
    initAlerts();
    initPasswordToggle();
    initDeleteConfirmation();
    initFormValidation();
    initImagePreview();
    initNumberInputs();
    initSearchEnhancements();
    initTableEnhancements();
    initAutoFocus();
    initCurrentYear();
    initSmoothScroll();
    initBackToTop();
    initDoubleSubmitProtection();
    initTooltips();
    console.log("Daily Bazaar JS loaded successfully.");
});


/* =========================================================
   1. ALERT AUTO HIDE
========================================================= */

function initAlerts() {
    const alerts = document.querySelectorAll(
        ".alert, .success, .error, .message"
    );
    alerts.forEach(function (alert) {
        // Make sure alert is visible
        alert.style.transition =
            "opacity 0.4s ease, transform 0.4s ease";
        // Auto hide after 5 seconds
        setTimeout(function () {
            if (!alert.parentElement) {
                return;
            }
            alert.style.opacity = "0";
            alert.style.transform = "translateY(-8px)";
            setTimeout(function () {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 450);
        }, 5000);
    });
}



/* =========================================================
   2. PASSWORD SHOW / HIDE
========================================================= */

function initPasswordToggle() {

    const passwordInputs = document.querySelectorAll(
        'input[type="password"]'
    );

    passwordInputs.forEach(function (input) {

        // Don't create duplicate button
        if (
            input.parentElement &&
            input.parentElement.querySelector(
                ".password-toggle"
            )
        ) {
            return;
        }

        const parent = input.parentElement;

        if (!parent) {
            return;
        }

        parent.style.position = "relative";

        const button = document.createElement("button");

        button.type = "button";

        button.className = "password-toggle";

        button.innerHTML = "👁️";

        button.setAttribute(
            "aria-label",
            "Show password"
        );

        button.style.position = "absolute";
        button.style.right = "10px";
        button.style.top = "50%";
        button.style.transform = "translateY(-50%)";
        button.style.border = "none";
        button.style.background = "transparent";
        button.style.cursor = "pointer";
        button.style.fontSize = "17px";
        button.style.padding = "4px";

        // Prevent weird form submission
        button.addEventListener(
            "mousedown",
            function (event) {
                event.preventDefault();
            }
        );

        button.addEventListener(
            "click",
            function () {

                if (input.type === "password") {

                    input.type = "text";

                    button.innerHTML = "🙈";

                    button.setAttribute(
                        "aria-label",
                        "Hide password"
                    );

                } else {

                    input.type = "password";

                    button.innerHTML = "👁️";

                    button.setAttribute(
                        "aria-label",
                        "Show password"
                    );

                }

            }
        );

        parent.appendChild(button);

    });

}



/* =========================================================
   3. DELETE CONFIRMATION
========================================================= */

function initDeleteConfirmation() {

    const deleteButtons = document.querySelectorAll(
        ".delete-btn"
    );

    deleteButtons.forEach(function (button) {

        // Skip if parent form already has inline confirmation
        const form = button.closest("form");

        if (
            form &&
            form.getAttribute("onsubmit")
        ) {
            return;
        }

        button.addEventListener(
            "click",
            function (event) {

                const confirmed = window.confirm(
                    "Are you sure you want to delete this item?\n\n" +
                    "This action cannot be undone."
                );

                if (!confirmed) {
                    event.preventDefault();
                }

            }
        );

    });

}



/* =========================================================
   4. FORM VALIDATION
========================================================= */

function initFormValidation() {

    const forms = document.querySelectorAll("form");

    forms.forEach(function (form) {

        form.addEventListener(
            "submit",
            function (event) {

                const requiredFields =
                    form.querySelectorAll(
                        "[required]"
                    );

                let valid = true;

                requiredFields.forEach(
                    function (field) {

                        if (
                            field.disabled ||
                            field.type === "hidden"
                        ) {
                            return;
                        }

                        const value =
                            field.value.trim();

                        if (!value) {

                            valid = false;

                            field.style.borderColor =
                                "#dc3545";

                            field.style.boxShadow =
                                "0 0 0 3px rgba(220,53,69,0.10)";

                        } else {

                            field.style.borderColor =
                                "";

                            field.style.boxShadow =
                                "";

                        }

                    }
                );

                if (!valid) {

                    event.preventDefault();

                    const firstInvalid =
                        form.querySelector(
                            ":invalid"
                        );

                    if (firstInvalid) {

                        firstInvalid.focus();

                    }

                    showTemporaryMessage(
                        "Please fill all required fields.",
                        "error"
                    );

                }

            }
        );

    });

}



/* =========================================================
   5. IMAGE PREVIEW
========================================================= */

function initImagePreview() {

    const imageInputs = document.querySelectorAll(
        'input[name="image"]'
    );

    imageInputs.forEach(function (input) {

        input.addEventListener(
            "input",
            function () {

                const value =
                    input.value.trim();

                if (!value) {
                    return;
                }

                const existingPreview =
                    input.parentElement.querySelector(
                        ".js-image-preview"
                    );

                if (existingPreview) {
                    existingPreview.remove();
                }

                const preview =
                    document.createElement("img");

                preview.className =
                    "js-image-preview";

                preview.style.width = "100px";
                preview.style.height = "80px";
                preview.style.objectFit = "cover";
                preview.style.borderRadius = "8px";
                preview.style.marginTop = "8px";
                preview.style.border =
                    "1px solid #ddd";

                /*
                 * Product management page stores
                 * image file name such as:
                 *
                 * potato.jpg
                 *
                 * Images folder is:
                 * ../images/
                 */

                preview.src =
                    "../images/" + value;

                preview.alt =
                    "Product preview";

                preview.onerror =
                    function () {

                        preview.style.display =
                            "none";

                    };

                input.parentElement.appendChild(
                    preview
                );

            }
        );

    });

}



/* =========================================================
   6. NUMBER INPUT PROTECTION
========================================================= */

function initNumberInputs() {

    const numberInputs =
        document.querySelectorAll(
            'input[type="number"]'
        );

    numberInputs.forEach(function (input) {

        input.addEventListener(
            "input",
            function () {

                if (
                    input.value !== "" &&
                    Number(input.value) < 0
                ) {

                    input.value = 0;

                }

            }
        );

    });

}



/* =========================================================
   7. SEARCH ENHANCEMENTS
========================================================= */

function initSearchEnhancements() {

    const searchInputs =
        document.querySelectorAll(
            'input[name="search"]'
        );

    searchInputs.forEach(function (input) {

        input.addEventListener(
            "keydown",
            function (event) {

                // Press Enter to search
                if (event.key === "Enter") {

                    const form =
                        input.closest("form");

                    if (form) {
                        form.submit();
                    }

                }

            }
        );

    });

}



/* =========================================================
   8. TABLE ENHANCEMENTS
========================================================= */

function initTableEnhancements() {

    const tables =
        document.querySelectorAll("table");

    tables.forEach(function (table) {

        const rows =
            table.querySelectorAll(
                "tbody tr"
            );

        rows.forEach(function (row) {

            row.addEventListener(
                "mouseenter",
                function () {

                    row.style.transition =
                        "background 0.15s ease";

                }
            );

        });

    });

}



/* =========================================================
   9. AUTO FOCUS
========================================================= */

function initAutoFocus() {

    /*
     * Don't automatically focus search fields.
     * Prefer first visible text input.
     */

    const firstInput =
        document.querySelector(
            'form input[type="text"]:not([name="search"])'
        );

    if (
        firstInput &&
        window.innerWidth > 600
    ) {

        try {

            firstInput.focus();

        } catch (error) {

            // Ignore focus errors

        }

    }

}



/* =========================================================
   10. CURRENT YEAR
========================================================= */

function initCurrentYear() {

    const yearElements =
        document.querySelectorAll(
            ".current-year, #current-year"
        );

    const year =
        new Date().getFullYear();

    yearElements.forEach(function (element) {

        element.textContent = year;

    });

}



/* =========================================================
   11. SMOOTH SCROLL
========================================================= */

function initSmoothScroll() {

    const links =
        document.querySelectorAll(
            'a[href^="#"]'
        );

    links.forEach(function (link) {

        link.addEventListener(
            "click",
            function (event) {

                const targetId =
                    link.getAttribute("href");

                if (
                    !targetId ||
                    targetId === "#"
                ) {
                    return;
                }

                const target =
                    document.querySelector(
                        targetId
                    );

                if (!target) {
                    return;
                }

                event.preventDefault();

                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }
        );

    });

}



/* =========================================================
   12. BACK TO TOP BUTTON
========================================================= */

function initBackToTop() {

    const button =
        document.createElement("button");

    button.type = "button";

    button.id =
        "dailyBazaarBackToTop";

    button.innerHTML = "↑";

    button.setAttribute(
        "aria-label",
        "Back to top"
    );

    button.style.position =
        "fixed";

    button.style.right =
        "20px";

    button.style.bottom =
        "20px";

    button.style.width =
        "45px";

    button.style.height =
        "45px";

    button.style.border =
        "none";

    button.style.borderRadius =
        "50%";

    button.style.background =
        "#198754";

    button.style.color =
        "#fff";

    button.style.fontSize =
        "22px";

    button.style.fontWeight =
        "bold";

    button.style.cursor =
        "pointer";

    button.style.display =
        "none";

    button.style.alignItems =
        "center";

    button.style.justifyContent =
        "center";

    button.style.boxShadow =
        "0 5px 15px rgba(0,0,0,0.20)";

    button.style.zIndex =
        "9999";

    button.style.transition =
        "0.2s";

    document.body.appendChild(
        button
    );


    window.addEventListener(
        "scroll",
        function () {

            if (window.scrollY > 300) {

                button.style.display =
                    "flex";

            } else {

                button.style.display =
                    "none";

            }

        }
    );


    button.addEventListener(
        "mouseenter",
        function () {

            button.style.transform =
                "translateY(-3px)";

        }
    );


    button.addEventListener(
        "mouseleave",
        function () {

            button.style.transform =
                "translateY(0)";

        }
    );


    button.addEventListener(
        "click",
        function () {

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });

        }
    );

}



/* =========================================================
   13. PREVENT DOUBLE FORM SUBMISSION
========================================================= */

function initDoubleSubmitProtection() {

    const forms =
        document.querySelectorAll("form");

    forms.forEach(function (form) {

        form.addEventListener(
            "submit",
            function () {

                /*
                 * Don't disable buttons on search forms
                 */
                const submitButtons =
                    form.querySelectorAll(
                        'button[type="submit"], input[type="submit"]'
                    );

                submitButtons.forEach(
                    function (button) {

                        /*
                         * Allow button to remain enabled
                         * for forms that may be submitted
                         * again after validation.
                         */

                        setTimeout(
                            function () {

                                button.disabled =
                                    true;

                                const originalText =
                                    button.innerHTML;

                                button.dataset.originalText =
                                    originalText;

                                if (
                                    button.tagName
                                    === "BUTTON"
                                ) {

                                    button.innerHTML =
                                        "Processing...";

                                }

                            },
                            50
                        );

                    }
                );

            }
        );

    });

}



/* =========================================================
   14. SIMPLE TOOLTIP SUPPORT
========================================================= */

function initTooltips() {

    const elements =
        document.querySelectorAll(
            "[data-tooltip]"
        );

    elements.forEach(function (element) {

        element.addEventListener(
            "mouseenter",
            function () {

                element.setAttribute(
                    "title",
                    element.dataset.tooltip
                );

            }
        );

    });

}



/* =========================================================
   15. TEMPORARY MESSAGE
========================================================= */

function showTemporaryMessage(
    message,
    type = "error"
) {

    /*
     * Don't create duplicate messages
     */

    const oldMessage =
        document.querySelector(
            ".js-temp-message"
        );

    if (oldMessage) {
        oldMessage.remove();
    }


    const messageBox =
        document.createElement("div");

    messageBox.className =
        "js-temp-message";


    messageBox.textContent =
        message;


    messageBox.style.position =
        "fixed";

    messageBox.style.top =
        "20px";

    messageBox.style.right =
        "20px";

    messageBox.style.zIndex =
        "10000";

    messageBox.style.padding =
        "13px 18px";

    messageBox.style.borderRadius =
        "8px";

    messageBox.style.fontWeight =
        "bold";

    messageBox.style.maxWidth =
        "350px";

    messageBox.style.boxShadow =
        "0 5px 20px rgba(0,0,0,0.15)";


    if (type === "success") {

        messageBox.style.background =
            "#d1e7dd";

        messageBox.style.color =
            "#0f5132";

    } else {

        messageBox.style.background =
            "#f8d7da";

        messageBox.style.color =
            "#842029";

    }


    document.body.appendChild(
        messageBox
    );


    setTimeout(
        function () {

            messageBox.style.opacity =
                "0";

            messageBox.style.transform =
                "translateY(-10px)";

            messageBox.style.transition =
                "0.3s";

            setTimeout(
                function () {

                    if (
                        messageBox.parentElement
                    ) {

                        messageBox.remove();

                    }

                },
                350
            );

        },
        3500
    );

}



/* =========================================================
   16. PRICE CHANGE PREVIEW
========================================================= */

function initPriceChangePreview() {

    const oldPrice =
        document.querySelector(
            '[name="old_price"]'
        );

    const newPrice =
        document.querySelector(
            '[name="new_price"]'
        );

    const preview =
        document.querySelector(
            ".price-change-preview"
        );

    if (
        !oldPrice ||
        !newPrice ||
        !preview
    ) {
        return;
    }


    function updatePricePreview() {

        const oldValue =
            parseFloat(oldPrice.value);

        const newValue =
            parseFloat(newPrice.value);


        if (
            Number.isNaN(oldValue) ||
            Number.isNaN(newValue)
        ) {

            preview.textContent =
                "";

            return;

        }


        const difference =
            newValue - oldValue;


        if (difference > 0) {

            preview.textContent =
                "↑ Price increased by ₹" +
                difference.toFixed(2);

            preview.style.color =
                "#dc3545";

        } else if (difference < 0) {

            preview.textContent =
                "↓ Price decreased by ₹" +
                Math.abs(difference).toFixed(2);

            preview.style.color =
                "#198754";

        } else {

            preview.textContent =
                "→ No price change";

            preview.style.color =
                "#6c757d";

        }

    }


    oldPrice.addEventListener(
        "input",
        updatePricePreview
    );

    newPrice.addEventListener(
        "input",
        updatePricePreview
    );

}


initPriceChangePreview();



/* =========================================================
   17. CATEGORY FILTER QUICK RESET
========================================================= */

function initCategoryFilter() {

    const categorySelect =
        document.querySelector(
            'select[name="category"]'
        );

    if (!categorySelect) {
        return;
    }


    categorySelect.addEventListener(
        "change",
        function () {

            categorySelect.style.borderColor =
                "#198754";

            setTimeout(
                function () {

                    categorySelect.style.borderColor =
                        "";

                },
                700
            );

        }
    );

}


initCategoryFilter();



/* =========================================================
   18. ESC KEY
========================================================= */

document.addEventListener(
    "keydown",
    function (event) {

        if (event.key !== "Escape") {
            return;
        }


        // Close temporary messages
        const tempMessage =
            document.querySelector(
                ".js-temp-message"
            );

        if (tempMessage) {

            tempMessage.remove();

        }


        // Close visible alerts
        const alerts =
            document.querySelectorAll(
                ".alert"
            );

        alerts.forEach(function (alert) {

            alert.style.display =
                "none";

        });

    }
);



/* =========================================================
   19. ONLINE / OFFLINE STATUS
========================================================= */

window.addEventListener(
    "offline",
    function () {

        showTemporaryMessage(
            "You are offline. Please check your internet connection.",
            "error"
        );

    }
);


window.addEventListener(
    "online",
    function () {

        showTemporaryMessage(
            "Internet connection restored.",
            "success"
        );

    }
);



/* =========================================================
   20. CONSOLE INFORMATION
========================================================= */

console.log(
    "%c Daily Bazaar Price System ",
    "background:#198754;color:white;padding:6px 12px;border-radius:5px;font-weight:bold;"
);

console.log(
    "Frontend JavaScript initialized."
);