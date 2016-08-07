# Slack log target for Yii 2

Pretty [Slack](https://slack.com) log target for Yii 2.

![Screenshot](README.png)

[![Code Quality](https://img.shields.io/scrutinizer/g/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://scrutinizer-ci.com/g/sergeymakinen/yii2-slack-log) [![Packagist Version](https://img.shields.io/packagist/v/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://packagist.org/packages/sergeymakinen/yii2-slack-log) [![Total Downloads](https://img.shields.io/packagist/dt/sergeymakinen/yii2-slack-log.svg?style=flat-square)](https://packagist.org/packages/sergeymakinen/yii2-slack-log) [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

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
                'exportInterval' => 50, // 50 or less is better
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
                    'yii\web\HttpException',
                    'yii\web\HttpException:404',
                ],
                'exportInterval' => 50,
                'enabled' => YII_ENV_PROD,
                'webhookUrl' => 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
                'username' => 'Fire Alarm Bot',
                'iconEmoji' => ':poop:',
            ],
        ],
    ],
],
```
