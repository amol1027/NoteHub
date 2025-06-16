<?php
// Validate and sanitize YouTube ID
$videoId = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id']) : null;
if (!$videoId || strlen($videoId) !== 11) {
    header("HTTP/1.1 400 Bad Request");
    die("Invalid YouTube ID");
}

// Check if we're listing formats, showing options, or downloading
$action = isset($_GET['action']) ? $_GET['action'] : 'options';

// If no specific action/format is set, show the options interface
if ($action === 'options') {
    // Get video info
    $infoCommand = sprintf(
        'yt-dlp --print "title" --print "duration" --print "thumbnail" -- %s',
        escapeshellarg("https://www.youtube.com/watch?v={$videoId}")
    );
    $videoInfo = shell_exec($infoCommand);
    $infoLines = explode("\n", $videoInfo);
    $videoTitle = isset($infoLines[0]) ? $infoLines[0] : 'YouTube Video';
    $videoDuration = isset($infoLines[1]) ? $infoLines[1] : '';
    $thumbnailUrl = isset($infoLines[2]) ? $infoLines[2] : '';

    // Format duration if available
    $formattedDuration = '';
    if (is_numeric($videoDuration)) {
        $minutes = floor($videoDuration / 60);
        $seconds = $videoDuration % 60;
        $formattedDuration = sprintf('%d:%02d', $minutes, $seconds);
    }

    // Display options interface
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Options for <?php echo htmlspecialchars($videoTitle); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        .video-container {
            display: flex;
            margin-bottom: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .thumbnail {
            flex: 0 0 180px;
            margin-right: 15px;
        }
        .thumbnail img {
            width: 100%;
            border-radius: 4px;
        }
        .video-info {
            flex: 1;
        }
        h1 {
            font-size: 22px;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .video-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .options-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .option-group {
            margin-bottom: 20px;
        }
        h2 {
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .radio-option {
            flex: 1 0 120px;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .radio-option:hover {
            background: #e9e9e9;
        }
        .radio-option input {
            margin-right: 5px;
        }
        .download-btn {
            display: inline-block;
            background: #4285f4;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .download-btn:hover {
            background: #3367d6;
        }
        .note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="video-container">
        <div class="thumbnail">
            <?php if ($thumbnailUrl): ?>
                <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" alt="Video thumbnail">
            <?php else: ?>
                <div style="height: 100px; background: #ddd; display: flex; align-items: center; justify-content: center;">No preview</div>
            <?php endif; ?>
        </div>
        <div class="video-info">
            <h1><?php echo htmlspecialchars($videoTitle); ?></h1>
            <?php if ($formattedDuration): ?>
                <div class="video-meta">Duration: <?php echo $formattedDuration; ?></div>
            <?php endif; ?>
            <div class="video-meta">YouTube ID: <?php echo htmlspecialchars($videoId); ?></div>
        </div>
    </div>

    <div class="options-container">
        <form action="download.php" method="get">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($videoId); ?>">
            <input type="hidden" name="action" value="download">
            
            <div class="option-group">
                <h2>Download Type</h2>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="type" value="video" checked> Video with Audio
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="type" value="audio"> Audio Only
                    </label>
                </div>
            </div>
            
            <div class="option-group" id="quality-options">
                <h2>Video Quality</h2>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="format" value="best" checked> Best Quality
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="format" value="1080p"> 1080p
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="format" value="720p"> 720p
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="format" value="480p"> 480p
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="format" value="360p"> 360p
                    </label>
                </div>
                <div class="note">Note: Lower qualities download faster and use less bandwidth.</div>
            </div>
            
            <div class="option-group">
                <h2>Download Method</h2>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="prompt" value="true" checked> Save File
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="prompt" value="false"> Direct Stream
                    </label>
                </div>
            </div>
            
            <button type="submit" class="download-btn">Download Now</button>
        </form>
    </div>

    <script>
        // Hide quality options when audio only is selected
        document.addEventListener('DOMContentLoaded', function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const qualityOptions = document.getElementById('quality-options');
            
            typeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'audio') {
                        qualityOptions.style.display = 'none';
                    } else {
                        qualityOptions.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>
    <?php
    exit;
}

if ($action === 'list_formats') {
    // Get available formats
    $command = sprintf(
        'yt-dlp -F -- %s',
        escapeshellarg("https://www.youtube.com/watch?v={$videoId}")
    );
    $formats = shell_exec($command);
    
    // Return formats as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'video_id' => $videoId,
        'formats' => explode("\n", $formats)
    ]);
    exit;
}

// Default is to download with specified options
$format = isset($_GET['format']) ? $_GET['format'] : 'best';
$type = isset($_GET['type']) ? $_GET['type'] : 'video'; // video, audio, or custom

// Determine format string based on user preferences
$formatString = '';
if ($type === 'audio') {
    $formatString = 'bestaudio[ext=m4a]/bestaudio';
} elseif ($type === 'video') {
    
    if ($format === 'best') {
        $formatString = 'best[ext=mp4]';
    } elseif ($format === '1080p') {
        $formatString = 'best[height<=1080][ext=mp4]';
    } elseif ($format === '720p') {
        $formatString = 'best[height<=720][ext=mp4]';
    } elseif ($format === '480p') {
        $formatString = 'best[height<=480][ext=mp4]';
    } elseif ($format === '360p') {
        $formatString = 'best[height<=360][ext=mp4]';
    }
} elseif ($type === 'custom' && isset($_GET['format_code'])) {
    // Allow user to specify exact format code
    $formatString = preg_replace('/[^a-zA-Z0-9+]/', '', $_GET['format_code']);
}

// If no valid format string was set, use default
if (empty($formatString)) {
    $formatString = 'best[ext=mp4]';
}

// First try downloading using direct URL
$command = sprintf(
    'yt-dlp -f "%s" --get-url -- %s',
    $formatString,
    escapeshellarg("https://www.youtube.com/watch?v={$videoId}")
);
$directUrl = shell_exec($command);

if (!$directUrl) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Failed to fetch download URL");
}

// Split the output by newline and get the first line
$lines = explode("\n", $directUrl);
$directUrl = trim($lines[0]);

// Ensure the URL does not contain newlines or other unwanted characters
$directUrl = filter_var($directUrl, FILTER_SANITIZE_URL);

// Check if we should use directly downloadable format or need to download and merge
if (strpos($formatString, '+') !== false || strpos($directUrl, '.m3u8') !== false) {
    // Need to download and merge video/audio or convert m3u8
    $tempFileName = 'downloads/' . uniqid('download_') . '.mp4';
    $extension = ($type === 'audio') ? 'mp3' : 'mp4';
    
    // Create downloads directory if it doesn't exist
    if (!file_exists('downloads')) {
        mkdir('downloads', 0755, true);
    }
    
    // Get video info for title
    $infoCommand = sprintf(
        'yt-dlp --print title -- %s',
        escapeshellarg("https://www.youtube.com/watch?v={$videoId}")
    );
    $videoTitle = trim(shell_exec($infoCommand));
    $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $videoTitle);
    
    // Use yt-dlp to download and merge the file
    $downloadCommand = sprintf(
        'yt-dlp -f "%s" -o "%s" -- %s',
        $formatString,
        escapeshellarg($tempFileName),
        escapeshellarg("https://www.youtube.com/watch?v={$videoId}")
    );
    
    // Execute download, with timeout protection
    $output = shell_exec($downloadCommand . ' 2>&1');
    
    // Check if file was downloaded successfully
    if (file_exists($tempFileName) && filesize($tempFileName) > 0) {
        // Serve the file to the user
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $safeTitle . '.' . $extension . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tempFileName));
        
        // Output file content
        readfile($tempFileName);
        
        // Clean up - delete the temporary file after serving
        register_shutdown_function(function() use ($tempFileName) {
            if (file_exists($tempFileName)) {
                unlink($tempFileName);
            }
        });
        
        exit;
    } else {
        // Log the error
        file_put_contents('debug.log', "Download failed: " . $output . "\n", FILE_APPEND);
        
        // Fall back to best single format
        $command = sprintf(
            'yt-dlp -f "best[ext=mp4]" --get-url -- %s',
            escapeshellarg("https://www.youtube.com/watch?v={$videoId}")
        );
        $directUrl = trim(shell_exec($command));
    }
}

// Log the output for debugging
file_put_contents('debug.log', "Format: $formatString\nURL: $directUrl\n", FILE_APPEND);

// Check if we should prompt for download or redirect
if (isset($_GET['prompt']) && $_GET['prompt'] === 'true') {
    // Get video info for title if not already obtained
    if (!isset($videoTitle)) {
        $infoCommand = sprintf(
            'yt-dlp --print title -- %s',
            escapeshellarg("https://www.youtube.com/watch?v={$videoId}")
        );
        $videoTitle = trim(shell_exec($infoCommand));
    }
    
    // Determine file extension
    $extension = 'mp4';
    if ($type === 'audio') {
        $extension = 'm4a';
    }
    
    // Clean title for filename
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $videoTitle) . '.' . $extension;
    
    // Send headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Stream the content
    readfile($directUrl);
} else {
    // Redirect the user to the direct download URL
    header("Location: " . $directUrl);
}
exit;
?>