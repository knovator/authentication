<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Order No</th>
        <th>Order Date</th>
        <th>Design</th>
        <th>Customer</th>
        <th>Total Mtr</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $orderKey => $order)
        <tr>
            <td>{{$orderKey + 1}}</td>
            <td>{{$order->order_no}}</td>
            <td>{{\Carbon\Carbon::parse($order->order_date)->format('D m Y')}}</td>
            <td>{{$order->design->quality_name}}</td>
            <td>{{ (!is_null($order->customer)) ? $order->customer->full_name:''}}</td>
            <td>{{ (!is_null($order->recipe_meters)) ? $order->recipe_meters->total:0 }}</td>
            <td>{{$order->status->name}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
