<?php

namespace GregorJ\Mattersend;

/**
 * Class GregorJ\Mattersend\Avatar
 *
 * Avatar properties management class.
 *
 * @package GregorJ\Mattersend
 * @author  Gregor J.
 * @license MIT
 */
class Avatar implements \JsonSerializable
{
    /**
     * @var array Allowed properties of this class.
     */
    private static $properties = [
        'displayName',
        'imageUrl',
        'name'
    ];

    /**
     * @var array Avatar properties.
     */
    private $content;

    /**
     * Avatar constructor.
     * @param array|object $data The avatar data to import either as array or as object.
     * @throws \InvalidArgumentException
     */
    public function __construct($data)
    {
        if (is_array($data)) {
            $this->importFromArray($data);
        } elseif (is_object($data)) {
            $this->importFromObject($data);
        } else {
            throw new \InvalidArgumentException('Expected avatar to either be an object or an array!');
        }
    }

    /**
     * @param array $data
     * @throws \InvalidArgumentException
     */
    private function importFromArray($data)
    {
        foreach (static::$properties as $property) {
            if (!array_key_exists($property, $data)) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing property %s',
                    $property
                ));
            }
            $this->set($property, trim($data[$property]));
        }
    }

    /**
     * @param object $data
     * @throws \InvalidArgumentException
     */
    private function importFromObject($data)
    {
        foreach (static::$properties as $property) {
            if (!property_exists($data, $property)) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing property %s',
                    $property
                ));
            }
            $this->set($property, trim($data->{$property}));
        }
    }

    /**
     * @param string $property
     * @param string $value
     * @throws \InvalidArgumentException
     */
    private function set($property, $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException(sprintf(
                'Empty property %s',
                $property
            ));
        }
        $this->content[$property] = $value;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->content;
    }

    /**
     * Get the avatar descriptive name.
     * @return string
     */
    public function getName()
    {
        return $this->content['name'];
    }

    /**
     * Get the avatar display name.
     * @return string
     */
    public function getDisplayName()
    {
        return $this->content['displayName'];
    }

    /**
     * Get the URL of the image.
     * @return string
     */
    public function getImageUrl()
    {
        return $this->content['imageUrl'];
    }
}
