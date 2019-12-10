<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Thread</th>
        <th>Last Used Date</th>
        <th>Available Qty(KG)</th>
        <th>Un Used Qty(KG)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($threadColors as $threadColorKey => $threadColor)
        <tr>
            <td>{{$threadColorKey + 1}}</td>
            <td>({{$threadColor->product->thread->denier}}) {{$threadColor->product->thread->name}}
                -{{$threadColor->product->color->name}}</td>
            <td>{{\Carbon\Carbon::parse($threadColor->last_used_date)->format('d M Y')}}</td>
            <td>{{$threadColor->available_count}}</td>
            <td>{{$threadColor->unused_qty}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
