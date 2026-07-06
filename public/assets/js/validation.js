// Client Side Validation Function: takes the id's of the inputs to be validated
function validateFields(...ids) {
    let isValid = true;

    ids.forEach(spec => {
        let [id, groupName] = spec.split(":"); // e.g., "genderMale:gender"
        const input = document.getElementById(id);
        if (!input) return;

        const type = input.type || input.tagName.toLowerCase();
        const name = input.name;

        // Reset previous error highlight
        input.classList.remove("is-invalid");
        if (input.classList.contains("select2")) {
            input.nextElementSibling.classList.remove("is-invalid");
        }

        // TEXT, EMAIL, NUMBER, PASSWORD, TEXTAREA
        if (["text", "email", "number", "password", "textarea", "tel", "date"].includes(type)) {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add("is-invalid");
            }
        }

        // SELECT
        else if (type === "select-one" || type === "select") {
            if (!input.value) {
                isValid = false;
                if (input.classList.contains("select2")) {
                    input.nextElementSibling.classList.add("is-invalid");
                } else {
                    input.classList.add("is-invalid");
                }
            }
        }

        // CHECKBOX / RADIO
        else if (type === "checkbox" || type === "radio") {
            // Only validate if it's the first in the group
            if (name && document.querySelectorAll(`[name="${name}"]`).length > 1) {
                const group = document.querySelectorAll(`[name="${name}"]`);
                const checked = Array.from(group).some(el => el.checked);
                group.forEach(el => el.classList.remove("is-invalid"));
                if (!checked) {
                    isValid = false;
                    group.forEach(el => el.classList.add("is-invalid"));
                }
            } else {
                if (!input.checked) {
                    isValid = false;
                    input.classList.add("is-invalid");
                }
            }
        }

        // Single CHECKBOX / RADIO (no group)
        else if (type === "checkbox" || type === "radio") {
            if (!input.checked) {
                isValid = false;
                input.classList.add("is-invalid");
            }
        }

        // FILE
        else if (type === "file") {
            if (!input.files || input.files.length === 0) {
                isValid = false;
                input.classList.add("is-invalid");
            }
        }
    });

    return isValid;
}
