<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Color utility and conversion
 *
 * Represents a color value, and converts between RGB/HSV/XYZ/Lab
 *
 * Example:
 * $color = new sfColor(0xFFFFFF);
 *
 * @see http://www.lateralcode.com/color-manager/
 * @package Sift
 * @subpackage color
 */
class sfColor
{
  /**
   * Map of color names
   *
   * @var array
   */
  protected static $namedColors = array(
      'aliceblue' => 15792383,
      'antiquewhite' => 16444375,
      'aqua' => 65535,
      'aquamarine' => 8388564,
      'azure' => 15794175,
      'beige' => 16119260,
      'bisque' => 16770244, 'black' => 0, 'blanchedalmond' => 16772045,
      'blue' => 255, 'blueviolet' => 9055202, 'brown' => 10824234,
      'burlywood' => 14596231, 'cadetblue' => 6266528, 'chartreuse' => 838835,
      'chocolate' => 13789470, 'coral' => 16744272, 'cornflowerblue' => 6591981,
      'cornsilk' => 16775388, 'crimson' => 14423100, 'cyan' => 65535,
      'darkblue' => 139, 'darkcyan' => 35723, 'darkgoldenrod' => 12092939,
      'darkgray' => 11119017, 'darkgreen' => 25600, 'darkkhaki' => 12433259,
      'darkmagenta' => 9109643, 'darkolivegreen' => 5597999, 'darkorange' => 16747520,
      'darkorchid' => 10040012, 'darkred' => 9109504, 'darksalmon' => 15308410,
      'darkseagreen' => 941991, 'darkslateblue' => 4734347, 'darkslategray' => 3100495,
      'darkturquoise' => 52945, 'darkviolet' => 9699539, 'deeppink' => 16716947,
      'deepskyblue' => 49151, 'dimgray' => 6908265, 'dodgerblue' => 2003199,
      'feldspar' => 13734517, 'firebrick' => 11674146, 'floralwhite' => 16775920,
      'forestgreen' => 2263842, 'fuchsia' => 16711935, 'gainsboro' => 14474460,
      'ghostwhite' => 16316671, 'gold' => 16766720, 'goldenrod' => 14329120,
      'gray' => 8421504, 'green' => 32768, 'greenyellow' => 11403055,
      'honeydew' => 15794160, 'hotpink' => 16738740, 'indianred ' => 13458524,
      'indigo ' => 4915330, 'ivory' => 16777200, 'khaki' => 15787660,
      'lavender' => 15132410, 'lavenderblush' => 16773365, 'lawngreen' => 8190976,
      'lemonchiffon' => 16775885, 'lightblue' => 11393254, 'lightcoral' => 15761536,
      'lightcyan' => 14745599, 'lightgoldenrodyellow' => 16448210, 'lightgrey' => 13882323,
      'lightgreen' => 9498256, 'lightpink' => 16758465, 'lightsalmon' => 16752762,
      'lightseagreen' => 2142890, 'lightskyblue' => 8900346, 'lightslateblue' => 8679679,
      'lightslategray' => 7833753, 'lightsteelblue' => 11584734, 'lightyellow' => 16777184,
      'lime' => 65280, 'limegreen' => 3329330, 'linen' => 16445670, 'magenta' => 16711935,
      'maroon' => 8388608, 'mediumaquamarine' => 6737322, 'mediumblue' => 205,
      'mediumorchid' => 12211667, 'mediumpurple' => 9662680, 'mediumseagreen' => 3978097,
      'mediumslateblue' => 8087790, 'mediumspringgreen' => 64154, 'mediumturquoise' => 4772300,
      'mediumvioletred' => 13047173, 'midnightblue' => 1644912, 'mintcream' => 16121850,
      'mistyrose' => 16770273, 'moccasin' => 16770229, 'navajowhite' => 16768685,
      'navy' => 128, 'oldlace' => 16643558, 'olive' => 8421376,
      'olivedrab' => 7048739, 'orange' => 16753920, 'orangered' => 16729344,
      'orchid' => 14315734, 'palegoldenrod' => 15657130, 'palegreen' => 10025880,
      'paleturquoise' => 11529966, 'palevioletred' => 14184595, 'papayawhip' => 16773077,
      'peachpuff' => 16767673, 'peru' => 13468991, 'pink' => 16761035,
      'plum' => 14524637, 'powderblue' => 11591910, 'purple' => 8388736,
      'red' => 16711680, 'rosybrown' => 12357519, 'royalblue' => 4286945,
      'saddlebrown' => 9127187, 'salmon' => 16416882, 'sandybrown' => 16032864,
      'seagreen' => 3050327, 'seashell' => 16774638, 'sienna' => 10506797,
      'silver' => 12632256, 'skyblue' => 8900331, 'slateblue' => 6970061,
      'slategray' => 7372944, 'snow' => 16775930, 'springgreen' => 65407,
      'steelblue' => 4620980, 'tan' => 13808780, 'teal' => 32896,
      'thistle' => 14204888, 'tomato' => 16737095, 'turquoise' => 4251856,
      'violet' => 15631086, 'violetred' => 13639824, 'wheat' => 16113331,
      'white' => 16777215, 'whitesmoke' => 16119285, 'yellow' => 16776960,
      'yellowgreen' => 10145074);

