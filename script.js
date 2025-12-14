// Character Counter
const analyzerInput = document.getElementById('analyzerInput');
const charCount = document.getElementById('charCount');

if (analyzerInput && charCount) {
    analyzerInput.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;

        if (length > 450) {
            charCount.style.color = '#ef4444';
        } else if (length > 350) {
            charCount.style.color = '#f59e0b';
        } else {
            charCount.style.color = '#94a3b8';
        }
    });
}

// Test Message Function
function testMessage(message) {
    if (analyzerInput) {
        analyzerInput.value = message;
        charCount.textContent = message.length;
        
        // Submit the form
        analyzerInput.closest('form').submit();
    }
}

// Statistics Counter (for future use)
let totalCount = parseInt(localStorage.getItem('totalCount') || '0');
let safeCount = parseInt(localStorage.getItem('safeCount') || '0');
let warningCount = parseInt(localStorage.getItem('warningCount') || '0');
let dangerCount = parseInt(localStorage.getItem('dangerCount') || '0');

// Update display if elements exist
if(document.getElementById('totalCount')) {
    document.getElementById('totalCount').textContent = totalCount;
    document.getElementById('safeCount').textContent = safeCount;
    document.getElementById('warningCount').textContent = warningCount;
    document.getElementById('dangerCount').textContent = dangerCount;
}

// THEME TOGGLE LOGIC
const themeToggleBtn = document.getElementById('themeToggle');
const htmlElement = document.documentElement;
const themeIcon = document.getElementById('themeIcon');

// Check saved theme on load
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'light') {
    htmlElement.setAttribute('data-theme', 'light');
    if(themeIcon) themeIcon.textContent = '🌙';
}

if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-theme');
        
        if (currentTheme === 'light') {
            htmlElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'dark');
            if(themeIcon) themeIcon.textContent = '☀️';
        } else {
            htmlElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
            if(themeIcon) themeIcon.textContent = '🌙';
        }
    });
}

console.log('ToxiGuard initialized - Dark Mode & Analysis Ready');

// Add function to set theme for submission
const analyzerForm = document.querySelector('.analyzer form');
const themeModeInput = document.getElementById('themeModeInput');

if (analyzerForm && themeModeInput) {
    analyzerForm.addEventListener('submit', function() {
        const currentTheme = htmlElement.getAttribute('data-theme') || 'dark';
        themeModeInput.value = currentTheme;
    });
}


// VOICE INPUT LOGIC (Feature 2.5)

const voiceInputBtn = document.getElementById('voiceInputBtn');
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
let recognition = null;
let isListening = false;

if (voiceInputBtn && SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.continuous = false; // Only get one result per speech
    recognition.lang = 'en-US'; // Set default language, can be extended for other languages like 'fil-PH'

    recognition.onstart = function() {
        console.log('Voice recognition started...');
        voiceInputBtn.textContent = '🔴 LISTENING...';
        voiceInputBtn.classList.add('is-listening');
        isListening = true;
    };

    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        analyzerInput.value = transcript;
        
        // Manually trigger input event to update character counter
        const eventInput = new Event('input', { bubbles: true });
        analyzerInput.dispatchEvent(eventInput);

        // Automatically submit for analysis after speech stops
        setTimeout(() => {
            analyzerInput.closest('form').submit();
        }, 500); // 0.5 sec delay before submitting
    };

    recognition.onerror = function(event) {
        console.error('Speech recognition error: ' + event.error);
        voiceInputBtn.textContent = '🎙️ Error: Try Again';
        voiceInputBtn.classList.remove('is-listening');
        isListening = false;
    };
    
    recognition.onend = function() {
        if(isListening) {
            voiceInputBtn.textContent = '🎙️ Speak Now';
            voiceInputBtn.classList.remove('is-listening');
            isListening = false;
        }
    };

    voiceInputBtn.addEventListener('click', () => {
        if (isListening) {
            recognition.stop();
        } else {
            try {
                recognition.start();
            } catch (e) {
                console.error('Recognition start error:', e);
                voiceInputBtn.textContent = '🎙️ Start Failed';
                voiceInputBtn.classList.remove('is-listening');
                isListening = false;
            }
        }
    });

} else if (voiceInputBtn) {
    // Browser does not support Speech Recognition
    voiceInputBtn.textContent = 'Voice Not Supported';
    voiceInputBtn.disabled = true;
    voiceInputBtn.title = 'Your browser does not support the Web Speech API.';
}