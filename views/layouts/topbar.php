<!--**********************************
    Nav header start
***********************************-->
<div class="nav-header">
    <a href="index.php" class="brand-logo">
        <img src="<?php echo BASE_URL; ?>/assets/images/logo-image.png" class="logo-abbr" alt="Logo">
        <img src="<?php echo BASE_URL; ?>/assets/images/logo-word.png" class="brand-title" alt="Lezato">
    </a>
    <div class="nav-control">
        <div class="hamburger">
            <span class="line" style="background-color: #6c757d;"></span>
            <span class="line" style="background-color: #6c757d;"></span>
            <span class="line" style="background-color: #6c757d;"></span>
        </div>
    </div>
</div>
<!--**********************************
    Nav header end
***********************************-->

<!--**********************************
    Header start
***********************************-->
<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left"></div>
                <ul class="navbar-nav header-right">

                    <li class="nav-item d-flex align-items-center me-3">
                        <div class="header-info text-end">
                            <span class="fs-16 font-w600 d-block text-dark">
                                <?= htmlspecialchars(trim($_SESSION['user_nombres'] . ' ' . $_SESSION['user_apellidos']) ?: $_SESSION['user_name']); ?>
                            </span>
                            <small class="font-w400 d-block text-muted">
                                <?= htmlspecialchars($_SESSION['user_rol']); ?>
                            </small>
                        </div>
                    </li>

                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link p-1 rounded-circle" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" style="background: linear-gradient(135deg, #d9383a 0%, #ff6b6b 100%); display: inline-flex; align-items: center; justify-content: center;">
                            <img src="<?php echo BASE_URL; ?>/assets/images/foto.png" width="50" height="50" style="border-radius: 50%; object-fit: cover; border: 2px solid #ffffff;" alt="Usuario"/>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:void(0);" id="btn-abrir-perfil" class="dropdown-item ai-icon">
                                <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" style="color: #6c757d;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span class="ms-2">Perfil</span>
                            </a>

                            <a href="<?php echo BASE_URL; ?>?c=auth&a=logout" class="dropdown-item ai-icon">
                                <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span class="ms-2">Cerrar Sesión</span>
                            </a>
                        </div>
                    </li>
                    
                </ul>
            </div>
        </nav>
    </div>
</div>

<!-- Profile Modal -->
<?php include_once __DIR__ . '/../profile/profile_modal.php'; ?>
<!--**********************************
    Header end ti-comment-alt
***********************************-->