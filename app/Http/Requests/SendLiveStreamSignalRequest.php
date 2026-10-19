<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendLiveStreamSignalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sender_session_id' => 'required|string',
            'target_session_id' => 'required|string',
            'signal_type'       => 'required|string|in:viewer_join,host_offer,viewer_answer,ice_candidate,ice_candidates_batch,host_ready',
            'signal_data'       => 'nullable|string',
        ];
    }
}
