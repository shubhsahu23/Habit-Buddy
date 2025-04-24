<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'habitbuddy');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password);
    
    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = explode('@', $email)[0];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Login</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-indigo-200 to-purple-100">
        <div class="w-full max-w-sm sm:max-w-md px-6 py-10 bg-white rounded-3xl shadow-2xl space-y-8">
            <div class="text-center">
                <h2 class="text-4xl font-extrabold text-gray-800">Welcome Back 👋</h2>
                <p class="mt-2 text-sm text-gray-600">Sign in to continue your journey</p>
            </div>
            
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    <form action="signin.php" method="POST" class="space-y-6">
      <div class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
          <input type="email" name="email" id="email" required
            class="w-full px-4 py-3 mt-1 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
            placeholder="you@example.com" />
        </div>
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
          <input type="password" name="password" id="password" required
            class="w-full px-4 py-3 mt-1 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
            placeholder="••••••••" />
        </div>
      </div>

      <button type="submit"
        class="w-full py-3 px-4 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition duration-200 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Sign In
      </button>
    </form>

    <div class="text-center text-sm text-gray-600">
      Don’t have an account?
      <a href="signup.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign Up</a>
    </div>
  </div>
</body>
</html>