  /**
   * Red mask
   *
   * @var integer
   */
  protected static $maskRed = 0xff0000;

  /**
   * Green mask
   *
   * @var integer
   */
  protected static $maskGreen = 0x00ff00;

  /**
   * Blue mask
   *
   * @var integer
   */
  protected static $maskBlue = 0x0000ff;

  /**
   * The web-safe colors do not all have standard names,
   * but each can be specified by an RGB triplet:
   * each component (red, green, and blue) takes one of the six values
   * from the following table (out of the 256 possible values available
   * for each component in full 24-bit color).
   *
   * @var array
   * @see http://en.wikipedia.org/wiki/Web_colors#Web-safe_colors
   */
  protected static $webSafe = array(0, 51, 102, 153, 204, 255);

  /**
   * @var int
   */
  protected $color = 0;

  /**
   * Initialize object
   *
   * @param int $color An integer color
   */
  public function __construct($color = null)
  {
    if (is_integer($color)) {
      $this->fromInt($color);
    } elseif (is_string($color)) {
      if (isset(self::$namedColors[strtolower($color)])) {
        $this->fromNamedColor($color);
      } else {
        $this->fromHex($color);
      }
    } elseif (is_array($color)) {
      // we assume its red, green, blue index array
      if (isset($color['red'])) {
        $this->fromRgbInt($color['red'], $color['green'], $color['blue']);
      } else {
        $this->fromRgbInt($color[0], $color[1], $color[2]);
      }
    }
  }

  /**
   * Init color from hex value
   *
   * @param string $hexValue
   *
   * @return Color
   */
  public function fromHex($hexValue)
  {
    $this->color = hexdec($hexValue);

    return $this;
  }

  public function fromNamedColor($colorName)
  {
    $colorName = strtolower($colorName);
    if (isset(self::$namedColors[$colorName])) {
      $this->color = self::$namedColors[$colorName];
    }

    return $this;
  }

  /**
   * Init color from integer RGB values
   *
   * @param int $red
   * @param int $green
   * @param int $blue
   *
   * @return Color
   */
  public function fromRgbInt($red, $green, $blue)
  {
    $this->color = (int) (($red << 16) + ($green << 8) + $blue);

    return $this;
  }

  /**
   * Init color from integer RGB values
   *
   * @param int $red
   * @param int $green
   * @param int $blue
   *
   * @return sfColor
   */
  public function fromRgbArray($rgb)
  {
    return $this->fromRgbInt($rgb['red'], $rgb['green'], $rgb['blue']);
  }

  /**
   * Init color from hex RGB values
   *
   * @param string $red
   * @param string $green
   * @param string $blue
   *
   * @return Color
   */
  public function fromRgbHex($red, $green, $blue)
  {
    return $this->fromRgbInt(hexdec($red), hexdec($green), hexdec($blue));
  }

  /**
   * Init color from hex RGB values
   *
   * @param string $rgb
   *
   * @return sfColor
   */
  public function fromRgbHexArray($rgb)
  {
    return $this->fromRgbInt(hexdec($rgb['red']), hexdec($rgb['green']), hexdec($rgb['blue']));
  }

  /**
   * Init color from integer value
   *
   * @param int $intValue
   *
   * @return Color
   */
  public function fromInt($intValue)
  {
    $this->color = $intValue;

    return $this;
  }

  /**
   * Convert color to hex
   *
   * @return string
   */
  public function toHex()
  {
    return dechex($this->color);
  }

  /**
   * Convert color to RGB array (integer values)
   *
   * @return array
   */
  public function toRgbInt()
  {
    return array(
        'red' => ($this->color & self::$maskRed) >> 16,
        'green' => ($this->color & self::$maskGreen) >> 8,
        'blue' => ($this->color & self::$maskBlue)
    );
  }

  /**
   * Convert color to RGB array (hex values)
   *
   * @return array
   */
  public function toRgbHex()
  {
    return array_map('dechex', $this->toRgbInt());
  }

