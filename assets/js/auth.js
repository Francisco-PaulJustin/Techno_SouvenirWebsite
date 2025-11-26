// Authentication functionality

document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signup-form');
    const loginForm = document.getElementById('login-form');

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const getErrorElement = (field) => {
        if (!field) return null;
        const name = field.getAttribute('name');
        return field.closest('.form-group')?.querySelector(`.field-error[data-for="${name}"], .field-error[data-for="${field.id}"]`) ||
               document.querySelector(`.field-error[data-for="${field.id}"]`);
    };

    const setFieldState = (field, isValid, message = '') => {
        if (!field) return;
        const errorEl = getErrorElement(field);

        if (!isValid) {
            field.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = message;
        } else {
            field.classList.remove('is-invalid');
            if (errorEl) errorEl.textContent = '';
        }
    };

    const validateRequired = (field, label = 'This field') => {
        if (!field) return true;
        const value = field.value.trim();
        if (!value) {
            setFieldState(field, false, `${label} is required`);
            return false;
        }
        setFieldState(field, true);
        return true;
    };

    const validateEmail = (field) => {
        if (!field) return true;
        const baseValid = validateRequired(field, 'Email');
        if (!baseValid) return false;

        if (!emailPattern.test(field.value.trim())) {
            setFieldState(field, false, 'Please enter a valid email');
            return false;
        }
        setFieldState(field, true);
        return true;
    };

    const validatePasswordLength = (field, min = 6) => {
        if (!field) return true;
        const baseValid = validateRequired(field, 'Password');
        if (!baseValid) return false;

        if (field.value.length < min) {
            setFieldState(field, false, `Password must be at least ${min} characters`);
            return false;
        }
        setFieldState(field, true);
        return true;
    };

    const validatePasswordMatch = (passwordField, confirmField) => {
        if (!passwordField || !confirmField) return true;
        if (!confirmField.value.trim()) {
            setFieldState(confirmField, false, 'Please confirm your password');
            return false;
        }
        if (passwordField.value !== confirmField.value) {
            setFieldState(confirmField, false, 'Passwords must match');
            return false;
        }
        setFieldState(confirmField, true);
        return true;
    };

    if (signupForm) {
        const firstName = signupForm.querySelector('#first_name');
        const lastName = signupForm.querySelector('#last_name');
        const email = signupForm.querySelector('#email');
        const password = signupForm.querySelector('#password');
        const confirmPassword = signupForm.querySelector('#confirm_password');

        // Live validation on input/blur
        [firstName, lastName].forEach((field) => {
            if (!field) return;
            const label = field.id === 'first_name' ? 'First name' : 'Last name';
            field.addEventListener('blur', () => validateRequired(field, label));
            field.addEventListener('input', () => validateRequired(field, label));
        });

        if (email) {
            email.addEventListener('blur', () => validateEmail(email));
            email.addEventListener('input', () => validateEmail(email));
        }

        if (password) {
            password.addEventListener('blur', () => validatePasswordLength(password, 6));
            password.addEventListener('input', () => {
                validatePasswordLength(password, 6);
                if (confirmPassword && confirmPassword.value.trim()) {
                    validatePasswordMatch(password, confirmPassword);
                }
            });
        }

        if (confirmPassword) {
            confirmPassword.addEventListener('blur', () => validatePasswordMatch(password, confirmPassword));
            confirmPassword.addEventListener('input', () => validatePasswordMatch(password, confirmPassword));
        }

        signupForm.addEventListener('submit', function(e) {
            let valid = true;

            if (!validateRequired(firstName, 'First name')) valid = false;
            if (!validateRequired(lastName, 'Last name')) valid = false;
            if (!validateEmail(email)) valid = false;
            if (!validatePasswordLength(password, 6)) valid = false;
            if (!validatePasswordMatch(password, confirmPassword)) valid = false;

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    if (loginForm) {
        const email = loginForm.querySelector('#email');
        const password = loginForm.querySelector('#password');

        if (email) {
            email.addEventListener('blur', () => validateEmail(email));
            email.addEventListener('input', () => validateEmail(email));
        }

        if (password) {
            password.addEventListener('blur', () => validateRequired(password, 'Password'));
            password.addEventListener('input', () => validateRequired(password, 'Password'));
        }

        loginForm.addEventListener('submit', (e) => {
            let valid = true;

            if (!validateEmail(email)) valid = false;
            if (!validateRequired(password, 'Password')) valid = false;

            if (!valid) {
                e.preventDefault();
            }
        });
    }
});

