<?php
set_time_limit(0);

// ✅ Channel list (name => ts URL)
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
        if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);

        // Track segment index per channel
        $indexFile = "$outputDir/last_index.txt";
        $segmentIndex = file_exists($indexFile) ? (int)file_get_contents($indexFile) : 0;

        $segmentFile = "$outputDir/index$segmentIndex.ts";
        $liveUrl = $url . '&cache=' . time();

        // Record 10s TS segment
        $cmd = "ffmpeg -y -fflags
