<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\CategoryHasSlot;
use App\Traits\CommonQueries;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
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
            $data = $this->categoriesCommonQuery()->get();
            return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
        }
        $data = $this->categoriesCommonQuery()->paginate(8);
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
        $validator = Validator::make($request->all(), $this->categoryValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        // Modules #active
        $modules = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        // Create category
        DB::transaction(function () use ($request, $modules, $id) {
            $category = Categories::create(array_merge($request->only(['name']), array('status' => $modules, 'created_by' => $id, 'updated_by' => $id)));
            foreach ($request->slot as $data) {
                CategoryHasSlot::create(["category_id" => $category->id, "slot_id" => $data]);
            }
        });
        return $this->successResponse(true, "", $this->constant::CATEGORY_CREATED, $this->http::CREATED);
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
        $auth = auth()->user()->id;
        $validator = Validator::make($request->all(), $this->categoryUpdateValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        // Modules #active
        $modules = $this->getModuleIdBasedOnCode($request->status);
        // Update category
        $category = Categories::where('id', $id)->first();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->status = $modules;
        $category->updated_by = $auth;
        $category->save();
        return $this->successResponse(true, $category, $this->constant::CATEGORY_UPDATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
