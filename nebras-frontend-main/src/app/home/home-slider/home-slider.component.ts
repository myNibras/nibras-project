import {
  Component, ViewChild, ElementRef, AfterViewInit, OnDestroy,
  inject, PLATFORM_ID, NgZone,
  OnInit
} from '@angular/core';
import { isPlatformBrowser, NgIf } from '@angular/common';
import { TranslateService } from '@ngx-translate/core';
import { NgFor } from '@angular/common';
import { Subject, Subscription, takeUntil } from 'rxjs';
import { HomeSlider } from 'app/shared/models/home-slider';
import { HomeSliderService } from 'app/shared/services/home-slider/home-slider.service';
import { StorageService } from 'app/core/storage/storage.service';
import { NgxSkeletonLoaderModule } from 'ngx-skeleton-loader';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-home-slider',
  standalone: true,
  imports: [NgFor, NgxSkeletonLoaderModule, NgIf, RouterLink],
  templateUrl: './home-slider.component.html',
  styleUrl: './home-slider.component.scss',
})
export class HomeSliderComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('swiperContainer', { static: false }) swiperContainer!: ElementRef<HTMLElement>;
  @ViewChild('paginationEl', { static: false }) paginationEl!: ElementRef<HTMLElement>;
  @ViewChild('nextEl', { static: false }) nextEl!: ElementRef<HTMLElement>;
  @ViewChild('prevEl', { static: false }) prevEl!: ElementRef<HTMLElement>;

  destroy$ = new Subject<void>;
  private swiper: any;
  private onStableSub?: Subscription;
  private langSub?: Subscription;
  isRTL = false;

  homeSliders: HomeSlider[] = [];
  loading = true;

  private readonly platformId = inject(PLATFORM_ID);
  private readonly isBrowser = isPlatformBrowser(this.platformId);

  constructor(
    private zone: NgZone,
    private translate: TranslateService,
    public storageService: StorageService,
    private homeSliderService: HomeSliderService,
  ) { }


  loginRoutes: Record<'ar' | 'en', string> = {
    ar: '/ar/تسجيل-الدخول',
    en: '/en/login'
  };

  ngOnInit(): void {
    this.storageService.siteLanguage$.pipe(takeUntil(this.destroy$)).subscribe(() => {
      this.loading = true;
      this.homeSliderService.get().subscribe((response: HomeSlider[]) => {
        this.homeSliders = response;
        setTimeout(() => {
          this.loading = false;
          this.initSwiper();
        }, 500)
      });
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
      autoplay: { delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: false },
      pagination: { el: this.paginationEl.nativeElement, clickable: true },
      navigation: { nextEl: this.nextEl.nativeElement, prevEl: this.prevEl.nativeElement },
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
}