  /**
   * Get Hue/Saturation/Value for the current color
   * (float values, slow but accurate)
   *
   * @return array
   */
  public function toHsvFloat()
  {
    $rgb = $this->toRgbInt();

    $rgbMin = min($rgb);
    $rgbMax = max($rgb);

    $hsv = array(
        'hue' => 0,
        'sat' => 0,
        'val' => $rgbMax
    );

    // If v is 0, color is black
    if ($hsv['val'] == 0) {
      return $hsv;
    }

    // Normalize RGB values to 1
    $rgb['red'] /= $hsv['val'];
    $rgb['green'] /= $hsv['val'];
    $rgb['blue'] /= $hsv['val'];
    $rgbMin = min($rgb);
    $rgbMax = max($rgb);

    // Calculate saturation
    $hsv['sat'] = $rgbMax - $rgbMin;
    if ($hsv['sat'] == 0) {
      $hsv['hue'] = 0;

      return $hsv;
    }

    // Normalize saturation to 1
    $rgb['red'] = ($rgb['red'] - $rgbMin) / ($rgbMax - $rgbMin);
    $rgb['green'] = ($rgb['green'] - $rgbMin) / ($rgbMax - $rgbMin);
    $rgb['blue'] = ($rgb['blue'] - $rgbMin) / ($rgbMax - $rgbMin);
    $rgbMin = min($rgb);
    $rgbMax = max($rgb);

    // Calculate hue
    if ($rgbMax == $rgb['red']) {
      $hsv['hue'] = 0.0 + 60 * ($rgb['green'] - $rgb['blue']);
      if ($hsv['hue'] < 0) {
        $hsv['hue'] += 360;
      }
    } else if ($rgbMax == $rgb['green']) {
      $hsv['hue'] = 120 + (60 * ($rgb['blue'] - $rgb['red']));
    } else {
      $hsv['hue'] = 240 + (60 * ($rgb['red'] - $rgb['green']));
    }

    return $hsv;
  }

  /**
   * Get HSV values for color
   * (integer values from 0-255, fast but less accurate)
   *
   * @return int
   */
  public function toHsvInt()
  {
    $rgb = $this->toRgbInt();

    $rgbMin = min($rgb);
    $rgbMax = max($rgb);

    $hsv = array(
        'hue' => 0,
        'sat' => 0,
        'val' => $rgbMax
    );

    // If value is 0, color is black
    if ($hsv['val'] == 0) {
      return $hsv;
    }

    // Calculate saturation
    $hsv['sat'] = round(255 * ($rgbMax - $rgbMin) / $hsv['val']);
    if ($hsv['sat'] == 0) {
      $hsv['hue'] = 0;

      return $hsv;
    }

    // Calculate hue
    if ($rgbMax == $rgb['red']) {
      $hsv['hue'] = round(0 + 43 * ($rgb['green'] - $rgb['blue']) / ($rgbMax - $rgbMin));
    } else if ($rgbMax == $rgb['green']) {
      $hsv['hue'] = round(85 + 43 * ($rgb['blue'] - $rgb['red']) / ($rgbMax - $rgbMin));
    } else {
      $hsv['hue'] = round(171 + 43 * ($rgb['red'] - $rgb['green']) / ($rgbMax - $rgbMin));
    }
    if ($hsv['hue'] < 0) {
      $hsv['hue'] += 255;
    }

    return $hsv;
  }

  /**
   * Convert color to integer
   *
   * @return int
   */
  public function toInt()
  {
    return $this->color;
  }

  /**
   * Alias of toString()
   *
   * @return string
   */
  public function __toString()
  {
    return $this->toString();
  }

  /**
   * Get color as string
   *
   * @return string
   */
  public function toString()
  {
    $str = (string) $this->toHex();
    if (strlen($str) < 6) {
      $str = str_pad($str, 6, '0', STR_PAD_LEFT);
    }

    return strtolower("#{$str}");
  }

  /**
   * Get the distance between this color and the given color
   *
   * @param Color $color
   *
   * @return int
   */
  public function getDistanceRgbFrom(sfColor $color)
  {
    $rgb1 = $this->toRgbInt();
    $rgb2 = $color->toRgbInt();

    $rDiff = abs($rgb1['red'] - $rgb2['red']);
    $gDiff = abs($rgb1['green'] - $rgb2['green']);
    $bDiff = abs($rgb1['blue'] - $rgb2['blue']);

    // Sum of RGB differences
    $diff = $rDiff + $gDiff + $bDiff;

    return $diff;
  }

