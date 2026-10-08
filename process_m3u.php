<?php

$source_url = "https://alixbd.com/playlistconfig/playlist.m3u";
$target_category = "Toffee";
$output_file = "toffee_playlist.m3u";

// cURL দিয়ে M3U ডাউনলোড
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

// লাইন বাই লাইন ভাগ করা
$lines = explode("\n", $m3u_content);
$new_m3u = "#EXTM3U\n";

for ($i = 0; $i < count($lines); $i++) {
    $line = trim($lines[$i]);

    // #EXTINF লাইন খোঁজা
    if (strpos($line, '#EXTINF:') === 0) {
        
        // group-title="Toffee" বা ক্যাটাগরিতে "Toffee" শব্দটি থাকলে ফিল্টার করা
        $is_toffee = false;

        if (preg_match('/group-title=["\']?([^"\',]+)["\']?/i', $line, $matches)) {
            if (stripos($matches[1], $target_category) !== false) {
                $is_toffee = true;
            }
        } elseif (stripos($line, $target_category) !== false) {
            // যদি group-title ট্যাগ না থাকে কিন্তু লাইনে Toffee শব্দটি থাকে
            $is_toffee = true;
        }

        if ($is_toffee) {
            $new_m3u .= $line . "\n";
            
            // পরবর্তী লাইনগুলোতে থাকা URL বা ট্যাগগুলো ক্যাচ করা
            while (isset($lines[$i + 1])) {
                $next_line = trim($lines[$i + 1]);
                if (empty($next_line)) {
                    $i++;
                    continue;
                }
                // নতুন কোনো #EXTINF শুরু হলে লুপ থামবে
                if (strpos($next_line, '#EXTINF:') === 0) {
                    break;
                }
                $new_m3u .= $next_line . "\n";
                $i++;
            }
        }
    }
}

file_put_contents($output_file, $new_m3u);
echo "Successfully updated $output_file\n";
