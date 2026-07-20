# Voice Chat Bot — CLI version

A voice assistant that runs entirely in your terminal: no browser, no
hosting, no CORS issues. Talk into your mic, it transcribes your speech,
sends it to Cohere, and speaks the reply back — all from `cmd`/terminal.

## How it works

1. **Speech → Text** — `speech_recognition` captures audio from your
   microphone and sends it to Google's free speech recognition API to get
   text back.
2. **Text → LLM reply** — the text is sent to Cohere's Chat API using the
   `cohere` Python package, which returns a reply.
3. **Text → Speech** — `pyttsx3` reads the reply out loud using your
   system's built-in text-to-speech voices (fully offline, no internet
   needed for this step).

## Setup

### 1. Install Python
Make sure you have Python 3.8+ installed. Check with:
```
python --version
```

### 2. Get a free Cohere API key
Sign up at [dashboard.cohere.com](https://dashboard.cohere.com/api-keys)
and copy your trial API key.

### 3. Set your API key
Open `voice_bot.py` and replace:
```python
COHERE_API_KEY = "PASTE_YOUR_COHERE_API_KEY_HERE"
```
with your real key.

### 4. Install dependencies
Open `cmd` (or terminal), navigate to this folder, and run:
```
pip install -r requirements.txt
```

**If `PyAudio` fails to install on Windows** (common issue — it needs a
compiled binary), run this instead:
```
pip install pipwin
pipwin install pyaudio
```

### 5. Run it
```
python voice_bot.py
```

Speak when you see `🎤 Listening...`. Press `Ctrl+C` to quit.

## Troubleshooting

- **"No module named 'pyaudio'"** → use the `pipwin` method above.
- **Nothing happens when you speak / "Couldn't understand that"** →
  check your microphone is set as the default input device in your OS
  sound settings.
- **No sound plays for the reply** → some systems need a specific voice
  installed for `pyttsx3` to work; check Windows Settings → Time & Language
  → Speech to confirm a voice is installed.
- **Cohere errors** → double check your API key was pasted correctly and
  has no extra spaces.
