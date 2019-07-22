<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Sales Order Summary</title>
</head>
<body>
<main>
    <div class="receipt-heading text-center">{{($isInvoice) ? 'TAX INVOICE':'ORDER FORM'}}</div>
    <div class="text-center">
        <small
        >(Invoice For supply of goods u/s 31 of GST Act, 2017 read with Rule 1
            of tax invoice rules 2017)
        </small>
    </div>

    <table>
        <tbody>
        <tr>
            <td
                style="
    border-bottom: 0;
"
            >
                <div class="preview-box">
                    <img
                        alt="design name"
                        class="preview"
                        src="{{$salesOrder->design->mainImage->file->url}}"
                    />
                </div>
            </td>
            <td
                style="
    border-bottom: 0;
"
            >
                <div></div>
                <table class="auto-layout right-bordered">
                    <tbody>
                    <tr>
                        <td>
                            <div>
                                <b>{{ (!is_null($salesOrder->manufacturingCompany)) ? $salesOrder->manufacturingCompany->name:'JENNY TEXO FAB'}}</b>
                            </div>
                            <small>
                                PLOT NO: M-3/6-7-8, ROAD NO: 23, VIBHAG-2, HOJIWALA IND,
                                ESTATE, SACHIN, SURAT.
                            </small>
                            <div>
                                SURAT - 394230
                            </div>
                            <div>INDIA</div>
                            <div><b>PHONE NO :</b> 98256 57870, 99099 11500</div>
                            <div><b>State :</b> GUJARAT &amp; <b>Code :</b> 24</div>
                            <div><b>GST NO :</b> 24AAPFMN352G13AZ8</div>
                        </td>
                        <td>
                            <!--                        <b class="underlined">Customers Details</b>-->
                            <div><b>{{strtoupper($salesOrder->customer->full_name)}}</b></div>
                            <div>
                                <small>
                                    {{strtoupper($salesOrder->customer->address.', '.$salesOrder->customer->city_name)}}
                                    .
                                </small>
                            </div>
                            <div><b>PHONE NO :</b> {{$salesOrder->customer->phone}}</div>
                            <div><b>State :</b> {{strtoupper($salesOrder->customer->state->name)}} {{--&amp; <b>Code :</b>
                                24--}}
                            </div>
                            <div><b>GST NO :</b> {{strtoupper($salesOrder->customer->gst_no)}}</div>
                            {{--                            <div><b>PAN NO :</b> AAPFM7520G</div>--}}
                        </td>
                    </tr>
                    <tr></tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                    <tr>
                        <td class="text-left label"><b>DESIGN NAME</b>: {{$salesOrder->design->quality_name}}</td>
                        <td class="text-left label"><b>ORDER NO</b>: {{$salesOrder->order_no}}</td>
                        <td class="text-left label"><b>ORDER
                                DATE</b>: {{\Carbon\Carbon::parse($salesOrder->order_date)->format('d M Y')}}</td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <br/>
    {{--    <div class="table-heading">Recipes</div>--}}
    <table class="listing-table auto-layout">
        <thead>
        <tr>
            <th class="sr-no text-left">#</th>
            <th class="text-left">Recipes</th>
            <th>Quantity (Mtr.)</th>
            <th>Rate (INR)</th>
            <th class="text-right">Amount</th>
        </tr>
        </thead>
        <tbody>
        <!--  for one recipe details starts here-->


        @php
            $totalQuantity = 0;
        @endphp

        @foreach($salesOrder->orderRecipes as $orderRecipeKey => $orderRecipe)
            @php
                $key = $orderRecipeKey + 1;
                    if ($isInvoice){
                        $partialQuantity =  $orderRecipe->partialOrders->sum('total_meters');
                        $totalQuantity = $totalQuantity + $partialQuantity;

                    }else{
                      $totalQuantity = $totalQuantity +  $orderRecipe->total_meters;
                    }

            @endphp
            <tr>
                <td class="sr-no text-left"><b>{{$key}}</b></td>
                <td><b>{{$orderRecipe->recipe->name}}</b></td>
                <td class="text-center"><b>{{$orderRecipe->total_meters}}</b>
                    @if($isInvoice && $partialQuantity)
                        <span style="font-size: 12px">({{$partialQuantity}})</span>
                    @endif
                </td>
                <td class="text-center"></td>
                <td class="text-right"></td>
            </tr>
            <!-- recipes partial orders delivery wise details -->
            @if($orderRecipe->partialOrders->isNotEmpty())
                @foreach($orderRecipe->partialOrders->sortBy('delivery.delivery_date')->values()->all() as $partialOrderKey => $partialOrder)
                    <tr>
                        <td class="sr-no text-left"></td>
                        <td>
                            {{$key.'.'.($partialOrderKey + 1).') '.\Carbon\Carbon::parse($partialOrder->delivery->delivery_date)->format('d, M Y')}}
                            <small><em>({{$partialOrder->delivery->delivery_no}})</em></small>
                        </td>
                        <td class="text-center">{{$partialOrder->total_meters}}</td>
                        <td class="text-center"></td>
                        <td class="text-right">{{$partialOrder->total_meters * $salesOrder->cost_per_meter }}</td>
                    </tr>
                @endforeach
            @endif
        @endforeach

        @php
            $price = $totalQuantity * $salesOrder->cost_per_meter;
        @endphp

        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td><b>TOTAL : </b></td>
            <td class="text-center"><b>{{$totalQuantity}}</b></td>
            <td class="text-center">{{$salesOrder->cost_per_meter}}</td>
            <td class="text-right"><b>{{$price}}</b></td>
        </tr>
        </tfoot>
    </table>

    <table class="listing-table  auto-layout right-bordered">
        <thead>
        <tr>
            <td class="label" colspan="4">TERMS &amp; CONDITIONS:</td>
            <td class="label" colspan="2">ASSESSABLE VALUE</td>
            <td></td>
            <td></td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="4" class="text-left" style="vertical-align: baseline;">
                <div>
                    <table class="no-border tnc-table low-space-table">
                        <tbody>
                        <tr>
                            <td
                                style="
    padding: 0;
