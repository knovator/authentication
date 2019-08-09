<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Order No</th>
        <th>Customer</th>
        <th>Order Date</th>
        <th>Design</th>
        <th>Total Qty</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $orderKey => $order)

        @if($order->customer)
            <tr>
                <td rowspan="5">{{$orderKey + 1}}</td>
                <td rowspan="5">{{$order->order_no}}</td>
                <td rowspan="5">{{$order->customer->full_name}}</td>
                <td rowspan="5">{{\Carbon\Carbon::parse($order->order_date)->format('D m Y')}}</td>
                <td rowspan="5">{{$order->design->quality_name}}</td>
                <td>Total Mtr : {{ (!is_null($order->recipe_meters)) ? $order->recipe_meters->total:0 }}</td>
                <td rowspan="5">{{$order->status->name}}</td>
            </tr>
            <tr>
                <td>Pending Mtr : {{ (!is_null($order->pending_meters)) ? $order->pending_meters:0 }}</td>
            </tr>
            <tr>
                <td>Manufacturing Mtr : {{ (!is_null($order->manufacturing_total_meters)) ? $order->manufacturing_total_meters->total:0 }}</td>
            </tr>
            <tr>
                <td>Delivered Mtr : {{ (!is_null($order->delivered_total_meters)) ? $order->delivered_total_meters->total:0 }}</td>
            </tr>

            <tr>
                <td>Cost Per Mtr : {{ $order->cost_per_meter }}</td>
            </tr>
        @endif

    @endforeach
    </tbody>
</table>
