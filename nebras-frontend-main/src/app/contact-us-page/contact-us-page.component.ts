import { Component, OnInit, HostListener } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule, NgIf } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { ContactUsComponent } from 'app/home/contact-us/contact-us.component';
import { TranslateService } from '@ngx-translate/core';
import { ContactService } from 'app/shared/services/contact-us/contact-us.service';
import { ContactRequest } from 'app/shared/models/contact-us'
import { TranslateModule } from '@ngx-translate/core';

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
  selector: 'app-contact-us-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    ContactUsComponent, NgIf, TranslateModule
  ],
  templateUrl: './contact-us-page.component.html',
  styleUrl: './contact-us-page.component.scss'
})
export class ContactUsPageComponent implements OnInit {

  form!: FormGroup;

  phonePlaceholder = 'XXXXXXXX';
  countryFlag = '';
  currentLang: string = 'en';
  formSubmitted = false;


  // ================= SUBJECT DROPDOWN =================
  subjectOpen = false;

  subjects: string[] = [
    'contact.subject.support',
    'contact.subject.inquiry',
    'contact.subject.suggestion'
  ];

  selectedSubject: string = 'contact.subject.support';


  // ====================================================

  private COUNTRIES: CountryJson[] = [];

  dropdowns: Record<DDKey, {
    open: boolean;
    highlighted: number;
    selected: number;
    search: string;
    options: CountryOption[];
  }> = {
      country: {
        open: false,
        highlighted: -1,
        selected: 0,
        search: '',
        options: []
      }
    };

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private translate: TranslateService,
    private contactService: ContactService
  ) { }

  ngOnInit(): void {

    this.form = this.fb.group({
      full_name: [
        '',
        [
          Validators.required,
          Validators.maxLength(50)
        ]
      ],

      email: ['', [Validators.required, Validators.email]],

      country: ['', Validators.required],

      phone: [
        '',
        [
          Validators.required,
          Validators.maxLength(10),
          Validators.pattern(/^[0-9]+$/)
        ]
      ],

      message: [
        '',
        [
          Validators.required,
          Validators.maxLength(200)
        ]
      ]
    });


    // Detect language
    this.currentLang = this.translate.currentLang || 'en';

    this.translate.onLangChange.subscribe(event => {
      this.currentLang = event.lang;
    });

    this.loadCountries();
  }

  // ================= COUNTRY LOGIC =================
  loadCountries(): void {
    this.http.get<CountryJson[]>('app/assets/json/countries.json')
      .subscribe({
        next: (countries) => {

          this.COUNTRIES = countries;

          this.dropdowns.country.options = this.COUNTRIES.map(
            (c: CountryJson): CountryOption => ({
              value: c.country_code,
              name: c.country_name_english,
              arName: c.country_name_arabic,
              dial: c.phone_code,
              flag: c.flag,
              placeholder: 'XXXXXXXX'
            })
          );

          this.initializeDropdown();
        },
        error: (err) => console.error('Failed to load countries:', err)
      });
  }

  private initializeDropdown(): void {

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
    this.dropdowns[key].open = !this.dropdowns[key].open;
    this.subjectOpen = false; // close subject if country opens
  }

  select(key: DDKey, index: number): void {

    const dd = this.dropdowns[key];
    const filteredList = this.filtered(key);
    const selectedItem = filteredList[index];

    dd.open = false;

    const fullIndex = dd.options
      .findIndex((o: CountryOption) => o.value === selectedItem.value);

    if (fullIndex !== -1) {
      dd.selected = fullIndex;
      this.countryFlag = selectedItem.flag;
      this.phonePlaceholder = 'XXXXXXXX';
      this.form.patchValue({ country: selectedItem.value });
    }
  }

  filtered(key: DDKey): CountryOption[] {
    const dd = this.dropdowns[key];
    const q = (dd.search || '').toLowerCase().trim();

    if (!q) return dd.options;

    return dd.options.filter((o: CountryOption) =>
      o.name.toLowerCase().includes(q) ||
      o.arName.toLowerCase().includes(q) ||
      o.value.toLowerCase().includes(q)
    );
  }

  // ================= SUBJECT LOGIC =================
  toggleSubject(): void {
    this.subjectOpen = !this.subjectOpen;
    this.dropdowns.country.open = false; // close country if subject opens
  }

  selectSubject(value: string): void {
    this.selectedSubject = value;
    this.subjectOpen = false;
  }

  // ================= PHONE INPUT =================
  onPhoneInput(ev: Event): void {
    const input = ev.target as HTMLInputElement;
    const v = input.value.replace(/\D/g, '').slice(0, 10);
    input.value = v;
    this.form.get('phone')?.setValue(v, { emitEvent: false });
  }


  // ================= CLOSE ON OUTSIDE CLICK =================
  @HostListener('document:click')
  onDocClick(): void {
    this.dropdowns.country.open = false;
    this.subjectOpen = false;
  }

  // ================= SUBMIT =================
  onSubmit(): void {

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const payload: ContactRequest = {
      full_name: this.form.value.full_name,
      email: this.form.value.email,
      phone: this.form.value.phone, // ✅ WITHOUT country code
      country: this.form.value.country,
      subject: this.selectedSubject,
      message: this.form.value.message
    };

    this.contactService.sendContact(payload).subscribe({
      next: (res) => {
        if (res.status) {
          console.log('Success:', res.message);
          this.formSubmitted = true;
        }
      },
      error: (err) => {
        console.error('API Error:', err);
      }
    });
  }

}
