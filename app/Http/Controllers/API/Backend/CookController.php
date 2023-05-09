<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Cook;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;

class CookController extends Controller
{

    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
        $this->middleware('role:Customer', ['only' => ['index', 'show']]);
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $module = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $cook = Cook::where('status', $module)->paginate();
        return $this->successResponse(true, $cook, $this->constant::GET_SUCCESS);
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
    public function show(string $id)
    {
        $module = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $cook = Cook::where([['status', $module], ['id', $id]])->first();
        return $this->successResponse(true, $cook, $this->constant::GET_SUCCESS);
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
