<?php
/**
 * File src/StarWarsQuotes.php
 *
 * Generate Mattermost messages from StarWars quotes.
 *
 * @package mattersend
 * @author  Gregor J.
 * @license MIT
 */

namespace GregorJ\Mattersend;

use ThibaudDauce\Mattermost\Message;

/**
 * Class GregorJ\Mattersend\StarWarsQuotes
 *
 * Generate Mattermost messages from StarWars quotes.
 *
 * @package GregorJ\Mattersend
 * @author  Gregor J.
 * @license MIT
 */
class StarWarsQuotes
{
    /**
     * @var array StarWars failure quotes.
     */
    private static $fail = [
        [
            [
                'avatar' => 'Stormtrooper head',
                'message' => 'The connection to %s is interrupted.'
            ],
            [
                'avatar' => 'Darth Vader mask silhouette',
                'message' => 'I find this lack in connectivity disturbing.'
            ]
        ],
        [
            [
                'avatar' => 'C3-PO silhouette',
                'message' => 'Sir, it is very possible this connection to %s is not entirely stable.'
            ],
            [
                'avatar' => 'Millenium Falcon',
                'message' => ":boom:\n`<connection to %s terminated>`"
            ],
            [
                'avatar' => 'Han Solo',
                'message' => 'Not entirely stable? Well, I am glad you are here to tell us these things.'
            ]
        ],
        [
            [
                'avatar' => 'C3-PO silhouette',
                'message' => 'His High Exaltedness, the great Jabba the Hutt, has decreed that the connection to %s is to be terminated immediately.'
            ],
            [
                'avatar' => 'Han Solo',
                'message' => 'Good, I hate long waits.'
            ]
        ],
        [
            [
                'avatar' => 'Yoda head',
                'message' => 'Mmm. Lost the connection to %s Master Obi-Wan has. How embarrassing. How embarrassing.'
            ]
        ],
        [
            [
                'avatar' => 'Stormtrooper head',
                'message' => 'Who is this? What’s your operating number?'
            ],
            [
                'avatar' => 'Han Solo',
                'message' => "Uh...\n`<connection to %s terminated>`\nBoring conversation, anyway."
            ]
        ],
        [
            [
                'avatar' => 'X-Wing',
                'message' => ":boom:\n`<connection to %s terminated>`"
            ],
            [
                'avatar' => 'C3-PO silhouette',
                'message' => 'Oh, dear!'
            ]
        ],
        [
            [
                'avatar' => 'Princess Leia Organa',
                'message' => 'Looks like you managed to cut off our only connection to %s!'
            ],
            [
                'avatar' => 'Han Solo',
                'message' => 'Maybe you like it back in your cell, Your Highness.'
            ]
        ]
    ];

    /**
     * @var array StarWars success quotes.
     */
    private static $success = [
        [
            [
                'avatar' => 'Death Star incomplete silhouette',
                'message' => "`<connection to %s established>`"
            ],
            [
                'avatar' => 'Darth Vader mask silhouette',
                'message' => "Don't be too proud of this connection you have constructed. The ability to bypass the KBA firewall is insignificant next to the power of the force."
            ]
        ],
        [
            [
                'avatar' => 'Luke Skywalker',
                'message' => "I am Luke Skywalker and I am here to rescue your connection to %s!\n:light-saber-green:"
            ],
            [
                'avatar' => 'Han Solo',
                'message' => "Great, kid. Don't get cocky."
            ]
        ],
        [
            [
                'avatar' => 'Darth Vader mask silhouette',
                'message' => "Now, witness the power of this fully operational connection to %s!\n:death-star:"
            ]
        ],
        [
            [
                'avatar' => 'C3-PO silhouette',
                'message' => 'Wonderful! We are now a part of the tribe at %s.'
            ],
            [
                'avatar' => 'Han Solo',
                'message' => 'Just what I always wanted.'
            ]
        ],
        [
            [
                'avatar' => 'C3-PO silhouette',
                'message' => 'Sir, the possibility of successfully establishing a connection to %s is approximately three thousand seven hundred and twenty to one!'
            ],
            [
                'avatar' => 'Han Solo',
                'message' => 'Never tell me the odds!'
            ],
            [
                'avatar' => 'Millenium Falcon',
                'message' => '`<connection to %s established>`'
            ]
        ],
        [
            [
                'avatar' => 'Han Solo',
                'message' => 'Not a bad bit of establishing a connection to %s, huh? You know, sometimes I amaze even myself.'
            ],
            [
                'avatar' => 'Princess Leia Organa',
                'message' => "That doesn't sound too hard."
            ]
        ],
        [
            [
                'avatar' => 'Han Solo',
                'message' => 'What the hell are you doing?'
            ],
            [
                'avatar' => 'Princess Leia Organa',
                'message' => "Somebody has to save our connection!\n`<connection to %s established>`"
            ]
        ]
    ];

