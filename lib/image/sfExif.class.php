<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfExifTools provides a toolkit for reading and writing exif data
 *
 * @package    Sift
 * @subpackage image
 */
class sfExif
{
    /**
     * Adapter holder
     *
     * @var sfExifAdapter
     */
    protected $adapter;

    /**
     * Catalogue path used for human readable formating of EXIF data
     *
     * @var string
     */
    public static $translationCatalogue = '%SF_SIFT_DATA_DIR%/i18n/catalogues/exif';

    /**
     * Constructs the adapter
     *
     * @param type $adapter
     * @param type $adapterOptions
     */
    public function __construct($adapter = null, $adapterOptions = null)
    {
        if (!$adapter) {
            $adapter = sfConfig::get('sf_image_exif_adapter', 'ExifTool');
        }

        if (!$adapterOptions) {
            $adapterOptions = sfConfig::get('sf_image_exif_adapter_options', array());
        }

        $this->adapter = self::factory($adapter, $adapterOptions);
    }

    /**
     * Returns a sfExifAdapter instance
     *
     * @param string $adapter
     * @param array  $options
     *
     * @return sfExifAdapter
     * @throws InvalidArgumentException
     */
    public static function factory($adapter = null, $options = array())
    {
        $adapterClass = sprintf('sfExifAdapter%s', ucfirst($adapter));
        if (!class_exists($adapterClass)) {
            throw new InvalidArgumentException(sprintf('Exif adapter "%s" not found.', $adapter));
        }

        return new $adapterClass($options);
    }

    /**
     * Returns adapter instance
     *
     * @return sfExifAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Magic method. This allows the calling of execute methods on adapter instance.
     *
     * @method
     * @param string $name the name of the method
     * @param        array Arguments for the method
     *
     * @return mixed
     * @throws BadMethodCallException If method is not present on the adapter side
     */
    public function __call($name, $arguments)
    {
        $callback = array($this->adapter, $name);

        if (!is_callable($callback)) {
            throw new BadMethodCallException(sprintf('Invalid method "%s" on "%s"', $name, get_class($this->adapter)));
        }

        return call_user_func_array($callback, $arguments);
    }

