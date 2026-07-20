# Voice Chat Bot (Speech → LLM → Speech)

A browser-based voice assistant: you talk, the browser turns your speech into
text, a PHP backend sends that text to the Cohere LLM, and the browser reads
the reply out loud.

## How it works

1. **Speech → Text** — `app.js` uses the browser's built-in Web Speech API
   (`SpeechRecognition`) to turn your microphone input into text. No API key
   needed for this part; it runs entirely in the browser.
2. **Text → LLM reply** — the recognized text is sent via `fetch()` to
   `chat.php` on the server. `chat.php` calls the Cohere Chat API using a
   secret API key (kept server-side, never exposed to the browser) and
   returns the model's reply as JSON.
3. **Text → Speech** — `app.js` uses the browser's `SpeechSynthesisUtterance`
   API to speak the reply out loud.

## Files

| File | Purpose |
|---|---|
| `index.html` | Page structure: chat log + mic button |
| `style.css` | Chat UI styling |
| `app.js` | Speech recognition, calling the backend, speech synthesis |
| `chat.php` | Server-side proxy that calls the Cohere API securely |

## Setup — split hosting (GitHub Pages + a PHP host)

GitHub Pages only serves static files (HTML/CSS/JS) — it **cannot run
`chat.php`**. So the frontend and backend live on two different hosts:

| Part | Goes on |
|---|---|
| `index.html`, `style.css`, `app.js` | GitHub Pages (static, free) |
| `chat.php` | A free PHP host, e.g. [000webhost](https://www.000webhost.com) or [InfinityFree](https://infinityfree.net) |

**Steps:**

1. **Get a free Cohere API key**
   Sign up at [dashboard.cohere.com](https://dashboard.cohere.com/api-keys)
   and copy your trial API key.

2. **Deploy `chat.php` to a PHP host** (000webhost, InfinityFree, or your
   school's hosting). Set the API key there — either:
   - Replace `PASTE_YOUR_COHERE_API_KEY_HERE` directly in the file, **or**
   - Set it as an environment variable named `COHERE_API_KEY` (recommended
     if your host supports it).

   Note the full URL where `chat.php` now lives, e.g.
   `https://yoursite.000webhostapp.com/chat.php`.

3. **Update `app.js`** — find the line:
   ```js
   const CHAT_API_URL = "https://REPLACE-WITH-YOUR-PHP-HOST/chat.php";
   ```
   and replace it with the real URL from step 2.

4. **Push `index.html`, `style.css`, `app.js` to a GitHub repo**, then
   enable GitHub Pages for it (repo Settings → Pages → deploy from
   `main` branch). GitHub will give you a URL like
   `https://yourusername.github.io/your-repo/`.

5. **(Optional, more secure)** In `chat.php`, replace the CORS line
   ```php
   header("Access-Control-Allow-Origin: *");
   ```
   with your exact GitHub Pages URL, e.g.
   ```php
   header("Access-Control-Allow-Origin: https://yourusername.github.io");
   ```
   so only your page can call the backend.

6. **Open your GitHub Pages URL in Chrome or Edge** (best Web Speech API
   support), click the mic button, and talk.

## Notes

- The Web Speech API requires **HTTPS** (or `localhost`) to access the
  microphone on most browsers.
- `recognition.lang` and `utterance.lang` in `app.js` are set to `"en-US"` —
  change both to `"ar-SA"` for Arabic input/output, or any other locale.
- Never put the Cohere API key directly in `app.js` — that would expose it
  to anyone viewing the page source. Always route it through `chat.php`.
