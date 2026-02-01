<?php
session_start();
require_once 'helpers.php';
requireLogin();

// Basic Validation
$file = $_GET['file'] ?? '';
$path = 'uploads/sops/' . basename($file); // Security: basename to prevent traversal

if (!file_exists($path)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>File Not Found</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="text-center">
            <div class="alert alert-danger">
                <h4><i class="fas fa-exclamation-circle me-2"></i>File Not Found</h4>
                <p class="mb-0">The requested file does not exist or has been removed.</p>
                <small class="text-muted d-block mt-2">File: <?php echo htmlspecialchars(basename($file)); ?></small>
            </div>
            <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
        </div>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    </body>
    </html>
    <?php
    exit;
}

$title = $_GET['title'] ?? 'SOP Document';
$user = $_SESSION['emp_code'] ?? 'N/A';
$userId = $_SESSION['user_id'] ?? 'UserID';
$date = date('d-M-Y H:i A'); // Server time

// Get the reason from session (passed from request_sop_reason.php)
$reason = $_SESSION['sop_request_reason'] ?? 'No reason provided';
// Clear the reason from session after use
unset($_SESSION['sop_request_reason']);

// Log Audit Trail with reason
$pdo = getDBConnection();
$auditDetails = "Printed/Viewed SOP: $title ($file) | Reason: $reason";
logAudit($pdo, $userId, 'Print SOP', $auditDetails, getClientIP());
// Pass current time to JS for client-side rendering if needed, but server time is safer for "controlled" aspect
$stampText = "VALID FOR 24HR ONLY | CONTROLLED COPY | Printed by: $user | Date: $date | KOPRAN LIMITED";

