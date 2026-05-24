import { Component, Input } from '@angular/core';
import { Teacher } from 'app/shared/models/teachers';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-teatchers-section-card',
  imports: [TranslateModule],
  templateUrl: './teatchers-section-card.component.html',
  styleUrl: './teatchers-section-card.component.scss'
})
export class TeatchersSectionCardComponent {

  @Input() teacher: Teacher | null = null;

  onImageError(event: any) {
    event.target.src = 'app/assets/images/shared/domy-profile.png';
  }

  get teacherSubjects(): string {
    return this.teacher?.courses?.join(', ') || '';
  }

}
