<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Customer Name</th>
        <th>Email</th>
        <th>Total Orders</th>
        <th>Total Meters</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $orderKey => $order)
        <tr>
            <td>{{$orderKey + 1}}</td>
            <td>{{$order->customer->full_name}}</td>
            <td>{{$order->customer->email}}</td>
            <td>{{$order->orders}}</td>
            <td>{{$order->meters}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
