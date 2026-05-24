import { Component, HostListener, ElementRef, ViewChild, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { Location, NgIf, NgClass, isPlatformBrowser } from '@angular/common';
import { routeTranslations } from 'app/route-translations';
import { StorageService } from 'app/core/storage/storage.service';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { RouterLink, Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { PaymentService } from 'app/shared/services/payment/payment.service';
import { SharedPopupComponent } from 'app/shared/components/shared-popup/shared-popup.component';
import { ChatNotificationsBellComponent } from 'app/shared/components/chat-notifications-bell/chat-notifications-bell.component';


@Component({
  selector: 'app-header',
  imports: [TranslateModule, NgIf, NgClass, RouterLink, SharedPopupComponent, ChatNotificationsBellComponent],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss'
})
export class HeaderComponent implements OnInit {
  private readonly platformId = inject(PLATFORM_ID);

  constructor(
    public storageService: StorageService,
    public translateService: TranslateService,
    public location: Location,
    public authService: AuthService,
    private router: Router,
    public paymentService: PaymentService,
  ) {
    this.router.events
      .pipe(filter(e => e instanceof NavigationEnd))
      .subscribe((e: any) => {
        this.closeMenu();
        if (this.authService.isLoggedIn()) {
          this.loadCart();
        } else {
          this.paymentService.setCartCount(0);
        }

        const url = decodeURIComponent(e.urlAfterRedirects.split('?')[0]);

        if (url.includes('subjects') || url.includes('المواد')) {
          this.setActive('subjects');
        }
        else if (url.includes('about-us') || url.includes('من-نحن')) {
          this.setActive('about');
        }
        else if (url.includes('contact-us') || url.includes('تواصل-معنا')) {
          this.setActive('contact');
        }
        else if (url.includes('our-careers') || url.includes('وظائفنا')) {
          this.setActive('careers');
        }
        else if (url === '/en' || url === '/en/' || url === '/ar' || url === '/ar/') {
          this.setActive('home');
        }
        else {
          this.activeSection = null;
        }
      });
  }

  /** False until we know auth state: no token (immediate) or after getProfile() completes (token present). */
  authResolved = false;
  // hasCartItems is now derived from paymentService.cartCount() — see template.

  // allow null so we can hide underline briefly
  activeSection: 'home' | 'about' | 'contact' | 'subjects' | 'careers' | null = 'home';

  private underlineTimer: any = null;

  dropdownOpen = false;

  // ✅ Reference the whole container (button + menu)
  @ViewChild('desktopDropdownContainer') desktopDropdownContainer!: ElementRef;
  @ViewChild('mobileDropdownContainer') mobileDropdownContainer!: ElementRef;


  toggleDropdown(): void {
    this.dropdownOpen = !this.dropdownOpen;
  }

  closeDropdown(): void {
    this.dropdownOpen = false;
  }


  logout(): void {
    this.authService.logout();
    this.dropdownOpen = false;
    this.closeMenu();

    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
    this.router.navigate([`/${this.homeRoutes[lang]}`]);
  }

  private setActive(section: 'home' | 'about' | 'contact' | 'subjects' | 'careers') {
    if (this.activeSection === section) return;

    if (this.underlineTimer) {
      clearTimeout(this.underlineTimer);
      this.underlineTimer = null;
    }

    this.activeSection = null;

    this.underlineTimer = setTimeout(() => {
      this.activeSection = section;
      this.underlineTimer = null;
    }, 180);
  }

  homeRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar',
    en: 'en'
  };
  subjectsRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/المواد/الكل',
    en: 'en/subjects/all'
  };
  profileRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/الملف-الشخصي',
    en: 'en/profile'
  };
  aboutUsRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/من-نحن',
    en: 'en/about-us'
  };
  basketRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/حقيبة-الشراء',
    en: 'en/basket'
  };
  careersRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/وظائفنا',
    en: 'en/our-careers'
  };

  contactUsRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تواصل-معنا',
    en: 'en/contact-us'
  };
  loginRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تسجيل-الدخول',
    en: 'en/login'
  };
  registerRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/انشئ-حساب',
    en: 'en/sign-up'
  };


  langOpen = false;
  scrolled = false;

  isMenuOpen = false;

  get isRTL(): boolean {
    return this.storageService.siteLanguage$.value === 'ar';
  }

  toggleLanguage(lang?: 'en' | 'ar'): void {
    const current = this.storageService.siteLanguage$.value;
    const next = lang ?? (current === 'ar' ? 'en' : 'ar');
    this.changeUrlLanguage(next);
    this.storageService.siteLanguage$.next(next);
    this.langOpen = false;
    document.documentElement.setAttribute('lang', next);
    this.closeMenu();
  }

  toggleLang(): void { this.langOpen = !this.langOpen; }

  changeUrlLanguage(language: 'en' | 'ar'): void {
    const url = decodeURIComponent(this.location.path());

    const [pathPart, queryPart] = url.split('?');
    const segments = pathPart.split('/').filter(Boolean);

    if (segments.length > 0) {
      segments[0] = language;

      for (let i = 1; i < segments.length; i++) {
        const cleanSegment = decodeURIComponent(segments[i]);
        segments[i] = encodeURIComponent(routeTranslations[language][cleanSegment] || cleanSegment);
      }
    }

    const newUrl = '/' + segments.join('/') + (queryPart ? '?' + queryPart : '');
    this.location.replaceState(newUrl);
  }

  setLanguage(lang: 'en' | 'ar'): void {
    this.changeUrlLanguage(lang);
    this.storageService.siteLanguage$.next(lang);
    this.langOpen = false;
    document.documentElement.setAttribute('lang', lang);
    this.closeMenu();
  }

  openMenu() { this.isMenuOpen = true; document.body.classList.add('overflow-hidden'); }
  closeMenu() { this.isMenuOpen = false; document.body.classList.remove('overflow-hidden'); }
  toggleMenu() { this.isMenuOpen ? this.closeMenu() : this.openMenu(); }

  @HostListener('document:keydown.escape')
  onEsc() { if (this.isMenuOpen) this.closeMenu(); }

  showPopup = false;

  // open popup
  openPopup() {
    console.log('Popup opened ✅');

    this.showPopup = true;
  }

  // close popup
  closePopup() {
    this.showPopup = false;
  }

  redirectToLogin() {
    this.showPopup = false;

    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
    const route = this.loginRoutes[lang];

    this.router.navigateByUrl(`/${route}?returnUrl=${this.router.url}`);
  }

  loadCart(): void {
    // Cart count is updated reactively by PaymentService via the signal,
    // so we just trigger the request and let the signal propagate.
    this.paymentService.getCart().subscribe({
      error: () => this.paymentService.setCartCount(0)
    });
  }

  ngOnInit(): void {
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }
    if (!this.authService.getToken()) {
      this.paymentService.setCartCount(0);
      this.authResolved = true;
      return;
    }
    this.authService.getProfile().subscribe({
      next: () => {
        this.authService.syncLoginSignalFromCookie();
        this.loadCart();
        this.authResolved = true;
      },
      error: () => {
        this.openPopup();
        this.authService.logout();
        this.paymentService.setCartCount(0);
        this.authResolved = true;
      }
    });
  }

}
