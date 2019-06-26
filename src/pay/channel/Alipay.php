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

use Jenssegers\Date\Date;
use think\Request;
use yunwuxin\alipay\Client;
use yunwuxin\pay\Channel;
use yunwuxin\pay\entity\PurchaseResult;
use yunwuxin\pay\entity\TransferResult;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\interfaces\Refundable;
use yunwuxin\pay\interfaces\Transferable;

class Alipay extends Channel
{
    /** @var Client */
    protected $client;

    public function __construct($config)
    {
        $this->client = new Client($config);
    }

    public function setTest()
    {
        $this->client->setTest();
    }

    /**
     * 订单查询
     * @param Payable $charge
     * @return PurchaseResult
     */
    public function query(Payable $charge)
    {
        $bizContent = [
            'out_trade_no' => $charge->getTradeNo(),
        ];

        $method = 'alipay.trade.query';

        $data = $this->client->execute($method, $bizContent);

        return new PurchaseResult('alipay', $data['trade_no'], $data['total_amount'] * 100, 'TRADE_SUCCESS' == $data['trade_status'], Date::now(), $data);
    }

    /**
     * 退款
     * @param Refundable $refund
     * @return array
     */
    public function refund(Refundable $refund)
    {
        $bizContent = array_filter([
            'out_trade_no'   => $refund->getCharge()->getTradeNo(),
            'refund_amount'  => $refund->getAmount() / 100,
            'refund_reason'  => $refund->getExtra('refund_reason'),
            'out_request_no' => $refund->getRefundNo(),
            'operator_id'    => $refund->getExtra('operator_id'),
            'store_id'       => $refund->getExtra('store_id'),
            'terminal_id'    => $refund->getExtra('terminal_id'),
        ]);

        $method = 'alipay.trade.refund';

        return $this->client->execute($method, $bizContent);
    }

    public function refundQuery(Refundable $refund)
    {
        $bizContent = [
            'out_trade_no'   => $refund->getCharge()->getTradeNo(),
            'out_request_no' => $refund->getRefundNo(),
        ];
        $method     = 'alipay.trade.fastpay.refund.query';

        return $this->client->execute($method, $bizContent);
    }

    public function transfer(Transferable $transfer)
    {
        $bizContent = array_filter([
            'out_biz_no'      => $transfer->getTransferNo(),
            'payee_type'      => $transfer->getExtra('payee_type'),
            'payee_account'   => $transfer->getAccount(),
            'amount'          => $transfer->getAmount() / 100,
            'payer_show_name' => $transfer->getExtra('payer_show_name'),
            'payee_real_name' => $transfer->getRealName(),
            'remark'          => $transfer->getRemark(),
        ]);

        $method = 'alipay.fund.trans.toaccount.transfer';

        $result = $this->client->execute($method, $bizContent);

        return new TransferResult($result['order_id'], $result['pay_date']);
    }

    public function completePurchase(Request $request)
    {
        $data = $request->post();

        $sign = $data['sign'];

        unset($data['sign'], $data['sign_type']);

        $this->client->verifySign($this->client->buildSignContent($data), $sign);

        $charge = $this->retrieveCharge($data['out_trade_no']);
        if (!$charge->isComplete()) {
            $charge->onComplete(new PurchaseResult('alipay', $data['trade_no'], $data['total_amount'] * 100, 'TRADE_SUCCESS' == $data['trade_status'], !empty($data['gmt_payment']) ? Date::parse($data['gmt_payment']) : null, $data));
        }
        return response('success');
    }

    public function buildAppParams(Payable $charge)
    {
        $bizContent = array_filter([
            'body'                 => $charge->getBody(),
            'subject'              => $charge->getSubject(),
            'out_trade_no'         => $charge->getTradeNo(),
            'timeout_express'      => $charge->getExpire(function (Date $date) {
                //todo
            }),
            'total_amount'         => $charge->getAmount() / 100,
            'seller_id'            => $charge->getExtra('seller_id'),
            'product_code'         => 'QUICK_MSECURITY_PAY',
            'goods_type'           => $charge->getExtra('goods_type'),
            'passback_params'      => $charge->getExtra('passback_params'),
            'promo_params'         => $charge->getExtra('promo_params'),
            'extend_params'        => $charge->getExtra('extend_params'),
            'enable_pay_channels'  => $charge->getExtra('enable_pay_channels'),
            'disable_pay_channels' => $charge->getExtra('disable_pay_channels'),
            'store_id'             => $charge->getExtra('store_id'),
        ]);

        return $this->client->buildParams('alipay.trade.app.pay', $bizContent, ['notify_url' => $this->notifyUrl]);
    }

    public function wapPay(Payable $charge)
    {
        $bizContent = array_filter([
            'body'                 => $charge->getBody(),
            'subject'              => $charge->getSubject(),
            'out_trade_no'         => $charge->getTradeNo(),
            'timeout_express'      => $charge->getExpire(function (Date $date) {
                //todo
            }),
            'total_amount'         => $charge->getAmount() / 100,
            'seller_id'            => $charge->getExtra('seller_id'),
            'auth_token'           => $charge->getExtra('auth_token'),
            'product_code'         => 'QUICK_WAP_PAY',
            'goods_type'           => $charge->getExtra('goods_type'),
            'passback_params'      => $charge->getExtra('passback_params'),
            'promo_params'         => $charge->getExtra('promo_params'),
            'extend_params'        => $charge->getExtra('extend_params'),
            'enable_pay_channels'  => $charge->getExtra('enable_pay_channels'),
            'disable_pay_channels' => $charge->getExtra('disable_pay_channels'),
            'store_id'             => $charge->getExtra('store_id'),
        ]);

        $params = $this->client->buildParams('alipay.trade.wap.pay', $bizContent, [
            'notify_url' => $this->notifyUrl,
            'return_url' => $charge->getExtra('return_url'),
        ]);

        return sprintf('%s?%s', $this->client->endpoint(), http_build_query($params));
    }

    public function preCreate(Payable $charge)
    {
        $bizContent = array_filter([
            'out_trade_no'          => $charge->getTradeNo(),
            'seller_id'             => $charge->getExtra('seller_id'),
            'total_amount'          => $charge->getAmount() / 100,
            'discountable_amount'   => $charge->getExtra('discountable_amount'),
            'undiscountable_amount' => $charge->getExtra('undiscountable_amount'),
            'buyer_logon_id'        => $charge->getExtra('buyer_logon_id'),
            'subject'               => $charge->getSubject(),
            'body'                  => $charge->getBody(),
            'goods_detail'          => $charge->getExtra('goods_detail'),
            'operator_id'           => $charge->getExtra('operator_id'),
            'store_id'              => $charge->getExtra('store_id'),
            'terminal_id'           => $charge->getExtra('terminal_id'),
            'extend_params'         => $charge->getExtra('extend_params'),
            'timeout_express'       => $charge->getExpire(function (Date $date) {
                //todo
            }),
            'royalty_info'          => $charge->getExtra('royalty_info'),
            'sub_merchant'          => $charge->getExtra('sub_merchant'),
            'alipay_store_id'       => $charge->getExtra('alipay_store_id'),
        ]);

        $method = 'alipay.trade.precreate';

        $extra = ['notify_url' => $this->notifyUrl];

        return $this->client->execute($method, $bizContent, $extra);
    }

}
