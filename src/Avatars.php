<?php

namespace GregorJ\Mattersend;

/**
 * Class GregorJ\Mattersend\Avatars
 *
 * Static class to manage avatars in Mattermost.
 *
 * @package GregorJ\Mattersend
 * @author  Gregor J.
 * @license MIT
 */
class Avatars
{
    /**
     * @var array Avatars and their URLs.
     */
    private $avatars;

    /**
     * Avatars constructor.
     * Loads the avatars from a given file.
     * @param string $source The JSON file to read avatar data from.
     * @throws \RuntimeException In case there is a problem with the given JSON file.
     * @throws \InvalidArgumentException In case there is a problem with the structure of the JSON file.
     */
    public function __construct($source)
    {
        $this->importAvatars($this->readFromFile($source));
    }

    /**
     * @param $source
     * @return mixed
     * @throws \RuntimeException
     */
    private function readFromFile($source)
    {
        $content = file_get_contents($source);
        if ($content === false) {
            throw new \RuntimeException(
                'Cannot read given avatars file!'
            );
        }
        $result = json_decode($content);
        if ($result === null) {
            throw new \RuntimeException('Invalid JSON in avatars file!');
        }
        return $result;
    }

    /**
     * Import avatars from an array of avatar data.
     * @param array $avatars
     * @throws \InvalidArgumentException
     */
    private function importAvatars($avatars)
    {
        foreach ($avatars as $avatarData) {
            $avatarObj = new Avatar($avatarData);
            $this->avatars[$avatarObj->getName()] = $avatarObj;
        }
    }

    /**
     * Determine if a certain avatar exists.
     * @param string $name The avatar name.
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->avatars);
    }

    /**
     * Get a certain avatar
     * @param string $name The avatar name.
     * @return \GregorJ\Mattersend\Avatar
     * @throws \RuntimeException
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \RuntimeException(sprintf(
                'Avatar %s not found!',
                $name
            ));
        }
        return $this->avatars[$name];
    }

    /**
     * @param string $name
     * @return array
     * @throws \InvalidArgumentException
     */
    public function search($name)
    {
        $name = strtolower(trim($name));
        if (empty($name)) {
            throw new \InvalidArgumentException(
                'Cannot search for an empty name!'
            );
        }
        return array_filter($this->avatars, function ($key) use ($name) {
            return (strpos(strtolower($key), $name) !== false);
        }, ARRAY_FILTER_USE_KEY);
    }
}
