<?php
/**
 * Slack log target for Yii 2
 *
 * @see       https://github.com/sergeymakinen/yii2-slack-log
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-slack-log/blob/master/LICENSE The MIT License
 */

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
        $this->copyToPayload($payload, 'username', $this->username);
        $this->copyToPayload($payload, 'icon_url', $this->iconUrl);
        $this->copyToPayload($payload, 'icon_emoji', $this->iconEmoji);
        $this->copyToPayload($payload, 'channel', $this->channel);
        if (extension_loaded('curl')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
            curl_setopt($curl, CURLOPT_POSTFIELDS, Json::encode($payload));
            curl_setopt($curl, CURLOPT_URL, $this->webhookUrl);
            curl_exec($curl);
            curl_close($curl);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json; charset=UTF-8\r\n",
                    'content' => Json::encode($payload)
                ]
            ]);
            file_get_contents($this->webhookUrl, false, $context);
        }
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
        if (isset($this->prefix)) {
            $attachment['fields'][] = [
                'title' => 'Prefix',
                'value' => '`' . static::encodeMessage(call_user_func($this->prefix, $message)) . '`',
                'short' => true,
            ];
        }
        $this->applyApplicationFormatToAttachment($attachment);
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

    /**
     * Inserts current application details to the attachment.
     *
     * @param array $attachment
     */
    private function applyApplicationFormatToAttachment(array &$attachment)
    {
        if (!isset(\Yii::$app)) {
            return;
        }

        if (\Yii::$app->getRequest() instanceof Request) {
            $attachment['author_name'] = Url::current([], true);
            $attachment['author_link'] = $attachment['author_name'];
            $attachment['fields'][] = [
                'title' => 'User IP',
                'value' => \Yii::$app->getRequest()->getUserIP(),
                'short' => true,
            ];
        } else {
            $attachment['author_name'] = implode(' ', \Yii::$app->getRequest()->getParams());
        }
        if (\Yii::$app->has('user', true) && !is_null(\Yii::$app->getUser())) {
            $user = \Yii::$app->getUser()->getIdentity(false);
            if (isset($user)) {
                $attachment['fields'][] = [
                    'title' => 'User ID',
                    'value' => $user->getId(),
                    'short' => true,
                ];
            }
        }
        if (
            \Yii::$app->has('session', true)
            && !is_null(\Yii::$app->getSession())
            && \Yii::$app->getSession()->getIsActive()
        ) {
            $attachment['fields'][] = [
                'title' => 'Session ID',
                'value' => \Yii::$app->getSession()->getId(),
                'short' => true,
            ];
        }
    }

    /**
     * Copies the value to the payload if the value is set.
     *
     * @param array $payload
     * @param string $name
     * @param mixed $value
     */
    private function copyToPayload(array &$payload, $name, $value)
    {
        if (isset($value)) {
            $payload[$name] = $value;
        }
    }
}
