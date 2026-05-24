import { Component, OnDestroy, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser, NgClass, NgFor, NgIf, NgTemplateOutlet } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';
import { StorageService } from 'app/core/storage/storage.service';
import { FormsModule } from '@angular/forms';
import { CoursesService } from 'app/shared/services/courses/courses.service';
import { ClassesService } from 'app/shared/services/classes/classes.service';
import { Course } from 'app/shared/models/courses';
import { Grade } from 'app/shared/models/classes';
import { ItemCardComponent } from 'app/shared/components/item-card/item-card.component';

interface FilterOption<V = string | number> {
  label: string;
  value: V;
  count: number;
}

interface PriceBucket {
  key: 'free' | 'under-50' | '50-100' | '100-200' | '200-plus';
  labelKey: string;
  match: (price: number) => boolean;
}

type SortKey = 'popular' | 'price-asc' | 'price-desc' | 'newest';

@Component({
  selector: 'app-subjects',
  standalone: true,
  imports: [
    ItemCardComponent,
    NgClass,
    NgFor,
    NgIf,
    NgTemplateOutlet,
    TranslateModule,
    FormsModule,
  ],
  templateUrl: './subjects.component.html',
  styleUrl: './subjects.component.scss',
})
export class SubjectsComponent implements OnInit, OnDestroy {
  // ---------- raw data ----------
  allCourses: Course[] = [];
  filteredCourses: Course[] = [];

  loading = true;
  gradesLoading = true;

  // ---------- filter state ----------
  searchTerm = '';
  selectedGradeIds = new Set<number>();
  selectedSubjectKeys = new Set<string>(); // dedup by lowercase title
  selectedTeacherIds = new Set<number>();
  selectedPriceBuckets = new Set<PriceBucket['key']>();
  showOnlyAvailable = false;
  sortBy: SortKey = 'popular';

  // ---------- derived option lists (rebuilt when allCourses change) ----------
  grades: FilterOption<number>[] = [];
  subjects: FilterOption<string>[] = [];
  teachers: FilterOption<number>[] = [];

  readonly priceBuckets: PriceBucket[] = [
    { key: 'free',      labelKey: 'Free',                   match: p => p === 0 },
    { key: 'under-50',  labelKey: 'filter_price_under_50',  match: p => p > 0 && p < 50 },
    { key: '50-100',    labelKey: 'filter_price_50_100',    match: p => p >= 50 && p <= 100 },
    { key: '100-200',   labelKey: 'filter_price_100_200',   match: p => p > 100 && p <= 200 },
    { key: '200-plus',  labelKey: 'filter_price_200_plus',  match: p => p > 200 },
  ];

  // ---------- ui state ----------
  mobileFilterOpen = false;

  closeMobileFilters(): void {
    this.mobileFilterOpen = false;
  }

  private destroy$ = new Subject<void>();
  private readonly platformId = inject(PLATFORM_ID);

  constructor(
    private coursesService: CoursesService,
    private classesService: ClassesService,
    private storageService: StorageService,
  ) {}

