<?php
/**
 * File Uploader
 * 
 * Secure file upload handling with validation and sanitization
 */

class FileUploader {
    private $allowedExtensions = [];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB default
    private $uploadPath = '';
    private $errors = [];
    private $uploadedFiles = [];
    
    /**
     * Constructor
     * 
     * @param string $uploadPath Path to the upload directory
     * @param array $allowedExtensions Allowed file extensions (without dot)
     * @param int $maxFileSize Maximum file size in bytes
     */
    public function __construct($uploadPath = '', $allowedExtensions = [], $maxFileSize = null) {
        $this->setUploadPath($uploadPath);
        
        if (!empty($allowedExtensions)) {
            $this->setAllowedExtensions($allowedExtensions);
        }
        
        if ($maxFileSize !== null) {
            $this->setMaxFileSize($maxFileSize);
        }
    }
    
    /**
     * Set allowed file extensions
     * 
     * @param array $extensions Array of allowed extensions (without dot)
     * @return self
     */
    public function setAllowedExtensions(array $extensions) {
        $this->allowedExtensions = array_map('strtolower', $extensions);
        return $this;
    }
    
    /**
     * Set maximum file size
     * 
     * @param int $bytes Maximum file size in bytes
     * @return self
     */
    public function setMaxFileSize($bytes) {
        $this->maxFileSize = (int) $bytes;
        return $this;
    }
    
    /**
     * Set upload path
     * 
     * @param string $path Path to the upload directory
     * @return self
     */
    public function setUploadPath($path) {
        $this->uploadPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
        
        // Create directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            if (!@mkdir($this->uploadPath, 0755, true)) {
                throw new RuntimeException("Failed to create upload directory: {$this->uploadPath}");
            }
        }
        
        // Ensure the directory is writable
        if (!is_writable($this->uploadPath)) {
            throw new RuntimeException("Upload directory is not writable: {$this->uploadPath}");
        }
        
        return $this;
    }
    
    /**
     * Get error messages
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get uploaded files
     * 
     * @return array Array of uploaded file information
     */
    public function getUploadedFiles() {
        return $this->uploadedFiles;
    }
    
    /**
     * Process file upload
     * 
     * @param string $fieldName Name of the file input field
     * @param string $newFilename Optional new filename (without extension)
     * @return bool True if upload was successful, false otherwise
     */
    public function upload($fieldName, $newFilename = null) {
        $this->errors = [];
        $this->uploadedFiles = [];
        
        if (!isset($_FILES[$fieldName])) {
            $this->errors[] = "No file was uploaded.";
            return false;
        }
        
        $files = $this->normalizeFiles($_FILES[$fieldName]);
        $success = true;
        
        foreach ($files as $file) {
            if (!$this->validateFile($file)) {
                $success = false;
                continue;
            }
            
            $uploadResult = $this->processFile($file, $newFilename);
            
            if ($uploadResult === false) {
                $success = false;
            } else {
                $this->uploadedFiles[] = $uploadResult;
            }
        }
        
        return $success;
    }
    
    /**
     * Normalize $_FILES array to handle multiple files
     */
    private function normalizeFiles($files) {
        $normalized = [];
        
        // Single file upload
        if (!is_array($files['name'])) {
            return [$files];
        }
        
        // Multiple files
        $count = count($files['name']);
        $keys = array_keys($files);
        
        for ($i = 0; $i < $count; $i++) {
            foreach ($keys as $key) {
                $normalized[$i][$key] = $files[$key][$i];
            }
        }
        
        return $normalized;
    }
    
    /**
     * Validate a single file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addUploadError($file['name'], $this->getUploadErrorMessage($file['error']));
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->addUploadError($file['name'], "File size exceeds the maximum allowed size of " . $this->formatBytes($this->maxFileSize));
            return false;
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!empty($this->allowedExtensions) && !in_array($extension, $this->allowedExtensions)) {
            $this->addUploadError($file['name'], "File type not allowed. Allowed types: " . implode(', ', $this->allowedExtensions));
            return false;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Additional security check using MIME type if needed
        // This is a basic example, you might want to expand this
        
        return true;
    }
    
    /**
     * Process a single file upload
     */
    private function processFile($file, $newFilename = null) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Generate a secure filename
        if ($newFilename === null) {
            $filename = $this->generateFilename($extension);
        } else {
            $filename = $this->sanitizeFilename($newFilename) . '.' . $extension;
        }
        
        $destination = $this->uploadPath . $filename;
        
        // Ensure the destination is within the upload directory
        if (strpos(realpath($destination), realpath($this->uploadPath)) !== 0) {
            $this->addUploadError($file['name'], "Invalid file path");
            return false;
        }
        
        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->addUploadError($file['name'], "Failed to move uploaded file");
            return false;
        }
        
        // Set proper permissions
        chmod($destination, 0644);
        
        return [
            'original_name' => $file['name'],
            'filename' => $filename,
            'path' => $destination,
            'size' => $file['size'],
            'mime_type' => $file['type'],
            'extension' => $extension,
            'url' => $this->getFileUrl($filename)
        ];
    }
    
    /**
     * Generate a secure filename
     */
    private function generateFilename($extension) {
        return uniqid('file_', true) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }
    
    /**
     * Sanitize a filename
     */
    private function sanitizeFilename($filename) {
        // Remove anything which isn't a word, whitespace, number, or any of the following characters -_~,;[]().
        $filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\)\.])", '', $filename);
        // Remove any runs of periods
        $filename = preg_replace('([\.]{2,})', '.', $filename);
        // Convert spaces to underscores
        $filename = str_replace(' ', '_', $filename);
        // Convert to lowercase
        $filename = strtolower($filename);
        // Remove anything which isn't a word, number, or the following characters: -_~,;[]().
        $filename = preg_replace("([^\w\d\-~,;\[\]\(\)\.])", '', $filename);
        
        return $filename;
    }
    
    /**
     * Get file URL (for web access)
     */
    private function getFileUrl($filename) {
        $baseUrl = rtrim(str_replace('\\', '/', str_ireplace($_SERVER['DOCUMENT_ROOT'], '', $this->uploadPath)), '/');
        return $baseUrl . '/' . $filename;
    }
    
    /**
     * Add an upload error
     */
    private function addUploadError($filename, $message) {
        $this->errors[] = [
            'file' => $filename,
            'message' => $message
        ];
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Format bytes to human-readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Delete a file
     * 
     * @param string $filename The filename to delete
     * @return bool True if the file was deleted, false otherwise
     */
    public function deleteFile($filename) {
        $filePath = $this->uploadPath . basename($filename);
        
        // Ensure the file is within the upload directory
        if (strpos(realpath($filePath), realpath($this->uploadPath)) !== 0) {
            $this->errors[] = "Invalid file path";
            return false;
        }
        
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        
        $this->errors[] = "File not found: $filename";
        return false;
    }
    
    /**
     * Clean up old files in the upload directory
     * 
     * @param int $maxAge Maximum file age in seconds
     * @return array Array of deleted files
     */
    public function cleanupOldFiles($maxAge = 86400) {
        $deleted = [];
        $now = time();
        
        if ($handle = opendir($this->uploadPath)) {
            while (false !== ($file = readdir($handle))) {
                $filePath = $this->uploadPath . $file;
                
                if ($file === '.' || $file === '..' || is_dir($filePath)) {
                    continue;
                }
                
                if (($now - filemtime($filePath)) > $maxAge) {
                    if (@unlink($filePath)) {
                        $deleted[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        
        return $deleted;
    }
}
