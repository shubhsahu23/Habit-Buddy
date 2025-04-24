<?php
include 'd.php';
$current_user = $_GET['user'] ?? '';
$result = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reviews</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif']
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body >
<!-- Navbar -->
<nav class="bg-white/30 backdrop-blur-md shadow-md w-full fixed top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Logo -->
      <div class="flex justify-center space-x-12 items-center h-16 ">
        <div class=" text-2xl font-bold text-blue-600"><a href="index.php">HabitBuddy</a></div>
      <!-- Desktop Menu -->
        <div class="hidden md:flex space-x-6">
            <a href="calendar.html" class="text-gray-700 hover:text-blue-600">Calendar</a>
            <a href="session.html" class="text-gray-700 hover:text-blue-600">Session</a>
            <a href="about.html" class="text-gray-700 hover:text-blue-600">About Us</a>
            <a href="contact.html" class="text-gray-700 hover:text-blue-600">Contact Us</a>
            <a href="review.php" class="underline underline-offset-8 text-blue-600">Reviews</a>
        </div>
      <!-- Mobile Toggle Button -->
      <button id="menu-btn" class="md:hidden focus:outline-none">
        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

   <!-- Mobile Menu -->
   <div id="mobile-menu" class="md:hidden hidden px-4 pb-4 space-y-2">
    <a href="calendar.html" class="block text-gray-700 hover:text-blue-600">Calendar</a>
    <a href="session.html" class="block text-gray-700 hover:text-blue-600">Session</a>
    <a href="about.html" class="block text-gray-700 hover:text-blue-600">About Us</a>
    <a href="contact.html" class="block text-gray-700 hover:text-blue-600">Contact Us</a>
    <a href="review.php" class="block text-gray-700 hover:text-blue-600">Reviews</a>
  </div>
</nav>
  <script>
      const menuBtn = document.getElementById('menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');

      menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      });
  </script>

  <div class="max-w-3xl mx-auto mt-16 px-4 pt-8">
    <!-- Review Form -->
    <div class="bg-white shadow-2xl rounded-2xl p-8 mb-12 transition hover:shadow-blue-200">
      <h1 class="text-3xl font-extrabold mb-6 text-center text-indigo-800">📝 Share Your Thoughts</h1>
      <form action="submit.php" method="POST" class="space-y-5">
        <input type="text" name="name" placeholder="Your Name" required
               class="w-full p-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
               value="<?= htmlspecialchars($current_user) ?>" />
        <textarea name="comment" placeholder="Write your review here..." required
                  class="w-full p-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none min-h-[100px]"></textarea>
        <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg">
          🚀 Submit Review
        </button>
      </form>
    </div>

    <!-- Reviews Section -->
    <div class="bg-white shadow-2xl rounded-2xl p-8 mb-6">
      <h2 class="text-2xl font-bold mb-6 text-indigo-800 flex items-center gap-2">🌟 What People Say</h2>

      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="mb-6 pb-4 border-b border-gray-200 group transition duration-200 hover:bg-indigo-50 rounded-xl px-4 py-2">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-semibold text-lg text-gray-900"><?= htmlspecialchars($row['name']) ?></p>
                <p class="text-gray-700 mt-2 whitespace-pre-line"><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
              </div>
              <div class="text-right text-sm text-gray-500 mt-1"><?= $row['created_at'] ?></div>
            </div>

            <?php if ($current_user && $current_user === $row['name']): ?>
              <form action="submit.php" method="POST" class="mt-3 text-right">
                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="delete_name" value="<?= htmlspecialchars($current_user) ?>">
                <button type="submit"
                        class="text-red-600 text-sm font-medium hover:underline hover:text-red-700 transition">
                  ❌ Delete
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-gray-500 text-center">No reviews yet. Be the first to leave one!</p>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>
