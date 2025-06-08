<?php
// seo_masterplan_dms/core/helpers.php

if (!function_exists('get_branding_setting')) {
    /**
     * Fetches a specific branding setting from the database.
     *
     * @param PDO $pdo The PDO database connection object.
     * @param string $setting_name The name of the setting to fetch.
     * @return string|null The setting value or null if not found or error.
     */
    function get_branding_setting($pdo, $setting_name) {
        if (!$pdo) { return null; } // Guard clause if $pdo is not available
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM branding_settings WHERE setting_name = ?");
            $stmt->execute([$setting_name]);
            $result = $stmt->fetchColumn();
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log("Error fetching branding setting '$setting_name': " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('set_branding_setting')) {
    /**
     * Updates or inserts a specific branding setting in the database.
     *
     * @param PDO $pdo The PDO database connection object.
     * @param string $setting_name The name of the setting.
     * @param string $setting_value The value of the setting.
     * @return bool True on success, false on failure.
     */
    function set_branding_setting($pdo, $setting_name, $setting_value) {
        if (!$pdo) { return false; } // Guard clause
        try {
            // Using INSERT ... ON DUPLICATE KEY UPDATE is efficient
            $stmt = $pdo->prepare("INSERT INTO branding_settings (setting_name, setting_value) VALUES (?, ?)
                                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            return $stmt->execute([$setting_name, $setting_value]);
        } catch (PDOException $e) {
            error_log("Error setting branding setting '$setting_name': " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_file_icon_class')) {
    /**
     * Returns a Font Awesome icon class based on the file extension or MIME type.
     *
     * @param string $filename_or_mimetype The original filename or MIME type.
     * @return string Font Awesome icon class.
     */
    function get_file_icon_class($filename_or_mimetype) {
        $extension = '';
        if (strpos($filename_or_mimetype, '.') !== false) { // Likely a filename
            $extension = strtolower(pathinfo($filename_or_mimetype, PATHINFO_EXTENSION));
        } else if (strpos($filename_or_mimetype, '/') !== false) { // Likely a MIME type
            // Basic MIME type to extension mapping (can be expanded)
            $mime_map = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'application/vnd.ms-powerpoint' => 'ppt',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/svg+xml' => 'svg',
                'text/plain' => 'txt',
                'application/zip' => 'zip',
                'application/x-figma' => 'figma', // Custom for figma
                'application/octet-stream' => 'generic' // Fallback for .fig if MIME is octet-stream
            ];
            foreach ($mime_map as $mime => $ext) {
                if (strpos(strtolower($filename_or_mimetype), $mime) !== false) {
                    $extension = $ext;
                    break;
                }
            }
            if (empty($extension) && $filename_or_mimetype === 'application/octet-stream'){
                $extension = 'generic'; // for figma if only octet-stream is detected
            }
        }


        switch ($extension) {
            case 'pdf':
                return 'fas fa-file-pdf'; // Solid PDF icon
            case 'doc':
            case 'docx':
                return 'fas fa-file-word';
            case 'xls':
            case 'xlsx':
                return 'fas fa-file-excel';
            case 'ppt':
            case 'pptx':
                return 'fas fa-file-powerpoint';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return 'fas fa-file-image';
            case 'svg':
                return 'fas fa-file-image'; // Or a specific SVG icon if available
            case 'txt':
                return 'fas fa-file-alt'; // Or fa-file-lines
            case 'zip':
            case 'rar':
                return 'fas fa-file-archive'; // Or fa-file-zipper
            case 'fig': // Figma extension
                return 'fab fa-figma'; // Font Awesome has a Figma brand icon
            case 'figma': // from MIME map
                return 'fab fa-figma';
            case 'generic': // from MIME map if it was octet-stream and we identified it as potentially .fig
                 return 'fas fa-file-dashed-line'; // A generic file icon
            default:
                return 'fas fa-file'; // Default generic file icon
        }
    }
}
?>