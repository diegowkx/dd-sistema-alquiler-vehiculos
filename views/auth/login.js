document.addEventListener('DOMContentLoaded', function () {

    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const toggleIcon = document.getElementById('toggleIcon');
    const emailInput = document.getElementById('emailInput');

    // 1. Mostrar / Ocultar Contraseña (Ojito)
    if (togglePassword && passwordInput && toggleIcon) {
        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.classList.toggle('fa-eye', !isPassword);
            toggleIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    }

    // 2. Limpieza de Correo (minúsculas y sin espacios accidentales)
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            this.value = this.value.trim().toLowerCase();
        });
    }
});