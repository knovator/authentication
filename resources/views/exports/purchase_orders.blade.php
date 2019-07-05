<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Customer Details</th>
        <th>Order No</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $orderKey => $order)
        <tr>
            <td rowspan="4">{{$orderKey + 1}}</td>
            <td>Name : Prashantkumar Morem</td>
        </tr>
        <tr>
            <td>Email1 : 9898870303</td>
        </tr>
        <tr>
            <td>Email2 : 9898870303</td>
        </tr>
        <tr>
            <td>Email3 : 9898870303</td>
        </tr>
        <tr>
            <td>Email3 : 9898870303</td>
    @endforeach
    </tbody>
</table>
