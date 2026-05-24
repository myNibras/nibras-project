import { Component, Input } from '@angular/core';
import { NgIf } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { Course } from 'app/shared/models/courses';
import { StorageService } from 'app/core/storage/storage.service';

@Component({
  selector: 'app-student-subject-hero',
  imports: [NgIf, TranslateModule],
  templateUrl: './student-subject-hero.component.html',
  styleUrl: './student-subject-hero.component.scss'
})
export class StudentSubjectHeroComponent {
  constructor(public storageService: StorageService) {}

  @Input() course: Course | null = null;

  getLocalizedCourseType(type?: string): string {
    const normalized = (type || '').toLowerCase();
    const isArabic = this.storageService.siteLanguage$.value === 'ar';

    if (normalized === 'recorded') {
      return isArabic ? 'مسجل' : 'Recorded';
    }

    if (normalized === 'online') {
      return isArabic ? 'أونلاين' : 'Online';
    }

    return type || '';
  }
}
