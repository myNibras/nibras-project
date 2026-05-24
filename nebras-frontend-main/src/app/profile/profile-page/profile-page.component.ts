import { Component, OnInit, OnDestroy } from '@angular/core';
import { UserInfoComponent } from "../components/user-info/user-info.component";
import { RelatedCoursesComponent } from "app/shared/components/related-courses/related-courses.component";
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { Student } from 'app/shared/models/auth';
import { NgIf, NgFor, NgClass } from '@angular/common';
import { Course } from "app/shared/models/courses";
import { CoursesService } from "app/shared/services/courses/courses.service";
import { StorageService } from 'app/core/storage/storage.service';
import { Subject, takeUntil, forkJoin } from 'rxjs';
import { ChangePasswordComponent } from '../components/change-password/change-password.component';
import { InvoicesComponent } from '../components/invoices/invoices.component';
import { UnpaidRemindersComponent } from '../components/unpaid-reminders/unpaid-reminders.component';
import { SuccessfulPopupComponent } from '../components/successful-popup/successful-popup.component';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { Router, ActivatedRoute } from '@angular/router';
import { PaymentService } from 'app/shared/services/payment/payment.service';
import { ProfilePaymentsRefreshService } from 'app/shared/services/profile-payments-refresh/profile-payments-refresh.service';
import { UnpaidInvoiceReminder } from 'app/shared/models/payment.model';
import { NetworkComponent } from 'app/shared/components/payments/network/network.component';
import { RegisteredMaterialsComponent } from "../components/registered-materials/registered-materials.component";
import { MyTestimonialsComponent } from "../components/my-testimonials/my-testimonials.component";
import { MessagesAndNotificationsComponent } from "../components/messages-and-notifications/messages-and-notifications.component";
import { GroupChatsComponent } from '../components/group-chats/group-chats.component';

@Component({
  selector: 'app-profile-page',
  standalone: true,
  imports: [
    UserInfoComponent,
    RelatedCoursesComponent,
    NgIf,
    NgFor,
    NgClass,
    ChangePasswordComponent,
    SuccessfulPopupComponent,
    InvoicesComponent,
    UnpaidRemindersComponent,
    TranslateModule,
    NetworkComponent,
    RegisteredMaterialsComponent,
    MyTestimonialsComponent,
    MessagesAndNotificationsComponent,
    GroupChatsComponent
  ],
  templateUrl: './profile-page.component.html',
  styleUrls: ['./profile-page.component.scss']
})
export class ProfilePageComponent implements OnInit, OnDestroy {

  student: Student | null = null;
  loading = true;
  relatedCourses: Course[] = [];
  destroy$ = new Subject<void>();

  selectedTab = 'profile';
  menuOpen = false;

  showChangePassword = false;
  showSuccessPopup = false;
  /** Installment payment: show gateway when set. */
  installmentSessionId: string | null = null;
  showInstallmentCheckout = false;
  installmentPaymentLoading = false;
  paymentError: string | null = null;
  /** After gateway redirect: show success popup. */
  showPaymentSuccessPopup = false;
  /** After gateway redirect: show failed message. */
  paymentFailedMessage: string | null = null;

  /** ===== Tabs config (used for sidebar & dropdown) ===== */
  tabs = [
    { key: 'profile', label: 'account information' },
    { key: 'registered-materials', label: 'registered materials' },
    { key: 'messages-and-notifications', label: 'messages and notifications' },
    { key: 'invoices', label: 'payments & invoices' },
    { key: 'rating', label: 'My Testimonials' },
    // { key: 'chats', label: 'Group Chats' }
  ];

  homeRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar',
    en: 'en'
  };

  constructor(
    private authService: AuthService,
    private coursesService: CoursesService,
    public storageService: StorageService,
    private translate: TranslateService,
    private router: Router,
    private route: ActivatedRoute,
    private paymentService: PaymentService,
    private paymentsRefresh: ProfilePaymentsRefreshService
  ) { }

  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadProfileAndCourses();
      });

    this.route.queryParams.pipe(takeUntil(this.destroy$)).subscribe((params) => {
      const payment = params['payment'];
      const tab = params['tab'];
      const message = params['message'] || null;

      // Switch to a specific tab if requested via the URL.
      if (tab && this.tabs.some(t => t.key === tab)) {
        this.selectedTab = tab;
      }

      if (payment === 'success') {
        // Default to the "My Enrolled Courses" tab so the new course is visible.
        if (!tab) this.selectedTab = 'registered-materials';
        this.showPaymentSuccessPopup = true;
        this.paymentsRefresh.triggerRefresh();
        this.router.navigate([], { relativeTo: this.route, queryParams: {}, queryParamsHandling: '' });
      } else if (payment === 'failed') {
        this.paymentFailedMessage = message || this.translate.instant('Payment failed. Please try again.');
        this.router.navigate([], { relativeTo: this.route, queryParams: {}, queryParamsHandling: '' });
      }
    });
  }

  /** ===== Tab Selection ===== */
  selectTab(tab: string) {
    this.selectedTab = tab;
    this.menuOpen = false; // close dropdown on mobile/tablet
  }

  /** ===== Active Tab Label for Dropdown ===== */
  get activeTabLabel(): string {
    return this.tabs.find(t => t.key === this.selectedTab)?.label || '';
  }

  startInstallmentPayment(installmentIds: number[]): void {
    this.paymentError = null;
    this.installmentPaymentLoading = true;
    this.paymentService.createInstallmentSession(installmentIds).subscribe({
      next: (data) => {
        this.installmentSessionId = data.session_id;
        this.showInstallmentCheckout = true;
        this.installmentPaymentLoading = false;
      },
      error: (err) => {
        this.installmentPaymentLoading = false;
        this.paymentError = err?.error?.message || err?.message || this.translate.instant('Failed to start payment. Please try again.');
      }
    });
  }

  onPayNowReminder(reminder: UnpaidInvoiceReminder): void {
    this.selectTab('invoices');
    this.startInstallmentPayment([reminder.id]);
  }

  onPayNowInstallment(installmentId: number): void {
    this.startInstallmentPayment([installmentId]);
  }

  closePaymentFailedMessage(): void {
    this.paymentFailedMessage = null;
  }

  closePaymentSuccessPopup(): void {
    this.showPaymentSuccessPopup = false;
  }

  private loadProfileAndCourses() {
    this.loading = true;

    forkJoin({
      profile: this.authService.getProfile(),
      courses: this.coursesService.getRelatedCourses()
    }).subscribe({
      next: ({ profile, courses }) => {
        this.student = profile.data;
        this.relatedCourses = courses;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.redirectToHome();
      }
    });
  }

  private redirectToHome() {
    const lang = this.translate.currentLang === 'ar' ? 'ar' : 'en';
    const route = this.homeRoutes[lang];
    this.router.navigate([`/${route}`]);
  }

  /** ===== Password ===== */
  openChangePassword() {
    this.showChangePassword = true;
  }

  closeChangePassword() {
    this.showChangePassword = false;
  }

  onPasswordChanged() {
    this.showChangePassword = false;
    this.showSuccessPopup = true;
  }

  /** ===== Profile Update ===== */
  onProfileUpdated(updated: boolean) {
    if (updated) {
      this.loadProfileAndCourses();
    }
  }

  closeSuccessPopup() {
    this.showSuccessPopup = false;
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  /** ===== Profile Image ===== */
  get profileImage(): string {
    if (this.student?.profile_picture) {
      return this.student.profile_picture;
    }

    const name = this.student?.name || 'User';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=F2F4F7&color=667085&size=120`;
  }
}
