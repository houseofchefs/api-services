<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Models\Payment;
use App\Traits\CommonQueries;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Razorpay\Api\Order;

class OrderController extends Controller
{
    private $constant;

    private $http;

    use ValidationTraits, ResponseTraits, CommonQueries;

    /**
     * constructor $middleware
     */
    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    /**
     * @api createOrder
     * @route #middleware customer only
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), $this->createOrderValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        //Modules #id
        $module = $this->getModuleIdBasedOnCode($this->constant::ORDER_SUCCESS);
        $count = Orders::count();
        $order = Orders::create(array_merge(
            $request->except('product_id'),
            array(
                "status"        => $module,
                "item_count"    => count($request->get('product_id')),
                "order_no"      => "HOC0000" . $count + 1,
                "customer_id"   => auth($this->constant::CUSTOMER_GUARD)->user()->id
            )
        ));

        DB::transaction(function () use ($order, $request) {
            // Order Details $store func
            if (count($request->get('product_id')) > 0) {
                foreach ($request->get('product_id') as $menu) {
                    $details = [
                        "menu_id"   => $menu["menu_id"],
                        "quantity"  => $menu["quantity"],
                        "order_id"  => $order->id
                    ];
                    OrderDetails::create($details);
                }
            }
        });
        $paymentStatus = $this->getModuleIdBasedOnCode('PS01');
        $active = $this->getModuleIdBasedOnCode('CS01');
        if (!$request->cod) {
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            $razorpay = $api->order->create([
                'receipt'           => $order->id,
                'amount'            => $request->price * 100,
                'currency'          => 'INR',
                'payment_capture'   => 1
            ]);
            $paymentData = [
                "customer_id"           => auth($this->constant::CUSTOMER_GUARD)->user()->id,
                "order_id"              => $order->id,
                "amount"                => $request->price,
                "razorpay_order_id"     => $razorpay->id,
                "razorpay_receipt_id"   => $razorpay->receipt,
                "status"                => $active,
                "payment_status"        => $paymentStatus,
                "created_at"            => Carbon::now()
            ];
        } else {
            $paymentData = [
                "customer_id"           => auth($this->constant::CUSTOMER_GUARD)->user()->id,
                "order_id"              => $order->id,
                "amount"                => $request->price,
                "payment_method"        => "Cash on Delivery",
                "status"                => $active,
                "payment_status"        => $paymentStatus,
                "created_at"            => Carbon::now()
            ];
        }

        $payment = Payment::create($paymentData);
        $address = Address::where('id', $request->address_id)->first();
        $order['address'] = $address;
        $order['payment'] = $payment;
        return $this->successResponse(true, $order, $this->constant::ORDER_CREATED, $this->http::CREATED);
    }

    /**
     * @api orderList
     * @route #middleware admin only
     */
    public function orderList()
    {
        $order = Orders::with(['details.menu', 'status', 'payments.status'])->orderBy('id','desc')->paginate();
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    /**
     * @api orderDetails
     * @route #middleware admin & customer
     */
    public function orderDetails(String $id)
    {
        $order = Orders::with(['details.menu', 'status', 'address', 'payments.status'])->where('id', $id)->first();
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    public function orderListForCustomer()
    {
        # code...
        $auth = auth($this->constant::CUSTOMER_GUARD)->user()->id;
        $order = Orders::where('customer_id', $auth)->with(['status', 'payments.status', 'details.menu', 'vendor'])->orderBy('id','desc')->paginate(10);
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    public function orderCancel(String $id)
    {
        $modules = $this->getModuleIdBasedOnCode('OS03');
        $order = Orders::where('id', $id)->update(['status' => $modules]);
        return $this->successResponse(true, $order, $this->constant::UPDATED_SUCCESS);
    }

    public function vendorBasedOrderList($id, $code)
    {
        $modules = $this->getModuleIdBasedOnCode($code);
        $order = Orders::with(['customers', 'payments.status', 'details.menu', 'vendor', 'address', 'status'])->where('vendor_id', $id)->where('status', $modules)->orderBy('id','desc')->paginate(10);
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    public function customerBasedOrderList($code)
    {
        $modules = $this->getModuleIdBasedOnCode($code);
        $id = auth($this->constant::CUSTOMER_GUARD)->user()->id;
        $order = Orders::with(['customers', 'payments.status', 'details.menu', 'vendor', 'address', 'status'])->where('customer_id', $id)->where('status', $modules)->orderBy('id','desc')->paginate(10);
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    public function nextAction($id, $code)
    {
        $modules = $this->getModuleIdBasedOnCode($code);
        $order = Orders::where('id', $id)->update(['status' => $modules]);
        return $this->successResponse(true, $order, $this->constant::UPDATED_SUCCESS);
    }

    public function updatePayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->updatePaymentValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        $payment = Payment::where("order_id", $id)->where('razorpay_order_id', $request->razorpay_order_id)->first();
        $order = Orders::where("id", $id)->first();

        if ($payment && $order) {
            $cancel = $this->getModuleIdBasedOnCode('PS02');
            $success = $this->getModuleIdBasedOnCode('PS03');
            // $orderStatus = $this->getModuleIdBasedOnCode('OS02');

            // $order->status = $orderStatus;
            // $order->save();

            $payment->razorpay_signature = $request->razorpay_signature;
            $payment->payment_method = $request->payment_method;
            $payment->razorpay_payment_id = $request->razorpay_payment_id;
            $payment->payment_status = $request->capture ? $success : $cancel;
            $payment->save();
        }
        return $this->successResponse(true, $payment, $this->constant::UPDATED_SUCCESS);
    }
}
