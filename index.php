<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToxiGuard - AI Chat Toxicity Detection</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="logo">🛡️ ToxiGuard</div>
        <div class="header-right">
            <button id="themeToggle" class="theme-toggle" title="Toggle Theme">
                <span id="themeIcon">☀️</span>
            </button>
            <div class="header-status">100+ Toxic Patterns Detected</div>
        </div>
    </header>

    <section class="hero">
        <h1>Professional Chat <span class="highlight">Toxicity Detection</span></h1>
        <p class="subtext">Real-time monitoring and analysis for Filipino and English messages. Detect toxic content with advanced AI-powered sentiment analysis.</p>

        <div class="stats">
            <div class="stat-card green">
                <h3>Total Analyzed</h3>
                <p id="totalCount">0</p>
            </div>
            <div class="stat-card blue">
                <h3>Safe Messages</h3>
                <p id="safeCount">0</p>
            </div>
            <div class="stat-card yellow">
                <h3>Questionable</h3>
                <p id="warningCount">0</p>
            </div>
            <div class="stat-card red">
                <h3>Toxic Detected</h3>
                <p id="dangerCount">0</p>
            </div>
        </div>

        <div class="examples">
            <div class="example green" onclick="testMessage('Hello! How are you today?')">Friendly Greeting</div>
            <div class="example blue" onclick="testMessage('Thank you so much!')">Thank You</div>
            <div class="example yellow" onclick="testMessage('This is stupid')">Mild Negative</div>
            <div class="example red" onclick="testMessage('Putangina mo gago')">Strong Insult</div>
        </div>
    </section>

    <section class="analyzer">
    <h2>Message Analyzer</h2>
    <form action="analyze.php" method="POST">
        <textarea name="message" id="analyzerInput" class="input-box" placeholder="Type or speak your message here..." required maxlength="500"></textarea>
        <input type="hidden" name="theme_mode" id="themeModeInput" value="dark">
        <div class="char-counter">
            <span id="charCount">0</span>/500 characters
        </div>
        <div class="analyzer-actions">
            <button type="button" id="voiceInputBtn" class="voice-btn" title="Voice Input">
                🎙️ Speak Now
            </button>
            <button type="submit" class="analyze-btn">Analyze Message 🚀</button>
        </div>
    </form>
</section>

    <section class="features-section">
        <h2 class="section-title">Powerful Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon blue"></div>
                <h3>Multi-Language Support</h3>
                <p>Detects toxic content in both Tagalog and English with cultural context awareness.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon orange"></div>
                <h3>Real-Time Analysis</h3>
                <p>Instant toxicity detection with live monitoring and immediate feedback.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon pink"></div>
                <h3>Advanced AI</h3>
                <p>Sentiment analysis combined with pattern matching for accurate decisions.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon teal"></div>
                <h3>Detailed Analytics</h3>
                <p>Comprehensive reports with confidence scores and toxicity metrics.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon purple"></div>
                <h3>Context Detection</h3>
                <p>Analyzes tone, punctuation, and message context for better accuracy.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon red"></div>
                <h3>High Accuracy</h3>
                <p>Trained on extensive toxic patterns with weighted scoring for precision.</p>
            </div>
        </div>
    </section>

    <footer class="footer">
        © 2025 ToxiGuard. Professional content moderation powered by AI.
    </footer>

    <script src="script.js"></script>
</body>
</html>