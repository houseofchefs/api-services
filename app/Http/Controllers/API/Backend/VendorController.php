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
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    ## Service Code Started

    public function index(Request $request)
    {
        # code...
        if ($request->type === $this->constant::DROPDOWN) {
            $data = DB::table('vendors')->select('id as value', 'name as label')->get();
            return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
        }
        $data = Vendor::with('status')->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function edit(String $id)
    {
        # code...
        $data = Vendor::with(['status', 'address', 'bank.type'])->where('id', $id)->first();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function dropdownVendor()
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = DB::table('vendors')->where('status', $status)->select('id as value', 'name as label')->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function updateVendor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->updateVendorValidator($id));

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        DB::transaction(function () use ($request, $id) {
            $vendors = $request->only(['name', 'email', 'mobile', 'gst_no', 'latitude', 'longitude', 'order_accept_time', 'close_time', 'open_time', 'status']);
            $address = $request->only(['door_no', 'lanmark', 'address_line', 'latitude', 'longitude', 'pincode', 'place_id']);
            $bank = $request->only(['bank_name', 'account_number', 'account_type', 'ifsc_code', 'holder_name']);

            Bank::where('id', $request->bank_id)->update(array_merge($bank, array('guard' => "cook", 'user_id' => $id)));
            Address::where('id', $request->address_id)->update(array_merge($address, array('guard' => "cook", 'user_id' => $id)));
            Vendor::where('id', $id)->update(array_merge($vendors, array('bank_id' => $request->bank_id, 'address_id' => $request->address_id, 'created_by' => 1)));
        });

        return $this->successResponse(true, "", $this->constant::CREATED_SUCCESS, 201);
    }
}
