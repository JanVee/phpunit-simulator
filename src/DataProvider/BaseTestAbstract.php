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
        $envelopment = empty(getenv('envelopment')) ? 'test' : getenv('envelopment');
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
}