  // ================= lifecycle =================
  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadCourses();
        this.loadClasses();
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  // ================= data loading =================
  private loadCourses(): void {
    this.loading = true;
    this.coursesService
      .get()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (courses: Course[]) => {
          this.allCourses = courses ?? [];
          this.rebuildOptionLists();
          this.applyFilters();
          this.loading = false;
        },
        error: () => {
          this.allCourses = [];
          this.filteredCourses = [];
          this.loading = false;
        },
      });
  }

  private loadClasses(): void {
    this.gradesLoading = true;
    this.classesService
      .get()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (classes: Grade[]) => {
          // Use grade list as canonical source; counts will be filled in once
          // courses also load (rebuildOptionLists is run twice — that's fine).
          this.grades = classes.map(g => ({
            label: g.name,
            value: g.id,
            count: this.countCoursesForGrade(g.id),
          }));
          this.gradesLoading = false;
        },
        error: () => {
          this.grades = [];
          this.gradesLoading = false;
        },
      });
  }

  private countCoursesForGrade(gradeId: number): number {
    return this.allCourses.filter(c => c.class_id === gradeId).length;
  }

  /** Rebuild Subject and Teacher option lists from current course set. */
  private rebuildOptionLists(): void {
    // Subjects deduped by lowercase title.
    const subjectMap = new Map<string, FilterOption<string>>();
    for (const c of this.allCourses) {
      const title = (c.title || '').trim();
      if (!title) continue;
      const key = title.toLowerCase();
      const entry = subjectMap.get(key);
      if (entry) {
        entry.count += 1;
      } else {
        subjectMap.set(key, { label: title, value: key, count: 1 });
      }
    }
    this.subjects = Array.from(subjectMap.values()).sort((a, b) =>
      a.label.localeCompare(b.label),
    );

    // Teachers deduped by id.
    const teacherMap = new Map<number, FilterOption<number>>();
    for (const c of this.allCourses) {
      if (!c.teacher?.id) continue;
      const entry = teacherMap.get(c.teacher.id);
      if (entry) {
        entry.count += 1;
      } else {
        teacherMap.set(c.teacher.id, {
          label: c.teacher.name || '',
          value: c.teacher.id,
          count: 1,
        });
      }
    }
    this.teachers = Array.from(teacherMap.values()).sort((a, b) =>
      a.label.localeCompare(b.label),
    );

    // Update grade counts now that courses are loaded.
    this.grades = this.grades.map(g => ({
      ...g,
      count: this.countCoursesForGrade(g.value),
    }));
  }

  // ================= filtering =================
  applyFilters(): void {
    const term = this.searchTerm.trim().toLowerCase();

    let result = this.allCourses.filter(c => {
      // Search
      if (term) {
        const haystack = [
          c.title,
          c.short_description,
          c.teacher?.name,
        ].filter(Boolean).join(' ').toLowerCase();
        if (!haystack.includes(term)) return false;
      }

      // Grade
      if (this.selectedGradeIds.size > 0) {
        if (c.class_id == null || !this.selectedGradeIds.has(c.class_id)) return false;
      }

      // Subject (by deduped title key)
      if (this.selectedSubjectKeys.size > 0) {
        const titleKey = (c.title || '').trim().toLowerCase();
        if (!this.selectedSubjectKeys.has(titleKey)) return false;
      }

      // Teacher
      if (this.selectedTeacherIds.size > 0) {
        if (!c.teacher?.id || !this.selectedTeacherIds.has(c.teacher.id)) return false;
      }

      // Price
      if (this.selectedPriceBuckets.size > 0) {
        const price = this.effectivePrice(c);
        const matchesAnyBucket = this.priceBuckets.some(
          b => this.selectedPriceBuckets.has(b.key) && b.match(price),
        );
        if (!matchesAnyBucket) return false;
      }

      // Availability
      if (this.showOnlyAvailable) {
        const seats = c.final_available_seats ?? c.available_seats;
        if (seats != null && seats <= 0) return false;
      }

      return true;
    });

    result = this.sortCourses(result, this.sortBy);
    this.filteredCourses = result;
  }

  /** After the user changes filters, jump to the top of the page so the updated grid is in view. */
  private scrollToTopAfterFilter(): void {
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }
    setTimeout(() => {
      window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
    }, 0);
  }

  private effectivePrice(c: Course): number {
    const raw = c.discount_price?.toString().trim() || c.price?.toString().trim() || '0';
    const n = parseFloat(raw.replace(/[^\d.\-]/g, ''));
    return isFinite(n) ? n : 0;
  }

  private sortCourses(list: Course[], by: SortKey): Course[] {
    const arr = [...list];
    switch (by) {
      case 'price-asc':
        return arr.sort((a, b) => this.effectivePrice(a) - this.effectivePrice(b));
      case 'price-desc':
        return arr.sort((a, b) => this.effectivePrice(b) - this.effectivePrice(a));
      case 'newest':
        return arr.sort((a, b) => (b.id ?? 0) - (a.id ?? 0));
      case 'popular':
      default:
        return arr;
    }
  }

  // ================= filter mutations =================
  toggleGrade(id: number): void {
    this.selectedGradeIds.has(id) ? this.selectedGradeIds.delete(id) : this.selectedGradeIds.add(id);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  toggleSubject(key: string): void {
    this.selectedSubjectKeys.has(key) ? this.selectedSubjectKeys.delete(key) : this.selectedSubjectKeys.add(key);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  toggleTeacher(id: number): void {
    this.selectedTeacherIds.has(id) ? this.selectedTeacherIds.delete(id) : this.selectedTeacherIds.add(id);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  togglePriceBucket(key: PriceBucket['key']): void {
    this.selectedPriceBuckets.has(key) ? this.selectedPriceBuckets.delete(key) : this.selectedPriceBuckets.add(key);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  toggleAvailability(): void {
    this.showOnlyAvailable = !this.showOnlyAvailable;
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  onSortChange(value: string): void {
    this.sortBy = (value as SortKey) || 'popular';
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  resetFilters(): void {
    this.searchTerm = '';
    this.selectedGradeIds.clear();
    this.selectedSubjectKeys.clear();
    this.selectedTeacherIds.clear();
    this.selectedPriceBuckets.clear();
    this.showOnlyAvailable = false;
    this.sortBy = 'popular';
    this.mobileFilterOpen = false;
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  // ================= helpers for the template =================
  isGradeSelected(id: number): boolean {
    return this.selectedGradeIds.has(id);
  }

  isSubjectSelected(key: string): boolean {
    return this.selectedSubjectKeys.has(key);
  }

  isTeacherSelected(id: number): boolean {
    return this.selectedTeacherIds.has(id);
  }

  isPriceSelected(key: PriceBucket['key']): boolean {
    return this.selectedPriceBuckets.has(key);
  }

  get activeFilterCount(): number {
    let n = 0;
    n += this.selectedGradeIds.size;
    n += this.selectedSubjectKeys.size;
    n += this.selectedTeacherIds.size;
    n += this.selectedPriceBuckets.size;
    if (this.showOnlyAvailable) n += 1;
    if (this.searchTerm.trim().length > 0) n += 1;
    return n;
  }

  removeGrade(id: number): void {
    this.selectedGradeIds.delete(id);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }
  removeSubject(key: string): void {
    this.selectedSubjectKeys.delete(key);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }
  removeTeacher(id: number): void {
    this.selectedTeacherIds.delete(id);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }
  removePriceBucket(key: PriceBucket['key']): void {
    this.selectedPriceBuckets.delete(key);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }
  clearSearch(): void {
    this.searchTerm = '';
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  gradeLabel(id: number): string {
    return this.grades.find(g => g.value === id)?.label || '';
  }
  subjectLabel(key: string): string {
    return this.subjects.find(s => s.value === key)?.label || '';
  }
  teacherLabel(id: number): string {
    return this.teachers.find(t => t.value === id)?.label || '';
  }
  priceLabelKey(key: PriceBucket['key']): string {
    return this.priceBuckets.find(b => b.key === key)?.labelKey || '';
  }
}
