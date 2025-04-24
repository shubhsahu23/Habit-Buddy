<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

include 'd.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Handle image upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $target_file;
    }

    // Update user data
    if ($profile_picture) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $profile_picture, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $user_id);
    }

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}

// Handle Delete
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    session_destroy();
    header("Location: signup.php");
    exit();
}

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-black pt-20 min-h-screen">

<!-- Navbar -->
<nav class="bg-black/30 backdrop-blur-md shadow-md w-full fixed top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Logo -->
      <div class="flex justify-center space-x-12 items-center h-16 ">
        <div class=" text-2xl font-bold text-white hover:text-yellow-300"><a href="index.php">HabitBuddy</a></div>
      <!-- Desktop Menu -->
        <div class="hidden md:flex space-x-6">
            <a href="calendar.html" class="text-white hover:text-yellow-300">Calendar</a>
            <a href="session.html" class="text-white hover:text-yellow-300">Session</a>
            <a href="about.html" class="text-white hover:text-yellow-300">About Us</a>
            <a href="contact.html" class="text-white hover:text-yellow-300">Contact Us</a>
            <a href="review.php" class="text-white hover:text-yellow-300">Reviews</a>
        </div>
      <!-- Mobile Toggle Button -->
      <button id="menu-btn" class="md:hidden focus:outline-none">
        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

   <!-- Mobile Menu -->
   <div id="mobile-menu" class="md:hidden hidden px-4 pb-4 space-y-2">
    <a href="myhabit.php" class="block text-white hover:text-yellow-300">Habits</a>
    <a href="session.html" class="block text-white hover:text-yellow-300">Session</a>
    <a href="#" class="block text-white hover:text-yellow-300">About Us</a>
    <a href="#" class="block text-white hover:text-yellow-300">Contact Us</a>
    <a href="review.php" class="block text-white hover:text-yellow-300">Reviews</a>
  </div>
</nav>
  <script>
      const menuBtn = document.getElementById('menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');

      menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      });
  </script>
<!-- Enhanced Profile Section -->
<div class="max-w-2xl mx-auto mt-6 bg-white shadow-xl rounded-xl overflow-hidden">
  <!-- Profile Header -->
  <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 h-32 relative">
    <?php if (!empty($user['profile_picture'])): ?>
      <div class="absolute -bottom-16 left-1/2 transform -translate-x-1/2">
        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile"
             class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-lg">
      </div>
    <?php endif; ?>
  </div>

  <!-- Profile Content -->
  <div class="px-8 pt-20 pb-8">
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($user['name']) ?></h2>
      <p class="text-gray-600 mt-2"><?= htmlspecialchars($user['email']) ?></p>
    </div>

    <?php if ($message): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
        <p><?= htmlspecialchars($message) ?></p>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-gray-700 font-medium mb-2">Full Name</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
              <i class="fas fa-user"></i>
            </span>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
          </div>
        </div>

        <div>
          <label class="block text-gray-700 font-medium mb-2">Email Address</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
              <i class="fas fa-envelope"></i>
            </span>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
          </div>
        </div>
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-2">Profile Picture</label>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <input type="file" name="profile_picture" id="profile-picture" class="hidden">
            <label for="profile-picture" class="cursor-pointer">
              <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 hover:border-blue-400 transition">
                <?php if (!empty($user['profile_picture'])): ?>
                  <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Current Profile" class="w-full h-full object-cover">
                <?php else: ?>
                  <i class="fas fa-camera text-gray-400 text-xl"></i>
                <?php endif; ?>
              </div>
            </label>
          </div>
          <div>
            <label for="profile-picture" class="block text-sm text-gray-500 mb-1">Click to change photo</label>
            <p class="text-xs text-gray-400">JPG, GIF or PNG. Max size 2MB</p>
          </div>
        </div>
      </div>

      <div class="pt-6 flex justify-end space-x-4">
        <button type="submit" name="delete"
                onclick="return confirm('Are you sure you want to delete your account?')"
                class="px-6 py-3 border border-red-500 text-red-500 rounded-lg font-medium hover:bg-red-50 transition flex items-center">
          <i class="fas fa-trash-alt mr-2"></i> Delete Account
        </button>
        <button type="submit" name="update"
                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition flex items-center">
          <i class="fas fa-save mr-2"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

</body>
</html>