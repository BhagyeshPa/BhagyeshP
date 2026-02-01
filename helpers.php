<?php
/**
 * Helper Functions for Kopran Application
 * Provides shared utilities for audit logging, role checking, and common operations
 */

// Set default timezone to India Standard Time
date_default_timezone_set('Asia/Kolkata');

/**
 * Log an action to the audit trail
 * 
 * @param PDO $pdo Database connection
 * @param string $userId User ID performing the action
 * @param string $action Action description
 * @param string $details Additional details (optional)
 * @param string $ipAddress IP address of the user (optional)
 * @return bool Success status
 */
function logAudit($pdo, $userId, $action, $details = '', $ipAddress = null)
{
    try {
        if ($ipAddress === null) {
            $ipAddress = getClientIP();
        }

        $stmt = $pdo->prepare("INSERT INTO audit_trail (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $action, $details, $ipAddress]);
    } catch (PDOException $e) {
        error_log("Audit log error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if current user has required role
 * Redirects to dashboard if unauthorized
 * 
 * @param array $allowedRoles Array of allowed roles (e.g., ['admin', 'qa'])
 * @param bool $redirect Whether to redirect on failure (default: true)
 * @return bool True if authorized, false otherwise
 */
function checkRole($allowedRoles, $redirect = true)
{
    if (!isset($_SESSION['role'])) {
        if ($redirect) {
            header('Location: login.php');
            exit;
        }
        return false;
    }

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        if ($redirect) {
            header('Location: dashboard.php?error=unauthorized');
            exit;
        }
        return false;
    }

    return true;
}

/**
 * Get client IP address
 * Handles proxies and forwarded IPs
 * 
 * @return string Client IP address
 */
function getClientIP()
{
    $ipAddress = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    return $ipAddress;
}

/**
 * Get database connection
 * 
 * @return PDO Database connection
 */
function getDBConnection()
{
    $host = 'localhost';
    $dbname = 'kopran';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Format timestamp for display
 * 
 * @param string $timestamp Database timestamp
 * @return string Formatted date/time
 */
function formatTimestamp($timestamp)
{
    return date('d M Y, h:i A', strtotime($timestamp));
}

/**
 * Sanitize filename for upload
 * 
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitizeFilename($filename)
{
    // Remove any path components
    $filename = basename($filename);

    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);

    // Remove any characters that aren't alphanumeric, underscore, hyphen, or dot
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);

    return $filename;
}

/**
 * Generate unique filename to prevent overwrites
 * 
 * @param string $originalFilename Original filename
 * @param string $directory Target directory
 * @return string Unique filename
 */
function generateUniqueFilename($originalFilename, $directory)
{
    $filename = sanitizeFilename($originalFilename);
    $pathInfo = pathinfo($filename);
    $baseName = $pathInfo['filename'];
    $extension = $pathInfo['extension'] ?? '';

    $counter = 1;
    $newFilename = $filename;

    while (file_exists($directory . '/' . $newFilename)) {
        $newFilename = $baseName . '_' . $counter . ($extension ? '.' . $extension : '');
        $counter++;
    }

    return $newFilename;
}

/**
 * Check if user is logged in
 * 
 * @param bool $redirect Whether to redirect to login page
 * @return bool True if logged in
 */
function requireLogin($redirect = true)
{
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        if ($redirect) {
            header('Location: login.php');
            exit;
        }
        return false;
    }
    return true;
}

/**
 * Verify password against hash
 * 
 * @param string $password Input password
 * @param string $hash Stored hash
 * @return bool True if match
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Hash password using bcrypt
 * 
 * @param string $password Password to hash
 * @return string Hashed password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Check if account is locked
 * 
 * @param PDO $pdo Database connection
 * @param string $userId User ID
 * @return bool True if locked
 */
function isAccountLocked($pdo, $userId)
{
    // Check if account_locked flag is set (assuming column exists)
    // Also check if failed_login_attempts >= 5 (policy)
    // NOTE: This assumes columns exist. If not, we should default to false/safe.
    try {
        $stmt = $pdo->prepare("SELECT account_locked, failed_login_attempts FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            return $res['account_locked'] == 1;
        }
        return false;
    } catch (Exception $e) {
        return false; // Fail safe
    }
}

/**
 * Record a failed login attempt
 * 
 * @param PDO $pdo Database connection
 * @param string $userId User ID
 * @return void
 */
function recordFailedLogin($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Lock if > 5 attempts
        $stmt = $pdo->prepare("UPDATE users SET account_locked = 1 WHERE user_id = ? AND failed_login_attempts >= 5");
        $stmt->execute([$userId]);
    } catch (Exception $e) {
        // Ignore error if column missing
    }
}

/**
 * Reset failed login attempts
 * 
 * @param PDO $pdo Database connection
 * @param string $userId User ID
 * @return void
 */
function resetFailedLogins($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, last_login_at = NOW() WHERE user_id = ?");
        $stmt->execute([$userId]);
    } catch (Exception $e) {
        // Ignore
    }
}

/**
 * Check if password needs rehash
 * 
 * @param string $hash Current hash
 * @return bool True if needs rehash
 */
function needsRehash($hash)
{
    return password_needs_rehash($hash, PASSWORD_BCRYPT);
}
?>