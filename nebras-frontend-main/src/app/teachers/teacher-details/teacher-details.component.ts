import { Component, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';
import { ItemCardComponent } from "app/shared/components/item-card/item-card.component";
import { TeachersService } from 'app/shared/services/teachers/teachers.service';
import { Teacher } from 'app/shared/models/teachers';
import { StorageService } from 'app/core/storage/storage.service';
import { Course } from 'app/shared/models/courses';
import { NgFor, NgIf, NgStyle } from '@angular/common';
import { TestimonialsComponent } from 'app/shared/components/testimonials/testimonials.component';
import { SafeUrlPipe } from 'app/shared/pipes/safe-url.pipe';

@Component({
  selector: 'app-teacher-details',
  standalone: true,
  imports: [TranslateModule, ItemCardComponent, NgFor, NgIf, NgStyle, TestimonialsComponent, SafeUrlPipe],
  templateUrl: './teacher-details.component.html',
  styleUrl: './teacher-details.component.scss'
})
export class TeacherDetailsComponent implements OnInit, OnDestroy {

  teacher: Teacher | null = null;
  loading = true;
  courses: Course[] = [];
  subjects: string = '';
  rating: number = 0;

  /** Exposed for template (e.g. app-testimonials [teacherId]) */
  teacherId!: number;

  private destroy$ = new Subject<void>();

  constructor(
    private route: ActivatedRoute,
    private teachersService: TeachersService,
    private storageService: StorageService
  ) { }

  ngOnInit(): void {

    // Get ID from route
    this.teacherId = Number(this.route.snapshot.paramMap.get('id'));

    // Reload when language changes
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadTeacher();
      });
  }


  loadTeacher() {
    if (!this.teacherId) return;

    this.loading = true;

    this.teachersService.getById(this.teacherId)
      .pipe(takeUntil(this.destroy$))
      .subscribe(teacher => {
        this.teacher = teacher;
        this.rating = teacher?.reviews ?? 0;
        this.subjects = teacher?.courses?.join(', ') || '';
        this.loading = false;
      });

    this.teachersService.getCoursesByTeacherId(this.teacherId)
      .pipe(takeUntil(this.destroy$))
      .subscribe(courses => {
        this.courses = courses;
      });

  }


  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
  onImageError(event: any) {
    event.target.src = 'app/assets/images/shared/domy-profile.png';
  }

  get isArabic(): boolean {
    return this.storageService.siteLanguage$.value === 'ar';
  }
}
