<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Wastage Order Summary</title>
</head>
<body>
<main>
    <div class="receipt-heading text-center">{{($isInvoice) ? 'TAX INVOICE':'ORDER FORM'}}</div>
    <div class="text-center">
        <small>(Invoice For supply of goods u/s 31 of GST Act, 2017 read with Rule 6 of tax invoice rules 2017)
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
                        src="{{$wastageOrder->design->mainImage->file->url}}"
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
                        @if(!is_null($wastageOrder->manufacturingCompany))
                            <td>
                                <div>
                                    <b>{{$wastageOrder->manufacturingCompany->name}}</b>
                                </div>
                                <small>
                                    {{$wastageOrder->manufacturingCompany->address}}
                                    , {{$wastageOrder->manufacturingCompany->city}}.
                                </small>
                                <div>
                                    {{$wastageOrder->manufacturingCompany->city.' - '.$wastageOrder->manufacturingCompany->pin_code}}
                                </div>
                                <div>{{$wastageOrder->manufacturingCompany->country}}</div>
                                <div><b>PHONE NO :</b> {{$wastageOrder->manufacturingCompany->phone}}</div>
                                <div><b>State :</b> {{$wastageOrder->manufacturingCompany->state}} &amp; <b>Code
                                        :</b> {{$wastageOrder->manufacturingCompany->state_code}}</div>
                                <div><b>GST NO :</b> {{$wastageOrder->manufacturingCompany->gst_no}}</div>
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
                            <!--                        <b class="underlined">Customers Details</b>-->
                            <div><b>{{strtoupper($wastageOrder->customer->full_name)}}</b></div>
                            <div>
                                <small>
                                    {{strtoupper($wastageOrder->customer->address.', '.$wastageOrder->customer->city_name)}}
                                    .
                                </small>
                            </div>
                            <div><b>PHONE NO :</b> {{$wastageOrder->customer->phone}}</div>
                            <div><b>State :</b> {{strtoupper($wastageOrder->customer->state->name)}} {{--&amp; <b>Code :</b>
                                 24--}}
                            </div>
                            <div><b>GST NO :</b> {{strtoupper($wastageOrder->customer->gst_no)}}</div>
                            <div><b>PO NO :</b> {{strtoupper($wastageOrder->customer_po_number)}}</div>
                        </td>
                    </tr>
                    <tr></tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                    <tr>
                        <td class="text-left label"><b>ORDER NO</b>: {{$wastageOrder->order_no}}</td>
                        <td class="text-left label"><b>ORDER
                                DATE</b>: {{\Carbon\Carbon::parse($wastageOrder->order_date)->format('d M Y')}}</td>
                        <td class="text-left label"><b>DESIGN NAME</b>: {{$wastageOrder->design->quality_name}}</td>
                        @if(!is_null($wastageOrder->challan_no))
                            <td class="text-left label"><b>Challan No</b>: {{$wastageOrder->challan_no}}</td>
                        @endif
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
            <th class="text-left">Colors</th>
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

        @foreach($wastageOrder->orderRecipes as $orderRecipeKey => $orderRecipe)
            @php
                $totalQuantity = $totalQuantity +  $orderRecipe->total_meters;
            @endphp
            <tr>
                <td class="sr-no text-left"><b>{{$orderRecipeKey + 1}}</b></td>
                <td><b>{{$orderRecipe->recipe->fiddles->first()->color->name}}</b></td>
                <td class="text-center"><b>{{$orderRecipe->total_meters}}</b></td>
                <td class="text-center"></td>
                <td class="text-right"></td>
            </tr>
        @endforeach

        @php
            $price = $totalQuantity * $wastageOrder->cost_per_meter;
        @endphp

        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td><b>TOTAL : </b></td>
            <td class="text-center"><b>{{$totalQuantity}}</b></td>
            <td class="text-center">{{$wastageOrder->cost_per_meter}}</td>
            <td class="text-right d-flex"><img class="rupee-sign" src="{{asset('img/rupee.png')}}"><b>{{$price}}</b>
            </td>
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
                        @if($wastageOrder->customer->state->code == 'GUJARAT')
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
                    class="text-center -agency"><b>For, {{ (!is_null($wastageOrder->manufacturingCompany)) ? $wastageOrder->manufacturingCompany->name:'JENNY TEXO FAB'}}</b></span>
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
