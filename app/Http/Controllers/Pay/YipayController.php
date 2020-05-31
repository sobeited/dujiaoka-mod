<?php

namespace App\Http\Controllers\Pay;

use App\Models\Pays;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class YipayController extends PayController
{
    // 这里自己配置请求网关
    const PAY_URI = 'http://pay.iq.ci/';

    public function gateway($payway, $oid)
    {
        $check = $this->checkOrder($payway, $oid);
        if ($check !== true) {
            return $this->error($check);
        }
        //组装支付参数
        $parameter = [
            'pid' => (int)$this->payInfo['merchant_id'],
            'type' => $this->payInfo['pay_check'],
            'out_trade_no' => $this->orderInfo['order_id'],
            'return_url' => site_url() . $this->payInfo['pay_handleroute'] . '/return_url',
            'notify_url' => site_url() . $this->payInfo['pay_handleroute'] . '/notify_url',
            'name' => '在线支付-' . $this->orderInfo['order_id'],
            'money' => (float)$this->orderInfo['actual_price'],
            'sign' => $this->payInfo['merchant_pem'],
            'sign_type' => 'MD5',
            'sitename' => config('webset.title')
        ];
        ksort($parameter); //重新排序$data数组
        reset($parameter); //内部指针指向数组中的第一个元素
        $sign = '';
        foreach ($parameter as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            if ($key != 'sign') {
                if ($sign != '') {
                    $sign .= "&";
                }
                $sign .= "$key=$val"; //拼接为url参数形式
            }
        }

        $sign = md5($sign . $this->payInfo['merchant_pem']);//密码追加进入开始MD5签名
        $parameter['sign'] = $sign;
        //待请求参数数组
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . self::PAY_URI . "submit.php' method='get'>";

        foreach ($parameter as $key => $val) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value=''></form>";
        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }

    public function notifyUrl(Request $request)
    {
        $data = $request->all();
        $cacheord = json_decode(Redis::hget('PENDING_ORDERS_LIST', $data['out_trade_no']), true);
        if (!$cacheord) {
            return 'fail';
        }
        $payInfo = Pays::where('id', $cacheord['pay_way'])->first()->toArray();
        ksort($data); //重新排序$data数组
        reset($data); //内部指针指向数组中的第一个元素
        $sign = '';
        foreach ($data as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            if ($key != 'sign') {
                if ($sign != '') {
                    $sign .= "&";
                }
                $sign .= "$key=$val"; //拼接为url参数形式
            }
        }

        if (!$data['trade_no'] || md5($sign . $payInfo['merchant_pem']) != $data['sign']) { //不合法的数据
            return 'fail';  //返回失败 继续补单
        } else { //合法的数据
            //业务处理
            $this->successOrder($data['out_trade_no'], $data['trade_no'], $data['money']);
            return 'success';
        }
    }

    public function returnUrl(Request $request)
    {
        $oid = $request->get('out_trade_no');
        $cacheord = json_decode(Redis::hget('PENDING_ORDERS_LIST', $oid), true);
        if (!$cacheord) {
            //可能已异步回调成功，跳转
            return redirect(site_url() . 'searchOrderById?order_id=' . $oid);
        } else {
            $payInfo = Pays::where('id', $cacheord['pay_way'])->first()->toArray();
            $data = json_decode($this->get(self::PAY_URI . "api.php?act=order&pid=" . $payInfo['merchant_id'] . "&key=" . $payInfo['merchant_pem'] . "&out_trade_no=" . $oid), true);
            try {
                if ($data['status'] == 1 && $data['trade_no']) {
                    $this->successOrder($oid, $data['trade_no'], $data['money']);
                    return redirect(site_url() . 'searchOrderById?order_id=' . $oid);
                }

            } catch (\Exception $e) {
                return $this->error('易支付异常：' . $e->getMessage());
            }
        }
    }


    public function get($url)
    {


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名

        //参数为1表示传输数据，为0表示直接输出显示。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //参数为0表示不带头文件，为1表示带头文件
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);


        return $output;
    }
}