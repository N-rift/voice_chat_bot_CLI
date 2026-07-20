"""
Voice Chat Bot (CLI version)
Runs entirely in the terminal: mic -> speech-to-text -> Cohere LLM -> text-to-speech.

Setup:
    pip install -r requirements.txt

Run:
    python voice_bot.py

Press Ctrl+C to quit.
"""

import speech_recognition as sr
import pyttsx3
import cohere

# ------------------------------------------------------------------
# 1. Put your Cohere API key here.
#    Get a free trial key at: https://dashboard.cohere.com/api-keys
# ------------------------------------------------------------------
COHERE_API_KEY = "PASTE_YOUR_COHERE_API_KEY_HERE"

co = cohere.Client(COHERE_API_KEY)

# Text-to-speech engine (offline, works fully in the terminal)
tts_engine = pyttsx3.init()
tts_engine.setProperty("rate", 175)  # speaking speed

# Speech recognizer (uses the microphone)
recognizer = sr.Recognizer()
mic = sr.Microphone()


def listen() -> str:
    """Step 1: Capture audio from the mic and convert it to text."""
    with mic as source:
        print("\n🎤 Listening... (speak now)")
        recognizer.adjust_for_ambient_noise(source, duration=0.5)
        audio = recognizer.listen(source)

    print("📝 Transcribing...")
    try:
        text = recognizer.recognize_google(audio)
        print(f"You said: {text}")
        return text
    except sr.UnknownValueError:
        print("⚠️  Couldn't understand that, try again.")
        return ""
    except sr.RequestError as e:
        print(f"⚠️  Speech recognition service error: {e}")
        return ""


def ask_llm(prompt: str) -> str:
    """Step 2: Send the recognized text to Cohere and get a reply."""
    response = co.chat(model="command-r", message=prompt)
    return response.text


def speak(text: str):
    """Step 3: Read the reply out loud."""
    print(f"🤖 Bot: {text}")
    tts_engine.say(text)
    tts_engine.runAndWait()


def main():
    if COHERE_API_KEY == "PASTE_YOUR_COHERE_API_KEY_HERE":
        print("❌ Please set your Cohere API key in this file before running.")
        return

    print("=" * 50)
    print("  Voice Chat Bot — CLI version")
    print("  Speak into your mic. Press Ctrl+C to quit.")
    print("=" * 50)

    while True:
        try:
            user_text = listen()
            if not user_text:
                continue

            reply = ask_llm(user_text)
            speak(reply)

        except KeyboardInterrupt:
            print("\n👋 Goodbye!")
            break
        except Exception as e:
            print(f"⚠️  Error: {e}")


if __name__ == "__main__":
    main()
