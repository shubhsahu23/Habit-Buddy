<?php 
session_start();
include 'd.php';
// Handle saving focus hours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hours']) && isset($_SESSION['user_id'])) {
    $hours = floatval($_POST['hours']);
    $user_id = $_SESSION['user_id'];
    $date = date('Y-m-d');
    
    // Check if entry exists for today
    $check_sql = "SELECT id FROM focus_hours WHERE user_id = ? AND date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $user_id, $date);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing entry
        $update_sql = "UPDATE focus_hours SET hours = ? WHERE user_id = ? AND date = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("dis", $hours, $user_id, $date);
        $update_stmt->execute();
    } else {
        // Insert new entry
        $insert_sql = "INSERT INTO focus_hours (user_id, date, hours) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isd", $user_id, $date, $hours);
        $insert_stmt->execute();
    }
    
    // Return success response
    echo json_encode(['success' => true]);
    exit;
}

// Get user's focus data for the year if logged in
$focus_data = array_fill(0, 365, 0);
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $year = date('Y');
    
    $sql = "SELECT DAYOFYEAR(date) as day, hours FROM focus_hours 
            WHERE user_id = ? AND YEAR(date) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $day_index = $row['day'] - 1; // Convert to 0-based index
        if ($day_index >= 0 && $day_index < 365) {
            $focus_data[$day_index] = $row['hours'];
        }
    }
    
    // Get today's hours if available
    $today_date = date('Y-m-d');
    $today_sql = "SELECT hours FROM focus_hours WHERE user_id = ? AND date = ?";
    $today_stmt = $conn->prepare($today_sql);
    $today_stmt->bind_param("is", $user_id, $today_date);
    $today_stmt->execute();
    $today_result = $today_stmt->get_result();
    $today_hours = 0;
    if ($today_result->num_rows > 0) {
        $row = $today_result->fetch_assoc();
        $today_hours = $row['hours'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>HABITBUDDY</title>
  <!-- <link rel="icon" href="icon.png" type="image/x-icon"> -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
  <style>
    .heat-cell {
      width: 16px;
      height: 16px;
      margin: 2px;
      border-radius: 4px;
      transition: all 0.3s ease;
    }
    .heat-cell:hover {
      transform: scale(1.1);
      box-shadow: 0 0 4px rgba(0,0,0,0.2);
    }
    .day-label {
      font-size: 12px;
      color: #6b7280;
      margin-right: 8px;
      width: 30px;
      text-align: right;
    }
    .highlight-day {
      font-weight: 600;
      color: #4b5563;
    }
    .week-row {
      display: flex;
      align-items: center;
      margin-bottom: 4px;
    }
    .heatmap-container {
      display: flex;
      flex-direction: column;
    }
    .month-labels {
      display: flex;
      margin-left: 38px;
      margin-bottom: 5px;
      height: 20px;
    }
    .month-label {
      font-size: 12px;
      color: #6b7280;
      text-align: left;
      white-space: nowrap;
      width: 84px;
    }
    .profile-menu {
      transition: all 0.3s ease;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
    }
    .profile-menu.show {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
    #tooltip {
      position: absolute;
      background: rgba(0, 0, 0, 0.8);
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 12px;
      pointer-events: none;
      z-index: 100;
      transition: opacity 0.2s;
      opacity: 0;
    }
    #tooltip.show {
      opacity: 1;
    }
  </style>
</head>
<body class="font-sans bg-black">
    <!-- Navbar -->
    <nav class="bg-black/30 backdrop-blur-md shadow-md w-full fixed top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      
      <!-- Logo -->
      <div class="text-2xl font-bold text-white hover:text-yellow-300">
        <a href="index.php">HabitBuddy</a>
      </div>

      <!-- Desktop Menu -->
      <div class="hidden md:flex space-x-6 items-center">
        <a href="calendar.html" class="text-white hover:text-yellow-300 transition duration-300">Calendar</a>
        <a href="session.html" class="text-white hover:text-yellow-300 transition duration-300">Session</a>
        <a href="about.html" class="text-white hover:text-yellow-300 transition duration-300">About Us</a>
        <a href="contact.html" class="text-white hover:text-yellow-300 transition duration-300">Contact Us</a>
        <a href="review.php" class="text-white hover:text-yellow-300 transition duration-300">Reviews</a>
      </div>

      <!-- Auth Buttons / Profile -->
      <div class="hidden md:flex items-center space-x-4">
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="relative group">
            <button id="profile-btn" class="flex items-center gap-2 hover:text-cyan-600 focus:outline-none transition">
              <div class="w-8 h-8 rounded-full bg-red-800 flex items-center justify-center text-white font-semibold">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
              </div>
              <span class="text-white"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </button>
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50">
              <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
              <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
            </div>
          </div>
        <?php else: ?>
          <a href="signin.php" class="border border-cyan-500 text-cyan-500 hover:bg-white px-4 py-2 rounded transition duration-200">Sign In</a>
          <a href="signup.php" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded transition duration-200">Sign Up</a>
        <?php endif; ?>
      </div>

      <!-- Mobile Toggle -->
      <button id="menu-btn" class="md:hidden text-white focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="md:hidden hidden px-4 pb-4 space-y-2 bg-black/30 backdrop-blur-md">
    <a href="calendar.html" class="block text-white hover:text-yellow-300">Calendar</a>
    <a href="session.html" class="block text-white hover:text-yellow-300">Session</a>
    <a href="about.html" class="block text-white hover:text-yellow-300">About Us</a>
    <a href="contact.html" class="block text-white hover:text-yellow-300">Contact Us</a>
    <a href="review.php" class="block text-white hover:text-yellow-300">Reviews</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="pt-2 border-t border-cyan-200">
        <a href="profile.php" class="block px-4 py-2 text-sm text-white hover:text-yellow-100">Profile</a>
        <a href="logout.php" class="block px-4 py-2 text-sm text-white hover:text-yellow-100">Sign out</a>
      </div>
    <?php else: ?>
      <a href="signin.php" class="block border border-cyan-500 text-cyan-500 hover:bg-cyan-50 px-4 py-2 rounded text-center">Sign In</a>
      <a href="signup.php" class="block bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded text-center">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>
  <script>
    // Mobile menu toggle
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (menuBtn) {
      menuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
      });
    }

    // Profile dropdown toggle
    const profileBtn = document.getElementById('profile-btn');
    const profileMenu = document.getElementById('profile-menu');
    if (profileBtn) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.classList.toggle('show');
      });

      // Close when clicking outside
      document.addEventListener('click', () => {
        profileMenu.classList.remove('show');
      });
    }
  </script>

