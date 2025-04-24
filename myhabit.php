<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Habit</title>
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
  <style>
    .habit-day {
      transition: all 0.2s ease;
    }
    .habit-day:hover {
      transform: scale(1.1);
    }
    .fade-in {
      animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .time-inputs {
      display: flex;
      gap: 10px;
    }
    .time-inputs select {
      flex: 1;
    }
  </style>
</head>
<body class="bg-gray-50">
 <!-- Navbar -->
 <nav class="bg-white/30 backdrop-blur-md shadow-md w-full fixed top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Logo -->
      <div class="flex justify-center space-x-12 items-center h-16 ">
        <div class=" text-2xl font-bold text-blue-600"><a href="index.php">HabitBuddy</a></div>
      <!-- Desktop Menu -->
        <div class="hidden md:flex space-x-6">
            <a href="myhabit.php" class="underline underline-offset-8 text-blue-600">Habit</a>
            <a href="session.html" class="text-gray-700 hover:text-blue-600">Session</a>
            <a href="about.html" class="text-gray-700 hover:text-blue-600">About Us</a>
            <a href="contact.html" class="text-gray-700 hover:text-blue-600">Contact Us</a>
            <a href="review.php" class="text-gray-700 hover:text-blue-600">Reviews</a>
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
  <div class="max-w-3xl mx-auto pt-24">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">My Habits</h1>
      <button id="addHabitBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
        <span class="mr-2">+</span> Add Habit
      </button>
    </div>

    <div id="habits" class="space-y-4"></div>
  </div>

  <!-- Add Habit Modal -->
  <div id="addHabitModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
      <h2 class="text-xl font-semibold mb-4">Add New Habit</h2>
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Habit Name*</label>
        <input id="habitName" type="text" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Description</label>
        <textarea id="habitDesc" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
      </div>
      
      <!-- New Time Section -->
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Preferred Time</label>
        <div class="time-inputs">
          <select id="habitHour" class="p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Hour</option>
            <option value="00">12 AM</option>
            <option value="01">1 AM</option>
            <option value="02">2 AM</option>
            <option value="03">3 AM</option>
            <option value="04">4 AM</option>
            <option value="05">5 AM</option>
            <option value="06">6 AM</option>
            <option value="07">7 AM</option>
            <option value="08">8 AM</option>
            <option value="09">9 AM</option>
            <option value="10">10 AM</option>
            <option value="11">11 AM</option>
            <option value="12">12 PM</option>
            <option value="13">1 PM</option>
            <option value="14">2 PM</option>
            <option value="15">3 PM</option>
            <option value="16">4 PM</option>
            <option value="17">5 PM</option>
            <option value="18">6 PM</option>
            <option value="19">7 PM</option>
            <option value="20">8 PM</option>
            <option value="21">9 PM</option>
            <option value="22">10 PM</option>
            <option value="23">11 PM</option>
          </select>
          <select id="habitMinute" class="p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Minute</option>
            <option value="00">00</option>
            <option value="15">15</option>
            <option value="30">30</option>
            <option value="45">45</option>
          </select>
          <select id="habitDuration" class="p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Duration</option>
            <option value="5">5 min</option>
            <option value="10">10 min</option>
            <option value="15">15 min</option>
            <option value="30">30 min</option>
            <option value="45">45 min</option>
            <option value="60">1 hour</option>
            <option value="90">1.5 hours</option>
            <option value="120">2 hours</option>
          </select>
        </div>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button id="cancelAddHabit" type="button" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
        <button id="saveHabit" type="button" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
      </div>
    </div>
  </div>

  <script>
    // Initialize habits from localStorage or default array
    let habits = JSON.parse(localStorage.getItem('habits')) || [
      { 
        title: "Reading", 
        desc: "Read a book for at least 30 min", 
        time: { hour: "20", minute: "00", duration: "30" },
        activeDays: Array(7).fill(false) 
      },
      { 
        title: "Morning Run", 
        desc: "Run at least 3km", 
        time: { hour: "06", minute: "30", duration: "30" },
        activeDays: Array(7).fill(false) 
      }
    ];

    const days = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
    const habitsContainer = document.getElementById('habits');
    const addHabitBtn = document.getElementById('addHabitBtn');
    const addHabitModal = document.getElementById('addHabitModal');
    const cancelAddHabit = document.getElementById('cancelAddHabit');
    const saveHabit = document.getElementById('saveHabit');
    const habitName = document.getElementById('habitName');
    const habitDesc = document.getElementById('habitDesc');
    const habitHour = document.getElementById('habitHour');
    const habitMinute = document.getElementById('habitMinute');
    const habitDuration = document.getElementById('habitDuration');

    // Format time for display
    function formatTime(time) {
      if (!time || !time.hour) return "No time set";
      
      let hours = parseInt(time.hour);
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12; // Convert 0 to 12
      
      return `${hours}:${time.minute || '00'} ${ampm} for ${time.duration} min`;
    }

    // Render all habits
    function renderHabits() {
      habitsContainer.innerHTML = '';
      habits.forEach((habit, index) => {
        const habitCard = document.createElement('div');
        habitCard.className = "bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow fade-in";
        
        // Header with title and time
        const header = document.createElement('div');
        header.className = "flex justify-between items-start mb-2";
        
        const title = document.createElement('h2');
        title.className = "text-lg font-semibold text-gray-800";
        title.textContent = habit.title;
        
        const timeBadge = document.createElement('div');
        timeBadge.className = "text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full";
        timeBadge.textContent = formatTime(habit.time);
        
        header.appendChild(title);
        header.appendChild(timeBadge);
        
        // Description
        const desc = document.createElement('p');
        desc.className = "text-sm text-gray-600 mb-3";
        desc.textContent = habit.desc;
        
        // Days of week
        const daysContainer = document.createElement('div');
        daysContainer.className = "flex items-center space-x-2 mb-3";
        
        days.forEach((day, i) => {
          const btn = document.createElement('button');
          btn.textContent = day;
          btn.className = `habit-day w-8 h-8 rounded-full text-sm flex items-center justify-center ${
            habit.activeDays[i] ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          }`;
          
          btn.addEventListener('click', () => {
            habit.activeDays[i] = !habit.activeDays[i];
            btn.className = `habit-day w-8 h-8 rounded-full text-sm flex items-center justify-center ${
              habit.activeDays[i] ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`;
            saveToLocalStorage();
          });
          
          daysContainer.appendChild(btn);
        });
        
        // Actions
        const actions = document.createElement('div');
        actions.className = "flex justify-end space-x-3";
        
        const editBtn = document.createElement('button');
        editBtn.innerHTML = '✏️ Edit';
        editBtn.className = "text-sm text-blue-600 hover:text-blue-800 flex items-center";
        
        const deleteBtn = document.createElement('button');
        deleteBtn.innerHTML = '🗑️ Delete';
        deleteBtn.className = "text-sm text-red-500 hover:text-red-700 flex items-center";
        deleteBtn.addEventListener('click', () => {
          if (confirm(`Delete "${habit.title}"?`)) {
            habits.splice(index, 1);
            saveToLocalStorage();
            renderHabits();
          }
        });
        
        actions.appendChild(editBtn);
        actions.appendChild(deleteBtn);
        
        // Assemble card
        habitCard.appendChild(header);
        habitCard.appendChild(desc);
        habitCard.appendChild(daysContainer);
        habitCard.appendChild(actions);
        habitsContainer.appendChild(habitCard);
      });
    }

    // Save to localStorage
    function saveToLocalStorage() {
      localStorage.setItem('habits', JSON.stringify(habits));
    }

    // Modal handlers
    addHabitBtn.addEventListener('click', () => {
      habitName.value = '';
      habitDesc.value = '';
      habitHour.value = '';
      habitMinute.value = '';
      habitDuration.value = '';
      addHabitModal.classList.remove('hidden');
    });

    cancelAddHabit.addEventListener('click', () => {
      addHabitModal.classList.add('hidden');
    });

    saveHabit.addEventListener('click', () => {
      const title = habitName.value.trim();
      const desc = habitDesc.value.trim();
      const hour = habitHour.value;
      const minute = habitMinute.value || '00';
      const duration = habitDuration.value || '30';
      
      if (!title) {
        alert('Please enter a habit name');
        return;
      }
      
      habits.push({
        title,
        desc,
        time: { hour, minute, duration },
        activeDays: Array(7).fill(false)
      });
      
      saveToLocalStorage();
      renderHabits();
      addHabitModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    addHabitModal.addEventListener('click', (e) => {
      if (e.target === addHabitModal) {
        addHabitModal.classList.add('hidden');
      }
    });

    // Initial render
    renderHabits();
  </script>
</body>
</html>