import { Component, Input } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { PurchasedCourse } from 'app/shared/models/courses';
import { StorageService } from 'app/core/storage/storage.service';
import { RouterLink } from '@angular/router';


@Component({
  selector: 'app-material-card',
  imports: [TranslateModule, RouterLink],
  templateUrl: './material-card.component.html',
  styleUrl: './material-card.component.scss'
})
export class MaterialCardComponent {

  constructor(
    public storageService: StorageService,
  ) { }

  @Input() material!: PurchasedCourse;

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

  openLink(link?: string) {
    if (!link) return;
    window.open(link, '_blank');
  }


}
