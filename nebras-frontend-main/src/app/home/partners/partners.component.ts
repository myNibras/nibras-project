import { AfterViewInit, Component, ElementRef, ViewChild, OnInit, OnDestroy } from '@angular/core';
import Swiper from 'swiper';
import { Autoplay } from 'swiper/modules';
import { NgxSkeletonLoaderModule } from 'ngx-skeleton-loader';
import { NgIf, NgFor } from '@angular/common';
import { StorageService } from 'app/core/storage/storage.service';
import { PartnersService  } from 'app/shared/services/partners/partners.service';
import { PartnersSection ,Partner } from 'app/shared/models/partners';
import { Subject, takeUntil } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';
import { RouterLink } from '@angular/router';

Swiper.use([Autoplay]);

@Component({
  selector: 'app-partners',
  standalone: true,
  imports: [NgxSkeletonLoaderModule, NgIf, NgFor,TranslateModule , RouterLink],
  templateUrl: './partners.component.html',
})
export class PartnersComponent implements OnInit, AfterViewInit, OnDestroy {

  constructor(
    public storageService: StorageService,
    private partnersService: PartnersService
  ) {}

  partnersPage : Record<'ar' | 'en', string> = {
        ar: '/ar/شركاؤنا',
        en: '/en/partners'
    };

  loadingPartners = true;
  partnersSection: PartnersSection | null = null;
  partners : Partner[] = [];

  private destroy$ = new Subject<void>();
  private swiper?: Swiper;

  @ViewChild('partnersSwiperContainer', { static: false })
  partnersSwiperContainer!: ElementRef<HTMLElement>;

  ngOnInit(): void {
  this.storageService.siteLanguage$
    .pipe(takeUntil(this.destroy$))
    .subscribe(() => {

      this.swiper?.destroy(true, true);
      this.swiper = undefined;

      this.loadingPartners = true;

      this.partnersService.get()
        .pipe(takeUntil(this.destroy$))
        .subscribe((response: PartnersSection | null) => {

          this.partnersSection = response;
          this.partners = response?.data ?? [];

          this.loadingPartners = false;

          setTimeout(() => {
            if (this.partners.length) {
              this.initSwiper();
            }
          }, 0);
        });
    });
}

ngAfterViewInit(): void {
  if (!this.loadingPartners && this.partners.length) {
    this.initSwiper();
  }
}


  private initSwiper(): void {
    if (!this.partnersSwiperContainer?.nativeElement) return;

    const dir = this.storageService.siteLanguage$.value === 'ar' ? 'rtl' : 'ltr';
    this.partnersSwiperContainer.nativeElement.setAttribute('dir', dir);

    this.swiper?.destroy(true, true);

    this.swiper = new Swiper(this.partnersSwiperContainer.nativeElement, {
      loop: true,
      speed: 700,
      spaceBetween: 30,
      slidesPerView: 6,
      autoplay: { delay: 2500, disableOnInteraction: false },

      observer: true,
      observeParents: true,
      preventClicks: false,
      preventClicksPropagation: false,

      breakpoints: {
        0: { slidesPerView: 2, spaceBetween: 16 },
        640: { slidesPerView: 3, spaceBetween: 18 },
        1024: { slidesPerView: 5, spaceBetween: 22 },
        1280: { slidesPerView: 6, spaceBetween: 30 },
      },
    });

    this.swiper.update();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    this.swiper?.destroy(true, true);
  }
}
