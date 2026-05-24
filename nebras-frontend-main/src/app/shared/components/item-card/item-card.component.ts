import { Component, OnInit, OnDestroy, Input } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { RouterLink, ActivatedRoute } from '@angular/router';
import { StorageService } from 'app/core/storage/storage.service';
import { Course } from 'app/shared/models/courses';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';
import { CoursesService } from 'app/shared/services/courses/courses.service';

@Component({
  selector: 'app-item-card',
  standalone: true,
  imports: [RouterLink, TranslateModule, NgIf, NgFor],
  templateUrl: './item-card.component.html',
  styleUrls: ['./item-card.component.scss'],
})
export class ItemCardComponent implements OnInit, OnDestroy {
  @Input() course?: Course;

  constructor(
    public storageService: StorageService,
    private route: ActivatedRoute,
    private coursesService: CoursesService,
  ) {}

  destroy$ = new Subject<void>();
  courses: Course[] = [];
  loading = true;

  /** normalize to 'ar' | 'en' */
  get lang(): 'ar' | 'en' {
    const v = (this.storageService.siteLanguage$.value || 'en').toLowerCase();
    return v.startsWith('ar') ? 'ar' : 'en';
  }

  ngOnInit(): void {
    // When the card is rendered standalone (no @Input course bound), it still
    // supports the older "list-fetch-by-slug" usage pattern. Skip the fetch
    // when a course was passed in.
    if (this.course) {
      this.loading = false;
      return;
    }

    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loading = true;

        this.route.paramMap
          .pipe(takeUntil(this.destroy$))
          .subscribe(params => {
            const slug = params.get('slug');

            if (!slug || slug === 'all-courses') {
              this.coursesService.get().subscribe(courses => {
                this.courses = courses;
                this.loading = false;
              });
            } else {
              this.coursesService.getByLevelSlug(slug).subscribe(courses => {
                this.courses = courses;
                this.loading = false;
              });
            }
          });
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  getLocalizedCourseType(type?: string): string {
    const normalized = (type || '').toLowerCase();
    const isArabic = this.lang === 'ar';

    if (normalized === 'recorded') return isArabic ? 'مسجل' : 'Recorded';
    if (normalized === 'online') return isArabic ? 'أونلاين' : 'Online';
    return type || '';
  }

  // ============== price helpers ==============
  private parsePrice(raw?: string | number | null): number {
    if (raw == null) return 0;
    const s = String(raw).trim();
    if (!s) return 0;
    const n = parseFloat(s.replace(/[^\d.\-]/g, ''));
    return isFinite(n) ? n : 0;
  }

  get hasDiscount(): boolean {
    if (!this.course) return false;
    const original = this.parsePrice(this.course.price);
    const discounted = this.parsePrice(this.course.discount_price);
    return discounted > 0 && original > 0 && discounted < original;
  }

  get effectivePrice(): number {
    if (!this.course) return 0;
    return this.hasDiscount ? this.parsePrice(this.course.discount_price) : this.parsePrice(this.course.price);
  }

  get originalPrice(): number {
    return this.parsePrice(this.course?.price);
  }

  get discountPercent(): number {
    if (!this.hasDiscount) return 0;
    const original = this.originalPrice;
    const discounted = this.parsePrice(this.course?.discount_price);
    if (original <= 0) return 0;
    return Math.round(((original - discounted) / original) * 100);
  }

  // ============== seats ==============
  get availableSeats(): number | null {
    const v = this.course?.final_available_seats ?? this.course?.available_seats;
    return v == null ? null : Number(v);
  }

  get isSoldOut(): boolean {
    const s = this.availableSeats;
    return s != null && s <= 0;
  }

  // ============== rating ==============
  get hasRating(): boolean {
    return typeof this.course?.rating === 'number' && (this.course?.rating ?? 0) > 0;
  }

  get ratingStars(): { full: number; half: boolean; empty: number } {
    const r = Math.max(0, Math.min(5, this.course?.rating ?? 0));
    const full = Math.floor(r);
    const half = r - full >= 0.5;
    const empty = 5 - full - (half ? 1 : 0);
    return { full, half, empty };
  }

  arrayOf(n: number): unknown[] {
    return Array.from({ length: Math.max(0, n) });
  }
}
