<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\SubCategory;
use App\Traits\CommonQueries;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
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
    public function index(Request $request)
    {
        if ($request->type === $this->constant::DROPDOWN) {
            $data = $this->subCategoriesCommonQuery()->get();
            return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
        }
        $data = $this->subCategoriesCommonQuery()->paginate();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
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
        $id = auth()->user()->id;
        $validator = Validator::make($request->all(), $this->subCategoryValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        // Modules #active
        $modules = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        // Create sub category
        $category = SubCategory::create(array_merge($request->only(['name', 'category_id']), array('status' => $modules, 'created_by' => $id, 'updated_by' => $id)));
        return $this->successResponse(true, $category, $this->constant::SUB_CATEGORY_CREATED, $this->http::CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
