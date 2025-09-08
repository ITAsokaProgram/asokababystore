<?php

namespace Firebase\JWT;

use InvalidArgumentException;
use TypeError;

class Key
{
    /**
     * @var string|resource
     */
    private $keyMaterial;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @param string|resource $keyMaterial
     * @param string $algorithm
     */
    public function __construct($keyMaterial, $algorithm)
    {
        if (!is_string($keyMaterial) && !is_resource($keyMaterial)) {
            throw new TypeError('Key material must be a string or resource');
        }

        if (empty($keyMaterial)) {
            throw new InvalidArgumentException('Key material must not be empty');
        }

        if (!is_string($algorithm) || empty($algorithm)) {
            throw new InvalidArgumentException('Algorithm must not be empty and must be a string');
        }

        $this->keyMaterial = $keyMaterial;
        $this->algorithm = $algorithm;
    }

    /**
     * Return the algorithm valid for this key
     *
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @return string|resource
     */
    public function getKeyMaterial()
    {
        return $this->keyMaterial;
    }
}
