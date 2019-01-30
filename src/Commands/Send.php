<?php
/**
 * File src/Commands/Send.php
 *
 * Command to send messages to Mattermost.
 *
 * @package mattersend
 * @author  Gregor J.
 * @license MIT
 */

namespace GregorJ\Mattersend\Commands;

use GregorJ\Mattersend\Avatars;
use GregorJ\Mattersend\MattermostClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThibaudDauce\Mattermost\Message;

/**
 * Class GregorJ\Mattersend\Commands\Send
 *
 * Command to send messages to Mattermost.
 *
 * @package GregorJ\Mattersend\Commands
 * @author  Gregor J.
 * @license MIT
 */
class Send extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription(
                'Send messages to mattersend using webhooks.'
            )
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'The message to send to Mattermost.'
            )
            ->addOption(
                'avatar',
                'a',
                InputOption::VALUE_REQUIRED,
                'Sender avatar.'
            )
            ->addOption(
                'sender',
                's',
                InputOption::VALUE_REQUIRED,
                'Sender name.'
            )
            ->addOption(
                'channel',
                'c',
                InputOption::VALUE_REQUIRED,
                'Name of the channel to send the message to. @username are channels too.'
            )
            ->addOption(
                'webhook',
                'w',
                InputOption::VALUE_REQUIRED,
                'Mattermost webhook to send the message to.'
                .' Alternatively the environment variable MATTERSEND_WEBHOOK will be used.'
            )
            ->addOption(
                'avatars-file',
                null,
                InputOption::VALUE_REQUIRED,
                'JSON file containing avatars.'
                .' Alternatively the environment variable MATTERSEND_AVATARS will be used.'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void|null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException-
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webhook = null;
        if ($input->hasOption('webhook')) {
            $webhook = $input->getOption('webhook');
        }

        if ($output->isVerbose()) {
            $output->writeln(sprintf(
                '<comment>Message:</comment> %s',
                $input->getArgument('message')
            ));
        }

        $message = (new Message())
            ->text($input->getArgument('message'));

        if ($input->getOption('sender') !== null) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf("<comment>Sender:</comment> '%s'", $input->getOption('sender')));
            }
            $message->username($input->getOption('sender'));
        }

        if ($input->getOption('avatar') !== null) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf("<comment>Avatar:</comment> '%s'", $input->getOption('avatar')));
            }
            $avatar = $this->getAvatar($input);
            $message->iconUrl($avatar->getImageUrl());
            if (empty($message->username)) {
                $message->username($avatar->getDisplayName());
            }
        }

        if ($input->getOption('channel') !== null) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf("<comment>Channel:</comment> '%s'", $input->getOption('channel')));
            }
            $message->channel($input->getOption('channel'));
        }

        $mattermost = new MattermostClient($webhook);
        $mattermost->send($message);

        if ($output->isVerbose()) {
            $output->writeln('Sent!');
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \GregorJ\Mattersend\Avatar
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private function getAvatar(InputInterface $input)
    {
        $avatars = $this->getAvatars($input);
        $avatarName = $input->getOption('avatar');
        if (!$avatars->has($avatarName)) {
            throw new \RuntimeException(sprintf(
                'Avatar \'%s\' not found',
                $avatarName
            ));
        }
        return $avatars->get($avatarName);
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
