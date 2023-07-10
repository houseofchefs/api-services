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
use App\Models\Bank;
use App\Models\Staff;
use App\Models\Vendor;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{

    use ResponseTraits, ValidationTraits, CommonQueries;

    ## Service Code Started

    public function index(Request $request)
    {
        # code...
        if ($request->type === Constants::DROPDOWN) {
            $data = DB::table('vendors')->select('id as value', 'name as label')->get();
            return $this->successResponse(true, $data, Constants::GET_SUCCESS);
        }
        $data = Vendor::with('status')->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function edit(String $id)
    {
        # code...
        $data = Vendor::with(['status', 'address', 'bank.type'])->where('id', $id)->first();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function details(Request $request, String $id)
    {
        # code...
        $customerId = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $origin = $request->get('latitude') . ',' . $request->get('longitude');

        $data = DB::table('vendors')
            ->selectRaw('vendors.id as id, vendors.close_time as close_time, vendors.order_accept_time as order_accept_time, vendors.name as name, vendors.image as image, vendors.latitude as latitude, vendors.longitude as longitude, vendors.rating as rating, vendors.close_time as close_time, vendors.ucount as count,IF(wishlists.id IS NULL, false, true) AS wishlist')
            ->where('vendors.id', $id)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('menus')
                    ->whereRaw('menus.vendor_id = vendors.id');
            })
            ->leftJoin('wishlists', function ($join) use ($customerId) {
                $join->on('wishlists.menu_id', '=', 'vendors.id')
                    ->where('wishlists.customer_id', '=', $customerId)
                    ->where('wishlists.type', '=', 'vendor');
            })
            ->first();
        if ($data) {
            $destination = $data->latitude . ',' . $data->longitude;
            $google = $this->getDistance($origin, $destination);
            $data->distance = $google['distance']['text'];
            $data->time = $google['duration']['text'];
        }
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function dropdownVendor()
    {
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $data = DB::table('vendors')->where('status', $status)->select('id as value', 'name as label')->get();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function updateVendor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->updateVendorValidator($id));

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        DB::transaction(function () use ($request, $id) {
            $vendors = $request->only(['name', 'email', 'gst_no', 'latitude', 'longitude', 'order_accept_time', 'close_time', 'open_time', 'status']);
            $address = $request->only(['door_no', 'lanmark', 'address_line', 'latitude', 'longitude', 'pincode', 'place_id']);
            $bank = $request->only(['bank_name', 'account_number', 'account_type', 'ifsc_code', 'holder_name']);

            Bank::where('id', $request->bank_id)->update(array_merge($bank, array('guard' => "cook", 'user_id' => $id)));
            Address::where('id', $request->address_id)->update(array_merge($address, array('guard' => "cook", 'user_id' => $id)));
            Vendor::where('id', $id)->update(array_merge($vendors, array('bank_id' => $request->bank_id, 'address_id' => $request->address_id, 'created_by' => 1, 'mobile' => preg_replace('/[^0-9]/', '', $request->get('mobile')))));

            if (gettype($request->get('image')) != 'string' && $request->file('image') != null) {
                $path = $this->uploadImage($request->file('image'), 'vendor/' . $id . '/profile', $id . '.' . $request->file('image')->getClientOriginalExtension());
                $vendor = Vendor::where('id', $id)->first();
                $vendor->image = $path;
                $vendor->save();
            }
        });

        return $this->successResponse(true, "", Constants::CREATED_SUCCESS, 201);
    }

    public function customerDetails(String $id)
    {
        # code...
        $auth = auth(Constants::CUSTOMER_GUARD)->user()->id;
        $wishlist = Wishlist::where('customer_id', $auth)->where('type', 'vendor')->where('menu_id', $id)->first();
        $data = Vendor::with(['status', 'address', 'bank.type'])->where('id', $id)->first();
        $data['wishlist'] = $wishlist ? true : false;
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * @param $request
     * create vendor
     */
    public function vendorSignup(Request $request)
    {
        $validator = Validator::make($request->all(), $this->createVendorValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        DB::transaction(function () use ($request) {
            $vendors = $request->only(['name', 'email', 'gst_no', 'latitude', 'longitude', 'order_accept_time', 'close_time', 'open_time']);
            $address = $request->only(['door_no', 'lanmark', 'address_line', 'latitude', 'longitude', 'pincode']);
            $bank = $request->only(['bank_name', 'account_number', 'account_type', 'ifsc_code', 'holder_name']);

            $currentDate = Carbon::now();
            $subscription = $currentDate->addDays(365);
            $bankDetail = Bank::create(array_merge($bank, array('guard' => "cook")));
            $addressDetail = Address::create(array_merge($address, array('guard' => "cook")));
            $vendor = Vendor::create(array_merge($vendors, array('bank_id' => $bankDetail->id, 'address_id' => $addressDetail->id, 'created_by' => 1, 'subscription_expire_at' => $subscription, 'mobile' => preg_replace('/[^0-9]/', '', $request->get('mobile')))));
            $bankDetail->user_id = $vendor->id;
            $addressDetail->user_id = $vendor->id;
            $bankDetail->save();
            $addressDetail->save();

            $path = $this->uploadImage($request->file('image'), 'vendor/' . $vendor->id . '/profile', $vendor->id . '.' . $request->file('image')->getClientOriginalExtension());
            $vendor->image = $path;
            $vendor->save();
        });

        return $this->successResponse(true, "", Constants::CREATED_SUCCESS, 201);
    }

    /**
     * @param $request
     * create riders
     */
    public function staffSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->staffSignupValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        //Modules #id
        $module = $this->getModuleIdBasedOnCode(Constants::ACTIVE);

        // Create Cook User
        $user = Staff::create(array_merge($request->only(['name', 'mobile', 'password', 'email', 'vendor_id']), array(Constants::STATUS => $module)));

        // assign the role to the created user #roles
        $user->assignRole($request->role);

        return $this->successResponse(true, "", Constants::CREATED_SUCCESS, 201);
    }
}
