<?php

namespace GregorJ\Mattersend\Commands;

use GregorJ\Mattersend\Avatars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GregorJ\Mattersend\Commands\AvatarSearch
 *
 * DESCRIPTION
 *
 * @package GregorJ\Mattersend\Commands
 * @author  Gregor J.
 * @license MIT
 */
class AvatarSearch extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Search for an avatar.')
            ->setHelp($this->getDescription())
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the avatar to search for.'
            )
            ->addOption(
                'avatars-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'JSON file containing avatars.'
                .' Alternatively the environment variable MATTERSEND_AVATARS will be used.'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchName = $input->getArgument('name');
        $avatars = $this->getAvatars($input);
        $results = $avatars->search($searchName);
        if (empty($results)) {
            $output->writeln('Nothing found!');
            return 1;
        }
        foreach ($results as $avatar) {
            /**
             * @var \GregorJ\Mattersend\Avatar $avatar
             */
            $output->writeln(sprintf('<comment>%s</comment>: %s', $avatar->getDisplayName(), $avatar->getName()));
        }
        return 0;
    }



    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \GregorJ\Mattersend\Avatars
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private function getAvatars(InputInterface $input)
    {
        $source = $this->getAvatarsFile($input);
        if (!is_string($source) && empty(trim($source))) {
            throw new \RuntimeException('No avatars file specified!');
        }
        return new Avatars($source);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return array|bool|false|string|string[]|null
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private function getAvatarsFile(InputInterface $input)
    {
        if ($input->hasOption('avatars-file') && $input->getOption('avatars-file') !== null) {
            return $input->getOption('avatars-file');
        }
        return getenv('MATTERSEND_AVATARS', true);
    }
}
