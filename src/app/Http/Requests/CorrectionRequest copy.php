<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class CorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    protected function prepareForValidation()
    {
        $this->merge([
            'clock_in' => $this->normalizeTime($this->clock_in),
            'clock_out' => $this->normalizeTime($this->clock_out),
            'breaks' => collect($this->breaks ?? [])->map(function ($break) {
                return [
                    'start' => $this->normalizeTime($break['start'] ?? null),
                    'end' => $this->normalizeTime($break['end'] ?? null),
                ];
            })->toArray(),
        ]);
    }

    private function normalizeTime(?string $value): ?string
    {
        if (!$value) return null;

        if (preg_match('/[０-９：]/u', $value)) {
            return null;
        }

        if (preg_match('/^\d{4}$/', $value)) {
            return substr($value, 0, 2) . ':' . substr($value, 2, 2);
        }

        if (preg_match('/^\d{1,2}$/', $value)) {
            return str_pad($value, 2, '0', STR_PAD_LEFT) . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value;
        }
        return null;
    }

    public function rules(): array
    {
        return [
            'attendance_id' => ['required'],
            'user_id' => ['required'],
            'admin_id' => ['nullable'],
            'clock_in'       => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'clock_out'      => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'breaks.*.start' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'breaks.*.end'   => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'reason' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.regex'       => '出勤時間は "HH:mm"(半角数字)形式で入力してください',
            'clock_out.regex'      => '退勤時間は "HH:mm"(半角数字)形式で入力してください',
            'breaks.*.start.regex' => '休憩開始時間は "HH:mm"(半角数字)形式で入力してください',
            'breaks.*.end.regex'   => '休憩終了時間は "HH:mm"(半角数字)形式で入力してください',
            'reason.required' => '備考を記入してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $fields = [
                'clock_in' => $this->input('clock_in'),
                'clock_out' => $this->input('clock_out'),
            ];

            foreach ($this->input('breaks') ?? [] as $i => $break) {
                $fields["breaks.$i.start"] = $break['start'] ?? '';
                $fields["breaks.$i.end"] = $break['end'] ?? '';
            }

            foreach ($fields as $key => $value) {
                if (!empty($value) && preg_match('/[０-９：]/u', $value)) {
                    $validator->errors()->add($key, '半角"HH:mm"で入力してください');
                }
            }

            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;
            $breaks = $this->breaks ?? [];

            $in = (!empty($clockIn) && preg_match('/^\d{2}:\d{2}$/', $clockIn))
                ? Carbon::createFromFormat('H:i', $clockIn)
                : null;
            $out = (!empty($clockOut) && preg_match('/^\d{2}:\d{2}$/', $clockOut))
                ? Carbon::createFromFormat('H:i', $clockOut)
                : null;

            if ($in && $out && $in->gte($out)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($breaks as $i => $break) {
                $start = (!empty($break['start']) && preg_match('/^\d{2}:\d{2}$/', $break['start']))
                    ? Carbon::createFromFormat('H:i', $break['start']) : null;

                $end = (!empty($break['end']) && preg_match('/^\d{2}:\d{2}$/', $break['end']))
                    ? Carbon::createFromFormat('H:i', $break['end']) : null;

                if ($start && $in && $start->lte($in)) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }
                if ($start && $out && $start->gte($out)) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }

                if ($end && $out && $end->gte($out)) {
                    $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                }

                if ($start && $end && $start->gte($end)) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }
            }
        });
    }
}
