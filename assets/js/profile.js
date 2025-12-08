document.addEventListener('DOMContentLoaded', () => {
    const avatarForm = document.getElementById('avatar-form');
    const avatarInput = document.getElementById('profile_image_upload');
    const profileForm = document.querySelector('.profile-form');
    const passwordForm = document.getElementById('password-form');

    const profileGrid = document.getElementById('profileGrid');
    const btnShowProfile = document.getElementById('btnShowProfile');

    // Initial setup: Hide profile sections
    if (profileGrid) {
        profileGrid.style.display = 'none';
    }

    // Toggle visibility of profile sections
    if (btnShowProfile) {
        btnShowProfile.addEventListener('click', () => {
            if (profileGrid.style.display === 'none') {
                profileGrid.style.display = 'grid';
            } else {
                profileGrid.style.display = 'none';
            }
        });
    }

    // Handle form submissions to keep the relevant section open if navigated back
    const urlParams = new URLSearchParams(window.location.search);
    const formSubmitted = urlParams.get('form_submitted');
    const activeSection = urlParams.get('active_section');

    if (formSubmitted && activeSection) {
        if (profileGrid) {
            profileGrid.style.display = 'grid';
        }
    }

    // Profile picture upload
    if (avatarInput) {
        avatarInput.addEventListener('change', () => {
            if (avatarInput.files.length) {
                avatarForm.submit();
            }
        });
    }

    // Form validation
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
