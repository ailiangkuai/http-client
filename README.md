http请求工具,用于请求http接口的php库，基于guzzlehttp/guzzle,guzzlehttp/guzzle库虽然
功能强大，但是写的时候总是会忘记如何设置参数，http-client支持连贯书写设置http请求参数
,支持自动解析json字符串和xml字符串
####安装
1.在项目中的composer.json中添加亚美仓库源
```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "http://10.1.140.143/"
        }
    ]
}
```
2.执行命令
  
```shell
  composer require yamei/http-client
```
####使用demo
>发送post请求
```php
<?php
use Ym\http\request\HttpClient;

/*
 * 请求的url:http://www.test.com/server/test/index?a=1
 * 请求方法:post
 * 请求$_POST参数 ['a'=>1]
 * 返回array
 * */
$response = HttpClient::post('http://www.test.com' . '/server/test/index')
->setFormData(['aaa'=>'post'])  //设置body实体并设置content-type为application/x-www-form-urlencoded
->setQuery(['a' => 1])  //设置url后面加到参数
->send()
->getJsonData();//解析json字符串

```

>发送get请求
```php


<?php 
use Ym\http\request\HttpClient;
/*
 * 请求的url:http://www.test.com/server/test/xml?a=1
 * 请求方法:get
 * 返回array
 * */
$response = HttpClient::get('http://www.test.com' . '/server/test/xml')
->setQuery(['a' => 1])  //设置url后面加到参数
->send()->getXmlData();

```