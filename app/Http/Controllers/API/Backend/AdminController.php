<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Riders;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\CommonQueries;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use ResponseTraits, ValidationTraits, CommonQueries;

    public function index()
    {
        # code...
        $data = User::with('roles')->role('admin')->orderByDesc('id')->paginate(10);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function edit($id)
    {
        # code...
        $data = User::with(['roles', 'status'])->role('admin')->where('id', $id)->first();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->updateAdminValidator($id));

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        User::where('id', $id)->update($request->only(['name', 'mobile', 'email', 'status']));

        return $this->successResponse(true, "", Constants::UPDATED_SUCCESS);
    }

    public function dashboard()
    {
        $orders = Orders::count();
        $deliveryId = $this->getModuleIdBasedOnCode(Constants::ORDER_DELIVERED);
        $delivery = Orders::where('status', $deliveryId)->count();
        $cancelId = $this->getModuleIdBasedOnCode(Constants::ORDER_CANCELED);
        $cancel = Orders::where('status', $cancelId)->count();
        $vendor = Vendor::count();
        $rider = Riders::count();
        $revenue = Orders::where('status', $deliveryId)->sum('price');
        $data = [
            "order"     => $orders,
            "delivery"  => $delivery,
            "cancel"    => $cancel,
            "vendor"    => $vendor,
            "rider"     => $rider,
            "revenue"   => $revenue
        ];
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }
}
