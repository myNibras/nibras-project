import { Component, OnInit } from '@angular/core';
import {
  FormBuilder,
  FormGroup,
  Validators,
  AbstractControl,
  ValidationErrors,
  ValidatorFn,
  ReactiveFormsModule
} from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import {
  ResetPasswordRequest,
  ResetPasswordResponse
} from 'app/shared/models/auth';
import { TranslateModule } from "@ngx-translate/core";
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-new-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule],
  templateUrl: './new-password.component.html',
  styleUrls: ['./new-password.component.scss']
})
export class NewPasswordComponent implements OnInit {

  form: FormGroup;
  showNewPassword = false;
  showConfirmPassword = false;
  loading = false;
  errorMessage: string | null = null;

  email: string | null = null;
  otp: string | null = null;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    this.form = this.fb.group(
      {
        password: ['', [Validators.required, Validators.minLength(6)]],
        confirmPassword: ['', Validators.required]
      },
      { validators: this.passwordMatchValidator }
    );
  }

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      this.email = params['email'] || null;
      this.otp = params['otp'] || null;

      if (!this.email || !this.otp) {
        this.router.navigate(['/en/reset-password']);
      }
    });
  }

  passwordMatchValidator: ValidatorFn = (
    group: AbstractControl
  ): ValidationErrors | null => {
    const pass = group.get('password')?.value;
    const confirm = group.get('confirmPassword')?.value;
    return pass && confirm && pass !== confirm ? { mismatch: true } : null;
  };

  toggleNewPassword() {
    this.showNewPassword = !this.showNewPassword;
  }

  toggleConfirmPassword() {
    this.showConfirmPassword = !this.showConfirmPassword;
  }

  onContinueClick(): void {
    if (this.form.invalid || !this.email || !this.otp) {
      this.form.markAllAsTouched();
      return;
    }

    const body: ResetPasswordRequest = {
      email: this.email,
      otp_code: this.otp,
      password: this.form.value.password,
      password_confirmation: this.form.value.confirmPassword
    };

    this.loading = true;
    this.errorMessage = null;

    this.authService.resetPassword(body).subscribe({
      next: (res: ResetPasswordResponse) => {
        this.loading = false;

        if (res.status) {
          const lang = this.router.url.includes('/ar/') ? 'ar' : 'en';

          const route =
            lang === 'ar'
              ? 'ar/تمت-العملية-بنجاح'
              : 'en/successful-message';

          this.router.navigateByUrl(`/${route}`);
        } else {
          this.errorMessage = res.message;
        }
      },
      error: () => {
        this.loading = false;
        this.errorMessage = 'Something went wrong. Please try again.';
      }
    });
  }
}