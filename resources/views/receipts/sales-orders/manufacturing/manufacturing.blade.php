<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Manufacturing Receipt</title>
    <style type="text/css">
        div.page {
            page-break-after: always;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

@foreach($machines as $machine)
    <div class="{{(!$loop->last) ? 'page':''}}">
        <main class="without-border">
            <div class="text-center">
                Manufacturing Receipt
            </div>
            <div class="receipt-heading text-center">{{$machine->name}}</div>
            <div class="text-center">
                <small>Panno : {{$machine->panno}}</small>
            </div>
            <br/>
            <div class="details-box">
                <div class="detail-row">
                    <table>
                        <tbody>
                        <tr>
                            <td class="text-left label">Order No.</td>
                            <td>: {{$salesOrder->order_no}}</td>
                            <td class="text-left label">Date</td>
                            <td>: {{\Carbon\Carbon::parse($salesOrder->order_date)->format('d, M Y')}}</td>
                        </tr>
                        <tr>
                            <td class="text-left label">Beam</td>
                            <td>
                                :
                                <span
                                    class="color-preview -block -no-color"
                                    style="display:inline; background-color: dodgerblue"
                                >({{$machine->threadColor->thread->denier}})</span
                                >
                                {{$machine->threadColor->thread->name}} ({{$machine->threadColor->color->name}})
                            </td>
                            <td class="text-left label">Reed</td>
                            <td>: 98</td>
                        </tr>
                        <tr>
                            <td class="text-left label">Designer No</td>
                            <td>: {{$salesOrder->design->detail->designer_no}}</td>
                            <td class="text-left label">
                                Pick
                                <small>(on loom)</small>
                            </td>
                            <td>
                                : {{$salesOrder->design->detail->pick_on_loom}} {{($salesOrder->design->detail->creming)?' - Creming':''}}</td>
                        </tr>
                        <tr>
                            <td class="text-left label">Delivery Date</td>
                            <td>: {{\Carbon\Carbon::parse($delivery->delivery_date)->format('d, M Y')}}</td>
                            {{--<td class="text-left label">Bill No.</td>
                            <td>: B124</td>--}}
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <br/>
            <div class="table-heading">Recipes</div>
            <table class="listing-table">
                <thead class="recipes-header">
                <tr class="header-row">
                    <th class="sr-no">#</th>
                    @foreach($salesOrder->design->fiddlePicks as $fiddlePickKey => $fiddlePick)
                        <th>F{{$fiddlePickKey + 1}} ({{$fiddlePick->pick}})</th>
                    @endforeach
                    <th class="total-mtr text-center">Mtrs.</th>
                </tr>
                </thead>
                <tbody class="recipes-content">


                @foreach($machine->soPartialOrders as $soPartialOrderKey => $soPartialOrder)
                    <tr class="content-row">
                        <td class="sr-no">{{$soPartialOrderKey + 1}}</td>

                        @foreach($soPartialOrder->orderRecipe->recipe->fiddles as $fiddle)
                            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >({{$fiddle->thread->denier}})</span
              >
                                {{$fiddle->thread->name}} ({{$fiddle->color->name}})
                            </td>
                        @endforeach

                        <td class="total-mtr text-center">{{ ($machine->panno !== 1) ? ($soPartialOrder->total_meters / $machine->panno) : $soPartialOrder->total_meters}}</td>
                    </tr>
                @endforeach


                </tbody>
            </table>
        </main>
    </div>
@endforeach


</body>
<link href="{{asset('css/global_receipt.css')}}" rel="stylesheet" type="text/css"/>
</html>