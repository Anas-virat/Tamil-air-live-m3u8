<?php
$channel_name = basename(__FILE__, '.php'); // "StarSports1Tamil"
$stream_url = "https://air-stream-ts.onrender.com/box.ts?id=4";

$output_dir = __DIR__ . "/$channel_name";
if (!is_dir($output_dir)) mkdir($output_dir, 0777, true);

$pid_file = "$output_dir/ffmpeg.pid";

// Check if already running
if (file_exists($pid_file)) {
    $pid = trim(file_get_contents($pid_file));
    if (posix_kill((int)$pid, 0)) {
        echo "$channel_name is already running (PID: $pid)";
        exit;
    }
}

$output_path = "$channel_name/index.m3u8";

// 🟢 'nohup' makes FFmpeg keep running even if PHP dies
$cmd = "nohup ffmpeg -hide_banner -y -re -i \"$stream_url\" \
-c copy \
-hls_time 4 \
-hls_list_size 5 \
-hls_flags delete_segments+omit_endlist \
-f hls \"$output_path\" > \"$output_dir/output.log\" 2>&1 & echo $!";

// Start FFmpeg
$pid = shell_exec($cmd);
file_put_contents($pid_file, $pid);

echo "Started stream: $channel_name (PID: $pid)";
