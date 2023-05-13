<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\Vendor;

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

    public function index()
    {
        # code...
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = Vendor::with('status')->where('status', $status)->paginate(10);
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }
}
