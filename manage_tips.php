<?php
session_start();
require_once 'helpers.php';
requireLogin();
checkRole(['qa', 'admin']); // Only QA and Admin

$pdo = getDBConnection();
$message = '';
$error = '';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $category = $_POST['category'];
        $content = trim($_POST['content']);
        $user_id = $_SESSION['user_id']; // Actually user_id is string, but added_by is INT (id). Let's use user's table ID if possible, or just skip if no int ID.
        // Wait, setup_knowledge_tips.php defines added_by as INT. current session has user_id (string).
        // Let's resolve internal ID.
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $u = $stmt->fetch();
        $added_by = $u ? $u['id'] : NULL;

        if (!empty($content)) {
            $stmt = $pdo->prepare("INSERT INTO knowledge_tips (category, tip_content, added_by) VALUES (?, ?, ?)");
            if ($stmt->execute([$category, $content, $added_by])) {
                $message = "Tip added successfully!";
            } else {
                $error = "Failed to add tip.";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['tip_id'];
        $stmt = $pdo->prepare("DELETE FROM knowledge_tips WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Tip deleted.";
    }
}

// Fetch all tips
$tips = $pdo->query("SELECT * FROM knowledge_tips ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Manage Knowledge Tips - Kopran</title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/layout_styles.css"> <!-- Common Layout Styles -->
    <style>
        .page-header h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--text-primary);
        }

        .manage-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .manage-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 24px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .manage-card .card-body {
            padding: 24px;
        }

        .table thead th {
            font-weight: 600;
            background: #f1f5f9;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table tbody td {
            vertical-align: middle;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
        }

        .badge-category {
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
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
                <h2><i class="fas fa-lightbulb text-warning me-2"></i> Manage Knowledge Tips</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Add New Tip Form -->
            <div class="manage-card">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>Add New Knowledge Tip
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted fw-bold">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="GMP">GMP</option>
                                    <option value="21CFR">21 CFR</option>
                                    <option value="SOP">SOP</option>
                                    <option value="Change Control">Change Control</option>
                                    <option value="Incident">Incident</option>
                                    <option value="General" selected>General</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small text-muted fw-bold">Tip Content</label>
                                <textarea name="content" class="form-control" rows="1" required
                                    placeholder="Enter a professional tip or quote..."></textarea>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>
                                    Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tips List -->
            <div class="manage-card">
                <div class="card-header">
                    <i class="fas fa-list me-2 text-secondary"></i>Existing Tips
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Category</th>
                                    <th style="width: 75%;">Content</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tips)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No tips found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tips as $tip): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $catClass = 'bg-secondary';
                                                switch ($tip['category']) {
                                                    case 'GMP':
                                                        $catClass = 'bg-success';
                                                        break;
                                                    case '21CFR':
                                                        $catClass = 'bg-danger';
                                                        break;
                                                    case 'SOP':
                                                        $catClass = 'bg-primary';
                                                        break;
                                                    case 'Incident':
                                                        $catClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'Change Control':
                                                        $catClass = 'bg-info text-dark';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $catClass; ?> badge-category">
                                                    <?php echo htmlspecialchars($tip['category']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($tip['tip_content']); ?>
                                            </td>
                                            <td>
                                                <form method="POST" onsubmit="return confirm('Delete this tip?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Right Panel (Knowledge Sharing) -->
        <?php include 'right_panel.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>