    /**
     * Obtain an array of supported meta data fields.
     *
     * @return array
     */
    public static function getCategories()
    {
        return array(
            'IPTC'      => array(
                'Keywords'         => array('description' => self::__('Image keywords'), 'type' => 'array'),
                'ObjectName'       => array('description' => self::__('Image Title'), 'type' => 'text'),
                'By-line'          => array('description' => self::__('By'), 'type' => 'text'),
                'CopyrightNotice'  => array('description' => self::__('Copyright'), 'type' => 'text'),
                'Caption-Abstract' => array('description' => self::__('Caption'), 'type' => 'text'),
            ),
            'XMP'       => array(
                'Creator'    => array('description' => self::__('Image Creator'), 'type' => 'text'),
                'Rights'     => array('description' => self::__('Rights'), 'type' => 'text'),
                'UsageTerms' => array('description' => self::__('Usage Terms'), 'type' => 'type'),
            ),
            'EXIF'      => array(
                'DateTime'              => array('description' => self::__('Date Photo Modified'), 'type' => 'date'),
                'DateTimeOriginal'      => array('description' => self::__('Date Photo Taken'), 'type' => 'date'),
                'DateTimeDigitized'     => array('description' => self::__('Date Photo Digitized'), 'type' => 'date'),
                'GPSLatitude'           => array('description' => self::__('Latitude'), 'type' => 'gps'),
                'GPSLongitude'          => array('description' => self::__('Longitude'), 'type' => 'gps'),
                'Make'                  => array('description' => self::__('Camera Make'), 'type' => 'text'),
                'Model'                 => array('description' => self::__('Camera Model'), 'type' => 'text'),
                'Software'              => array('description' => self::__('Software Version'), 'type' => 'text'),
                'ImageType'             => array('description' => self::__('Photo Type'), 'type' => 'text'),
                'ImageDescription'      => array('description' => self::__('Photo Description'), 'type' => 'text'),
                'FileSize'              => array('description' => self::__('File Size'), 'type' => 'number'),
                'ExifImageWidth'        => array('description' => self::__('Width'), 'type' => 'number'),
                'ExifImageLength'       => array('description' => self::__('Height'), 'type' => 'number'),
                'XResolution'           => array('description' => self::__('X Resolution'), 'type' => 'number'),
                'YResolution'           => array('description' => self::__('Y Resolution'), 'type' => 'number'),
                'ResolutionUnit'        => array('description' => self::__('Resolution Unit'), 'type' => 'text'),
                'ShutterSpeedValue'     => array('description' => self::__('Shutter Speed'), 'type' => 'number'),
                'ExposureTime'          => array('description' => self::__('Exposure'), 'type' => 'number'),
                'FocalLength'           => array('description' => self::__('Focal Length'), 'type' => 'number'),
                'FocalLengthIn35mmFilm' => array(
                    'description' => self::__('Focal Length (35mm equiv)'),
                    'type'        => 'number'
                ),
                'ApertureValue'         => array('description' => self::__('Aperture'), 'type' => 'number'),
                'FNumber'               => array('description' => self::__('F-Number'), 'type' => 'number'),
                'ISOSpeedRatings'       => array('description' => self::__('ISO Setting'), 'type' => 'number'),
                'ExposureBiasValue'     => array('description' => self::__('Exposure Bias'), 'type' => 'number'),
                'ExposureMode'          => array('description' => self::__('Exposure Mode'), 'type' => 'number'),
                'ExposureProgram'       => array('description' => self::__('Exposure Program'), 'type' => 'number'),
                'MeteringMode'          => array('description' => self::__('Metering Mode'), 'type' => 'number'),
                'Flash'                 => array('description' => self::__('Flash Setting'), 'type' => 'number'),
                'UserComment'           => array('description' => self::__('User Comment'), 'type' => 'text'),
                'ColorSpace'            => array('description' => self::__('Color Space'), 'type' => 'number'),
                'SensingMethod'         => array('description' => self::__('Sensing Method'), 'type' => 'number'),
                'WhiteBalance'          => array('description' => self::__('White Balance'), 'type' => 'number'),
                'Orientation'           => array('description' => self::__('Camera Orientation'), 'type' => 'number'),
                'Copyright'             => array('description' => self::__('Copyright'), 'type' => 'text'),
                'Artist'                => array('description' => self::__('Artist'), 'type' => 'text'),
                'LightSource'           => array('description' => self::__('Light source'), 'type' => 'number'),
                'ImageStabalization'    => array('description' => self::__('Image Stabilization'), 'type' => 'text'),
                'SceneCaptureType'      => array('description' => self::__('Scene Type'), 'type' => 'number'),
            ),
            'COMPOSITE' => array(
                'LensID'   => array('description' => self::__('Lens Id'), 'type' => 'text'),
                'Lens'     => array('description' => self::__('Lens'), 'type' => 'text'),
                'Aperture' => array('description' => self::__('Aperture'), 'type' => 'text'),
                'DOF'      => array('description' => self::__('Depth of Field'), 'type' => 'text'),
                'FOV'      => array('description' => self::__('Field of View'), 'type' => 'text')
            )
        );
    }

    /**
     * Return a flattened array of supported metadata fields.
     *
     * @param $driver
     *
     * @return unknown_type
     */
    public static function getFields($driver = null, $description_only = false)
    {
        if (!is_null($driver) && is_array($driver)) {
            $driver = self::factory($driver[0], $driver[1]);
        }

        if ($driver instanceof sfExifAdapter) {
            $supported = $driver->supportedCategories();
        } else {
            $supported = array('XMP', 'IPTC', 'EXIF');
        }

        $categories = self::getCategories();
        $flattened = array();

        foreach ($supported as $category) {
            $flattened = array_merge($flattened, $categories[$category]);
        }

        if ($description_only) {
            foreach ($flattened as $key => $data) {
                $return[$key] = $data['description'];
            }

            return $return;
        }

        ksort($flattened);

        return $flattened;
    }

