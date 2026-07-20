const micBtn = document.getElementById("micBtn");
const chatLog = document.getElementById("chatLog");
const status = document.getElementById("status");

// --- Step 1: Speech recognition setup (browser mic -> text) ---
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

if (!SpeechRecognition) {
  status.textContent = "Speech recognition is not supported in this browser. Try Chrome or Edge.";
  micBtn.disabled = true;
} else {
  const recognition = new SpeechRecognition();
  recognition.lang = "en-US";      // change to "ar-SA" for Arabic, etc.
  recognition.interimResults = false;
  recognition.maxAlternatives = 1;

  let listening = false;

  micBtn.addEventListener("click", () => {
    if (listening) return;
    recognition.start();
  });

  recognition.onstart = () => {
    listening = true;
    micBtn.classList.add("listening");
    micBtn.textContent = "🎙️ Listening...";
    status.textContent = "Speak now...";
  };

  recognition.onend = () => {
    listening = false;
    micBtn.classList.remove("listening");
    micBtn.textContent = "🎤 Hold to Talk";
  };

  recognition.onerror = (event) => {
    status.textContent = "Mic error: " + event.error;
  };

  // --- Step 2: When speech is recognized, send text to backend ---
  recognition.onresult = async (event) => {
    const userText = event.results[0][0].transcript;
    addMessage("user", userText);
    status.textContent = "Thinking...";

    try {
      const reply = await askLLM(userText);
      addMessage("bot", reply);
      speak(reply);
      status.textContent = "Click the mic and speak...";
    } catch (err) {
      status.textContent = "Error: " + err.message;
    }
  };
}

// --- Step 2b: Call our own PHP backend (never call the LLM API key directly from JS) ---
// IMPORTANT: chat.php lives on a separate PHP host, NOT on GitHub Pages
// (GitHub Pages can only serve static files). Replace this with the
// full URL where you uploaded chat.php, e.g.:
// "https://yoursite.000webhostapp.com/chat.php"
const CHAT_API_URL = "https://REPLACE-WITH-YOUR-PHP-HOST/chat.php";

async function askLLM(prompt) {
  const res = await fetch(CHAT_API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ prompt })
  });

  if (!res.ok) {
    throw new Error("Server error (" + res.status + ")");
  }

  const data = await res.json();
  if (data.error) throw new Error(data.error);
  return data.reply;
}

// --- Step 3: Text to speech (browser speaks the reply) ---
function speak(text) {
  const utterance = new SpeechSynthesisUtterance(text);
  utterance.lang = "en-US"; // match recognition.lang above
  speechSynthesis.speak(utterance);
}

// --- Helper: add a message bubble to the chat log ---
function addMessage(role, text) {
  const div = document.createElement("div");
  div.className = "msg " + role;
  div.textContent = text;
  chatLog.appendChild(div);
  chatLog.scrollTop = chatLog.scrollHeight;
}
