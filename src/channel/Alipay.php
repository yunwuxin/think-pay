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
use RuntimeException;
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
        $resolver->setRequired(['app_id', 'alipay_public_key', 'app_private_key']);

        $resolver->setNormalizer('alipay_public_key', function (Options $options, $value) {
            if (is_file($value)) {
                $value = file_get_contents($value);
            }

            return $value;
        });

        $resolver->setNormalizer('app_private_key', function (Options $options, $value) {
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

        return new PurchaseResult($this->getName(), $data['trade_no'], $data['total_amount'] * 100, 'TRADE_SUCCESS' == $data['trade_status'], Carbon::now(), $data);
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
        $data = $request->post('', null, null);

        $sign = $data['sign'];

        unset($data['sign'], $data['sign_type']);

        $this->verifySign($this->buildSignContent($data), $sign);

        $charge = $this->retrieveCharge($data['out_trade_no']);
        if (!$charge->isComplete()) {
            $charge->onComplete(new PurchaseResult($this->getName(), $data['trade_no'], $data['total_amount'] * 100, 'TRADE_SUCCESS' == $data['trade_status'], !empty($data['gmt_payment']) ? Carbon::parse($data['gmt_payment']) : null, $data));
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
        $key = convert_key($this->getOption('alipay_public_key'), 'public key');
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
        $key  = convert_key($this->getOption('app_private_key'), 'RSA PRIVATE key');
        if ("RSA2" == $params['sign_type']) {
            openssl_sign($data, $sign, $key, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $key);
        }
        return base64_encode($sign);
    }

    protected function handleResponse(RequestInterface $request, ResponseInterface $response)
    {
        $uri   = $request->getUri();
        $query = parse_url($uri, PHP_URL_QUERY);
        parse_str($query, $body);

        $method = $body['method'];

        $content = $response->getBody()->getContents();

        $response = json_decode($content, true);

        if ($response === null) {
            echo json_last_error_msg();
            throw new RuntimeException(json_last_error_msg());
        }

        $key = str_replace('.', '_', $method) . '_response';
        if (!isset($response[$key])) {
            throw new RuntimeException('系统繁忙');
        }

        $result = $response[$key];

        $this->verifySign(json_encode($result, JSON_UNESCAPED_UNICODE), $response['sign'] ?? '');

        if (empty($result['code']) || $result['code'] != 10000) {
            throw new DomainException(isset($result['sub_msg']) ? $result['sub_msg'] : $result['msg']);
        }

        return $result;
    }
}