    /**
     * Translates the message
     *
     * @param string $message
     * @param array  $params
     *
     * @return string
     */
    public static function __($message, $params = array())
    {
        if (function_exists('__')) {
            return __($message, $params, self::$translationCatalogue);
        }

        if (empty($params)) {
            $params = array();
        }

        // replace object with strings
        foreach ($params as $key => $value) {
            if (is_object($value) && method_exists($value, '__toString')) {
                $params[$key] = $value->__toString();
            }
        }

        return strtr($message, $params);
    }

    /**
     * More human friendly exposure formatting.
     *
     * @param
     */
    protected static function formatExposure($data)
    {
        if ($data > 0) {
            if ($data > 1) {
                return self::__('%duration% sec', array('%duration%' => sprintf('%d', round($data, 2))));
            } else {
                $n = $d = 0;
                self::convertToFraction($data, $n, $d);
                if ($n <> 1) {
                    return self::__('%duration% sec', array('%duration%' => sprintf("%4f", $n / $d)));
                }

                return self::__('%n% / %d% sec', array('%n%' => sprintf('%s', $n), '%d%' => sprintf('%s', $d)));
            }
        } else {
            return __('Bulb', array(), self::$translationCatalogue);
        }
    }

    /**
     * Converts a floating point number into a fraction.
     * Many thanks to Matthieu Froment for this code.
     *
     * (Ported from the Exifer library).
     */
    protected static function convertToFraction($v, &$n, &$d)
    {
        $MaxTerms = 15; // Limit to prevent infinite loop
        $MinDivisor = 0.000001; // Limit to prevent divide by zero
        $MaxError = 0.00000001; // How close is enough
        // Initialize fraction being converted
        $f = $v;

        // Initialize fractions with 1/0, 0/1
        $n_un = 1;
        $d_un = 0;
        $n_deux = 0;
        $d_deux = 1;

        for ($i = 0; $i < $MaxTerms; $i++) {
            $a = floor($f); // Get next term
            $f = $f - $a; // Get new divisor
            $n = $n_un * $a + $n_deux; // Calculate new fraction
            $d = $d_un * $a + $d_deux;
            $n_deux = $n_un; // Save last two fractions
            $d_deux = $d_un;
            $n_un = $n;
            $d_un = $d;

            // Quit if dividing by zero
            if ($f < $MinDivisor) {
                break;
            }
            if (abs($v - $n / $d) < $MaxError) {
                break;
            }

            // reciprocal
            $f = 1 / $f;
        }
    }

