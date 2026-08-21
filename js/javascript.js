var timeout = 500;
var closetimer = 0;
var ddmenuitem = 0;

function mopen(id) {
  // cancel close timer
  mcancelclosetime();

  // close old layer
  if (ddmenuitem) ddmenuitem.style.visibility = "hidden";

  // get new layer and show it
  ddmenuitem = document.getElementById(id);
  ddmenuitem.style.visibility = "visible";
}

function mclose() {
  if (ddmenuitem) ddmenuitem.style.visibility = "hidden";
}

function mclosetime() {
  closetimer = window.setTimeout(mclose, timeout);
}

function mcancelclosetime() {
  if (closetimer) {
    window.clearTimeout(closetimer);
    closetimer = null;
  }
}

document.onclick = mclose;

function updateClock() {
  var currentTime = new Date();

  var currentHours = currentTime.getHours();
  var currentMinutes = currentTime.getMinutes();
  var currentSeconds = currentTime.getSeconds();

  // Pad the minutes and seconds with leading zeros, if required
  currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes;
  currentSeconds = (currentSeconds < 10 ? "0" : "") + currentSeconds;

  // Choose either "AM" or "PM" as appropriate
  var timeOfDay = currentHours < 12 ? "AM" : "PM";

  // Convert the hours component to 12-hour format if needed
  currentHours = currentHours > 12 ? currentHours - 12 : currentHours;

  // Convert an hours component of "0" to "12"
  currentHours = currentHours == 0 ? 12 : currentHours;

  // Compose the string for display
  var currentTimeString =
    currentHours +
    ":" +
    currentMinutes +
    ":" +
    currentSeconds +
    " " +
    timeOfDay;

  // Update the time display
  document.getElementById("clock").firstChild.nodeValue = currentTimeString;
}
