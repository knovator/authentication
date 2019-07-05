<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Customer Name</th>
    </tr>
    </thead>

    <tbody>
    @foreach($orders as $orderKey => $order)
        <tr>
            <td>{{$orderKey + 1}}</td>
            <td>{{$order->customer->full_name}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
