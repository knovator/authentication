<table>
    <thead>
    <tr>
        <th><b>Customer Name</b></th>
        <td>{{$customer->full_name}}</td>
    </tr>
    <tr>
        <th><b>Email</b></th>
        <td>{{$customer->email}}</td>
    </tr>
    <tr>
        <th><b>Phone</b></th>
        <td>{{$customer->phone}}</td>
    </tr>
    <tr>
        <th><b>GST No</b></th>
        <td>{{$customer->gst_no}}</td>
    </tr>
    <tr></tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Order No</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Quantity {{ (($orderType == 'purchase') || $orderType == 'yarn')?'(KG)':'(Mtr)' }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $orderKey => $order)
        <tr>
            <td>{{$orderKey + 1}}</td>
            <td>{{$order->order_no}}</td>
            <td>{{\Carbon\Carbon::parse($order->order_date)->format('d M Y')}}</td>
            <td>{{$order->status->name}}</td>
            <td>{{$order->quantity->total}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
