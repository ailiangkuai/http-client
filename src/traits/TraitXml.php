<?php
/**
 * 广州亚美科技
 * 本源代码由广州亚美科技及其作者共同所有，未经版权持有者的事先书面授权，
 * 不得使用、复制、修改、合并、发布、分发和/或销售本源代码的副本。
 *
 * @copyright Copyright (c) 2017 all rights reserved.
 */

namespace Ym\http\request\traits;


trait TraitXml
{
    /**
     * 解析xml
     * @param $data
     * @param $contentType
     * @return array
     * @author yaoyongfeng
     */
    private function parseXML($data, $contentType)
    {
        if (preg_match('/charset=(.*)/i', $contentType, $matches)) {
            $encoding = $matches[1];
        } else {
            $encoding = 'UTF-8';
        }
        $dom = new \DOMDocument('1.0', $encoding);
        $dom->loadXML($data, LIBXML_NOCDATA);
        return $this->convertXmlToArray(simplexml_import_dom($dom->documentElement));
    }

    /**
     * Converts XML document to array.
     * @param string|\SimpleXMLElement $xml xml to process.
     * @return array XML array representation.
     */
    private function convertXmlToArray($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        $result = (array)$xml;
        foreach ($result as $key => $value) {
            if (!is_scalar($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }
}