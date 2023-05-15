<?php

namespace App\Http\Controllers\API\Backend;

use App\Constants\Constants;
use App\Constants\HTTPStatusCode;
use App\Http\Controllers\Controller;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Models\Payment;
use App\Traits\CommonQueries;
use App\Traits\ResponseTraits;
use App\Traits\ValidationTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                "item_count"    => count($request->get('product_id')),
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
        $payment = $this->getModuleIdBasedOnCode("PS01");
        Payment::create(["payment_method" => $request->payment_method, "payment_status" => $payment,'order_id' => $order->id,'amount' => $request->price]);
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
        $auth = auth($this->constant::CUSTOMER_GUARD)->user()->id;
        $order = Orders::where('id', $auth)->with(['status','payments.method','payments.status','details.menu','vendor'])->paginate(10);
        return $this->successResponse(true, $order, $this->constant::GET_SUCCESS);
    }

    public function orderCancel(String $id) {
        $modules = $this->getModuleIdBasedOnCode('OS03');
        $order = Orders::where('id', $id)->update(['status' => $modules]);
        return $this->successResponse(true, $order, $this->constant::UPDATED_SUCCESS);
    }
}