  /**
   * Detect if color is grayscale
   *
   * @param int @threshold
   *
   * @return bool
   */
  public function isGrayscale($threshold = 16)
  {
    $rgb = $this->toRgbInt();

    // Get min and max rgb values, then difference between them
    $rgbMin = min($rgb);
    $rgbMax = max($rgb);
    $diff = $rgbMax - $rgbMin;

    return $diff < $threshold;
  }

  /**
   * Get the closest matching color from the given array of colors
   *
   * @param array $colors array of integers or Color objects
   *
   * @return mixed the array key of the matched color
   */
  public function getClosestMatch(array $colors)
  {
    $matchDist = 10000;
    $matchKey = null;
    foreach ($colors as $key => $color) {
      if (false === ($color instanceof sfColor)) {
        $c = new sfColor($color);
      }
      $dist = $this->getDistanceRgbFrom($c);
      if ($dist < $matchDist) {
        $matchDist = $dist;
        $matchKey = $key;
      }
    }

    return $matchKey;
  }

  /**
   * Mixes two colors together with given alpha
   *
   * @param sfColor $color
   * @param integer $alpha
   * @return sfColor
   * @throws InvalidArgumentException*
   */
  public function mix(sfColor $color, $alpha = 50)
  {
    if ($alpha > 100 || $alpha < 0) {
      throw new InvalidArgumentException('Invalid alpha given. Should be: 0 < $alpha < 100');
    }

    $rgb = $this->toRgbInt();
    $red = $rgb['red'];
    $green = $rgb['green'];
    $blue = $rgb['blue'];

    $color = $color->toRgbInt();

    // taken from mootools Color utility class
    // https://github.com/mootools/mootools-more/blob/master/Source/Utilities/Color.js
    $red = round(($red / 100 * (100 - $alpha)) + ($color['red'] / 100 * $alpha));
    $green = round(($green / 100 * (100 - $alpha)) + ($color['green'] / 100 * $alpha));
    $blue = round(($blue / 100 * (100 - $alpha)) + ($color['blue'] / 100 * $alpha));

    return new sfColor(array(
                'red' => $red,
                'green' => $green,
                'blue' => $blue));
  }

  /**
   * Inverts the color
   *
   * @return sfColor
   */
  public function invert()
  {
    $this->color = (~$this->color) & (self::$maskRed + self::$maskGreen + self::$maskBlue);

    return $this;
  }

  public function modifyBrightness($percent)
  {
    $rgb = $this->toRgbInt();
    foreach (array('red', 'green', 'blue') as $i) {
      if ($percent > 0) {
        // Lighter
        $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
      } else {
        // Darker
        $positivePercent = $percent - ($percent * 2);
        $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1 - $positivePercent));
      }

      // In case rounding up causes us to go to 256
      $rgb[$i] = $this->clamp($rgb[$i]);
    }

    $this->fromRgbHexArray($rgb);

    return $this;
  }

  public function lighten($factor)
  {
    return $this->modifyBrightness($factor);
  }

  public function darken($factor)
  {
    return $this->modifyBrightness(-1 * $factor);
  }

  /**
   * Returns color brightness
   *
   * @see http://www.nbdtech.com/Blog/archive/2008/04/27/Calculating-the-Perceived-Brightness-of-a-Color.aspx
   * @return float
   * */
  public function getBrightness()
  {
    $rgb = $this->toRgbInt();

    return sqrt((pow($rgb['red'], 2) * .241) + (pow($rgb['green'], 2) * .691) + (pow($rgb['blue'], 2) * .068));
  }

  /**
   * Calculates the luminance of a color (0-255)
   *
   * @return float
   */
  public function getLuminance()
  {
    $rgb = $this->toRgbInt();

    return(min($rgb['red'], $rgb['green'], $rgb['blue']) + max($rgb['red'], $rgb['green'], $rgb['blue'])) >> 1;
  }

  /**
   * Makes the color "websafe"
   *
   * @return sfColor
   */
  public function makeWebSafe()
  {
    $rgb = $this->toRgbInt();
    foreach ($rgb as $name => $color) {
      if ($color < 0x1a) {
        $color = 0x00;
      } else if ($color < 0x4d) {
        $color = 0x33;
      } else if ($color < 0x80) {
        $color = 0x66;
      } else if ($color < 0xB3) {
        $color = 0x99;
      } else if ($color < 0xE6) {
        $color = 0xCC;
      } else {
        $color = 0xFF;
      }
      $rgb[$name] = $color;
    }

    return $this->fromRgbArray($rgb);
  }

  /**
   * Clamps the value to range 0 - 255
   *
   * @param integer $colorValue
   * @return integer
   */
  private function clamp($colorValue)
  {
    // clamp colorValue in interval [0, 255]
    return max(0, min(255, $colorValue));
  }

}
