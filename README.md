# Weather
基于[高德开放平台](https://lbs.amap.com/dev/id/newuser)的PHP天气信息组件

## 安装
```angular2
$ composer require eddietest123\weather -vvv
```

## 配置
在使用本扩展之前，你需要去[高德开放平台](https://lbs.amap.com/dev/id/newuser)注册账号，然后创建应用，获取应用的API Key。

## 使用
```angular2
use Eddie\Weather\Weather;

$key = '******************';

$weather = new Weather($key);
```
## 获取实时天气
```angular2
$response = $weather->getWeather('深圳');
```
返回：
```angular2
{
    "status": "1",
    "count": "1",
    "info": "OK",
    "infocode": "10000",
    "lives": [
        {
            "province": "广东",
            "city": "深圳市",
            "adcode": "440300",
            "weather": "中雨",
            "temperature": "27",
            "winddirection": "西南",
            "windpower": "5",
            "humidity": "94",
            "reporttime": "2018-08-21 16:00:00"
        }
    ]
}
```

## 参考
- [高德开放平台天气接口]()

## License

MIT