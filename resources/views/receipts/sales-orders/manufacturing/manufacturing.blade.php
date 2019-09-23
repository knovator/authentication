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

    @php
    $machineData  = $machine->orderCopiedMachines ?? $machine;
    @endphp

    <div class="{{(!$loop->last) ? 'page':''}}">
        <main class="without-border">
            <div class="text-center">
                Manufacturing Receipt
            </div>
            <div class="receipt-heading text-center">{{$machineData->name}}</div>
            <div class="text-center">
                <small>Panno : {{$machineData->panno}}</small>
            </div>
            <br/>
            <div class="details-box">
                <div class="detail-row">
                    <table>
                        <tbody>
                        <tr>
                            <td class="text-left label">Order No.</td>
                            <td>: {{$salesOrder->order_no}}</td>
                            <td class="text-left label">Order Date</td>
                            <td>: {{\Carbon\Carbon::parse($salesOrder->order_date)->format('d M Y')}}</td>
                        </tr>
                        <tr>
                            <td class="text-left label">Beam</td>
                            <td>
                                :
                                <span
                                    class="color-preview -block -no-color"
                                    style="display:inline; background-color: dodgerblue"
                                >({{$salesOrder->designBeam->threadColor->thread->denier}})</span
                                >
                                {{$salesOrder->designBeam->threadColor->thread->name}} ({{$salesOrder->designBeam->threadColor->color->name}})
                            </td>
                            <td class="text-left label">Design Name</td>
                            <td>: {{$salesOrder->design->quality_name}}</td>
                        </tr>
                        <tr>
                            <td class="text-left label">Delivery Date</td>
                            <td>: {{\Carbon\Carbon::parse($delivery->delivery_date)->format('d M Y')}}</td>
                            <td class="text-left label">
                                Pick
                                <small>(on loom)</small>
                            </td>
                            <td>
                                : {{$salesOrder->design->detail->pick_on_loom}} {{($salesOrder->design->detail->creming)?' - Creming':''}}</td>
                        </tr>
                        <tr>
                            <td class="text-left label">Customer</td>
                            <td>
                                : {{$salesOrder->customer->full_name}}{{ (!is_null($salesOrder->customer_po_number)) ? ' ('.$salesOrder->customer_po_number.')':'' }}</td>
                            <td class="text-left label">Worker Name</td>
                            <td>:</td>
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
              >({{$fiddle->thread->denier}})</span>{{$fiddle->thread->name}} <br>({{$fiddle->color->name}})
                            </td>
                        @endforeach

                        <td class="total-mtr text-center">{{ ($machineData->panno !== 1) ? ($soPartialOrder->total_meters / $machineData->panno) : $soPartialOrder->total_meters}}</td>
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
