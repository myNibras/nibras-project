import { Component, OnDestroy, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { TeacherCardComponent } from '../teacher-card/teacher-card.component';
import { isPlatformBrowser, NgClass, NgFor, NgIf, NgTemplateOutlet } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';
import { Router } from '@angular/router';
import { TeachersService } from 'app/shared/services/teachers/teachers.service';
import { Teacher } from 'app/shared/models/teachers';
import { StorageService } from 'app/core/storage/storage.service';
import { FormsModule } from '@angular/forms';

interface FilterOption<V = string | number> {
  label: string;
  value: V;
  count: number;
}

interface ExperienceBucket {
  key: 'lt-2' | '2-5' | '5-10' | '10-plus';
  labelKey: string;
  match: (years: number) => boolean;
}

type SortKey = 'popular' | 'experience-desc' | 'experience-asc' | 'name';

@Component({
  selector: 'app-all-teachers',
  standalone: true,
  imports: [
    TeacherCardComponent,
    NgClass,
    NgFor,
    NgIf,
    NgTemplateOutlet,
    TranslateModule,
    FormsModule,
  ],
  templateUrl: './all-teachers.component.html',
  styleUrl: './all-teachers.component.scss',
})
export class AllTeachersComponent implements OnInit, OnDestroy {

  // ---------- raw data ----------
  allTeachers: Teacher[] = [];
  filteredTeachers: Teacher[] = [];

  /** From API (`TeachersSectionData`) — hero heading and subtitle */
  sectionTitle = '';
  sectionDescription = '';

  loading = true;

  // ---------- filter state ----------
  searchTerm = '';
  selectedSubjectKeys = new Set<string>();
  selectedExperienceBuckets = new Set<ExperienceBucket['key']>();
  sortBy: SortKey = 'popular';

  // ---------- derived option lists ----------
  subjects: FilterOption<string>[] = [];

  readonly experienceBuckets: ExperienceBucket[] = [
    { key: 'lt-2',     labelKey: 'exp_lt_2',     match: y => y < 2 },
    { key: '2-5',      labelKey: 'exp_2_5',      match: y => y >= 2 && y <= 5 },
    { key: '5-10',     labelKey: 'exp_5_10',     match: y => y > 5 && y <= 10 },
    { key: '10-plus',  labelKey: 'exp_10_plus',  match: y => y > 10 },
  ];

  // ---------- ui state ----------
  mobileFilterOpen = false;

  closeMobileFilters(): void {
    this.mobileFilterOpen = false;
  }

  private destroy$ = new Subject<void>();
  private readonly platformId = inject(PLATFORM_ID);

  constructor(
    private teachersService: TeachersService,
    private storageService: StorageService,
    private router: Router,
  ) {}

  // ================= lifecycle =================
  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => this.loadTeachers());
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  // ================= data loading =================
  private loadTeachers(): void {
    this.loading = true;
    this.teachersService.getSection()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (section) => {
          if (section) {
            this.sectionTitle = section.section_title ?? '';
            this.sectionDescription = section.section_description ?? '';
            this.allTeachers = Array.isArray(section.data) ? section.data : [];
          } else {
            this.sectionTitle = '';
            this.sectionDescription = '';
            this.allTeachers = [];
          }
          this.rebuildSubjectOptions();
          this.applyFilters();
          this.loading = false;
        },
        error: () => {
          this.allTeachers = [];
          this.filteredTeachers = [];
          this.sectionTitle = '';
          this.sectionDescription = '';
          this.loading = false;
        },
      });
  }

  /** Build the Subject option list by deduping all course titles across teachers. */
  private rebuildSubjectOptions(): void {
    const map = new Map<string, FilterOption<string>>();
    for (const t of this.allTeachers) {
      for (const courseTitle of t.courses ?? []) {
        const title = (courseTitle || '').trim();
        if (!title) continue;
        const key = title.toLowerCase();
        const entry = map.get(key);
        if (entry) entry.count += 1;
        else map.set(key, { label: title, value: key, count: 1 });
      }
    }
    this.subjects = Array.from(map.values()).sort((a, b) => a.label.localeCompare(b.label));
  }

  // ================= filtering =================
  applyFilters(): void {
    const term = this.searchTerm.trim().toLowerCase();

    let result = this.allTeachers.filter(t => {
      // Search across name + position + courses
      if (term) {
        const haystack = [
          t.name,
          t.position,
          ...(t.courses ?? []),
        ].filter(Boolean).join(' ').toLowerCase();
        if (!haystack.includes(term)) return false;
      }

      // Subject (any of the teacher's courses must match a selected subject key)
      if (this.selectedSubjectKeys.size > 0) {
        const teacherCourseKeys = (t.courses ?? []).map(c => (c || '').trim().toLowerCase());
        const hasMatch = teacherCourseKeys.some(k => this.selectedSubjectKeys.has(k));
        if (!hasMatch) return false;
      }

      // Experience bucket
      if (this.selectedExperienceBuckets.size > 0) {
        const years = Number(t.years_of_experience ?? 0);
        const matchesAny = this.experienceBuckets.some(
          b => this.selectedExperienceBuckets.has(b.key) && b.match(years),
        );
        if (!matchesAny) return false;
      }

      return true;
    });

    result = this.sortTeachers(result, this.sortBy);
    this.filteredTeachers = result;
  }

  /** After the user changes filters, jump to the top of the page (same as subjects page). */
  private scrollToTopAfterFilter(): void {
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }
    setTimeout(() => {
      window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
    }, 0);
  }

  private sortTeachers(list: Teacher[], by: SortKey): Teacher[] {
    const arr = [...list];
    switch (by) {
      case 'experience-desc':
        return arr.sort((a, b) => Number(b.years_of_experience ?? 0) - Number(a.years_of_experience ?? 0));
      case 'experience-asc':
        return arr.sort((a, b) => Number(a.years_of_experience ?? 0) - Number(b.years_of_experience ?? 0));
      case 'name':
        return arr.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
      case 'popular':
      default:
        return arr;
    }
  }

  // ================= filter mutations =================
  toggleSubject(key: string): void {
    this.selectedSubjectKeys.has(key) ? this.selectedSubjectKeys.delete(key) : this.selectedSubjectKeys.add(key);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  toggleExperienceBucket(key: ExperienceBucket['key']): void {
    this.selectedExperienceBuckets.has(key)
      ? this.selectedExperienceBuckets.delete(key)
      : this.selectedExperienceBuckets.add(key);
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
    this.selectedSubjectKeys.clear();
    this.selectedExperienceBuckets.clear();
    this.sortBy = 'popular';
    this.mobileFilterOpen = false;
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  // ================= helpers for the template =================
  isSubjectSelected(key: string): boolean {
    return this.selectedSubjectKeys.has(key);
  }

  isExperienceSelected(key: ExperienceBucket['key']): boolean {
    return this.selectedExperienceBuckets.has(key);
  }

  get activeFilterCount(): number {
    let n = 0;
    n += this.selectedSubjectKeys.size;
    n += this.selectedExperienceBuckets.size;
    if (this.searchTerm.trim().length > 0) n += 1;
    return n;
  }

  removeSubject(key: string): void {
    this.selectedSubjectKeys.delete(key);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }
  removeExperienceBucket(key: ExperienceBucket['key']): void {
    this.selectedExperienceBuckets.delete(key);
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }
  clearSearch(): void {
    this.searchTerm = '';
    this.applyFilters();
    this.scrollToTopAfterFilter();
  }

  subjectLabel(key: string): string {
    return this.subjects.find(s => s.value === key)?.label || '';
  }
  experienceLabelKey(key: ExperienceBucket['key']): string {
    return this.experienceBuckets.find(b => b.key === key)?.labelKey || '';
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
