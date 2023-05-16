<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    private $constant;

    private $http;

    use ResponseTraits, ValidationTraits, CommonQueries;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    public function index($id)
    {
        $data = Staff::where('vendor_id', $id)->with('status')->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function inactive($id)
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::INACTIVE);
        $data = Staff::where('id', $id)->update(['status' => $status]);
        return $this->successResponse(true, $data, $this->constant::UPDATED_SUCCESS);
    }

    public function active($id)
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = Staff::where('id', $id)->update(['status' => $status]);
        return $this->successResponse(true, $data, $this->constant::UPDATED_SUCCESS);
    }
}
