<?php
session_start();
require_once 'helpers.php';
requireLogin();
checkRole(['user', 'qa', 'admin']); // All roles can view SOPs

$pdo = getDBConnection();

// Fetch all files with department info
$files = [];
$query = "SELECT f.id, f.title, f.sop_number, f.image, f.status, d.code as department_code 
          FROM fileup f 
          LEFT JOIN departments d ON f.department_id = d.id 
          ORDER BY f.sop_number ASC";
$stmt = $pdo->query($query);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Kopran | SOP View</title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css">
    <style>
        .page-header h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--text-primary);
        }

        .sop-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .sop-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .sop-card .card-body {
            padding: 20px;
        }

        .sop-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .sop-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 15px;
        }

        .badge-dept {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
        }

        .btn-view-options {
            width: 100%;
            border-radius: 8px;
            font-weight: 500;
        }

        /* Modal Styles */
        .format-list-group .list-group-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: none;
            border-right: none;
            padding: 12px 20px;
        }

        .format-list-group .list-group-item:first-child {
            border-top: none;
        }

        .format-list-group .list-group-item:last-child {
            border-bottom: none;
        }

        .format-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 6px;
            margin-right: 12px;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Header -->
        <?php include 'header.php'; ?>
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="app-main">
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">
                <h2><i class="fas fa-book-reader text-primary me-2"></i> Standard Operating Procedures - Format</h2>
            </div>

            <!-- Search Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-search me-2"></i>
                    <span>Search SOP by Number</span>
                </div>
                <div class="card-body p-4">
                    <!-- Step 1: Enter SOP Number -->
                    <div class="mb-4">
                        <label class="form-label fw-600 text-primary mb-3" style="font-size: 1.1rem;">
                            <i class="fas fa-circle-notch" style="color: #3b82f6;"></i> Step 1: Enter SOP Number
                        </label>
                        <input type="text" class="form-control form-control-lg" id="sopNumberInput"
                            placeholder="e.g., SOP-001" autocomplete="off">
                        <small class="text-muted d-block mt-2">Type the SOP number to search</small>
                        <div id="sopNotFound" class="alert alert-warning alert-sm mt-2 d-none" role="alert">
                            <small><i class="fas fa-exclamation-triangle me-1"></i> No SOP found with this number</small>
                        </div>
                    </div>

                    <!-- Step 2: SOP Name (Auto-filled) -->
                    <div class="mb-4 d-none" id="step2Container">
                        <label class="form-label fw-600 text-success mb-3" style="font-size: 1.1rem;">
                            <i class="fas fa-circle-notch" style="color: #10b981;"></i> Step 2: SOP Name <span class="text-muted">(Auto-filled)</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="sopNameDisplay"
                            placeholder="Name will appear here..." readonly style="background-color: #f0fdf4; border-color: #10b981;">
                    </div>

                    <!-- Step 3: SOP Version (Auto-filled) -->
                    <div class="mb-4 d-none" id="step3Container">
                        <label class="form-label fw-600 text-info mb-3" style="font-size: 1.1rem;">
                            <i class="fas fa-circle-notch" style="color: #0891b2;"></i> Step 3: SOP Version <span class="text-muted">(Auto-filled)</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="sopVersionDisplay"
                            placeholder="Version will appear here..." readonly style="background-color: #ecf0ff; border-color: #0891b2;">
                    </div>

                    <!-- Step 4: Select Formats -->
                    <div id="step4Container" class="d-none">
                        <label class="form-label fw-600 text-warning mb-3" style="font-size: 1.1rem;">
                            <i class="fas fa-circle-notch" style="color: #f59e0b;"></i> Step 4: Available Formats
                        </label>
                        <div id="formatsLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2">Loading formats...</p>
                        </div>
                        <div id="formatsContainer" class="list-group format-list-group d-none">
                            <!-- Formats loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regular Grid Search -->
            <?php if (in_array($_SESSION['role'] ?? '', ['qa', 'admin'])): ?>
            <div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="sopSearch"
                                placeholder="Or search SOPs by title or number...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4" id="sopGrid">
                <?php foreach ($files as $file): ?>
                    <div class="col-md-6 col-lg-4 sop-item">
                        <div class="sop-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span
                                        class="badge-dept"><?php echo htmlspecialchars($file['department_code'] ?? 'GEN'); ?></span>
                                    <small
                                        class="text-muted fw-bold"><?php echo htmlspecialchars($file['sop_number']); ?></small>
                                </div>
                                <h5 class="sop-title text-truncate" title="<?php echo htmlspecialchars($file['title']); ?>">
                                    <?php echo htmlspecialchars($file['title']); ?>
                                </h5>
                                <div class="sop-meta">
                                    <i class="far fa-file-pdf me-1"></i> Main Document
                                </div>
                                <button class="btn btn-outline-primary btn-view-options"
                                    onclick="openFormatModal(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars(addslashes($file['title'])); ?>', '<?php echo $file['sop_number']; ?>')">
                                    <i class="fas fa-eye me-2"></i> View / Print
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>

        <!-- Right Panel -->
        <?php include 'right_panel.php'; ?>
    </div>

    <!-- Format Selection Modal -->
    <div class="modal fade" id="formatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">SOP Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3">Select the format you want to view or print:</p>
                    <div id="modalLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="formatList" class="list-group format-list-group d-none">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedSopId = null;

        // NEW MULTI-STEP SEARCH FUNCTIONALITY
        const sopNumberInput = document.getElementById('sopNumberInput');
        const sopNameDisplay = document.getElementById('sopNameDisplay');
        const sopVersionDisplay = document.getElementById('sopVersionDisplay');
        const step2Container = document.getElementById('step2Container');
        const step3Container = document.getElementById('step3Container');
        const step4Container = document.getElementById('step4Container');
        const sopNotFound = document.getElementById('sopNotFound');

        // Clear input on page load
        sopNumberInput.value = '';

        // Real-time SOP number lookup
        sopNumberInput.addEventListener('input', function () {
            const sopNumber = this.value.trim();
            if (!sopNumber) {
                sopNameDisplay.value = '';
                sopVersionDisplay.value = '';
                step2Container.classList.add('d-none');
                step3Container.classList.add('d-none');
                step4Container.classList.add('d-none');
                sopNotFound.classList.add('d-none');
                return;
            }

            // Fetch SOP details by number
            fetch(`get_sop_by_number.php?number=${encodeURIComponent(sopNumber)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sop) {
                        // Show step 2
                        sopNameDisplay.value = data.sop.title;
                        step2Container.classList.remove('d-none');
                        sopNotFound.classList.add('d-none');

                        // Show step 3
                        sopVersionDisplay.value = data.sop.version || '1.0';
                        step3Container.classList.remove('d-none');

                        // Show step 4 and load formats
                        selectedSopId = data.sop.id;
                        step4Container.classList.remove('d-none');
                        loadFormats(selectedSopId);
                    } else {
                        sopNameDisplay.value = '';
                        sopVersionDisplay.value = '';
                        selectedSopId = null;
                        step2Container.classList.add('d-none');
                        step3Container.classList.add('d-none');
                        step4Container.classList.add('d-none');
                        sopNotFound.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    console.error('Error fetching SOP:', err);
                    sopNameDisplay.value = '';
                    sopVersionDisplay.value = '';
                    step2Container.classList.add('d-none');
                    step3Container.classList.add('d-none');
                    step4Container.classList.add('d-none');
                    sopNotFound.classList.remove('d-none');
                });
        });

        function loadFormats(sopId) {
            const loading = document.getElementById('formatsLoading');
            const container = document.getElementById('formatsContainer');

            loading.classList.remove('d-none');
            container.classList.add('d-none');
            container.innerHTML = '';

            fetch(`get_sop_formats.php?sop_id=${sopId}`)
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('d-none');
                    container.classList.remove('d-none');

                    // Add Main SOP only if it exists
                    if (data.main_file && data.main_file.trim() !== '') {
                        addFormatItem(container, 'Main SOP Document', data.main_file, true);
                    }

                    // Add Additional Formats
                    if (data.formats && data.formats.length > 0) {
                        data.formats.forEach(fmt => {
                            addFormatItem(container, fmt.format_name, fmt.file_name, false);
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    loading.innerHTML = '<p class="text-danger text-center">Error loading formats.</p>';
                });
        }

        function addFormatItem(container, name, filename, isMain) {
            const item = document.createElement('div');
            item.className = 'list-group-item';
            const ext = filename.split('.').pop().toLowerCase();
            let iconClass = 'fa-file-alt';
            if (['jpg', 'jpeg', 'png'].includes(ext)) iconClass = 'fa-file-image';
            if (ext === 'pdf') iconClass = 'fa-file-pdf';
            if (['doc', 'docx'].includes(ext)) iconClass = 'fa-file-word';

            item.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="format-icon"><i class="fas ${iconClass}"></i></div>
                    <div>
                        <div class="fw-bold text-dark">${name}</div>
                        <small class="text-muted">${isMain ? 'Original Document' : 'Annexure/Format'}</small>
                    </div>
                </div>
                <a href="request_sop_reason.php?file=${encodeURIComponent(filename)}&title=${encodeURIComponent(name)}" class="btn btn-sm btn-primary">
                    <i class="fas fa-print me-1"></i> View / Print
                </a>
            `;
            container.appendChild(item);
        }

        // ORIGINAL GRID SEARCH FUNCTIONALITY
        document.getElementById('sopSearch').addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase();
            const items = document.querySelectorAll('.sop-item');
            items.forEach(item => {
                const title = item.querySelector('.sop-title').textContent.toLowerCase();
                const number = item.querySelector('.text-muted').textContent.toLowerCase();
                if (title.includes(searchText) || number.includes(searchText)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        function openFormatModal(sopId, title, sopNumber) {
            document.getElementById('modalTitle').textContent = sopNumber + ': ' + title;
            const loading = document.getElementById('modalLoading');
            const list = document.getElementById('formatList');

            loading.classList.remove('d-none');
            list.classList.add('d-none');
            list.innerHTML = ''; // Clear previous

            const modal = new bootstrap.Modal(document.getElementById('formatModal'));
            modal.show();

            // Fetch formats via AJAX
            fetch(`get_sop_formats.php?sop_id=${sopId}`)
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('d-none');
                    list.classList.remove('d-none');

                    // Add Main SOP only if it exists
                    if (data.main_file && data.main_file.trim() !== '') {
                        addFormatItem(list, 'Main SOP Document', data.main_file, true);
                    }

                    // Add Additional Formats
                    if (data.formats && data.formats.length > 0) {
                        data.formats.forEach(fmt => {
                            addFormatItem(list, fmt.format_name, fmt.file_name, false);
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    loading.innerHTML = '<p class="text-danger text-center">Error loading formats.</p>';
                });
        }
    </script>
</body>

</html>