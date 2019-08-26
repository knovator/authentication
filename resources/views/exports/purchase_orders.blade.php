<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Order No</th>
        <th>Customer</th>
        <th>Order Date</th>
        <th>Threads</th>
        <th>Delivery Date</th>
        <th>Challan No</th>
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
                <td rowspan="{{$rowSpan}}">{{\Carbon\Carbon::parse($order->order_date)->format('D m Y')}}</td>
                <td>{{'('.$order->threads[0]->thread_color->thread->denier.') '.$order->threads[0]->thread_color->thread->name.' ('.$order->threads[0]->thread_color->color->name.')'}}
                    : {{$order->threads[0]->kg_qty. ' KG'}}</td>

                @if(!isset($order->deliveries[0]))
                    <td rowspan="{{$rowSpan}}"></td>
                    <td rowspan="{{$rowSpan}}"></td>
                @endif

            </tr>
            @php
                unset($order->threads[0]);
            @endphp

            @foreach($order->threads as $thread)
                <tr>
                    <td>{{'('.$thread->thread_color->thread->denier.') '.$thread->thread_color->thread->name.' ('.$thread->thread_color->color->name.')'}}
                        : {{$thread->kg_qty. ' KG'}}</td>
                </tr>
            @endforeach


            @if(!empty($order->deliveries))
                @foreach($order->deliveries as $deliveryKey => $delivery)
                    @php
                        $rowSpan = count($delivery->partial_orders);
                    @endphp
                    <tr>
                        <td rowspan="{{$rowSpan}}"></td>
                        <td rowspan="{{$rowSpan}}"></td>
                        <td rowspan="{{$rowSpan}}"></td>
                        <td rowspan="{{$rowSpan}}"></td>
                        <td>{{'('.$delivery->partial_orders[0]->purchased_thread->thread_color->thread->denier.') '.$delivery->partial_orders[0]->purchased_thread->thread_color->thread->name.' ('.$delivery->partial_orders[0]->purchased_thread->thread_color->color->name.')'}}
                            : {{$delivery->partial_orders[0]->kg_qty. ' KG'}}</td>
                        <td rowspan="{{$rowSpan}}">{{\Carbon\Carbon::parse($delivery->delivery_date)->format('D m Y')}}</td>
                        <td rowspan="{{$rowSpan}}">{{$delivery->bill_no}}</td>
                    </tr>

                    @php
                        unset($delivery->partial_orders[0]);
                    @endphp

                    @foreach($delivery->partial_orders as $partialOrder)
                        <tr>
                            <td>{{'('.$partialOrder->purchased_thread->thread_color->thread->denier.') '.$partialOrder->purchased_thread->thread_color->thread->name.' ('.$partialOrder->purchased_thread->thread_color->color->name.')'}}
                                : {{$partialOrder->kg_qty. ' KG'}}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endif
        @endif
        <tr></tr>
    @endforeach
    </tbody>
</table>
