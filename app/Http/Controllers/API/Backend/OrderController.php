<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Customers;
use App\Models\Menu;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\Vendor;
use App\Models\VendorRating;
use App\Traits\CommonQueries;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;

class OrderController extends Controller
{

    use ValidationTraits, ResponseTraits, CommonQueries;

    /**
     * @api createOrder
     * @route #middleware customer only
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), $this->createOrderValidator());
        // If validator fails it will #returns
        // if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        $agent = $request->userAgent();
        if ($validator->fails()) {
            if (str_contains($agent, 'Mobile')) {
                $errors = $validator->errors();
                $errorResponse['error'] = $errors->first();
                return $this->errorResponse(false, $errors->first(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
            }
            return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        }
        //Modules #id
        $module = $this->getModuleIdBasedOnCode(Constants::ORDER_SUCCESS);
        $count = Orders::count();
        $order = Orders::create(array_merge(
            $request->except('product_id'),
            array(
                "status"        => $module,
                "item_count"    => count($request->get('product_id')),
                "order_no"      => "HOC0000" . $count + 1,
                "customer_id"   => $request->customer_id
            )
        ));

        // Order Details $store func
        if (count($request->get('product_id')) > 0) {
            $requestedMenu = $request->get('product_id');
            foreach ($requestedMenu as $menu) {
                $details = [
                    "menu_id"   => $menu["menu_id"],
                    "quantity"  => $menu["quantity"],
                    "order_id"  => $order->id
                ];
                OrderDetails::create($details);
            }
        }
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
                "customer_id"           => $request->customer_id,
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
                "customer_id"           => $request->customer_id,
                "order_id"              => $order->id,
                "amount"                => $request->price,
                "payment_method"        => "Cash on Delivery",
                "status"                => $active,
                "payment_status"        => $paymentStatus,
                "created_at"            => Carbon::now()
            ];
        }
        // Reduce if the points used by the customer
        if ($request->get('usePoints')) {
            $customer = Customers::where('id', $request->customer_id)->first();
            if ($customer && $customer->points > 0 && $request->get('points') <= $customer->points) {
                $customer->points = $customer->points - $request->get('points');
                $customer->save();
            }
        }
        $payment = Payment::create($paymentData);
        $address = Address::where('id', $request->address_id)->first();
        $order['address'] = $address;
        $order['payment'] = $payment;
        return $this->successResponse(true, $order, Constants::ORDER_CREATED, HTTPStatusCode::CREATED);
    }

    /**
     * @api orderList
     * @route #middleware admin only
     */
    public function orderList(Request $request)
    {
        $order = Orders::when($request->get('type') == "reviews", function ($q) {
            $q->where('isRated', 1);
        })->with(['details.menu', 'status', 'payments.status', 'customers', 'vendor'])->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse(true, $order, Constants::GET_SUCCESS);
    }

    /**
     * @api orderDetails
     * @route #middleware admin & customer
     */
    public function orderDetails(String $id)
    {
        $order = Orders::with(['details.menu', 'status', 'address', 'payments.status'])->where('id', $id)->first();
        return $this->successResponse(true, $order, Constants::GET_SUCCESS);
    }

    public function orderListForCustomer()
    {
        # code...
        $auth = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $order = Orders::where('customer_id', $auth)->with(['status', 'payments.status', 'details.menu', 'vendor'])->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse(true, $order, Constants::GET_SUCCESS);
    }

    public function orderCancel(String $id)
    {
        $modules = $this->getModuleIdBasedOnCode('OS03');
        $order = Orders::where('id', $id)->update(['status' => $modules]);
        return $this->successResponse(true, $order, Constants::UPDATED_SUCCESS);
    }

    public function vendorBasedOrderList($id, $code)
    {
        $modules = $this->getModuleIdBasedOnCode($code);
        $order = Orders::with(['customers', 'payments.status', 'details.menu', 'vendor', 'address', 'status'])->where('vendor_id', $id)->where('status', $modules)->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse(true, $order, Constants::GET_SUCCESS);
    }

    public function customerBasedOrderList($code)
    {
        $modules = $this->getModuleIdBasedOnCode($code);
        $id = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $order = Orders::with(['customers', 'payments.status', 'details.menu', 'vendor', 'address', 'status'])->where('customer_id', $id)->where('status', $modules)->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse(true, $order, Constants::GET_SUCCESS);
    }

    public function nextAction($id, $code)
    {
        $modules = $this->getModuleIdBasedOnCode($code);
        $order = Orders::where('id', $id)->update(['status' => $modules]);
        return $this->successResponse(true, $order, Constants::UPDATED_SUCCESS);
    }

    public function updatePayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->updatePaymentValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

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
        return $this->successResponse(true, $payment, Constants::UPDATED_SUCCESS);
    }

    public function getOrderReviewList($id)
    {
        $order = Orders::where('id', $id)->with(['details.menu', 'vendor'])->first();
        return $this->successResponse(true, $order, Constants::GET_SUCCESS);
    }

    public function orderRating(Request $request, $id)
    {
        $auth = auth(Constants::CUSTOMER_GUARD)->user()->id;

        if ($request->get('rating') > 0) {
            $exist = VendorRating::where('customer_id', $auth)->first();
            if ($exist) {
                $vendorRating = $exist;
            } else {
                $vendorRating = new VendorRating();
            }
            // Vendor Rating Store
            $vendorRating->vendor_id = $request->get('vendor_id');
            $vendorRating->rating = $request->get('rating');
            $vendorRating->customer_id = $auth;
            $vendorRating->save();

            // Sum and Count
            $sum = VendorRating::where('vendor_id', $request->get('vendor_id'))->sum('rating');
            $count = VendorRating::where('vendor_id', $request->get('vendor_id'))->count();

            // Store Count and Rating in vendor
            $vendor = Vendor::where('id', $request->get('vendor_id'))->first();
            $vendor->rating = $sum / $count;
            $vendor->ucount = $count;
            $vendor->save();
        }

        // Create Rating for Menu
        if (count($request->get('menu')) > 0) {
            foreach ($request->get('menu') as $menu) {
                $orderDetail = OrderDetails::where('id', $menu["details_id"])->first();
                $orderDetail->ratings = $menu['rating'];
                $orderDetail->save();

                $sum = OrderDetails::whereNotNull('ratings')->where('menu_id', $menu['menu_id'])->sum('ratings');
                $count = OrderDetails::whereNotNull('ratings')->where('menu_id', $menu['menu_id'])->count();
                $menu = Menu::where('id', $menu['menu_id'])->first();
                $menu->rating = $sum / $count;
                $menu->ucount = $count;
                $menu->save();
            }
        }
        Orders::where('id', $id)->update(['isRated' => 1]);

        return $this->successResponse(true, "", Constants::UPDATED_SUCCESS);
    }
}
