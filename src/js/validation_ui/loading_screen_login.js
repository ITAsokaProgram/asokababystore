export const showLoadingScreen = () => {
  const load = document.getElementById('loading-screen');
  load.classList.remove("hidden");
}

export const closeLoadingScreen = () => {
  const load = document.getElementById('loading-screen');
  load.classList.add("hidden");
}

export default {showLoadingScreen,closeLoadingScreen}