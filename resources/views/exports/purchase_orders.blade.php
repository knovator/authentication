<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Customer Name</th>
        <th>Customer Details</th>
        <th>Order Date</th>
        <th>Order No</th>
        <th>Status</th>
    </tr>
    </thead>

    <tbody>
    @foreach($orders as $orderKey => $order)
        <tr>
            <td>{{$orderKey + 1}}</td>
            <td>{{$appliedCandidate->status->name}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
