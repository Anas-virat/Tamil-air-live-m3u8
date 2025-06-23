<?php
// Don't timeout or quit
set_time_limit(0);
ignore_user_abort(true);

// Stream input and channel name
$url = "https://air-stream-ts.onrender.com/box.ts?id=4"; // ✅ Your TS URL
$channel = "Starsports1tamil"; // ✅ Folder name (channel)

// Constants
$segmentDuration = 10; // 10 seconds per segment
$maxSegments = 6;

// Output folder
$baseFolder = __DIR__ . "/$channel";
if (!is_dir($baseFolder)) mkdir($baseFolder, 0777, true);

// Filenames
$segmentPrefix = "segment_";
$playlistFile = "$baseFolder/playlist.m3u8";

$segmentIndex = 0;
$segmentList = [];

echo "▶ Starting segmenter...\n";
echo "🌐 Source: $url\n📁 Saving to: $baseFolder\n";

// Infinite loop
while (true) {
    $startTime = microtime(true);
    $segmentFile = "$baseFolder/{$segmentPrefix}{$segmentIndex}.ts";

    echo "📦 Writing: $segmentFile\n";

    // Open stream to read
    $stream = fopen($url, 'r');
    $output = fopen($segmentFile, 'w');

    if (!$stream || !$output) {
        echo "❌ Stream/File error. Retrying in 5 seconds...\n";
        sleep(5);
        continue;
    }

    // Read TS data for 10 seconds
    while ((microtime(true) - $startTime) < $segmentDuration) {
        $data = fread($stream, 8192);
        if (!$data) break;
        fwrite($output, $data);
    }

    fclose($stream);
    fclose($output);

    // Update segment list
    $segmentList[] = "{$segmentPrefix}{$segmentIndex}.ts";
    if (count($segmentList) > $maxSegments) {
        $old = array_shift($segmentList);
        @unlink("$baseFolder/$old");
    }

    // Build M3U8
    $m3u8 = "#EXTM3U\n";
    $m3u8 .= "#EXT-X-VERSION:3\n";
    $m3u8 .= "#EXT-X-TARGETDURATION:$segmentDuration\n";
    $m3u8 .= "#EXT-X-MEDIA-SEQUENCE:" . ($segmentIndex - count($segmentList) + 1) . "\n";

    foreach ($segmentList as $seg) {
        $m3u8 .= "#EXTINF:$segmentDuration,\n$seg\n";
    }

    file_put_contents($playlistFile, $m3u8);

    $segmentIndex++;
}
