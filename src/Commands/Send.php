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
                'Sends messages to mattersend using webhooks.'
            )
            ->setHelp($this->getDescription())
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'The message to send to Mattermost.'
            )
            ->addOption(
                'avatar',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Sender avatar.',
                'robot'
            )
            ->addOption(
                'sender',
                's',
                InputOption::VALUE_OPTIONAL,
                'Sender name.',
                'RoBoT'
            )
            ->addOption(
                'channel',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Name of the channel to send the message to. @username are channels too.'
            )
            ->addOption(
                'webhook',
                'w',
                InputOption::VALUE_OPTIONAL,
                'Mattermost webhook to send the message to.'
                .' Alternatively the environment variable MATTERSEND_WEBHOOK will be used.'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webhook = null;
        if ($input->hasOption('webhook')) {
            $webhook = $input->getOption('webhook');
        }

        $message = (new Message())
            ->text($input->getArgument('message'))
            ->username($input->getOption('sender'));

        if ($input->hasOption('avatar')) {
            $avatar = $input->getOption('avatar');
            if (!Avatars::has($avatar)) {
                throw new \RuntimeException(sprintf(
                    'Avatar \'%s\' not found',
                    $avatar
                ));
            }
            $message->iconUrl(Avatars::get($avatar));
        }

        if ($input->hasOption('channel')) {
            $message->channel($input->getOption('channel'));
        }

        $mattermost = new MattermostClient($webhook);
        $mattermost->send($message);

        if ($input->getOption('verbose')) {
            $output->writeln(sprintf(
                '%s: %s',
                $input->getOption('sender'),
                $input->getArgument('message')
            ));
        }
    }
}
