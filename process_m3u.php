<?php

$source_url = "https://alixbd.com/playlistconfig/playlist.m3u";
$target_category = "Toffee";
$output_file = "toffee_playlist.m3u";

// M3U লিঙ্ক থেকে কন্টেন্ট নিয়ে আসা
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$m3u_content = curl_exec($ch);
curl_close($ch);

if (!$m3u_content) {
    die("Failed to fetch M3U playlist.\n");
}

$lines = explode("\n", $m3u_content);
$new_m3u = "#EXTM3U\n";
$is_matching_category = false;

foreach ($lines as $line) {
    $trimmed = trim($line);

    if (strpos($trimmed, '#EXTINF:') === 0) {
        // group-title এর মধ্যে "Toffee" আছে কি না চেক করা
        if (preg_match('/group-title=["\']?([^"\',]+)["\']?/i', $trimmed, $matches)) {
            $category = trim($matches[1]);
            if (strcasecmp($category, $target_category) === 0) {
                $is_matching_category = true;
                $new_m3u .= $trimmed . "\n";
            } else {
                $is_matching_category = false;
            }
        } else {
            $is_matching_category = false;
        }
    } elseif ($is_matching_category && !empty($trimmed)) {
        // ক্যাটাগরি ম্যাচ করলে তার স্ট্রিম URL যোগ করা
        $new_m3u .= $trimmed . "\n";
        $is_matching_category = false; // পরবর্তী এন্ট্রির জন্য রিসেট
    }
}

// নতুন M3U ফাইল সেভ করা
file_put_contents($output_file, $new_m3u);
echo "Successfully generated $output_file for category: $target_category\n";

