@extends('portal.layouts.app')
@section('title', __('applications.create.title'))
@section('page-title', __('applications.create.page_title'))

@section('content')
@php $user = auth('portal')->user(); @endphp

<div class="max-w-2xl" x-data="applicationForm()">
    <div class="mb-6">
        <h1 class="text-title text-text">{{ __('applications.create.heading') }}</h1>
        <p class="text-body text-secondary mt-1">{{ __('applications.create.subtitle') }}</p>
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
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.full_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" x-model="form.full_name" required
                            class="input-base w-full @error('full_name') input-error @enderror">
                        @error('full_name') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.ic_no') }} <span class="text-danger">*</span></label>
                        <input type="text" name="ic_no" x-model="form.ic_no" required maxlength="12"
                            class="input-base w-full @error('ic_no') input-error @enderror">
                        @error('ic_no') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.date_of_birth') }}</label>
                        <input type="date" name="date_of_birth" x-model="form.date_of_birth"
                            class="input-base w-full @error('date_of_birth') input-error @enderror">
                        @error('date_of_birth') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.gender') }}</label>
                        <select name="gender" x-model="form.gender" class="input-base w-full @error('gender') input-error @enderror">
                            <option value="">{{ __('applications.create.gender_placeholder') }}</option>
                            <option value="male">{{ __('applications.gender.male') }}</option>
                            <option value="female">{{ __('applications.gender.female') }}</option>
                        </select>
                        @error('gender') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.phone') }} <span class="text-danger">*</span></label>
                        <input type="text" name="phone" x-model="form.phone" required
                            class="input-base w-full @error('phone') input-error @enderror">
                        @error('phone') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.email') }} <span class="text-danger">*</span></label>
                        <input type="email" name="email" x-model="form.email" required
                            class="input-base w-full @error('email') input-error @enderror">
                        @error('email') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.address') }}</label>
                        <textarea name="address" x-model="form.address" rows="2" class="input-base w-full @error('address') input-error @enderror"></textarea>
                        @error('address') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.district') }}</label>
                        <input type="text" name="district" x-model="form.district"
                            class="input-base w-full @error('district') input-error @enderror">
                        @error('district') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.state') }}</label>
                        <select name="state" x-model="form.state" class="input-base w-full @error('state') input-error @enderror">
                            <option value="">{{ __('applications.create.state_placeholder') }}</option>
                            @foreach($states as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('state') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.postcode') }}</label>
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
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.marital_status') }}</label>
                        <select name="marital_status" x-model="form.marital_status" class="input-base w-full @error('marital_status') input-error @enderror">
                            <option value="">{{ __('common.actions.select') }}</option>
                            <option value="single">{{ __('applications.marital.single') }}</option>
                            <option value="married">{{ __('applications.marital.married') }}</option>
                            <option value="divorced">{{ __('applications.marital.divorced') }}</option>
                            <option value="widowed">{{ __('applications.marital.widowed') }}</option>
                        </select>
                        @error('marital_status') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.household_count') }} <span class="text-danger">*</span></label>
                        <input type="number" name="household_count" x-model="form.household_count" required min="1" max="20"
                            class="input-base w-full @error('household_count') input-error @enderror">
                        @error('household_count') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.occupation') }}</label>
                        <input type="text" name="occupation" x-model="form.occupation"
                            class="input-base w-full @error('occupation') input-error @enderror">
                        @error('occupation') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.income_source') }}</label>
                        <select name="income_source" x-model="form.income_source" class="input-base w-full @error('income_source') input-error @enderror">
                            <option value="">{{ __('common.actions.select') }}</option>
                            <option value="employment">{{ __('applications.income_source.employment') }}</option>
                            <option value="business">{{ __('applications.income_source.business') }}</option>
                            <option value="pension">{{ __('applications.income_source.pension') }}</option>
                            <option value="none">{{ __('applications.income_source.none') }}</option>
                            <option value="other">{{ __('applications.income_source.other') }}</option>
                        </select>
                        @error('income_source') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.monthly_income') }} <span class="text-danger">*</span></label>
                        <input type="number" name="monthly_income" x-model="form.monthly_income" required min="0" step="0.01"
                            class="input-base w-full @error('monthly_income') input-error @enderror">
                        @error('monthly_income') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.spouse_income') }}</label>
                        <input type="number" name="spouse_income" x-model="form.spouse_income" min="0" step="0.01"
                            class="input-base w-full @error('spouse_income') input-error @enderror">
                        @error('spouse_income') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2 pt-3 border-t border-divider">
                        <p class="text-body font-semibold text-text mb-3">{{ __('applications.create.assistance_section') }}</p>
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.assistance_type') }}</label>
                        <select name="assistance_type" x-model="form.assistance_type" class="input-base w-full @error('assistance_type') input-error @enderror">
                            <option value="">{{ __('applications.create.assistance_type_placeholder') }}</option>
                            @foreach($assistanceTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('assistance_type') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.assistance_frequency') }}</label>
                        <select name="assistance_frequency" x-model="form.assistance_frequency" class="input-base w-full @error('assistance_frequency') input-error @enderror">
                            <option value="">{{ __('common.actions.select') }}</option>
                            <option value="monthly">{{ __('applications.frequency.monthly') }}</option>
                            <option value="one_off">{{ __('applications.frequency.one_off') }}</option>
                        </select>
                        @error('assistance_frequency') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.ic_document') }}</label>
                        <input type="file" name="ic_document" accept=".pdf,.jpg,.jpeg,.png"
                            class="input-base w-full @error('ic_document') input-error @enderror">
                        @error('ic_document') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('applications.create.salary_document') }}</label>
                        <input type="file" name="salary_document" accept=".pdf,.jpg,.jpeg,.png"
                            class="input-base w-full @error('salary_document') input-error @enderror">
                        @error('salary_document') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Step 3: Semakan --}}
            <div x-show="step === 2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-small">
                    <div><span class="text-secondary">{{ __('applications.create.review.name') }}</span> <span class="font-medium" x-text="form.full_name || '-'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.ic') }}</span> <span class="font-medium" x-text="form.ic_no || '-'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.phone') }}</span> <span class="font-medium" x-text="form.phone || '-'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.email') }}</span> <span class="font-medium" x-text="form.email || '-'"></span></div>
                    <div class="sm:col-span-2"><span class="text-secondary">{{ __('applications.create.review.address') }}</span> <span class="font-medium" x-text="form.address || '-'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.district') }}</span> <span class="font-medium" x-text="form.district || '-'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.state') }}</span> <span class="font-medium" x-text="form.state || '-'"></span></div>
                    <div class="pt-3 border-t border-divider sm:col-span-2"></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.households') }}</span> <span class="font-medium" x-text="form.household_count || '1'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.status') }}</span> <span class="font-medium" x-text="form.marital_status || '-'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.income') }}</span> <span class="font-medium">RM <span x-text="parseFloat(form.monthly_income || 0).toFixed(2)"></span></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.spouse_income') }}</span> <span class="font-medium">RM <span x-text="parseFloat(form.spouse_income || 0).toFixed(2)"></span></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.assistance_type') }}</span> <span class="font-medium" x-text="form.assistance_type || '-'"></span></div>
                    <div><span class="text-secondary">{{ __('applications.create.review.frequency') }}</span> <span class="font-medium" x-text="form.assistance_frequency || '-'"></span></div>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-divider flex items-center justify-between gap-3">
                <div>
                    <button type="button" class="btn btn-ghost btn-md" x-show="step > 0" @click="step--">
                        <i class="fa-solid fa-arrow-left text-[13px]"></i>
                        <span>{{ __('applications.create.before') }}</span>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('user.applications.index') }}" class="btn btn-ghost btn-md">{{ __('common.actions.cancel') }}</a>
                    <button type="button" class="btn btn-outline btn-md" x-show="step < 2" @click="nextStep()">
                        <span>{{ __('applications.create.next') }}</span>
                        <i class="fa-solid fa-arrow-right text-[13px]"></i>
                    </button>
                    <button type="submit" class="btn btn-primary btn-md" x-show="step === 2">
                        <i class="fa-solid fa-paper-plane text-[13px]"></i>
                        <span>{{ __('applications.create.submit') }}</span>
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
            steps: [
                @json(__('applications.create.steps.personal')),
                @json(__('applications.create.steps.household')),
                @json(__('applications.create.steps.review')),
            ],
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
                        alert(@json(__('applications.create.required_fields')));
                        return;
                    }
                }
                if (this.step === 1) {
                    if (!this.form.household_count || this.form.monthly_income === '') {
                        alert(@json(__('applications.create.required_fields')));
                        return;
                    }
                }
                this.step++;
            },
        };
    }
</script>
@endpush
