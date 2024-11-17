<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FTP Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>FTP Client Login</h1>
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('ftp.connect') }}">
        @csrf
        <div class="mb-3">
            <label>FTP Host</label>
            <input type="text" name="ftp_host" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>FTP Username</label>
            <input type="text" name="ftp_username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>FTP Password</label>
            <input type="password" name="ftp_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Connect</button>
    </form>
</div>
</body>
</html>
