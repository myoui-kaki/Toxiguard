<?php
require __DIR__ . '/vendor/autoload.php';

use Sentiment\Analyzer;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    
    if ($message === "") {
        die("No message provided.");
    }

    // Initialize sentiment analyzer
    $analyzer = new Analyzer();

    // Comprehensive toxic words list
    $toxicKeywords = [
        // Tagalog offensive words (Basic)
        'gago', 'tanga', 'bobo', 'putang', 'puta', 'ulol', 'inutil', 'tarantado', 
        'hayop', 'walang kwenta', 'peste', 'leche', 'tangina', 'buwisit',
        
        // Tagalog offensive words (Expanded)
        'tang ina', 'putangina', 'punyeta', 'yawa', 'pakshet', 'pakyu', 
        'hinayupak', 'hayup ka', 'gagong', 'tangek', 'shunga', 'kupal', 
        'lintik', 'putik', 'animal ka', 'salot', 'walanghiya', 'siraulo', 
        'luko', 'baliw', 'gunggong', 'hudas', 'bruha', 'unggoy', 'baboy', 
        'aso ka', 'demonyo', 'peste ka', 'gaga', 'tanga ka', 'bobo ka', 
        'hangal', 'tangang tao', 'tae', 'pakingshet', 'pokpok', 'amputa', 
        'kingina', 'plastik', 'suwail', 'traydor',
        
        // Tagalog insulting terms
        'basura', 'walang hiya', 'walang utak', 'walang kwentang tao', 
        'pangit', 'kadiri', 'nakakasuya', 'nakakainis', 'nakakagigil', 
        'sakit sa bangs', 'cancer', 'kasuklam suklam', 'nakakapoot',
        
        // Tagalog threats
        'mamatay ka', 'sana mamatay', 'mamatay ka na', 'lumayas ka', 
        'umalis ka', 'maghiwalay na tayo', 'ayaw na kita', 'iwan na kita', 
        'susuntukin kita', 'sasaktan kita', 'papatayin', 'bugbugin',
        
        // English offensive words
        'stupid', 'idiot', 'hate', 'kill', 'die', 'fool', 'dumb', 'loser', 
        'worthless', 'trash', 'garbage', 'pathetic', 'disgusting', 'moron', 
        'imbecile', 'retard', 'dumbass', 'asshole', 'bastard', 'bitch', 
        'damn', 'hell', 'crap', 'shit', 'fuck', 'screw you', 'shut up', 
        'ugly', 'fat', 'failure', 'useless', 'terrible', 'horrible', 
        'awful', 'nasty', 'gross', 'repulsive', 'revolting', 'sickening', 
        'vile', 'despicable',
        
        // English threats
        'i hate you', 'kill yourself', 'die now', 'drop dead', 'go to hell', 
        'get lost', 'shut the hell up', 'fuck off', 'piss off', 'you suck', 
        'nobody likes you', 'everyone hates you', 'scum', 'waste of space', 
        'parasite', 'plague',
        
        // Mixed variations
        'bobo amputa', 'tanga gago', 'fuck ka', 'shit ka', 'putang shit',
        'potangina', 'potang ina', 'tangina mo', 'putangina mo', 'gago ka',
    ];

    // Normalize message for detection
    $messageLower = mb_strtolower($message, 'UTF-8');
    $messageNoSpaces = preg_replace('/\s+/', '', $messageLower);
    
    $toxicWordCount = 0;
    $foundToxicWords = [];
    $toxicityScore = 0;

    // Check for toxic keywords
    foreach ($toxicKeywords as $keyword) {
        $keywordNoSpaces = preg_replace('/\s+/', '', $keyword);
        
        // Check both original and no-space versions
    $pattern = '/\b' . preg_quote($keyword, '/') . '\b/u';

    if (preg_match($pattern, $messageLower)) { 
        
        $toxicWordCount++;
        $foundToxicWords[] = $keyword;

            // Weight severe words more heavily
            if (in_array($keyword, ['putangina', 'potangina', 'tangina', 'fuck', 'shit', 
                'bitch', 'asshole', 'mamatay', 'kill', 'putangina mo', 'tangina mo'])) {
                $toxicityScore += 0.6;
            } elseif (strlen($keyword) > 10) {
                $toxicityScore += 0.4;
            } else {
                $toxicityScore += 0.35;
            }
        }
    }

    // Get sentiment analysis from library
    $result = $analyzer->getSentiment($message);

    // ==========================================
    // FIX: MANUAL OVERRIDE FOR TAGALOG/DETECTED WORDS
    // ==========================================
    if ($toxicWordCount > 0) {
        // If we found bad words, force the sentiment to be Negative
        // This fixes the issue where Tagalog curse words show as "Neutral"
        
        // Calculate artificial negative score based on toxicity
        $artificialNeg = 0.5 + ($toxicWordCount * 0.15); 
        if ($artificialNeg > 0.98) $artificialNeg = 0.98;

        // Force update the result array if the library missed it
        if ($result['neg'] < $artificialNeg) {
            $result['neg'] = $artificialNeg;
            $result['neu'] = 1 - $artificialNeg; // Reduce neutral
            $result['pos'] = 0; // Remove positive
            $result['compound'] = -1 * $artificialNeg; // Force negative compound
        }
    }
    // ==========================================
    // END FIX
    // ==========================================

    $compound = $result['compound'];

    // Combine sentiment with keyword detection
    $adjustedScore = $compound - $toxicityScore;

    // Check for aggressive formatting
    $exclamationCount = substr_count($message, '!');
    $capsRatio = 0;
    $letterCount = preg_match_all('/[a-zA-Z]/', $message);
    $hasRepetitiveChars = false;
    $spamFactor = 0; // Initialize spam factor

    if ($letterCount > 0) {
        $upperCount = preg_match_all('/[A-Z]/', $message);
        $capsRatio = $upperCount / $letterCount;
    }

    if ($exclamationCount > 2) {
        $adjustedScore -= 0.15;
    }
    
    if ($capsRatio > 0.6 && $letterCount > 10) {
        $adjustedScore -= 0.2;
    }

    // Feature 1.5: Check for excessive repeated characters (e.g., "Hiiiii", "Yessss!!!"). Use 3 or more of the same character.
    if (preg_match('/(.)\1{2,}/u', $message)) {
        $adjustedScore -= 0.1;
        $hasRepetitiveChars = true;
    }
    
    // Feature 1.5: Check for repetitive/spam-like phrases
    $spamPatterns = [
        'buy now', 'free gift', 'click here', 'subscribe now', 'win money',
        'follow me', 'share this', 'check out this', 'limited time'
    ];
    
    foreach ($spamPatterns as $pattern) {
        $patternCount = substr_count($messageLower, $pattern);
        if ($patternCount >= 2) {
            // Give a penalty for repeated commercial terms
            $spamFactor += 0.2; 
        }
    }
    
    // Apply spam penalty
    $adjustedScore -= $spamFactor;


    // Determine toxicity status with stricter thresholds
    if ($toxicWordCount >= 2 || $adjustedScore <= -0.5) {
        $status = "TOXIC";
        $class = "toxic";
        $description = "This message contains harmful or offensive language that may hurt others.";
        $emoji = "⚠️";
    } elseif ($toxicWordCount >= 1 || $adjustedScore <= -0.25) {
        $status = "HIGHLY QUESTIONABLE";
        $class = "questionable";
        $description = "This message has negative elements and may be offensive.";
        $emoji = "⚡";
    } elseif ($adjustedScore <= -0.1) {
        $status = "QUESTIONABLE";
        $class = "questionable";
        $description = "This message has a slightly negative tone. Please be mindful.";
        $emoji = "⚡";
    } else {
        $status = "SAFE";
        $class = "safe";
        $description = "This message is safe and respectful. Great job! 🎉";
        $emoji = "✅";
    }

    // Calculate confidence
    $confidence = min(100, abs($adjustedScore) * 100 + ($toxicWordCount * 10));
    if ($confidence < 20 && $status === "SAFE") {
        $confidence = 85;
    }

    // Check for theme mode from POST
    $themeMode = 'dark'; // Default
    if (isset($_POST['theme_mode']) && in_array($_POST['theme_mode'], ['light', 'dark'])) {
        $themeMode = $_POST['theme_mode'];
    }
}
?>

