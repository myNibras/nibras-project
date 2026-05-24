import { Component, ElementRef, EventEmitter, HostListener, OnInit, Output, ViewChild, OnDestroy, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators, FormGroup } from '@angular/forms';
import { LoginRegisterComponent } from '../../login-register.component';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { GoogleAuthService } from 'app/shared/services/google-auth/google-auth.service';
import { CookieService } from 'ngx-cookie-service';
import { ClassesService } from 'app/shared/services/classes/classes.service';
import { StorageService } from 'app/core/storage/storage.service';
import { Subject, takeUntil } from 'rxjs';
import { Grade } from 'app/shared/models/classes';
import { Country } from 'app/shared/models/country';
import { HttpClient } from '@angular/common/http';
import { strictEmailValidator } from 'app/shared/validators/email.validator';
import { Router, RouterLink } from '@angular/router';
import { ToastService } from 'app/shared/services/toast/toast.service';


type DDKey = 'country' | 'class' | 'gender';

interface CountryOption {
  code: string;
  name: string;
  arName: string;
  flag: string;
  dialCode: string;
}

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule, RouterLink],
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})

export class RegisterComponent implements OnInit, OnDestroy {
  @Input() parent?: LoginRegisterComponent;
  @Output() closeModal = new EventEmitter<boolean>();

  @ViewChild('phoneInput', { static: false }) phoneInput!: ElementRef<HTMLInputElement>;

  grades: Grade[] = [];
  registerForm!: FormGroup;
  loading: boolean = false;
  error_message: string = '';
  showPassword = false;
  showConfirmPassword = false;
  googleAvailable = false;
  countryFlag = 'https://flagcdn.com/w40/qa.png';
  phonePlaceholder = 'XXXXXXXX'; // unified placeholder
  private destroy$ = new Subject<void>();

  private COUNTRIES: CountryOption[] = [];

