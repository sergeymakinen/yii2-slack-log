<?php
/**
 * Slack log target for Yii 2.
 *
 * @see       https://github.com/sergeymakinen/yii2-slack-log
 * @copyright Copyright (c) 2016 Sergey Makinen (https://makinen.ru)
 * @license   https://github.com/sergeymakinen/yii2-slack-log/blob/master/LICENSE The MIT License
 */

namespace sergeymakinen\log;

use yii\base\InvalidValueException;
use yii\di\Instance;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\httpclient\Client;
use yii\log\Logger;
use yii\log\Target;
use yii\web\Request;

class SlackTarget extends Target
{
    /**
     * Yii HTTP client configuration.
     * This can be a component ID, a configuration array or a Client instance.
     *
     * @var Client|array|string
     * @since 1.2
     */
    public $httpClient = [
        'class' => 'yii\httpclient\Client',
    ];

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
        Logger::LEVEL_WARNING => 'warning',
    ];

    /**
     * @inheritDoc
     */
    public $exportInterval = 50;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->httpClient = Instance::ensure($this->httpClient, Client::className());
    }

    /**
     * @inheritDoc
     */
    public function export()
    {
        $response = $this->httpClient->post(
            $this->webhookUrl,
            Json::encode($this->getPayload()),
            ['Content-Type: application/json; charset=UTF-8']
        )->send();
        if (!$response->getIsOk()) {
            throw new InvalidValueException(
                "Unable to send logs to Slack: {$response->getContent()}", (int) $response->getStatusCode()
            );
        }
    }

    /**
     * Encodes special chars in a message as HTML entities.
     *
     * @param string $message
     *
     * @return string
     */
    protected function encodeMessage($message)
    {
        return htmlspecialchars($message, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * Returns a Slack API payload.
     *
     * @return array
     * @since 1.2
     */
    protected function getPayload()
    {
        $payload = [
            'parse' => 'none',
            'attachments' => array_map([$this, 'formatMessageAttachment'], $this->messages),
        ];
        $this->insertIntoPayload($payload, 'username', $this->username);
        $this->insertIntoPayload($payload, 'icon_url', $this->iconUrl);
        $this->insertIntoPayload($payload, 'icon_emoji', $this->iconEmoji);
        $this->insertIntoPayload($payload, 'channel', $this->channel);
        return $payload;
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
            'fallback' => $this->encodeMessage($this->formatMessage($message)),
            'title' => ucwords(Logger::getLevelName($level)),
            'fields' => [
                [
                    'title' => 'Level',
                    'value' => Logger::getLevelName($level),
                    'short' => true,
                ],
                [
                    'title' => 'Category',
                    'value' => '`' . $this->encodeMessage($category) . '`',
                    'short' => true,
                ],
            ],
            'footer' => static::className(),
            'ts' => (int) round($timestamp),
            'mrkdwn_in' => [
                'fields',
                'text',
            ],
        ];
        if (isset($this->prefix)) {
            $attachment['fields'][] = [
                'title' => 'Prefix',
                'value' => '`' . $this->encodeMessage(call_user_func($this->prefix, $message)) . '`',
                'short' => true,
            ];
        }
        if (isset(\Yii::$app)) {
            $this->insertRequestIntoAttachment($attachment);
            $this->insertUserIntoAttachment($attachment);
            $this->insertSessionIntoAttachment($attachment);
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
        $attachment['text'] = "```\n" . $this->encodeMessage($text) . "\n```";
        if (isset($message[4]) && !empty($message[4])) {
            $this->insertTracesIntoAttachment($message[4], $attachment);
        }
        return $attachment;
    }

    /**
     * Inserts session data into the attachement if applicable.
     *
     * @param array $attachment
     */
    private function insertSessionIntoAttachment(array &$attachment)
    {
        if (
            \Yii::$app->has('session', true)
            && !is_null(\Yii::$app->getSession())
            && \Yii::$app->getSession()->getIsActive()
        ) {
            $attachment['fields'][] = [
                'title' => 'Session ID',
                'value' => '`' . $this->encodeMessage(\Yii::$app->getSession()->getId()) . '`',
                'short' => true,
            ];
        }
    }

    /**
     * Inserts traces into the attachement if applicable.
     *
     * @param array $traces
     * @param array $attachment
     */
    private function insertTracesIntoAttachment(array $traces, array &$attachment)
    {
        $traces = array_map(function ($trace) {
            return "in {$trace['file']}:{$trace['line']}";
        }, $traces);
        $attachment['fields'][] = [
            'title' => 'Stack Trace',
            'value' => "```\n" . $this->encodeMessage(implode("\n", $traces)) . "\n```",
            'short' => false,
        ];
    }

    /**
     * Inserts user data into the attachement if applicable.
     *
     * @param array $attachment
     */
    private function insertUserIntoAttachment(array &$attachment)
    {
        if (\Yii::$app->has('user', true) && !is_null(\Yii::$app->getUser())) {
            $user = \Yii::$app->getUser()->getIdentity(false);
            if (isset($user)) {
                $attachment['fields'][] = [
                    'title' => 'User ID',
                    'value' => $this->encodeMessage($user->getId()),
                    'short' => true,
                ];
            }
        }
    }

    /**
     * Inserts request data into the attachement if applicable.
     *
     * @param array $attachment
     */
    private function insertRequestIntoAttachment(array &$attachment)
    {
        if (\Yii::$app->getRequest() instanceof Request) {
            $attachment['author_name'] = $attachment['author_link'] = Url::current([], true);
            $attachment['fields'][] = [
                'title' => 'User IP',
                'value' => \Yii::$app->getRequest()->getUserIP(),
                'short' => true,
            ];
        } else {
            if (isset($_SERVER['argv'])) {
                $params = $_SERVER['argv'];
            } else {
                $params = [];
            }
            $attachment['author_name'] = implode(' ', $params);
        }
    }

    /**
     * Copies the value to the payload if the value is set.
     *
     * @param array $payload
     * @param string $name
     * @param mixed $value
     */
    private function insertIntoPayload(array &$payload, $name, $value)
    {
        if (isset($value)) {
            $payload[$name] = $value;
        }
    }
}
