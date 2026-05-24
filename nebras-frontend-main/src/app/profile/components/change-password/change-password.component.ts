import { Component, EventEmitter, Output } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { ChangePasswordRequest } from 'app/shared/models/auth';


@Component({
  selector: 'app-change-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule],
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.scss']
})
export class ChangePasswordComponent {
  @Output() closeModal = new EventEmitter<void>();
  @Output() passwordChanged = new EventEmitter<void>();

  form: FormGroup;
  showCurrentPassword = false;
  showNewPassword = false;
  showConfirmPassword = false;
  loading = false;

  // 🔥 store error per field
  fieldErrors: { [key: string]: string } = {};
  generalError: string | null = null;
  errorMessage: string | null = null;

  constructor(private fb: FormBuilder, private authService: AuthService) {
    this.form = this.fb.group({
      currentPassword: ['', Validators.required],
      newPassword: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', Validators.required],
    }, { validators: this.passwordsMatch });
  }

  toggleCurrentPassword() { this.showCurrentPassword = !this.showCurrentPassword; }
  toggleNewPassword() { this.showNewPassword = !this.showNewPassword; }
  toggleConfirmPassword() { this.showConfirmPassword = !this.showConfirmPassword; }

  private passwordsMatch(group: FormGroup) {
    const newPwd = group.get('newPassword')?.value;
    const confirmPwd = group.get('confirmPassword')?.value;
    return newPwd && confirmPwd && newPwd !== confirmPwd ? { mismatch: true } : null;
  }

  onBackdropClick(event: MouseEvent) {
    if ((event.target as HTMLElement).id === 'auth-backdrop') {
      this.closeModal.emit();
    }
  }

  onContinueClick() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.fieldErrors = {};
    this.generalError = null;

    const body: ChangePasswordRequest = {
      current_password: this.form.value.currentPassword,
      new_password: this.form.value.newPassword,
      new_password_confirmation: this.form.value.confirmPassword,
    };

    this.authService.changePassword(body).subscribe({
      next: (res) => {
        this.loading = false;
        if (res.status) {
          console.log('Password changed successfully:', res.message);
          this.passwordChanged.emit();
        } else {
          this.generalError = res.message || 'Something went wrong';
        }
      },
      error: (err) => {
        this.loading = false;

        let backendMessage: string | null = null;

        if (err?.error?.errors) {
          const firstField = Object.keys(err.error.errors)[0];
          backendMessage = err.error.errors[firstField][0]; // e.g. "Current password is incorrect."
        } else if (err?.error?.message) {
          backendMessage = err.error.message;
        }

        // Map backend string → translation key
        if (backendMessage === 'Current password is incorrect.') {
          this.errorMessage = 'currentPassword incorrect';
        } else {
          this.errorMessage = backendMessage || 'something went wrong';
        }

        console.error('Change password failed:', err);
      }

    });
  }
}
