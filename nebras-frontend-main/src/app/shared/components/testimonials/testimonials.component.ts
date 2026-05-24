import {
  Component, ViewChild, ElementRef, AfterViewInit, OnDestroy,
  inject, PLATFORM_ID, NgZone,
  OnInit, OnChanges, Input, SimpleChanges
} from '@angular/core';
import { isPlatformBrowser, NgIf } from '@angular/common';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';
import { NgFor } from '@angular/common';
import { Subject, Subscription, takeUntil } from 'rxjs';
import { Testimonial, TestimonialsSection } from 'app/shared/models/testimonial';
import { StorageService } from 'app/core/storage/storage.service';
import { NgxSkeletonLoaderModule } from 'ngx-skeleton-loader';
import { TestimonialService } from 'app/shared/services/testimonial/testimonial.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-testimonials',
  standalone: true,
  imports: [NgFor, NgxSkeletonLoaderModule, TranslatePipe, NgIf],
  templateUrl: './testimonials.component.html',
  styleUrl: './testimonials.component.scss',
})
export class TestimonialsComponent implements OnInit, OnChanges, AfterViewInit, OnDestroy {
  @ViewChild('swiperContainer', { static: false }) swiperContainer!: ElementRef<HTMLElement>;
  @ViewChild('paginationEl', { static: false }) paginationEl!: ElementRef<HTMLElement>;
  @ViewChild('nextEl', { static: false }) nextEl!: ElementRef<HTMLElement>;
  @ViewChild('prevEl', { static: false }) prevEl!: ElementRef<HTMLElement>;

  /** When set, fetch testimonials for this teacher only (API: ?teacher_id=X). */
  @Input() teacherId?: number;
  /** When true, use title/description from API; otherwise use static translations. */
  @Input() useApiTitle = true;

  destroy$ = new Subject<void>;
  private swiper: any;
  private onStableSub?: Subscription;
  private langSub?: Subscription;
  isRTL = false;

  testimonials: Testimonial[] = [];
  sectionTitle = '';
  sectionDescription = '';
  loading = true;

  private readonly platformId = inject(PLATFORM_ID);
  private readonly isBrowser = isPlatformBrowser(this.platformId);

  constructor(
    private zone: NgZone,
    private translate: TranslateService,
    public storageService: StorageService,
    private testimonialService: TestimonialService,
    private router: Router,
  ) { }

  goToContactUs(): void {
    const lang = this.storageService.siteLanguage$.value === 'ar' ? 'ar' : 'en';
    const url = lang === 'ar' ? '/ar/تواصل-معنا' : '/en/contact-us';
    this.router.navigateByUrl(url);
  }


