<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Thread</th>
        <th>Used Qty(KG)</th>
        <th>Purchased Delivered(KG)</th>
        <th>Available Qty(KG)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($threadColors as $threadColorKey => $threadColor)
        <tr>
            <td>{{$threadColorKey + 1}}</td>
            <td>({{$threadColor->product->thread->denier}}) {{$threadColor->product->thread->name}}
                -{{$threadColor->product->color->name}}</td>
            <td>{{$threadColor->so_used}}</td>
            <td>{{$threadColor->po_delivered}}</td>
            <td>{{$threadColor->available_count}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
