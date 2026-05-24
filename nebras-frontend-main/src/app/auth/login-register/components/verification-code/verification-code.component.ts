import {
  Component,
  QueryList,
  ViewChildren,
  ElementRef,
  OnInit,
  OnDestroy
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { TranslateModule } from "@ngx-translate/core";
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-verification-code',
  standalone: true,
  imports: [CommonModule, FormsModule, TranslateModule],
  templateUrl: './verification-code.component.html',
  styleUrls: ['./verification-code.component.scss']
})
export class VerificationCodeComponent implements OnInit, OnDestroy {

  @ViewChildren('otpInput') otpInputs!: QueryList<ElementRef<HTMLInputElement>>;

  otpLength = 6;
  code: string[] = Array(this.otpLength).fill('');
  countdown = 60;
  loading = false;
  errorMessage: string | null = null;

  private countdownInterval: any;
  private routeSub!: Subscription;

  email: string | null = null;

  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) { }

  ngOnInit(): void {
    this.routeSub = this.route.queryParams.subscribe(params => {
      this.email = params['email'] || null;

      if (!this.email) {
        this.router.navigate(['/en/reset-password']); // fallback
      }
    });

    this.startCountdown();
  }

  ngOnDestroy(): void {
    clearInterval(this.countdownInterval);
    this.routeSub?.unsubscribe();
  }

  trackByIndex(index: number): number {
    return index;
  }

  onInput(event: Event, index: number): void {
    const el = event.target as HTMLInputElement;
    this.errorMessage = null;

    let v = el.value.replace(/\D/g, '');

    if (!v) {
      this.code[index] = '';
      el.value = '';
      return;
    }

    if (v.length === 1) {
      this.code[index] = v;
      el.value = v;

      if (index < this.otpLength - 1) {
        this.otpInputs.toArray()[index + 1].nativeElement.focus();
      }

      return;
    }

    this.distributeFrom(index, v);
  }

  onKeyDown(e: KeyboardEvent, index: number): void {
    const inputs = this.otpInputs.toArray();

    if (e.key === 'Backspace') {
      e.preventDefault();

      const el = inputs[index].nativeElement;

      if (el.value) {
        el.value = '';
        this.code[index] = '';
      }

      if (index > 0) {
        const prev = inputs[index - 1].nativeElement;
        prev.value = '';
        this.code[index - 1] = '';
        prev.focus();
      }

      return;
    }

    if (e.key === 'Enter') {
      this.onContinueClick();
      return;
    }

    const isModifier = e.ctrlKey || e.metaKey || e.altKey;
    if (!isModifier && !/^\d$/.test(e.key)) {
      e.preventDefault();
    }
  }

  onPaste(e: ClipboardEvent, index: number): void {
    e.preventDefault();
    const text = (e.clipboardData?.getData('text') || '').replace(/\D/g, '');
    if (!text) return;
    this.distributeFrom(index, text);
  }

  private distributeFrom(startIndex: number, digits: string): void {
    const inputs = this.otpInputs.toArray();

    for (let i = 0; i < digits.length && startIndex + i < this.otpLength; i++) {
      const ch = digits[i];
      this.code[startIndex + i] = ch;
      inputs[startIndex + i].nativeElement.value = ch;
    }

    const end = Math.min(startIndex + digits.length, this.otpLength) - 1;
    inputs[end].nativeElement.focus();

    if (this.code.join('').length === this.otpLength && this.code.every(c => c !== '')) {
      this.onContinueClick();
    }
  }

  private startCountdown(): void {
    clearInterval(this.countdownInterval);
    this.countdown = 60;

    this.countdownInterval = setInterval(() => {
      if (this.countdown > 0) {
        this.countdown--;
      } else {
        clearInterval(this.countdownInterval);
      }
    }, 1000);
  }

  resendOtp(): void {
    if (this.countdown > 0 || !this.email) return;

    this.loading = true;

    this.authService.requestPasswordReset({ email: this.email }).subscribe({
      next: (res) => {
        this.loading = false;

        if (res.status) {
          this.code = Array(this.otpLength).fill('');
          this.startCountdown();
        } else {
          this.errorMessage = 'otp.resendFailed';
        }
      },
      error: () => {
        this.loading = false;
        this.errorMessage = 'otp.genericError';
      }
    });
  }

  onContinueClick(): void {
    const otpCode = this.code.join('');

    if (!this.email || otpCode.length < this.otpLength) {
      this.errorMessage = 'otp.emptyCode';
      return;
    }

    this.loading = true;
    this.errorMessage = null;

    this.authService.verifyOtp({
      email: this.email,
      otp_code: otpCode
    }).subscribe({
      next: (res) => {
        this.loading = false;

        if (res.status) {
          const lang = this.router.url.includes('/ar/') ? 'ar' : 'en';

          const route =
            lang === 'ar'
              ? 'ar/كلمة-المرور-الجديدة'
              : 'en/new-password';

          this.router.navigateByUrl(`/${route}?email=${this.email}&otp=${otpCode}`);
        } else {
          this.errorMessage = 'otp.invalid';
        }
      },
      error: () => {
        this.loading = false;
        this.errorMessage = 'otp.invalid';
      }
    });
  }
}