  dropdowns: Record<DDKey, any> = {
    country: {
      open: false,
      highlighted: -1,
      selected: 0,
      search: '',
      options: [] as Array<{ value: string; name: string; arName: string; dial: string; flag: string }>,
    },
    class: {
      open: false,
      highlighted: -1,
      selected: null,
      options: [] as Array<{ id: number; name: string }>,
    },
    gender: {
      open: false,
      highlighted: -1,
      selected: null,
      options: [
        { label: 'Male', value: '0' },
        { label: 'Female', value: '1' },
      ],
    },
  };



  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private googleAuthService: GoogleAuthService,
    private cookieService: CookieService,
    private translate: TranslateService,
    private classesService: ClassesService,
    public storageService: StorageService,
    private router: Router,
    private http: HttpClient,
    private toast: ToastService,
  ) { }

  private profileRoutes: Record<'ar' | 'en', string> = {
    ar: '/ar/الملف-الشخصي',
    en: '/en/profile',
  };

  loginRoutes: Record<'ar' | 'en', string> = {
    ar: '/ar/تسجيل-الدخول',
    en: '/en/login'
  };


  ngOnInit(): void {
    // Load countries JSON
    this.http.get<Country[]>('app/assets/json/countries.json').subscribe({
      next: (countries) => {
        this.COUNTRIES = countries.map(c => ({
          code: c.country_code,
          name: c.country_name_english,
          arName: c.country_name_arabic,
          flag: c.flag,
          dialCode: c.phone_code
        }));

        this.initializeDropdown(); // Initialize dropdown AFTER loading
      },
      error: (err) => console.error('Failed to load countries JSON:', err)
    });

    // Form setup
    this.registerForm = this.fb.group({
      name: this.fb.control('', {
        validators: [Validators.required, Validators.minLength(2), Validators.pattern(/^[\u0600-\u06FFa-zA-Z ]+$/)]
      }),
      email: ['', [Validators.required, strictEmailValidator()]],
      class_id: ['', Validators.required],
      age: ['', [Validators.required, Validators.min(3)]],
      gender: ['', Validators.required],
      country: ['', Validators.required],
      phone: ['', [Validators.required, Validators.minLength(7), Validators.maxLength(11)]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', Validators.required]
    }, { validators: [RegisterComponent.passwordsMatchValidator] });

    this.storageService.siteLanguage$.pipe(takeUntil(this.destroy$)).subscribe(() => {
      this.classesService.get().subscribe(data => {
        this.grades = data;
        this.dropdowns.class.options = this.grades.map(g => ({ id: g.id, name: g.name }));
      });
    });

    // Preload Google Identity Services so requestAccessToken() runs inside the
    // user-gesture window on mobile. If the SDK can't be reached (network or
    // tracker block), hide the Google button entirely so the user isn't stuck.
    if (this.googleAuthService.isAvailable()) {
      this.googleAuthService.loadScript().then(
        () => { this.googleAvailable = true; },
        (err) => {
          console.warn('[GoogleAuth] disabling button — SDK unreachable', err);
          this.googleAvailable = false;
        }
      );
    }
  }


  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
  private initializeDropdown() {
    // Map bilingual countries
    this.dropdowns.country.options = this.COUNTRIES.map(c => ({
      value: c.code,
      name: c.name,
      arName: c.arName,
      dial: c.dialCode,
      flag: c.flag
    }));

    // ✅ Set Qatar as default
    const qatarIndex = this.dropdowns.country.options.findIndex(
      (o: { value: string; name: string; arName: string; dial: string; flag: string }) => o.value === 'QA'
    );
    this.dropdowns.country.selected = qatarIndex >= 0 ? qatarIndex : 0;
    const c = this.dropdowns.country.options[this.dropdowns.country.selected];

    this.countryFlag = c.flag;
    this.phonePlaceholder = 'XXXXXXXX';
    this.registerForm.patchValue({
      country: c.value,
      phone: ''
    }, { emitEvent: false });
  }

  getLabel(key: DDKey): string {
    if (key === 'class') {
      const id = this.registerForm.get('class_id')?.value;
      return this.dropdowns.class.options.find((c: any) => c.id === id)?.name || '';
    }
    if (key === 'gender') {
      const val = this.registerForm.get('gender')?.value;
      const label = this.dropdowns.gender.options.find((g: any) => g.value === val)?.label;
      return label ? this.translate.instant(label) : '';
    }
    return '';
  }

  onSubmit() {
    const value = this.registerForm.getRawValue();
    this.loading = true;
    this.error_message = '';

    this.authService.register(value).subscribe({
      next: (data) => {
        this.loading = false;
        if (data.status) {
          this.cookieService.set('token', data.data.token, { path: '/', secure: true, sameSite: 'Strict' });
          this.cookieService.set('student', JSON.stringify(data.data.student), { path: '/', secure: true, sameSite: 'Strict' });
          this.authService.notifyLoginSuccess();
          this.showSuccessAndRedirectHome();
        }
      },
      error: (err) => {
        this.loading = false;
        if (err.error?.data) {
          const validationErrors = err.error.data;
          Object.keys(validationErrors).forEach(field => {
            const control = this.registerForm.get(field);
            if (control) {
              control.setErrors({ serverError: validationErrors[field][0] });
            }
          });
        } else {
          this.error_message = (err.error) ? err.error.message : this.translate.instant('something went wrong, please try again');
        }
      }
    });
  }

  toggle(key: DDKey) {
    // Close all others
    (Object.keys(this.dropdowns) as DDKey[]).forEach(k => {
      if (k !== key) this.dropdowns[k].open = false;
    });

    const dd = this.dropdowns[key];
    dd.open = !dd.open;
    if (dd.open) {
      dd.highlighted = Math.max(dd.selected ?? -1, -1);
    }
  }

  select(key: DDKey, index: number) {
    const dd = this.dropdowns[key];
    const filteredList = this.filtered(key);
    const selectedItem = filteredList[index];

    dd.open = false;

    if (key === 'country') {
      const fullIndex = dd.options.findIndex((o: any) => o.value === selectedItem.value);
      if (fullIndex !== -1) {
        dd.selected = fullIndex;
        this.countryFlag = selectedItem.flag;
        this.registerForm.patchValue({ country: selectedItem.value }, { emitEvent: false });
      }
    }

    if (key === 'class') {
      this.registerForm.patchValue({ class_id: selectedItem.id }, { emitEvent: false });
      dd.selected = index;
    }

    if (key === 'gender') {
      this.registerForm.patchValue({ gender: selectedItem.value }, { emitEvent: false });
      dd.selected = index;
    }
  }

  filtered(key: DDKey) {
    const dd = this.dropdowns[key];
    if (key !== 'country') return dd.options;

    const q = (dd.search || '').toLowerCase().trim();
    if (!q) return dd.options;

    return dd.options.filter((o: any) =>
      (o.name?.toLowerCase().includes(q)) ||
      (o.arName?.toLowerCase().includes(q)) ||
      (o.value?.toLowerCase().includes(q)) ||
      (o.dial?.includes(q))
    );
  }


  onCountrySearch(ev: Event) {
    const v = (ev.target as HTMLInputElement).value || '';
    this.dropdowns.country.search = v;
  }

  onKeydown(key: DDKey, ev: KeyboardEvent) {
    const dd = this.dropdowns[key];
    const max = dd.options.length - 1;

    switch (ev.key) {
      case 'ArrowDown':
        ev.preventDefault();
        dd.open = true;
        dd.highlighted = Math.min(max, (dd.highlighted ?? -1) + 1);
        break;
      case 'ArrowUp':
        ev.preventDefault();
        dd.open = true;
        dd.highlighted = Math.max(0, (dd.highlighted ?? 0) - 1);
        break;
      case 'Enter':
        ev.preventDefault();
        if (dd.open && dd.highlighted >= 0) this.select(key, dd.highlighted);
        else this.toggle(key);
        break;
      case 'Escape':
        dd.open = false;
        break;
    }
  }

  onPhoneInput(ev: Event) {
    const input = ev.target as HTMLInputElement;
    let v = input.value.replace(/\D/g, ''); // only numbers
    input.value = v;
    this.registerForm.get('phone')?.setValue(v, { emitEvent: false });
  }

  @HostListener('document:click')
  onDocClick() {
    (Object.keys(this.dropdowns) as DDKey[]).forEach(k => (this.dropdowns[k].open = false));
  }

  togglePassword() { this.showPassword = !this.showPassword; }
  toggleConfirmPassword() { this.showConfirmPassword = !this.showConfirmPassword; }

  get googleSignInEnabled(): boolean {
    return this.googleAvailable;
  }

  signUpWithGoogle(): void {
    if (!this.googleSignInEnabled) return;
    this.loading = true;
    this.error_message = '';
    this.googleAuthService.signIn().subscribe({
      next: (credential) => {
        this.authService.loginWithGoogle(credential).subscribe({
          next: (data) => {
            this.loading = false;
            if (data.status) {
              this.cookieService.set('token', data.data.token, { path: '/', secure: true, sameSite: 'Strict' });
              this.cookieService.set('student', JSON.stringify(data.data.student), { path: '/', secure: true, sameSite: 'Strict' });
              this.authService.notifyLoginSuccess();
              this.showSuccessAndRedirectHome();
            }
          },
          error: (err) => {
            this.loading = false;
            this.error_message = err.error?.message ? String(err.error.message) : this.translate.instant('something went wrong, please try again');
          }
        });
      },
      error: (err) => {
        this.loading = false;
        console.error('[GoogleAuth] sign-up failed', err);
        const base = this.translate.instant('Google Sign-In was cancelled or unavailable.');
        const detail = err?.message ? `: ${err.message}` : '';
        this.error_message = `${base}${detail}`;
      }
    });
  }

  private static passwordsMatchValidator(group: any) {
    const pass = group.get('password')?.value;
    const confirm = group.get('confirmPassword')?.value;
    return pass && confirm && pass !== confirm ? { passwordsNotMatching: true } : null;
  }

  private showSuccessAndRedirectHome(): void {
    const lang = this.storageService.siteLanguage$.value === 'en' ? 'en' : 'ar';

    // Close the modal first (when register is mounted inside the login/register modal).
    if (this.parent) {
      this.parent.close();
      this.closeModal.emit(true);
    }

    // Navigate the user to their profile page.
    this.router.navigateByUrl(this.profileRoutes[lang]);

    // Show the welcome toast.
    this.toast.success(
      this.translate.instant('regstration message'),
      this.translate.instant('regstration title'),
      6000,
    );
  }
}
