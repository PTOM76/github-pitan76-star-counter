<?php
function getTotalStars($username) {
    $url = "https://api.github.com/users/$username/repos?page=1&per_page=100&sort=updated";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: PHP'));
    
    $response = curl_exec($ch);
    curl_close($ch);

    $repos = json_decode($response, true);

    $stars = 0;
    foreach ($repos as $repo) {
        $stars += $repo['stargazers_count'];
    }

    return $stars;
}

function base64_image($image) {
    ob_start();
    imagepng($image);
    $encoded = ob_get_contents();
    ob_end_clean();
    
    return 'data:image/png;base64,' . base64_encode($encoded);
}

$username = $_GET['user'];
$w = isset($_GET['w']) ? (int) $_GET['w'] : 0;
$h = isset($_GET['h']) ? (int) $_GET['h'] : 0;

if ($w != 0 && $h == 0) {
  $h = ($w / 1080) * 640;
}

$stars = getTotalStars($username);

$image = imagecreatefrompng('background.png');
imagesavealpha($image, TRUE);

$text_color = imagecolorallocate($image, 0, 0, 0);


$font_file = './pv-font.ttf';
$font_size = 160;

$text = sprintf('%03d', $stars);

$c1 = substr($text, -1, 1);
$c2 = substr($text, -2, 1);
$c3 = substr($text, -3, 1);

imagettftext($image, $font_size, 0, 155, 585, $text_color, $font_file, $c3);
imagettftext($image, $font_size, 0, 450, 585, $text_color, $font_file, $c2);
imagettftext($image, $font_size, 0, 750, 585, $text_color, $font_file, $c1);

if ($w != 0 && $h != 0)
  $image2 = imagescale($image, $w, $h);

imagesavealpha($image2, TRUE);

header('Content-Type: image/svg+xml');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=1000');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$encoded = base64_image($image2);

echo <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<svg width="$w" height="$h" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <title>GitHub Pitan76 Star Counter</title>
    <g>
        <image x="0" y="0" width="$w" height="$h" xlink:href="$encoded" />
    </g>
</svg>
EOD;

imagedestroy($image);
imagedestroy($image2);
