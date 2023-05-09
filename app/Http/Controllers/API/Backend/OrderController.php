<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Traits\CommonQueries;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    private $constant;

    private $http;

    use ValidationTraits, ResponseTraits, CommonQueries;

    /**
     * constructor $middleware
     */
    public function __construct()
    {
        $this->constant = new Constants();
        $this->http = new HTTPStatusCode();
    }

    /**
     * @api createOrder
     * @route #middleware customer only
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), $this->createOrderValidator());
        // If validator fails it will #returns
        if ($validator->fails()) return $this->errorResponse(false, $validator->errors(), $this->constant::UNPROCESS_ENTITY, $this->http::UNPROCESS_ENTITY_CODE);
        //Modules #id
        $module = $this->getModuleIdBasedOnCode($this->constant::ORDER_SUCCESS);
        $count = Orders::count();
        $order = Orders::create(array_merge(
            $request->except('product_id'),
            array(
                "status"        => $module,
                "order_no"      => "HOC0000" . $count + 1,
                "customer_id"   => auth($this->constant::CUSTOMER_GUARD)->user()->id
            )
        ));
        // Order Details $store func
        if (count($request->get('product_id')) > 0) {
            foreach ($request->get('product_id') as $menu) {
                $details = [
                    "menu_id"   => $menu,
                    "order_id"  => $order->id
                ];
                OrderDetails::create($details);
            }
        }
        return $this->successResponse(true, $order, $this->constant::ORDER_CREATED, $this->http::CREATED);
    }

    /**
     * @api orderList
     * @route #middleware admin only
     */
    public function orderList()
    {
        $order = Orders::with(['details.menu', 'status'])->paginate();
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    /**
     * @api orderDetails
     * @route #middleware admin & customer
     */
    public function orderDetails(String $id)
    {
        $order = Orders::with(['details.menu', 'status'])->where('id', $id)->first();
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    public function orderListForCustomer()
    {
        # code...
        $order = Orders::with(['details.menu', 'status'])->where('customer_id', auth($this->constant::CUSTOMER_GUARD)->user()->id)->paginate();
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }
}
