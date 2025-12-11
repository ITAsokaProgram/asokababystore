function getToken() {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; admin_token=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}
function getTimeAgo(date) {
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const chatDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const dayDiffMs = today - chatDay;
  const diffDays = Math.floor(dayDiffMs / 86400000);

  if (diffDays === 0) {
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);

    if (diffMins < 1) return "Baru saja";
    if (diffMins < 60) return `${diffMins} menit lalu`;
    return `${diffHours} jam lalu`;
  }

  if (diffDays < 7) {
    return `${diffDays} hari lalu`;
  }

  return date.toLocaleDateString("id-ID", { day: "numeric", month: "short" });
}
function getBrightness(hex) {
  if (!hex) return 0;
  hex = hex.replace("#", "");
  if (hex.length === 3) {
    hex = hex
      .split("")
      .map((char) => char + char)
      .join("");
  }
  if (hex.length !== 6) return 0;
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);
  return (r * 299 + g * 587 + b * 114) / 1000;
}

function formatTimestamp(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  const hours = date.getHours().toString().padStart(2, "0");
  const minutes = date.getMinutes().toString().padStart(2, "0");
  return `${hours}:${minutes}`;
}

function formatDateSeparator(dateString) {
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);

  const options = {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  };

  if (date.toDateString() === today.toDateString()) {
    return "Hari ini";
  }
  if (date.toDateString() === yesterday.toDateString()) {
    return "Kemarin";
  }
  return date.toLocaleDateString("id-ID", options);
}

function debounce(func, delay) {
  let timeoutId;
  return function (...args) {
    const context = this;
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      func.apply(context, args);
    }, delay);
  };
}

function playNotificationSound() {
  const sound = document.getElementById("notification-sound");
  if (sound) {
    sound.currentTime = 0;
    sound.play().catch((error) => {
      console.warn(
        "Gagal memutar suara notifikasi, perlu interaksi pengguna.",
        error
      );
    });
  }
}

function showBrowserNotification(title, body) {
  if (!("Notification" in window)) {
    console.log("Browser ini tidak mendukung notifikasi desktop.");
    return;
  }

  if (Notification.permission === "granted") {
    new Notification(title, {
      body: body,
      icon: "/public/images/logo1.png",
    });
  } else if (Notification.permission !== "denied") {
    Notification.requestPermission().then((permission) => {
      if (permission === "granted") {
        new Notification(title, {
          body: body,
          icon: "/public/images/logo1.png",
        });
      }
    });
  }
}
