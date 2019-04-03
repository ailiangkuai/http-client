<?php
/**
 * 亿牛集团
 * 本源代码由亿牛集团及其作者共同所有，未经版权持有者的事先书面授权，
 * 不得使用、复制、修改、合并、发布、分发和/或销售本源代码的副本。
 *
 * @copyright Copyright (c) 2017 yiniu.com all rights reserved.
 */


namespace Ym\http\request;


use Ym\http\request\exceptions\InvalidArgumentException;
use Ym\http\request\traits\TraitJson;
use Ym\http\request\traits\TraitXml;

/**
 * Class Response
 * @package Ym\http\request
 * @author yaoyongfeng
 */
class Response
{
    use TraitXml;
    use TraitJson;
    /* @var \GuzzleHttp\Psr7\Response $_response*/
    private $_response;
    private $_format;
    private $_decodeData;
    /* @var string $_data*/
    private $_data;

    public function __construct(\GuzzleHttp\Psr7\Response $response, $format)
    {
        $this->_response = $response;
        //只能获取一次
        $this->_data = $response->getBody()->getContents();
        $this->_format = $format;
    }

    public function getIsOk()
    {
        return (string)$this->_response->getStatusCode() === "200";
    }

    public function getData()
    {

        return $this->_data;
    }

    public function getHeader($header)
    {
        return $this->_response->getHeader($header);
    }

    public function getHeaders()
    {
        return $this->_response->getHeaders();
    }


    /**
     * 返回解码格式请求是xml,json返回数组
     * @param null $format
     * @return array|mixed|string|null
     * @throws InvalidArgumentException
     * @author yaoyongfeng
     */
    public function getDecodeData($format = null)
    {
        $format = is_null($format) ? strtolower($this->_format) : strtolower($format);
        switch ($format) {
            case HttpClient::FORMAT_JSON:
                $this->_decodeData = self::jsonDecode($this->getData(), true);
                break;
//            case HttpClient::FORMAT_JSONP:
//                $this->_decodeData = $this->getData();
//                break;
//            case HttpClient::FORMAT_HTML:
//                $this->_decodeData = $this->getData();
//                break;
//            case HttpClient::FORMAT_TEXT:
//                $this->_decodeData = $this->getData();
//                break;
            case HttpClient::FORMAT_XML:
                $this->_decodeData = $this->parseXML($this->getData(), $this->_response->getHeaderLine('content-type'));
                break;
            case HttpClient::FORMAT_URLENCODED:
                parse_str($this->getData(), $this->_decodeData);
                break;
            default:
                $this->_decodeData = $this->getData();
                break;
        }
        return $this->_decodeData;
    }

    /**
     * @param bool $asArray false returned objects will be converted into associative arrays.
     * @return array
     * @throws InvalidArgumentException
     * @author yaoyongfeng
     */
    public function getJsonData($asArray = true)
    {
        if ($this->getData() === null || $this->getData() === '') {
            return null;
        }
        $this->_decodeData = self::jsonDecode($this->getData(), $asArray);
        return $this->_decodeData;
    }

    /**
     * @return array|mixed|string
     * @author yaoyongfeng
     */
    public function getXmlData()
    {
        return $this->getDecodeData(HttpClient::FORMAT_XML);
    }

}