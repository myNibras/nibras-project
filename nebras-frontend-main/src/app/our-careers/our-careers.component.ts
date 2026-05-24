import { Component, OnInit, HostListener } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { TranslateModule } from '@ngx-translate/core';
import { CareersService } from 'app/shared/services/careers/careers.service';

type DDKey = 'country';

interface CountryJson {
  country_name_english: string;
  country_name_arabic: string;
  flag: string;
  phone_code: string;
  phone_placeholder: string;
  country_code: string;
  currency_code: string;
}

interface CountryOption {
  value: string;
  name: string;
  arName: string;
  dial: string;
  flag: string;
  placeholder: string;
}

@Component({
  selector: 'app-our-careers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule],
  templateUrl: './our-careers.component.html',
  styleUrl: './our-careers.component.scss'
})
export class OurCareersComponent implements OnInit {

  form!: FormGroup;

  selectedFileName = '';
  currentLang = 'en';

  phonePlaceholder = 'XXXXXXXX';
  countryFlag = '';

  isSubmitted = false;
  isLoading = false;

  private COUNTRIES: CountryJson[] = [];

  dropdowns: Record<DDKey, any> = {
    country: { open: false, selected: 0, search: '', options: [] }
  };

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private translate: TranslateService,
    private careersService: CareersService
  ) { }

  ngOnInit(): void {

    this.form = this.fb.group({
      experience: [
        '',
        [
          Validators.required,
          Validators.pattern(/^[0-9]+$/)
        ]
      ],
      specialization: ['', Validators.required],
      name: ['', Validators.required],
      country: ['', Validators.required],
      phone: [
        '',
        [
          Validators.required,
          Validators.maxLength(10),
          Validators.pattern(/^[0-9]+$/)
        ]
      ],
      email: ['', [Validators.required, Validators.email]],
      cv: [null, Validators.required]
    });

    this.currentLang = this.translate.currentLang || 'en';

    this.translate.onLangChange.subscribe(event => {
      this.currentLang = event.lang;
    });

    this.loadCountries();
  }

  onExperienceInput(event: Event): void {
    const input = event.target as HTMLInputElement;

    const value = input.value.replace(/\D/g, '');

    input.value = value;

    this.form.get('experience')?.setValue(value, { emitEvent: false });
  }

  loadCountries(): void {

    this.http.get<CountryJson[]>('app/assets/json/countries.json')
      .subscribe({
        next: (countries) => {

          this.COUNTRIES = countries;

          this.dropdowns.country.options = this.COUNTRIES.map((c: CountryJson): CountryOption => ({
            value: c.country_code,
            name: c.country_name_english,
            arName: c.country_name_arabic,
            dial: c.phone_code,
            flag: c.flag,
            placeholder: c.phone_placeholder || 'XXXXXXXX'
          }));

          this.initializeCountry();
        },
        error: (err) => console.error('Failed to load countries:', err)
      });
  }

  private initializeCountry(): void {

    const qatarIndex = this.dropdowns.country.options
      .findIndex((o: CountryOption) => o.value === 'QA');

    this.dropdowns.country.selected = qatarIndex >= 0 ? qatarIndex : 0;

    const selected = this.dropdowns.country.options[this.dropdowns.country.selected];

    this.countryFlag = selected.flag;
    this.phonePlaceholder = 'XXXXXXXX';

    this.form.patchValue({
      country: selected.value
    });
  }

  toggle(key: DDKey): void {

    const wasOpen = this.dropdowns[key].open;

    Object.keys(this.dropdowns).forEach(k => {
      this.dropdowns[k as DDKey].open = false;
    });

    this.dropdowns[key].open = !wasOpen;
  }

  selectCountry(index: number): void {

    const selectedItem = this.dropdowns.country.options[index];

    this.dropdowns.country.open = false;
    this.dropdowns.country.selected = index;

    this.countryFlag = selectedItem.flag;
    this.phonePlaceholder = 'XXXXXXXX';

    this.form.patchValue({ country: selectedItem.value });
  }

  onPhoneInput(ev: Event): void {

    const input = ev.target as HTMLInputElement;

    const v = input.value.replace(/\D/g, '').slice(0, 10);

    input.value = v;

    this.form.get('phone')?.setValue(v, { emitEvent: false });
  }

  onFileSelect(event: any): void {

    const file = event.target.files[0];

    if (file) {

      this.selectedFileName = file.name;

      this.form.patchValue({ cv: file });
    }
  }

  @HostListener('document:click')
  onDocClick(): void {

    Object.keys(this.dropdowns).forEach(k => {
      this.dropdowns[k as DDKey].open = false;
    });
  }

  onSubmit(): void {

    if (this.form.invalid) {

      this.form.markAllAsTouched();
      return;
    }

    this.isLoading = true;

    const selected = this.dropdowns.country.options[this.dropdowns.country.selected];

    const fullPhone = selected.dial + this.form.value.phone;

    const formData = new FormData();

    formData.append('full_name', this.form.value.name);
    formData.append('email', this.form.value.email);
    formData.append('phone_number', fullPhone);
    formData.append('years_of_experience', this.form.value.experience);
    formData.append('major_specialization', this.form.value.specialization);

    if (this.form.value.cv) {
      formData.append('cv', this.form.value.cv);
    }

    this.careersService.sendContact(formData).subscribe({
      next: () => {

        this.isLoading = false;
        this.isSubmitted = true;

        this.form.reset();
        this.selectedFileName = '';

        this.initializeCountry();
      },
      error: (err) => {

        this.isLoading = false;
        console.error('Submission error:', err);
      }
    });
  }
}