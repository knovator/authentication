<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Order No</th>
        <th>Customer</th>
        <th>Order Date</th>
        <th>Threads</th>
        <th>Quantity</th>
        <th>Rate</th>
        <th>Total Rate</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $orderKey => $order)
        
        @if($order->customer)
            @php
                $rowSpan = count($order->threads);
            @endphp
            <tr>
                <td rowspan="{{$rowSpan}}">{{$orderKey + 1}}</td>
                <td rowspan="{{$rowSpan}}">{{$order->order_no}}</td>
                <td rowspan="{{$rowSpan}}">{{$order->customer->full_name}}</td>
                <td rowspan="{{$rowSpan}}">{{\Carbon\Carbon::parse($order->order_date)->format('d M Y')}}</td>
                <td>{{$order->threads[0]->thread_color->thread->denier.' - '.$order->threads[0]->thread_color->thread->name.' ('.$order->threads[0]->thread_color->color->name.')'}}</td>
                <td>{{$order->threads[0]->kg_qty}}</td>
                <td>{{$order->threads[0]->rate}}</td>
                <td>{{$order->threads[0]->kg_qty * $order->threads[0]->rate}}</td>

            </tr>
            @php
                unset($order->threads[0]);
            @endphp

            @foreach($order->threads as $thread)
                <tr>
                    <td>{{$thread->thread_color->thread->denier.' - '.$thread->thread_color->thread->name.' ('.$thread->thread_color->color->name.')'}}</td>
                    <td>{{$thread->kg_qty}}</td>
                    <td>{{$thread->rate}}</td>
                    <td>{{$thread->kg_qty * $thread->kg_qty}}</td>
                </tr>
            @endforeach
        @endif

    @endforeach
    </tbody>
</table>
