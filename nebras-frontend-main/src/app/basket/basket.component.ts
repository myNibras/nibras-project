import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CartItemComponent } from "./components/cart-item/cart-item.component";
import { PaymentService } from 'app/shared/services/payment/payment.service';
import { CartData, CartItem, CouponDetails } from 'app/shared/models/payment.model';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { StorageService } from 'app/core/storage/storage.service';
import { NetworkComponent } from "app/shared/components/payments/network/network.component";
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { TranslateModule } from '@ngx-translate/core';
import { RelatedCoursesComponent } from 'app/shared/components/related-courses/related-courses.component';
import { CoursesService } from 'app/shared/services/courses/courses.service';
import { Course } from 'app/shared/models/courses';


@Component({
  selector: 'app-basket',
  standalone: true,
  imports: [CommonModule, FormsModule, CartItemComponent, NetworkComponent, TranslateModule, RelatedCoursesComponent],
  templateUrl: './basket.component.html',
  styleUrl: './basket.component.scss'
})
export class BasketComponent implements OnInit, OnDestroy {
  coupon = '';
  couponLoading = false;
  couponError = '';
  couponDetails?: CouponDetails;
  couponApplied = false;


  loading = true;
  cart?: CartData;
  items: CartItem[] = [];
  relatedCourses: Course[] = [];

  private destroy$ = new Subject<void>();
  selected_payment = '';
  sessionId = '';

  constructor(
    private paymentService: PaymentService,
    private authService: AuthService,
    private storageService: StorageService,
    private router: Router,
    private translate: TranslateService,
    private coursesService: CoursesService,
  ) { }

  homeRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar',
    en: 'en'
  };

  loginRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تسجيل-الدخول',
    en: 'en/login'
  };
  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadCart();
        this.loadRelatedCourses();
      });
  }


  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  loadCart(): void {
    this.loading = true;

    this.paymentService.getCart().subscribe({
      next: (res) => {
        this.cart = res.data;
        this.items = res.data?.items ?? [];
        this.loading = false;

        // ✅ Detect applied coupon from backend
        if (this.cart?.coupon_code && this.cart.coupon_code !== 'null') {
          this.couponApplied = true;
          this.coupon = this.cart.coupon_code;

          this.couponDetails = {
            coupon_code: this.cart.coupon_code,
            discount_percentage: this.cart.discount_percentage,
            discount_amount: Number(this.cart.discount_amount),
            original_amount: this.cart.original_amount,
            final_amount: Number(this.cart.final_amount),
          };
        } else {
          // ❌ No coupon
          this.couponApplied = false;
          this.couponDetails = undefined;
          this.coupon = '';
        }
      },
      error: (err) => {
        this.loading = false;
        if (err?.status === 401) {
          this.authService.logout();

          const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
          const route = this.loginRoutes[lang];

          this.router.navigateByUrl(`/${route}?returnUrl=${this.router.url}`);

          return;
        }
        this.redirectToHome();
      }
    });
  }

  private redirectToHome() {
    const lang = this.translate.currentLang === 'ar' ? 'ar' : 'en';
    const route = this.homeRoutes[lang];
    this.router.navigate([`/${route}`]);
  }


  checkout() {
    if (!this.authService.isLoggedIn()) {

      const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
      const route = this.loginRoutes[lang];

      this.router.navigateByUrl(`/${route}?returnUrl=${this.router.url}`);
      return;
    }

    this.redirectToPayment();
  }

  redirectToPayment() {
    const payload = { payment_method: "network" };
    this.loading = true;

    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.paymentService.createSession(payload).subscribe({
          next: (value: any) => {
            this.selected_payment = "network";
            this.sessionId = value.data.session_id;
            this.loading = false;
          },
          error: (error) => {
            if (error?.error?.error != null) location.reload();
            this.loading = false;
          }
        });
      });
  }

  get total(): number {
    const t = this.cart?.total_amount;
    return t ? Number(t) : 0;
  }

  remove(cartItemId: number) {
    const item = this.items.find(x => x.id === cartItemId);

    if (!item) return;

    this.loading = true;

    this.paymentService.removeFromCart(item.course_id).subscribe({
      next: () => {
        this.loadCart();
      },
      error: (err) => {
        this.loading = false;
        console.error('Remove from cart failed:', err);
      }
    });
  }

  get hasCashPaymentItems(): boolean {
    return this.items.some(item =>
      item.payment_type !== 'monthly' && item.payment_type
    );
  }

  applyCoupon() {
    if (!this.coupon || this.couponLoading || this.couponApplied) return;

    // Check if there are any cash payment items
    if (!this.hasCashPaymentItems) {
      this.couponError = 'Coupons can only be applied to cash payment items.';
      return;
    }

    this.couponLoading = true;
    this.couponError = '';

    this.paymentService.addCoupon(this.coupon).subscribe({
      next: () => {
        this.couponApplied = true;
        this.loadCart(); // backend decides
        this.couponLoading = false;
      },
      error: (err) => {
        this.couponLoading = false;

        const message =
          err?.error?.message || err?.message || '';

        this.couponError = message;

      }
    });
  }


  removeCoupon() {
    this.couponLoading = true;

    this.paymentService.removeCoupon().subscribe({
      next: () => {
        this.coupon = '';
        this.couponApplied = false;
        this.couponDetails = undefined;

        // refresh cart without coupon
        this.loadCart();

        this.couponLoading = false;
      },
      error: (err) => {
        this.couponLoading = false;
        console.error('Remove coupon error:', err);
      }
    });
  }

  formatPrice(price: string | number | undefined): string {
    if (!price) return '0';
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    if (isNaN(numPrice)) return String(price);

    // Check if it's a whole number
    if (numPrice % 1 === 0) {
      return numPrice.toString();
    }

    // Otherwise return with decimals
    return numPrice.toFixed(2);
  }

  loadRelatedCourses(): void {
    this.coursesService.getRelatedCourses().subscribe({
      next: (courses) => {
        this.relatedCourses = courses || [];
      },
      error: (err) => {
        console.error('Error loading related courses:', err);
        this.relatedCourses = [];
      }
    });
  }

}
