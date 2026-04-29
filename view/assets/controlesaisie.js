(function () {
    var namePattern = /^[A-Za-z\u00c0-\u00d6\u00d8-\u00f6\u00f8-\u00ff\s]+$/;
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var today = new Date();
    var minBirthDate = new Date(1950, 0, 1);
    var maxBirthDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());

    function toDateInputValue(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function getOrCreateErrorNode(input) {
        var field = input.closest('.field') || input.closest('.form-group');
        if (!field) {
            return null;
        }

        var fieldName = input.getAttribute('name') || 'field';
        var existingNode = field.querySelector('.field-error-message[data-for="' + fieldName + '"]');
        if (existingNode) {
            return existingNode;
        }

        var errorNode = document.createElement('p');
        errorNode.className = 'field-error-message';
        errorNode.setAttribute('data-for', fieldName);
        errorNode.setAttribute('aria-live', 'polite');
        field.appendChild(errorNode);

        return errorNode;
    }

    function showError(input, message) {
        var errorNode = getOrCreateErrorNode(input);
        if (!errorNode) {
            return;
        }

        input.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');
        errorNode.textContent = message;
        errorNode.classList.add('visible');
    }

    function clearError(input) {
        var errorNode = getOrCreateErrorNode(input);
        if (!errorNode) {
            return;
        }

        input.classList.remove('is-invalid');
        input.removeAttribute('aria-invalid');
        errorNode.textContent = '';
        errorNode.classList.remove('visible');
    }

    function validateNomPrenom(input, label) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            showError(input, label + ' est obligatoire.');
            return false;
        }

        if (!namePattern.test(value)) {
            showError(input, label + ' doit contenir uniquement des lettres.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validateEmail(input) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            showError(input, 'E-mail est obligatoire.');
            return false;
        }

        if (!emailPattern.test(value)) {
            showError(input, 'E-mail invalide.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validatePassword(input) {
        if (!input) {
            return true;
        }

        var value = input.value || '';
        if (value.trim() === '') {
            showError(input, 'Mot de passe est obligatoire.');
            return false;
        }

        if (value.length < 6) {
            showError(input, 'Mot de passe doit contenir au moins 6 caracteres.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validateResetCode(input) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').replace(/\s+/g, '').toUpperCase();
        input.value = value;

        if (value === '') {
            showError(input, 'Code de reinitialisation est obligatoire.');
            return false;
        }

        if (!/^[A-Z0-9]{6}$/.test(value)) {
            showError(input, 'Code invalide. Entrez 6 caracteres.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validatePasswordConfirm(passwordInput, confirmInput) {
        if (!confirmInput) {
            return true;
        }

        var value = (confirmInput.value || '').trim();
        if (value === '') {
            showError(confirmInput, 'Confirmation du mot de passe obligatoire.');
            return false;
        }

        if (passwordInput && value !== (passwordInput.value || '')) {
            showError(confirmInput, 'Les mots de passe ne correspondent pas.');
            return false;
        }

        clearError(confirmInput);
        return true;
    }

    function validateSexe(input) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            showError(input, 'Sexe est obligatoire.');
            return false;
        }

        if (value !== 'homme' && value !== 'femme') {
            showError(input, 'Sexe est invalide.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validatePoids(input) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            showError(input, 'Poids est obligatoire.');
            return false;
        }

        var poids = Number(value);
        if (Number.isNaN(poids)) {
            showError(input, 'Poids doit etre numerique.');
            return false;
        }

        if (poids < 30 || poids > 250) {
            showError(input, 'Poids doit etre entre 30 et 250 kg.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validateTaille(input) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            if (input.required) {
                showError(input, 'Taille est obligatoire.');
                return false;
            }
            clearError(input);
            return true;
        }

        var taille = Number(value);
        if (Number.isNaN(taille)) {
            showError(input, 'Taille doit etre numerique.');
            return false;
        }

        if (taille < 120 || taille > 210) {
            showError(input, 'Taille doit etre entre 120 et 210 cm.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validateObjectif(input) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            showError(input, 'Objectif est obligatoire.');
            return false;
        }

        if (value.length < 3 || value.length > 255) {
            showError(input, 'Objectif doit contenir entre 3 et 255 caracteres.');
            return false;
        }

        clearError(input);
        return true;
    }

    function calculateAgeFromDateValue(dateValue) {
        if (!dateValue || !/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
            return null;
        }

        var parts = dateValue.split('-');
        var year = Number(parts[0]);
        var month = Number(parts[1]);
        var day = Number(parts[2]);
        if (!Number.isInteger(year) || !Number.isInteger(month) || !Number.isInteger(day)) {
            return null;
        }

        var birthDate = new Date(year, month - 1, day);
        if (
            Number.isNaN(birthDate.getTime()) ||
            birthDate.getFullYear() !== year ||
            birthDate.getMonth() !== month - 1 ||
            birthDate.getDate() !== day
        ) {
            return null;
        }

        var age = today.getFullYear() - year;
        var monthDiff = today.getMonth() - (month - 1);
        var dayDiff = today.getDate() - day;
        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
            age -= 1;
        }

        return age;
    }

    function validateAge(input) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            showError(input, 'Age est obligatoire.');
            return false;
        }

        var age = Number(value);
        if (!Number.isInteger(age)) {
            showError(input, 'Age doit etre un entier.');
            return false;
        }

        if (age < 13 || age > 120) {
            showError(input, 'Age doit etre compris entre 13 et 120.');
            return false;
        }

        clearError(input);
        return true;
    }

    function validateDateNaissance(input, ageInput) {
        if (!input) {
            return true;
        }

        var value = (input.value || '').trim();
        if (value === '') {
            showError(input, 'Date de naissance est obligatoire.');
            if (ageInput) {
                ageInput.value = '';
            }
            return false;
        }

        var dateValue = new Date(value);
        if (Number.isNaN(dateValue.getTime())) {
            showError(input, 'Date de naissance est invalide.');
            if (ageInput) {
                ageInput.value = '';
            }
            return false;
        }

        var minValue = toDateInputValue(minBirthDate);
        var maxValue = toDateInputValue(maxBirthDate);
        if (value < minValue || value > maxValue) {
            showError(input, 'Date de naissance invalide.');
            if (ageInput) {
                ageInput.value = '';
            }
            return false;
        }

        if (ageInput) {
            var age = calculateAgeFromDateValue(value);
            ageInput.value = age === null ? '' : String(age);
        }

        clearError(input);
        return true;
    }

    function attachValidationForAuthForm(form, type) {
        var nomInput = form.querySelector('[name="nom"]');
        var prenomInput = form.querySelector('[name="prenom"]');
        var emailInput = form.querySelector('[name="email"]');
        var passwordInput = form.querySelector('[name="password"]');

        var validators = [];
        if (type === 'register') {
            validators.push(function () { return validateNomPrenom(nomInput, 'Nom'); });
            validators.push(function () { return validateNomPrenom(prenomInput, 'Prenom'); });
            if (nomInput) {
                nomInput.addEventListener('input', function () { validateNomPrenom(nomInput, 'Nom'); });
                nomInput.addEventListener('blur', function () { validateNomPrenom(nomInput, 'Nom'); });
            }
            if (prenomInput) {
                prenomInput.addEventListener('input', function () { validateNomPrenom(prenomInput, 'Prenom'); });
                prenomInput.addEventListener('blur', function () { validateNomPrenom(prenomInput, 'Prenom'); });
            }
        }

        validators.push(function () { return validateEmail(emailInput); });
        validators.push(function () { return validatePassword(passwordInput); });

        if (emailInput) {
            emailInput.addEventListener('input', function () { validateEmail(emailInput); });
            emailInput.addEventListener('blur', function () { validateEmail(emailInput); });
        }
        if (passwordInput) {
            passwordInput.addEventListener('input', function () { validatePassword(passwordInput); });
            passwordInput.addEventListener('blur', function () { validatePassword(passwordInput); });
        }

        form.addEventListener('submit', function (event) {
            var isValid = true;
            validators.forEach(function (validator) {
                if (!validator()) {
                    isValid = false;
                }
            });

            if (isValid) {
                return;
            }

            event.preventDefault();
            var firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }, true);
    }

    function attachValidationForForgotForm(form) {
        var emailInput = form.querySelector('[name="email"]');

        var validators = [
            function () { return validateEmail(emailInput); }
        ];

        if (emailInput) {
            emailInput.addEventListener('input', function () { validateEmail(emailInput); });
            emailInput.addEventListener('blur', function () { validateEmail(emailInput); });
        }

        form.addEventListener('submit', function (event) {
            var isValid = true;
            validators.forEach(function (validator) {
                if (!validator()) {
                    isValid = false;
                }
            });

            if (isValid) {
                return;
            }

            event.preventDefault();
            var firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }, true);
    }

    function attachValidationForResetForm(form) {
        var emailInput = form.querySelector('[name="email"]');
        var codeInput = form.querySelector('[name="code"]');
        var passwordInput = form.querySelector('[name="password"]');
        var passwordConfirmInput = form.querySelector('[name="password_confirm"]');

        var validators = [
            function () { return validateEmail(emailInput); },
            function () { return validateResetCode(codeInput); },
            function () { return validatePassword(passwordInput); },
            function () { return validatePasswordConfirm(passwordInput, passwordConfirmInput); }
        ];

        if (emailInput) {
            emailInput.addEventListener('input', function () { validateEmail(emailInput); });
            emailInput.addEventListener('blur', function () { validateEmail(emailInput); });
        }
        if (codeInput) {
            codeInput.addEventListener('input', function () { validateResetCode(codeInput); });
            codeInput.addEventListener('blur', function () { validateResetCode(codeInput); });
        }
        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                validatePassword(passwordInput);
                validatePasswordConfirm(passwordInput, passwordConfirmInput);
            });
            passwordInput.addEventListener('blur', function () { validatePassword(passwordInput); });
        }
        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('input', function () { validatePasswordConfirm(passwordInput, passwordConfirmInput); });
            passwordConfirmInput.addEventListener('blur', function () { validatePasswordConfirm(passwordInput, passwordConfirmInput); });
        }

        form.addEventListener('submit', function (event) {
            var isValid = true;
            validators.forEach(function (validator) {
                if (!validator()) {
                    isValid = false;
                }
            });

            if (isValid) {
                return;
            }

            event.preventDefault();
            var firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }, true);
    }

    function attachValidationForProfileForm(form) {
        var nomInput = form.querySelector('[name="nom"]');
        var prenomInput = form.querySelector('[name="prenom"]');
        var dateInput = form.querySelector('[name="date_naissance"]');
        var sexeInput = form.querySelector('[name="sexe"]');
        var ageInput = form.querySelector('[name="age"]');
        var poidsInput = form.querySelector('[name="poids"]');
        var tailleInput = form.querySelector('[name="taille"]');
        var objectifInput = form.querySelector('[name="objectif"]');
        var emailInput = form.querySelector('[name="email"]');
        var passwordInput = form.querySelector('[name="password"]');

        if (dateInput) {
            dateInput.setAttribute('min', toDateInputValue(minBirthDate));
            dateInput.setAttribute('max', toDateInputValue(maxBirthDate));
        }
        if (ageInput) {
            ageInput.readOnly = true;
        }

        var validators = [
            function () { return validateNomPrenom(nomInput, 'Nom'); },
            function () { return validateNomPrenom(prenomInput, 'Prenom'); },
            function () { return validateDateNaissance(dateInput, ageInput); },
            function () { return validateSexe(sexeInput); },
            function () { return validateAge(ageInput); },
            function () { return validatePoids(poidsInput); },
            function () { return validateTaille(tailleInput); },
            function () { return validateObjectif(objectifInput); },
            function () { return validateEmail(emailInput); },
            function () { return validatePassword(passwordInput); }
        ];

        if (nomInput) {
            nomInput.addEventListener('input', function () { validateNomPrenom(nomInput, 'Nom'); });
            nomInput.addEventListener('blur', function () { validateNomPrenom(nomInput, 'Nom'); });
        }
        if (prenomInput) {
            prenomInput.addEventListener('input', function () { validateNomPrenom(prenomInput, 'Prenom'); });
            prenomInput.addEventListener('blur', function () { validateNomPrenom(prenomInput, 'Prenom'); });
        }
        if (dateInput) {
            dateInput.addEventListener('input', function () { validateDateNaissance(dateInput, ageInput); });
            dateInput.addEventListener('blur', function () { validateDateNaissance(dateInput, ageInput); });
        }
        if (sexeInput) {
            sexeInput.addEventListener('change', function () { validateSexe(sexeInput); });
            sexeInput.addEventListener('blur', function () { validateSexe(sexeInput); });
        }
        if (poidsInput) {
            poidsInput.addEventListener('input', function () { validatePoids(poidsInput); });
            poidsInput.addEventListener('blur', function () { validatePoids(poidsInput); });
        }
        if (tailleInput) {
            tailleInput.addEventListener('input', function () { validateTaille(tailleInput); });
            tailleInput.addEventListener('blur', function () { validateTaille(tailleInput); });
        }
        if (objectifInput) {
            objectifInput.addEventListener('input', function () { validateObjectif(objectifInput); });
            objectifInput.addEventListener('blur', function () { validateObjectif(objectifInput); });
        }
        if (emailInput) {
            emailInput.addEventListener('input', function () { validateEmail(emailInput); });
            emailInput.addEventListener('blur', function () { validateEmail(emailInput); });
        }
        if (passwordInput) {
            passwordInput.addEventListener('input', function () { validatePassword(passwordInput); });
            passwordInput.addEventListener('blur', function () { validatePassword(passwordInput); });
        }

        validateDateNaissance(dateInput, ageInput);

        form.addEventListener('submit', function (event) {
            var isValid = true;
            validators.forEach(function (validator) {
                if (!validator()) {
                    isValid = false;
                }
            });

            if (isValid) {
                return;
            }

            event.preventDefault();
            var firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }, true);
    }

    function detectFormType(form) {
        var action = form.getAttribute('action') || '';
        if (action.indexOf('action=register') !== -1) {
            return 'register';
        }
        if (action.indexOf('action=login') !== -1) {
            return 'login';
        }
        if (action.indexOf('action=forgot') !== -1) {
            return 'forgot';
        }
        if (action.indexOf('action=reset-password') !== -1) {
            return 'reset';
        }
        if (
            action.indexOf('action=update-profile') !== -1 ||
            action.indexOf('action=update-user') !== -1 ||
            action.indexOf('action=store-user') !== -1
        ) {
            return 'profile';
        }
        return '';
    }

    function initSiteWideValidation() {
        var forms = document.querySelectorAll('form');
        forms.forEach(function (form) {
            var formType = detectFormType(form);
            if (formType === 'register' || formType === 'login') {
                attachValidationForAuthForm(form, formType);
                return;
            }

            if (formType === 'forgot') {
                attachValidationForForgotForm(form);
                return;
            }

            if (formType === 'reset') {
                attachValidationForResetForm(form);
                return;
            }

            if (formType === 'profile') {
                attachValidationForProfileForm(form);
            }
        });
    }

    function bootValidation() {
        try {
            initSiteWideValidation();
        } catch (error) {
            console.error('Initialization failed for site-validation:', error);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootValidation);
    } else {
        bootValidation();
    }
})();
