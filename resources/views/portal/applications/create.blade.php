@extends('portal.layouts.app')
@section('title', 'Mohon Bantuan — Portal Pemohon')
@section('page-title', 'Mohon Bantuan')

@section('content')
@php $user = auth('portal')->user(); @endphp

<div class="max-w-2xl" x-data="applicationForm()">
    <div class="mb-6">
        <h1 class="text-title text-text">Mohon Bantuan Zakat</h1>
        <p class="text-body text-secondary mt-1">Isi maklumat permohonan anda. Permohonan akan disimpan sebagai draf.</p>
    </div>

    <x-app-card class="p-6 mb-5">
        <div class="flex items-center gap-2 mb-6">
            <template x-for="(label, i) in steps" :key="i">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2" :class="i < step ? 'text-primary' : (i === step ? 'text-primary font-semibold' : 'text-tertiary')">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-[12px] font-semibold border-2"
                            :class="i < step ? 'bg-primary text-white border-primary' : (i === step ? 'border-primary text-primary' : 'border-divider')"
                            x-text="i + 1"></span>
                        <span class="text-small hidden sm:inline" x-text="label"></span>
                    </div>
                    <template x-if="i < steps.length - 1">
                        <div class="w-8 sm:w-12 h-px" :class="i < step ? 'bg-primary' : 'bg-divider'"></div>
                    </template>
                </div>
            </template>
        </div>

        <form method="POST" action="{{ route('user.applications.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Step 1: Maklumat Peribadi --}}
            <div x-show="step === 0">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Nama Penuh <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" x-model="form.full_name" required
                            class="input-base w-full @error('full_name') input-error @enderror">
                        @error('full_name') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">No. Kad Pengenalan <span class="text-danger">*</span></label>
                        <input type="text" name="ic_no" x-model="form.ic_no" required maxlength="12"
                            class="input-base w-full @error('ic_no') input-error @enderror">
                        @error('ic_no') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Tarikh Lahir</label>
                        <input type="date" name="date_of_birth" x-model="form.date_of_birth"
                            class="input-base w-full @error('date_of_birth') input-error @enderror">
                        @error('date_of_birth') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Jantina</label>
                        <select name="gender" x-model="form.gender" class="input-base w-full @error('gender') input-error @enderror">
                            <option value="">Pilih Jantina</option>
                            <option value="male">Lelaki</option>
                            <option value="female">Perempuan</option>
                        </select>
                        @error('gender') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">No. Telefon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" x-model="form.phone" required
                            class="input-base w-full @error('phone') input-error @enderror">
                        @error('phone') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Emel <span class="text-danger">*</span></label>
                        <input type="email" name="email" x-model="form.email" required
                            class="input-base w-full @error('email') input-error @enderror">
                        @error('email') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-small font-medium text-text mb-1.5">Alamat</label>
                        <textarea name="address" x-model="form.address" rows="2" class="input-base w-full @error('address') input-error @enderror"></textarea>
                        @error('address') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Daerah</label>
                        <input type="text" name="district" x-model="form.district"
                            class="input-base w-full @error('district') input-error @enderror">
                        @error('district') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Negeri</label>
                        <select name="state" x-model="form.state" class="input-base w-full @error('state') input-error @enderror">
                            <option value="">Pilih Negeri</option>
                            @foreach($states as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('state') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Poskod</label>
                        <input type="text" name="postcode" x-model="form.postcode" maxlength="5"
                            class="input-base w-full @error('postcode') input-error @enderror">
                        @error('postcode') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Step 2: Isi Rumah & Pendapatan --}}
            <div x-show="step === 1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Status Perkahwinan</label>
                        <select name="marital_status" x-model="form.marital_status" class="input-base w-full @error('marital_status') input-error @enderror">
                            <option value="">Pilih</option>
                            <option value="single">Bujang</option>
                            <option value="married">Berkahwin</option>
                            <option value="divorced">Bercerai</option>
                            <option value="widowed">Janda/Duda</option>
                        </select>
                        @error('marital_status') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Bil. Isi Rumah <span class="text-danger">*</span></label>
                        <input type="number" name="household_count" x-model="form.household_count" required min="1" max="20"
                            class="input-base w-full @error('household_count') input-error @enderror">
                        @error('household_count') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Pekerjaan</label>
                        <input type="text" name="occupation" x-model="form.occupation"
                            class="input-base w-full @error('occupation') input-error @enderror">
                        @error('occupation') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Sumber Pendapatan</label>
                        <select name="income_source" x-model="form.income_source" class="input-base w-full @error('income_source') input-error @enderror">
                            <option value="">Pilih</option>
                            <option value="employment">Pekerjaan</option>
                            <option value="business">Perniagaan</option>
                            <option value="pension">Pencen</option>
                            <option value="none">Tiada</option>
                            <option value="other">Lain-lain</option>
                        </select>
                        @error('income_source') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Pendapatan Bulanan (RM) <span class="text-danger">*</span></label>
                        <input type="number" name="monthly_income" x-model="form.monthly_income" required min="0" step="0.01"
                            class="input-base w-full @error('monthly_income') input-error @enderror">
                        @error('monthly_income') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Pendapatan Pasangan (RM)</label>
                        <input type="number" name="spouse_income" x-model="form.spouse_income" min="0" step="0.01"
                            class="input-base w-full @error('spouse_income') input-error @enderror">
                        @error('spouse_income') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2 pt-3 border-t border-divider">
                        <p class="text-body font-semibold text-text mb-3">Jenis Bantuan & Dokumen</p>
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Jenis Bantuan</label>
                        <select name="assistance_type" x-model="form.assistance_type" class="input-base w-full @error('assistance_type') input-error @enderror">
                            <option value="">Pilih Jenis Bantuan</option>
                            @foreach($assistanceTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('assistance_type') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Kekerapan</label>
                        <select name="assistance_frequency" x-model="form.assistance_frequency" class="input-base w-full @error('assistance_frequency') input-error @enderror">
                            <option value="">Pilih</option>
                            <option value="monthly">Bulanan</option>
                            <option value="one_off">One-Off</option>
                        </select>
                        @error('assistance_frequency') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Salinan Kad Pengenalan (PDF/JPG/PNG)</label>
                        <input type="file" name="ic_document" accept=".pdf,.jpg,.jpeg,.png"
                            class="input-base w-full @error('ic_document') input-error @enderror">
                        @error('ic_document') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Slip Gaji / Penyata Pendapatan</label>
                        <input type="file" name="salary_document" accept=".pdf,.jpg,.jpeg,.png"
                            class="input-base w-full @error('salary_document') input-error @enderror">
                        @error('salary_document') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Step 3: Semakan --}}
            <div x-show="step === 2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-small">
                    <div><span class="text-secondary">Nama:</span> <span class="font-medium" x-text="form.full_name || '-'"></span></div>
                    <div><span class="text-secondary">No. KP:</span> <span class="font-medium" x-text="form.ic_no || '-'"></span></div>
                    <div><span class="text-secondary">Telefon:</span> <span class="font-medium" x-text="form.phone || '-'"></span></div>
                    <div><span class="text-secondary">Emel:</span> <span class="font-medium" x-text="form.email || '-'"></span></div>
                    <div class="sm:col-span-2"><span class="text-secondary">Alamat:</span> <span class="font-medium" x-text="form.address || '-'"></span></div>
                    <div><span class="text-secondary">Daerah:</span> <span class="font-medium" x-text="form.district || '-'"></span></div>
                    <div><span class="text-secondary">Negeri:</span> <span class="font-medium" x-text="form.state || '-'"></span></div>
                    <div class="pt-3 border-t border-divider sm:col-span-2"></div>
                    <div><span class="text-secondary">Isi Rumah:</span> <span class="font-medium" x-text="form.household_count || '1'"></span></div>
                    <div><span class="text-secondary">Status:</span> <span class="font-medium" x-text="form.marital_status || '-'"></span></div>
                    <div><span class="text-secondary">Pendapatan:</span> <span class="font-medium">RM <span x-text="parseFloat(form.monthly_income || 0).toFixed(2)"></span></span></div>
                    <div><span class="text-secondary">Pendapatan Pasangan:</span> <span class="font-medium">RM <span x-text="parseFloat(form.spouse_income || 0).toFixed(2)"></span></span></div>
                    <div><span class="text-secondary">Jenis Bantuan:</span> <span class="font-medium" x-text="form.assistance_type || '-'"></span></div>
                    <div><span class="text-secondary">Kekerapan:</span> <span class="font-medium" x-text="form.assistance_frequency || '-'"></span></div>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-divider flex items-center justify-between gap-3">
                <div>
                    <button type="button" class="btn btn-ghost btn-md" x-show="step > 0" @click="step--">
                        <i class="fa-solid fa-arrow-left text-[13px]"></i>
                        <span>Sebelum</span>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('user.applications.index') }}" class="btn btn-ghost btn-md">Batal</a>
                    <button type="button" class="btn btn-outline btn-md" x-show="step < 2" @click="nextStep()">
                        <span>Seterusnya</span>
                        <i class="fa-solid fa-arrow-right text-[13px]"></i>
                    </button>
                    <button type="submit" class="btn btn-primary btn-md" x-show="step === 2">
                        <i class="fa-solid fa-paper-plane text-[13px]"></i>
                        <span>Hantar Permohonan</span>
                    </button>
                </div>
            </div>
        </form>
    </x-app-card>
