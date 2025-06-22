<?php
set_time_limit(0);

// List of channels: 'folder_name' => 'stream_url'
$channels = [
    'SonyYay' => 'https://air-stream-ts.onrender.com/box.ts?id=3',
    'starsports1tamil'          => 'https://air-stream-ts.onrender.com/box.ts?id=4',
];

// Segment config
$segmentDuration = 10;
$baseDir = __DIR__;
$segmentLimit = 5; // only 5 segments max

foreach ($channels as $name => $url) {
    $pid = pcntl_fork();
    if ($pid === -1) {
        die("Could not fork for channel $name\n");
    } elseif ($pid > 0) {
        continue; // parent continues to next channel
    }

    // Child process per channel
    $outputDir = "$baseDir/$name";
    if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);

    $segmentIndex = 0;

    while (true) {
        $segmentFile = "$outputDir/index$segmentIndex.ts";
        $liveUrl = $url . '&cache=' . time();

        // FFmpeg command to capture 10s
        $cmd = "ffmpeg -y -fflags +discardcorrupt -re -rw_timeout 5000000 -i \"$liveUrl\" -t $segmentDuration -c copy \"$segmentFile\" 2>&1";
        shell_exec($cmd);

        if (file_exists($segmentFile)) {
            // Build playlist with last 5 segments
            $startIndex = max(0, $segmentIndex - $segmentLimit + 1);
            $m3u8 = "#EXTM3U\n";
            $m3u8 .= "#EXT-X-VERSION:3\n";
            $m3u8 .= "#EXT-X-TARGETDURATION:$segmentDuration\n";
            $m3u8 .= "#EXT-X-MEDIA-SEQUENCE:$startIndex\n";

            for ($i = $startIndex; $i <= $segmentIndex; $i++) {
                $m3u8 .= "#EXTINF:$segmentDuration,\nindex$i.ts\n";
            }

            file_put_contents("$outputDir/index.m3u8", $m3u8);

            // Delete segments older than last 5
            $deleteIndex = $segmentIndex - $segmentLimit;
            if ($deleteIndex >= 0) {
                $oldFile = "$outputDir/index$deleteIndex.ts";
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
        }

        $segmentIndex++;
        sleep($segmentDuration);
    }

    exit; // end child
}
