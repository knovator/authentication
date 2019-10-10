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
            <td>({{$threadColor->product->thread->denier}}) {{$threadColor->product->thread->name}}
                -{{$threadColor->product->color->name}}</td>
            <td>{{$threadColor->available_count}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
