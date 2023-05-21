<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\CategoryHasSlot;
use App\Models\Menu;
use App\Models\Vendor;
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
        $data = $this->categoriesCommonQuery()->paginate(10);
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
            $category = Categories::create(array_merge($request->only(['name', 'image', 'vendor_id']), array('status' => $modules, 'created_by' => $id, 'updated_by' => $id)));
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
        $data = $this->categoriesCommonQuery()->where('categories.id', $id)->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
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
        // Update category
        DB::transaction(function () use ($request, $auth, $id) {
            $category = Categories::where('id', $id)->first();
            $category->name = $request->name;
            $category->status = $request->status;
            $category->vendor_id = $request->vendor_id;
            $category->save();
            CategoryHasSlot::where('category_id', $id)->delete();
            foreach ($request->slot as $data) {
                CategoryHasSlot::create(["category_id" => $category->id, "slot_id" => $data]);
            }
        });
        return $this->successResponse(true, "", $this->constant::CATEGORY_UPDATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function vendorDropDown()
    {
        $data =  DB::table('vendors')->select('name as label', 'id as value')->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function masterCategory()
    {
        # code...
        $data =  DB::table('categories')
            ->join('users', 'categories.created_by', '=', 'users.id')
            ->join('modules', 'categories.status', '=', 'modules.id')
            ->where('vendor_id', 0)
            ->select('categories.name', 'users.name as created', 'modules.module_name as status', 'categories.id', 'categories.image', 'categories.vendor_id as vendor_id')
            ->addSelect(DB::raw('(SELECT GROUP_CONCAT(modules.module_name SEPARATOR ", ") FROM categories_has_slot
                                JOIN modules ON categories_has_slot.slot_id = modules.id
                                WHERE categories_has_slot.category_id = categories.id) as slots'))->get();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }

    public function vendorBasedCategory($id)
    {
        # code...
        $data = [];
        $categoryIds = Menu::where('vendor_id', 1)
            ->distinct()
            ->pluck('category_id');
        if (count($categoryIds) > 0) {
            $data = $this->categoriesCommonQuery()->whereIn('categories.id', $categoryIds)->get();
        }
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }
}
