document.addEventListener('DOMContentLoaded', function () {
    const btnAbrirPerfil = document.getElementById('btn-abrir-perfil');
    const profileModalElement = document.getElementById('profileModal');
    const profileForm = document.getElementById('profileForm');
    const toggleProfPasswordBtn = document.getElementById('toggleProfPassword');
    const profPasswordInput = document.getElementById('prof_password');
    const toggleProfIcon = document.getElementById('toggleProfIcon');
    const profTelefonoInput = document.getElementById('prof_telefono');

    // Referencias a la barra y texto de fortaleza del perfil
    const profStrengthBar = document.getElementById('prof-password-strength-bar');
    const profStrengthText = document.getElementById('prof-password-strength-text');

    let profileModal = null;
    if (profileModalElement) {
        profileModal = new bootstrap.Modal(profileModalElement);
    }

    // 1. Sanitización en vivo: Solo números en el teléfono
    if (profTelefonoInput) {
        profTelefonoInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, ''); // Elimina cualquier carácter que no sea número
        });
    }

    // 2. Función para evaluar la fortaleza de la contraseña
    function evaluarFortalezaProfPassword() {
        if (!profPasswordInput || !profStrengthBar || !profStrengthText) return;

        const val = profPasswordInput.value;

        // Campo vacío (se mantiene la clave actual)
        if (val.length === 0) {
            profStrengthBar.style.width = '0%';
            profStrengthBar.className = 'progress-bar';
            profStrengthText.textContent = 'Si se deja en blanco, se conservará la contraseña actual.';
            profStrengthText.className = 'form-text text-muted';
            return;
        }

        const tieneNumero = /\d/.test(val);
        const tieneSimbolo = /[^a-zA-Z0-9]/.test(val);
        const tieneLetra = /[a-zA-Z]/.test(val);

        // Reglas de evaluación
        if (val.length < 5) {
            profStrengthBar.style.width = '30%';
            profStrengthBar.className = 'progress-bar bg-danger';
            profStrengthText.textContent = 'Contraseña Débil (mínimo 5 caracteres)';
            profStrengthText.className = 'form-text text-danger';
        } else if (val.length >= 5 && val.length <= 6) {
            if (tieneNumero && tieneSimbolo) {
                profStrengthBar.style.width = '65%';
                profStrengthBar.className = 'progress-bar bg-warning';
                profStrengthText.textContent = 'Contraseña Media';
                profStrengthText.className = 'form-text text-warning';
            } else {
                profStrengthBar.style.width = '35%';
                profStrengthBar.className = 'progress-bar bg-danger';
                profStrengthText.textContent = 'Contraseña Débil (requiere al menos un número y un símbolo)';
                profStrengthText.className = 'form-text text-danger';
            }
        } else if (val.length >= 7) {
            if (tieneLetra && tieneSimbolo && tieneNumero) {
                profStrengthBar.style.width = '100%';
                profStrengthBar.className = 'progress-bar bg-success';
                profStrengthText.textContent = 'Contraseña Alta / Segura';
                profStrengthText.className = 'form-text text-success';
            } else {
                profStrengthBar.style.width = '65%';
                profStrengthBar.className = 'progress-bar bg-warning';
                profStrengthText.textContent = 'Contraseña Media (agrega letras, números y símbolos)';
                profStrengthText.className = 'form-text text-warning';
            }
        }
    }

    if (profPasswordInput) {
        profPasswordInput.addEventListener('input', evaluarFortalezaProfPassword);
    }

    // 3. Abrir modal y cargar datos del usuario desde backend
    if (btnAbrirPerfil) {
        btnAbrirPerfil.addEventListener('click', function (e) {
            e.preventDefault();

            fetch('?c=profile&a=get')
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const user = res.data;

                        // Asignar datos a los campos
                        document.getElementById('prof_documento_id').value = user.documento_id || '';
                        document.getElementById('prof_username').value     = user.username || '';
                        document.getElementById('prof_nombres').value      = user.nombres || '';
                        document.getElementById('prof_apellidos').value    = user.apellidos || '';
                        document.getElementById('prof_rol_name').value     = user.rol_name || '';
                        document.getElementById('prof_correo').value       = user.correo || '';
                        document.getElementById('prof_telefono').value     = user.telefono || '';

                        // Resetear el estado del campo contraseña y el ojito
                        if (profPasswordInput) {
                            profPasswordInput.value = '';
                            profPasswordInput.type = 'password';
                        }
                        if (toggleProfIcon) {
                            toggleProfIcon.classList.remove('fa-eye-slash');
                            toggleProfIcon.classList.add('fa-eye');
                        }

                        evaluarFortalezaProfPassword();

                        if (profileModal) profileModal.show();
                    } else {
                        Swal.fire('Error', res.message || 'No se pudieron obtener los datos', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                });
        });
    }

    // 4. Lógica del ojo (mostrar/ocultar contraseña)
    if (toggleProfPasswordBtn && profPasswordInput && toggleProfIcon) {
        toggleProfPasswordBtn.addEventListener('click', function () {
            const isPassword = profPasswordInput.type === 'password';
            profPasswordInput.type = isPassword ? 'text' : 'password';

            toggleProfIcon.classList.toggle('fa-eye', !isPassword);
            toggleProfIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    }

    // 5. Enviar actualización del perfil
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const passwordVal = profPasswordInput ? profPasswordInput.value.trim() : '';

            // Validar que la nueva contraseña tenga al menos 5 caracteres si el usuario decidió escribir una
            if (passwordVal !== '' && passwordVal.length < 5) {
                Swal.fire('Atención', 'La nueva contraseña debe tener al menos 5 caracteres.', 'warning');
                return;
            }

            const formData = new FormData(this);

            fetch('?c=profile&a=update', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (profileModal) profileModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Ocurrió un error al intentar guardar.', 'error');
            });
        });
    }
});