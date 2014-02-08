<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The sfDataUri class provides a convenient way to access and construct
 * data URIs, but should not be relied upon for enforcing RFC 2397 standards.
 *
 * This class will not:
 *  - Validate the media-type provided/parsed
 *  - Validate the encoded data provided/parsed
 *
 * == Data URI to image
 *
 * <pre>
 * $inputValue = 'data:image/gif;base64,R0lGODlhFwAQAIAAAAAAAP///yH5BAAAAAAA'.
 *               'LAAAAAAXABAAAAI3DIKJp73vHEvgMGsgpuviXUXNJImdGJpqWW3Zc1FfBo'.
 *               'agdsJGu0fk6gMKhzjOrEQz9kbFWFJRAAA7';
 *
 * if($dataUri = sfDataUri::tryParse($inputValue))
 * {
 *   $data = $dataUri->getDecodedData();
 *   if($data !== false)
 *   {
 *    // Create and output image
 *    $image = imagecreatefromstring($data);
 *    if($image !== false)
 *    {
 *      header('Content-Type: image/gif');
 *      imagegif($image);
 *      imagedestroy($image);
 *    }
 *   }
 * }
 *
 * </pre>
 *
 * @link       http://www.flyingtophat.co.uk/blog/27/using-data-uris-in-php
 * @package    Sift
 * @subpackage util
 */
class sfDataUri
{
    /**
     * Regular expression used for decomposition of data URI scheme
     *
     * @var string
     */
    private static $REGEX_URI = '/^data:(.+?){0,1}(?:(?:;(base64)\,){1}|\,)(.+){0,1}$/';

    /**
     * Default type
     */
    const DEFAULT_TYPE = 'text/plain;charset=US-ASCII';

    /**
     * Encoded octets encoding
     */
    const ENCODING_URL_ENCODED_OCTETS = 'encoded_octets';

    /**
     * Base64 encoding
     */
    const ENCODING_BASE64 = 'base64';

    /**
     * Keyword used in the data URI to signify base64 encoding
     *
     * @var string
     */
    const BASE64_KEYWORD = 'base64';

    /**
     * The LITLEN (1024) limits the number of characters which can appear in
     * a single attribute value literal
     */
    const LITLEN = 0;

    /**
     * The ATTSPLEN (2100) limits the sum of all
     * lengths of all attribute value specifications which appear in a tag
     */
    const ATTSPLEN = 1;

    /**
     * The TAGLEN (2100) limits the overall length of a tag
     */
    const TAGLEN = 2;

    /**
     * ATTS_TAG_LIMIT is the length limit allowed for TAGLEN & ATTSPLEN DataURi
     */
    const ATTS_TAG_LIMIT = 2100;

    /**
     * LIT_LIMIT is the length limit allowed for LITLEN DataURi
     */
    const LIT_LIMIT = 1024;

    /**
     * Media type
     *
     * @var string
     */
    protected $mediaType = self::DEFAULT_TYPE;

    /**
     * Encoding
     *
     * @var string
     */
    protected $encoding;

    /**
     * Encoded data
     *
     * @var string
     */
    protected $encodedData;

    /**
     * Instantiates an instance of the sfDataURI class, initialised with the
     * default values defined in RFC 2397. That is the media-type of
     * text/plain;charset=US-ASCII and encoding type of URL encoded octets.
     *
     * @param string  $mediaType
     * @param string  $data     Unencoded data
     * @param integer $encoding Class constant of either
     * @param boolean $strict   Strict mode? In strict mode
     *                          {@link sfDataUri::ENCODING_URL_ENCODED_OCTETS} or
     *                          {@link sfDataUri::ENCODING_BASE64}
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        $mediaType = self::DEFAULT_TYPE,
        $data = '',
        $encoding = self::ENCODING_URL_ENCODED_OCTETS,
        $strict = false,
        $lengthMode = self::TAGLEN
    ) {
        $this->setMediaType($mediaType);
        $this->setData($data, $encoding, $strict, $lengthMode);
    }

    /**
     * Returns the data URI's media-type. If none was provided then in
     * accordance to RFC 2397 it will default to text/plain;charset=US-ASCII
     *
     * @return string The media type
     */
    public function getMediaType()
    {
        return empty($this->mediaType) === false ? $this->mediaType : self::DEFAULT_TYPE;
    }

    /**
     * Sets the media type.
     *
     * @param string $mediaType The media type
     *
     * @return sfDataUri
     */
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    /**
     * Returns the method of encoding used for the data.
     *
     * @return int Class constant of either
     * {@link sfDataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link sfDataUri::ENCODING_BASE64}
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Returns the data in its encoded form.
     *
     * @return string Encoded data
     */
    public function getEncodedData()
    {
        return $this->encodedData;
    }

    /**
     * Sets the encoded data and the encoding scheme used to encode/decode it.
     * Be aware that the data is not validated, so ensure that the correct
     * encoding scheme is provided otherwise the method
     * {@link sfDataUri::tryDecodeData($decodedData)} will fail.
     *
     * @param int    $encoding Class constant of either
     *                         {@link sfDataUri::ENCODING_URL_ENCODED_OCTETS} or
     *                         {@link sfDataUri::ENCODING_BASE64}
     * @param string $data     Data encoded with the encoding scheme provided
     *
     * @throws InvalidArgumentException If the encoding is not valid
     * @return sfDataUri
     */
    public function setEncodedData($encoding, $data)
    {
        $this->assertEncoding($encoding);
        $this->encoding = $encoding;
        $this->encodedData = $data;

        return $this;
    }

