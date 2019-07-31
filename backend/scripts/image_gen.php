<?php
$texts = array(
  1 => 'Hunter',
  2 => 'Kieran',
  4 => 'Trust'
);
$textsB = array_flip($texts);

foreach ($texts as $text){
  $im = imagecreatefrompng("../../images/logo@2x.png");
  $white = ImageColorAllocate($im, 255, 255, 255);
  $black = ImageColorAllocate($im, 0, 0, 0);

  ImageTTFText($im, 10, -45, 10, 50, $white, '../../webfonts/38454E_1_0.ttf', $text);
  ImageTTFText($im, 10, -45, 10, 70, $white, '../../webfonts/38454E_1_0.ttf', $textsB[$text]);

  ImagePng($im, "images/$text.png");
}