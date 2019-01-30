<?php
/**
 * File src/MattermostClient.php
 *
 * Mattermost client for mattersend.
 *
 * @package mattersend
 * @author  Gregor J.
 * @license MIT
 */

namespace GregorJ\Mattersend;

use GuzzleHttp\Client;
use ThibaudDauce\Mattermost\Mattermost;
use ThibaudDauce\Mattermost\Message;

/**
 * Class GregorJ\Mattersend\MattermostClient
 *
 * Mattermost client class utilizing ThibaudDauce\Mattermost.
 *
 * @package GregorJ\Mattersend
 * @author  Gregor J.
 * @license MIT
 */
class MattermostClient
{
    /**
     * @var string The Mattermost webhook URL
     */
    private $webhook;

    /**
     * @var array The http client options.
     */
    private $httpOptions = [];

    /**
     * MattermostClient constructor.
     * @param string $webhook The Mattermost webhook to send the message to.
     */
    public function __construct($webhook = null)
    {
        if ($webhook === null) {
            $webhook = getenv('MATTERSEND_WEBHOOK', true);
        }
        if (!is_string($webhook)) {
            throw new \RuntimeException('No webhook defined!');
        }
        $this->webhook = $webhook;
        $proxy = getenv('http_proxy', true);
        if (!empty($proxy)) {
            $this->httpOptions['proxy'] = str_replace('http://', 'tcp://', $proxy);
        }
    }

    /**
     * Send a message to the defined webhook.
     * @param \ThibaudDauce\Mattermost\Message $message
     */
    public function send(Message $message)
    {
        (new Mattermost(new Client($this->httpOptions), $this->webhook))
            ->send($message);
    }
}
