import { NgFor, NgIf } from '@angular/common';
import {
  AfterViewInit,
  Component,
  ElementRef,
  ViewChild,
  OnDestroy,
  inject,
  Input,
  OnInit
} from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Subscription, Subject, takeUntil } from 'rxjs';
import Swiper from 'swiper';
import type { SwiperOptions } from 'swiper/types';
import { Mousewheel, Keyboard, Autoplay } from 'swiper/modules';
import { ItemCardComponent } from '../item-card/item-card.component';
import { Course } from 'app/shared/models/courses';
import { TranslateModule } from '@ngx-translate/core';
import { ActivatedRoute } from '@angular/router';
import { StorageService } from 'app/core/storage/storage.service';
import { CoursesService } from 'app/shared/services/courses/courses.service';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';

Swiper.use([Mousewheel, Keyboard, Autoplay]);

@Component({
  selector: 'app-related-courses',
  standalone: true,
  imports: [ItemCardComponent, NgFor, TranslateModule, NgIf],
  templateUrl: './related-courses.component.html',
  styleUrls: ['./related-courses.component.scss']
})
export class RelatedCoursesComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('swiperContainer', { static: false }) swiperContainer!: ElementRef<HTMLElement>;
  @Input() courses: Course[] | undefined; // still keep Input for flexibility

  private swiperInstance: Swiper | null = null;
  private langSub: Subscription;
  private translateService = inject(TranslateService);
  private mql?: MediaQueryList;
  private io?: IntersectionObserver;
  private destroy$ = new Subject<void>();

  loading = true;

  constructor(
    public storageService: StorageService,
    private route: ActivatedRoute,
    private coursesService: CoursesService,
    private authService: AuthService
  ) {
    this.langSub = this.translateService.onLangChange.subscribe(() => {
      this.reinitSwiper();
    });
  }

  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loading = true;

        this.route.paramMap
          .pipe(takeUntil(this.destroy$))
          .subscribe(params => {
            const levelSlug = params.get('acadmic_level');
            const courseSlug = params.get('course_slug');
            const courseId = params.get('course_id');

            if (levelSlug && courseSlug && courseId) {
              this.coursesService.getCourseBySlug(levelSlug, courseSlug, courseId, this.authService.getToken()).subscribe(res => {
                if (res) {
                  // ✅ fetch related courses directly from response
                  this.courses = res.related_courses;
                }
                this.loading = false;
                this.reinitSwiper(); // re-init swiper after data loads
              });
            } else {
              this.loading = false;
            }
          });
      });
  }

  ngAfterViewInit(): void {
    this.mql = window.matchMedia('(min-width: 1280px)');
    this.mql.addEventListener('change', this.applyMode);

    this.io = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) {
        this.applyMode();
        this.io?.disconnect();
      }
    });
    this.io.observe(this.swiperContainer.nativeElement);
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    this.mql?.removeEventListener('change', this.applyMode);
    this.io?.disconnect();
    this.destroySwiper();
    this.langSub.unsubscribe();
  }

  private applyMode = () => {
    const host = this.swiperContainer?.nativeElement;
    if (!host) return;

    const slideCount = host.querySelectorAll(':scope .swiper-wrapper > .swiper-slide').length;
    const isDesktop = this.mql?.matches ?? false;

    const needSlider = !isDesktop || slideCount > 3;

    if (needSlider) this.initSwiper();
    else this.destroySwiper();
  };

  private initSwiper(): void {
    if (this.swiperInstance) return;

    const host = this.swiperContainer.nativeElement;

    const config: SwiperOptions = {
      slidesPerView: 3,
      spaceBetween: 0,
      centeredSlides: false,
      roundLengths: true,
      slidesOffsetBefore: 0,
      slidesOffsetAfter: 0,

      freeMode: {
        enabled: true,
        momentum: true,
        momentumBounce: false,
      },
      loop: true,
      watchOverflow: true,

      mousewheel: { forceToAxis: true, sensitivity: 1 },
      keyboard: { enabled: true, onlyInViewport: true },

      autoplay: {
        delay: 2000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true
      },

      breakpoints: {
        0: { slidesPerView: 1.1, freeMode: { enabled: false } },
        768: { slidesPerView: 2, freeMode: { enabled: true } },
        1023: { slidesPerView: 3, freeMode: { enabled: true } }
      },

      observer: true,
      observeParents: true,
    };

    this.swiperInstance = new Swiper(host, config);
  }

  private destroySwiper(): void {
    if (this.swiperInstance) {
      this.swiperInstance.destroy(true, true);
      this.swiperInstance = null;
    }
  }

  private reinitSwiper() {
    this.destroySwiper();
    this.applyMode();
  }
}
