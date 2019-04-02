<?php
/**
 * 亿牛集团
 * 本源代码由亿牛集团及其作者共同所有，未经版权持有者的事先书面授权，
 * 不得使用、复制、修改、合并、发布、分发和/或销售本源代码的副本。
 *
 * @copyright Copyright (c) 2017 yiniu.com all rights reserved.
 */


namespace Ym\http\request;


use Psr\Http\Message\StreamInterface;
use \GuzzleHttp\RequestOptions;
use Ym\http\request\exceptions\InvalidArgumentException;
use Ym\http\request\exceptions\NotSupportedException;

/**
 *
 * ～～～～～～～～ example ～～～～～～～～～～～～～
 * 发送post请求
 * $response = HttpClient::post('http://www.test.com' . '/server/test/index')
 *   ->setFormData(['aaa'=>'post'])  //设置body实体并设置content-type为application/x-www-form-urlencoded
 *   ->setQuery(['a' => 1])  //设置url后面加到参数
 *   ->send();
 *   var_dump($response->getDecodeData());
 *
 * $response = HttpClient::get('http://www.test.com' . '/server/test/index')
 *   ->setQuery(['a' => 1])  //设置url后面加到参数
 *   ->send();
 *   var_dump($response->getDecodeData());
 * ～～～～～～～～～～～～～～～～～～～～～
 * Class httpClient 基于GuzzleHttp  setFormData和setBody不能同时使用
 * @package yiniu\components\http
 * @author yaoyongfeng
 */
class HttpClient
{
    /**
     * POST method
     *
     * @var string
     */
    const POST = 'POST';

    /**
     * PUT method
     *
     * @var string
     */
    const PUT = 'PUT';

    /**
     * GET method
     *
     * @var string
     */
    const GET = 'GET';

    /**
     * HEAD method
     *
     * @var string
     */
    const HEAD = 'HEAD';

    /**
     * DELETE method
     *
     * @var string
     */
    const DELETE = 'DELETE';

    /**
     * OPTIONS method
     *
     * @var string
     */
    const OPTIONS = 'OPTIONS';

    /**
     * TRACE method
     *
     * @var string
     */
    const TRACE = 'TRACE';

    /**
     * PATCH method
     *
     * @link https://tools.ietf.org/html/rfc5789
     * @var string
     */
    const PATCH = 'PATCH';

    const FORMAT_TEXT = 'text';
    /* post表单类型提交*/
    const FORMAT_URLENCODED = 'raw-urlencoded';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_XML = 'xml';

    private $_method = null;
    private $_url = null;
    /* @var float $_timeout 请求超时的秒数。使用 0 无限期的等待(默认行为) */
    private $_timeout = 8.0;
    /* @var float $_connect_timeout 表示等待服务器响应超时的最大值，使用 0 将无限等待 (默认行为). */
    private $_connect_timeout = 10;
    /* @var string|float $_version 请求要使用到的协议版本 */
    private $_version = 1.1;
    private $_format = self::FORMAT_TEXT;
    private $_query = [];
    /* @var resource|string|StreamInterface */
    private $_body = null;
    /* @var array $_formData */
    private $_formData = null;
    private $_headers = [
        'User-Agent' => 'Mozilla/5.0 YN7725 RequestClient(compatible; MSIE 5.01; Windows NT 5.0)',
    ];
    private $_charset = 'UTF-8';


    /**
     * @param string $method 方法 默认是get方法
     * @return self
     * @author yaoyongfeng
     */
    public static function instance($method = self::GET)
    {
        return (new self())->setMethod($method);
    }

    /**
     * @param string $url 不能带querystring参数
     * @return self
     * @author yaoyongfeng
     */
    public static function get($url)
    {
        return self::instance(self::GET)->setBaseUri($url);
    }

    /**
     * @param string $url 不能带querystring参数
     * @return self
     * @author yaoyongfeng
     */
    public static function post($url)
    {
        return self::instance(self::POST)->setBaseUri($url);
    }

    /**
     *
     * @param $method
     * @return $this
     * @author yaoyongfeng
     */
    private function setMethod($method)
    {
        $this->_method = strtoupper($method);
        return $this;
    }


