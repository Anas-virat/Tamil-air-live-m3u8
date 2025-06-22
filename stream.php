<?php
set_time_limit(0);

// ✅ Channel list
$channels = [
    'sonyyay' => 'https://air-stream-ts.onrender.com/box.ts?id=3',
    'starsports1tamil' => 'https://air-stream-ts.onrender.com/box.ts?id=4',
];

// ✅ Segment settings
$segmentDuration = 10;
$segmentLimit = 5;
$baseDir = __DIR__;

while (true) {
    foreach ($channels as $name => $url) {
        $outputDir = "$baseDir/$name";
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Track segment index
        $indexFile = "$outputDir/last_index.txt";
        $segmentIndex = file_exists($indexFile) ? (int)file_get_contents($indexFile) : 0;

        $segmentFile = "$outputDir/index$segmentIndex.ts";
        $liveUrl = $url . '&cache=' . time();

        // Run FFmpeg to record a 10-second .ts segment
        $cmd = "ffmpeg -y -fflags +discardcorrupt -re -rw_timeout 5000000 -i \"$liveUrl\" -t $segmentDuration -c copy \"$segmentFile\" 2>&1";
        shell_exec($cmd);

        if (file_exists($segmentFile)) {
            // Build index.m3u8 with up to 5 segments
            $startIndex = max(0, $segmentIndex - $segmentLimit + 1);
            $m3u8 = "#EXTM3U\n";
            $m3u8 .= "#EXT-X-VERSION:3\n";
            $m3u8 .= "#EXT-X-TARGETDURATION:$segmentDuration\n";
            $m3u8 .= "#EXT-X-MEDIA-SEQUENCE:$startIndex\n";

            for ($i = $startIndex; $i <= $segmentIndex; $i++) {
                $m3u8 .= "#EXTINF:$segmentDuration,\nindex$i.ts\n";
            }

            file_put_contents("$outputDir/index.m3u8", $m3u8);
            file_put_contents($indexFile, $segmentIndex);

            // Delete old segment
            $oldIndex = $segmentIndex - $segmentLimit;
            if ($oldIndex >= 0) {
                $oldFile = "$outputDir/index$oldIndex.ts";
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
        }

        $segmentIndex++;
    }

    // Wait before starting next loop
    sleep($segmentDuration);
}
