<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied | Laboratory Lending System</title>
    <link href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 text-center">
    <div>
        <h1 class="display-4">403</h1>
        <p class="text-muted">You do not have permission to access this page.</p>
        <a href="<?= url(Auth::homePath(Auth::user()['role'])) ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
