<table>
    <thead>
    <tr>
        <th>Sr No</th>
        <th>Thread</th>
        <th>Available Qty(KG)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($threadColors as $threadColorKey => $threadColor)
        <tr>
            <td>{{$threadColorKey + 1}}</td>
            <td>({{$threadColor->thread->denier}}) {{$threadColor->thread->name}}
                -{{$threadColor->color->name}}</td>
            <td>{{(!is_null($threadColor->available_stock)) ? $threadColor->available_stock->available_qty: 0.0}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
