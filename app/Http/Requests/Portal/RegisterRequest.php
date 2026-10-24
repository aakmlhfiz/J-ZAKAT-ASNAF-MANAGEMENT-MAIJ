<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ic_no' => ['required', 'string', 'size:12', 'unique:user.users,ic_no'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'phone' => ['required', 'string', 'max:15'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                'unique:user.users,email',
                Rule::unique('admins', 'email'),
            ],
            'address' => ['required', 'string', 'max:500'],
            'district' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'in:Johor,Kedah,Kelantan,Melaka,Negeri Sembilan,Pahang,Perak,Perlis,Pulau Pinang,Sabah,Sarawak,Selangor,Terengganu,Kuala Lumpur,Putrajaya,Labuan'],
            'postcode' => ['required', 'string', 'size:5'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama penuh diperlukan.',
            'ic_no.required' => 'No Kad Pengenalan diperlukan.',
            'ic_no.size' => 'No Kad Pengenalan mesti 12 digit.',
            'ic_no.unique' => 'Maklumat yang anda masukkan telah didaftarkan.',
            'date_of_birth.required' => 'Tarikh lahir diperlukan.',
            'date_of_birth.before' => 'Tarikh lahir tidak sah.',
            'gender.required' => 'Jantina diperlukan.',
            'phone.required' => 'No Telefon diperlukan.',
            'email.required' => 'Emel diperlukan.',
            'email.email' => 'Emel tidak sah.',
            'email.unique' => 'Maklumat yang anda masukkan telah didaftarkan.',
            'address.required' => 'Alamat diperlukan.',
            'district.required' => 'Daerah diperlukan.',
            'state.required' => 'Negeri diperlukan.',
            'postcode.required' => 'Poskod diperlukan.',
            'postcode.size' => 'Poskod mesti 5 digit.',
            'password.required' => 'Kata laluan diperlukan.',
            'password.min' => 'Kata laluan mesti sekurang-kurangnya 8 aksara.',
            'password.confirmed' => 'Kata laluan tidak sepadan.',
        ];
    }
}
