<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\User;
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
        $data = User::with('roles')->role('admin')->where('id', $id)->first();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->updateAdminValidator($id));

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        User::where('id', $id)->update($request->only(['name', 'mobile', 'email']));

        return $this->successResponse(true, "", Constants::UPDATED_SUCCESS);
    }
}
