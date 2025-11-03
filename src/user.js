// Minimal form validation
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (!/^[a-zA-Z0-9]+$/.test(username)) {
                e.preventDefault();
                alert('Username must contain only letters and numbers');
                return;
            }

            if (password.length < 12) {
                e.preventDefault();
                alert('Password must be at least 12 characters long');
                return;
            }
            alert('⚠️ Notice: Login functionality is not implemented yet.');
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!/^[a-zA-Z0-9]+$/.test(username)) {
                e.preventDefault();
                alert('Username must contain only letters and numbers');
                return;
            }

            if (password.length < 12) {
                e.preventDefault();
                alert('Password must be at least 12 characters long');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            alert('⚠️ Notice: Registration functionality is not implemented yet.');
        });
    }
});
