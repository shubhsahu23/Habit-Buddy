const calender = document.querySelector(".calender"),
date = document.querySelector(".date"),
daysContainer = document.querySelector(".days"),
prev = document.querySelector(".prev"),
next = document.querySelector(".next"),
todayBtn = document.querySelector(".today-btn"),
gotoBtn = document.querySelector(".goto-btn"),
dateInput = document.querySelector(".date-input"),
eventContainer = document.querySelector(".events1");

let today = new Date();
let activeDay;
let month = today.getMonth();
let year = today.getFullYear();

const monthNames = [
"January", "February", "March", "April", "May", "June",
"July", "August", "September", "October", "November", "December"
];

// Events array - will be populated from server
let eventsArr = [];

// Initialize calendar and load events when page loads
document.addEventListener('DOMContentLoaded', async () => {
initCalender();
await getEvents();
});

// Function to fetch events from PHP backend
async function getEvents() {
try {
  const response = await fetch('api/get_events.php');
  if (!response.ok) throw new Error('Failed to fetch events');
  eventsArr = await response.json();
  updateEvents(activeDay);
} catch (error) {
  console.error('Error loading events:', error);
  eventContainer.innerHTML = `<h3 class="no-event">Error loading habits. Please refresh.</h3>`;
}
}

// Function to save events to PHP backend
async function saveEvents() {
try {
  const response = await fetch('api/save_events.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(eventsArr)
  });
  
  if (!response.ok) throw new Error('Failed to save events');
  const result = await response.json();
  if (!result.success) throw new Error(result.message);
} catch (error) {
  console.error('Error saving events:', error);
  alert('Failed to save habits. Please try again.');
}
}

// function to add days
async function initCalender() {
    // First load events from server
    await getEvents();
    
    // Then render the calendar with the events
    renderCalendar();
  }
  
  function renderCalendar() {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const prevMonthLastDay = new Date(year, month, 0);
    const prevDate = prevMonthLastDay.getDate();
    const lastDate = lastDay.getDate();
    const day = firstDay.getDay();
    const nextDays = 7 - lastDay.getDay() - 1;
  
    date.innerHTML = monthNames[month] + " " + year;
  
    let days = "";
  
    for (let x = day; x > 0; x--) {
      days += `<div class="day prev-date">${prevDate - x + 1}</div>`;
    }
  
    for (let i = 1; i <= lastDate; i++) {
      let event = false;
      eventsArr.forEach((eventObj) => {
        if (
          eventObj.day == i &&
          eventObj.month === month + 1 &&
          eventObj.year == year
        ) {
          event = true;
        }
      });
  
      if (
        i === new Date().getDate() &&
        year === new Date().getFullYear() &&
        month === new Date().getMonth()
      ) {
        activeDay = i;
        updateEvents(i);
        if (event) {
          days += `<div class="day today event">${i}</div>`;
        } else {
          days += `<div class="day today">${i}</div>`;
        }
      } else {
        if (event) {
          days += `<div class="day event">${i}</div>`;
        } else {
          days += `<div class="day">${i}</div>`;
        }
      }
    }
  
    for (let j = 1; j <= nextDays; j++) {
      days += `<div class="day next-date">${j}</div>`;
    }
  
    daysContainer.innerHTML = days;
    activeDayStyle();
    
    // After rendering, make sure the active day is highlighted
    if (activeDay) {
      const activeDayElement = [...document.querySelectorAll('.day')].find(el => 
        parseInt(el.textContent) === activeDay && 
        !el.classList.contains('prev-date') && 
        !el.classList.contains('next-date')
      );
      if (activeDayElement) {
        activeDayElement.classList.add('active1');
      }
    }
  }

function activeDayStyle() {
const boxes = document.querySelectorAll(".day");

boxes.forEach((box) => {
  box.addEventListener("click", function (e) {
    const activeElement = document.querySelector(".active1");
    if (activeElement) {
      activeElement.classList.remove("active1");
    }
    box.classList.add("active1");
    getActiveDay(e.target.innerHTML);
  });
});
}

// prev Month
function prevMonth() {
month--;
if (month < 0) {
  month = 11;
  year--;
}
initCalender();
}

// next month;
function NextMonth() {
month++;
if (month > 11) {
  month = 0;
  year++;
}
initCalender();
}

// add eventlistnner on prev and next;
prev.addEventListener("click", prevMonth);
next.addEventListener("click", NextMonth);

// lets add goto date and goto today fucntionality
todayBtn.addEventListener("click", () => {
today = new Date();
month = today.getMonth();
year = today.getFullYear();
initCalender();
});

dateInput.addEventListener("input", (event) => {
dateInput.value = dateInput.value.replace(/[^0-9/]/g, "");

if (dateInput.value.length === 2 && event.inputType !== "deleteContentBackward") {
  dateInput.value += "/";
}

if (dateInput.value.length > 7) {
  dateInput.value = dateInput.value.slice(0, 7);
}

if (event.inputType === "deleteContentBackward" && dateInput.value.length === 3) {
  dateInput.value = dateInput.value.slice(0, 2);
}
});

gotoBtn.addEventListener("click", gotoDate);

function gotoDate() {
const dateArr = dateInput.value.split("/");

if (dateArr.length === 2) {
  if (dateArr[0] > 0 && dateArr[0] < 13 && dateArr[1].length === 4) {
    month = dateArr[0] - 1;
    year = dateArr[1];
    initCalender();
    return;
  }
}
alert("Invalid Date");
}

