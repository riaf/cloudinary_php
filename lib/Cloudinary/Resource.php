<?php
/**
 * Resource.php
 *
 * @author Keisuke SATO <sato@crocos.co.jp>
 * @package cloudinary
 * @license MIT License
 */

namespace Cloudinary;

use Cloudinary\Cloudinary;

/**
 * Resource.
 *
 * @author Keisuke SATO <sato@crocos.co.jp>
 */
class Resource
{
    /**
     * @var string $cloudName
     */
    protected $cloudName;

    /**
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * @var string $apiSecret
     */
    protected $apiSecret;

    /**
     * @var array $parameterMap
     */
    protected $parameterMap = array(
        'overlay' => 'l',
        'density' => 'dn',
        'page' => 'pg',
    );


    /**
     * Constructor
     *
     * @param string $cloudName
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct($cloudName, $apiKey = null, $apiSecret = null)
    {
        $this->cloudName = $cloudName;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Create the images url
     *
     * @param string $name
     * @param array $options
     * @return string
     */
    public function getUrl($name, array $options = array())
    {
        $type = isset($options['type']) ? $options['type'] : 'upload';

        if (in_array($type, array('upload', 'asset')) && (stripos($name, 'http://') === 0 || stripos($name, 'https://') === 0)) {
            return $name;
        } else if ($type === 'fetch' && isset($options['format'])) {
            $options['fetch_format'] = $options['format'];
            unset($options['format']);
        }

        $url = '';

        if (isset($options['secure']) && $options['secure']) {
            $url = 'https://';

            if (isset($options['secure_distribution'])) {
                $url .= $options['secure_distribution'];
            } else {
                $url .= Cloudinary::SHARED_CDN;
            }
        } else {
            $url = 'http://res.cloudinary.com';
        }

        $url = implode('/', $this->_removeEmptyValuesFromArray(array(
            $url,
            $this->cloudName,
            isset($options['resource_type']) ? $options['resource_type'] : 'image',
            $type,
            $this->_convertParameterString($options),
            $this->_normalizeName(
                $name,
                isset($options['format']) ? $options['format'] : null
            ),
        )));

        return $url;
    }

    /**
     * @param array $options
     * @return string
     */
    protected function _convertParameterString(array $options)
    {
        if (empty($options)) {
            return '';
        }

        $stringifiedParameters = array();

        if (isset($options['transformation'])) {
            $transformation = $options['transformation'];
            if (!is_array($transformation)) {
                $transformation = array($transformation);
            }

            if ($this->_isArray($transformation) && is_array($transformation[0])) {
                foreach($transformation as $t) {
                    if (empty($t)) {
                        continue;
                    }

                    $stringifiedParameters[] = $this->_convertTransformationString($t);
                }
            } else {
                $stringifiedParameters[] = $this->_convertTransformationString($transformation);
            }
        }

        $stringifiedParameters[] = $this->_convertTransformationString($options);

        return implode('/', $this->_removeEmptyValuesFromArray($stringifiedParameters));
    }

    /**
     * @param array $options
     * @return string
     */
    protected function _convertTransformationString(array $options)
    {
        if (empty($options)) {
            return '';
        }

        if ($this->_isArray($options)) {
            return 't_' . implode('.', $options);
        }

        if (isset($options['size'])) {
            list($options['width'], $options['height']) = explode('x', $options['size']);
        }

        $params = array();

        ksort($options);

        foreach ($options as $name => $value) {
            // ignore keys
            if (in_array($name, array(
                'type',
                'resource_type',
                'cloud_name',
                'secure',
                'secure_distribution',
                'private_cdn',
                'format',
                'transformation',
                'size',
            ))) {
                continue;
            }

            $key = $name;

            switch ($key) {
                case 'background':
                    if ($value[0] === '#') {
                        $value = 'rgb:' . substr($value, 1);
                    }
                    break;
                case 'effect':
                    if (is_array($value)) {
                        $value = implode(':', $value);
                    }
                    break;
            }

            if (isset($this->parameterMap[$key])) {
                $key = $this->parameterMap[$key];
            } else if (strlen($key) > 1) {
                $key = substr($key, 0, 1);
            }

            if (in_array($key, array('w', 'h')) && (!isset($options['crop']) && !isset($options['overlay']) && !isset($options['underlay']))) {
                continue;
            }

            $params[] = sprintf('%s_%s', $key, $value);
        }

        return implode(',', $params);
    }

    /**
     * @param string $name
     * @param string $format
     * @return string
     */
    protected function _normalizeName($name, $format = null)
    {
        if (!is_null($format)) {
            if (in_array(substr($name, -4), array(
                '.png',
                '.gif',
                '.jpg',
                'jpeg',
            ))) {
                $name = preg_replace('/\.(png|gif|jpg|jpeg)$/', '.' . $format, $name);
            } else {
                $name .= '.' . $format;
            }
        }

        return strtr(rawurlencode($name), array(
            '%21' => '!',
            '%2A' => '*',
            '%27' => '\'',
            '%28' => '(',
            '%29' => ')',
            '%3A' => ':',
            '%2F' => '/',
        ));
    }

    /**
     * @param array $array
     * @return boolean
     */
    protected function _isArray(array $array)
    {
        foreach ($array as $k => $v)
            break;

        return $k === 0;
    }

    /**
     * @param array $array
     * @return array
     */
    protected function _removeEmptyValuesFromArray(array $array)
    {
        return array_diff($array, array(''));
    }
}
