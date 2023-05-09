<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Modules;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;

class ModuleController extends Controller
{

    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    use ResponseTraits, ValidationTraits, CommonQueries;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $module = Modules::where([["module_id", null], ["status", 1]])->get();
        return $this->successResponse(true, $module, $this->constant::GET_SUCCESS);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $code)
    {
        $moduleId = $this->getModuleIdBasedOnCode($code);
        $module = Modules::where([["module_id", $moduleId], ["status", 1]])->get();
        return $this->successResponse(true, $module, $this->constant::GET_SUCCESS);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
