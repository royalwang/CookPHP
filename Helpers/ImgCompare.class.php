<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href='http://www.cookphp.org'>CookPHP</a>
 */

namespace Helpers;

/**
 * 图片比较类
 * @author 费尔 <admin@cookphp.org>
 */
class ImgCompare {

    public function compare($a, $b) {
        $i1 = $this->createImage($a);
        $i2 = $this->createImage($b);
        if (!$i1 || !$i2) {
            return false;
        }
        $i1 = $this->resizeImage($i1, $a);
        $i2 = $this->resizeImage($i2, $b);

        imagefilter($i1, IMG_FILTER_GRAYSCALE);
        imagefilter($i2, IMG_FILTER_GRAYSCALE);

        $colorMean1 = $this->colorMeanValue($i1);
        $colorMean2 = $this->colorMeanValue($i2);

        $bits1 = $this->bits($colorMean1);
        $bits2 = $this->bits($colorMean2);

        $hammeringDistance = 100;

        for ($a = 0; $a < 64; $a++) {

            if ($bits1[$a] != $bits2[$a]) {
                $hammeringDistance = $hammeringDistance - 2;
            }
        }

        return $hammeringDistance;
    }

    private function mimeType($i) {
        $mime = getimagesize($i);
        $return = [$mime[0], $mime[1]];
        switch ($mime['mime']) {
            case 'image/jpeg':
                $return[] = 'jpg';
                return $return;
            case 'image/png':
                $return[] = 'png';
                return $return;
            default:
                return false;
        }
    }

    private function createImage($i) {
        $mime = $this->mimeType($i);
        if ($mime[2] == 'jpg') {
            return imagecreatefromjpeg($i);
        } else if ($mime[2] == 'png') {
            return imagecreatefrompng($i);
        } else {
            return false;
        }
    }

    private function resizeImage($i, $source) {
        $mime = $this->mimeType($source);
        $t = imagecreatetruecolor(8, 8);
        $source = $this->createImage($source);
        imagecopyresized($t, $source, 0, 0, 0, 0, 8, 8, $mime[0], $mime[1]);
        return $t;
    }

    private function colorMeanValue($i) {
        $colorList = [];
        $colorSum = 0;
        for ($a = 0; $a < 8; $a++) {

            for ($b = 0; $b < 8; $b++) {

                $rgb = imagecolorat($i, $a, $b);
                $colorList[] = $rgb & 0xFF;
                $colorSum += $rgb & 0xFF;
            }
        }
        return array($colorSum / 64, $colorList);
    }

    private function bits($colorMean) {
        $bits = [];
        foreach ($colorMean[1] as $color) {
            $bits[] = ($color >= $colorMean[0]) ? 1 : 0;
        }
        return $bits;
    }

}
