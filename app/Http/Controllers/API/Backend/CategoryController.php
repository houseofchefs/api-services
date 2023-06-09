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
    use ResponseTraits, ValidationTraits, CommonQueries;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->type === Constants::DROPDOWN) {
            $data = $this->categoriesCommonQuery()->get();
            return $this->successResponse(true, $data, Constants::GET_SUCCESS);
        }
        $data = $this->categoriesCommonQuery()->paginate(10);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
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
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        // Modules #active
        $modules = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        // Create category
        DB::transaction(function () use ($request, $modules, $id) {
            $category = Categories::create(array_merge($request->only(['name', 'image', 'vendor_id']), array('status' => $modules, 'created_by' => $id, 'updated_by' => $id)));
            foreach ($request->slot as $data) {
                CategoryHasSlot::create(["category_id" => $category->id, "slot_id" => $data]);
            }
            $path = $this->uploadImage($request->file('image'), 'category', $category->id . '.' . $request->file('image')->getClientOriginalExtension());
            $category->image = $path;
            $category->save();
        });
        return $this->successResponse(true, "", Constants::CATEGORY_CREATED, HTTPStatusCode::CREATED);
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
        $data = $this->categoriesCommonQuery($id)->get();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $auth = auth()->user()->id;
        $validator = Validator::make($request->all(), $this->categoryUpdateValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);
        // Update category
        DB::transaction(function () use ($request, $auth, $id) {
            $category = Categories::where('id', $id)->first();
            $category->name = $request->name;
            $category->status = $request->status;
            $category->vendor_id = $request->vendor_id;
            CategoryHasSlot::where('category_id', $id)->delete();
            foreach ($request->slot as $data) {
                CategoryHasSlot::create(["category_id" => $category->id, "slot_id" => $data]);
            }
            if (gettype($request->get('image')) != 'string' && $request->file('image') != null) {
                $path = $this->uploadImage($request->file('image'), 'category', $category->id . '.' . $request->file('image')->getClientOriginalExtension());
                $category->image = $path;
            }
            $category->save();
        });
        return $this->successResponse(true, "", Constants::CATEGORY_UPDATED);
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
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function masterCategory()
    {
        # code...
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $data =  DB::table('categories')
            ->join('users', 'categories.created_by', '=', 'users.id')
            ->join('modules', 'categories.status', '=', 'modules.id')
            ->where('vendor_id', 0)
            ->where("categories.status", $status)
            ->select('categories.name', 'users.name as created', 'modules.module_name as status', 'categories.id', 'categories.image', 'categories.vendor_id as vendor_id')
            ->addSelect(DB::raw('(SELECT GROUP_CONCAT(modules.module_name SEPARATOR ", ") FROM categories_has_slot
                                JOIN modules ON categories_has_slot.slot_id = modules.id
                                WHERE categories_has_slot.category_id = categories.id) as slots'))->get();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function vendorBasedCategory($id)
    {
        # code...
        $data = [];
        $categoryIds = Menu::where('vendor_id', $id)
            ->distinct()
            ->pluck('category_id');
        $active = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        if (count($categoryIds) > 0) {
            $data = $this->categoriesCommonQuery()->where('categories.status', $active)->whereIn('categories.id', $categoryIds)->get();
        }
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }

    public function activeCategory()
    {
        $status = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $data = $this->categoriesCommonQuery()->where("categories.status", $status)->paginate(10);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }
}
