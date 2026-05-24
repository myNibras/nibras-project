import { NgIf, NgClass, NgStyle, NgFor } from '@angular/common';
import { Component, Input, OnDestroy, SimpleChanges } from '@angular/core';
import { TranslateService, TranslateModule } from '@ngx-translate/core';
import { NetworkComponent } from "app/shared/components/payments/network/network.component";
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { PaymentService } from 'app/shared/services/payment/payment.service';
import { Subject } from 'rxjs';
import { Router } from '@angular/router';
import { SharedPopupComponent } from 'app/shared/components/shared-popup/shared-popup.component';

@Component({
  selector: 'app-hero',
  standalone: true,
  imports: [TranslateModule, NetworkComponent, NgIf, NgClass, SharedPopupComponent, NgStyle, NgFor],
  templateUrl: './hero.component.html',
  styleUrl: './hero.component.scss'
})
export class HeroComponent implements OnDestroy {
  @Input() course_id?: number;
  @Input() title!: string | undefined;
  @Input() image!: string | undefined;
  @Input() short_description!: string | undefined;
  @Input() price!: string | undefined;
  @Input() discount_price!: string | undefined;
  @Input() class!: string | undefined;
  @Input() course_type?: string;
  @Input() available_seats?: number | null;
  @Input() final_available_seats?: number | null;
  @Input() payment_type?: string;
  @Input() semester_months?: number;
  @Input() monthly_amount?: number | string;
  @Input() purchase?: boolean;

  destroy$ = new Subject<void>();
  selected_payment: string = "";
  sessionId: string = "";
  loading: boolean = false;
  rating = 4.5;
  selectedPlan: 'installments' | 'oneTime' = 'oneTime';

  // ✅ popup state
  showPopup = false;
  popupTitle = '';
  popupMessage = '';
  popupShowLoginButton = false;
  popupShowCartButton = false;

  constructor(
    private translate: TranslateService,
    private authService: AuthService,
    private paymentService: PaymentService,
    private router: Router
  ) { }

  basketRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/حقيبة-الشراء',
    en: 'en/basket'
  };
  loginRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تسجيل-الدخول',
    en: 'en/login'
  };

  ngOnInit(): void {
    this.setInitialPaymentPlan();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['payment_type']) {
      this.setInitialPaymentPlan();
    }
  }

  private setInitialPaymentPlan(): void {
    if (this.payment_type === 'monthly') {
      this.selectedPlan = 'installments';
    } else if (this.payment_type === 'one-off') {
      this.selectedPlan = 'oneTime';
    } else if (this.payment_type === 'both') {
      // Default to oneTime for 'both', but user can switch
      this.selectedPlan = 'oneTime';
    }
  }

  get isArabic(): boolean {
    return (this.translate.currentLang || '').startsWith('ar');
  }

  getLocalizedCourseType(type?: string): string {
    const normalized = (type || '').toLowerCase();
    if (normalized === 'recorded') {
      return this.isArabic ? 'مسجل' : 'Recorded';
    }
    if (normalized === 'online') {
      return this.isArabic ? 'أونلاين' : 'Online';
    }
    return type || '';
  }

  getPrice(): number {
    return this.price ? parseFloat(this.price) : 0;
  }

  getDiscountPrice(): number {
    return this.discount_price ? parseFloat(this.discount_price) : 0;
  }

  getMonthlyAmount(): number | undefined {
    if (this.monthly_amount === undefined || this.monthly_amount === null) {
      return undefined;
    }
    if (typeof this.monthly_amount === 'string') {
      const parsed = parseFloat(this.monthly_amount);
      return isNaN(parsed) ? undefined : parsed;
    }
    return this.monthly_amount;
  }

  checkout() {
    if (this.course_id == null) {
      console.error('course_id is missing');
      return;
    }

    if (this.purchase) {
      return;
    }

    if (!this.authService.isLoggedIn()) {
      const lang: 'ar' | 'en' = this.isArabic ? 'ar' : 'en';
      const route = this.loginRoutes[lang];

      this.router.navigateByUrl(`/${route}?returnUrl=${this.router.url}`);
      return;
    }

    this.addToCartRequest();
  }

  private addToCartRequest() {
    this.loading = true;
    const paymentType = this.selectedPlan === 'oneTime' ? 'one-off' : 'monthly';

    this.paymentService.addToCart(this.course_id!, paymentType).subscribe({
      next: (res: any) => {
        this.loading = false;

        if (res?.status === false && res?.message === 'Course already in cart') {
          this.openPopupKey(
            'alreadyInCartTitle',
            'alreadyInCartMessage',
            false,
            undefined,
            true
          );
          return;
        }

        const lang: 'ar' | 'en' = this.isArabic ? 'ar' : 'en';
        this.router.navigate([this.basketRoutes[lang]]);
      },

      error: (err) => {
        this.loading = false;
        const backend = err?.error;

        if (backend?.message === 'Course already in cart') {
          this.openPopupKey(
            'alreadyInCartTitle',
            'alreadyInCartMessage',
            false,
            undefined,
            true
          );
          return;
        }

        if (err?.status === 401) {
          this.openPopupKey(
            'loginRequiredTitle',
            'loginRequiredMessage',
            true
          );
          return;
        }

        this.openPopupKey(
          'somethingWentWrongTitle',
          'tryAgainMessage',
          false,
          backend?.message
        );

        console.error('Add to cart failed:', err);
      }
    });
  }

  // ✅ popup helpers
  private openPopupKey(
    titleKey: string,
    messageKey: string,
    showLoginButton: boolean,
    fallbackMessage?: string,
    showCartButton = false
  ) {
    this.popupTitle = this.translate.instant(titleKey);
    this.popupMessage = fallbackMessage || this.translate.instant(messageKey);
    this.popupShowLoginButton = showLoginButton;
    this.popupShowCartButton = showCartButton;
    this.showPopup = true;
  }


  closePopup() {
    if (this.popupShowCartButton) {
      this.goToCartFromPopup();
    } else {
      this.showPopup = false;
    }
  }

  goToCartFromPopup() {
    this.showPopup = false;
    const lang: 'ar' | 'en' = this.isArabic ? 'ar' : 'en';
    this.router.navigate([this.basketRoutes[lang]]);
  }

  onPopupLogin() {
    this.showPopup = false;

    const lang: 'ar' | 'en' = this.isArabic ? 'ar' : 'en';
    const route = this.loginRoutes[lang];

    this.router.navigateByUrl(`/${route}?returnUrl=${this.router.url}`);
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
