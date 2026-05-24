import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';
import { NgIf, NgFor } from '@angular/common';
import { NgxSkeletonLoaderComponent } from 'ngx-skeleton-loader';
import { StudentSubjectHeroComponent } from "./components/student-subject-hero/student-subject-hero.component";
import { QuickInfoComponent } from "app/subject-page/quick-info/quick-info.component";
import { ChatComponent } from "./components/chat/chat.component";
import { TeacherRatingComponent } from "./components/teacher-rating/teacher-rating.component";
import { RelatedCoursesComponent } from "app/shared/components/related-courses/related-courses.component";
import { CoursesService } from "app/shared/services/courses/courses.service";
import { AuthService } from "app/shared/services/auth-service/auth-service.service";
import { StorageService } from "app/core/storage/storage.service";
import { Course } from "app/shared/models/courses";
import { Router } from '@angular/router';
import { CurriculumComponent } from "app/subject-page/curriculum/curriculum.component";
@Component({
  selector: 'app-student-subject-details',
  imports: [
    TranslateModule,
    NgIf,
    NgFor,
    NgxSkeletonLoaderComponent,
    StudentSubjectHeroComponent,
    QuickInfoComponent,
    CurriculumComponent,
    ChatComponent,
    TeacherRatingComponent,
    RelatedCoursesComponent
  ],
  templateUrl: './student-subject-details.component.html',
  styleUrl: './student-subject-details.component.scss'
})
export class StudentSubjectDetailsComponent implements OnInit, OnDestroy {
  course: Course | null = null;
  relatedCourses: Course[] = [];
  loading = true;
  skeletonCount = Array(3);
  private destroy$ = new Subject<void>();

  constructor(
    private route: ActivatedRoute,
    private coursesService: CoursesService,
    private authService: AuthService,
    public storageService: StorageService,
    private router: Router
  ) { }

  ngOnInit(): void {

    const token = this.authService.getToken();

    if (!token) {
      this.router.navigate(['/login']);
      return;
    }

    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {

        this.route.paramMap
          .pipe(takeUntil(this.destroy$))
          .subscribe(params => {

            const courseId = params.get('course_id');

            if (courseId) {

              this.loading = true;

              this.coursesService.getCourseById(+courseId, token).subscribe(res => {

                if (res) {
                  this.course = res.course;
                  this.relatedCourses = res.related_courses || [];
                }

                this.loading = false;

              });

            } else {
              this.loading = false;
            }

          });

      });

  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
