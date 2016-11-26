<?php

namespace sergeymakinen\tests;

use sergeymakinen\tests\mocks\Contains;
use sergeymakinen\tests\mocks\Matches;
use sergeymakinen\tests\mocks\TestController;
use sergeymakinen\tests\mocks\Tester;
use sergeymakinen\tests\mocks\TestException;
use yii\base\ErrorHandler;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\log\Logger;

class SlackTargetTest extends TestCase
{
    public function testEncodeMessage()
    {
        $this->assertEquals('Hello &amp; &lt;world&gt; 🌊', $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'encodeMessage', ['Hello & <world> 🌊']));
    }

    public function testGetPayload()
    {
        $expected = [
            'parse' => 'none',
            'attachments' => [
                [
                    'fallback' => new Contains([
                        'foo[error][sergeymakinen\tests\SlackTargetTest::testGetPayload] Exception \'sergeymakinen\tests\mocks\TestException\' with message \'Hello &amp; &lt;world&gt; 🌊\'',
                        '[internal function]: sergeymakinen\tests\SlackTargetTest-&gt;testGetPayload()'
                    ]),
                    'title' => 'Error',
                    'fields' => [
                        [
                            'title' => 'Level',
                            'value' => 'error',
                            'short' => true,
                        ],
                        [
                            'title' => 'Category',
                            'value' => '`sergeymakinen\tests\SlackTargetTest::testGetPayload`',
                            'short' => true,
                        ],
                        [
                            'title' => 'Prefix',
                            'value' => '`foo`',
                            'short' => true,
                        ],
                        [
                            'title' => 'User IP',
                            'value' => '0.0.0.0',
                            'short' => true,
                        ],
                        [
                            'title' => 'User ID',
                            'value' => 'userId',
                            'short' => true,
                        ],
                        [
                            'title' => 'Session ID',
                            'value' => '`session_id`',
                            'short' => true,
                        ],
                    ],
                    'footer' => 'sergeymakinen\log\SlackTarget',
                    'ts' => new Matches('/[0-9]+/'),
                    'mrkdwn_in' => [
                        'fields',
                        'text',
                    ],
                    'author_name' => Url::current([], true),
                    'author_link' => Url::current([], true),
                    'color' => 'danger',
                    'text' => new Contains('Exception \'sergeymakinen\tests\mocks\TestException\' with message \'Hello &amp; &lt;world&gt; 🌊\''),
                ],
                [
                    'fallback' => new Contains([
                        'foo[info][sergeymakinen\tests\SlackTargetTest::testGetPayload]',
                        '\'foo\','
                    ]),
                    'title' => 'Info',
                    'fields' => [
                        [
                            'title' => 'Level',
                            'value' => 'info',
                            'short' => true,
                        ],
                        [
                            'title' => 'Category',
                            'value' => '`sergeymakinen\tests\SlackTargetTest::testGetPayload`',
                            'short' => true,
                        ],
                        [
                            'title' => 'Prefix',
                            'value' => '`foo`',
                            'short' => true,
                        ],
                        [
                            'title' => 'User IP',
                            'value' => '0.0.0.0',
                            'short' => true,
                        ],
                        [
                            'title' => 'User ID',
                            'value' => 'userId',
                            'short' => true,
                        ],
                        [
                            'title' => 'Session ID',
                            'value' => '`session_id`',
                            'short' => true,
                        ],
                    ],
                    'footer' => 'sergeymakinen\log\SlackTarget',
                    'ts' => new Matches('/[0-9]+/'),
                    'mrkdwn_in' => [
                        'fields',
                        'text',
                    ],
                    'author_name' => Url::current([], true),
                    'author_link' => Url::current([], true),
                    'text' => new Contains('\'foo\','),
                ],
            ],
            'username' => 'Fire Alarm Bot',
            'icon_emoji' => ':poop:',
        ];
        \Yii::error(ErrorHandler::convertExceptionToString(new TestException('Hello & <world> 🌊')), __METHOD__);
        \Yii::info(['foo'], __METHOD__);
        \Yii::$app->log->logger->flush();
        $actual = $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'getPayload');
        $this->comparePayload($expected, $actual);
        $emptyArray = '[]';
        $actualArray = VarDumper::export($actual);
        $this->assertEquals($emptyArray, $actualArray);
    }

    protected function comparePayload(&$expected, &$actual, $actualKey = null)
    {
        $actualParent = &$actual;
        if (isset($actualKey)) {
            $actual = &$actual[$actualKey];
        }
        if (is_array($expected)) {
            $this->assertInternalType('array', $actual);
            foreach ($expected as $key => $value) {
                $this->assertArrayHasKey($key, $actual);
                $this->comparePayload($expected[$key], $actual, $key);
            }
            if (isset($actualKey) && empty($actual)) {
                unset($actualParent[$actualKey]);
            }
        } else {
            if ($expected instanceof Tester) {
                $expected->test($this, $actual);
            } else {
                $this->assertEquals($expected, $actual);
            }
            if (isset($actualParent)) {
                unset($actualParent[$actualKey]);
            }
        }
    }

    public function testInsertRequestIntoAttachment()
    {
        $attachment = [];
        $this->createConsoleApplication([
            'charset' => 'UTF-8',
            'components' => [
                'log' => $this->getLogConfig(),
            ],
        ]);

        $_SERVER['argv'] = [
            'cmd',
            '--arg1',
            'arg2'
        ];
        $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'insertRequestIntoAttachment', [&$attachment]);
        $this->assertEquals([
            'author_name' => 'cmd --arg1 arg2'
        ], $attachment);

        $_SERVER['argv'] = null;
        $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'insertRequestIntoAttachment', [&$attachment]);
        $this->assertEquals([
            'author_name' => ''
        ], $attachment);
    }

    public function testFormatMessageAttachment()
    {
        $now = microtime(true);
        $trace = array_filter(debug_backtrace(), function ($trace) {
            return isset($trace['file']);
        });
        $attachment = $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'formatMessageAttachment', [[
            0 => new TestException('bar'),
            1 => Logger::LEVEL_TRACE,
            2 => 'category',
            3 => $now,
            4 => $trace,
        ]]);
        $expected = [
            'fallback' => new Contains([
                'foo[trace][category]',
                'sergeymakinen\tests\mocks\TestException',
                'bar'
            ]),
            'title' => 'Trace',
            'fields' => [
                [
                    'title' => 'Level',
                    'value' => 'trace',
                    'short' => true,
                ],
                [
                    'title' => 'Category',
                    'value' => '`category`',
                    'short' => true,
                ],
                [
                    'title' => 'Prefix',
                    'value' => '`foo`',
                    'short' => true,
                ],
                [
                    'title' => 'User IP',
                    'value' => '0.0.0.0',
                    'short' => true,
                ],
                [
                    'title' => 'User ID',
                    'value' => 'userId',
                    'short' => true,
                ],
                [
                    'title' => 'Session ID',
                    'value' => '`session_id`',
                    'short' => true,
                ],
                [
                    'title' => 'Stack Trace',
                    'value' => new Contains('in '),
                    'short' => false,
                ],
            ],
            'footer' => 'sergeymakinen\log\SlackTarget',
            'ts' => (string) (int) round($now),
            'mrkdwn_in' => [
                'fields',
                'text',
            ],
            'author_link' => Url::current([], true),
            'author_name' => Url::current([], true),
            'text' => new Contains('sergeymakinen\tests\mocks\TestException: bar in'),
        ];
        $this->comparePayload($expected, $attachment);
        $emptyArray = '[]';
        $actualArray = VarDumper::export($attachment);
        $this->assertEquals($emptyArray, $actualArray);
    }

    /**
     * @expectedException \yii\base\InvalidValueException
     * @expectedExceptionCode 404
     */
    public function testExport()
    {
        \Yii::error(ErrorHandler::convertExceptionToString(new TestException('Hello & <world> 🌊')), __METHOD__);
        \Yii::$app->log->logger->flush();
        \Yii::$app->log->targets['slack']->export();
    }

    protected function setUp()
    {
        parent::setUp();
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $this->createWebApplication([
            'charset' => 'UTF-8',
            'components' => [
                'log' => $this->getLogConfig(),
                'session' => [
                    'class' => 'sergeymakinen\tests\mocks\TestSession'
                ],
                'user' => [
                    'class' => 'sergeymakinen\tests\mocks\TestUser',
                    'identityClass' => 'sergeymakinen\tests\mocks\TestIdentity'
                ]
            ],
        ]);
        \Yii::$app->controller = new TestController('test', \Yii::$app);
        \Yii::$app->session->isActive;
        \Yii::$app->user->getIdentity();
    }

    protected function tearDown()
    {
        \Yii::$app->log->targets['slack']->messages = [];
        \Yii::$app->log->logger->messages = [];
        parent::tearDown();
    }

    protected function getLogConfig()
    {
        return [
            'targets' => [
                'slack' => [
                    'class' => 'sergeymakinen\log\SlackTarget',
                    'levels' => ['error', 'info'],
                    'categories' => [
                        'sergeymakinen\tests\*',
                    ],
                    'webhookUrl' => 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
                    'username' => 'Fire Alarm Bot',
                    'iconEmoji' => ':poop:',
                    'prefix' => function () {
                        return 'foo';
                    },
                    'logVars' => []
                ],
            ],
        ];
    }
}
