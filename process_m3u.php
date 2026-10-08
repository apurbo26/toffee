<?php

$source_url = "https://alixbd.com/playlistconfig/playlist.m3u";
$target_category = "Toffee";
$output_file = "toffee_playlist.m3u";

// M3U কন্টেন্ট ডাউনলোড
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$m3u_content = curl_exec($ch);
curl_close($ch);

if (!$m3u_content) {
    die("Error: M3U playlist download failed.\n");
}

$lines = explode("\n", $m3u_content);
$new_m3u = "#EXTM3U\n";
$is_matching = false;

foreach ($lines as $line) {
    $trimmed = trim($line);

    if (strpos($trimmed, '#EXTINF:') === 0) {
        // group-title="Toffee" চেক করা
        if (preg_match('/group-title=["\']?([^"\',]+)["\']?/i', $trimmed, $matches)) {
            if (strcasecmp(trim($matches[1]), $target_category) === 0) {
                $is_matching = true;
                $new_m3u .= $trimmed . "\n";
            } else {
                $is_matching = false;
            }
        } else {
            $is_matching = false;
        }
    } elseif ($is_matching && !empty($trimmed)) {
        $new_m3u .= $trimmed . "\n";
        $is_matching = false;
    }
}

file_put_contents($output_file, $new_m3u);
echo "Playlist updated successfully!\n";
