<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../layouts/head/head_meta.php'; ?>
    <?php include __DIR__ . '/../layouts/head/head_css.php'; ?>

</head>
<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <div class="text-center mb-3">
                                        <a href="index.php">
                                            <img src="<?php echo BASE_URL; ?>/assets/images/logo-full.png" alt="Logo" class="img-fluid" style="max-height: 160px; width: auto;">
                                        </a>
                                    </div>
                                    <h4 class="text-center mb-4">Login</h4>
                                    
                                    <form id="loginForm" action="<?php echo BASE_URL; ?>/?c=auth&a=process_login" method="POST">
                                        <!-- Campo de Correo Electrónico -->
                                        <div class="mb-3">
                                            <label class="mb-1"><strong>Correo electrónico</strong></label>
                                            <input type="email" class="form-control" id="emailInput" name="email" placeholder="tucorreo@example.com" required autocomplete="email">
                                        </div>
                                        <!-- Campo de Contraseña Ocultable -->
                                        <div class="mb-3">
                                            <label class="mb-1"><strong>Contraseña</strong></label>
                                            <div class="position-relative">
                                                <input type="password" class="form-control" id="passwordInput" name="password" placeholder="Contraseña" style="padding-right: 45px;" autocomplete="current-password" required>
                                                <span id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6e6e6e; z-index: 10;">
                                                    <i class="fa fa-eye" id="toggleIcon"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <style>
                                        .btn-custom-red {
                                            background-color: #d9383a !important;
                                            border-color: #d9383a !important;
                                            color: #ffffff !important;
                                        }
                                        .btn-custom-red:hover {
                                            background-color: #b82d2f !important; /* Tono un poco más oscuro al pasar el cursor */
                                            border-color: #b82d2f !important;
                                        }
                                        </style>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-custom-red btn-block">Ingresar</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Base de la plantilla -->
    <?php include __DIR__ . '/../layouts/scripts.php'; ?>

    <!-- Manejo de Alertas PHP con SweetAlert2 -->
    <?php if (isset($error) && $error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: '¡Acceso Denegado!',
            text: '<?php echo $error; ?>',
            confirmButtonColor: '#ff7301',
            background: '#ffffff',
            customClass: { popup: 'shadow-lg' },
            heightAuto: false // <-- AÑADE ESTA LÍNEA
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Completado!',
            text: '<?php echo $success; ?>',
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            heightAuto: false // <-- AÑADE ESTA LÍNEA TAMBIÉN
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
    <?php endif; ?>

    <!-- Script de login -->
    <script src="<?php echo BASE_URL; ?>/views/auth/login.js?v=<?php echo time(); ?>"></script>
</body>
</html>