<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Design</title>
</head>
<body>
<main class="without-border">
    <section class="section-1">
        <div class="preview-box">
            <img alt="design name" class="preview" src="{{$design->mainImage->file->url}}"/>
        </div>
        <div class="details-box">
            <div class="detail-row design-name">{{$design->quality_name}}
            </div>
            <table>
                <tbody>
                <tr>
                    <td class="text-left label">Reed</td>
                    <td>: {{$design->detail->reed}}</td>
                    <td class="text-left label">Avg. Pick</td>
                    <td>: {{$design->detail->avg_pick}}</td>
                </tr>
                <tr>
                    <td class="text-left label">Feeder</td>
                    <td>: {{$design->fiddles}}</td>
                    <td class="text-left label">Panno</td>
                    <td>: {{$design->detail->panno}} (+4)</td>
                </tr>
                <tr>
                    <td class="text-left label">Type</td>
                    <td>: {{ucfirst($design->type)}}</td>
                    <td class="text-left label">Creming</td>
                    <td>: {{ ($design->detail->creming) ? 'Yes':'No' }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
    <br/>
    <table class="listing-table">
        <thead class="recipes-header">
        <tr class="header-row">
            @foreach($design->fiddlePicks as $fiddle)
                <th>F{{$fiddle->fiddle_no}} ({{$fiddle->pick}})</th>
            @endforeach
        </tr>
        </thead>
    </table>


    @foreach($design->beams as $beam)
        <div class="beams-box">
            <div class="beam-name">
                <div class="color-preview -block"
                     style="background-color: {{$beam->threadColor->color->code}}; margin: 5px">{{$beam->threadColor->thread->denier}}</div>
                {{$beam->threadColor->thread->name}} ({{$beam->threadColor->color->name}})
            </div>
            <table class="listing-table">
                <tbody class="recipes-content">
                @foreach($beam->recipes as $recipe)
                    <tr class="content-row">
                        @foreach($recipe->fiddles as $fiddle)
                            <td class="text-center"><span class="color-preview -block"
                                                          style="background-color: {{$fiddle->color->code}}">{{$fiddle->thread->denier}}</span>
                                {{$fiddle->thread->name}} ({{$fiddle->color->name}})
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</main>
</body>
<link href="{{asset('css/global_receipt.css')}}" rel="stylesheet" type="text/css"/>
</html>
