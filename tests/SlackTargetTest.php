<?php

namespace sergeymakinen\tests\slacklog;

use sergeymakinen\tests\slacklog\helpers\Contains;
use sergeymakinen\tests\slacklog\helpers\Matches;
use sergeymakinen\tests\slacklog\helpers\Tester;
use sergeymakinen\tests\slacklog\stubs\TestController;
use sergeymakinen\tests\slacklog\stubs\TestException;
use yii\base\ErrorHandler;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;
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
                        '&lt;foo&gt;[error][sergeymakinen\tests\slacklog\SlackTargetTest::testGetPayload]',
                        'sergeymakinen\tests\slacklog\stubs\TestException',
                        'Hello &amp; &lt;world&gt; 🌊',
                        '[internal function]: sergeymakinen\tests\slacklog\SlackTargetTest-&gt;testGetPayload()',
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
                            'value' => '`sergeymakinen\tests\slacklog\SlackTargetTest::testGetPayload`',
                            'short' => true,
                        ],
                        [
                            'title' => 'Prefix',
                            'value' => '`&lt;foo&gt;`',
                            'short' => true,
                        ],
                        [
                            'title' => 'User IP',
                            'value' => '0.0.0.0',
                            'short' => true,
                        ],
                        [
                            'title' => 'User ID',
                            'value' => '&lt;userId&gt;',
                            'short' => true,
                        ],
                        [
                            'title' => 'Session ID',
                            'value' => '&lt;session_id&gt;',
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
                    'text' => new Contains([
                        'sergeymakinen\tests\slacklog\stubs\TestException',
                        'Hello &amp; &lt;world&gt; 🌊',
                    ]),
                ],
                [
                    'fallback' => new Contains([
                        '&lt;foo&gt;[info][sergeymakinen\tests\slacklog\SlackTargetTest::testGetPayload]',
                        'bar',
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
                            'value' => '`sergeymakinen\tests\slacklog\SlackTargetTest::testGetPayload`',
                            'short' => true,
                        ],
                        [
                            'title' => 'Prefix',
                            'value' => '`&lt;foo&gt;`',
                            'short' => true,
                        ],
                        [
                            'title' => 'User IP',
                            'value' => '0.0.0.0',
                            'short' => true,
                        ],
                        [
                            'title' => 'User ID',
                            'value' => '&lt;userId&gt;',
                            'short' => true,
                        ],
                        [
                            'title' => 'Session ID',
                            'value' => '&lt;session_id&gt;',
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
                    'text' => new Contains('bar'),
                ],
            ],
            'username' => 'Fire Alarm Bot',
            'icon_emoji' => ':poop:',
        ];
        \Yii::error(ErrorHandler::convertExceptionToString(new TestException('Hello & <world> 🌊')), __METHOD__);
        \Yii::info(['bar'], __METHOD__);
        \Yii::$app->log->logger->flush();
        $actual = $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'getPayload');
        $this->comparePayload($expected, $actual);
        $emptyArray = '[]';
        $actualArray = VarDumper::export($actual);
        $this->assertEquals($emptyArray, $actualArray);
    }

    public function testFormatMessageAttachment()
    {
        $now = microtime(true);
        $trace = array_filter(debug_backtrace(), function ($trace) {
            return isset($trace['file']);
        });
        $attachment = $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'formatMessageAttachment', [[
            new TestException('bar'),
            Logger::LEVEL_TRACE,
            '<category>',
            $now,
            $trace,
        ]]);
        $expected = [
            'fallback' => new Contains([
                '&lt;foo&gt;[trace][&lt;category&gt;]',
                'sergeymakinen\tests\slacklog\stubs\TestException',
                'bar',
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
                    'value' => '`&lt;category&gt;`',
                    'short' => true,
                ],
                [
                    'title' => 'Prefix',
                    'value' => '`&lt;foo&gt;`',
                    'short' => true,
                ],
                [
                    'title' => 'User IP',
                    'value' => '0.0.0.0',
                    'short' => true,
                ],
                [
                    'title' => 'User ID',
                    'value' => '&lt;userId&gt;',
                    'short' => true,
                ],
                [
                    'title' => 'Session ID',
                    'value' => '&lt;session_id&gt;',
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
            'text' => new Contains([
                'sergeymakinen\tests\slacklog\stubs\TestException',
                'bar',
            ]),
        ];
        $this->comparePayload($expected, $attachment);
        $emptyArray = '[]';
        $actualArray = VarDumper::export($attachment);
        $this->assertEquals($emptyArray, $actualArray);

        $this->tearDown();
        $this->createConsoleApplication([
            'components' => [
                'log' => $this->getLogConfig(),
            ],
        ]);
        $attachment = $this->invokeInaccessibleMethod(\Yii::$app->log->targets['slack'], 'formatMessageAttachment', [[
            new TestException('bar'),
            Logger::LEVEL_TRACE,
            'category',
            $now,
            $trace,
        ]]);
        $this->assertSame(implode(' ', $_SERVER['argv']), $attachment['author_name']);
    }

    protected function mockClient($success)
    {
        $response = $this->createMock(Response::className());
        if ($success) {
            $response
                ->method('getIsOk')
                ->willReturn(true);
            $response
                ->method('getContent')
                ->willReturn('success');
            $response
                ->method('getStatusCode')
                ->willReturn(200);
        } else {
            $response
                ->method('getIsOk')
                ->willReturn(false);
            $response
                ->method('getContent')
                ->willReturn('error');
            $response
                ->method('getStatusCode')
                ->willReturn(404);
        }
        $request = $this->createMock(Request::className());
        $request
            ->method('setFormat')
            ->willReturnSelf();
        $request
            ->method('send')
            ->willReturn($response);
        $client = $this->createMock(Client::className());
        $client
            ->method('post')
            ->willReturn($request);
        return $client;
    }

    public function testExportReal()
    {
        $configPath = \Yii::getAlias('@tests/config-local.php');
        if (!is_file($configPath)) {
            $this->markTestSkipped('No config file: ' . $configPath);
            return;
        }

        $config = require $configPath;
        \Yii::error(ErrorHandler::convertExceptionToString(new TestException('Hello & <world> 🌊')), __METHOD__);
        \Yii::$app->log->logger->flush();
        \Yii::$app->log->targets['slack']->webhookUrl = $config['webhookUrl'];
        \Yii::$app->log->targets['slack']->export();
    }

    public function testExportOk()
    {
        \Yii::error(ErrorHandler::convertExceptionToString(new TestException('Hello & <world> 🌊')), __METHOD__);
        \Yii::$app->log->logger->flush();
        \Yii::$app->log->targets['slack']->httpClient = $this->mockClient(true);
        \Yii::$app->log->targets['slack']->export();
    }

    /**
     * @expectedException \yii\base\InvalidValueException
     * @expectedExceptionCode 404
     */
    public function testExportError()
    {
        \Yii::error(ErrorHandler::convertExceptionToString(new TestException('Hello & <world> 🌊')), __METHOD__);
        \Yii::$app->log->logger->flush();
        \Yii::$app->log->targets['slack']->httpClient = $this->mockClient(false);
        \Yii::$app->log->targets['slack']->export();
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

    protected function setUp()
    {
        parent::setUp();
        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
        $this->createWebApplication([
            'components' => [
                'log' => $this->getLogConfig(),
                'session' => [
                    'class' => 'sergeymakinen\tests\slacklog\stubs\TestSession',
                ],
                'user' => [
                    'class' => 'sergeymakinen\tests\slacklog\stubs\TestUser',
                    'identityClass' => 'sergeymakinen\tests\slacklog\stubs\TestIdentity',
                ],
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
                        return '<foo>';
                    },
                    'logVars' => [],
                ],
            ],
        ];
    }
}
