<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use App\Traits\CommonQueries;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    private $constant;

    private $http;

    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
        // $this->middleware(['auth:customer', 'role:customer']);
    }

    use ResponseTraits, ValidationTraits, CommonQueries;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = Cart::with(['menu', 'user', 'cook'])->paginate();
        return $this->successResponse(true, $cart, $this->constant::GET_SUCCESS, $this->http::OK);
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
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        // create cart
        $cart = Cart::create(array_merge($request->all(), array('user_id' => auth()->guard($this->constant::CUSTOMER_GUARD)->user()->id)));
        return $this->successResponse(true, $cart, $this->constant::CREATED_SUCCESS, $this->http::CREATED);
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
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);

        $card = Cart::where('id', $id)->first();
        $card->quantity = $request->get('quantity');
        $card->save();
        return $this->successResponse(true, "", $this->constant::UPDATED_SUCCESS, $this->http::OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Cart::where('id', $id)->delete();
        return $this->successResponse(true, "", $this->constant::GET_SUCCESS, $this->http::OK);
    }
}
