<?php
/**
 * @Author:      Jan
 * @Description:
 * @Date:        Created in 2022/6/13 10:38 上午
 * @Modifid By:
 */

namespace DataProvider;

use Codeception\PHPUnit\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class BaseTestAbstract extends TestCase
{
    protected $providerDirectory = 'tests' . DIRECTORY_SEPARATOR . 'provider';

    protected $testNamespace = 'core\TestCase';

    public function autoProvider()
    {
        $debugArray = debug_backtrace();
        $searchArray =
            [
                $this->testNamespace . '\\',
                '\\'
            ];
        $replaceArray =
            [
                null,
                DIRECTORY_SEPARATOR
            ];

        $className = str_replace($searchArray, $replaceArray, $debugArray[4]['args'][0]);
        $method = $debugArray[4]['args'][1];

        /* load as needed */
        $json = $this->loadFromJSON($className, $method);
        if ($json)
        {
            return $json;
        }
        $xml = $this->loadFromXML($className, $method);
        if ($xml)
        {
            return $xml;
        }
        return null;
    }


    /**
     * load json from path
     *
     * @param string $className name of the class
     * @param string $method name of the method
     *
     * @return array|null
     */
    protected function loadFromJSON($className, $method)
    {
        $serializer = new Serializer(
            [
                new ObjectNormalizer()
            ],
            [
                new JsonEncoder()
            ]);
        $content = $this->_loadContent($className, $method, 'json');
        if ($content)
        {
            return $serializer->decode($content, 'json');
        }
        return null;
    }


    /**
     * load xml from path
     *
     * @param string $className name of the class
     * @param string $method name of the method
     *
     * @return array|null
     */
    protected function loadFromXML($className, $method)
    {
        $serializer = new Serializer(
            [
                new ObjectNormalizer()
            ],
            [
                new XmlEncoder()
            ]);
        $content = $this->_loadContent($className, $method, 'xml');
        if ($content)
        {
            return $serializer->decode($content, 'xml');
        }
        return null;
    }


    /**
     * load content from path
     *
     * @param string $className name of the class
     * @param string $methodName name of the method
     * @param string $fileExtension extension of the file
     *
     * @return string|null
     */
    protected function _loadContent($className, $methodName, $fileExtension)
    {
        // 环境变量
        $envelopment = empty($_ENV['ENVIRONMENT']) ? 'test' : $_ENV['ENVIRONMENT'];
        $fileMethod = $this->providerDirectory . DIRECTORY_SEPARATOR . $envelopment . DIRECTORY_SEPARATOR . $className . '_' . $methodName . '.' . $fileExtension;
        $fileClassName = $this->providerDirectory . DIRECTORY_SEPARATOR . $envelopment . DIRECTORY_SEPARATOR . $className . '.' . $fileExtension;

        /* load as needed */
        if (file_exists($fileMethod))
        {
            return file_get_contents($fileMethod);
        }
        if (file_exists($fileClassName))
        {
            return file_get_contents($fileClassName);
        }
        return null;
    }


    /**
     * 使用curl方式发起的get请求
     *
     * @param $url
     * @param $data
     * @param int $timeout
     * @param null $errorInfo
     * @return mixed|array
     */
    public static function coreCurlGet($url, $data = array(), $timeout = 30, &$errorInfo = null)
    {
        //组合带参数的URL
        if (!empty($data) && is_array($data)) {
            $amp = (strpos($url, '?') == 0) ? "?" : "&";
            foreach ($data as $paramKey => $paramValue) {
                $url .= $amp . $paramKey . '=' . urlencode($paramValue);
                $amp = '&';
            }
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $output = curl_exec($ch);
        if ($errorInfo !== null) {
            $errorInfo = [
                'error_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'error_no' => curl_errno($ch),
                'error_message' => curl_error($ch)
            ];
        }
        curl_close($ch);
        unset($ch);
        return $output;
    }


    /**
     * 使用curl方式发起的post请求
     *
     * @param null $url
     * @param null $params
     * @param array $proxy
     * @param int $timeout
     * @param null $errorInfo
     * @return mixed|string
     */
    public static function coreCurlPost($url = null, $params = null, $proxy = [], $timeout = 30, &$errorInfo = null)
    {
        if (empty($url)) {
            return '';
        }

        $curl = curl_init();
        if (is_array($proxy) && isset($proxy['ip'])) {
            $info = parse_url($url);
            $curlVersion = curl_version();
            $port = isset($proxy['port']) ? $proxy['port'] : (strtolower($info['scheme']) == 'https' ? 443 : 80);
            if (version_compare($curlVersion['version'], '7.21.3', '>=')) {
                if (!defined('CURLOPT_RESOLVE')) define('CURLOPT_RESOLVE', 203);
                curl_setopt($curl, CURLOPT_RESOLVE, [$info['host'] . ':' . $port . ':' . $proxy['ip']]);
            } else {
                curl_setopt($curl, CURLOPT_PROXY, 'http://' . $proxy['ip'] . ':' . $port);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }

        if (!empty($params) && is_array($params)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }

        $content = curl_exec($curl);
        if ($errorInfo !== null) {
            $errorInfo = [
                'error_code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
                'error_no' => curl_errno($curl),
                'error_message' => curl_error($curl)
            ];
        }

        curl_close($curl);
        return $content;
    }
}