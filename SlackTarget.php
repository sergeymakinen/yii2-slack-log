<?php

namespace sergeymakinen\log;

use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;
use yii\web\Request;

class SlackTarget extends Target
{
    /**
     * Incoming Webhook URL.
     *
     * @var string
     */
    public $webhookUrl;

    /**
     * Displayed username.
     *
     * @var string
     */
    public $username;

    /**
     * Icon URL.
     *
     * @var string
     */
    public $iconUrl;

    /**
     * Icon Emoji.
     *
     * @var string
     */
    public $iconEmoji;

    /**
     * Channel or Direct Message.
     *
     * @var string
     */
    public $channel;

    /**
     * Colors per a Logger level.
     *
     * @var array
     */
    public $colors = [
        Logger::LEVEL_ERROR => 'danger',
        Logger::LEVEL_WARNING => 'warning'
    ];

    /**
     * @inheritDoc
     */
    public function export()
    {
        if (!isset($this->webhookUrl)) {
            return;
        }

        $payload = [
            'parse' => 'none',
            'attachments' => array_map([$this, 'formatMessageAttachment'], $this->messages)
        ];
        if (isset($this->username)) {
            $payload['username'] = $this->username;
        }
        if (isset($this->iconUrl)) {
            $payload['icon_url'] = $this->iconUrl;
        }
        if (isset($this->iconEmoji)) {
            $payload['icon_emoji'] = $this->iconEmoji;
        }
        if (isset($this->channel)) {
            $payload['channel'] = $this->channel;
        }
        $context  = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json; charset=UTF-8\r\n",
                'content' => Json::encode($payload)
            ]
        ]);
        @file_get_contents($this->webhookUrl, false, $context);
    }

    /**
     * Encodes special chars in a message as HTML entities.
     *
     * @param string $message
     *
     * @return string
     */
    protected static function encodeMessage($message)
    {
        return htmlspecialchars($message, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * Returns a properly formatted message attachment for Slack API.
     *
     * @param array $message
     *
     * @return array
     */
    protected function formatMessageAttachment($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $attachment = [
            'fallback' => static::encodeMessage($this->formatMessage($message)),
            'title' => ucwords(Logger::getLevelName($level)),
            'fields' => [
                [
                    'title' => 'Level',
                    'value' => Logger::getLevelName($level),
                    'short' => true
                ],
                [
                    'title' => 'Category',
                    'value' => '`' . static::encodeMessage($category) . '`',
                    'short' => true
                ]
            ],
            'footer' => static::class,
            'ts' => (integer) round($timestamp),
            'mrkdwn_in' => [
                'fields',
                'text'
            ]
        ];
        if ($this->prefix !== null) {
            $attachment['fields'][] = [
                'title' => 'Prefix',
                'value' => '`' . static::encodeMessage(call_user_func($this->prefix, $message)) . '`',
                'short' => true,
            ];
        }
        if (isset(\Yii::$app)) {
            if (isset($_SERVER['argv'])) {
                $attachment['author_name'] = implode(' ', $_SERVER['argv']);
            } elseif (\Yii::$app->request instanceof Request) {
                $attachment['author_name'] = Url::current([], true);
                $attachment['author_link'] = $attachment['author_name'];
                $attachment['fields'][] = [
                    'title' => 'User IP',
                    'value' => \Yii::$app->request->userIP,
                    'short' => true,
                ];
            }
            if (\Yii::$app->has('user', true) && isset(\Yii::$app->user)) {
                $user = \Yii::$app->user->getIdentity(false);
                if (isset($user)) {
                    $attachment['fields'][] = [
                        'title' => 'User ID',
                        'value' => $user->getId(),
                        'short' => true,
                    ];
                }
            }
            if (\Yii::$app->has('session', true) && isset(\Yii::$app->session)) {
                if (\Yii::$app->session->isActive) {
                    $attachment['fields'][] = [
                        'title' => 'Session ID',
                        'value' => \Yii::$app->session->id,
                        'short' => true,
                    ];
                }
            }
        }
        if (isset($this->colors[$level])) {
            $attachment['color'] = $this->colors[$level];
        }
        if (!is_string($text)) {
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $attachment['text'] = "```\n" . static::encodeMessage($text) . "\n```";
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }
        if (!empty($traces)) {
            $attachment['fields'][] = [
                'title' => 'Stack Trace',
                'value' => "```\n" . static::encodeMessage(implode("\n", $traces)) . "\n```",
                'short' => false,
            ];
        }
        return $attachment;
    }
}
