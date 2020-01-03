<?php
// +----------------------------------------------------------------------
// | ThinkPay
// +----------------------------------------------------------------------
// | Copyright (c) yunwuxin All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace yunwuxin\pay\channel;

use Carbon\Carbon;
use DomainException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use think\Cache;
use think\Request;
use yunwuxin\pay\Channel;
use yunwuxin\pay\entity\PurchaseResult;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\interfaces\Refundable;
use yunwuxin\pay\request\wechat\GetSignKeyRequest;
use yunwuxin\pay\request\wechat\OrderQueryRequest;
use yunwuxin\pay\request\wechat\RefundQueryRequest;
use yunwuxin\pay\request\wechat\RefundRequest;
use function yunwuxin\pay\array2xml;
use function yunwuxin\pay\convert_key;
use function yunwuxin\pay\xml2array;

class Wechat extends Channel
{
    const TYPE_NATIVE = 'NATIVE';
    const TYPE_JSAPI  = 'JSAPI';
    const TYPE_APP    = 'APP';

    protected $test = false;

    /** @var Cache */
    protected $cache;

    public function __construct(Cache $cache, $options = [])
    {
        parent::__construct($options);
        $this->cache = $cache;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['app_id', 'mch_id', 'key']);

        $resolver->setDefined(['cert', 'ssl_key']);

        $resolver->setNormalizer('cert', function (Options $options, $value) {
            if (!empty($value) && !is_file($value)) {
                $fn = runtime_path() . 'think-pay-wechat-cert-' . md5($value);
                if (!file_exists($fn)) {
                    file_put_contents($fn, convert_key($value, 'CERTIFICATE'));
                }
                return $fn;
            }

            return $value;
        });

        $resolver->setNormalizer('ssl_key', function (Options $options, $value) {
            if (!empty($value) && !is_file($value)) {
                $fn = runtime_path() . 'think-pay-wechat-ssl-key-' . md5($value);
                if (!file_exists($fn)) {
                    file_put_contents($fn, convert_key($value, 'PRIVATE KEY'));
                }
                return $fn;
            }

            return $value;
        });
    }

    protected function getHttpClientConfig()
    {
        $config = parent::getHttpClientConfig();

        if ($this->getOption('cert') && $this->getOption('ssl_key')) {
            $config = array_merge($config, [
                'cert'    => $this->getOption('cert'),
                'ssl_key' => $this->getOption('ssl_key'),
            ]);
        }

        return $config;
    }

    protected function getSignKey()
    {
        return $this->cache->remember('wechat_sandbox_key', function () {

            $request = $this->createRequest(GetSignKeyRequest::class, $this->getOption('mch_id'));

            $result = $this->sendRequest($request);

            return $result['sandbox_signkey'];
        });
    }

    public function generateSign(array $params): string
    {
        unset($params['sign']);
        ksort($params);
        $query = urldecode(http_build_query($params));
        $query .= "&key={$this->getOption('key')}";
        return strtoupper(md5($query));
    }

    public function query(Payable $charge)
    {
        $request = $this->createRequest(OrderQueryRequest::class, $charge);

        $data = $this->sendRequest($request);

        if ($data['result_code'] == 'SUCCESS' && $data['trade_state'] == 'SUCCESS') {
            $result = new PurchaseResult('wechat', $data['transaction_id'], $data['total_fee'], true, Carbon::parse($data['time_end']), $data);
        } else {
            $result = new PurchaseResult('wechat', null, null, false, null, $data);
        }

        return $result;
    }

    /**
     * 退款
     * @param Refundable $refund
     * @return array
     */
    public function refund(Refundable $refund)
    {
        $request = $this->createRequest(RefundRequest::class, $refund);

        return $this->sendRequest($request);
    }

    public function refundQuery(Refundable $refund)
    {
        $request = $this->createRequest(RefundQueryRequest::class, $refund);

        return $this->sendRequest($request);
    }

    public function completePurchase(Request $request)
    {
        libxml_disable_entity_loader(true);
        $data = xml2array($request->getContent());
        $this->verifySign($this->generateSign($data), $data['sign']);
        $charge = $this->retrieveCharge($data['out_trade_no']);
        if (!$charge->isComplete()) {
            $charge->onComplete(new PurchaseResult('wechat', $data['transaction_id'], $data['total_fee'], $data['result_code'] == 'SUCCESS', Carbon::parse($data['time_end']), $data));
        }
        $return = [
            'return_code' => 'SUCCESS',
            'return_msg'  => 'OK',
        ];
        return response(array2xml($return));
    }

    public function verifySign($data, $sign)
    {
        if ($sign != $data) {
            throw new DomainException('签名验证失败');
        }
    }

    protected function handleResponse(RequestInterface $request, ResponseInterface $response)
    {
        $result = xml2array($response->getBody()->getContents());

        if ($result['return_code'] != 'SUCCESS') {
            throw new DomainException($result['return_msg']);
        }

        if (isset($result['sign'])) {
            $this->verifySign($this->generateSign($result), $request['sign']);
        }
        return $result;
    }

    public function setSandbox()
    {
        $this->options['key'] = $this->getSignKey();
        return parent::setSandbox();
    }
}
