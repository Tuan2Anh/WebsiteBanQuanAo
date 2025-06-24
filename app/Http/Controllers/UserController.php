<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    public function index()
    {
        return view('user.index');
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->paginate(10);
        return view('user.orders', compact('orders'));
    }


    public function account_detail()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $address = Address::where('user_id', Auth::user()->id)->where('isdefault', 1)->first();
        return view('user.account-detait', compact('address'));
    }


    public function order_details($order_id)
    {
        $order = Order::where('user_id', Auth::user()->id)->where('id', $order_id)->first();
        if ($order) {
            $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
            $transaction = Transaction::where('order_id', $order_id)->first();
            return view('user.order-detail', compact('order', 'orderItems', 'transaction'));
        } else {
            return redirect()->route('login');
        }
    }

    public function order_cancel(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = "canceled";
        $order->canceled_date = Carbon::now();
        $order->save();
        return back()->with("status", "Order has been cancelled successfully!");
    }

    public function update_account_details(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name'      => 'required|string|max:100',
            'phone'     => 'required|numeric|digits:10',
            'zip'       => 'required|numeric|digits:6',
            'state'     => 'required|string|max:100',
            'city'      => 'required|string|max:100',
            'address'   => 'required|string|max:255',
            'locality'  => 'required|string|max:255',
            'landmark'  => 'required|string|max:255',
        ]);

        // Cập nhật hoặc tạo mới địa chỉ mặc định
        Address::updateOrCreate(
            ['user_id' => $user->id, 'isdefault' => true],
            [
                'name'      => $validatedData['name'],
                'phone'     => $validatedData['phone'],
                'zip'       => $validatedData['zip'],
                'state'     => $validatedData['state'],
                'city'      => $validatedData['city'],
                'address'   => $validatedData['address'],
                'locality'  => $validatedData['locality'],
                'landmark'  => $validatedData['landmark'],
                'country'   => 'VietNam',
            ]
        );

        return redirect()->back()->with('success', 'Account details updated successfully.');
    }
}
