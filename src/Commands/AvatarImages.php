<?php

namespace GregorJ\Mattersend\Commands;

use GregorJ\Mattersend\ImageFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class GregorJ\Mattersend\Commands\AvatarImages
 *
 * Retrieve all images from a directory and create a JSON the Avatar class can read.
 *
 * @package JohamG\MattermostAvatars\Commands
 * @author  Gregor J.
 * @license MIT
 */
class AvatarImages extends Command
{
    /**
     * Configure options and parameters.
     */
    protected function configure()
    {
        $this
            ->setDescription('Compile a list of available images.')
            ->setHelp('Compile a list of available images.')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'The source directory to search for images.'
            )
            ->addArgument(
                'output',
                InputArgument::OPTIONAL,
                'Filename to write the JSON into.'
            )
            ->addOption(
                'path-prefix',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Prefix to the file path found.'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \LogicException in case iterate() fails
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = new ImageFinder($input->getArgument('source'));
        $prefix = $this->getPathPrefix($input);
        $images = [];
        foreach ($files->iterate() as $file) {
            $images[] = $this->createAvatar($file, $prefix);
        }
        return $this->write($images, $input->getArgument('output'), $output);
    }

    /**
     * @param array $images
     * @param string $fileName
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    private function write(array $images, $fileName, OutputInterface $output)
    {
        if ($fileName === null || $fileName === '-') {
            $output->writeln(json_encode($images, JSON_PRETTY_PRINT));
            return 0;
        }
        $result = file_put_contents($fileName, json_encode($images));
        return $result === false ? 1 : 0;
    }

    /**
     * Create avatar structure from filename.
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @param string $prefix
     * @return array avatar array
     */
    private function createAvatar(SplFileInfo $file, $prefix)
    {
        $name = $this->removeExtension($file);
        if (preg_match('/(.+) \(([^)]+)\)/', $name, $matches)) {
            return [
                'name' => $matches[1],
                'displayName' => $matches[2],
                'imageUrl' => $this->formatUrl($file, $prefix)
            ];
        }
        return [
            'name' => $name,
            'displayName' => $name,
            'imageUrl' => $this->formatUrl($file, $prefix)
        ];
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @return string|string[]|null
     */
    private function removeExtension(SplFileInfo $file)
    {
        return preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getFilename());
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @param string $prefix
     * @return string
     */
    private function formatUrl(SplFileInfo $file, $prefix)
    {
        return sprintf(
            '%s%s',
            $prefix,
            str_replace(getcwd().DIRECTORY_SEPARATOR, '', $file->getRealPath())
        );
    }

    /**
     * Get the prefix for the image file path.
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return string
     */
    private function getPathPrefix(InputInterface $input)
    {
        $prefix = $input->getOption('path-prefix');
        if ($prefix === null) {
            return '';
        }
        return sprintf('%s%s', rtrim($prefix, '/'), DIRECTORY_SEPARATOR);
    }
}