<!DOCTYPE html>
<html lang="en" <?php if (isset($themeMode) && $themeMode === 'light') echo 'data-theme="light"'; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analysis Result - Chat Toxicity Filter</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div style="position: absolute; top: 20px; right: 20px; z-index: 1000;">
    <button id="themeToggle" class="theme-toggle" title="Toggle Theme" style="background: rgba(0,0,0,0.2); color: white; border-radius: 50%; width: 40px; height: 40px; border: 1px solid rgba(255,255,255,0.2);">
        <span id="themeIcon">☀️</span>
    </button>
</div>

    <div class="background-animation">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
        <div class="circle circle-3"></div>
    </div>

    <div class="container">
        <div class="header-section">
            <div class="logo">🛡️</div>
            <h1>Analysis Complete</h1>
            <p class="subtitle">Here's the detailed analysis of your message</p>
        </div>

        <div class="message-preview">
            <h3>📝 Message Analyzed:</h3>
            <div class="message-box">
                <?php echo htmlspecialchars($message ?? ''); ?>
            </div>
        </div>

        <div class="result-card <?php echo $class ?? ''; ?>">
            <div class="status-badge">
                <span class="emoji"><?php echo $emoji ?? ''; ?></span>
                <span class="status-text"><?php echo $status ?? ''; ?></span>
            </div>
            
            <p class="description"><?php echo $description ?? ''; ?></p>
            
            <div class="confidence-bar">
                <div class="confidence-label">Confidence Level</div>
                <div class="progress-bar">
                    <div class="progress-fill <?php echo $class ?? ''; ?>" style="width: <?php echo $confidence ?? 0; ?>%">
                        <?php echo round($confidence ?? 0); ?>%
                    </div>
                </div>
            </div>
        </div>

        <div class="details-section">
            <h3>📊 Detailed Analysis:</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💯</div>
                    <div class="stat-label">Compound Score</div>
                    <div class="stat-value"><?php echo number_format($compound ?? 0, 3); ?></div>
                </div>
                
                <div class="stat-card positive">
                    <div class="stat-icon">😊</div>
                    <div class="stat-label">Positive</div>
                    <div class="stat-value"><?php echo number_format($result['pos'] ?? 0, 3); ?></div>
                </div>
                
                <div class="stat-card negative">
                    <div class="stat-icon">😠</div>
                    <div class="stat-label">Negative</div>
                    <div class="stat-value"><?php echo number_format($result['neg'] ?? 0, 3); ?></div>
                </div>
                
                <div class="stat-card neutral">
                    <div class="stat-icon">😐</div>
                    <div class="stat-label">Neutral</div>
                    <div class="stat-value"><?php echo number_format($result['neu'] ?? 0, 3); ?></div>
                </div>
            </div>

            <?php if (isset($toxicWordCount) && $toxicWordCount > 0): ?>
            <div class="warning-box">
                <strong>⚠️ Offensive Language Detected</strong>
                <p>Found <?php echo $toxicWordCount; ?> potentially offensive <?php echo $toxicWordCount == 1 ? 'word' : 'words'; ?> in the message.</p>
                <?php if ($toxicWordCount >= 2): ?>
                <p style="margin-top: 10px; color: #dc2626; font-weight: 600;">
                    🚨 Multiple offensive words detected. This message is highly inappropriate.
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($capsRatio) && $capsRatio > 0.6 && $letterCount > 10): ?>
            <div class="warning-box" style="margin-top: 15px;">
                <strong>🔊 Aggressive Formatting Detected</strong>
                <p>Excessive use of capital letters may indicate shouting or aggression.</p>
            </div>
            <?php endif; ?>

            <?php if (isset($hasRepetitiveChars) && $hasRepetitiveChars): ?>
            <div class="warning-box" style="margin-top: 15px;">
                <strong>〰️ Repetitive Characters Detected</strong>
                <p>Excessive repeated characters may indicate strong emotion or spam/trolling.</p>
            </div>
            <?php endif; ?>

            <?php if (isset($spamFactor) && $spamFactor > 0): ?>
            <div class="warning-box" style="margin-top: 15px;">
                <strong>🔗 Spam-like Content Detected</strong>
                <p>The message contains multiple instances of commercial or promotional phrases.</p>
            </div>
            <?php endif; ?>

        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn-primary">
                <span>🔄</span> Analyze Another Message
            </a>
        </div>

        <div class="footer-note">
            <p>💡 <strong>Pro Tip:</strong> This system detects 100+ offensive words in Tagalog and English, including variations and mixed-language content!</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach((stat, index) => {
                stat.style.opacity = '0';
                stat.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    stat.style.transition = 'all 0.5s ease';
                    stat.style.opacity = '1';
                    stat.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>