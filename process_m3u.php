<?php

$source_url = "https://alixbd.com/playlistconfig/playlist.m3u";
$new_category = "Toffee";
$output_file = "toffee_playlist.m3u";

// M3U প্লেলিস্ট ডাউনলোড করা
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$m3u_content = curl_exec($ch);
curl_close($ch);

if (!$m3u_content) {
    die("Error: M3U playlist download failed.\n");
}

$lines = explode("\n", $m3u_content);
$new_m3u = "#EXTM3U\n";

foreach ($lines as $line) {
    $trimmed = trim($line);

    if (empty($trimmed)) {
        continue;
    }

    if (strpos($trimmed, '#EXTINF:') === 0) {
        // বিদ্যমান group-title থাকলে তা Toffee দিয়ে রিপ্লেস করা
        if (preg_match('/group-title=["\']?[^"\',]+["\']?/i', $trimmed)) {
            $modified_line = preg_replace('/group-title=["\']?[^"\',]+["\']?/i', 'group-title="' . $new_category . '"', $trimmed);
        } else {
            // group-title না থাকলে নতুন করে যোগ করা
            $modified_line = str_replace('#EXTINF:-1', '#EXTINF:-1 group-title="' . $new_category . '"', $trimmed);
            if ($modified_line === $trimmed) {
                $modified_line = preg_replace('/#EXTINF:([-\d]+)/', '#EXTINF:$1 group-title="' . $new_category . '"', $trimmed);
            }
        }
        $new_m3u .= $modified_line . "\n";
    } else if (strpos($trimmed, '#') !== 0) {
        // স্ট্রিম URL লাইন
        $new_m3u .= $trimmed . "\n";
    } else {
        // অন্যান্য ট্যাগ বা হেডারের জন্য (যেমন #EXTVLCOPT ইত্যাদি)
        if (strpos($trimmed, '#EXTM3U') === false) {
            $new_m3u .= $trimmed . "\n";
        }
    }
}

file_put_contents($output_file, $new_m3u);
echo "All channels successfully grouped under '$new_category' category!\n";
