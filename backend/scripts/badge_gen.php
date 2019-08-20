<?php
if (!file_exists('badge_files')) {
  mkdir('badge_files');
}

$toGen = array(
  'vip' => false,
  'guest' => true,
  'crew' => false,
  'security' => false
);

$loops = 0;
//for testing, only first 5 badges
if (($handle = fopen("badges.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 50, ",")) !== FALSE && $loops < 5) {
    $sourceFile = "source/{$data[2]}_front.png";
    if (!file_exists($sourceFile)) {
      die('Source ' . $sourceFile . ' not found.');
    }

    //If current type is false, skip
    if (!$toGen[$data[2]]) {
      continue;
    }

    $im = imagecreatefrompng($sourceFile);
    $white = ImageColorAllocate($im, 255, 255, 255);
    $black = ImageColorAllocate($im, 0, 0, 0);

    ImageTTFText($im, 34, 0, 71, 870, $white, '../../webfonts/TideSans-700Mondo.ttf', $data[1]);
    ImageTTFText($im, 60, 0, 66, 973, $white, '../../webfonts/TideSans-700Mondo.ttf', '#' . $data[0]);

    $data[1] = preg_replace('/[^\w\- ]/', '', $data[1]);

    ImagePng($im, "badge_files/{$data[0]}_{$data[1]}_a.png");
    //copy("source/{$data[2]}_back.png", "badge_files/{$data[0]}_{$data[1]}_b.png");
    //$loops += 1;
  }
  fclose($handle);
} else {
  echo 'Error opening file badges.csv';
}

/*$texts = array(
  [
    1,
    'Hunter',
    'staff'
  ],
  [
    2,
    'DatDraggy',
    'guest'
  ],
  [
    4,
    'Trust',
    'vip'
  ]
);

foreach ($texts as $data) {
  $im = imagecreatefrompng("source/{$data[2]}_front.png");
  $white = ImageColorAllocate($im, 255, 255, 255);
  $black = ImageColorAllocate($im, 0, 0, 0);

  ImageTTFText($im, 100, 0, 212, 2605, $white, '../../webfonts/TideSans-700Mondo.ttf', $data[1]);
  ImageTTFText($im, 160, 0, 205, 2925, $white, '../../webfonts/TideSans-700Mondo.ttf', '#' . $data[0]);

  ImagePng($im, "images/{$data[0]}_{$data[1]}_a.png");
  copy("source/{$data[2]}_back.png", "images/{$data[0]}_{$data[1]}_b.png");
}*/
