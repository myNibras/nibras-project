import {
  AfterViewInit, Component, ElementRef, EventEmitter, HostListener,
  Input, OnChanges, OnDestroy, Output, SimpleChanges, ViewChild
} from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { LoginComponent } from './components/login/login.component';
import { RegisterComponent } from './components/register/register.component';
import { TranslateModule } from '@ngx-translate/core';
import { ResetPasswordComponent } from './components/reset-password/reset-password.component';
import { VerificationCodeComponent } from './components/verification-code/verification-code.component';
import { NewPasswordComponent } from './components/new-password/new-password.component';
import { SuccessfulPopupComponent } from './components/successful-popup/successful-popup.component';

type Tab = 'login' | 'register';

@Component({
  selector: 'app-login-register',
  standalone: true,
  imports: [
    CommonModule,
    LoginComponent,
    RegisterComponent,
    NgIf,
    TranslateModule,
    ResetPasswordComponent,
    VerificationCodeComponent,
    NewPasswordComponent,
    SuccessfulPopupComponent,
  ],
  templateUrl: './login-register.component.html',
  styleUrls: ['./login-register.component.scss'],
})
export class LoginRegisterComponent implements AfterViewInit, OnChanges, OnDestroy {
  @Input() open = false; // control if modal is open
  @Input() isRTL = false; // right-to-left layout flag
  @Output() closed = new EventEmitter<void>(); // emit when modal is closed

  // manage which view to show (login, register, forgot-password flow)
  view: 'login' | 'register' | 'forgot-password' | 'verification-code' | 'new-password' | 'successful-popup' = 'login';

  popupTitle: string = '';
  popupMessage: string = '';
  popupButtonLabel: string = 'Ok';
  resetEmail: string | null = null;
  verifiedOtp: string | null = null;


  switchTo(
    viewName: typeof this.view,
    options?: { title?: string; message?: string; buttonLabel?: string }
  ) {
    this.view = viewName;
    if (options?.title) this.popupTitle = options.title;
    if (options?.message) this.popupMessage = options.message;
    if (options?.buttonLabel) this.popupButtonLabel = options.buttonLabel;
  }


  // active tab for login/register (used for indicator)
  activeTab: Tab = 'login';
  verificationOpen = false;

  // indicator width and left position
  indicatorW = 0; // px
  indicatorL = 0; // px

  // references to DOM elements for indicator calculation
  @ViewChild('tabsRow', { static: false }) tabsRow!: ElementRef<HTMLDivElement>;
  @ViewChild('loginTxt', { static: false }) loginTxt!: ElementRef<HTMLSpanElement>;
  @ViewChild('registerTxt', { static: false }) registerTxt!: ElementRef<HTMLSpanElement>;

  private ro?: ResizeObserver;

  // switch between login/register tabs
  switch(tab: Tab) {
    this.activeTab = tab;
    this.updateIndicator();
  }
  // close modal
  close() {
    this.view = 'login';   // ✅ reset to default
    this.closed.emit();
  }

  // also in closeModal
  closeModal(event: boolean) {
    console.log(event);
    this.view = 'login';   // ✅ reset to default
    this.open = false;
  }


  // close modal when ESC key is pressed
  @HostListener('document:keydown.escape')
  onEsc() { if (this.open) this.close(); }

  // close modal when clicking backdrop
  onBackdropClick(e: MouseEvent) {
    if ((e.target as HTMLElement).id === 'lr-backdrop') this.close();
  }

  // run after component view is initialized
  ngAfterViewInit() {
    this.queueIndicatorRecalc();
  }

  // handle changes on inputs
  ngOnChanges(changes: SimpleChanges) {
    if (changes['open']?.currentValue && !changes['open'].previousValue) {
      this.queueIndicatorRecalc();
    }
  }


  // recalc indicator on window resize
  @HostListener('window:resize')
  onResize() { this.updateIndicator(); }

  // schedule recalculation of indicator position/size
  private queueIndicatorRecalc() {
    setTimeout(() => this.updateIndicator(), 0);
    requestAnimationFrame(() => this.updateIndicator());
    requestAnimationFrame(() => requestAnimationFrame(() => this.updateIndicator()));

    if ('ResizeObserver' in window && this.tabsRow?.nativeElement && !this.ro) {
      this.ro = new ResizeObserver(() => this.updateIndicator());
      this.ro.observe(this.tabsRow.nativeElement);
    }
  }

  // disconnect ResizeObserver on destroy
  ngOnDestroy() { this.ro?.disconnect(); }

  // update indicator width and position under active tab
  private updateIndicator() {
    if (!this.tabsRow || !this.loginTxt || !this.registerTxt) return;

    const rowEl = this.tabsRow.nativeElement;
    const txtEl = (this.activeTab === 'login' ? this.loginTxt : this.registerTxt).nativeElement;

    const rowRect = rowEl.getBoundingClientRect();
    const txtRect = txtEl.getBoundingClientRect();

    const padding = 24;
    const textW = txtEl.offsetWidth || txtRect.width || 0;
    const width = Math.min(Math.max(textW + padding, 64), 160);
    const left = (txtRect.left - rowRect.left) + (textW - width) / 2;

    this.indicatorW = Math.round(width);
    this.indicatorL = Math.round(left);
  }
}
