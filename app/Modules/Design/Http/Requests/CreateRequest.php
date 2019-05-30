<?php

namespace App\Modules\Design\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\Design\Http\Requests
 */
class CreateRequest extends FormRequest
{

    use APIResponse;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            // design details
            'name'                      => 'required|string|max:60',
            'quality_name'              => 'required|string|max:60',
            'type'                      => 'required|string|max:60',
            'fiddles'                   => 'required|integer',
            'is_active'                 => 'required|boolean',
            'designer_no'               => 'required|string|max:60',
            'avg_pick'                  => 'required|numeric',
            'pick_on_loom'              => 'required|numeric',
            'panno'                     => 'required|integer',
            'additional_panno'          => 'required|integer',
            'reed'                      => 'required|string',
            // design images
            'images'                    => 'required|array',
            'images.*.file_id'          => 'required|integer',
            'images.*.type'             => 'required|in:MAIN,SUB',

            // design fiddle picks
            'fiddle_picks'              => 'required|array',
            'fiddle_picks.*.pick'       => 'required|numeric',
            'fiddle_picks.*.fiddle_no'  => 'required|integer',

            // design beams
            'design_beams'              => 'required|array',
            'design_beams.*.beam_id'    => 'required|integer',
            'design_beams.*.recipes_id' => 'required|array',


        ];
    }


}
