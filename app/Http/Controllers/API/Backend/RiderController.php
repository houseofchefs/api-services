<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Bank;
use App\Models\Orders;
use App\Models\Riders;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RiderController extends Controller
{
    use ResponseTraits, ValidationTraits, CommonQueries;

    public function index()
    {
        $data = Riders::orderBy('id', 'desc')->with(['status', 'vehicle'])->paginate(10);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * @param $request
     * create riders
     */
    public function riderSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->riderSignupValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        //Modules #id
        $module = $this->getModuleIdBasedOnCode(Constants::ACTIVE);

        // Create Cook User
        $user = Riders::create(array_merge($request->only([Constants::NAME, Constants::MOBILE]), array(Constants::PASSWORD => env(Constants::RIDER_PASSWORD), Constants::STATUS => $module)));

        // assign the role to the created user #roles
        $user->assignRole(Constants::RIDER_ROLE);

        // send otp common function
        $this->sendOtp($request->get(Constants::MOBILE), Constants::RIDER_GUARD);
        return $this->successResponse(true, "", Constants::OTP_SENT_SUCCESS, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->riderStoreValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        DB::transaction(function () use ($request) {
            $rider = $request->only(['name', 'email', 'mobile', 'password']);
            $address = $request->only(['door_no', 'lanmark', 'address_line', 'latitude', 'longitude', 'pincode']);
            $bank = $request->only(['bank_name', 'account_number', 'account_type', 'ifsc_code', 'holder_name']);
            $vehicle = [
                "reg_no"            => $request->get('registration_number'),
                'insurance_number'  => $request->get('insurance_number')
            ];

            $bankDetail = Bank::create(array_merge($bank, array('guard' => "rider")));
            $addressDetail = Address::create(array_merge($address, array('guard' => "rider")));
            $vehicle = Vehicle::create(array_merge($vehicle, ['status' => 2]));
            $rider = Riders::create(array_merge($rider, array(
                'bank_id' => $bankDetail->id,
                'address_id' => $addressDetail->id,
                'vehicle_id'  => $vehicle->id,
                'created_by' => 1
            )));
            // assign the role to the created user #roles
            $rider->assignRole(Constants::RIDER_ROLE);
            $bankDetail->user_id = $rider->id;
            $addressDetail->user_id = $rider->id;
            $bankDetail->save();
            $addressDetail->save();
        });

        return $this->successResponse(true, "", Constants::CREATED_SUCCESS, 201);
    }

    public function edit($id)
    {
        $data = Riders::where('id', $id)->with(['vehicle', 'status', 'bank.type', 'address'])->first();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->riderUpdateValidator($id));

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        DB::transaction(function () use ($request, $id) {
            $rider = $request->only(['name', 'email', 'mobile', 'status']);
            $address = $request->only(['door_no', 'lanmark', 'address_line', 'latitude', 'longitude', 'pincode']);
            $bank = $request->only(['bank_name', 'account_number', 'account_type', 'ifsc_code', 'holder_name']);
            $vehicle = [
                "reg_no"            => $request->get('registration_number'),
                'insurance_number'  => $request->get('insurance_number')
            ];

            Bank::where('id', $request->bank_id)->update(array_merge($bank, array('guard' => "rider")));
            Address::where('id', $request->address_id)->update(array_merge($address, array('guard' => "rider")));
            $vehicle = Vehicle::where('id', $request->vehicle_id)->update($vehicle);
            $rider = Riders::where('id', $id)->update(array_merge($rider, array(
                'bank_id' => $request->bank_id,
                'address_id' =>  $request->address_id,
                'vehicle_id' => $request->vehicle_id
            )));
        });

        return $this->successResponse(true, "", Constants::UPDATED_SUCCESS);
    }

    public function assignedOrders($id)
    {
        $rider = Orders::with(['details.menu', 'status', 'payments', 'customers', 'address'])->orderBy('id', 'desc')->where('rider_id', $id)->paginate(10);
        return $this->successResponse(true, $rider, Constants::GET_SUCCESS);
    }
}
