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
use think\Request;
use yunwuxin\pay\Channel;
use yunwuxin\pay\entity\PurchaseResult;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\interfaces\Refundable;
use yunwuxin\pay\request\alipay\TradeQueryRequest;
use yunwuxin\pay\request\alipay\TradeRefundQueryRequest;
use yunwuxin\pay\request\alipay\TradeRefundRequest;
use function yunwuxin\pay\convert_key;

class Alipay extends Channel
{
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

    /**
     * 订单查询
     * @param Payable $charge
     * @return PurchaseResult
     */
    public function query(Payable $charge)
    {
        $request = $this->createRequest(TradeQueryRequest::class, $charge);

        $data = $this->sendRequest($request);

        return new PurchaseResult('alipay', $data['trade_no'], $data['total_amount'] * 100, 'TRADE_SUCCESS' == $data['trade_status'], Carbon::now(), $data);
    }

    /**
     * 退款
     * @param Refundable $refund
     * @return array
     */
    public function refund(Refundable $refund)
    {
        $request = $this->createRequest(TradeRefundRequest::class, $refund);

        return $this->sendRequest($request);
    }

    public function refundQuery(Refundable $refund)
    {
        $request = $this->createRequest(TradeRefundQueryRequest::class, $refund);

        return $this->sendRequest($request);
    }

    public function completePurchase(Request $request)
    {
        $data = $request->post();

        $sign = $data['sign'];

        unset($data['sign'], $data['sign_type']);

        $this->verifySign($this->buildSignContent($data), $sign);

        $charge = $this->retrieveCharge($data['out_trade_no']);
        if (!$charge->isComplete()) {
            $charge->onComplete(new PurchaseResult('alipay', $data['trade_no'], $data['total_amount'] * 100, 'TRADE_SUCCESS' == $data['trade_status'], !empty($data['gmt_payment']) ? Carbon::parse($data['gmt_payment']) : null, $data));
        }
        return response('success');
    }

    protected function buildSignContent($params)
    {
        ksort($params);
        return urldecode(http_build_query($params));
    }

    public function verifySign($data, $sign)
    {
        $key = convert_key($this->getOption('public_key'), 'public key');
        if ('RSA2' == $this->getOption('sign_type')) {
            $result = (bool) openssl_verify($data, base64_decode($sign), $key, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool) openssl_verify($data, base64_decode($sign), $key);
        }
        if (!$result) {
            throw new DomainException('签名验证失败');
        }
    }

    public function generateSign(array $params): string
    {
        $data = $this->buildSignContent($params);
        $key  = convert_key($this->getOption('private_key'), 'RSA PRIVATE key');
        if ("RSA2" == $params['sign_type']) {
            openssl_sign($data, $sign, $key, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $key);
        }
        return base64_encode($sign);
    }

    protected function handleResponse(RequestInterface $request, ResponseInterface $response)
    {
        $method = $request->getBody()['method'];

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
}
