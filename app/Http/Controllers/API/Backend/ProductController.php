<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\Menu;
use App\Models\Product;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    use ResponseTraits, ValidationTraits, CommonQueries;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Product::with('status')->paginate(10);
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
        $validator = Validator::make($request->all(), $this->createProductValidation());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // Product #status
        $modules = $this->getModuleIdBasedOnCode(Constants::MENU_HOLD);

        // create
        $percentage = $this->getModuleBasedOnCode("MT12");
        $adminPrice = $request->price * ($percentage->description / 100);
        $vendorPrice = $request->price - $adminPrice;
        $data = Menu::create(array_merge($request->only(["name", "description", "vendor_id", "units", "price", 'category_id', 'menu_type', "isPreOrder", "isDaily", 'type', "min_quantity"]), array('status' => $modules, 'admin_price' => $adminPrice, 'vendor_price' => $vendorPrice)));

        $path = $this->uploadImage($request->file('image'), 'vendor/' . $request->get('vendor_id') . '/product', $data->id . '.' . $request->file('image')->getClientOriginalExtension());
        $data->image = $path;
        $data->save();

        return $this->successResponse(true, "", Constants::CREATED_SUCCESS, HTTPStatusCode::CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $modules = $this->getModuleIdBasedOnCode(Constants::ACTIVE);
        $data = Product::where('status', $modules)->where('id', $id)->first();
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
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
        $validator = Validator::make($request->all(), $this->updateProductValidation());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        // Product #status
        $modules = $this->getModuleIdBasedOnCode($request->status);
        $product = Menu::where('id', $id)->first();
        $percentage = $this->getModuleBasedOnCode("MT12");
        $adminPrice = $request->price * ($percentage->description / 100);
        $vendorPrice = $request->price - $adminPrice;
        if ($product) {
            $product->name = $request->name;
            $product->description = $request->description;
            $product->units = $request->units;
            $product->admin_price = $adminPrice;
            $product->vendor_price = $vendorPrice;
            $product->price = $request->price;
            $product->status = $modules;
            if (gettype($request->get('image')) != 'string' && $request->file('image') != null) {
                $path = $this->uploadImage($request->file('image'), 'vendor/' . $request->get('vendor_id') . '/product', $product->id . '.' . $request->file('image')->getClientOriginalExtension());
                $product->image = $path;
                $product->save();
            }
            $product->save();
        }

        return $this->successResponse(true, "", Constants::UPDATED_SUCCESS);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function vendorBasedProduct($id)
    {
        $data = Menu::with('status')->where('vendor_id', $id)->where('menu_type', 'product')->orderBy('id', 'desc')->paginate(12);
        return $this->successResponse(true, $data, Constants::GET_SUCCESS);
    }
}
