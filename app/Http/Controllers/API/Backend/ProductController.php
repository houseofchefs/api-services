<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Models\Product;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
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
        $modules = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = Product::where('status', $modules)->paginate(10);
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
        $validator = Validator::make($request->all(), $this->createProductValidation());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Product #status
        $modules = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        // create
        Product::create(array_merge($request->only(["name", "description", "image","vendor_id", "units", "price"]), array('status' => $modules)));

        return $this->successResponse(true, "", $this->constant::CREATED_SUCCESS, $this->http::CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $modules = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = Product::where('status', $modules)->where('id', $id)->first();
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
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
        $validator = Validator::make($request->all(), $this->createProductValidation());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Product #status
        $modules = $this->getModuleIdBasedOnCode($request->status);
        $product = Product::where('id', $id)->first();
        if ($product) {
            $product->name = $request->name;
            $product->description = $request->description;
            $product->image = $request->image;
            $product->units = $request->units;
            $product->price = $request->price;
            $product->status = $modules;
            $product->save();
        }

        return $this->successResponse(true, "", $this->constant::UPDATED_SUCCESS);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function vendorBasedProduct($id) {
        $modules = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $data = DB::table('products')->where('vendor_id', $id)->where('status', $modules)->orderBy('id','desc')->paginate(12);
        return $this->successResponse(true, $data, $this->constant::GET_SUCCESS);
    }
}
