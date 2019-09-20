<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Accounting Receipt</title>
</head>
<body>
<!--/*

fontSize: 100% / (totalFiddle * 7)  <= 14 && 100% / (totalFiddle * 7) >= 10
    ? middleW() / totalFiddle / 7
    : 13

*/-->
<main>
    <div class="text-center receipt-small-heading ">
        Accounting Receipt
    </div>
    <div class="receipt-heading text-center">{{\Carbon\Carbon::parse($delivery->delivery_date)->format('d M Y')}}
        {{ (!is_null($delivery->bill_no)) ? '('.$delivery->bill_no.')':'' }}
    </div>
    <br/>
    <table>
        <tbody>
        <tr>
            <td style="
    border-bottom: 0;
">
                <div class="preview-box">
                    <img alt="design name" class="preview"
                         src="{{$salesOrder->design->mainImage->file->url}}"
                </div>
            </td>
            <td style="
    border-bottom: 0;
">
                <div>
                </div>
                <table class="auto-layout right-bordered">
                    <tbody>
                    <tr>
                        @if(!is_null($salesOrder->manufacturingCompany))
                            <td>
                                <div>
                                    <b>{{$salesOrder->manufacturingCompany->name}}</b>
                                </div>
                                <small>
                                    {{$salesOrder->manufacturingCompany->address}}
                                    , {{$salesOrder->manufacturingCompany->city}}.
                                </small>
                                <div>
                                    {{$salesOrder->manufacturingCompany->city.' - '.$salesOrder->manufacturingCompany->pin_code}}
                                </div>
                                <div>{{$salesOrder->manufacturingCompany->country}}</div>
                                <div><b>PHONE NO :</b> {{$salesOrder->manufacturingCompany->phone}}</div>
                                <div><b>State :</b> {{$salesOrder->manufacturingCompany->state}} &amp; <b>Code
                                        :</b> {{$salesOrder->manufacturingCompany->state_code}}</div>
                                <div><b>GST NO :</b> {{$salesOrder->manufacturingCompany->gst_no}}</div>
                            </td>
                        @else
                            <td>
                                <div>
                                    <b>JENNY TEXO FAB</b>
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
                        @endif

                        <td>
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
                            <div><b>PO NO :</b> {{strtoupper($salesOrder->customer_po_number)}}</div>
                        </td>
                    </tr>
                    <tr>
                    </tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                    <tr>
                        <td class="text-left label"><b>ORDER NO</b>: {{$salesOrder->order_no}}</td>
                        <td class="text-left label"><b>DESIGN NAME</b>: {{$salesOrder->design->quality_name}}</td>
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
    <table class="listing-table">
        <thead class="recipes-header">
        <tr class="header-row">
            <td class="sr-no text-center">#</td>
            <td>Colors</td>
            <td class="text-center">Quantity (Mtr.)</td>
            <td class="text-center">Rate (INR)</td>
            <td class="text-right">Amount</td>
        </tr>
        </thead>
        <tbody class="recipes-content">


        @foreach($delivery->partialOrders as $partialOrderKey => $partialOrder)
            <tr class="content-row">
                <td class="sr-no text-center">{{$partialOrderKey + 1}}</td>
                <td>{{$partialOrder->orderRecipe->recipe->fiddles->first()->color->name}}</td>
                <td class="text-center">{{$partialOrder->total_meters}}</td>
                <td class="text-center"></td>
                <td class="text-right">{{$partialOrder->total_meters * $salesOrder->cost_per_meter}}</td>
            </tr>
        @endforeach
        </tbody>

        @php
            $totalQuantity = $delivery->partialOrders->sum('total_meters');
            $price = $totalQuantity * $salesOrder->cost_per_meter;
        @endphp

        <tfoot>
        <tr>
            <td></td>
            <td><b>TOTAL : </b></td>
            <td class="text-center"><b>{{$totalQuantity}}</b></td>
            <td class="text-center">{{$salesOrder->cost_per_meter}}</td>
            <td class="text-right"><img class="rupee-sign" src="{{asset('img/rupee.png')}}"><b>{{$price}}</b></td>
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
                            <td>1) Payment will be accepted only by A/C, Payee's Draft/ Cheque.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                2) Goods sold will not be taken back.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                3) Any complaint regarding goods should be reported in writing within 24 hours of the
                                receipt of goods.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                4) Interest will be charged @24% p.a.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                5) The goods are dispatched on your account at your risk & responsibility.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                6) Subject to Surat Jurisdiction E. & O.E.
                            </td>
                        </tr>
                        </tbody>
                        {{--                        <tfoot>--}}
                        {{--                        <tr>--}}
                        {{--                            <td>--}}
                        {{--                                <b--}}
                        {{--                                >Received the above goods in good condition and order--}}
                        {{--                                    along with transpoter invoice copy.</b--}}
                        {{--                                >--}}
                        {{--                            </td>--}}
                        {{--                        </tr>--}}
                        {{--                        </tfoot>--}}
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
                                style="border-bottom: 0;    float: right;"><img class="rupee-sign"
                                                                                src="{{asset('img/rupee.png')}}">{{$price = $price + (($price / 100) * 5) }}
                            </td>
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
                    Certified that the particulars given above are true and the correct and the amount indicated
                    represents the price actually charged and that there is no flow of additional consideration directly
                    or indirectly from the buyer.
                </p>
                <br/>
                <b>Prepared By:</b>
            </td>
            <td class="signature" style="vertical-align: top">
                <span
                    class="text-center -agency"><b>For, {{ (!is_null($salesOrder->manufacturingCompany)) ? $salesOrder->manufacturingCompany->name:'JENNY TEXO FAB'}}</b></span>
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
