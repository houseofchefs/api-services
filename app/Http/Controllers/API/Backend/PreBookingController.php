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
use App\Models\PreBooking;
use App\Models\PreBookingDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreBookingController extends Controller
{
    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    public function store(Request $request)
    {
        # code...
        $id = auth($this->constant::CUSTOMER_GUARD)->user()->id;
        $validator = Validator::make($request->all(), $this->createPreBookingValidation());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        $booking = PreBooking::create(array_merge($request->only(['booking_date', 'address_id', 'price', 'items', 'latitude', 'longitude', 'vendor_id', 'instructions']), array('customer_id' => $id)));
        if (count($request->menus) > 0) {
            foreach ($request->menus as $menu) {
                PreBookingDetail::create(["menu_id" => $menu['menu_id'], "quantity" => $menu['quantity'], "booking_id" => $booking->id]);
            }
        };
        $address = Address::where('id', $request->address_id)->first();
        $booking['address'] = $address;
        return $this->successResponse(true, $booking, $this->constant::CREATED_SUCCESS, $this->http::CREATED);
    }
}