"
                            >
                                1) Subject To SURAT Jurisdiction E. &amp; O.E.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                2) Payment Will be accepted only By A/c, Payer's Draft /
                                Cheque.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                3) Any Complaint regarding goods should be re[ported in
                                writing within 24 ours of the receipt of goods.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                4) No responsibility for ay defect in yarn after weaving
                                &amp; Processing.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                5) Goods Sold will not be taken back.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                6) The Goods are dispatched on your account and at about
                                risk &amp; responsibility.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                7) Use Yarn Batch Wise Only.
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td>
                                <b
                                >Received the above goods in good condition and order
                                    along with transpoter invoice copy.</b
                                >
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </td>
            <td
                colspan="4"
                class="text-left"
                style="
    padding: 0;
    position: relative;
    border: 0;
    vertical-align: top;
"
            >
                <div>
                    <table class="right-bordered gst-table">
                        <tbody>
                        @if($salesOrder->customer->state->code == 'GUJARAT')
                            <tr>
                                <td class=" label">SGST</td>
                                <td class="text-center">2.50 %</td>
                                <td class="text-right">{{($price / 100) * 2.5}}</td>
                            </tr>
                            <tr>
                                <td class=" label">CGST</td>
                                <td class="text-center">2.50 %</td>
                                <td class="text-right">{{($price / 100) * 2.5}}</td>
                            </tr>
                        @else
                            <tr>
                                <td class=" label">IGST</td>
                                <td class="text-center">5.00 %</td>
                                <td class="text-right">{{($price / 100) * 5}}</td>
                            </tr>
                        @endif
                        <tr class="highlight-row" style="position: absolute;bottom: 0;width: 100%;">
                            <td class="label" style="
        border-right: 0 !important;
    border-bottom: 0;
        float: left;
    ">
                                GRAND TOTAL :
                            </td>
                            <td class="text-right"
                                style="border-bottom: 0;    float: right;">{{$price = $price + (($price / 100) * 5) }}</td>
                        </tr>
                        <tr></tr>
                        <tr></tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="rupees-in-words">
        Total Value Of Goods In Word:
        <span>
          <b>{{displayWords($price)}}</b>
        </span>
    </div>
    <table class="auto-layout receipt-footer">
        <tbody>
        <tr>
            <td class="declaration" style="border-left: 0">
                <p>
                    Certified that the perticulars given above are true amd correct and
                    the amount indicated represents tje price actually charged and that
                    there is no flow of additional consideration directly or indirectly
                    from the buyer.
                </p>
                <br/>
                <b>Prepared By:</b>
            </td>
            <td class="signature" style="vertical-align: top">
                <span class="text-center -agency"><b>For, JENNY/SIDDHI TEXO FAB</b></span>
                <br/>
                <br/>
                <br/>
                <br/>
                <span class="text-center -signatory"><b>Authorised Signatory</b></span>
            </td>
        </tr>
        </tbody>
    </table>
</main>
</body>
<link href="{{asset('css/global_receipt.css')}}" rel="stylesheet" type="text/css"/>
</html>
