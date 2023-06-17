<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
        // $this->middleware( 'role:admin');
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $discount = Discount::with(['vendor', 'status', 'type'])->orderBy('id', 'desc')->paginate(10);
        // $discount = DB::table('discounts')
        // ->where([
        //     ['discounts.status', $status],
        //     ['discounts.expire_at', '<', Carbon::now()]
        // ])
        //     ->join('modules as mstatus', 'discounts.status', '=', 'mstatus.id')
        //     ->join('modules as mtype', 'discounts.type', '=', 'mtype.id')
        //     ->join('vendors', 'discounts.vendor_id', '=', 'vendors.id')
        //     ->join('categories', 'discounts.category_id', '=', 'categories.id')
        //     ->select(
        //         'discounts.id as id',
        //         'discounts.name',
        //         'vendors.name as vendor_name',
        //         'mtype.module_name as type',
        //         'categories.name as category',
        //         'discounts.percentage as percentage',
        //         'discounts.expire_at as expireAt'
        //     )
        //     ->paginate(10);

        return $this->successResponse(true, $discount, $this->constant::GET_SUCCESS, $this->http::OK);
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
        $validator = Validator::make($request->all(), $this->discountValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // Status
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        // Create Discount
        $discount = Discount::create(array_merge(
            $request->only(['name', 'description', 'vendor_id', 'category_id', "percentage", "expire_at", 'type']),
            array(
                'status' => $status
            )
        ));

        $path = $this->uploadImage($request->file('image'), '/discount', $discount->id . '.' . $request->file('image')->getClientOriginalExtension());
        $discount->image = $path;
        $discount->save();
        return $this->successResponse(true, $discount, $this->constant::DISCOUNT_CREATED, $this->http::CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $discount = Discount::with(['vendor', 'status', 'type', 'category'])->where([['id', $id]])->first();
        return $this->successResponse(true, $discount, $this->constant::GET_SUCCESS, $this->http::OK);
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
        $discount = Discount::where('id', $id)->first();
        if (optional($discount)->id) {

            $discount->name = $request->name;
            $discount->category_id = $request->category_id;
            $discount->vendor_id = $request->vendor_id;
            $discount->percentage = $request->percentage;
            $discount->description = $request->description;
            if (gettype($request->get('image')) != 'string' && $request->file('image') != null) {
                $path = $this->uploadImage($request->file('image'), '/discount', $discount->id . '.' . $request->file('image')->getClientOriginalExtension());
                $discount->image = $path;
            }
            $discount->type = $request->type;
            $discount->expire_at = $request->expire_at;
            $discount->status = $request->status;
            $discount->save();
            return $this->successResponse(true, $discount, $this->constant::DISCOUNT_UPDATED, $this->http::OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::INACTIVE);
        $discount = Discount::where('id', $id)->first();
        $discount->status = $status;
        return $this->successResponse(true, $discount, $this->constant::GET_SUCCESS);
    }

    /**
     * Available Discount List
     */
    public function discountList()
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $discount = DB::table('discounts')->where([['status', $status], ['expire_at', '>', Carbon::now()]])->orderBy('percentage', 'desc')->limit(3)->select('name', 'percentage', 'image', 'id', 'description')->get();
        return $this->successResponse(true, $discount, $this->constant::GET_SUCCESS);
    }
}
