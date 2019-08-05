<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Order No</th>
        <th>Order Date</th>
        <th>Customer Details</th>
        <th>Threads</th>
        <th>Total</th>
        <th>Status</th>
        <th>Challan No</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $orderKey => $order)

        @if($order->customer)

            <tr>
                <td rowspan="4">{{$orderKey + 1}}</td>
                <td rowspan="4">{{$order->order_no}}</td>
                <td rowspan="4">{{\Carbon\Carbon::parse($order->order_date)->format('D m Y')}}</td>
                <td>{{$order->customer->full_name}}</td>
                <td rowspan="4">@foreach($order->threads as $orderThread){{$orderThread->thread_color->thread->name}}
                    ({{$orderThread->kg_qty. ' KG'}})@if(!$loop->last){{','}}@endif @endforeach

                </td>
                <td rowspan="4">{{$order->thread_qty->total. ' KG'}}</td>
                <td rowspan="4">{{$order->status->name}}</td>
                <td rowspan="4">{{$order->challan_no}}</td>
            </tr>
            <tr>
                <td>Phone: {{$order->customer->phone}}</td>
            </tr>
            <tr>
                <td>GST No: {{$order->customer->gst_no}}</td>
            </tr>
            <tr>
                <td>{{$order->customer->address.','.$order->customer->city_name}}</td>
            </tr>
        @endif

    @endforeach
    </tbody>
</table>