<section class="relative h-screen bg-cover bg-center pt-12 " 
         style="background-image: url('man.jpg');">
  <!-- Overlay -->
  <div class="absolute inset-0 bg-opacity-70"></div>

  <!-- Content -->
  <div class="relative z-10 flex flex-col items-center justify-center text-center text-white h-full px-4">
    <div class="text-4xl md:text-6xl font-bold leading-snug mb-6 drop-shadow-lg">
      <span>We first make our habits,</span><br/>
      <span>then our habits make us.</span>
    </div>

    <p class="text-lg md:text-xl italic mb-8 text-gray-100 drop-shadow-md">
      — Aditya Thakur
    </p>

    <a href="#progress-section"
       class="bg-white text-black font-semibold py-3 px-8 rounded-full shadow-lg hover:bg-yellow-100 transition-all duration-300 ease-in-out">
      Track Your Progress
    </a>
  </div>
</section>

<!-- Progress Dashboard -->
<div id="progress-section" class="flex flex-col items-center pt-16 pb-12">
  <div class="w-full max-w-6xl px-4 ">
    <!-- Enhanced Heatmap with Goals -->
    <div class="bg-black text-white p-6 rounded-xl shadow-md shadow-yellow-100 mb-8">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Consistency Board</h1>
      </div>
      <!-- Month Labels -->
      <div id="month-labels" class="month-labels"></div>
      
      <!-- Heatmap Container -->
      <div class="heatmap-container">
        <div id="heatmap" class="heatmap-grid"></div>
      </div>

      <div class="mt-4 flex justify-between text-xs text-white">
        <span>Less</span>
        <div class="flex space-x-1">
          <div class="w-4 h-4 rounded-sm bg-green-100"></div>
          <div class="w-4 h-4 rounded-sm bg-green-200"></div>
          <div class="w-4 h-4 rounded-sm bg-green-300"></div>
          <div class="w-4 h-4 rounded-sm bg-green-500"></div>
          <div class="w-4 h-4 rounded-sm bg-green-700"></div>
        </div>
        <span>More</span>
      </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">      
        <!-- Today's Focus Section -->
        <div class="bg-gradient-to-l from-white to-yellow-100 p-6 rounded-xl shadow">
          <h2 class="text-xl font-bold mb-4">Today's Focused Hours</h2>
          <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-1">
              <div class="bg-gradient-to-l from-white to-yellow-100 p-4 rounded-lg" >
                <p class="font-medium mb-2">Today: <span id="today-display" class="font-semibold">0 hours</span> <span id="today-status" class="text-sm"></span></p>
                <div class="flex items-center space-x-2 mb-3">
                  <input type="number" id="today-input" placeholder="Enter hours (e.g., 1.5)" 
                         class="border p-2 rounded flex-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                         min="0" max="24" step="0.1">
                  <button onclick="saveTodayHours()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
                    Save
                  </button>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                  <span>Goal: 2 hours</span>
                  <span id="today-percentage">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                  <div id="today-progress-bar" class="bg-blue-500 h-2 rounded-full" style="width: 0%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <!-- Quick Actions -->
      <div class="bg-gradient-to-r from-white to-yellow-100 p-6 rounded-xl shadow">
        <h3 class="font-semibold text-lg mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
          <a href="session.html" class="bg-blue-100 text-blue-600 p-3 rounded-lg hover:bg-blue-200 transition flex flex-col items-center">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span class="text-xs">New Session</span>
          </a>
          <a href="myhabit.php" class="bg-green-100 text-green-600 p-3 rounded-lg hover:bg-green-200 transition flex flex-col items-center">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="text-xs">Add Habit</span>
          </a>
          <a href="calendar.html" class="bg-yellow-100 text-yellow-700 p-3 rounded-lg hover:bg-yellow-200 transition flex flex-col items-center">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="text-xs">Calendar</span>
          </a>
        </div>
      </div>
    </div>

    
    <!-- Motivational Quote -->
    <div class="bg-gradient-to-b from-yellow-300 to-yellow-500 text-black p-6 rounded-xl shadow-lg mb-8">
      <h3 class="font-bold text-xl mb-3">Today's Motivation</h3>
      <p id="daily-quote" class="text-lg italic mb-4">"The secret of getting ahead is getting started."</p>
      <p id="quote-author" class="text-right">— Mark Twain</p>
    </div>

    <!-- Tooltip for heatmap -->
    <div id="tooltip" class="hidden"></div>
    
