# Slack log target for Yii 2

Pretty [Slack](https://slack.com) log target for Yii 2.

![Screenshot](docs/README.png)

[![Code Quality](https://img.shields.io/scrutinizer/g/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://scrutinizer-ci.com/g/sergeymakinen/yii2-slack-log) [![Build Status](https://img.shields.io/travis/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://travis-ci.org/sergeymakinen/yii2-slack-log) [![Code Coverage](https://img.shields.io/codecov/c/github/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://codecov.io/gh/sergeymakinen/yii2-slack-log) [![SensioLabsInsight](https://img.shields.io/sensiolabs/i/ba92b44d-afd3-463d-9d61-95ac316537af.svg?style=flat-square)](https://insight.sensiolabs.com/projects/ba92b44d-afd3-463d-9d61-95ac316537af)

[![Packagist Version](https://img.shields.io/packagist/v/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://packagist.org/packages/sergeymakinen/yii2-slack-log) [![Total Downloads](https://img.shields.io/packagist/dt/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://packagist.org/packages/sergeymakinen/yii2-slack-log) [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Installation

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require sergeymakinen/yii2-slack-log "^1.0"
```

or add

```
"sergeymakinen/yii2-slack-log": "^1.0"
```

to the require section of your `composer.json` file.

## Usage

First set up an [incoming webhook integration](https://my.slack.com/services/new/incoming-webhook/) in your Slack team and obtain a token. It should look like `https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX`.

Then set the following Yii 2 configuration parameters:

```php
'components' => [
    'log' => [
        'targets' => [
            [
                'class' => 'sergeymakinen\log\SlackTarget',
                'webhookUrl' => 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
            ],
        ],
    ],
],
```

Sample config:

```php
'components' => [
    'log' => [
        'targets' => [
            [
                'class' => 'sergeymakinen\log\SlackTarget',
                'levels' => ['error'],
                'except' => [
                    'yii\web\HttpException:*',
                ],
                'enabled' => YII_ENV_PROD,
                'webhookUrl' => 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
                'username' => 'Fire Alarm Bot',
                'iconEmoji' => ':poop:',
            ],
        ],
    ],
],
```
