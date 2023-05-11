<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Traits\CommonQueries;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Bank;
use App\Models\Cook;
use App\Models\Customers;
use App\Models\Payment;
use App\Models\Riders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DetailsController extends Controller
{
    private $constant;

    private $http;

    use ValidationTraits, ResponseTraits, CommonQueries;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
        // $this->middleware(['jwt.auth:customers', 'jwt.auth:cooks', 'jwt.auth:riders'], ['only' => ['address', 'updateAddress', 'bank', 'updateBank']]);
        // $this->middleware(['jwt.auth:customers'], ['only' => ['payment']]);
        // $this->middleware(['auth:users'], ['only' => ['paymentList']]);
    }

    #### Method's are started

    public function address(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), $this->addressValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        $address = Address::create($request->only(["user_id", "door_no", "address_line", "address_type", "place_id", "pincode", "latitude", "longitude", "guard"]));

        ## Customer's
        if ($request->get('guard') == $this->constant::CUSTOMER_GUARD) $user = Customers::where('id', $request->get('user_id'))->first();

        ## Rider's
        if ($request->get('guard') == $this->constant::RIDER_GUARD) $user = Riders::where('id', $request->get('user_id'))->first();

        ## Cook's
        if ($request->get('guard') == $this->constant::COOK_GUARD) $user = Cook::where('id', $request->get('user_id'))->first();

        $user->address_id = $address->id;
        $user->save();
        return $this->successResponse(true, $address, $this->constant::ADDRESS_CREATED, $this->http::CREATED);
    }

    public function updateAddress(Request $request, String $id)
    {
        # code...
        $validator = Validator::make($request->all(), $this->addressValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        $address = Address::where('id', $id)->update($request->all());
        return $this->successResponse(true, "", $this->constant::ADDRESS_UPDATED, $this->http::OK);
    }

    public function bank(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), $this->bankValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        $bank = Bank::create($request->all());

        ## Customer's
        if ($request->get('guard') == $this->constant::CUSTOMER_GUARD) $user = Customers::where('id', $request->get('user_id'))->first();

        ## Rider's
        if ($request->get('guard') == $this->constant::RIDER_GUARD) $user = Riders::where('id', $request->get('user_id'))->first();

        ## Cook's
        if ($request->get('guard') == $this->constant::COOK_GUARD) $user = Cook::where('id', $request->get('user_id'))->first();

        $user->bank_id = $bank->id;
        $user->save();
        return $this->successResponse(true, $bank, $this->constant::BANK_CREATED, $this->http::CREATED);
    }

    public function updateBank(Request $request, String $id)
    {
        # code...
        $validator = Validator::make($request->all(), $this->bankValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        Bank::where('id', $id)->update($request->all());
        return $this->successResponse(true, "", $this->constant::BANK_UPDATED, $this->http::OK);
    }

    public function payment(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), $this->paymentValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        // #method
        $method = $this->getModuleIdBasedOnCode($request->get('payment_method'));
        // #status
        $status = $this->getModuleIdBasedOnCode($this->constant::PAYMENT_SUCCESS);
        // Payment Create
        $payment = Payment::create(array_merge(
            $request->except("payment_method"),
            array("payment_method" => $method, "payment_status" => $status)
        ));
        return $this->successResponse(true, $payment, $this->constant::CREATED_SUCCESS, $this->http::CREATED);
    }

    /**
     * payment list
     */
    public function paymentList()
    {
        $payment = Payment::with(['method', 'status'])->paginate();
        return $this->successResponse(true, $payment, $this->constant::GET_SUCCESS, $this->http::OK);
    }

    /**
     * customer address list
     */
    public function customerAddressList(string $id, string $guard)
    {
        $address = DB::table('address')->where('user_id', $id)->where('guard', $guard)->select('id', 'place_id', 'door_no', 'lanmark', 'address_line', 'address_type', 'pincode', 'latitude', 'longitude')->get();
        return $this->successResponse(true, $address, $this->constant::GET_SUCCESS);
    }

    /**
     * set active address
     */
    public function setActiveAddress(string $address)
    {
        # code...
        $user = Customers::where('id', auth($this->constant::CUSTOMER_GUARD)->user()->id)->first();

        if ($user) {
            $user->address_id = $address;
            $user->save();
        }
        return $this->successResponse(true, "", $this->constant::UPDATED_SUCCESS);
    }
}
