<?php
/**
 * @Author:      Jan
 * @Description:
 * @Date:        Created in 2022/6/14 5:57 下午
 * @Modifid By:
 */

namespace HttpProvider;

class HttpProvider
{
    /**
     * 使用curl方式发起的get请求
     *
     * @param $url
     * @param int $timeout
     * @return mixed|array
     */
    public static function coreCurlGet($url, $timeout = 30)
    {
        $arrCurlResult = array();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $arrCurlResult['output'] = $output;//返回结果
        $arrCurlResult['response_code'] = $responseCode;//返回http状态
        curl_close($ch);
        unset($ch);
        return $arrCurlResult;
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