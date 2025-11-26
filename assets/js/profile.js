document.addEventListener('DOMContentLoaded', () => {
    const avatarForm = document.getElementById('avatar-form');
    const avatarInput = avatarForm ? avatarForm.querySelector('input[name="profile_image"]') : null;
    const scrollButtons = document.querySelectorAll('[data-scroll-to]');
    const profileForm = document.querySelector('.profile-form');
    const passwordForm = document.getElementById('password-form');

    if (avatarInput) {
        avatarInput.addEventListener('change', () => {
            if (avatarInput.files.length) {
                avatarForm.submit();
            }
        });
    }

    scrollButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.scrollTo);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const validateField = (field) => {
        if (!field) return;
        if (field.required && !field.value.trim()) {
            field.classList.add('is-invalid');
        } else if (field.type === 'email' && field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    };

    if (profileForm) {
        profileForm.querySelectorAll('input, textarea').forEach(field => {
            field.addEventListener('input', () => validateField(field));
        });
    }

    if (passwordForm) {
        const newPassword = passwordForm.querySelector('#new_password');
        const confirmPassword = passwordForm.querySelector('#confirm_password');

        passwordForm.addEventListener('input', () => {
            if (newPassword && confirmPassword) {
                if (confirmPassword.value && confirmPassword.value !== newPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                } else {
                    confirmPassword.classList.remove('is-invalid');
                }
            }
        });
    }
});

