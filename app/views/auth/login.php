<?php
/**
 * LOGIN PAGE view (shown by AuthController::showLogin).
 * Sections: brand panel (left), alert messages, login form (right).
 */
?>
<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">

        <!-- Left brand panel -->
        <div class="col-lg-6 brand-panel d-flex flex-column justify-content-between">
            <div>
                <p class="brand-title mb-3"><?= e(APP_NAME) ?></p>
                <hr class="brand-divider">
            </div>
            <h1 class="brand-tagline d-none d-lg-block">
                Laboratory<br>equipment,<br>lent and returned<br>on time.
            </h1>
        </div>

        <!-- Right login form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white">
            <div class="login-box">
                <h2 class="login-heading">Welcome back</h2>
                <p class="login-subtitle">Log in with your authorised staff account</p>

                <?php /* Alert messages: error (red) and success (green) with close button */ ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-icon alert-dismissible fade show py-2 small" role="alert">
                        <span><?= e($error) ?></span>
                        <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-icon alert-dismissible fade show py-2 small" data-auto-dismiss="5000" role="alert">
                        <span><?= e($success) ?></span>
                        <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php /* Login form: the "required" class on a label adds the red * */ ?>
                <form action="<?= url('login') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label required">Email address</label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="name@university.lk" value="<?= e($oldEmail) ?>"
                               required autofocus>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="mb-1">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Enter your password" required>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>

                    <p class="forgot-text text-end mb-3">Forgot password? Contact the Administrator.</p>

                    <button type="submit" class="btn btn-brand w-100">Log In</button>
                </form>
            </div>
        </div>

    </div>
</div>
