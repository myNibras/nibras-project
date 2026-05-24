import {
  Component,
  ViewChild,
  ElementRef,
  AfterViewInit,
  OnDestroy,
  inject,
  PLATFORM_ID,
  NgZone,
  OnInit
} from '@angular/core';
import { isPlatformBrowser, NgFor, NgIf } from '@angular/common';
import { Router } from '@angular/router';
import { StorageService } from 'app/core/storage/storage.service';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';
import { TeatchersSectionCardComponent } from './components/teatchers-section-card/teatchers-section-card.component';
import { TeachersService } from 'app/shared/services/teachers/teachers.service';
import { Teacher } from 'app/shared/models/teachers';

@Component({
  selector: 'app-teachers-section',
  standalone: true,
  imports: [NgFor, TranslateModule, TeatchersSectionCardComponent, NgIf],
  templateUrl: './teachers-section.component.html',
  styleUrl: './teachers-section.component.scss',
})
export class TeachersSectionComponent implements OnInit, AfterViewInit, OnDestroy {

  @ViewChild('swiperContainer', { static: false })
  swiperContainer!: ElementRef<HTMLElement>;

  teachers: Teacher[] = [];
  sectionTitle: string | null = null;
  loading = true;

  private swiper: any;
  private destroy$ = new Subject<void>();

  private readonly platformId = inject(PLATFORM_ID);
  private readonly isBrowser = isPlatformBrowser(this.platformId);

  constructor(
    private zone: NgZone,
    private translate: TranslateService,
    public storageService: StorageService,
    private router: Router,
    private teachersService: TeachersService
  ) { }

  teachersRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/المعلمين',
    en: 'en/teachers'
  };

  // ✅ SAME PATTERN AS ALL TEACHERS
  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadTeachers();
      });
  }

  loadTeachers() {
    this.loading = true;
    this.teachers = [];
    this.sectionTitle = null;

    this.teachersService.getSection()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (section) => {
          if (section) {
            this.sectionTitle = section.section_title || null;
            this.teachers = Array.isArray(section.data) ? section.data : [];
          } else {
            this.teachers = [];
          }
          this.loading = false;

          if (this.isBrowser) {
            setTimeout(() => this.initSwiper(), 0);
          }
        },
        error: () => {
          this.teachers = [];
          this.sectionTitle = null;
          this.loading = false;
        }
      });
  }


  goToTeachers() {
    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
    this.router.navigate([`/${this.teachersRoutes[lang]}`]);
  }

  ngAfterViewInit(): void {
    if (!this.isBrowser) return;

    this.setDir(this.translate.currentLang === 'ar' ? 'rtl' : 'ltr');

    this.zone.runOutsideAngular(() => {
      setTimeout(() => this.initSwiper(), 0);
    });

    this.translate.onLangChange
      .pipe(takeUntil(this.destroy$))
      .subscribe(({ lang }) => {
        this.rebuildForDir(lang === 'ar' ? 'rtl' : 'ltr');
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    this.swiper?.destroy?.(true, true);
  }

  private setDir(dir: 'rtl' | 'ltr') {
    this.swiperContainer?.nativeElement?.setAttribute('dir', dir);
  }

  private async initSwiper() {
    if (!this.swiperContainer) return;

    this.swiper?.destroy?.(true, true);

    const [{ default: Swiper }, { Autoplay }] = await Promise.all([
      import('swiper'),
      import('swiper/modules'),
    ]);

    this.swiper = new Swiper(this.swiperContainer.nativeElement, {
      modules: [Autoplay],
      slidesPerView: 'auto',
      spaceBetween: 50,
      loop: true,
      speed: 800,
      autoplay: {
        delay: 2500,
        disableOnInteraction: false,
      }
    });
  }

  private rebuildForDir(dir: 'rtl' | 'ltr') {
    this.swiper?.destroy?.(true, true);
    this.setDir(dir);

    this.zone.runOutsideAngular(() => {
      setTimeout(() => this.initSwiper(), 0);
    });
  }

  goToTeacherDetails(id: number) {
    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';

    const route =
      lang === 'ar'
        ? `/ar/تفاصيل-المعلم/${id}`
        : `/en/teacher-details/${id}`;

    this.router.navigate([route]);
  }

}