    /**
     * @var string The host being monitored.
     */
    private $host;

    /**
     * @var \GregorJ\Mattersend\Avatars
     */
    private $avatars;

    /**
     * StarWarsQuotes constructor.
     * @param string $host The host being monitored.
     * @param \GregorJ\Mattersend\Avatars The avatars file containing the avatars.
     * @throws \InvalidArgumentException in case the monitor host is not a string or empty.
     */
    public function __construct($host, Avatars $avatars)
    {
        if (!is_string($host) || empty(trim($host))) {
            throw new \InvalidArgumentException('Invalid monitor host.');
        }
        $this->host = trim($host);
        $this->avatars = $avatars;
    }

    /**
     * Get a random StarWars quote dialog.
     * @param bool $success Success (true) or failure (false) quote.
     * @return array Mattermost messages of the quote dialog from StarWars.
     * @throws \RuntimeException in case the requested avatar for the quote cannot be found.
     */
    public function getQuote($success)
    {
        $dialog = $this->getRandomDialog($success);
        $return = [];
        foreach ($dialog as $textProperties) {
            $return[] = $this->composeMessage($textProperties);
        }
        return $return;
    }

    /**
     * Get a random dialog from the success and failure dialogs of this class.
     * @param bool $success Success (true) or failure (false) quote.
     * @return array Array of messages of a dialog.
     */
    private function getRandomDialog($success)
    {
        $dialogs = $success ? static::$success : static::$fail;
        $max = count($dialogs) -1;
        $randomId = mt_rand(0, $max);
        return $dialogs[$randomId];
    }

    /**
     * Compose a Mattermost message from the properties of a single dialog message.
     * @param array $textProperties
     * @return \ThibaudDauce\Mattermost\Message
     * @throws \RuntimeException in case the requested avatar for the quote cannot be found.
     */
    private function composeMessage($textProperties)
    {
        $return = new Message();
        foreach ($textProperties as $property => $value) {
            switch ($property) {
                case 'avatar':
                    $avatar = $this->avatars->get($value);
                    $return->iconUrl($avatar->getImageUrl());
                    if (!array_key_exists('sender', $textProperties)) {
                        $return->username($avatar->getDisplayName());
                    }
                    break;
                case 'sender':
                    $return->username($value);
                    break;
                case 'message':
                    $return->text($this->formatConnectionString($value));
                    break;
                case 'channel':
                    $return->channel($value);
                    break;
                default:
                    break;
            }
        }
        if (empty($return->text) || empty($return->username)) {
            throw new \RuntimeException('Username or text have not been set!');
        }
        return $return;
    }

    /**
     * Format the connection string in a message text.
     * @param string $text The text message containing a %s placeholder.
     * @return string The text message where the placeholder has been replaced.
     */
    private function formatConnectionString($text)
    {
        if (strpos($text, '%s') === false) {
            return $text;
        }
        return sprintf($text, $this->host);
    }
}
