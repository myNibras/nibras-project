import { Component, OnInit, OnDestroy } from '@angular/core';
import { HeroComponent } from "./hero/hero.component";
import { QuickInfoComponent } from "./quick-info/quick-info.component";
import { CurriculumComponent } from "./curriculum/curriculum.component";
import { RelatedCoursesComponent } from "app/shared/components/related-courses/related-courses.component";
import { ActivatedRoute, Router } from '@angular/router';
import { CoursesService } from "app/shared/services/courses/courses.service";
import { Course } from "app/shared/models/courses";
import { StorageService } from "app/core/storage/storage.service";
import { AuthService } from "app/shared/services/auth-service/auth-service.service";
import { Subject, takeUntil } from 'rxjs';
import { NgIf, NgFor } from '@angular/common';
import { NgxSkeletonLoaderComponent } from 'ngx-skeleton-loader';
import { BreadcrumbComponent } from "app/shared/components/breadcrumb/breadcrumb.component";
import { Breadcrumb } from 'app/shared/components/breadcrumb/types/breadcrumb.types';

@Component({
  selector: 'app-subject-page',
  standalone: true,
  imports: [
    HeroComponent,
    QuickInfoComponent,
    CurriculumComponent,
    RelatedCoursesComponent,
    NgxSkeletonLoaderComponent,
    NgFor,
    NgIf,
    BreadcrumbComponent
  ],
  templateUrl: './subject-page.component.html',
  styleUrl: './subject-page.component.scss'
})
export class SubjectPageComponent implements OnInit, OnDestroy {
  course: Course | null = null;
  loading = true;
  destroy$ = new Subject<void>();
  relatedCourses: Course[] = [];
  skeletonCount = Array(3);
  lang: string = '';
  breadcrumb: Breadcrumb[] = [];

  constructor(
    private route: ActivatedRoute,
    private coursesService: CoursesService,
    public storageService: StorageService,
    private router: Router,
    private authService: AuthService
  ) { }

  ngOnInit(): void {
    this.lang = this.storageService.siteLanguage$.value;
    this.storageService.siteLanguage$.pipe(takeUntil(this.destroy$)).subscribe((lang: string) => {
      this.loading = true;
      this.route.paramMap.pipe(takeUntil(this.destroy$)).subscribe(params => {
        const levelSlug = params.get('acadmic_level');
        const courseSlug = params.get('course_slug');
        const courseId = params.get('course_id');
        if (lang != this.lang && levelSlug != null && courseSlug != null && courseId != null) {
          this.lang = lang;
          if (lang == "ar") {
            this.router.navigate(["ar/تفاصيل-المادة", this.course?.academic_level.slug_ar, this.course?.slug_ar, this.course?.id]);
          } else {
            this.router.navigate(["en/course-details", this.course?.academic_level.slug_en, this.course?.slug_en, this.course?.id]);
          }
          return;
        }
        if (levelSlug && courseSlug && courseId) {
          this.coursesService.getCourseBySlug(levelSlug, courseSlug, courseId, this.authService.getToken()).subscribe(res => {
            if (res) {
              this.course = res.course;
              this.relatedCourses = res.related_courses;
              this.buildBreadcrumb();
            }
            this.loading = false;
          });

        } else {
          this.loading = false;
        }
      });
    });
  }

  getPrice(): number {
    return this.course?.price ? parseFloat(this.course.price) : 0;
  }

  getDiscountPrice(): number {
    return this.course?.discount_price ? parseFloat(this.course.discount_price) : 0;
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private buildBreadcrumb(): void {
    if (!this.course) return;

    const isAr = this.lang === 'ar';

    const subjectsAllLink = isAr ? '/ar/المواد/الكل' : '/en/subjects/all';
    const detailsBaseLink = isAr ? '/ar/تفاصيل-المادة' : '/en/course-details';


    const levelSlug = isAr ? this.course.academic_level.slug_ar : this.course.academic_level.slug_en;
    const courseSlug = isAr ? this.course.slug_ar : this.course.slug_en;


    this.breadcrumb = [
      { title: isAr ? 'المواد' : 'Subjects', link: subjectsAllLink },
      { title: this.course.title, link: `${detailsBaseLink}/${levelSlug}/${courseSlug}/${this.course.id}` }
    ];
  }

}