</div>
@endsection

@push('scripts')
<script>
    function applicationForm() {
        return {
            step: 0,
            steps: ['Peribadi', 'Isi Rumah', 'Semak'],
            form: {
                full_name: @json(old('full_name', $user->name)),
                ic_no: @json(old('ic_no', $user->ic_no)),
                date_of_birth: @json(old('date_of_birth', $user->date_of_birth?->format('Y-m-d'))),
                gender: @json(old('gender', $user->gender)),
                phone: @json(old('phone', $user->phone)),
                email: @json(old('email', $user->email)),
                address: @json(old('address', $user->address)),
                district: @json(old('district', $user->district)),
                state: @json(old('state', $user->state)),
                postcode: @json(old('postcode', $user->postcode)),
                marital_status: @json(old('marital_status')),
                household_count: @json(old('household_count', 1)),
                occupation: @json(old('occupation')),
                income_source: @json(old('income_source')),
                monthly_income: @json(old('monthly_income', 0)),
                spouse_income: @json(old('spouse_income', 0)),
                assistance_type: @json(old('assistance_type')),
                assistance_frequency: @json(old('assistance_frequency')),
            },
            nextStep() {
                if (this.step === 0) {
                    if (!this.form.full_name || !this.form.ic_no || !this.form.phone || !this.form.email) {
                        alert('Sila isi semua ruangan wajib (*) sebelum meneruskan.');
                        return;
                    }
                }
                if (this.step === 1) {
                    if (!this.form.household_count || this.form.monthly_income === '') {
                        alert('Sila isi semua ruangan wajib (*) sebelum meneruskan.');
                        return;
                    }
                }
                this.step++;
            },
        };
    }
</script>
@endpush