    /**
     * Checks the validity of encoding
     *
     * @param integer $encoding The encoding
     *
     * @throws InvalidArgumentException
     */
    protected function assertEncoding($encoding)
    {
        if (!in_array(
            $encoding,
            array(
                self::ENCODING_URL_ENCODED_OCTETS,
                self::ENCODING_BASE64
            ),
            true
        )
        ) {
            throw new InvalidArgumentException(sprintf('Unsupported encoding scheme "%s"', $encoding));
        }
    }

    /**
     * Checks the data length based on the strict mode and length mode
     *
     * @param string  $data       The data
     * @param integer $lengthMode Max allowed data length
     * @param boolean $strict     Check data length
     *
     * @throws InvalidArgumentException If the data is too long
     */
    protected function assertDataLength($data, $lengthMode, $strict)
    {
        if ($strict && $lengthMode === self::LITLEN && strlen($data) > self::LIT_LIMIT) {
            throw new InvalidArgumentException(sprintf(
                'The data is too long (%s chars, max: %s).',
                strlen($data),
                self::LIT_LIMIT
            ));
        } elseif ($strict && strlen($data) > self::ATTS_TAG_LIMIT) {
            throw new InvalidArgumentException(sprintf(
                'The data is too long (%s chars, max: %s).',
                strlen($data),
                self::ATTS_TAG_LIMIT
            ));
        }
    }

    /**
     * Sets the data for the data URI, which it stores in encoded form using
     * the encoding scheme provided.
     *
     * @param string  $data     Data to encode then store
     * @param integer $encoding Class constant of either
     *                          {@link sfDataUri::ENCODING_URL_ENCODED_OCTETS} or
     *                          {@link sfDataUri::ENCODING_BASE64}
     *
     * @throws InvalidArgumentException If the encoding is invalid
     * @return sfDataUri
     */
    public function setData(
        $data,
        $encoding = self::ENCODING_URL_ENCODED_OCTETS,
        $strict = false,
        $lengthMode = self::TAGLEN
    ) {
        $this->assertDataLength($data, $strict, $lengthMode);
        $this->assertEncoding($encoding);
        $this->encoding = $encoding;

        switch ($encoding) {
            case self::ENCODING_URL_ENCODED_OCTETS:
                $this->encodedData = rawurlencode($data);
                break;

            case self::ENCODING_BASE64:
                $this->encodedData = base64_encode($data);
                break;
        }

        return $this;
    }

    /**
     * Tries to decode the URI's data using the encoding scheme set.
     *
     * @param null $decodedData Stores the decoded data
     *
     * @return boolean <code>true</code> if data was output, else <code>false</code>
     */
    public function getDecodedData()
    {
        switch ($this->getEncoding()) {
            case self::ENCODING_URL_ENCODED_OCTETS:
                return rawurldecode($this->getEncodedData());
                break;

            case self::ENCODING_BASE64:
                $b64Decoded = base64_decode($this->getEncodedData(), true);
                if ($b64Decoded !== false) {
                    return $b64Decoded;
                }
                break;

            default:
                // NOP
                break;
        }

        return false;
    }

    /**
     * Generates a data URI string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        $output = 'data:';

        if (($this->getMediaType() !== self::DEFAULT_TYPE)
            || ($this->getEncoding() !== self::ENCODING_URL_ENCODED_OCTETS)
        ) {
            $output .= $this->getMediaType();

            if ($this->getEncoding() === self::ENCODING_BASE64) {
                $output .= ';' . self::BASE64_KEYWORD;
            }
        }

        $output .= ',' . $this->getEncodedData();

        return $output;
    }

    /**
     * __toString magic method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Determines whether a string is data URI with the components necessary for
     * it to be parsed by the {@link sfDataUri::tryParse($uri)} method.
     *
     * @param string $string Data URI
     *
     * @return boolean <code>true</code> if possible to parse, else <code>false</code>
     */
    public static function isParsable($dataUriString)
    {
        return (preg_match(self::$REGEX_URI, $dataUriString) === 1);
    }

    /**
     * Parses a string data URI into an instance of a sfDataUri object.
     *
     * @param string $dataUriString Data URI to be parsed
     *
     * @return boolean false|sfDataUri False when the parsing
     * @throws sfParseException If the dataUri could not be parsed
     */
    public static function tryParse(
        $dataUriString,
        $throwException = false,
        $strict = false,
        $lengthMode = self::TAGLEN
    ) {
        if (!self::isParsable($dataUriString)) {
            if ($throwException) {
                throw new sfParseException(sprintf(
                    'The data uri "%s" could not be parsed',
                    strlen($dataUriString) > 128 ? substr($dataUriString, 0, 128) : $dataUriString
                ));
            }

            return false;
        }

        preg_match_all(self::$REGEX_URI, $dataUriString, $matches, PREG_SET_ORDER);
        $mediatype = isset($matches[0][1]) ? $matches[0][1] : self::DEFAULT_TYPE;
        $matchedEncoding = isset($matches[0][2]) ? $matches[0][2] : '';
        $encoding = (strtolower($matchedEncoding) === self::BASE64_KEYWORD) ? self::ENCODING_BASE64
            : self::ENCODING_URL_ENCODED_OCTETS;
        $data = isset($matches[0][3]) ? $matches[0][3] : '';

        return new sfDataUri($mediatype, $data, $encoding, $strict, $lengthMode);
    }

}
