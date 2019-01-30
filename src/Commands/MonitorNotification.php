<?php

namespace GregorJ\Mattersend\Commands;

use GregorJ\Mattersend\Avatars;
use GregorJ\Mattersend\MattermostClient;
use GregorJ\Mattersend\StarWarsQuotes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThibaudDauce\Mattermost\Message;

/**
 * Class GregorJ\Mattersend\Commands\MonitorNotification
 *
 * Command to send monitor notifications to Mattermost.
 *
 * @package GregorJ\Mattersend\Commands
 * @author  Gregor J.
 * @license MIT
 */
class MonitorNotification extends Command
{
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setDescription(
                'Send monitor notifications to Mattermost.'
            )
            ->setHelp($this->getDescription())
            ->addArgument(
                'host',
                InputArgument::REQUIRED,
                'Monitored host.'
            )
            ->addArgument(
                'status',
                InputArgument::REQUIRED,
                'Monitor status: success|fail'
            )
            ->addOption(
                'avatar',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Sender avatar of the Mattermost notification.',
                'robot'
            )
            ->addOption(
                'sender',
                's',
                InputOption::VALUE_OPTIONAL,
                'Sender name of the Mattermost notifictation.',
                'Monitor'
            )
            ->addOption(
                'channel',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Name of the channel to send the message to. @username are channels too.'
            )
            ->addOption(
                'star-wars',
                null,
                InputOption::VALUE_NONE,
                'Use quotes from StarWars as messages to Mattermost. Overwrites sender and avatar options.'
            )
            ->addOption(
                'webhook',
                null,
                InputOption::VALUE_OPTIONAL,
                'Mattermost webhook to send the message to.'
                .' Alternatively the environment variable MATTERSEND_WEBHOOK will be used.'
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
     * @return int|void|null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webhook = null;
        if ($input->hasOption('webhook')) {
            $webhook = $input->getOption('webhook');
        }

        $messages = $this->getMessages(
            $this->isSuccess($input->getArgument('status')),
            $input->getArgument('host'),
            $input
        );

        $mattermost = new MattermostClient($webhook);

        foreach ($messages as $message) {
            /**
             * @var $message \ThibaudDauce\Mattermost\Message
             */
            $message->channel($input->getOption('channel'));
            $mattermost->send($message);
            if ($input->getOption('verbose')) {
                $output->writeln(sprintf(
                    '(%s) %s: %s',
                    $message->channel,
                    $message->username,
                    $message->text
                ));
            }
        }
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

    /**
     * Get array of Mattermost messages to send.
     * @param bool $success Monitor success?
     * @param string $host Monitored host.
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return array Mattermost messages.
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private function getMessages($success, $host, InputInterface $input)
    {
        if ($input->getOption('star-wars')) {
            return (new StarWarsQuotes($host, $this->getAvatars($input)))
                ->getQuote($success);
        }
        return [$this->getDefaultMessage($success, $host, $input)];
    }

    /**
     * @param bool $success Monitor success?
     * @param string $host Monitored host.
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \ThibaudDauce\Mattermost\Message
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private function getDefaultMessage($success, $host, InputInterface $input)
    {
        $message = new Message();
        $message->username($input->getOption('sender'));
        if ($success) {
            return $message->text(sprintf(
                'Successfully established a connection to %s',
                $host
            ));
        }
        return $message->text(sprintf(
            'Lost connection to %s',
            $host
        ));
    }

    /**
     * Is the monitor status a success?
     * @param string $status Monitor status.
     * @return bool success?
     * @throws \RuntimeException
     */
    private function isSuccess($status)
    {
        switch ($status) {
            case 'success':
                return true;
            case 'fail':
                return false;
            default:
                throw new \RuntimeException(sprintf(
                    "Invalid monitor status '%s'. Please choose either 'success' or 'fail'.",
                    $status
                ));
        }
    }
}