  ngOnInit(): void {
    // Skip during SSR. Loading on the server caches stale results in any
    // upstream HTML cache (CDN/edge), so admin status changes don't appear
    // until the cache evicts. Browser-only fetch keeps the list fresh on
    // every refresh.
    if (!this.isBrowser) {
      this.loading = false;
      return;
    }
    this.storageService.siteLanguage$.pipe(takeUntil(this.destroy$)).subscribe(() => this.load());
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['teacherId'] && !changes['teacherId'].firstChange) {
      this.load();
    }
  }

  private load(): void {
    this.loading = true;
    const request$ = this.teacherId != null
      ? this.testimonialService.getByTeacherId(this.teacherId)
      : this.testimonialService.get();
    request$.subscribe((response: TestimonialsSection) => {
      this.sectionTitle = response.section_title;
      this.sectionDescription = response.section_description;
      this.testimonials = response.data || [];
      setTimeout(() => {
        this.loading = false;
        this.initSwiper();
      }, 500);
    });
  }

  ngAfterViewInit(): void {
    if (!this.isBrowser) return;

    this.isRTL = (this.translate.currentLang || 'en') === 'ar';
    this.setDir(this.isRTL ? 'rtl' : 'ltr');

    this.zone.runOutsideAngular(() => setTimeout(() => this.initSwiper(), 0));

    // Heal after any DOM swap (HMR, ngIf, etc.)
    this.onStableSub = this.zone.onStable.subscribe(() => this.ensureControlsAlive());

    // 🔁 Rebuild when language toggles (fixes lag + wrong arrows)
    this.langSub = this.translate.onLangChange.subscribe(({ lang }) => {
      this.isRTL = (lang || 'en') === 'ar';
      this.rebuildForDir(this.isRTL ? 'rtl' : 'ltr');
    });
  }

  ngOnDestroy(): void {
    this.langSub?.unsubscribe();
    this.onStableSub?.unsubscribe();
    this.swiper?.destroy?.(true, true);
  }

  private setDir(dir: 'rtl' | 'ltr') {
    this.swiperContainer.nativeElement.setAttribute('dir', dir);
  }

  private async initSwiper() {
    this.swiper?.destroy?.(true, true);

    const [{ default: Swiper }, { Pagination, Autoplay, Navigation }] = await Promise.all([
      import('swiper'),
      import('swiper/modules'),
    ]);

    this.swiper = new Swiper(this.swiperContainer.nativeElement, {
      modules: [Pagination, Autoplay, Navigation],
      slidesPerView: 1,
      loop: false,
      speed: 500,
      observer: true,
      observeParents: true,
      // autoplay: { delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: false },
      pagination: { el: this.paginationEl.nativeElement, clickable: true },
      navigation: { nextEl: this.nextEl.nativeElement, prevEl: this.prevEl.nativeElement },
      breakpoints: {
        640: {
          slidesPerView: 1,
        },
        768: {
          slidesPerView: 2,
        },
        1200: {
          slidesPerView: 3,
        },
      },
    });

  }

  private rebuildForDir(dir: 'rtl' | 'ltr') {
    // stop + destroy then reinit with new dir (most reliable)
    const hadAutoplay = !!(this.swiper?.autoplay?.running);
    this.swiper?.autoplay?.stop?.();
    this.swiper?.destroy?.(true, true);

    this.setDir(dir);

    this.zone.runOutsideAngular(() => {
      setTimeout(() => {
        this.initSwiper().then(() => {
          if (hadAutoplay) setTimeout(() => this.swiper?.autoplay?.start?.(), 0);
        });
      }, 0);
    });
  }

  /** keep bullets/arrows connected after HMR/partial DOM swaps */
  private ensureControlsAlive() {
    const s = this.swiper;
    if (!s || s.destroyed) return;

    const pagEl = this.paginationEl?.nativeElement;
    const next = this.nextEl?.nativeElement;
    const prev = this.prevEl?.nativeElement;

    let changed = false;

    if (pagEl && (s.params?.pagination?.el !== pagEl || !pagEl.querySelector('.swiper-pagination-bullet'))) {
      s.params.pagination.el = pagEl;
      (s.pagination as any).el = pagEl;
      s.pagination.init?.(); s.pagination.render?.(); s.pagination.update?.();
      changed = true;
    }
    if (s.params?.navigation && (s.params.navigation.nextEl !== next || s.params.navigation.prevEl !== prev)) {
      s.params.navigation.nextEl = next; s.params.navigation.prevEl = prev;
      (s.navigation as any).nextEl = next; (s.navigation as any).prevEl = prev;
      s.navigation.init?.(); s.navigation.update?.();
      changed = true;
    }
    if (changed) {
      s.updateSize?.(); s.updateSlides?.(); s.updateSlidesClasses?.(); s.updateProgress?.();
      if ((s as any).autoplay && !(s as any).autoplay.running) setTimeout(() => (s as any).autoplay.start?.(), 0);
    }
  }

  getTestimonialImage(testimonial: Testimonial) {
    if (testimonial && testimonial.image) {
      return testimonial.image;
    } else {
      return "https://ui-avatars.com/api/?name=" + testimonial.name + "&background=F2F4F7&color=667085";
    }
  }

  /** Decorative quote character(s); API can override via `quote`. */
  quoteMark(testimonial: Testimonial): string {
    const q = testimonial.quote?.trim();
    return q && q.length > 0 ? q : '\u201C';
  }

  /** Hex from API (`#RRGGBB` or `RRGGBB`) with safe fallback. */
  quoteIconColor(testimonial: Testimonial): string {
    let raw = testimonial.quote_icon_color?.trim() ?? '';
    if (!raw) {
      return '#1396FD';
    }
    if (!raw.startsWith('#')) {
      raw = `#${raw}`;
    }
    if (/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(raw)) {
      return raw;
    }
    return '#1396FD';
  }
}
