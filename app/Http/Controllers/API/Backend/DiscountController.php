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
        $discount = Discount::with(['status', 'createdBy', 'type'])->where([['status', $status], ['expire_at', '>', Carbon::now()]])->paginate();
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
        $id = auth()->user()->id;
        $validator = Validator::make($request->all(), $this->discountValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // type #id
        $type = $this->getModuleIdBasedOnCode($request->get('type'));
        $count = $this->getModuleBasedOnCode($request->get('type'))->description;
        // Status
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);

        // Create Discount
        $discount = Discount::create(array_merge(
            $request->only(['name', 'description', 'image', 'vendor_id', 'category_id', "percentage"]),
            array(
                'type' => $type,
                'expire_at' => Carbon::now()->addDays($count),
                'status' => $status,
                'created_by' => $id,
                'updated_by' => $id
            )
        ));
        return $this->successResponse(true, $discount, $this->constant::DISCOUNT_CREATED, $this->http::CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $status = $this->getModuleIdBasedOnCode($this->constant::ACTIVE);
        $discount = Discount::with(['status', 'createdBy', 'type'])->where([['id', $id], ['status', $status]])->first();
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
            // type #id
            $type = $this->getModuleIdBasedOnCode($request->get('type'));
            $count = $this->getModuleBasedOnCode($request->get('type'))->description;
            // Status
            $status = $this->getModuleIdBasedOnCode($request->get('status'));

            $discount->name = $request->name;
            $discount->description = $request->description;
            $discount->image = $request->image;
            $discount->type = $type;
            $discount->status = $status;
            if ($discount->type != $type) {
                $discount->expire_at = Carbon::now()->addDays($count);
            }
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
