<?php
/**
 * Tag Format Helper Functions
 * Handles automatic tag generation and increment management
 */

require_once __DIR__ . '/../connect.php';

class TagFormatHelper {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Generate next tag number for a specific type
     * @param string $tagType - Type of tag (red_tag, ics_no, itr_no, par_no, ris_no, inventory_tag)
     * @return string|false - Generated tag or false on error
     */
    public function generateNextTag($tagType) {
        try {
            // Get tag format configuration
            $stmt = $this->conn->prepare("SELECT * FROM tag_formats WHERE tag_type = ? AND is_active = 1 LIMIT 1");
            $stmt->bind_param("s", $tagType);
            $stmt->execute();
            $format = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$format) {
                throw new Exception("No active format found for tag type: $tagType");
            }
            
            // Parse the template and generate tag
            return $this->parseAndGenerateTag($format);
            
        } catch (Exception $e) {
            error_log("Tag generation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parse template and generate the actual tag
     * @param array $format - Format configuration from database
     * @return string - Generated tag
     */
    private function parseAndGenerateTag($format) {
        $template = $format['format_template'];
        $tagType = $format['tag_type'];
        
        // Replace date placeholders (if any)
        $template = $this->replaceDatePlaceholders($template);
        
        // For simple prefix+digits format, use a single counter per tag type
        $prefixHash = md5($format['prefix']);
        
        // Get or create counter for this tag type and prefix (no year separation)
        $counter = $this->getOrCreateCounter($tagType, 'global', $prefixHash);
        
        // Increment counter
        $nextNumber = $counter + 1;
        $this->updateCounter($tagType, 'global', $prefixHash, $nextNumber);
        
        // Replace increment placeholder with formatted number
        $incrementPlaceholder = '{' . str_repeat('#', $format['increment_digits']) . '}';
        $formattedNumber = str_pad($nextNumber, $format['increment_digits'], '0', STR_PAD_LEFT);
        $template = str_replace($incrementPlaceholder, $formattedNumber, $template);
        
        return $template;
    }
    
    /**
     * Replace date placeholders in template
     * @param string $template - Template with date placeholders
     * @return string - Template with dates replaced
     */
    private function replaceDatePlaceholders($template) {
        $replacements = [
            '{YYYY}' => date('Y'),
            '{YY}' => date('y'),
            '{MM}' => date('m'),
            '{DD}' => date('d'),
            '{MMDD}' => date('md'),
            '{YYYYMM}' => date('Ym'),
            '{YYYYMMDD}' => date('Ymd')
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Get or create counter for specific tag type, year, and prefix
     * @param string $tagType
     * @param string $year
     * @param string $prefixHash
     * @return int - Current counter value
     */
    private function getOrCreateCounter($tagType, $year, $prefixHash) {
        // Try to get existing counter
        $stmt = $this->conn->prepare("SELECT current_count FROM tag_counters WHERE tag_type = ? AND year_period = ? AND prefix_hash = ?");
        $stmt->bind_param("sss", $tagType, $year, $prefixHash);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($result) {
            return (int)$result['current_count'];
        }
        
        // Create new counter
        $stmt = $this->conn->prepare("INSERT INTO tag_counters (tag_type, year_period, prefix_hash, current_count) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $tagType, $year, $prefixHash);
        $stmt->execute();
        $stmt->close();
        
        return 0;
    }
    
    /**
     * Update counter value
     * @param string $tagType
     * @param string $year
     * @param string $prefixHash
     * @param int $newCount
     */
    private function updateCounter($tagType, $year, $prefixHash, $newCount) {
        $stmt = $this->conn->prepare("UPDATE tag_counters SET current_count = ?, updated_at = CURRENT_TIMESTAMP WHERE tag_type = ? AND year_period = ? AND prefix_hash = ?");
        $stmt->bind_param("isss", $newCount, $tagType, $year, $prefixHash);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Get all tag formats
     * @return array - All tag formats
     */
    public function getAllTagFormats() {
        $stmt = $this->conn->prepare("SELECT * FROM tag_formats ORDER BY tag_type");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }
    
    /**
     * Update tag format
     * @param string $tagType
     * @param array $data - Format data
     * @return bool - Success status
     */
    public function updateTagFormat($tagType, $data) {
        try {
            // Get current format to check if prefix changed
            $stmt = $this->conn->prepare("SELECT prefix FROM tag_formats WHERE tag_type = ?");
            $stmt->bind_param("s", $tagType);
            $stmt->execute();
            $current = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            // Update format
            $stmt = $this->conn->prepare("UPDATE tag_formats SET format_template = ?, prefix = ?, suffix = ?, increment_digits = ?, date_format = ?, updated_at = CURRENT_TIMESTAMP WHERE tag_type = ?");
            $stmt->bind_param("sssiss", 
                $data['format_template'], 
                $data['prefix'], 
                $data['suffix'], 
                $data['increment_digits'], 
                $data['date_format'], 
                $tagType
            );
            $success = $stmt->execute();
            $stmt->close();
            
            // If prefix changed and reset_on_change is enabled, reset counters
            if ($success && $current && $current['prefix'] !== $data['prefix']) {
                $this->resetCountersForTagType($tagType);
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Tag format update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset counters for a tag type (when prefix changes)
     * @param string $tagType
     */
    private function resetCountersForTagType($tagType) {
        $stmt = $this->conn->prepare("DELETE FROM tag_counters WHERE tag_type = ?");
        $stmt->bind_param("s", $tagType);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Get preview of next tag without incrementing
     * @param string $tagType
     * @return string|false - Preview tag or false on error
     */
    public function previewNextTag($tagType) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM tag_formats WHERE tag_type = ? AND is_active = 1 LIMIT 1");
            $stmt->bind_param("s", $tagType);
            $stmt->execute();
            $format = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$format) {
                return false;
            }
            
            $template = $format['format_template'];
            
            // Replace date placeholders (if any)
            $template = $this->replaceDatePlaceholders($template);
            
            // Get current counter (without incrementing) - using global counter
            $prefixHash = md5($format['prefix']);
            $counter = $this->getOrCreateCounter($format['tag_type'], 'global', $prefixHash);
            $nextNumber = $counter + 1;
            
            // Replace increment placeholder
            $incrementPlaceholder = '{' . str_repeat('#', $format['increment_digits']) . '}';
            $formattedNumber = str_pad($nextNumber, $format['increment_digits'], '0', STR_PAD_LEFT);
            $template = str_replace($incrementPlaceholder, $formattedNumber, $template);
            
            return $template;
            
        } catch (Exception $e) {
            error_log("Tag preview error: " . $e->getMessage());
            return false;
        }
    }
}

// Global helper function for easy access
function generateTag($tagType) {
    global $conn;
    $helper = new TagFormatHelper($conn);
    return $helper->generateNextTag($tagType);
}

function previewTag($tagType) {
    global $conn;
    $helper = new TagFormatHelper($conn);
    return $helper->previewNextTag($tagType);
}
?>
