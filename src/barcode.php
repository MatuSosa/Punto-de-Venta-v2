<?php
function barcode($file, $text, $size = 20, $orientation = "horizontal", $code_type = "code128", $print = true)
{
    $im = imagecreate($size * strlen($text), $size);

    $background_color = imagecolorallocate($im, 255, 255, 255);
    $bar_color = imagecolorallocate($im, 0, 0, 0);

    $font = __DIR__ . '/font/FreeSansBold.ttf';
    imagettftext($im, 10, 0, 10, $size - 10, $bar_color, $font, $text);

    if ($print) {
        imagepng($im, $file);
    }
    imagedestroy($im);
}

?>
