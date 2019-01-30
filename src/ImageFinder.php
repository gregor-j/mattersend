<?php

namespace GregorJ\Mattersend;

use Symfony\Component\Finder\Finder;

/**
 * Class JohamG\MattermostAvatars\ImageFinder
 *
 * Utilizes the symfony finder to retrieve all images from a directory.
 *
 * @package JohamG\MattermostAvatars
 * @author  Gregor J.
 * @license MIT
 */
class ImageFinder
{
    /**
     * @var \Symfony\Component\Finder\Finder
     */
    private $finder;

    private static $extensions = [
        '*.png',
        '*.gif',
        '*.jpg'
    ];

    /**
     * ImageFinder constructor.
     * @param string $directory
     * @throws \InvalidArgumentException
     */
    public function __construct($directory)
    {
        $this->finder = (new Finder())
            ->in($directory)
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->depth(0)
            ->files()
        ;
        foreach (static::$extensions as $extension) {
            $this->finder->name($extension);
        }
    }

    /**
     * Get a file iterator.
     * @return \Iterator|\Symfony\Component\Finder\SplFileInfo[]
     * @throws \LogicException
     */
    public function iterate()
    {
        return $this->finder->getIterator();
    }
}
