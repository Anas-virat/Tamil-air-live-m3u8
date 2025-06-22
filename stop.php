<?php
if (!isset($_GET['name'])) {
    die("Usage: stop.php?name=ChannelName");
}

$channel_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_GET['name']);
$pid_file = __DIR__ . "/$channel_name/ffmpeg.pid";

if (!file_exists($pid_file)) {
    die("FFmpeg not running or PID file missing for $channel_name.");
}

$pid = trim(file_get_contents($pid_file));

// Try killing the process
if (posix_kill((int)$pid, 0)) {
    exec("kill $pid");
    unlink($pid_file);
    echo "FFmpeg for $channel_name stopped (PID $pid)";
} else {
    unlink($pid_file); // stale PID file
    echo "FFmpeg not running. Removed stale PID.";
}
