<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CategoryHasSlot;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use ResponseTraits, ValidationTraits, CommonQueries;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth(Constants::CUSTOMER_GUARD)->user()->id;

        $cart = DB::table('cart')
            ->where('user_id', $userId)
            ->join('menus', 'cart.menu_id', '=', 'menus.id')
            ->join('customers', 'cart.user_id', '=', 'customers.id')
            ->join('vendors', 'cart.vendor_id', '=', 'vendors.id')
            ->join('modules', 'cart.slot_id', '=', 'modules.id')
            ->select(
                'cart.id as id',
                'cart.quantity as quantity',
                'menus.image as image',
                'menus.id as menu_id',
                'menus.name as menu_name',
                'menus.price as price',
                'customers.name as customer_name',
                'customers.id as customer_id',
                'vendors.id as vendor_id',
                'vendors.name as vendor_name',
                'menus.category_id as category_id',
                'modules.description as timeslot',
                'modules.module_name as slot_name',
                'modules.id as slot_id',
                'vendors.close_time as close_time'
            )
            ->paginate(10);

        return $this->successResponse(true, $cart, Constants::GET_SUCCESS, HTTPStatusCode::OK);
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
        $validator = Validator::make($request->all(), $this->cartValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        $id = auth()->guard(Constants::CUSTOMER_GUARD)->user()->id;
        $exist = Cart::where('vendor_id', $request->vendor_id)->where('menu_id', $request->menu_id)->where('user_id', $id)->first();
        if ($exist) {
            $exist->quantity += $request->quantity;
            $exist->save();
            return $this->successResponse(true, $exist, Constants::UPDATED_SUCCESS, HTTPStatusCode::OK);
        }
        // create cart
        $cart = Cart::create(array_merge($request->all(), array('user_id' => $id)));
        return $this->successResponse(true, $cart, Constants::CREATED_SUCCESS, HTTPStatusCode::CREATED);
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
        $validator = Validator::make($request->all(), $this->cartUpdateValidator());

        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), Constants::UNPROCESS_ENTITY, HTTPStatusCode::UNPROCESS_ENTITY_CODE);

        $card = Cart::where('id', $id)->first();
        $card->quantity = $request->get('quantity');
        $card->save();
        return $this->successResponse(true, "", Constants::UPDATED_SUCCESS, HTTPStatusCode::OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Cart::where('id', $id)->delete();
        return $this->successResponse(true, "", Constants::GET_SUCCESS, HTTPStatusCode::OK);
    }

    /**
     * Remove customer based cart list
     */
    public function customerCartRemove($id)
    {
        Cart::where('user_id', $id)->delete();
        return $this->successResponse(true, "", Constants::GET_SUCCESS, HTTPStatusCode::OK);
    }
}
