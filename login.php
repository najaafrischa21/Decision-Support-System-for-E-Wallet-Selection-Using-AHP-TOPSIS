<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #007bff;
      height: 100vh;
      overflow: hidden;
    }

    .login-wrapper {
      display: flex;
      height: 100%;
      width: 100%;
    }

    .left-box {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2;
    }

    .login-card {
      background: white;
      padding: 40px;
      border-radius: 20px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    .login-card h3 {
      text-align: center;
      color: #007bff;
      margin-bottom: 30px;
    }

    .form-control {
      border-radius: 8px;
    }

    .btn-login {
      background-color: #007bff;
      color: white;
      font-weight: bold;
      border-radius: 8px;
    }

    .btn-login:hover {
      background-color: #0056b3;
    }

    .right-image {
      flex: 1;
      background: white;
      border-top-left-radius: 100% 100%;
      border-bottom-left-radius: 100% 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    .right-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    @media (max-width: 768px) {
      .right-image {
        display: none;
      }

      .login-card {
        margin: 20px;
      }
    }
	
	
  </style>
</head>
<body>

<div class="login-wrapper">
  <!-- Kolom kiri untuk form login -->
  <div class="left-box">
    <div class="login-card">
      <h3>Login Admin</h3>
      <form action="aksi_login.php" method="POST">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" name="username" id="username" required>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-login">Login</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Kolom kanan untuk gambar dengan bentuk setengah lingkaran -->
  <div class="right-image">
    <img src="gambar/ewallet.png" alt="E-Wallet">
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
