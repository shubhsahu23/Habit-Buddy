<?php
$mysqli = new mysqli('localhost', 'root', '', 'habitbuddy');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['e'];
    $password = $_POST['p'];

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert = $mysqli->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $insert->bind_param("ss", $email, $hashed_password);

        if ($insert->execute()) {
            $success = "Account created successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-100 via-indigo-200 to-purple-100">
  <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-2xl shadow-xl">
    <div class="text-center">
      <h2 class="text-3xl font-extrabold text-gray-900">Create Account</h2>
      <p class="mt-2 text-sm text-gray-600">Please fill in your details to register</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="text-red-600 text-sm text-center font-medium"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
      <div class="text-green-600 text-sm text-center font-medium"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="signup.php" method="POST" class="mt-6 space-y-6">
      <div class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
          <input type="email" name="e" id="email" required
                 class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                 placeholder="Enter your email"/>
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input type="password" name="p" id="password" required
                 class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                 placeholder="Create a password"/>
        </div>
      </div>

      <button type="submit"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
        Create Account
      </button>
    </form>
    
    <p class="mt-6 text-center text-sm text-gray-600">
      Already have an account?
      <a href="signin.php" class="font-medium text-indigo-600 hover:text-indigo-500">Sign in</a>
    </p>
  </div>
</body>
</html>