    /**
     * Convert an exif field into human-readable form.
     * Some of these cases are ported from the Exifer library, others were
     * changed from their implementation where the EXIF standard dictated
     * different behaviour.
     *
     * @param string $field The name of the field to translate.
     * @param string $data  The data value to translate.
     *
     * @return string  The converted data.
     */
    public static function getHumanReadable($field, $data)
    {
        switch ($field) {
            case 'ExposureMode':
                switch ($data) {
                    case 0:
                        return self::__("Auto exposure");
                    case 1:
                        return self::__("Manual exposure");
                    case 2:
                        return self::__("Auto bracket");
                    default:
                        return self::__("Unknown");
                }

            case 'ExposureProgram':
                switch ($data) {
                    case 1:
                        return self::__("Manual");
                    case 2:
                        return self::__("Normal Program");
                    case 3:
                        return self::__("Aperture Priority");
                    case 4:
                        return self::__("Shutter Priority");
                    case 5:
                        return self::__("Creative");
                    case 6:
                        return self::__("Action");
                    case 7:
                        return self::__("Portrait");
                    case 8:
                        return self::__("Landscape");
                    default:
                        return self::__("Unknown");
                }

            case 'XResolution':
            case 'YResolution':
                if (strpos($data, '/') !== false) {
                    list($n, $d) = explode('/', $data, 2);

                    return self::__('%resulution% dots per unit', array('%resolution%' => sprintf('%d', $n)));
                }

                return self::__('%resulution% per unit', array('%resolution%' => sprintf('%d', $data)));

            case 'ResolutionUnit':
                switch ($data) {
                    case 1:
                        return self::__("Pixels");
                    case 2:
                        return self::__("Inch");
                    case 3:
                        return self::__("Centimeter");
                    default:
                        return self::__("Unknown");
                }

            case 'ExifImageWidth':
            case 'ExifImageLength':
                return self::__('%width% pixeld', array('%width%' => sprintf('%d', $data)));

            case 'Orientation':
                switch ($data) {
                    case 1:
                        return sprintf(self::__("Normal (O deg)"));
                    case 2:
                        return sprintf(self::__("Mirrored"));
                    case 3:
                        return sprintf(self::__("Upsidedown"));
                    case 4:
                        return sprintf(self::__("Upsidedown Mirrored"));
                    case 5:
                        return sprintf(self::__("90 deg CW Mirrored"));
                    case 6:
                        return sprintf(self::__("90 deg CCW"));
                    case 7:
                        return sprintf(self::__("90 deg CCW Mirrored"));
                    case 8:
                        return sprintf(self::__("90 deg CW"));
                }
                break;

            case 'ExposureTime':
                if (strpos($data, '/') !== false) {
                    list($n, $d) = explode('/', $data, 2);
                    if ($d == 0) {
                        return;
                    }
                    $data = $n / $d;
                }

                return self::_formatExposure($data);

            case 'ShutterSpeedValue':
                if (strpos($data, '/') !== false) {
                    list($n, $d) = explode('/', $data, 2);
                    if ($d == 0) {
                        return;
                    }
                    $data = $n / $d;
                }
                $data = exp($data * log(2));
                if ($data > 0) {
                    $data = 1 / $data;
                }

                return self::formatExposure($data);

            case 'ApertureValue':
            case 'MaxApertureValue':
                if (strpos($data, '/') !== false) {
                    list($n, $d) = explode('/', $data, 2);
                    if ($d == 0) {
                        return;
                    }
                    $data = $n / $d;
                    $data = exp(($data * log(2)) / 2);

                    // Precision is 1 digit.
                    $data = round($data, 1);
                }

                return 'f/' . $data;

            case 'FocalLength':
                if (strpos($data, '/') !== false) {
                    list($n, $d) = explode('/', $data, 2);
                    if ($d == 0) {
                        return;
                    }

                    return self::__('%focal_length% mm', array('%focal_length%' => sprintf('%d', round($n / $d))));
                }

                return self::__('%focal_length% mm', array('%focal_length%' => sprintf('%d', $data)));

            case 'FNumber':
                if (strpos($data, '/') !== false) {
                    list($n, $d) = explode('/', $data, 2);
                    if ($d != 0) {
                        return 'f/' . round($n / $d, 1);
                    }
                }

                return 'f/' . $data;

            case 'ExposureBiasValue':
                if (strpos($data, '/') !== false) {
                    list($n, $d) = explode('/', $data, 2);
                    if ($n == 0) {
                        return '0 EV';
                    }
                }

                return $data . ' EV';

            case 'MeteringMode':
                switch ($data) {
                    case 0:
                        return self::__("Unknown");
                    case 1:
                        return self::__("Average");
                    case 2:
                        return self::__("Center Weighted Average");
                    case 3:
                        return self::__("Spot");
                    case 4:
                        return self::__("Multi-Spot");
                    case 5:
                        return self::__("Multi-Segment");
                    case 6:
                        return self::__("Partial");
                    case 255:
                        return self::__("Other");
                    default:
                        return self::__('Uknown: %data%', array('%data%' => $data));
                }
                break;

            case 'LightSource':
                switch ($data) {
                    case 1:
                        return self::__("Daylight");
                    case 2:
                        return self::__("Fluorescent");
                    case 3:
                        return self::__("Tungsten");
                    case 4:
                        return self::__("Flash");
                    case 9:
                        return self::__("Fine weather");
                    case 10:
                        return self::__("Cloudy weather");
                    case 11:
                        return self::__("Shade");
                    case 12:
                        return self::__("Daylight fluorescent");
                    case 13:
                        return self::__("Day white fluorescent");
                    case 14:
                        return self::__("Cool white fluorescent");
                    case 15:
                        return self::__("White fluorescent");
                    case 17:
                        return self::__("Standard light A");
                    case 18:
                        return self::__("Standard light B");
                    case 19:
                        return self::__("Standard light C");
                    case 20:
                        return 'D55';
                    case 21:
                        return 'D65';
                    case 22:
                        return 'D75';
                    case 23:
                        return 'D50';
                    case 24:
                        return self::__("ISO studio tungsten");
                    case 255:
                        return self::__("other light source");
                    default:
                        return self::__("Unknown");
                }

            case 'WhiteBalance':
                switch ($data) {
                    case 0:
                        return self::__("Auto");
                    case 1:
                        return self::__("Manual");
                    default:
                        self::__("Unknown");
                }
                break;

            case 'FocalLengthIn35mmFilm':
                return $data . ' mm';

            case 'Flash':
                switch ($data) {
                    case 0:
                        return self::__("No Flash");
                    case 1:
                        return self::__("Flash");
                    case 5:
                        return self::__("Flash, strobe return light not detected");
                    case 7:
                        return self::__("Flash, strobe return light detected");
                    case 9:
                        return self::__("Compulsory Flash");
                    case 13:
                        return self::__("Compulsory Flash, Return light not detected");
                    case 15:
                        return self::__("Compulsory Flash, Return light detected");
                    case 16:
                        return self::__("No Flash");
                    case 24:
                        return self::__("No Flash");
                    case 25:
                        return self::__("Flash, Auto-Mode");
                    case 29:
                        return self::__("Flash, Auto-Mode, Return light not detected");
                    case 31:
                        return self::__("Flash, Auto-Mode, Return light detected");
                    case 32:
                        return self::__("No Flash");
                    case 65:
                        return self::__("Red Eye");
                    case 69:
                        return self::__("Red Eye, Return light not detected");
                    case 71:
                        return self::__("Red Eye, Return light detected");
                    case 73:
                        return self::__("Red Eye, Compulsory Flash");
                    case 77:
                        return self::__("Red Eye, Compulsory Flash, Return light not detected");
                    case 79:
                        return self::__("Red Eye, Compulsory Flash, Return light detected");
                    case 89:
                        return self::__("Red Eye, Auto-Mode");
                    case 93:
                        return self::__("Red Eye, Auto-Mode, Return light not detected");
                    case 95:
                        return self::__("Red Eye, Auto-Mode, Return light detected");
                }
                break;

            case 'FileSize':
                if ($data <= 0) {
                    return '0 Bytes';
                }
                $s = array('B', 'kB', 'MB', 'GB');
                $e = floor(log($data, 1024));

                return round($data / pow(1024, $e), 2) . ' ' . $s[$e];

            case 'SensingMethod':
                switch ($data) {
                    case 1:
                        return self::__("Not defined");
                    case 2:
                        return self::__("One Chip Color Area Sensor");
                    case 3:
                        return self::__("Two Chip Color Area Sensor");
                    case 4:
                        return self::__("Three Chip Color Area Sensor");
                    case 5:
                        return self::__("Color Sequential Area Sensor");
                    case 7:
                        return self::__("Trilinear Sensor");
                    case 8:
                        return self::__("Color Sequential Linear Sensor");
                    default:
                        return self::__("Unknown");
                }

            case 'ColorSpace':
                switch ($data) {
                    case 1:
                        return self::__("sRGB");
                    default:
                        return self::__("Uncalibrated");
                }

            case 'SceneCaptureType':
                switch ($data) {
                    case 0:
                        return self::__("Standard");
                    case 1:
                        return self::__("Landscape");
                    case 2:
                        return self::__("Portrait");
                    case 3:
                        return self::__("Night Scene");
                    default:
                        return self::__("Unknown");
                }

            case 'DateTime':
            case 'DateTimeOriginal':
            case 'DateTimeDigitized':

                // FIXME: use i18n!
                return date('m/d/Y H:i:s O', $data);

            case 'UserComment':
                //@TODO: the first 8 bytes of this field contain the charset used
                //       to encode the comment. Either ASCII, JIS, UNICODE, or
                //       UNDEFINED. Should probably either convert to a known charset
                //       here and let the calling code deal with it, or allow this
                //       method to take an optional charset to convert to
                $data = trim(substr($data, 7));

            default:
                return !empty($data) ? $data : '---';
        }
    }

}
