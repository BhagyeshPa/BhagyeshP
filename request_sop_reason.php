<?php
session_start();
require_once 'helpers.php';
requireLogin();

// If user is QA or Admin, redirect directly to print page (bypass reason requirement)
if (in_array($_SESSION['role'] ?? '', ['qa', 'admin'])) {
    if (isset($_GET['file']) && isset($_GET['title'])) {
        header('Location: print_sop.php?file=' . urlencode($_GET['file']) . '&title=' . urlencode($_GET['title']));
        exit;
    }
}

// Check if coming from SOP selection
if (!isset($_GET['file']) || !isset($_GET['title'])) {
    header('Location: view_sops.php');
    exit;
}

$file = basename($_GET['file']);
$title = $_GET['title'];
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'User';

// Store in session for later use
$_SESSION['sop_file'] = $file;
$_SESSION['sop_title'] = $title;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        // Apply dark mode immediately before page renders to prevent flash
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Format Access Request - Controlled Copy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link href="css/layout_styles.css" rel="stylesheet">
    <style>
        .app-main {
            padding: 20px;
        }
        .request-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .request-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .request-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .request-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .request-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .request-body {
            padding: 30px;
        }
        .sop-info {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .sop-info-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sop-info-value {
            color: #555;
            font-size: 1.1rem;
            margin-top: 5px;
        }
        .reason-section {
            margin-bottom: 20px;
        }
        .reason-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .reason-label i {
            margin-right: 8px;
            color: #667eea;
        }
        .word-counter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .word-counter .word-count {
            font-weight: 600;
            color: #667eea;
        }
        .word-counter .min-requirement {
            color: #999;
        }
        .word-counter.valid .word-count {
            color: #10b981;
        }
        .word-counter.invalid .word-count {
            color: #ef4444;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 150px;
            border-color: #ddd;
            width: 100%;
        }
        textarea.form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .form-group {
            width: 100%;
        }
        .btn-section {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        .btn-submit {
            flex: 1;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:disabled {
            background: #ccc;
            color: #999;
            cursor: not-allowed;
        }
        .btn-cancel {
            flex: 1;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            background: #e5e7eb;
            color: #333;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background: #d1d5db;
        }
        .error-message {
            display: none;
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="request-container">
                <div class="request-card">
                    <div class="request-header">
                        <i class="fas fa-lock-open" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
                        <h2>Format Access Request</h2>
                        <p>Please provide your reason for accessing this document (Minimum 6 words required)</p>
                    </div>

                    <div class="request-body">
                        <div id="errorMessage" class="error-message"></div>

                        <!-- SOP Information -->
                        <div class="sop-info">
                            <div class="sop-info-label">
                                <i class="fas fa-file-pdf"></i> Document
                            </div>
                            <div class="sop-info-value"><?php echo htmlspecialchars($title); ?></div>
                        </div>

                        <!-- Reason Form -->
                        <form id="reasonForm" onsubmit="submitReason(event)">
                            <div class="reason-section">
                                <label class="reason-label">
                                    <i class="fas fa-pen-fancy"></i> Why do you need this Format? <span class="text-danger">*</span>
                                </label>
                                <div class="form-group">
                                    <textarea id="reasonText" class="form-control" placeholder="Please explain why you need to access this document. Minimum 6 words required..." required></textarea>
                                </div>

                                <div class="word-counter" id="wordCounter">
                                    <div class="min-requirement">Minimum: <span>6 words</span></div>
                                    <div class="word-count">Current: <span id="wordCount">0</span> words</div>
                                </div>
                            </div>

                            <div class="btn-section">
                                <button type="button" class="btn-cancel" onclick="window.location.href='view_sops.php'">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </button>
                                <button type="submit" class="btn-submit" id="submitBtn" style="background: #ccc; color: white;" disabled>
                                    <i class="fas fa-check me-2"></i> Proceed to Print
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <?php include 'right_panel.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        // MINIMUM 6 WORDS REQUIRED - VERSION: <?php echo time(); ?>
        const reasonText = document.getElementById('reasonText');
        const wordCounter = document.getElementById('wordCounter');
        const wordCount = document.getElementById('wordCount');
        const submitBtn = document.getElementById('submitBtn');
        const MIN_WORDS = 6; // *** MINIMUM 6 WORDS REQUIRED ***

        // Real-time word counter with validation
        reasonText.addEventListener('input', function() {
            const words = this.value.trim().split(/\s+/).filter(w => w.length > 0).length;
            wordCount.textContent = words;

            if (words >= MIN_WORDS) {
                wordCounter.classList.remove('invalid');
                wordCounter.classList.add('valid');
                submitBtn.disabled = false;
                submitBtn.style.background = '#667eea';
            } else {
                wordCounter.classList.remove('valid');
                wordCounter.classList.add('invalid');
                submitBtn.disabled = true;
                submitBtn.style.background = '#ccc';
            }
        });

        function submitReason(event) {
            event.preventDefault();

            const reason = reasonText.value.trim();
            const words = reason.split(/\s+/).filter(w => w.length > 0).length;

            // Check minimum words
            if (words < MIN_WORDS) {
                showError(`Please enter at least ${MIN_WORDS} words. Current: ${words} words`);
                return;
            }

            // Store in session and redirect to print
            const file = '<?php echo addslashes($file); ?>';
            const title = '<?php echo addslashes($title); ?>';

            // Store reason in session via AJAX
            fetch('store_sop_reason.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'reason=' + encodeURIComponent(reason) + '&file=' + encodeURIComponent(file) + '&title=' + encodeURIComponent(title)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to print page
                    window.location.href = 'print_sop.php?file=' + encodeURIComponent(file) + '&title=' + encodeURIComponent(title);
                } else {
                    showError(data.message || 'An error occurred while storing your request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
            });
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
