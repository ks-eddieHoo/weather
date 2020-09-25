<?php

/*
 * This file is part of the overtrue/weather.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Eddie\Weather\Tests;

use Eddie\Weather\Exceptions\HttpException;
use Eddie\Weather\Weather;
use Eddie\Weather\Exceptions\InvalidArgumentException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Mockery\Matcher\AnyArgs;
use PHPUnit\Framework\TestCase;

class WeatherTest extends TestCase
{
    public function testGetLiveWeather()
    {
        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->expects()->getWeather('深圳', 'base', 'json')->andReturn(['success' => true]);

        $this->assertSame(['success' => true], $w->getLiveWeather('深圳'));
    }

    public function testGetForecastsWeather()
    {
        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->expects()->getWeather('深圳', 'all', 'json')->andReturn(['success' => true]);

        $this->assertSame(['success' => true], $w->getForecastsWeather('深圳'));
    }

    public function testGetWeather()
    {
        // 创建模拟接口响应值
        $response = new Response(200, [], '{"success": true}');

        // 创建模拟 http client
        $client = \Mockery::mock(Client::class);

        // 指定将会产生的行为 （在后续的测试中将会按下面的参数来调用）
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'output' => 'json',
                'extensions' => 'base',
            ],
        ])->andReturn($response);

        // 将 'getHttpClient' 方法替换为上面创建的http client为返回值的模拟方法
        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client); // $client 为上面创建的模拟实例

        // 然后调用 'getWeather' 方法，并断言返回值为模拟的返回值
        $this->assertSame(['success' => true], $w->getWeather('深圳'));

        // xml
        $response = new Response(200, [], '<hello>content</hello>');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'extensions' => 'all',
                'output' => 'xml',
            ],
        ])->andReturn($response);

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>', $w->getWeather('深圳', 'all', 'xml'));
    }

    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->get(new AnyArgs()) // 由于上面的用例已经验证过参数传递，所以这里不关心参数了。
            ->andThrow(new \Exception('request timeout')); // 当调用 get 方法时会抛出异常。

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        // 接着需要断言调用时会产生异常
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $w->getWeather('深圳');
    }

    // 2. http client 创建与参数设置
    public function testGetHttpClient()
    {
        $w = new Weather('mock-key');

        $this->assertInstanceOf(ClientInterface::class, $w->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $w = new Weather('mock-key');

        $this->assertNull($w->getHttpClient()->getConfig('timeout'));

        $w->setGuzzleOptions(['timeout' => 5000]);

        $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
    }

    // 1. 参数检查
    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-key');

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Invalid type value(base/all): foo');

        $w->getWeather('深圳', 'foo');

        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }

    // 2. 参数检查
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Invalid response format: array');

        $w->getWeather('深圳', 'base', 'array');

        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }
}
