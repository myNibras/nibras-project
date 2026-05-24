import { Component, Input, OnInit, OnDestroy, OnChanges, SimpleChanges } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { Subject, takeUntil } from 'rxjs';
import { HttpErrorResponse } from '@angular/common/http';
import { StorageService } from 'app/core/storage/storage.service';
import { Teacher } from 'app/shared/models/teachers';
import { TeachersService } from 'app/shared/services/teachers/teachers.service';
import { Router } from '@angular/router';
import { TestimonialService } from 'app/shared/services/testimonial/testimonial.service';
import { TranslateService } from '@ngx-translate/core';
import { inject } from '@angular/core';

@Component({
  selector: 'app-teacher-rating',
  standalone: true,
  imports: [
    TranslateModule,
    NgFor,
    ReactiveFormsModule,
    NgIf
  ],
  templateUrl: './teacher-rating.component.html',
  styleUrls: ['./teacher-rating.component.scss']
})
export class TeacherRatingComponent implements OnInit, OnDestroy, OnChanges {

  @Input() teacherId?: number | null;
  @Input() courseId?: number | null;

  ratingForm!: FormGroup;
  rating = 0;

  teacher: Teacher | null = null;
  previewImage: string | null = null;
  reviewSent = false;
  private destroy$ = new Subject<void>();
  private translate = inject(TranslateService);

  // Inline error alert (instead of browser window.alert)
  errorAlertVisible = false;
  errorAlertMessage = '';

  constructor(
    private fb: FormBuilder,
    private teachersService: TeachersService,
    private testimonialService: TestimonialService,
    private storageService: StorageService,
    private router: Router,
  ) { }

  selectedFile: File | null = null;

  teacherDetailsRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تفاصيل-المعلم',
    en: 'en/teacher-details'
  };


  ngOnInit(): void {
    this.ratingForm = this.fb.group({
      comment: ['']
    });
  }

  onFileSelected(event: any): void {

    const file = event.target.files[0];

    if (!file) return;

    const allowedMimeTypes = ['image/webp', 'image/jpeg', 'image/jpg', 'image/png'];
    const allowedExtensions = ['webp', 'jpeg', 'jpg', 'png'];
    const ext = (file.name.split('.').pop() || '').toLowerCase();

    const isAllowedByMime = file.type ? allowedMimeTypes.includes(file.type) : false;
    const isAllowedByExtension = ext ? allowedExtensions.includes(ext) : false;

    if (!isAllowedByMime && !isAllowedByExtension) {
      this.showError('validation_webp_only'); // key reused; updated text to match allowed types
      return;
    }

    if (file.size > 2048 * 1024) {
      this.showError('validation_image_size');
      return;
    }

    this.selectedFile = file;

    const reader = new FileReader();

    reader.onload = () => {
      this.previewImage = reader.result as string;
    };

    reader.readAsDataURL(file);
  }

  private showError(key: string) {
    this.translate.get(key).subscribe(msg => this.showBeautifulAlert(String(msg)));
  }

  private showBeautifulAlert(message: string): void {
    this.errorAlertMessage = message;
    this.errorAlertVisible = true;
  }

  private handleApiError(err: unknown): void {
    let message: string | undefined;

    // HTTP errors like 422 (Laravel validation) come as HttpErrorResponse
    if (err instanceof HttpErrorResponse) {
      const body = err.error;
      if (body && typeof body === 'object' && 'message' in body) {
        const m = (body as { message?: unknown }).message;
        if (typeof m === 'string' && m) {
          message = m;
        }
      }
    } else if (err && typeof err === 'object' && 'message' in err) {
      const m = (err as { message?: unknown }).message;
      if (typeof m === 'string' && m) {
        message = m;
      }
    }

    this.showBeautifulAlert(
      message ?? this.translate.instant('something went wrong, please try again')
    );
  }
  submit(): void {

    if (!this.teacherId) return;

    // Hide any previous error before submitting
    this.errorAlertVisible = false;

    const comment = this.ratingForm.value.comment;

    if (!comment) {
      this.showError('validation_write_review');
      return;
    }

    if (!this.rating) {
      this.showError('validation_select_rating');
      return;
    }
        
    const formData = new FormData();
    formData.append('text', comment);
    formData.append('rate', String(this.rating));

    if (this.courseId != null) {
      formData.append('course_id', String(this.courseId));
    }

    // Attach selected image if user uploaded one.
    if (this.selectedFile) {
      formData.append('image', this.selectedFile, this.selectedFile.name);
    }

    this.testimonialService.create(formData)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: () => {
        this.reviewSent = true;
          this.errorAlertVisible = false;

        this.ratingForm.reset();
        this.rating = 0;
        this.selectedFile = null;
        this.previewImage = null;
        },
        error: (err) => this.handleApiError(err),
      });

  }
  ngOnChanges(changes: SimpleChanges): void {

    if (changes['teacherId'] && this.teacherId) {
      this.loadTeacher();
    }

  }

  loadTeacher() {

    if (!this.teacherId) return;

    this.teachersService.getById(this.teacherId)
      .pipe(takeUntil(this.destroy$))
      .subscribe(teacher => {
        this.teacher = teacher;
      });

  }

  setRating(star: number): void {
    this.rating = star;
  }

  get commentLength(): number {
    return this.ratingForm.get('comment')?.value?.length || 0;
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  goToTeachers() {

    if (!this.teacherId) return;

    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';

    this.router.navigate([
      this.teacherDetailsRoutes[lang],
      this.teacherId
    ]);

  }
}