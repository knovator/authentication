<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Manufacturing Receipt</title>
</head>
<body>
<!--/*

fontSize: 100% / (totalFiddle * 7)  <= 14 && 100% / (totalFiddle * 7) >= 10
        ? middleW() / totalFiddle / 7
        : 13

*/-->
<main class="without-border">
    <div class="text-center">
        Manufacturing Receipt
    </div>
    <div class="receipt-heading text-center">Machine No. - 1</div>
    <div class="text-center">
        <small>Machine Panno : 2</small>
    </div>
    <br/>
    <div class="details-box">
        <div class="detail-row">
            <table>
                <tbody>
                <tr>
                    <td class="text-left label">Order No.</td>
                    <td>: O1454</td>
                    <td class="text-left label">Date</td>
                    <td>: 23 December, 2018</td>
                </tr>
                <tr>
                    <td class="text-left label">Beam</td>
                    <td>
                        :
                        <span
                            class="color-preview -block -no-color"
                            style="display:inline; background-color: dodgerblue"
                        >90</span
                        >
                        30kota (White)
                    </td>
                    <td class="text-left label">Reed</td>
                    <td>: 98</td>
                </tr>
                <tr>
                    <td class="text-left label">Designer No</td>
                    <td>: DZ124</td>
                    <td class="text-left label">
                        Pick
                        <small>(on loom)</small>
                    </td>
                    <td>: 398 - Creming</td>
                </tr>
                <tr>
                    <td class="text-left label">Delivery Date</td>
                    <td>: 12 December, 2018</td>
                    <td class="text-left label">Bill No.</td>
                    <td>: B124</td>
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
            <th>F1 (120)</th>
            <th>F2 (250)</th>
            <th>F3 (154)</th>
            <th>F4 (145)</th>
            <th>F5 (145)</th>
            <!--  <th>F3 (145)</th>
              <th>F3 (145)</th>
              <th>F3 (145)</th>
              <th>F3 (145)</th>
              <th>F3 (145)</th>
              <th>F3 (145)</th>-->
            <th class="total-mtr text-center">Mtrs.</th>
        </tr>
        </thead>
        <tbody class="recipes-content">
        <tr class="content-row">
            <td class="sr-no">1</td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >700</span
              >
                T7 (108-TUSSAR)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: dodgerblue"
              >90</span
              >
                T4 (1590-FIROZI)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: crimson"
              >150</span
              >
                LICHI (203-MAROON)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: blueviolet"
              >300</span
              >
                T5 (1296-BEGAN)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >100</span
              >
                T7 (108-TUSSAR)
            </td>
            <!-- <td class"text-center"><span class="color-preview -block -no-color" style="background-color: dodgerblue">470</span> T4 (1590-FIROZI)
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: blueviolet">400</span> T5 (1296-BEGAN)</td>
             </td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: crimson">150</span> LICHI (203-MAROON)
             </td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: blueviolet">182</span> T5 (1296-BEGAN)
             </td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: brown">340</span> T2 (Dark Brown)</td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: aquamarine">500</span> T7 (108-TUSSAR)
             </td>-->
            <td class="total-mtr text-center">30</td>
        </tr>
        <tr class="content-row">
            <td class="sr-no">2</td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: dodgerblue"
              >470</span
              >
                T4 (1590-FIROZI)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: crimson"
              >150</span
              >
                LICHI (203-MAROON)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: blueviolet"
              >182</span
              >
                T5 (1296-BEGAN)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: brown"
              >340</span
              >
                T2 (Dark Brown)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >500</span
              >
                T7 (108-TUSSAR)
            </td>
            <!--
              <td class"text-center"><span class="color-preview -block -no-color" style="background-color: blueviolet">300</span> T5 (1296-BEGAN)</td>
              <td class"text-center"><span class="color-preview -block -no-color" style="background-color: aquamarine">100</span> T7 (108-TUSSAR)</td>
              <td class"text-center"><span class="color-preview -block -no-color" style="background-color: blueviolet">400</span> T5 (1296-BEGAN)</td>
              <td class"text-center"><span class="color-preview -block -no-color" style="background-color: dodgerblue">470</span> T4 (1590-FIROZI)
              </td>
              <td class"text-center"><span class="color-preview -block -no-color" style="background-color: aquamarine">100</span> T7 (108-TUSSAR)</td>
              <td class"text-center"><span class="color-preview -block -no-color" style="background-color: blueviolet">300</span> T5 (1296-BEGAN)</td>-->
            <td class="total-mtr text-center">20</td>
        </tr>
        <tr class="content-row">
            <td class="sr-no">3</td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >700</span
              >
                T7 (108-TUSSAR)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: dodgerblue"
              >90</span
              >
                T4 (1590-FIROZI)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >100</span
              >
                T7 (108-TUSSAR)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: blueviolet"
              >300</span
              >
                T5 (1296-BEGAN)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >250</span
              >
                T7 (108-TUSSAR)
            </td>
            <!-- <td class"text-center"><span class="color-preview -block -no-color" style="background-color: dodgerblue">158</span> T4 (1590-FIROZI)
             </td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: brown">75</span> T2 (Dark Brown)</td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: crimson">600</span> LICHI (203-MAROON)
             </td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: aquamarine">700</span> T7 (108-TUSSAR)</td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: blueviolet">400</span> T5 (1296-BEGAN)</td>
             <td class"text-center"><span class="color-preview -block -no-color" style="background-color: dodgerblue">158</span> T4 (1590-FIROZI)
             </td>-->
            <td class="total-mtr text-center">10</td>
        </tr>
        <tr class="content-row">
            <td class="sr-no">4</td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: darkorchid"
              >45</span
              >
                T9 - (1454-WINE)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >250</span
              >
                T7 (108-TUSSAR)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: dodgerblue"
              >158</span
              >
                T4 (1590-FIROZI)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: brown"
              >75</span
              >
                T2 (Dark Brown)
            </td>
            <td class="text-center">
              <span
                  class="color-preview -block -no-color"
                  style="background-color: aquamarine"
              >700</span
              >
                T7 (108-TUSSAR)
            </td>
            <!--   <td class"text-center"><span class="color-preview -block -no-color" style="background-color: crimson">150</span> LICHI (203-MAROON)
               <td class"text-center"><span class="color-preview -block -no-color" style="background-color: dodgerblue">90</span> T4 (1590-FIROZI)</td>
               </td>
               <td class"text-center"><span class="color-preview -block -no-color" style="background-color: blueviolet">300</span> T5 (1296-BEGAN)</td>
               <td class"text-center"><span class="color-preview -block -no-color" style="background-color: crimson">600</span> LICHI (203-MAROON)
               </td>
               <td class"text-center"><span class="color-preview -block -no-color" style="background-color: dodgerblue">158</span> T4 (1590-FIROZI)
               </td>
               <td class"text-center"><span class="color-preview -block -no-color" style="background-color: aquamarine">700</span> T7 (108-TUSSAR)</td>-->
            <td class="total-mtr text-center">45</td>
        </tr>
        </tbody>
    </table>
</main>
</body>
<link href="{{asset('css/global_receipt.css')}}" rel="stylesheet" type="text/css"/>
</html>
