<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Traits\CommonQueries;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Customer;

class CustomerController extends Controller
{
    use ResponseTraits, ValidationTraits, CommonQueries;

    public function index(Request $request)
    {
        # code...
        if ($request->type === Constants::DROPDOWN) {
            $customer = DB::table('customers')->orderByDesc('id')->get();
            return $this->successResponse(true, $customer, Constants::GET_SUCCESS);
        }
        $customer = DB::table('customers')->select('id', 'name', 'mobile', 'dob', 'email')->orderByDesc('id')->paginate(10);
        return $this->successResponse(true, $customer, Constants::GET_SUCCESS);
    }

    public function edit($id)
    {
        # code...
        $customer = DB::table('customers')->select('id', 'name', 'mobile', 'dob', 'email', 'points', 'referral_code', 'image')->where('id', $id)->first();
        return $this->successResponse(true, $customer, Constants::GET_SUCCESS);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->updateCustomerSignupValidator($id));

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        Customers::where('id', $id)->update($request->only(['name', 'mobile', 'dob', 'email']));

        return $this->successResponse(true, "", Constants::UPDATED_SUCCESS);
    }

    public function updateProfile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "image"     => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        $customer = Customers::where('id', $id)->first();

        if ($customer) {
            $path = $this->uploadImage($request->file('image'), 'customer', $id . '.' . $request->file('image')->getClientOriginalExtension());
            $customer->image = $path;
            $customer->save();
        }
        return $this->successResponse(true, $path, Constants::UPDATED_SUCCESS);
    }
}
