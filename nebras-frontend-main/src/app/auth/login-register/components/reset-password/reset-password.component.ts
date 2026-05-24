import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { SendOtpResponse } from 'app/shared/models/auth';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { strictEmailValidator } from 'app/shared/validators/email.validator';
import { StorageService } from 'app/core/storage/storage.service';
import { Router, RouterLink } from '@angular/router';

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule, RouterLink],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent {

  form: FormGroup;
  loading = false;
  errorMessage: string | null = null;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private translate: TranslateService,
    public storageService: StorageService,
    private router: Router
  ) {
    this.form = this.fb.group({
      email: ['', [Validators.required, strictEmailValidator()]]
    });
  }

  registerRoutes: Record<'ar' | 'en', string> = {
    ar: '/ar/انشئ-حساب',
    en: '/en/sign-up'
  };

  onSendResetClick(): void {
    if (this.form.invalid) return;

    this.loading = true;
    this.errorMessage = null;

    const email = this.form.value.email;

    this.authService.requestPasswordReset({ email }).subscribe({
      next: (res: SendOtpResponse) => {
        this.loading = false;

        if (res.status) {
          const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';

          const route =
            lang === 'ar'
              ? 'ar/التحقق-من-الرمز'
              : 'en/verification-code';

          this.router.navigateByUrl(`/${route}?email=${email}`);
        } else {
          this.errorMessage =
            res.message || this.translate.instant('error.generic');
        }
      },
      error: (err) => {
        this.loading = false;

        if (err?.error?.message) {
          switch (err.error.message) {
            case 'The selected email is invalid.':
              this.errorMessage =
                this.translate.instant('error.invalidEmail');
              break;
            default:
              this.errorMessage =
                this.translate.instant('error.generic');
          }
        } else {
          this.errorMessage =
            this.translate.instant('error.generic');
        }

        console.error('Reset password error:', err);
      }
    });
  }
}