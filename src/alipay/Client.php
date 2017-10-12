<?php

namespace yunwuxin\alipay;

use DomainException;
use GuzzleHttp\Psr7\Response;
use Jenssegers\Date\Date;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Client
{

    protected $liveEndpoint = "https://openapi.alipay.com/gateway.do";
    protected $testEndpoint = "https://openapi.alipaydev.com/gateway.do";

    protected $options;

    protected $test = false;

    public function __construct($options)
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function execute($method, $bizContent, $extra = [])
    {
        $params   = $this->buildParams($method, $bizContent, $extra);
        $response = \yunwuxin\util\http\Client::get($this->endpoint(), \yunwuxin\util\http\Options::makeWithQuery($params));

        return $this->validateResponse($response, $method);
    }

    public function setTest()
    {
        $this->test = true;
        return $this;
    }

    /**
     * @param Response $response
     * @param          $method
     * @return array
     */
    protected function validateResponse($response, $method)
    {
        $response = json_decode($response->getBody()->getContents(), true);

        $key    = str_replace('.', '_', $method) . '_response';
        $result = $response[$key];

        if (empty($result['code']) || $result['code'] != 10000) {
            throw new DomainException(isset($result['sub_msg']) ? $result['sub_msg'] : $result['msg']);
        }
        if (isset($response['sign'])) {
            $this->verifySign(json_encode($result, JSON_UNESCAPED_UNICODE), $response['sign']);
        }
        return $result;
    }

    public function verifySign($data, $sign)
    {
        $res = $this->buildPublicKey();
        if ('RSA2' == $this->options['sign_type']) {
            $result = (bool) openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        }
        if (!$result) {
            throw new DomainException('签名验证失败');
        }
    }

    public function endpoint()
    {
        if ($this->test) {
            return $this->testEndpoint;
        } else {
            return $this->liveEndpoint;
        }
    }

    public function buildParams($method, $bizContent, $extra = [])
    {
        $params = array_merge([
            'app_id'      => $this->options['app_id'],
            'method'      => $method,
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => Date::now()->format('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode($bizContent, JSON_UNESCAPED_UNICODE)
        ], $extra);

        $params['sign'] = $this->generateSign($params);

        return $params;
    }

    protected function generateSign($params)
    {
        $data = $this->buildSignContent($params);
        $res  = $this->buildPrivateKey();
        if ("RSA2" == $this->options['sign_type']) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }
        return base64_encode($sign);
    }

    /**
     * 生成待签名内容
     * @param $params
     * @return string
     */
    public function buildSignContent($params)
    {
        ksort($params);
        return urldecode(http_build_query($params));
    }

    protected function buildPublicKey()
    {
        return "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->options['public_key'], 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
    }

    protected function buildPrivateKey()
    {
        return "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->options['private_key'], 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('sign_type', 'RSA2');
        $resolver->setRequired(['app_id', 'public_key', 'private_key']);

        $resolver->setNormalizer('public_key', function (Options $options, $value) {
            if (is_file($value)) {
                $value = file_get_contents($value);
            }

            return $value;
        });

        $resolver->setNormalizer('private_key', function (Options $options, $value) {
            if (is_file($value)) {
                $value = file_get_contents($value);
            }

            return $value;
        });
    }
}