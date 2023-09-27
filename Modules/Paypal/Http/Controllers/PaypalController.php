<?php

namespace Modules\Paypal\Http\Controllers;

use App\Models\Plan;
use App\Models\Order;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rawilk\Settings\Support\Context;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PayPal\Rest\ApiContext;
use Illuminate\Support\Facades\Session;
use Modules\Paypal\Entities\PaypalUtility;
use Modules\Paypal\Events\PaypalPaymentStatus;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{


    // private $_api_context;
    protected $invoiceData;
    public $paypal_mode;
    public $paypal_client_id;
    public $paypal_secret_key;
    public $enable_paypal;
    public $currancy;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function setting(Request $request)
    {

        if (Auth::user()->can('paypal manage')) {
            if ($request->has('paypal_payment_is_on')) {
                $validator = Validator::make($request->all(),
                [
                    'company_paypal_mode' => 'required|string',
                    'company_paypal_client_id' => 'required|string',
                    'company_paypal_secret_key' => 'required|string',
                ]);
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
            }

            $userContext = new Context(['user_id' => Auth::user()->id,'workspace_id'=>getActiveWorkSpace()]);
            if($request->has('paypal_payment_is_on')){
                \Settings::context($userContext)->set('paypal_payment_is_on', $request->paypal_payment_is_on);
                \Settings::context($userContext)->set('company_paypal_mode', $request->company_paypal_mode);
                \Settings::context($userContext)->set('company_paypal_client_id', $request->company_paypal_client_id);
                \Settings::context($userContext)->set('company_paypal_secret_key', $request->company_paypal_secret_key);
            }else{
                \Settings::context($userContext)->set('paypal_payment_is_on', 'off');
            }

            return redirect()->back()->with('success', __('Paypal Setting save successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // get paypal payment setting
    public function paymentConfig($id=null, $workspace=Null)
    {
        if(!empty($id) && empty($workspace))
        {
            $this->currancy  = !empty(company_setting('defult_currancy',$id)) ? company_setting('defult_currancy',$id) : '$';
            $this->enable_paypal = !empty(company_setting('paypal_payment_is_on',$id)) ? company_setting('paypal_payment_is_on',$id) : 'off';

            if(company_setting('company_paypal_mode',$id) == 'live')
            {
                config(
                    [
                        'paypal.live.client_id' => !empty(company_setting('company_paypal_client_id',$id)) ? company_setting('company_paypal_client_id',$id) : '',
                        'paypal.live.client_secret' => !empty(company_setting('company_paypal_secret_key',$id)) ? company_setting('company_paypal_secret_key',$id) : '',
                        'paypal.mode' => !empty(company_setting('company_paypal_mode',$id)) ? company_setting('company_paypal_mode',$id) : '',
                    ]
                );
            }
            else{
                config(
                    [
                        'paypal.sandbox.client_id' => !empty(company_setting('company_paypal_client_id',$id)) ? company_setting('company_paypal_client_id',$id) : '',
                        'paypal.sandbox.client_secret' => !empty(company_setting('company_paypal_secret_key',$id)) ? company_setting('company_paypal_secret_key',$id) : '',
                        'paypal.mode' => !empty(company_setting('company_paypal_mode',$id)) ? company_setting('company_paypal_mode',$id) : '',
                    ]
                );
            }
        }elseif(!empty($id) && !empty($workspace)){
            $this->currancy  = !empty(company_setting('defult_currancy',$id,$workspace)) ? company_setting('defult_currancy',$id,$workspace) : '$';
            $this->enable_paypal = !empty(company_setting('paypal_payment_is_on',$id,$workspace)) ? company_setting('paypal_payment_is_on',$id,$workspace) : 'off';

            if(company_setting('company_paypal_mode',$id,$workspace) == 'live')
            {
                config(
                    [
                        'paypal.live.client_id' => !empty(company_setting('company_paypal_client_id',$id,$workspace)) ? company_setting('company_paypal_client_id',$id,$workspace) : '',
                        'paypal.live.client_secret' => !empty(company_setting('company_paypal_secret_key',$id,$workspace)) ? company_setting('company_paypal_secret_key',$id,$workspace) : '',
                        'paypal.mode' => !empty(company_setting('company_paypal_mode',$id,$workspace)) ? company_setting('company_paypal_mode',$id,$workspace) : '',
                    ]
                );
            }
            else{
                config(
                    [
                        'paypal.sandbox.client_id' => !empty(company_setting('company_paypal_client_id',$id,$workspace)) ? company_setting('company_paypal_client_id',$id,$workspace) : '',
                        'paypal.sandbox.client_secret' => !empty(company_setting('company_paypal_secret_key',$id,$workspace)) ? company_setting('company_paypal_secret_key',$id,$workspace) : '',
                        'paypal.mode' => !empty(company_setting('company_paypal_mode',$id,$workspace)) ? company_setting('company_paypal_mode',$id,$workspace) : '',
                    ]
                );
            }
        }
        else{
            $this->currancy  = !empty(company_setting('defult_currancy')) ? company_setting('defult_currancy') : '$';
            $this->enable_paypal = !empty(company_setting('paypal_payment_is_on')) ? company_setting('paypal_payment_is_on') : 'off';

            if(company_setting('company_paypal_mode') == 'live')
            {
                config(
                    [
                        'paypal.live.client_id' => !empty(company_setting('company_paypal_client_id')) ? company_setting('company_paypal_client_id') : '',
                        'paypal.live.client_secret' => !empty(company_setting('company_paypal_secret_key')) ? company_setting('company_paypal_secret_key') : '',
                        'paypal.mode' => !empty(company_setting('company_paypal_mode')) ? company_setting('company_paypal_mode') : '',
                    ]
                );
            }
            else{
                config(
                    [
                        'paypal.sandbox.client_id' => !empty(company_setting('company_paypal_client_id')) ? company_setting('company_paypal_client_id') : '',
                        'paypal.sandbox.client_secret' => !empty(company_setting('company_paypal_secret_key')) ? company_setting('company_paypal_secret_key') : '',
                        'paypal.mode' => !empty(company_setting('company_paypal_mode')) ? company_setting('company_paypal_mode') : '',
                    ]
                );
            }
        }
    }

    public function invoicePayWithPaypal(Request $request)
    {
        $user    = Auth::user();
        $validator = Validator::make(
            $request->all(),
            ['amount' => 'required|numeric', 'invoice_id' => 'required']
        );
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $invoice_id = $request->input('invoice_id');
        $type = $request->type;
        if($type == 'invoice')
        {
            $invoice = \App\Models\Invoice::find($invoice_id);
            $user_id = $invoice->created_by;
            $workspace = $invoice->workspace;
            $payment_id = $invoice->id;
        }
        elseif($type == 'salesinvoice') {

            $invoice = \Modules\Sales\Entities\SalesInvoice::find($invoice_id);
            $user_id = $invoice->created_by;
            $workspace = $invoice->workspace;
            $payment_id = $invoice->id;

        }
        elseif($type == 'retainer') {

            $invoice = \Modules\Retainer\Entities\Retainer::find($invoice_id);
            $user_id = $invoice->created_by;
            $workspace = $invoice->workspace;
            $payment_id = $invoice->id;
        }

        $this->invoiceData  = $invoice;
        $this->paymentConfig($user_id,$workspace);
        $get_amount = $request->amount;
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        if ($invoice) {
            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                $name = isset($user->name)?$user->name:'public' . " - " . $invoice_id;
                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('invoice.paypal',[$payment_id,$get_amount, $type]),
                        "cancel_url" =>  route('invoice.paypal',[$payment_id,$get_amount, $type]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => $this->currancy = company_setting('defult_currancy', $user_id),

                                "value" => $get_amount
                            ]
                        ]
                    ]
                ]);

                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()->back()->with('error', 'Something went wrong.');
                }
                else {
                    if($request->type == 'invoice'){
                        return redirect()->route('invoice.show', $invoice_id)->with('error', $response['message'] ?? 'Something went wrong.');
                    }
                    elseif($request->type == 'salesinvoice'){
                        return redirect()->route('salesinvoice.show', $invoice_id)->with('error', $response['message'] ?? 'Something went wrong.');
                    }
                    elseif($request->type == 'retainer'){
                        return redirect()->route('retainer.show', $invoice_id)->with('error', $response['message'] ?? 'Something went wrong.');
                    }
                }

                return redirect()->back()->with('error', __('Unknown error occurred'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount,$type)
    {
        if($type == 'invoice')
        {
            $invoice = \App\Models\Invoice::find($invoice_id);
            $this->paymentConfig($invoice->created_by,$invoice->workspace);
            $this->invoiceData  = $invoice;

            if ($invoice) {
                $payment_id = Session::get('paypal_payment_id');
                Session::forget('paypal_payment_id');
                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->route('invoice.show', $invoice_id)->with('error', __('Payment failed'));
                }
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                try {
                    $invoice_payment                 = new \App\Models\InvoicePayment();
                    $invoice_payment->invoice_id     = $invoice_id;
                    $invoice_payment->date           = Date('Y-m-d');
                    $invoice_payment->account_id     = 0;
                    $invoice_payment->payment_method = 0;
                    $invoice_payment->amount         = $amount;
                    $invoice_payment->order_id       = $orderID;
                    $invoice_payment->currency       = $this->currancy;
                    $invoice_payment->payment_type = 'PAYPAL';
                    $invoice_payment->save();

                    $due     = $invoice->getDue();
                    if ($due <= 0) {
                        $invoice->status = 4;
                        $invoice->save();
                    } else {
                        $invoice->status = 3;
                        $invoice->save();
                    }
                    if(module_is_active('Account'))
                    {
                        //for customer balance update
                        \Modules\Account\Entities\AccountUtility::updateUserBalance('customer', $invoice->customer_id, $invoice_payment->amount, 'debit');
                    }
                    event(new PaypalPaymentStatus($invoice,$type,$invoice_payment));

                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));

                } catch (\Exception $e) {
                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success',$e->getMessage());
                }
            } else {
                return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice not found.'));
            }

        }
        elseif($type == 'salesinvoice')
        {
            $salesinvoice = \Modules\Sales\Entities\SalesInvoice::find($invoice_id);
            $this->paymentConfig($salesinvoice->created_by,$salesinvoice->workspace);

            $this->invoiceData  = $salesinvoice;
            if ($salesinvoice)
            {
                $payment_id = Session::get('paypal_payment_id');
                Session::forget('paypal_payment_id');
                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->route('salesinvoice.show', $invoice_id)->with('error', __('Payment failed'));
                }

                try {
                    $salesinvoice_payment                 = new \Modules\Sales\Entities\SalesInvoicePayment();
                    $salesinvoice_payment->invoice_id     = $invoice_id;
                    $salesinvoice_payment->transaction_id = app('Modules\Sales\Http\Controllers\SalesInvoiceController')->transactionNumber($salesinvoice->created_by);
                    $salesinvoice_payment->date           = Date('Y-m-d');
                    $salesinvoice_payment->amount         = $amount;
                    $salesinvoice_payment->client_id      = 0;
                    $salesinvoice_payment->payment_type   = 'PAYPAL';
                    $salesinvoice_payment->save();
                    $due     = $salesinvoice->getDue();
                    if ($due <= 0) {
                        $salesinvoice->status = 3;
                        $salesinvoice->save();
                    } else {
                        $salesinvoice->status = 2;
                        $salesinvoice->save();
                    }

                    event(new PaypalPaymentStatus($salesinvoice,$type,$salesinvoice_payment));

                    return redirect()->route('pay.salesinvoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Sales Invoice paid Successfully!'));

                } catch (\Exception $e) {

                    return redirect()->route('pay.salesinvoice',  \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success',$e->getMessage());
                }
            } else {

                return redirect()->route('pay.salesinvoice',  \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Sales Invoice not found.'));
            }
        }


        elseif($type == 'retainer')
        {
            $retainer = \Modules\Retainer\Entities\Retainer::find($invoice_id);
            $this->paymentConfig($retainer->created_by,$retainer->workspace);

            $this->invoiceData  = $retainer;
            if ($retainer)
            {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $payment_id = Session::get('paypal_payment_id');
                Session::forget('paypal_payment_id');
                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->route('retainer.show', $invoice_id)->with('error', __('Payment failed'));
                }

                try {
                    $retainer_payment                 = new \Modules\Retainer\Entities\RetainerPayment();
                    $retainer_payment->retainer_id     = $invoice_id;
                    $retainer_payment->date           = Date('Y-m-d');
                    $retainer_payment->account_id     = 0;
                    $retainer_payment->payment_method = 0;
                    $retainer_payment->amount         = $amount;
                    $retainer_payment->order_id       = $orderID;
                    $retainer_payment->currency       = $this->currancy;
                    $retainer_payment->payment_type = 'PAYPAL';
                    $retainer_payment->save();
                    $due     = $retainer->getDue();
                    if ($due <= 0) {
                        $retainer->status = 3;
                        $retainer->save();
                    } else {
                        $retainer->status = 2;
                        $retainer->save();
                    }
                    //for customer balance update
                    \Modules\Retainer\Entities\RetainerUtility::updateUserBalance('customer', $retainer->customer_id, $retainer_payment->amount, 'debit');

                    event(new PaypalPaymentStatus($retainer,$type,$retainer_payment));

                    return redirect()->route('pay.retainer', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Retainer paid Successfully!'));

                } catch (\Exception $e) {
                    return redirect()->route('pay.retainer',  \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success',$e->getMessage());
                }
            } else {

                return redirect()->route('pay.retainer',  \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Retainer not found.'));
            }
        }
    }

    public function planPayWithPaypal(Request $request)
    {
        $plan = Plan::find($request->plan_id);
        $user_counter = !empty($request->user_counter_input) ? $request->user_counter_input : 0;
        $workspace_counter = !empty($request->workspace_counter_input) ? $request->workspace_counter_input : 0;
        $user_module = !empty($request->user_module_input) ? $request->user_module_input : '0';
        $duration = !empty($request->time_period) ? $request->time_period : 'Month';
        $user_module_price = 0;
        if(!empty($user_module) && $plan->custom_plan == 1)
        {
            $user_module_array =    explode(',',$user_module);
            foreach ($user_module_array as $key => $value)
            {
                $temp = ($duration == 'Year') ? ModulePriceByName($value)['yearly_price'] : ModulePriceByName($value)['monthly_price'];
                $user_module_price = $user_module_price + $temp;
            }
        }
        $user_price = 0;
        $temp = ($duration == 'Year') ? $plan->price_per_user_yearly : $plan->price_per_user_monthly;
        if($user_counter > 0)
        {

            $user_price = $user_counter * $temp;
        }
        $workspace_price = 0;
        if($workspace_counter > 0)
        {
            $workspace_price = $workspace_counter * $temp;
        }
        $plan_price = ($duration == 'Year') ? $plan->package_price_yearly : $plan->package_price_monthly;
        $counter = [
            'user_counter'=>$user_counter,
            'workspace_counter'=>$workspace_counter,
        ];
        if(admin_setting('company_paypal_mode') == 'live')
        {
            config(
                [
                    'paypal.live.client_id' => !empty(admin_setting('company_paypal_client_id')) ? admin_setting('company_paypal_client_id') : '',
                    'paypal.live.client_secret' => !empty(admin_setting('company_paypal_secret_key')) ? admin_setting('company_paypal_secret_key') : '',
                    'paypal.mode' => !empty(admin_setting('company_paypal_mode')) ? admin_setting('company_paypal_mode') : '',
                ]
            );
        }
        else{
            config(
                [
                    'paypal.sandbox.client_id' => !empty(admin_setting('company_paypal_client_id')) ? admin_setting('company_paypal_client_id') : '',
                    'paypal.sandbox.client_secret' => !empty(admin_setting('company_paypal_secret_key')) ? admin_setting('company_paypal_secret_key') : '',
                    'paypal.mode' => !empty(admin_setting('company_paypal_mode')) ? admin_setting('company_paypal_mode') : '',
                ]
            );
        }
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        if ($plan) {
            try {
                $price     = $plan_price + $user_module_price + $user_price + $workspace_price;

                if($price <= 0){
                    $assignPlan= DirectAssignPlan($plan->id,$duration,$user_module,$counter,'PAYPAL');
                    if($assignPlan['is_success']){
                       return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
                    }else{
                       return redirect()->route('plans.index')->with('error', __('Something went wrong, Please try again,'));
                    }
                }
                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('plan.get.paypal.status', [
                                    $plan->id,
                                    'amount' => $price,
                                    'user_module' => $user_module,
                                    'counter' => $counter,
                                    'duration' => $duration,
                    ]),
                        "cancel_url" =>  route('plan.get.paypal.status', [
                            $plan->id,
                                    'amount' => $price,
                                    'user_module' => $user_module,
                                    'counter' => $counter,
                                    'duration' => $duration,

                        ]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => admin_setting('defult_currancy'),
                                "value" => $price,

                            ]
                        ]
                    ]
                ]);
                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()
                        ->route('plans.index', \Illuminate\Support\Facades\Crypt::encrypt($plan->id))
                        ->with('error', 'Something went wrong. OR Unknown error occurred');
                } else {
                    return redirect()
                        ->route('plans.index', \Illuminate\Support\Facades\Crypt::encrypt($plan->id))
                        ->with('error', $response['message'] ?? 'Something went wrong.');
                }

            } catch (\Exception $e) {
                return redirect()->route('plans.index')->with('error', __($e->getMessage()));
            }
        } else {
            return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function planGetPaypalStatus(Request $request, $plan_id)
    {
        $user = Auth::user();
        $plan = Plan::find($plan_id);
        if ($plan)
        {
            if(admin_setting('company_paypal_mode') == 'live')
            {
                config(
                    [
                        'paypal.live.client_id' => !empty(admin_setting('company_paypal_client_id')) ? admin_setting('company_paypal_client_id') : '',
                        'paypal.live.client_secret' => !empty(admin_setting('company_paypal_secret_key')) ? admin_setting('company_paypal_secret_key') : '',
                        'paypal.mode' => !empty(admin_setting('company_paypal_mode')) ? admin_setting('company_paypal_mode') : '',
                    ]
                );
            }
            else{
                config(
                    [
                        'paypal.sandbox.client_id' => !empty(admin_setting('company_paypal_client_id')) ? admin_setting('company_paypal_client_id') : '',
                        'paypal.sandbox.client_secret' => !empty(admin_setting('company_paypal_secret_key')) ? admin_setting('company_paypal_secret_key') : '',
                        'paypal.mode' => !empty(admin_setting('company_paypal_mode')) ? admin_setting('company_paypal_mode') : '',
                    ]
                );
            }

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->capturePaymentOrder($request['token']);
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            try {
                if (isset($response['status']) && $response['status'] == 'COMPLETED')
                {
                    if ($response['status'] == 'COMPLETED') {
                        $statuses = __('succeeded');
                    }

                    $order = Order::create(
                        [
                            'order_id' => $orderID,
                            'name' => null,
                            'email' => null,
                            'card_number' => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name' =>  !empty($plan->name) ? $plan->name :'Basic Package',
                            'plan_id' => $plan->id,
                            'price' => !empty($request->amount)?$request->amount:0,
                            'price_currency' => admin_setting('defult_currancy'),
                            'txn_id' => '',
                            'payment_type' => __('PAYPAL'),
                            'payment_status' =>$statuses,
                            'receipt' => null,
                            'user_id' => $user->id,
                        ]
                    );
                    $type = 'Subscription';
                    $user = User::find($user->id);
                    $assignPlan = $user->assignPlan($plan->id,$request->duration,$request->user_module,$request->counter);
                    $value = Session::get('user-module-selection');

                    event(new PaypalPaymentStatus($plan,$type,$order));

                    if(!empty($value))
                    {
                        Session::forget('user-module-selection');
                    }

                    if ($assignPlan['is_success']) {
                        return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
                    } else {
                        return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
                    }

                } else {
                    return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
                }

            } catch (\Exception $e) {
                return redirect()->route('plans.index')->with('error', __('Transaction has been failed.'));
            }

        } else {
            return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
        }
    }
}
