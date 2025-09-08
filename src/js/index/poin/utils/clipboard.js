// Copy reward code to clipboard
export function copyCode() {
  const code = document.getElementById("rewardCode").textContent;
  copyToClipboard(code);

  // Change button text temporarily
  const copyBtn = document.getElementById("copyBtn");
  const originalText = copyBtn.textContent;
  copyBtn.textContent = "✅ Tersalin!";
  copyBtn.classList.add("bg-green-100", "text-green-600");
  copyBtn.classList.remove("bg-blue-100", "text-blue-600");

  setTimeout(() => {
    copyBtn.textContent = originalText;
    copyBtn.classList.remove("bg-green-100", "text-green-600");
    copyBtn.classList.add("bg-blue-100", "text-blue-600");
  }, 2000);
}

// Copy to clipboard function
function copyToClipboard(text) {
  if (navigator.clipboard) {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        // Success
      })
      .catch(() => {
        // Fallback
        fallbackCopyTextToClipboard(text);
      });
  } else {
    fallbackCopyTextToClipboard(text);
  }
}

// Fallback copy function for older browsers
function fallbackCopyTextToClipboard(text) {
  const textArea = document.createElement("textarea");
  textArea.value = text;
  textArea.style.top = "0";
  textArea.style.left = "0";
  textArea.style.position = "fixed";
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();
  try {
    document.execCommand("copy");
  } catch (err) {
    console.error("Fallback: Could not copy text");
  }
  document.body.removeChild(textArea);
}