const addEventContainer = document.querySelector(".add-event-box"),
addEventCloseBtn = document.querySelector(".close"),
addEventIcon = document.querySelector(".add-event-icon"),
addEventTitle = document.querySelector(".event-name"),
addEventForm = document.querySelector(".event-time-form"),
addEventTo = document.querySelector(".event-time-to"),
addEventSubmit = document.querySelector(".add-event-btn");

addEventIcon.addEventListener("click", () => {
addEventContainer.classList.toggle("active");
});

addEventCloseBtn.addEventListener("click", () => {
addEventContainer.classList.remove("active");
});

document.addEventListener("click", (e) => {
if (e.target != addEventIcon && !addEventContainer.contains(e.target)) {
  addEventContainer.classList.remove("active");
}
});

addEventTitle.addEventListener("input", (e) => {
addEventTitle.value = addEventTitle.value.slice(0, 50);
});

addEventForm.addEventListener("input", (e) => {
addEventForm.value = addEventForm.value.replace(/[^0-9:]/g, "");
if (addEventForm.value.length === 2 && e.inputType !== "deleteContentBackward") {
  addEventForm.value += ":";
}
if (addEventForm.value.length > 5) {
  addEventForm.value = addEventForm.value.slice(0, 5);
}
});

addEventTo.addEventListener("input", (e) => {
addEventTo.value = addEventTo.value.replace(/[^0-9:]/g, "");
if (addEventTo.value.length === 2 && e.inputType !== "deleteContentBackward") {
  addEventTo.value += ":";
}
if (addEventTo.value.length > 5) {
  addEventTo.value = addEventTo.value.slice(0, 5);
}
});

const eventDay = document.querySelector(".event-day"),
eventDate = document.querySelector(".event-date");

function getActiveDay(date) {
const day = new Date(year, month, date);
const dayName = day.toString().split(" ")[0];
eventDay.innerHTML = dayName;
eventDate.innerHTML = date + " " + monthNames[month] + " " + year;
updateEvents(date);
activeDay = date;
}

function updateEvents(date) {
let Events = "";
eventsArr.forEach((event) => {
  if (date == event.day && month + 1 == event.month && year === event.year) {
    event.events.forEach((ev) => {
      Events += `
        <div class="events">
          <div class="title">
            <i class="fas fa-circle"></i>
            <h3 class="event-title">${ev.title}</h3>
          </div>
          <div class="event-time">
            <span class="event-time">${ev.time}</span>
          </div>
        </div>`;
    });
  }
});

eventContainer.innerHTML = Events || `<h3 class="no-event">No Habits</h3>`;
}

addEventSubmit.addEventListener("click", async () => {
const eventTitle = addEventTitle.value.trim();
const eventForm = addEventForm.value;
const eventTo = addEventTo.value;

if (eventTitle === "" || eventForm === "" || eventTo === "") {
  alert("Please fill all the fields");
  return;
}

const timeFromeArr = eventForm.split(":").map(Number);
const timeToArr = eventTo.split(":").map(Number);
if (
  timeFromeArr.length !== 2 ||
  timeToArr.length !== 2 ||
  timeFromeArr[0] > 23 ||
  timeToArr[1] > 59 ||
  timeToArr[0] > 23 ||
  timeFromeArr[1] > 59
) {
  alert("Invalid Time Format");
  return;
}

const timeForm = convertTime(eventForm);
const timeTo = convertTime(eventTo);

let eventExit = false;
eventsArr.forEach((event) => {
  if (event.day == activeDay && event.month === month + 1 && event.year == year) {
    event.events.forEach((e) => {
      if (e.title.toLocaleLowerCase() === eventTitle.toLocaleLowerCase()) {
        eventExit = true;
      }
    });
  }
});

if (eventExit) {
  alert("Habit already added");
  return;
}

const newEvent = {
  title: eventTitle,
  time: timeForm + " - " + timeTo,
};

let eventAdded = false;
eventsArr.forEach((item) => {
  if (item.day == activeDay && item.month === month + 1 && item.year === year) {
    item.events.push(newEvent);
    eventAdded = true;
  }
});

if (!eventAdded) {
  eventsArr.push({
    day: activeDay,
    month: month + 1,
    year: year,
    events: [newEvent],
  });
}

const activeDayElem = document.querySelector(".day.active1");
if (!activeDayElem.classList.contains("event")) {
  activeDayElem.classList.add("event");
}

addEventContainer.classList.remove("active");
addEventTitle.value = "";
addEventForm.value = "";
addEventTo.value = "";

await saveEvents();
updateEvents(activeDay);
});

function convertTime(time) {
let timeArr = time.split(":");
let timeHour = timeArr[0];
let timeMin = timeArr[1];
let timeFormat = timeHour >= 12 ? "PM" : "AM";
timeHour = timeHour % 12 || 12;
time = timeHour + ":" + timeMin + " " + timeFormat;
return time;
}

eventContainer.addEventListener("click", async (e) => {
if (e.target.classList.contains("events")) {
  if (confirm("Are you sure you want to delete this habit?")) {
    const eventTitle = e.target.querySelector("h3").innerText;

    eventsArr.forEach((ev) => {
      if (ev.day == activeDay && ev.month === month + 1 && ev.year === year) {
        ev.events.forEach((item, index) => {
          if (item.title === eventTitle) {
            ev.events.splice(index, 1);
          }
        });
      }

      if (ev.events.length === 0) {
        eventsArr.splice(eventsArr.indexOf(ev), 1);
        const activeDayElem = document.querySelector(".day.active1");
        if (activeDayElem.classList.contains("event")) {
          activeDayElem.classList.remove("event");
        }
      }
    });

    await saveEvents();
    updateEvents(activeDay);
  }
}
});