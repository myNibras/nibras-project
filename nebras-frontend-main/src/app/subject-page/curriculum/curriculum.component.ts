import { Component, Input } from '@angular/core';
import { NgIf, NgFor, NgClass } from '@angular/common';  // 👈 add NgClass
import { Curriculum } from 'app/shared/models/courses';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-curriculum',
  standalone: true,
  imports: [NgIf, NgFor, NgClass, TranslateModule],   // 👈 include it here
  templateUrl: './curriculum.component.html',
  styleUrls: ['./curriculum.component.scss']
})
export class CurriculumComponent {
  @Input() curriculums: Curriculum[] = [];

  openIndex: number | null = 0;

  toggleCurriculum(index: number) {
    this.openIndex = this.openIndex === index ? null : index;
  }
}
