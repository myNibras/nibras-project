import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { ReactiveFormsModule, NonNullableFormBuilder, Validators, FormGroup } from '@angular/forms';
import { TranslateModule } from '@ngx-translate/core';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { GoogleAuthService } from 'app/shared/services/google-auth/google-auth.service';
import { CookieService } from 'ngx-cookie-service';
import { strictEmailValidator } from 'app/shared/validators/email.validator';
import { RouterLink, Router } from '@angular/router';
import { StorageService } from 'app/core/storage/storage.service';


@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule, NgIf, RouterLink],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

  @Output() closeModal = new EventEmitter<boolean>();

  loginForm!: FormGroup;
  showLoginPassword = false;
  loading: boolean = false;
  error_message: string = '';
  googleAvailable = false;

  constructor(
    public storageService: StorageService,
    private fb: NonNullableFormBuilder,
    private authService: AuthService,
    private googleAuthService: GoogleAuthService,
    private cookieService: CookieService,
    private router: Router
  ) { }

  registerRoutes: Record<'ar' | 'en', string> = {
    ar: '/ar/انشئ-حساب',
    en: '/en/sign-up'
  };
  resetPasswordRoutes: Record<'ar' | 'en', string> = {
    ar: '/ar/إعادة-تعيين-كلمة-المرور',
    en: '/en/reset-password'
  };

  ngOnInit(): void {

    this.loginForm = this.fb.group({
      email: this.fb.control('', {
        validators: [Validators.required, strictEmailValidator()]
      }),
      password: this.fb.control('', {
        validators: [Validators.required, Validators.minLength(6)]
      }),
    });

    this.loginForm.valueChanges.subscribe(() => {
      this.error_message = '';
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

  toggleLoginPassword(): void {
    this.showLoginPassword = !this.showLoginPassword;
  }

  get googleSignInEnabled(): boolean {
    return this.googleAvailable;
  }

  signInWithGoogle(): void {
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
              const returnUrl = this.router.routerState.snapshot.root.queryParams['returnUrl'];
              this.router.navigateByUrl(returnUrl || '/');
            }
          },
          error: (err) => {
            this.loading = false;
            this.error_message = err.error?.message ? String(err.error.message) : 'Something went wrong. Please try again.';
          }
        });
      },
      error: (err) => {
        this.loading = false;
        console.error('[GoogleAuth] sign-in failed', err);
        const detail = err?.message ? `: ${err.message}` : '';
        this.error_message = `Google Sign-In was cancelled or unavailable${detail}`;
      }
    });
  }

  submit(): void {

    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    const value = this.loginForm.getRawValue();
    this.loading = true;

    this.authService.login(value).subscribe({
      next: (data) => {
        this.loading = false;

        if (data.status) {

          this.cookieService.set(
            'token',
            data.data.token,
            { path: '/', secure: true, sameSite: 'Strict' }
          );

          this.cookieService.set(
            'student',
            JSON.stringify(data.data.student),
            { path: '/', secure: true, sameSite: 'Strict' }
          );

          this.authService.notifyLoginSuccess();

          const returnUrl = this.router.routerState.snapshot.root.queryParams['returnUrl'];

          this.router.navigateByUrl(returnUrl || '/');
        }
      },
      error: (err) => {
        this.loading = false;

        if (err.error?.message === 'messages.invalid_credentials') {
          this.error_message = 'Wrong email or password.';
        } else {
          this.error_message = 'Something went wrong. Please try again.';
        }
      }
    });
  }

}