<script>
  // Data and configuration
  const heatmap = document.getElementById('heatmap');
  const monthLabels = document.getElementById('month-labels');
  const tooltip = document.getElementById('tooltip');
  const weeks = 52;
  const days = 7;
  const yearDays = 365;
  const today = new Date();
  
  // Initialize data from PHP
  let data = <?php echo json_encode($focus_data); ?>;
  
  const dayNames = ["Wed", "Thu", "Fri", "Sat", "Sun", "Mon", "Tue"];
  const monthNames = [];
  // const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

  // Motivational quotes
  const quotes = [
  { text: "The secret of getting ahead is getting started.", author: "Mark Twain" },
  { text: "You don't have to be great to start, but you have to start to be great.", author: "Zig Ziglar" },
  { text: "Discipline is choosing between what you want now and what you want most.", author: "Abraham Lincoln" },
  { text: "Success is the sum of small efforts, repeated day in and day out.", author: "Robert Collier" },
  { text: "We first make our habits, then our habits make us.", author: "John Dryden" },
  { text: "The chains of habit are too light to be felt until they are too heavy to be broken.", author: "Warren Buffett" },
  { text: "Small daily improvements are the key to staggering long-term results.", author: "Unknown" }
];
let quoteIndex = 0;
function showRotatingQuote() {
  const quoteElement = document.getElementById('daily-quote');
  const authorElement = document.getElementById('quote-author');

  quoteElement.textContent = `"${quotes[quoteIndex].text}"`;
  authorElement.textContent = `— ${quotes[quoteIndex].author}`;

  quoteIndex = (quoteIndex + 1) % quotes.length; // Loop back to start
}
// Initial quote
showRotatingQuote();
// Change quote every 5 seconds (5000 ms)
setInterval(showRotatingQuote, 5000);


  // Initialize the page
  document.addEventListener('DOMContentLoaded', () => {
    renderHeatmap();
    showDailyQuote();
    loadTodayData();
  });

  // Load today's data
  function loadTodayData() {
    const todayIndex = getTodayIndex();
    const todayHours = data[todayIndex] || 0;
    
    // Update input field
    document.getElementById('today-input').value = todayHours > 0 ? todayHours : '';
    
    // Update display
    updateTodayDisplay(todayHours);
  }

  // Save today's focus hours
  function saveTodayHours() {
    const hours = parseFloat(document.getElementById('today-input').value);
    
    if (isNaN(hours) || hours < 0 || hours > 24) {
      alert("Please enter a valid number between 0 and 24.");
      return;
    }

    const todayIndex = getTodayIndex();
    data[todayIndex] = hours;
    
    <?php if (isset($_SESSION['user_id'])): ?>
      // Save to database via AJAX
      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `hours=${hours}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateUIAfterSave(hours);
        } else {
          alert('Failed to save hours. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
    <?php else: ?>
      // For non-logged in users, use localStorage
      localStorage.setItem(`habit-data-${today.getFullYear()}`, JSON.stringify(data));
      updateUIAfterSave(hours);
    <?php endif; ?>
  }

  function updateUIAfterSave(hours) {
    // Update displays
    updateTodayDisplay(hours);
    renderHeatmap();
    
    // Show confirmation
    const saveBtn = document.querySelector('#today-input + button');
    saveBtn.textContent = 'Saved!';
    saveBtn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
    saveBtn.classList.add('bg-green-500', 'hover:bg-green-600');
    
    setTimeout(() => {
      saveBtn.textContent = 'Save';
      saveBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
      saveBtn.classList.add('bg-blue-500', 'hover:bg-blue-600');
    }, 2000);
  }

  // Update today's display
  function updateTodayDisplay(hours) {
    document.getElementById('today-display').textContent = `${hours} hour${hours !== 1 ? 's' : ''}`;
    
    // Calculate progress percentage (goal is 3 hours)
    const progress = Math.min(100, (hours / 2) * 100);
    document.getElementById('today-progress-bar').style.width = `${progress}%`;
    document.getElementById('today-percentage').textContent = `${Math.round(progress)}%`;
    
    // Update status indicator
    const statusElement = document.getElementById('today-status');
    if (hours >= 2) {
      statusElement.textContent = '✅ Goal achieved!';
      statusElement.className = 'text-sm text-green-500';
    } else if (hours > 0) {
      statusElement.textContent = '🟡 Keep going!';
      statusElement.className = 'text-sm text-yellow-500';
    } else {
      statusElement.textContent = '🔴 No data yet';
      statusElement.className = 'text-sm text-red-500';
    }
  }

  // Heatmap functions
  function getTodayIndex() {
    const startOfYear = new Date(today.getFullYear(), 0, 1);
    const diff = Math.floor((today - startOfYear) / (1000 * 60 * 60 * 24));
    return diff;
  }

  function getColor(hours) {
    if (hours === 0) return '#e5e7eb';
    if (hours < 1) return '#bbf7d0';
    if (hours < 1.5) return '#86efac';
    if (hours < 2) return '#4ade80';
    if (hours < 3) return '#22c55e';
    return '#166534';
  }

  function renderHeatmap() {
    heatmap.innerHTML = '';
    monthLabels.innerHTML = '';
    let total = 0;
    let count = 0;
    const dailyGoal = 3;

    const startOfYear = new Date(today.getFullYear(), 0, 1);
    const weeksPerMonthLabel = 4;
    const weekWidth = 20;

    // Create month labels (every 4 weeks)
    for (let m = 0; m < weeks / weeksPerMonthLabel; m++) {
      const monthLabel = document.createElement('div');
      monthLabel.className = 'month-label';
      
      const middleWeek = m * weeksPerMonthLabel + Math.floor(weeksPerMonthLabel / 2);
      const middleDate = new Date(startOfYear);
      middleDate.setDate(startOfYear.getDate() + middleWeek * 7);
      monthLabel.textContent = monthNames[middleDate.getMonth()];
      
      monthLabels.appendChild(monthLabel);
    }

    // Create day rows
    for (let d = 0; d < days; d++) {
      const weekRow = document.createElement('div');
      weekRow.classList.add('week-row');

      // Add day label
      const dayLabel = document.createElement('div');
      dayLabel.className = 'day-label text-white';
      dayLabel.textContent = dayNames[d];
      if (d === 0 || d === 6) { // Sunday or Saturday
        dayLabel.classList.add('highlight-day');
      }
      weekRow.appendChild(dayLabel);

      // Add week columns
      for (let w = 0; w < weeks; w++) {
        const i = w * days + d;
        if (i >= data.length) break;

        const hours = data[i] || 0;
        const cellDate = new Date(startOfYear);
        cellDate.setDate(startOfYear.getDate() + i);

        const cell = document.createElement('div');
        cell.className = `heat-cell`;
        cell.style.backgroundColor = getColor(hours);

        // Tooltip content
        const tooltipText = `${cellDate.toDateString()} — ${hours} hour${hours !== 1 ? 's' : ''}`;

        // Hover logic
        cell.addEventListener('mouseenter', (e) => {
          tooltip.textContent = tooltipText;
          tooltip.style.display = 'block';
          tooltip.style.top = `${e.pageY - 40}px`;
          tooltip.style.left = `${e.pageX + 15}px`;
          tooltip.classList.add('show');
        });
        
        cell.addEventListener('mousemove', (e) => {
          tooltip.style.top = `${e.pageY - 40}px`;
          tooltip.style.left = `${e.pageX + 15}px`;
        });
        
        cell.addEventListener('mouseleave', () => {
          tooltip.classList.remove('show');
          setTimeout(() => tooltip.style.display = 'none', 200);
        });

        weekRow.appendChild(cell);
        
        if (hours > 0) {
          total += hours;
          count++;
        }
      }
      heatmap.appendChild(weekRow);
    }
  }


  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });
  });
</script>
</body>
</html>