    /**
     *
     * @param string $baseUri 不能带querystring参数
     * @return $this
     * @author yaoyongfeng
     */
    public function setBaseUri($baseUri)
    {

        if (filter_var($baseUri, FILTER_VALIDATE_URL) == false) {
            throw new InvalidArgumentException('url格式不对');
        }
        if (filter_var($baseUri, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
            throw new InvalidArgumentException('url不能带参数,请通过setQuery传递url参数');
        }
        $this->_url = $baseUri;
        return $this;
    }


    public function getBaseUri()
    {
        if (empty($this->_url)) {
            throw new InvalidArgumentException('请设置请求url');
        }
        return $this->_url;
    }

    /**
     *
     * @param $timeout
     * @return $this
     * @author yaoyongfeng
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = (float)$timeout;
        return $this;
    }

    /**
     *
     * @param $format
     * @return $this
     * @author yaoyongfeng
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        switch (strtolower($this->_format)) {
            case self::FORMAT_JSON:
                $this->addHeaders(['Content-Type' => 'application/json; charset=' . $this->_charset]);
                break;
            case self::FORMAT_JSONP:
                $this->addHeaders(['Content-Type' => 'application/javascript; charset=' . $this->_charset]);
                break;
            case self::FORMAT_HTML:
                $this->addHeaders(['Content-Type' => 'text/html; charset=' . $this->_charset]);
                break;
            case self::FORMAT_XML:
                $this->addHeaders(['Content-Type' => 'application/xml; charset=' . $this->_charset]);
                break;
            case self::FORMAT_URLENCODED:
                $this->addHeaders(['Content-Type' => 'application/x-www-form-urlencoded; charset=' . $this->_charset]);
                break;

        }
        return $this;
    }

    /**
     * 默认会在baseUri后面添加?和query string，可以被$_GET获取
     * @param array $query
     * @return $this
     * @author yaoyongfeng
     */
    public function setQuery(array $query)
    {
        $this->_query = $query;
        return $this;
    }


    /**
     * 设置http请求body实体，并设置content-type 为application/x-www-form-urlencoded 表单提交，可以被$_POST获取到
     * @param array $formData
     * @return $this
     * @throws NotSupportedException
     * @author yaoyongfeng
     */
    public function setFormData(array $formData)
    {
        if (in_array($this->_method, [self::GET, self::HEAD])) {
            throw new NotSupportedException('请求方法为get不支持设置formData 请使用setQuery');
        }
        if (!is_null($this->_body)) {
            throw new InvalidArgumentException('调用 setPostData 出错已经设置了body');
        }
        if ($this->_format != self::FORMAT_TEXT) {
            throw new InvalidArgumentException('调用此函数请勿设置 format，程序会默认设置format为application/x-www-form-urlencoded');
        }
        $this->_formData = $formData;
        return $this;
    }


    /**
     * 设置http请求body实体  不能被$_POST获取到，只能通过file_get_contents('php://input') 获取请求body实体
     * @param $body
     * @return $this
     * @throws NotSupportedException
     * @author yaoyongfeng
     */
    public function setBody($body)
    {
        if (in_array($this->_method, [self::GET, self::HEAD])) {
            throw new NotSupportedException('请求方法为get不支持设置body 请使用setQuery');
        }
        if (!is_null($this->_formData)) {
            throw new InvalidArgumentException('调用 setBody 出错，已经设置了postData');
        }
        if ($this->_method == self::POST && is_array($body)) {
            throw new InvalidArgumentException('Passing in the "body" request option as an array to send a POST request has been deprecated. Please use the "form_params" request option to send a application/x-www-form-urlencoded request, or the "multipart" request option to send a multipart/form-data request.');
        }
        if (is_resource($this->_body) && get_resource_type($this->_body) != 'file') {
            throw new InvalidArgumentException('body 格式有误');
        }
        $this->_body = $body;
        return $this;
    }

    public function setCharset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }

    /**
     *
     * @param array $header
     * @return $this
     * @author yaoyongfeng
     */
    public function addHeaders(array $header)
    {
        $this->_headers = array_merge($this->_headers, $header);
        return $this;
    }


    public function getBody()
    {
        return $this->_body;
    }

    public function getFormData()
    {
        return $this->_formData;
    }


    /**
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author yaoyongfeng
     */
    public function send()
    {
        $client = new \GuzzleHttp\Client([]);
        $config = [
            RequestOptions::DEBUG           => false,
            RequestOptions::HTTP_ERRORS     => false,
            RequestOptions::QUERY           => $this->_query,
            RequestOptions::CONNECT_TIMEOUT => $this->_connect_timeout,
            RequestOptions::TIMEOUT         => $this->_timeout,
            RequestOptions::HEADERS         => $this->_headers,
        ];
        if (!in_array($this->_method, [self::GET, self::HEAD])) {
            !is_null($this->getBody()) && $config[RequestOptions::BODY] = $this->getBody();
            !is_null($this->getFormData()) && $config[RequestOptions::FORM_PARAMS] = $this->getFormData();
        }
        try {
            $request = $client->request($this->_method, $this->getBaseUri(), $config);
            //开发模式也可以写入日志请求参数和结果
        } catch (\GuzzleHttp\Exception\GuzzleException $exception) {
            //todo 写入日志 'http client error:' . $exception->getMessage()
            throw $exception;
        }
        return new Response($request, $this->_format);
    }

}