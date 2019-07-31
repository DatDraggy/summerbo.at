<?php
$texts = array(
  [
    1,
    'Hunter',
    'staff'
  ],
  [
    2,
    'DatDraggy',
    'staff'
  ],
  [
    4,
    'Trust',
    'vip'
  ]
);

foreach ($texts as $text) {
  $im = imagecreatefrompng("source/{$text[2]}_front.png");
  $white = ImageColorAllocate($im, 255, 255, 255);
  $black = ImageColorAllocate($im, 0, 0, 0);

  ImageTTFText($im, 100, 0, 212, 2605, $white, '../../webfonts/TideSans-700Mondo.ttf', $text[1]);
  ImageTTFText($im, 160, 0, 205, 2925, $white, '../../webfonts/TideSans-700Mondo.ttf', '#' . $text[0]);

  ImagePng($im, "images/{$text[0]}.png");
}