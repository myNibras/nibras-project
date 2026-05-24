import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';
import { StorageService } from 'app/core/storage/storage.service';
import { Teacher } from 'app/shared/models/teachers';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-teacher-card',
  imports: [TranslateModule],
  templateUrl: './teacher-card.component.html',
  styleUrl: './teacher-card.component.scss'
})
export class TeacherCardComponent {

  constructor(
    public storageService: StorageService,
    private router: Router,
  ) { }
  @Input() teacher: Teacher | null = null;

  get teacherSubjects(): string {
    return this.teacher?.courses?.join(', ') || '';
  }

  teacherDetailsRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تفاصيل-المعلم',
    en: 'en/teacher-details'
  };

  goToTeachers() {
    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
    this.router.navigate([`/${this.teacherDetailsRoutes[lang]}`]);
  }

  onImageError(event: any) {
    event.target.src = 'app/assets/images/shared/domy-profile.png';
  }

}
