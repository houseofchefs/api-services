<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\Address;
use App\Models\Modules;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\PreBooking;
use App\Models\PreBookingDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;

class PreBookingController extends Controller
{
    use ResponseTraits, ValidationTraits, CommonQueries;

    public function store(Request $request)
    {
        # code...
        $id = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $validator = Validator::make($request->all(), $this->createPreBookingValidation());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        $slot = Modules::where('id', $request->slot_id)->first();
        $fromTime = explode('-', $slot->description);
        $count = Orders::count();
        $module = $this->getModuleIdBasedOnCode(Constants::ORDER_SUCCESS);

        $booking = Orders::create(array_merge(
            $request->only(['address_id', 'price', 'items', 'latitude', 'longitude', 'vendor_id', 'instructions', 'expected_delivery']),
            array(
                "status"        => $module,
                'customer_id' => $id,
                "pre_booking" => 1,
                "order_no"      => "HOC0000" . $count + 1,
                "item_count"    => count($request->get('menus'))
            )
        ));
        if (count($request->menus) > 0) {
            foreach ($request->menus as $menu) {
                OrderDetails::create(["menu_id" => $menu['menu_id'], "quantity" => $menu['quantity'], "order_id" => $booking->id]);
            }
        };
        $paymentStatus = $this->getModuleIdBasedOnCode('PS01');
        $active = $this->getModuleIdBasedOnCode('CS01');
        if (!$request->cod) {
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            $razorpay = $api->order->create([
                'receipt'           => $booking->id,
                'amount'            => $request->price * 100,
                'currency'          => 'INR',
                'payment_capture'   => 1
            ]);
            $paymentData = [
                "customer_id"           => auth(Constants::CUSTOMER_GUARD)->user()->id,
                "order_id"              => $booking->id,
                "amount"                => $request->price,
                "razorpay_order_id"     => $razorpay->id,
                "razorpay_receipt_id"   => $razorpay->receipt,
                "status"                => $active,
                "payment_status"        => $paymentStatus,
                "created_at"            => Carbon::now()
            ];
        } else {
            $paymentData = [
                "customer_id"           => auth(Constants::CUSTOMER_GUARD)->user()->id,
                "order_id"              => $booking->id,
                "amount"                => $request->price,
                "payment_method"        => "Cash on Delivery",
                "status"                => $active,
                "payment_status"        => $paymentStatus,
                "created_at"            => Carbon::now()
            ];
        }

        $payment = Payment::create($paymentData);

        $address = Address::where('id', $request->address_id)->first();
        $booking['address'] = $address;
        $booking['payment'] = $payment;
        return $this->successResponse(true, $booking, Constants::CREATED_SUCCESS, HTTPStatusCode::CREATED);
    }
}