// Get extension
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?> - Controlled Copy</title>
    <!-- pdf-lib for client-side PDF modification -->
    <script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
    <style>
        body { margin: 0; padding: 0; background: #525659; font-family: 'Arial', sans-serif; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .iframe-container { width: 100%; height: 100%; border: none; }
        
        /* Loading Overlay */
        #loading {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            z-index: 1000;
        }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid #f3f3f3; border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Legacy Footer for non-PDFs */
        .control-footer { 
            background: #fff; padding: 15px 20px; 
            border-top: 4px solid #da291c;
            display: flex; justify-content: space-between; align-items: flex-start;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            font-size: 12px;
        }
        .footer-left {
            flex: 1;
        }
        .footer-center {
            flex: 1;
            text-align: center;
        }
        .footer-right {
            flex: 1;
            text-align: right;
        }
        .footer-item {
            margin: 3px 0;
            line-height: 1.4;
        }
        .footer-label {
            font-weight: 600;
            color: #333;
        }
        .footer-value {
            color: #555;
        }
        @media print {
            body { 
                overflow: visible;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .control-footer { 
                position: fixed; 
                bottom: 0; 
                left: 0; 
                right: 0; 
                border-top: 2px solid #000;
                background: #fff;
                padding: 10px 20px;
                font-size: 10px;
                z-index: 9999;
            }
            @page {
                size: A4 portrait;
                margin: 20mm 15mm 30mm 15mm;
            }
        }
    </style>
</head>
<body>

    <div id="loading">
        <div class="spinner"></div>
        <p style="margin-top: 15px; color: #555; font-weight: bold;">Generating Controlled Copy...</p>
    </div>

    <?php if ($ext === 'pdf'): ?>
        <!-- PDF Container -->
        <iframe id="pdfFrame" class="iframe-container" src=""></iframe>

        <script>
            const { PDFDocument, rgb, StandardFonts } = PDFLib;

            async function stampPdf() {
                try {
                    // 1. Fetch original PDF
                    const url = '<?php echo htmlspecialchars($path); ?>';
                    const existingPdfBytes = await fetch(url).then(res => res.arrayBuffer());

                    // 2. Load a PDFDocument
                    const pdfDoc = await PDFDocument.load(existingPdfBytes);
                    const pages = pdfDoc.getPages();
                    const font = await pdfDoc.embedFont(StandardFonts.Helvetica);
                    const text = '<?php echo $stampText; ?>';

                    // 3. Add Stamp to Every Page
                    pages.forEach(page => {
                        const { width, height } = page.getSize();
                        
                        // Calculate font size to fit text within page width with margins
                        let textSize = 10;
                        let textWidth = font.widthOfTextAtSize(text, textSize);
                        const maxWidth = width - 40; // 20px margin on each side
                        
                        // Reduce font size if text is too wide
                        while (textWidth > maxWidth && textSize > 5) {
                            textSize -= 0.5;
                            textWidth = font.widthOfTextAtSize(text, textSize);
                        }
                        
                        // Draw footer text centered at bottom
                        page.drawText(text, {
                            x: (width / 2) - (textWidth / 2),
                            y: 15,
                            size: textSize,
                            font: font,
                            color: rgb(0.8, 0, 0), // Dark Red
                        });

                        // Optional: Add a subtle thin line above text
                        page.drawLine({
                            start: { x: 20, y: 25 },
                            end: { x: width - 20, y: 25 },
                            thickness: 1,
                            color: rgb(0.8, 0, 0),
                            opacity: 0.5,
                        });
                    });

                    // 4. Save and Display
                    const pdfBytes = await pdfDoc.save();
                    const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });
                    const blobUrl = URL.createObjectURL(pdfBlob);
                    
                    const iframe = document.getElementById('pdfFrame');
                    iframe.src = blobUrl; // + '#toolbar=0&navpanes=0'; // Toolbar hidden 

                    // Remove loader
                    document.getElementById('loading').style.display = 'none';

                } catch (err) {
                    console.error(err);
                    alert("Error generating controlled copy: " + err.message);
                    document.getElementById('loading').innerText = "Error loading document.";
                }
            }

            stampPdf();
        </script>

    <?php else: ?>
        <!-- Fallback for Images/Other -->
        <script>document.getElementById('loading').style.display = 'none';</script>
        
        <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
            <div style="text-align: center; padding: 20px; background: white; flex: 1; overflow: auto;">
                <img src="<?php echo htmlspecialchars($path); ?>" style="max-width: 100%; max-height: 80vh;">
            </div>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; background: white; flex: 1;">
                <p>This file type (<?php echo $ext; ?>) cannot be previewed. <a href="<?php echo htmlspecialchars($path); ?>">Download File</a></p>
            </div>
        <?php endif; ?>

        <div class="control-footer">
            <div class="footer-left">
                <div class="footer-item"><span class="footer-label">Company:</span> <span class="footer-value">KOPRAN LIMITED</span></div>
                <div class="footer-item"><span class="footer-label">Document:</span> <span class="footer-value"><?php echo htmlspecialchars($title); ?></span></div>
            </div>
            <div class="footer-center">
                <div class="footer-item" style="color: #da291c; font-weight: bold; white-space: nowrap; font-size: 11px;">Valid for 24Hr ONLY CONFIDENTIAL</div>
            </div>
            <div class="footer-right">
                <div class="footer-item"><span class="footer-label">Printed by:</span> <span class="footer-value"><?php echo htmlspecialchars($user); ?></span></div>
                <div class="footer-item"><span class="footer-label">Date:</span> <span class="footer-value"><?php echo $date; ?></span></div>
                <div class="footer-item"><span class="footer-label">Reason:</span> <span class="footer-value"><?php echo substr(htmlspecialchars($reason), 0, 50) . (strlen($reason) > 50 ? '...' : ''); ?></span></div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Force portrait mode for printing
        window.addEventListener('beforeprint', function() {
            document.body.style.width = '210mm';
            document.body.style.height = '297mm';
        });
        
        // Set print settings
        if (window.matchMedia) {
            var mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(mql) {
                if (mql.matches) {
                    console.log('Portrait mode enforced');
                }
            });
        }
    </script>

</body>
</html>